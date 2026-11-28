<x-public-layout title="FAQ | EventEase" hide-dashboard>
    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8" x-data="{ tab: 'all' }">
        
        <x-back-home />

        <h1 class="text-3xl font-bold text-slate-900">Frequently asked questions</h1>
        <p class="mt-2 text-gray-800">Quick answers about registering, tickets, and running events on EventEase.</p>

        <div class="mt-6 flex flex-wrap gap-2" role="group" aria-label="Choose a group of questions">
            @foreach (['all' => 'All', 'participants' => 'Participants', 'organizers' => 'Organizers', 'account' => 'Account and privacy', 'help' => 'Troubleshooting'] as $key => $label)
                <button type="button" x-on:click="tab = '{{ $key }}'"
                        x-bind:aria-pressed="(tab === '{{ $key }}').toString()"
                        x-bind:class="tab === '{{ $key }}' ? 'border-slate-900 bg-slate-900 text-white' : 'border-gray-400 bg-white text-slate-900 hover:bg-gray-50'"
                        class="tap rounded-full border px-4 text-sm font-semibold transition-colors">{{ $label }}</button>
            @endforeach
        </div>

        <div class="mt-8 space-y-6">

            {{-- PARTICIPANTS --}}
            <section x-show="tab === 'all' || tab === 'participants'" aria-labelledby="faq-participants"
                     class="rounded-xl bg-white px-6 pb-1 pt-5 shadow">
                <h2 id="faq-participants" class="text-xl font-bold text-slate-900">For participants</h2>

                <div class="mt-2">
                    <x-faq-item question="Do I need an account to look at events?">
                        <p>No. Everyone can browse the home page and open event pages. You only need a Participant account when you want to register.</p>
                    </x-faq-item>

                    <x-faq-item question="How do I register for an event?">
                        <ol>
                            <li>Log in, or create an account with the type <strong>Participant</strong>.</li>
                            <li>Open the event and click <strong>Register for this event</strong>.</li>
                            <li>Choose a category if the event has them (for example 21K).</li>
                            <li>Fill in the form, tick the agreement box, and press <strong>Register</strong>.</li>
                        </ol>
                        <p>You then see a thank-you page, and your ticket is saved under <strong>My tickets</strong>.</p>
                    </x-faq-item>

                    <x-faq-item question="Where is my ticket?">
                        <p>Click <strong>My tickets</strong> in the top menu, then <strong>View ticket</strong>. The ticket has your QR code and a ticket code. You can show it on your phone or print it.</p>
                    </x-faq-item>

                    <x-faq-item question="What do I bring to the event?">
                        <p>Bring your QR code (on your phone or printed) or your ticket code. Also bring a valid ID, because the organizer may check it at the entrance.</p>
                    </x-faq-item>

                    <x-faq-item question="Can I register more than once for the same event?">
                        <p>No. It is one ticket per person for each event, and EventEase stops a second registration. Using several accounts to get extra tickets breaks the <a href="{{ route('terms') }}">Terms and Conditions</a>.</p>
                    </x-faq-item>

                    <x-faq-item question="Why can't I register?">
                        <p>Check these reasons:</p>
                        <ul>
                            <li>The registration deadline has passed, or the event has started.</li>
                            <li>The event or the category you chose is full. A label such as "Only 2 left" shows how many slots remain.</li>
                            <li>You already registered for this event.</li>
                            <li>You are logged in as an organizer or an admin. Only Participant accounts can register.</li>
                            <li>The event is not public yet, because an admin has not approved it.</li>
                        </ul>
                    </x-faq-item>

                    <x-faq-item question="Can I cancel my registration?">
                        <p>Not inside EventEase yet. Please contact the organizer of the event and ask them to remove your registration.</p>
                    </x-faq-item>

                    <x-faq-item question="Do I pay on EventEase?">
                        <p>No. EventEase does not take payments. If a category shows a price, it is for your information. Ask the organizer how and when to pay.</p>
                    </x-faq-item>

                    <x-faq-item question="Can I give or sell my ticket to someone else?">
                        <p>No. A ticket is personal, and it cannot be transferred, sold, or resold. Do not post your QR code in public, because someone else could use it.</p>
                    </x-faq-item>

                    <x-faq-item question="The event uses an outside form. What do I do?">
                        <p>Some organizers use their own form, such as Google Forms. Click the button on the event page and fill in the form there. You will not get an EventEase ticket for these events, so ask the organizer how they confirm your registration.</p>
                    </x-faq-item>

                    <x-faq-item question="Can I add the event to my calendar?">
                        <p>Yes. On the thank-you page that you see after registering, click <strong>Add to calendar</strong>. It downloads a calendar file that you can open in Google Calendar, Apple Calendar, or Outlook.</p>
                    </x-faq-item>
                </div>
            </section>

            {{-- ORGANIZERS --}}
            <section x-show="tab === 'all' || tab === 'organizers'" aria-labelledby="faq-organizers"
                     class="rounded-xl bg-white px-6 pb-1 pt-5 shadow">
                <h2 id="faq-organizers" class="text-xl font-bold text-slate-900">For organizers</h2>

                <div class="mt-2">
                    <x-faq-item question="How do I become an organizer?">
                        <p>Create an account and choose the type <strong>Event Organizer</strong>. An admin must approve it before you can create events. Until then you see a "waiting for approval" page. Click <strong>Check status again</strong> to see if you are approved.</p>
                    </x-faq-item>

                    <x-faq-item question="How do I create an event?">
                        <p>After your account is approved, click <strong>New event</strong>. Enter the name, type, date, time, place, number of slots, poster, and the registration deadline. Then design your registration form. An admin reviews every new event before it becomes public.</p>
                    </x-faq-item>

                    <x-faq-item question="Why can't people see my event?">
                        <p>New events are reviewed by an admin. Until the event is approved, only you and the admins can see it. The badge on your event shows <strong>Waiting for approval</strong>, <strong>Approved</strong>, or <strong>Declined</strong>.</p>
                    </x-faq-item>

                    <x-faq-item question="My event was declined. What now?">
                        <p>Open the event and read the reason from the admin. Click <strong>Edit details</strong>, fix the problem, and save. The event goes back to the admin for review automatically.</p>
                    </x-faq-item>

                    <x-faq-item question="How do I design the registration form?">
                        <p>Open your event and click <strong>Form designer</strong>. You can use the EventEase form, where you drag and drop blocks such as short answer, dropdown, and checkboxes, and choose colors and fonts with a live preview. Or you can link an outside form, such as Google Forms. Fun runs can also have categories (42K, 21K, 10K) with their own slots and prices.</p>
                    </x-faq-item>

                    <x-faq-item question="How do I check people in at the event?">
                        <p>Open your event and click <strong>Check-in</strong>. Scan the QR code with the camera, or type the ticket code. A green card means checked in, an amber card means the ticket was already used, and a red card means the ticket was not found. You can undo a check-in from the list.</p>
                    </x-faq-item>

                    <x-faq-item question="Can I see or download the list of participants?">
                        <p>Yes. The event page shows your participants, their answers, and a <strong>Download CSV</strong> button. The <strong>Reports</strong> page shows registrations per day and attendance, and you can export it or print it as a PDF.</p>
                    </x-faq-item>

                    <x-faq-item question="How do I set a registration deadline?">
                        <p>Open <strong>Edit details</strong> and use the registration deadline field. The buttons <strong>+1 day</strong> and <strong>+1 week</strong> help you move it, and <strong>Remove deadline</strong> clears it. Without a deadline, registration closes when the event starts.</p>
                    </x-faq-item>

                    <x-faq-item question="Can I take payments through EventEase?">
                        <p>No. EventEase does not process payments. If you charge a fee, arrange it with participants outside EventEase.</p>
                    </x-faq-item>
                </div>
            </section>

            {{-- ACCOUNT AND PRIVACY --}}
            <section x-show="tab === 'all' || tab === 'account'" aria-labelledby="faq-account"
                     class="rounded-xl bg-white px-6 pb-1 pt-5 shadow">
                <h2 id="faq-account" class="text-xl font-bold text-slate-900">Account and privacy</h2>

                <div class="mt-2">
                    <x-faq-item question="How do I change my name, email, or password?">
                        <p>Click your name at the top right, then <strong>Profile</strong>. You can change them there.</p>
                    </x-faq-item>

                    <x-faq-item question="I forgot my password.">
                        <p>Click <strong>Forgot your password?</strong> on the login page and follow the steps. If you do not get an email, contact the EventEase team.</p>
                    </x-faq-item>

                    <x-faq-item question="How do I delete my account?">
                        <p>Open <strong>Profile</strong> and use <strong>Delete account</strong> at the bottom. Your registrations are deleted too. If you are an organizer, your events and their registrations are deleted as well. This cannot be undone.</p>
                    </x-faq-item>

                    <x-faq-item question="Who can see my information?">
                        <p>The organizer of an event you register for can see your name, email, and answers. Admins can see accounts and registrations so they can run the site. Your registration is not public and other participants cannot see it. Read the <a href="{{ route('privacy') }}">Privacy Policy</a> for details.</p>
                    </x-faq-item>

                    <x-faq-item question="What is an admin?">
                        <p>Admins look after EventEase. They approve organizer accounts and events, and they can remove content or accounts that break the <a href="{{ route('terms') }}">Terms and Conditions</a>.</p>
                    </x-faq-item>
                </div>
            </section>

            {{-- TROUBLESHOOTING --}}
            <section x-show="tab === 'all' || tab === 'help'" aria-labelledby="faq-help"
                     class="rounded-xl bg-white px-6 pb-1 pt-5 shadow">
                <h2 id="faq-help" class="text-xl font-bold text-slate-900">Troubleshooting</h2>

                <div class="mt-2">
                    <x-faq-item question="I see “This page expired”.">
                        <p>The page was open for too long, or your login ended. Go back, refresh the page, and send the form again. Nothing was saved.</p>
                    </x-faq-item>

                    <x-faq-item question="The camera does not work on the check-in page.">
                        <p>Allow the camera in your browser (click the lock icon next to the address bar). Browsers only allow the camera on secure pages (https) or on <em>localhost</em>, so if you open the site by an IP address over Wi-Fi, the camera may be blocked. In that case, type the ticket code instead.</p>
                    </x-faq-item>

                    <x-faq-item question="A QR code will not scan.">
                        <p>Turn the screen brightness up, hold the phone steady, and keep the whole code in the frame. You can always type the ticket code that is printed under the QR code.</p>
                    </x-faq-item>

                    <x-faq-item question="I see “Page not found”.">
                        <p>The address may be wrong, or the event was removed or is not public yet. Go back to the <a href="{{ route('home') }}">home page</a> and look for the event there.</p>
                    </x-faq-item>
                </div>
            </section>
        </div>

        {{-- Still need help --}}
        <div class="mt-8 rounded-xl bg-white p-6 text-center shadow">
            <h2 class="text-lg font-semibold text-slate-900">Still need help?</h2>
            <p class="mt-1 text-gray-800">
                Write to {{ config('legal.contact_name') }} at
                <a href="mailto:{{ config('legal.contact_email') }}" class="text-indigo-800 underline">{{ config('legal.contact_email') }}</a>.
            </p>
            <p class="mt-2 text-sm text-gray-700">
                You can also read the <a href="{{ route('terms') }}" class="text-indigo-800 underline">Terms and Conditions</a>
                and the <a href="{{ route('privacy') }}" class="text-indigo-800 underline">Privacy Policy</a>.
            </p>
        </div>
    </div>
</x-public-layout>