(() => {
  const apiUrl = "api/events.php";
  const form = document.getElementById("api-event-form");
  const list = document.getElementById("api-event-list");
  const status = document.getElementById("api-status");
  const fields = {
    id: document.getElementById("api-event-id"),
    title: document.getElementById("api-event-title"),
    date: document.getElementById("api-event-date"),
    category: document.getElementById("api-event-category"),
    description: document.getElementById("api-event-description")
  };
  const saveButton = document.getElementById("api-save-button");
  const cancelButton = document.getElementById("api-cancel-button");

  function cell(row, value) {
    const td = document.createElement("td");
    td.textContent = value;
    row.append(td);
  }

  function resetForm() {
    form.reset();
    fields.id.value = "";
    saveButton.textContent = "Add event";
    cancelButton.hidden = true;
  }

  async function request(url, options) {
    const response = await fetch(url, options);
    const result = await response.json();
    if (!response.ok || !result.success) throw new Error(result.message || "Request failed.");
    return result;
  }

  async function loadEvents() {
    status.textContent = "Loading events…";
    list.replaceChildren();
    try {
      const result = await request(apiUrl);
      result.events.forEach((event) => {
        const row = document.createElement("tr");
        cell(row, event.title);
        cell(row, event.event_date);
        cell(row, event.category);
        cell(row, event.description);
        const actions = document.createElement("td");
        const edit = document.createElement("button");
        edit.type = "button";
        edit.textContent = "Edit";
        edit.addEventListener("click", () => {
          fields.id.value = event.id;
          fields.title.value = event.title;
          fields.date.value = event.event_date;
          fields.category.value = event.category;
          fields.description.value = event.description;
          saveButton.textContent = "Save changes";
          cancelButton.hidden = false;
          fields.title.focus();
        });
        const remove = document.createElement("button");
        remove.type = "button";
        remove.textContent = "Delete";
        remove.addEventListener("click", async () => {
          if (!window.confirm("Delete this event?")) return;
          try {
            const result = await request(apiUrl, {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({ action: "delete", id: event.id })
            });
            status.textContent = result.message;
            await loadEvents();
          } catch (error) {
            status.textContent = error.message;
          }
        });
        actions.append(edit, document.createTextNode(" "), remove);
        row.append(actions);
        list.append(row);
      });
      status.textContent = `${result.events.length} events loaded.`;
    } catch (error) {
      status.textContent = `Unable to load events: ${error.message}`;
    }
  }

  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    const action = fields.id.value ? "update" : "create";
    status.textContent = "Saving event…";
    try {
      const result = await request(apiUrl, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          action,
          id: fields.id.value,
          title: fields.title.value.trim(),
          event_date: fields.date.value,
          category: fields.category.value.trim(),
          description: fields.description.value.trim()
        })
      });
      resetForm();
      status.textContent = result.message;
      await loadEvents();
    } catch (error) {
      status.textContent = error.message;
    }
  });

  cancelButton.addEventListener("click", resetForm);
  loadEvents();
})();
