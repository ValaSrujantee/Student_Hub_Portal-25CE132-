# Practical verification checklist

1. **Planning:** review the scope, role list, sitemap, wireframe, and GitHub remote.
2. **Semantic pages:** open the HTML pages and check headings, page regions, form labels, image alternatives, and navigation by keyboard.
3. **Responsive UI:** inspect Home, About, Registration, Dashboard, and Events at mobile, tablet, and desktop widths.
4. **JavaScript UI:** open/close the hamburger menu, dismiss the notice, move the event slider, open/close an event modal, expand a FAQ, and switch themes across page reloads.
5. **Registration validation:** submit blank/invalid details, then submit a valid sample; check field messages and password strength.
6. **External JSON:** serve the project over HTTP; switch Events/Students/FAQs, search, filter, sort, and change pages. Stop the server or request a missing JSON file to view the error state.
7. **PHP file processing:** send a valid and invalid contact message; confirm only validated messages are appended to `data/contact_messages.json`.
8. **Database setup:** import `database/schema.sql`, run `database/seed.php`, and open `db_test.php`.
9. **Secure registration:** create a new account; retry with the same email and confirm the duplicate message. Confirm passwords are stored as hashes.
10. **Sessions and roles:** sign in as the demo admin and student; check role redirects, logout, timeout, and a forbidden admin page as a student.
11. **Student CRUD:** add, search, filter, edit, and confirm a deletion. The schema seeds 20 students.
12. **Event CRUD:** add/update/delete an event; try an unsupported file, a file over 2 MB, and an allowed image.
13. **JSON API:** use the admin API page to list, add, update, and delete an event without reloading the page; check loading and error messages.
14. **Admin dashboard:** check counts, latest registrations, role-specific menu, and audit records after admin changes.
15. **Integration/deployment:** follow the XAMPP instructions in `README.md`, open the local site over HTTP, and review the changed files before GitHub submission.
