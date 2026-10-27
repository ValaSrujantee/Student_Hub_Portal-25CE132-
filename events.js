(() => {
  const sources = {
    events: { file: "events.json", filter: "category", order: "date" },
    students: { file: "students.json", filter: "course", order: "name" },
    faqs: { file: "faqs.json", filter: "category", order: "question" }
  };
  const pageSize = 5;
  const cache = new Map();
  const elements = {
    type: document.getElementById("data-type"),
    search: document.getElementById("search-records"),
    filter: document.getElementById("filter-records"),
    sort: document.getElementById("sort-records"),
    status: document.getElementById("data-status"),
    records: document.getElementById("records"),
    pageStatus: document.getElementById("page-status"),
    previousPage: document.getElementById("previous-page"),
    nextPage: document.getElementById("next-page"),
    slider: document.getElementById("slider-content"),
    sliderMessage: document.getElementById("slider-message"),
    previousEvent: document.getElementById("previous-event"),
    nextEvent: document.getElementById("next-event"),
    banner: document.getElementById("notice-banner"),
    dismissNotice: document.getElementById("dismiss-notice"),
    modal: document.getElementById("event-modal"),
    modalTitle: document.getElementById("modal-title"),
    modalDescription: document.getElementById("modal-description"),
    closeModal: document.getElementById("close-modal")
  };
  let currentType = "events";
  let records = [];
  let currentPage = 1;
  let featuredEvents = [];
  let featuredIndex = 0;

  function fetchRecords(type) {
    if (!cache.has(type)) {
      const request = fetch(sources[type].file)
        .then((response) => {
          if (!response.ok) throw new Error(`Request failed: ${response.status}`);
          return response.json();
        })
        .then((data) => {
          if (!Array.isArray(data)) throw new Error("The JSON file must contain a list of records.");
          return data;
        })
        .catch((error) => {
          cache.delete(type);
          throw error;
        });
      cache.set(type, request);
    }
    return cache.get(type);
  }

  function setFilterOptions() {
    const field = sources[currentType].filter;
    const values = [...new Set(records.map((record) => record[field]).filter(Boolean))].sort((a, b) => a.localeCompare(b));
    elements.filter.replaceChildren(new Option("All", ""));
    values.forEach((value) => elements.filter.add(new Option(value, value)));
  }

  function filteredRecords() {
    const query = elements.search.value.trim().toLocaleLowerCase();
    const selectedFilter = elements.filter.value;
    const field = sources[currentType].filter;
    const orderField = sources[currentType].order;
    const direction = elements.sort.value === "desc" ? -1 : 1;
    return records
      .filter((record) => !selectedFilter || record[field] === selectedFilter)
      .filter((record) => !query || Object.values(record).join(" ").toLocaleLowerCase().includes(query))
      .sort((a, b) => String(a[orderField]).localeCompare(String(b[orderField]), undefined, { numeric: true }) * direction);
  }

  function addText(parent, tagName, text, className) {
    const node = document.createElement(tagName);
    node.textContent = text;
    if (className) node.className = className;
    parent.append(node);
    return node;
  }

  function createCard(record, index) {
    const card = document.createElement("article");
    card.className = "record-card";

    if (currentType === "events") {
      addText(card, "h3", record.title);
      addText(card, "p", `${record.date} · ${record.category}`);
      addText(card, "p", record.description);
      const details = document.createElement("button");
      details.type = "button";
      details.textContent = "Event details";
      details.addEventListener("click", () => openEvent(record));
      card.append(details);
    } else if (currentType === "students") {
      addText(card, "h3", record.name);
      addText(card, "p", `${record.course} · Year ${record.year}`);
      addText(card, "p", record.profile);
    } else {
      const questionId = `faq-answer-${record.id}-${index}`;
      const question = document.createElement("button");
      question.type = "button";
      question.className = "faq-question";
      question.textContent = record.question;
      question.setAttribute("aria-expanded", "false");
      question.setAttribute("aria-controls", questionId);
      const answer = document.createElement("p");
      answer.className = "faq-answer";
      answer.id = questionId;
      answer.textContent = record.answer;
      answer.hidden = true;
      question.addEventListener("click", () => {
        const isExpanded = question.getAttribute("aria-expanded") === "true";
        question.setAttribute("aria-expanded", String(!isExpanded));
        answer.hidden = isExpanded;
      });
      card.append(question, answer);
      addText(card, "p", record.category);
    }
    return card;
  }

  function renderRecords() {
    const matching = filteredRecords();
    const pageCount = Math.max(1, Math.ceil(matching.length / pageSize));
    currentPage = Math.min(currentPage, pageCount);
    const start = (currentPage - 1) * pageSize;
    elements.records.replaceChildren(...matching.slice(start, start + pageSize).map(createCard));
    elements.status.textContent = `${matching.length} record${matching.length === 1 ? "" : "s"} found.`;
    elements.pageStatus.textContent = `Page ${currentPage} of ${pageCount}`;
    elements.previousPage.disabled = currentPage <= 1;
    elements.nextPage.disabled = currentPage >= pageCount;
  }

  function renderSlider() {
    elements.slider.replaceChildren();
    if (!featuredEvents.length) {
      addText(elements.slider, "p", "No events are available.");
      return;
    }
    const event = featuredEvents[featuredIndex];
    addText(elements.slider, "h3", event.title);
    addText(elements.slider, "p", `${event.date} · ${event.category}`);
    addText(elements.slider, "p", event.description);
  }

  function openEvent(record) {
    elements.modalTitle.textContent = record.title;
    elements.modalDescription.textContent = `${record.date} · ${record.category}. ${record.description}`;
    elements.modal.showModal();
  }

  async function loadCurrentDataset() {
    currentType = elements.type.value;
    currentPage = 1;
    elements.records.replaceChildren();
    elements.status.textContent = `Loading ${currentType}…`;
    try {
      records = await fetchRecords(currentType);
      setFilterOptions();
      renderRecords();
    } catch (error) {
      records = [];
      elements.filter.replaceChildren(new Option("All", ""));
      elements.status.textContent = `Unable to load ${currentType}. Check that ${sources[currentType].file} is available, then select the data again.`;
      elements.records.replaceChildren();
      elements.pageStatus.textContent = "Page 1";
      elements.previousPage.disabled = true;
      elements.nextPage.disabled = true;
    }
  }

  elements.type.addEventListener("change", loadCurrentDataset);
  elements.search.addEventListener("input", () => { currentPage = 1; renderRecords(); });
  elements.filter.addEventListener("change", () => { currentPage = 1; renderRecords(); });
  elements.sort.addEventListener("change", () => { currentPage = 1; renderRecords(); });
  elements.previousPage.addEventListener("click", () => { currentPage -= 1; renderRecords(); });
  elements.nextPage.addEventListener("click", () => { currentPage += 1; renderRecords(); });
  elements.previousEvent.addEventListener("click", () => {
    if (!featuredEvents.length) return;
    featuredIndex = (featuredIndex - 1 + featuredEvents.length) % featuredEvents.length;
    renderSlider();
  });
  elements.nextEvent.addEventListener("click", () => {
    if (!featuredEvents.length) return;
    featuredIndex = (featuredIndex + 1) % featuredEvents.length;
    renderSlider();
  });
  elements.dismissNotice.addEventListener("click", () => elements.banner.remove());
  elements.closeModal.addEventListener("click", () => elements.modal.close());

  fetchRecords("events")
    .then((data) => {
      featuredEvents = data;
      renderSlider();
    })
    .catch(() => {
      elements.slider.replaceChildren();
      addText(elements.slider, "p", "Unable to load featured events. Check that events.json is available.");
    });

  loadCurrentDataset();
})();
