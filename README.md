# Student Hub

Student Hub is a basic student portal project for the ITUE203 Web Development Frameworks practicals. It contains responsive HTML/CSS pages, frontend JavaScript, PHP form processing, MySQL database exercises, session login, CRUD pages, and a small JSON API.

GitHub repository: [ValaSrujantee/WDF_25CE132](https://github.com/ValaSrujantee/WDF_25CE132)

## Pages and roles

- **Public:** Home, About, Events, Registration, Secure Registration, Login, and Contact.
- **Student:** Dashboard, Courses, Assignments, Results, Timetable, and Events.
- **Faculty:** Dashboard and the faculty/course/timetable pages.
- **Admin:** Statistics dashboard, student management, event management, API CRUD, and audit log view.

See [sitemap.md](sitemap.md), [docs/project_requirements.md](docs/project_requirements.md), and [docs/wireframe.md](docs/wireframe.md) for the project plan and navigation.

## Run locally with XAMPP

1. Start Apache and MySQL in the XAMPP Control Panel.
2. Put this folder under `C:\xampp\htdocs\StudentHub` (or use PHP's local server from this folder).
3. Import `database/schema.sql` in phpMyAdmin. It creates the `studenthub` database and seeds 20 student rows, 15 event rows, and sample registrations.
4. Check the database values in `config.php`. The defaults match a local XAMPP install (`root`, blank password). Environment variables can override them.
5. Run `C:\xampp\php\php.exe database\seed.php` from this project folder to create the two demonstration accounts. The script prints random passwords for newly created accounts; save them because rerunning the script leaves existing accounts unchanged.
6. Open `http://localhost/StudentHub/` or `http://127.0.0.1:8000/` if using `php -S 127.0.0.1:8000`.

The Contact page writes messages to `data/contact_messages.json`. Give the `data` folder write permission for the PHP process. Event posters are limited to JPG, PNG, or WEBP files up to 2 MB; give `uploads/events` write permission.

## Database and practical files

- `database/schema.sql` contains the MySQL schema and sample data.
- `database/seed.php` creates password-hashed demo users.
- `db_pdo.php` demonstrates PDO connectivity; `db_mysqli.php` is used by the MySQLi practicals.
- `docs/er_model.md` contains the ER model.
- `docs/test_checklist.md` lists the practical checks.
- `screenshots/` contains the wireframe, static-page, responsive-layout, interface-interaction, registration-validation, PHP contact, database connection, role/session, CRUD, and Fetch API screenshots.
- `docs/accessibility_checklist.md` contains the static-page accessibility checklist.

The browser Fetch API pages need HTTP. Do not open `events.html` with a `file://` URL. GitHub Pages serves static files only; the PHP/MySQL practicals need a PHP host or a local server such as XAMPP.

## Project structure

```text
StudentHub/
├── api/                 PHP JSON endpoint
├── database/            MySQL schema, ER model, and demo seed script
├── data/                JSON contact form storage
├── docs/                Requirements, wireframe, and practical checks
├── screenshots/         Practical screenshots
├── uploads/events/      Validated event poster uploads
├── *.html               Static Student Hub pages
├── *.php                Registration, login, dashboard, and CRUD pages
├── *.js                 Frontend interactions and Fetch API client
└── *.css                Shared page styling
```
