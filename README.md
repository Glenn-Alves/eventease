# EventEase

EventEase is an event registration and management system. Organizers create events and design their own registration form. Participants browse events and register online.

Built with **Laravel 13** (PHP), **Tailwind CSS**, **Alpine.js**, and **MySQL / MariaDB**.

---

## Features

**Anyone (not logged in)**
- Browse upcoming events on the home page and filter them by type (Concert, Fun Run, Festival, Others)
- Read the event details. Registering needs an account.

**Participants**
- Dashboard with event boxes and a menu by event type
- Register with the organizer's own form page and get a ticket code
- "My Registrations" page with a ticket for every event

**Organizers**
- Dashboard with numbers (events, registrations, slots left) and recent registrations
- Create events with a thumbnail and an event type
- Form designer: drag and drop blocks (section heading, note, short answer, paragraph, number, date, dropdown, multiple choice, checkboxes), pick colors, font and corners, live preview. Or paste a Google Forms link instead.
- Categories with price, slots and deadline (Fun Run events only, for example 42K, 21K, 10K)
- See every participant's answers and download all registrations as a CSV file

---

## What you need installed

| Tool | Version | How to check |
|---|---|---|
| Git | any | `git --version` |
| PHP | **8.3 or higher** | `php -v` |
| Composer | 2.x | `composer -V` |
| Node.js | LTS | `node -v` and `npm -v` |
| MySQL or MariaDB | MySQL 8 or MariaDB 10.6+ | XAMPP includes MariaDB |

PHP extensions needed: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `zip`, `curl`, `xml`, `tokenizer`, `ctype`.
If Composer says an extension is missing, open `php.ini` (XAMPP: `xampp/php/php.ini`), remove the `;` at the start of that `extension=` line, and save.

### Windows

1. **XAMPP**: download from https://www.apachefriends.org. Choose a version with **PHP 8.3 or higher**.
2. **PHP in PATH**: Start menu, search "environment variables", open **Edit the system environment variables**, **Environment Variables**, select **Path**, **Edit**, **New**, and add your PHP folder (for example `C:\xampp\php`).
3. **Composer**: https://getcomposer.org (`Composer-Setup.exe`). When it asks for PHP, choose XAMPP's `php.exe`.
4. **Node.js LTS**: https://nodejs.org (keep **Add to PATH** on).
5. **Git**: https://git-scm.com

Close all terminals, open a **new** Command Prompt, and run the checks from the table above.

If Laragon is installed on the same PC, close it completely first (tray icon, **Exit**). Laragon and XAMPP use the same ports (80, 443, 3306) and cannot run together.

### Linux (Ubuntu, Debian, Linux Mint)

```bash
sudo apt update
sudo apt install -y git curl unzip
sudo apt install -y php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-bcmath php8.3-intl
php -v
```

If `php8.3-cli` is not found, add the PHP repository first:

```bash
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update
```

Composer:

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer -V
```

Node.js (with nvm):

```bash
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.1/install.sh | bash
```

Close the terminal, open a new one, then:

```bash
nvm install --lts
node -v
```

Database: install MariaDB (see step 3 below) or XAMPP for Linux from https://www.apachefriends.org.

---

## First-time setup

### 1. Get the code

```bash
git clone https://github.com/Glenn-Alves/eventease.git
cd eventease
```

The repository is private. The owner must add you first: GitHub repository, **Settings**, **Collaborators**, **Add people**.
When Git asks for a password, use a **Personal Access Token**, not your GitHub password. Create one at GitHub, **Settings**, **Developer settings**, **Personal access tokens**, **Tokens (classic)**, with the **repo** permission. To stop Git from asking every time, run `git config --global credential.helper store`. Never share your token.

### 2. Install the packages

```bash
composer install
npm install
```

### 3. Start the database and create an empty database named `eventease`

**Windows with XAMPP**
1. Open the XAMPP Control Panel and click **Start** next to **MySQL** (and **Apache**, for phpMyAdmin).
2. Open http://localhost/phpmyadmin, click **New**, name it `eventease`, choose collation `utf8mb4_unicode_ci`, and click **Create**.

**Linux with XAMPP**

```bash
sudo /opt/lampp/lampp startmysql
sudo /opt/lampp/bin/mysql -u root -e "CREATE DATABASE eventease CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

**Linux with MariaDB from apt**

```bash
sudo apt install -y mariadb-server
sudo systemctl start mariadb
sudo mariadb
```

Then paste this in the MariaDB prompt:

```sql
CREATE DATABASE eventease CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'eventease'@'localhost' IDENTIFIED BY 'eventease123';
CREATE USER 'eventease'@'127.0.0.1' IDENTIFIED BY 'eventease123';
GRANT ALL PRIVILEGES ON eventease.* TO 'eventease'@'localhost';
GRANT ALL PRIVILEGES ON eventease.* TO 'eventease'@'127.0.0.1';
FLUSH PRIVILEGES;
EXIT;
```

### 4. Create the `.env` file

Windows: `copy .env.example .env`
Linux: `cp .env.example .env`

Then:

```bash
php artisan key:generate
```

Open `.env` in a text editor and set these lines (remove the `#` in front of the database lines and change `sqlite` to `mysql`):

```
APP_NAME=EventEase
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eventease
DB_USERNAME=root
DB_PASSWORD=
```

On Linux with MariaDB from apt, use `DB_USERNAME=eventease` and `DB_PASSWORD=eventease123` instead.

The `.env` file is private to each computer. It is never uploaded to Git.

### 5. Create the tables and build the site

```bash
php artisan migrate
php artisan storage:link
npm run build
```

On Windows, if `storage:link` shows a permission error, open the terminal with **Run as administrator** and run it again.

### 6. Run it

```bash
php artisan serve
```

Open http://localhost:8000

### 7. Create your accounts

There are no ready-made accounts. Open http://localhost:8000/register and create:

- one **Event Organizer** account (to create events and design forms)
- one **Participant** account (to register for events)

Use a different email for each. Use two browsers, or a private window, to be logged in as both at the same time.

---

## Running it every day

1. Start the database (XAMPP Control Panel, **Start** MySQL. Linux: `sudo /opt/lampp/lampp startmysql` or `sudo systemctl start mariadb`).
2. In the project folder: `php artisan serve`
3. Open http://localhost:8000

Only when you are changing the design (CSS or the form designer), run this in a second terminal and keep it open:

```bash
npm run dev
```

Otherwise `npm run build` (run once after pulling changes) is enough.

---

## Share the same sample data (optional)

The database is **not** in Git, so everyone starts empty. To share events and accounts, one person exports the database and sends the file (USB, Drive, or chat):

```bash
# Windows (Command Prompt, not PowerShell). Change the path if XAMPP is somewhere else.
C:\xampp\mysql\bin\mysqldump.exe -u root eventease > eventease.sql

# Linux with XAMPP
/opt/lampp/bin/mysqldump -u root eventease > eventease.sql

# Linux with MariaDB from apt
mysqldump -u eventease -p eventease > eventease.sql
```

The other person imports it into an empty `eventease` database, and does **not** run `php artisan migrate` afterwards:

```bash
# Windows (Command Prompt)
C:\xampp\mysql\bin\mysql.exe -u root eventease < eventease.sql

# Linux with XAMPP
/opt/lampp/bin/mysql -u root eventease < eventease.sql

# Linux with MariaDB from apt
mysql -u eventease -p eventease < eventease.sql
```

Event thumbnails are files, not database rows. To share them, also copy the folder `storage/app/public/events` to the same place on the other computer.

Do not upload a database file to GitHub if it has real people's data.

---

## Working together with Git

Before you start working:

```bash
git pull
```

After you finish something that works:

```bash
git add .
git commit -m "Say what you changed"
git push
```

Rules:

- Never commit `.env`. It is already ignored.
- Never use `git push --force`.
- If `git push` says `rejected (fetch first)`, run `git pull`, fix any conflict, then push again.
- After a `git pull`, run these when something changed: `composer install`, `npm install`, `php artisan migrate`, `npm run build`.
- To avoid editing the same file as a teammate, work on your own branch and open a Pull Request on GitHub:

```bash
git switch -c feature/your-feature-name
git push -u origin feature/your-feature-name
```

---

## Project map

| Path | What is in it |
|---|---|
| `routes/web.php` | All page addresses |
| `app/Http/Controllers/Organizer/` | Organizer pages: dashboard, events, form designer, registrations and CSV |
| `app/Http/Controllers/Participant/` | Participant pages: dashboard, event page, registration form, My Registrations |
| `app/Http/Controllers/PublicEventController.php` | The public events page (no login) |
| `app/Http/Middleware/RoleMiddleware.php` | Keeps organizer and participant pages apart |
| `app/Models/` | `Event`, `EventCategory`, `EventField`, `Registration`, `RegistrationAnswer`, `User` |
| `database/migrations/` | The database tables |
| `resources/views/` | The pages (Blade): `organizer/`, `participant/`, `public/`, `auth/`, `layouts/`, `components/` |
| `resources/js/app.js` | Alpine.js and the drag and drop plugin |
| `public/` | Favicon and logo files |

---

## Troubleshooting

| Problem | Fix |
|---|---|
| `php` or `composer` or `node` is not recognized | Close all terminals and open a new one. Check the PATH. On Windows, do not use the Laragon terminal. |
| Composer says PHP is too old | Laravel 13 needs PHP 8.3 or higher. Install a newer PHP or XAMPP. |
| `could not find driver` | Enable `extension=pdo_mysql` in `php.ini`. |
| `Connection refused` or `SQLSTATE[HY000] [2002]` | The database is not running. Start MySQL. |
| `Access denied for user` | Check `DB_USERNAME` and `DB_PASSWORD` in `.env`. |
| MySQL or Apache will not start in XAMPP | Another program uses the port. Close Laragon completely (tray icon, Exit). |
| The pages have no styling | Run `npm run build` (or keep `npm run dev` open), then press Ctrl+F5. |
| Images do not show | Run `php artisan storage:link`. Thumbnails are not in Git, so upload them again or copy `storage/app/public/events`. |
| `419 Page Expired` | Refresh the page and try again. |
| Weird errors after pulling changes | Run `composer install`, `php artisan migrate`, `php artisan view:clear`, `php artisan route:clear`, `npm run build`. |
| Port 8000 already in use | Close the other terminal, or run `php artisan serve --port=8001`. |