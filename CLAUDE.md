# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this repo is

A **Thai-language Research Repository web app** (ระบบคลังงานวิจัย) for วิทยาลัยอาชีวศึกษาร้อยเอ็ด (Roi Et Vocational College), built from a Claude Design prototype. Stack: **vanilla PHP 8.2 + MariaDB 10.4 (PDO), zero Composer dependencies**, served under XAMPP at `http://localhost/rvc.arch/`. All UI text is Thai; years use the Buddhist calendar (2568 = 2025).

The original design prototype is preserved in [project/](project/) (`Research Repository.dc.html` + generated `support.js` runtime) — it is the visual source of truth. The live app recreates it; it does **not** reuse the prototype's `<x-dc>`/React runtime.

## Commands

- **Install / reset database (destructive):** [install.php](install.php) is a **form-based web installer** — open `/rvc.arch/install.php` in a browser to enter DB connection, admin account, app name, and a "seed sample data" toggle; it tests the connection, creates the DB, runs `sql/schema.sql` (drops & recreates tables), **writes `config/config.php` from the entered values**, creates the admin, optionally seeds 4 categories + 4 sample users + 12 research, writes `storage/uploads/sample.pdf`, and drops a `config/installed.lock` marker. CLI fallback `php install.php` uses the existing `config/config.php` (does not overwrite it) and seeds the demo with admin `somchai@rvc.ac.th` / `admin1234`. `run_install()` is the shared core used by both paths; delete/restrict `install.php` after a real deploy.
- **Lint PHP:** `"/c/xampp2/php/php" -l <file>` (CLI binary is `C:\xampp2\php\php.exe`).
- **Run:** Apache + MySQL must be running in XAMPP. MySQL start (if not using the control panel): `C:\xampp2\mysql_start.bat`. App is then at `http://localhost/rvc.arch/`.
- **Local run without Apache:** `php -S 127.0.0.1:8123 <router>` where the router serves real static files and otherwise `require`s `index.php` (base-path detection resolves to `/` automatically). There is no test suite.
- **Admin login (seeded):** `somchai@rvc.ac.th` / `admin1234`.

## Architecture

Front-controller MVC-ish, no framework. Request flow: `.htaccess` rewrites everything except real files to **[index.php](index.php)**, which owns the **route table** (`[method, pattern, [Controller, action], adminOnly?]`; `{id}` captures a numeric segment) and dispatches. `adminOnly` routes call `Auth::requireLogin()` before the controller runs.

- **[app/App.php](app/App.php)** — config loader, base-path/URL detection (`App::url()`, `App::basePath()` — critical because the app lives in the `/rvc.arch` subdirectory, so never hardcode absolute paths), view rendering (`App::render()` wraps a view in `views/layout.php`; `App::partial()` renders to string), and the domain constants `CAT_COLORS`, `CHAPTER_NAMES`, `DEPTS`, `STATUSES`, `AUTHOR_ROLES`.
- **[app/Database.php](app/Database.php)** — shared PDO (DSN `charset=utf8mb4`, exceptions on, real prepares). `serverPdo()` connects without a DB for the installer.
- **[app/Repository.php](app/Repository.php)** — **all** SQL lives here. `decorate()` attaches the view fields the design needs (category name/colour, status badge colours, lead-author initial, short abstract) so public and admin screens stay consistent. `save()` writes a research record + its authors/keywords/chapter rows in one transaction.
- **[app/Auth.php](app/Auth.php)** — session auth against `users.password_hash` (`password_hash`/`password_verify`), regenerates id on login/logout.
- **[app/helpers.php](app/helpers.php)** — globals used everywhere in views: `h()` (escape — every dynamic value in a view must go through it), `url()`, `asset()`, CSRF (`csrf_field()`/`verify_csrf()` — every POST controller calls `verify_csrf()` first), flash messages, and design helpers (`name_initial()`, `category_color()`, `status_meta()`, `role_meta()`).
- **Controllers** ([app/controllers/](app/controllers/)) — `PublicController` (home/search/detail/download), `AuthController`, `AdminController` (dashboard/manage/submit/categories/users). They read `$_GET`/`$_POST`, call the Repository, and either `App::render(...)` or `redirect()`.
- **Views** ([views/](views/)) — plain PHP templates. `layout.php` picks one of three chromes by `$section`: public (`partials/public_header` + `public_footer`), admin (`partials/admin_shell` = sidebar + top bar wrapping the content), or `bare` (login). Inline styles are ported verbatim from the prototype for pixel fidelity; the design **tokens** (`--bg`, `--primary`, `--c1..--c5`, light/dark) live once in [assets/app.css](assets/app.css) on `.app-root[data-theme]`.

### Key behaviours to preserve

- **Publish gating:** only `status = 'เผยแพร่'` research appears on the public site (home/search/detail); admins can view any status. Enforced in `Repository::search()`/`latestPublished()` and `PublicController::detail()`.
- **Per-chapter files:** every research has 9 `research_files` rows (one per `CHAPTER_NAMES` slot), each `is_public` or locked. Downloads go **only** through `/download/{id}` (`PublicController::download`), which enforces public-vs-locked access — uploads are never served directly (denied via `storage/uploads/.htaccess` and the root `.htaccess`).
- **Client JS is enhancement only** ([assets/theme.js](assets/theme.js) theme cycle system→light→dark; [assets/admin.js](assets/admin.js) sidebar collapse, repeatable authors, keyword chips, upload preview). All server actions work without JS via POST forms.
- **Category accent colour** is positional (`CAT_COLORS[index % 5]`), matching the prototype — computed at read time, not stored.

## Gotchas

- **UTF-8 everywhere.** DB is `utf8mb4`; PDO DSN sets the charset. When verifying Thai data from the Windows shell, note that **Git Bash mangles UTF-8 in command-line arguments** — `mysql -e "... 'ไทย' ..."` and `curl -F field=ไทย` corrupt the bytes. Verify by piping a UTF-8 `.sql` file (`mysql db < file.sql`) or via a PHP/HTTP client with literal strings, not argv. The app itself handles browser UTF-8 form posts correctly.
- **Credentials:** [config/config.php](config/config.php) is gitignored (defaults to XAMPP root/no-password). [config/config.sample.php](config/config.sample.php) is the template.
- `install.php` is destructive and unauthenticated — intended for local setup; remove or protect it in any real deployment.
