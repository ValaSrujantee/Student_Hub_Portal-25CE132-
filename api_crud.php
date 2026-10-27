<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_role('admin');
$pageTitle = 'Fetch API Event Management';
require __DIR__ . '/site_header.php';
?>
<main class="page-main">
  <h2>Event CRUD with Fetch API</h2>
  <section class="registration-panel" aria-labelledby="api-form-heading">
    <h3 id="api-form-heading">Add an event</h3>
    <form id="api-event-form" class="registration-form">
      <input id="api-event-id" type="hidden">
      <label class="form-field" for="api-event-title">Title<input id="api-event-title" type="text" maxlength="150" required></label>
      <label class="form-field" for="api-event-date">Date<input id="api-event-date" type="date" required></label>
      <label class="form-field" for="api-event-category">Category<input id="api-event-category" type="text" maxlength="80" required></label>
      <label class="form-field" for="api-event-description">Description<textarea id="api-event-description" rows="3" required></textarea></label>
      <button id="api-save-button" type="submit">Add event</button>
      <button id="api-cancel-button" type="button" hidden>Cancel edit</button>
    </form>
  </section>
  <section class="data-browser" aria-labelledby="api-list-heading">
    <h3 id="api-list-heading">Events</h3>
    <p id="api-status" role="status" aria-live="polite">Loading events…</p>
    <div class="table-scroll"><table class="data-table"><thead><tr><th>Title</th><th>Date</th><th>Category</th><th>Description</th><th>Actions</th></tr></thead><tbody id="api-event-list"></tbody></table></div>
  </section>
</main>
<script src="api-crud.js" defer></script>
<?php require __DIR__ . '/site_footer.php'; ?>
