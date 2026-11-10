#!/usr/bin/env python3
"""
EventEase demo automation
=========================

Opens a real browser and uses EventEase by itself, step by step, like a person would:

  organizer   : register, create events, design the form, add a gallery image
  visitor     : browse events without logging in
  participant : register for an event, choose a category, get a QR ticket
  rules       : one person one ticket, closed deadline, full event
  organizer   : check-in by ticket code, reports, CSV downloads

Every step prints PASS or FAIL, takes a screenshot, and shows a caption on the page.
At the end it writes demo_output/report.html with all the steps and screenshots.
After a fully successful run, it removes only that run's test records and unreferenced uploaded images.
Failed runs keep their test data for debugging; use --keep-test-data to always retain it.

Setup (once, with internet):
    pip install playwright
    playwright install chromium

Run (the site must be running: MySQL started and `php artisan serve`):
    python eventease_demo.py
    python eventease_demo.py --slow 500            # slower, easier to follow live
    python eventease_demo.py --headless            # no window, for a quick check
    python eventease_demo.py --keep-going          # do not stop at the first failure
    python eventease_demo.py --start-server ~/eventease   # also starts `php artisan serve`
    python eventease_demo.py --keep-test-data            # keep generated test records
"""

import argparse
import csv
import html
import json
import re
import tempfile
import struct
import subprocess
import sys
import time
import urllib.request
import zlib
from contextlib import contextmanager
from datetime import datetime, timedelta
from pathlib import Path

from playwright.sync_api import expect, sync_playwright

# ----------------------------------------------------------------------------
# Settings you may want to change
# ----------------------------------------------------------------------------

CATEGORIES = [
    # name, price, slots, inclusions
    ("42K", "2999", "50", "Official race shirt\nFinisher medal\nPost-race meal"),
    ("21K", "1499", "50", "Official race shirt\nFinisher medal"),
]

# Shows a caption at the bottom of every page (it survives page changes)
CAPTION_JS = """
(() => {
  const draw = () => {
    let text = null;
    try { text = sessionStorage.getItem('demoCaption'); } catch (e) {}
    let el = document.getElementById('demo-caption');
    if (!text) { if (el) el.remove(); return; }
    if (!el) {
      el = document.createElement('div');
      el.id = 'demo-caption';
      el.style.cssText = 'position:fixed;left:50%;bottom:16px;transform:translateX(-50%);'
        + 'z-index:2147483647;background:#111827;color:#fff;padding:10px 18px;border-radius:10px;'
        + 'font:600 15px system-ui,sans-serif;box-shadow:0 4px 20px rgba(0,0,0,.35);'
        + 'pointer-events:none;max-width:90vw;text-align:center';
      document.documentElement.appendChild(el);
    }
    el.textContent = text;
  };
  window.__drawCaption = draw;
  document.addEventListener('DOMContentLoaded', draw);
})();
"""

CAPTION_SET = """text => {
  try { sessionStorage.setItem('demoCaption', text); } catch (e) {}
  if (window.__drawCaption) window.__drawCaption();
}"""


# ----------------------------------------------------------------------------
# Small helpers
# ----------------------------------------------------------------------------

def make_poster(path: Path, width=1280, height=720):
    """Write a purple gradient PNG, so the demo event has a poster (no extra library needed)."""
    top, bottom = (79, 70, 229), (168, 85, 247)
    rows = []

    for y in range(height):
        t = y / (height - 1)
        color = bytes(int(top[i] + (bottom[i] - top[i]) * t) for i in range(3))
        rows.append(b"\x00" + color * width)

    def chunk(kind: bytes, data: bytes) -> bytes:
        body = kind + data
        return struct.pack(">I", len(data)) + body + struct.pack(">I", zlib.crc32(body) & 0xFFFFFFFF)

    png = b"\x89PNG\r\n\x1a\n"
    png += chunk(b"IHDR", struct.pack(">IIBBBBB", width, height, 8, 2, 0, 0, 0))
    png += chunk(b"IDAT", zlib.compress(b"".join(rows), 9))
    png += chunk(b"IEND", b"")
    path.write_bytes(png)


def slug(text: str) -> str:
    return re.sub(r"[^a-z0-9]+", "-", text.lower()).strip("-")[:50]


def reachable(url: str, seconds: int = 5) -> bool:
    try:
        urllib.request.urlopen(url, timeout=seconds)
        return True
    except Exception as error:
        # An HTTP error page still means the server answered
        return hasattr(error, "code")


def see(locator, timeout=8000):
    expect(locator).to_be_visible(timeout=timeout)


class Abort(Exception):
    """Stops the run after a failed step."""


class Runner:
    def __init__(self, page, context, base_url, out_dir, keep_going):
        self.page = page
        self.context = context
        self.base = base_url.rstrip("/")
        self.out = out_dir
        self.keep_going = keep_going
        self.results = []
        self.count = 0

    def url(self, path: str) -> str:
        return self.base + path

    def say(self, text: str):
        try:
            self.page.evaluate(CAPTION_SET, text)
        except Exception:
            pass  # about:blank has no storage

    def shot(self, number, title, failed=False):
        name = f"{number:02d}-{'FAILED-' if failed else ''}{slug(title)}.png"

        try:
            self.page.screenshot(path=str(self.out / name))
            return name
        except Exception:
            return None

    @contextmanager
    def step(self, title, optional=False):
        self.count += 1
        number = self.count
        started = time.time()

        print(f"[{number:02d}] {title} ... ", end="", flush=True)
        self.say(f"{number}. {title}")

        try:
            yield
        except Exception as error:
            seconds = round(time.time() - started, 1)
            lines = str(error).strip().splitlines()
            message = lines[0] if lines else type(error).__name__
            status = "SKIP" if optional else "FAIL"
            shot = self.shot(number, title, failed=True)

            self.results.append({"n": number, "title": title, "status": status,
                                 "seconds": seconds, "message": message, "shot": shot})
            print(f"{status} ({message})")

            if status == "FAIL" and not self.keep_going:
                raise Abort() from error
        else:
            seconds = round(time.time() - started, 1)
            self.page.wait_for_timeout(500)
            shot = self.shot(number, title)

            self.results.append({"n": number, "title": title, "status": "PASS",
                                 "seconds": seconds, "message": "", "shot": shot})
            print(f"PASS ({seconds}s)")


# ----------------------------------------------------------------------------
# Actions on the site
# ----------------------------------------------------------------------------

def register_account(r: Runner, name, email, role, password):
    page = r.page
    r.context.clear_cookies()  # the register page is only for people who are logged out
    page.goto(r.url("/register"))
    page.fill("input[name=name]", name)
    page.fill("input[name=email]", email)
    page.select_option("select[name=role]", role)
    page.fill("input[name=password]", password)
    page.fill("input[name=password_confirmation]", password)
    page.get_by_role("button", name=re.compile("create account", re.I)).click()
    if role == "organizer":
        page.wait_for_url(re.compile(r"/(?:organizer/)?dashboard/?$"))
    else:
        page.wait_for_url(re.compile(rf"/{role}/dashboard/?$"))


def login(r: Runner, email, password, role):
    page = r.page
    r.context.clear_cookies()
    page.goto(r.url("/login"))
    page.fill("input[name=email]", email)
    page.fill("input[name=password]", password)
    page.get_by_role("button", name=re.compile("sign in", re.I)).click()
    if role == "organizer":
        page.wait_for_url(re.compile(r"/(?:organizer/)?dashboard/?$"))
    else:
        page.wait_for_url(re.compile(rf"/{role}/dashboard/?$"))


def create_event(r: Runner, title, event_type, start, deadline, slots, location, poster=None):
    page = r.page
    page.goto(r.url("/organizer/events/create"))
    page.fill("input[name=title]", title)
    page.select_option("select[name=type]", event_type)

    if poster:
        page.set_input_files("input[name=thumbnail]", str(poster))

    page.fill("textarea[name=description]", f"Automated demo event: {title}.")
    page.fill("input[name=event_date]", start.strftime("%Y-%m-%d"))
    page.fill("input[name=event_time]", start.strftime("%H:%M"))

    if deadline:
        page.fill("input[name=registration_deadline]", deadline.strftime("%Y-%m-%dT%H:%M"))

    page.fill("input[name=location]", location)
    page.fill("input[name=total_slots]", str(slots))
    page.get_by_role("button", name=re.compile("save event", re.I)).click()

    # A new event opens its form designer right away
    page.wait_for_url(re.compile(r"/organizer/events/\d+/form"))

    return int(re.search(r"/events/(\d+)/form", page.url).group(1))


def design_form(r: Runner, event_id, title):
    page = r.page
    page.goto(r.url(f"/organizer/events/{event_id}/form"))

    add_category = page.get_by_role("button", name="+ Add Category")

    for i, (name, price, slots, inclusions) in enumerate(CATEGORIES):
        add_category.click()
        page.fill(f'input[name="categories[{i}][name]"]', name)
        page.fill(f'input[name="categories[{i}][price]"]', price)
        page.fill(f'input[name="categories[{i}][slots]"]', slots)
        page.fill(f'textarea[name="categories[{i}][inclusions]"]', inclusions)

    page.get_by_role("button", name="+ Section heading").click()
    page.fill('input[name="blocks[0][label]"]', "Personal Information")

    page.get_by_role("button", name="+ Short answer").click()
    page.fill('input[name="blocks[1][label]"]', "Contact number")
    page.check('input[name="blocks[1][required]"]')

    page.get_by_role("button", name="+ Dropdown").click()
    page.fill('input[name="blocks[2][label]"]', "T-shirt size")
    page.fill('textarea[name="blocks[2][options]"]', "Small\nMedium\nLarge")

    # Look and feel
    page.fill('input[name="theme[primary]"]', "#7c3aed")
    page.fill('input[name="theme[title]"]', f"Join {title}")
    page.fill('textarea[name="theme[intro]"]', "Fill in the form to get your QR ticket.")

    page.get_by_role("button", name="Save Form").click()
    see(page.get_by_text("Registration form saved"))


def register_for_event(r: Runner, event_id):
    """Fills in whatever the organizer's form asks for, submits it, and returns the ticket code."""
    page = r.page
    page.goto(r.url(f"/participant/events/{event_id}"))
    page.get_by_role("link", name=re.compile(r"^Register( for this event)?$", re.I)).click()
    page.wait_for_url(re.compile(rf"/participant/events/{event_id}/register"))

    radios = page.locator("input[name=category_id]:not([disabled])")
    if radios.count():
        radios.first.check()

    texts = page.locator('input[type=text][name^="answers["]')
    if texts.count():
        texts.first.fill("0917 123 4567")

    dropdowns = page.locator('select[name^="answers["]')
    if dropdowns.count():
        dropdowns.first.select_option(index=1)

    page.locator("button.ee-btn").click()
    page.wait_for_url(re.compile(r"/participant/registrations/\d+"))

    code = page.locator("p.font-mono").first.inner_text().strip()

    if not re.fullmatch(r"[A-Z0-9]{10}", code):
        raise AssertionError(f"The ticket code looks wrong: {code!r}")

    return code


def check_in(r: Runner, event_id, code, expected_title):
    page = r.page
    page.fill("#ticket-code", code)
    page.press("#ticket-code", "Enter")
    see(page.get_by_role("heading", name=expected_title, exact=True))


def save_download(page, click_target, path: Path):
    with page.expect_download() as download:
        click_target.click()

    download.value.save_as(str(path))

    return list(csv.reader(path.open(encoding="utf-8-sig")))


def find_project_root(args) -> Path:
    # Find the Laravel project that owns the database used by this demo.
    candidates = []

    if getattr(args, "project_dir", None):
        candidates.append(Path(args.project_dir))
    if getattr(args, "start_server", None):
        candidates.append(Path(args.start_server))
    candidates.extend([Path.cwd(), Path(__file__).resolve().parent])

    for candidate in candidates:
        candidate = candidate.expanduser().resolve()
        if (candidate / "artisan").is_file() and (candidate / "bootstrap" / "app.php").is_file():
            return candidate

    raise RuntimeError(
        "Could not find the Laravel project. Run this script from the EventEase project folder "
        "or pass --project-dir /path/to/eventease."
    )


def cleanup_test_data(project_dir: Path, organizer: dict, participants: list, event_titles: list) -> dict:
    # Delete only this run's exact test records and unreferenced uploaded images.
    php_source = r'''<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$payload = json_decode(file_get_contents('php://stdin'), true, 512, JSON_THROW_ON_ERROR);
$DB = \Illuminate\Support\Facades\DB::class;
$Storage = \Illuminate\Support\Facades\Storage::class;

try {
    $organizer = $DB::table('users')
        ->where('name', $payload['organizer']['name'])
        ->where('email', $payload['organizer']['email'])
        ->where('role', 'organizer')
        ->first();

    if (!$organizer) {
        throw new RuntimeException('The exact test organizer account was not found; nothing was deleted.');
    }

    $participantRows = [];
    foreach ($payload['participants'] as $expected) {
        $participant = $DB::table('users')
            ->where('name', $expected['name'])
            ->where('email', $expected['email'])
            ->where('role', 'participant')
            ->first();

        if (!$participant) {
            throw new RuntimeException('A test participant account was not found; nothing was deleted.');
        }
        $participantRows[] = $participant;
    }

    $events = $DB::table('events')
        ->where('user_id', $organizer->id)
        ->whereIn('title', $payload['event_titles'])
        ->get(['id', 'title', 'thumbnail']);

    if ($events->count() !== count($payload['event_titles'])) {
        throw new RuntimeException('The expected test events were not all found; nothing was deleted.');
    }

    $eventIds = $events->pluck('id')->all();
    $paths = [];
    foreach ($events as $event) {
        if (!empty($event->thumbnail)) {
            $paths[] = $event->thumbnail;
        }
    }

    $galleryPaths = $DB::table('event_gallery_items')
        ->whereIn('event_id', $eventIds)
        ->pluck('path')->all();
    $paths = array_values(array_unique(array_filter(array_merge($paths, $galleryPaths))));

    $summary = [
        'events_deleted' => 0,
        'participants_deleted' => [],
        'organizer_deleted' => false,
        'files_deleted' => 0,
        'files_kept_because_still_referenced' => [],
    ];

    $DB::transaction(function () use ($DB, $eventIds, $participantRows, $organizer, &$summary) {
        // Foreign-key cascades remove each event's fields, categories, registrations,
        // registration answers, and gallery database records.
        $summary['events_deleted'] = $DB::table('events')->whereIn('id', $eventIds)->delete();

        // Remove only the exact demo participants, and only if they have no remaining data.
        foreach ($participantRows as $participant) {
            $hasEvents = $DB::table('events')->where('user_id', $participant->id)->exists();
            $hasRegistrations = $DB::table('registrations')->where('user_id', $participant->id)->exists();

            if (!$hasEvents && !$hasRegistrations) {
                $DB::table('users')->where('id', $participant->id)->delete();
                $summary['participants_deleted'][] = $participant->email;
            }
        }

        // Keep the organizer if it owns any event or registration outside this test run.
        $organizerHasEvents = $DB::table('events')->where('user_id', $organizer->id)->exists();
        $organizerHasRegistrations = $DB::table('registrations')->where('user_id', $organizer->id)->exists();
        if (!$organizerHasEvents && !$organizerHasRegistrations) {
            $DB::table('users')->where('id', $organizer->id)->delete();
            $summary['organizer_deleted'] = true;
        }
    });

    // Files are deleted only after the database transaction commits, and only when
    // no remaining event or gallery record references the same public-disk path.
    foreach ($paths as $path) {
        $normalized = str_replace('\\', '/', (string) $path);
        if ($normalized === '' || str_starts_with($normalized, '/') || preg_match('#(^|/)\\.\\.(/|$)#', $normalized)) {
            continue;
        }

        $stillUsedByEvent = $DB::table('events')->where('thumbnail', $path)->exists();
        $stillUsedByGallery = $DB::table('event_gallery_items')->where('path', $path)->exists();
        if ($stillUsedByEvent || $stillUsedByGallery) {
            $summary['files_kept_because_still_referenced'][] = $path;
            continue;
        }

        $disk = $Storage::disk('public');
        if ($disk->exists($path) && $disk->delete($path)) {
            $summary['files_deleted']++;
        }
    }

    echo json_encode($summary, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . PHP_EOL);
    exit(1);
}
'''

    payload = {
        "organizer": {"name": organizer["name"], "email": organizer["email"]},
        "participants": [
            {"name": person["name"], "email": person["email"]}
            for person in participants
        ],
        "event_titles": event_titles,
    }

    temp_path = None
    try:
        with tempfile.NamedTemporaryFile(
            mode="w", encoding="utf-8", suffix=".php",
            prefix=".eventease-demo-cleanup-", dir=project_dir, delete=False
        ) as temp_file:
            temp_file.write(php_source)
            temp_path = Path(temp_file.name)

        result = subprocess.run(
            ["php", str(temp_path)],
            cwd=project_dir,
            input=json.dumps(payload),
            text=True,
            capture_output=True,
            timeout=90,
        )

        if result.returncode != 0:
            detail = result.stderr.strip() or result.stdout.strip() or "Unknown PHP cleanup error"
            raise RuntimeError(f"Laravel cleanup failed: {detail}")

        try:
            summary = json.loads(result.stdout)
        except json.JSONDecodeError as error:
            raise RuntimeError(
                "Laravel cleanup ran but returned an unreadable result: " + result.stdout[:500]
            ) from error

        print(
            "Cleanup summary: "
            f"{summary['events_deleted']} events, "
            f"{len(summary['participants_deleted'])} participants, "
            f"organizer {'deleted' if summary['organizer_deleted'] else 'preserved'}, "
            f"{summary['files_deleted']} uploaded files."
        )
        return summary
    finally:
        if temp_path and temp_path.exists():
            temp_path.unlink()


# ----------------------------------------------------------------------------
# The demo itself
# ----------------------------------------------------------------------------

def run_demo(r: Runner, args):
    stamp = datetime.now().strftime("%m%d%H%M%S")
    password = args.password

    organizer = {
        "name": "Earl Jordan Parreno",
        "email": f"earl.parreno.{stamp}@eventease.test",
    }
    participant_jordan = {
        "name": "Jordan Parreno",
        "email": f"jordan.parreno.{stamp}@eventease.test",
    }
    participant_ej = {
        "name": "EJ Parreno",
        "email": f"ej.parreno.{stamp}@eventease.test",
    }

    title = f"Earl Jordan Parreno - Cebu Fun Run {stamp}"
    closed_title = f"Parreno Community Run {stamp}"
    full_title = f"EJ Parreno - One Slot Concert {stamp}"

    poster = r.out / "poster.png"
    make_poster(poster)

    start = (datetime.now() + timedelta(days=30)).replace(hour=17, minute=30, second=0, microsecond=0)
    deadline = (start - timedelta(days=10)).replace(hour=12, minute=0)

    page = r.page
    state = {}

    # ---------------------------------------------------------------- organizer
    with r.step("Open the EventEase home page"):
        page.goto(r.url("/"))
        see(page.get_by_text("EventEase").first)

    with r.step("Register an organizer account"):
        register_account(r, organizer["name"], organizer["email"], "organizer", password)
        see(page.get_by_text("Organizer Dashboard").first)

    with r.step("Create a Fun Run event with a poster and a registration deadline"):
        state["event"] = create_event(r, title, "funrun", start, deadline, 100, "SM Seaside Grounds, Cebu", poster)

    with r.step("Design the registration form: categories, questions, colors"):
        design_form(r, state["event"], title)

    with r.step("Add a route map to the event gallery", optional=True):
        page.goto(r.url(f"/organizer/events/{state['event']}/gallery"))
        page.fill("input[name=section]", "Route maps")
        page.set_input_files('input[name="images[]"]', str(poster))
        page.get_by_role("button", name="Upload", exact=True).click()
        see(page.get_by_text(re.compile(r"images? added")))

    with r.step("Create an event whose registration deadline already passed"):
        state["closed"] = create_event(r, closed_title, "festival", start - timedelta(days=25),
                                       datetime.now() - timedelta(days=1), 50, "Cebu City")

    with r.step("Create a concert with only 1 slot"):
        state["full"] = create_event(r, full_title, "concert", start - timedelta(days=20), None, 1, "Cebu City")

    # ------------------------------------------------------------------ visitor
    with r.step("A visitor who is not logged in can browse events but must log in to register"):
        r.context.clear_cookies()
        page.goto(r.url("/"))
        see(page.get_by_text(title).first)

        page.goto(r.url(f"/events/{state['event']}"))
        see(page.get_by_role("link", name=re.compile("log in to register", re.I)))

    with r.step("The event page shows a countdown to the start", optional=True):
        see(page.get_by_text(re.compile("Starts in|Event starts in")).first)

    # -------------------------------------------------------------- participant
    with r.step("Register Jordan Parreno as a participant"):
        register_account(r, participant_jordan["name"], participant_jordan["email"], "participant", password)
        see(page.get_by_text("Participant Dashboard").first)

    with r.step("Jordan Parreno registers for the Fun Run, chooses a category, and gets a QR ticket"):
        state["code"] = register_for_event(r, state["event"])
        see(page.locator(".ticket-qr svg"))

    with r.step("The ticket shows in My Registrations"):
        page.goto(r.url("/participant/registrations"))
        see(page.get_by_text(state["code"]).first)

    with r.step("Rule: one person, one ticket (a second registration is refused)"):
        page.goto(r.url(f"/participant/events/{state['event']}/register"))
        see(page.get_by_text("You are registered").first)

    with r.step("Rule: registration is closed after the deadline"):
        page.goto(r.url(f"/participant/events/{state['closed']}"))
        see(page.get_by_text("Registration is closed").first)

    with r.step("Jordan Parreno takes the only slot of the concert"):
        register_for_event(r, state["full"])

    with r.step("Register EJ Parreno as a second participant"):
        register_account(r, participant_ej["name"], participant_ej["email"], "participant", password)

    with r.step("Rule: EJ Parreno sees that the one-slot concert is full"):
        page.goto(r.url(f"/participant/events/{state['full']}"))
        see(page.get_by_text("This event is full").first)

    # ---------------------------------------------------------------- organizer
    with r.step("The organizer logs in again"):
        login(r, organizer["email"], password, "organizer")

    with r.step("Check-in: typing the ticket code marks Jordan Parreno as attended"):
        page.goto(r.url(f"/organizer/events/{state['event']}/check-in"))
        check_in(r, state["event"], state["code"], "Checked in")

    with r.step("Rule: the same ticket cannot be checked in twice"):
        check_in(r, state["event"], state["code"], "Already checked in")

    with r.step("Rule: a wrong ticket code is rejected"):
        check_in(r, state["event"], "ZZZZZZZZZZ", "Ticket not found")

    with r.step("Reports show 100% attendance, and the summary downloads as CSV", optional=True):
        page.goto(r.url(f"/organizer/reports?event={state['event']}"))
        see(page.get_by_text("100% of registered people"))

        rows = save_download(page, page.get_by_role("link", name=re.compile("download csv", re.I)),
                             r.out / "report-summary.csv")

        if not any(title in cell for row in rows for cell in row):
            raise AssertionError("The report CSV does not contain the event")

    with r.step("The participant list downloads as CSV with Jordan Parreno's answers and ticket"):
        page.goto(r.url(f"/organizer/events/{state['event']}"))
        rows = save_download(page, page.get_by_role("link", name=re.compile("download csv", re.I)).first,
                             r.out / "participants.csv")

        flat = [cell for row in rows for cell in row]

        if participant_jordan["email"] not in flat or state["code"] not in flat:
            raise AssertionError("The participant CSV is missing Jordan Parreno's email or ticket code")

    cleanup_summary = {"status": "not_run"}

    if args.keep_test_data:
        cleanup_summary = {"status": "kept_by_user_request"}
        print("Test accounts and events were kept (--keep-test-data).")
    elif any(item["status"] == "FAIL" for item in r.results):
        cleanup_summary = {"status": "kept_because_test_failed"}
        print("Test data was kept because at least one test step failed.")
    else:
        with r.step("Clean up this run's test accounts, events, and uploaded images"):
            project_dir = find_project_root(args)
            cleanup_summary = cleanup_test_data(
                project_dir,
                organizer,
                [participant_jordan, participant_ej],
                [title, closed_title, full_title],
            )
            cleanup_summary["status"] = "completed"

    r.say("Demo finished")

    return {
        "organizer_account": organizer,
        "participant_jordan_parreno": participant_jordan,
        "participant_ej_parreno": participant_ej,
        "main_event": title,
        "cleanup": cleanup_summary,
    }


# ----------------------------------------------------------------------------
# Report
# ----------------------------------------------------------------------------

def write_reports(r: Runner, accounts, started_at, elapsed):
    passed = sum(1 for x in r.results if x["status"] == "PASS")
    failed = sum(1 for x in r.results if x["status"] == "FAIL")
    skipped = sum(1 for x in r.results if x["status"] == "SKIP")

    (r.out / "results.json").write_text(json.dumps(
        {"started": started_at, "seconds": round(elapsed, 1), "passed": passed,
         "failed": failed, "skipped": skipped, "steps": r.results, "accounts": accounts},
        indent=2))

    colors = {"PASS": "#15803d", "FAIL": "#b91c1c", "SKIP": "#a16207"}
    rows = []

    for x in r.results:
        shot = f'<a href="{x["shot"]}"><img src="{x["shot"]}" width="220"></a>' if x["shot"] else ""
        note = f'<div style="color:#b91c1c">{html.escape(x["message"])}</div>' if x["message"] else ""
        rows.append(
            f'<tr><td>{x["n"]}</td><td>{html.escape(x["title"])}{note}</td>'
            f'<td style="color:{colors[x["status"]]};font-weight:700">{x["status"]}</td>'
            f'<td>{x["seconds"]}s</td><td>{shot}</td></tr>')

    (r.out / "report.html").write_text(f"""<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><title>EventEase automation report</title>
<style>
body{{font-family:system-ui,sans-serif;margin:32px;color:#111827}}
table{{border-collapse:collapse;width:100%}} td,th{{border-bottom:1px solid #e5e7eb;padding:10px;text-align:left;vertical-align:top}}
.card{{display:inline-block;margin:0 12px 16px 0;padding:12px 20px;border-radius:10px;background:#f3f4f6}}
</style></head><body>
<h1>EventEase automation report</h1>
<p>Run started {html.escape(started_at)}. Total time {round(elapsed, 1)} seconds.</p>
<div class="card"><b style="color:#15803d">{passed}</b> passed</div>
<div class="card"><b style="color:#b91c1c">{failed}</b> failed</div>
<div class="card"><b style="color:#a16207">{skipped}</b> skipped</div>
<table><tr><th>#</th><th>Step</th><th>Result</th><th>Time</th><th>Screenshot</th></tr>{''.join(rows)}</table>
</body></html>""")

    print()
    print("=" * 60)
    print(f"  {passed} passed | {failed} failed | {skipped} skipped | {round(elapsed, 1)} seconds")
    print(f"  Report:      {r.out / 'report.html'}")
    print(f"  Screenshots: {r.out}")
    print("=" * 60)

    return failed


# ----------------------------------------------------------------------------
# Main
# ----------------------------------------------------------------------------

def main():
    parser = argparse.ArgumentParser(description="Automated end-to-end demo of EventEase")
    parser.add_argument("--url", default="http://localhost:8000", help="address of the running site")
    parser.add_argument("--headless", action="store_true", help="run without a visible browser window")
    parser.add_argument("--slow", type=int, default=250, help="milliseconds between actions (default 250)")
    parser.add_argument("--keep-going", action="store_true", help="continue after a failed step")
    parser.add_argument("--keep-open", action="store_true", help="wait before closing the browser at the end")
    parser.add_argument("--out", default="demo_output", help="folder for screenshots and the report")
    parser.add_argument("--password", default="Demo#12345", help="password for the accounts it creates")
    parser.add_argument("--project-dir", help="Laravel project folder used for safe test-data cleanup")
    parser.add_argument("--keep-test-data", action="store_true",
                        help="keep the generated accounts and events instead of cleaning them up")
    parser.add_argument("--start-server", metavar="PROJECT_DIR",
                        help="start `php artisan serve` in this project folder first")
    args = parser.parse_args()

    out = Path(args.out)
    out.mkdir(parents=True, exist_ok=True)

    server = None

    if args.start_server:
        print(f"Starting php artisan serve in {args.start_server} ...")
        server = subprocess.Popen(["php", "artisan", "serve"], cwd=args.start_server,
                                  stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)

        for _ in range(30):
            if reachable(args.url, 2):
                break
            time.sleep(1)

    if not reachable(args.url):
        print(f"\nCannot open {args.url}\n"
              "  1. Start MySQL (XAMPP Control Panel, or: sudo systemctl start mariadb)\n"
              "  2. In the project folder run: php artisan serve\n"
              "  3. Run this script again (or use --start-server PROJECT_DIR)")
        if server:
            server.terminate()
        sys.exit(2)

    started_at = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    started = time.time()
    accounts = {}
    failed = 1

    print(f"\nEventEase automation | {args.url}\n")

    try:
        with sync_playwright() as p:
            browser = p.chromium.launch(headless=args.headless, slow_mo=args.slow)
            context = browser.new_context(viewport={"width": 1366, "height": 820}, accept_downloads=True)
            context.add_init_script(CAPTION_JS)
            context.set_default_timeout(15000)
            page = context.new_page()

            runner = Runner(page, context, args.url, out, args.keep_going)

            try:
                accounts = run_demo(runner, args)
            except Abort:
                print("\nStopped at the first failed step (use --keep-going to continue).")
            finally:
                failed = write_reports(runner, accounts, started_at, time.time() - started)

                if args.keep_open and not args.headless:
                    input("\nPress Enter to close the browser ...")

                browser.close()
    finally:
        if server:
            server.terminate()

    sys.exit(1 if failed else 0)


if __name__ == "__main__":
    main()
