# Student Hub project requirements

## Problem and scope

Student Hub provides one place for students, faculty, and administrators to reach course information, assignments, results, timetables, events, registrations, and basic administration tools. The project uses HTML, CSS, JavaScript, PHP, JSON files, and MySQL.

## Roles

- **Student:** view the student dashboard and academic pages; create a student account.
- **Faculty:** open the faculty dashboard and course/timetable pages.
- **Admin:** view totals and recent registrations; manage students and events.
- **Visitor:** view public pages, send a contact message, or register/sign in.

## Main modules and requirements

1. The project contains more than ten pages covering Home, About, Login, Dashboard, Registration, Events, Contact, faculty, courses, assignments, results, and timetable. FAQs are available in the Events data view.
2. Pages use headings, semantic page regions, form labels, image alternatives, and keyboard-operable links and controls.
3. The interface adapts to mobile, tablet, and desktop widths.
4. JavaScript adds the menu, theme preference, event slider, notice, modal, FAQ collapse, validation, and JSON views.
5. PHP validates submitted contact and registration data; contact messages are stored in JSON.
6. MySQL stores users, students, events, registrations, and audit entries.
7. Login uses password hashes and sessions; role checks protect student/admin pages.
8. Student and event records can be managed with prepared statements. Event posters have type and size checks.
9. The events JSON API supports asynchronous list, add, update, and delete operations.

## Navigation flow

Home links to public pages and login. Login redirects to the dashboard for the signed-in role. Student, faculty, and admin dashboards link to only their relevant pages. The sitemap is in `sitemap.md`.

## URL and browser notes

A URL has a scheme (such as `https`), host (domain), optional port, path, query string, and fragment. The browser requests the resource from the server, receives HTML, parses it into the DOM, loads linked CSS/JavaScript/images, applies styles, runs scripts, and paints the page. Page links use relative paths within this project.

## GitHub workflow

Keep practical work in separate, descriptive commits and push the project to the configured GitHub repository after reviewing the files. PHP pages require a PHP-capable host; GitHub Pages alone cannot run them.
