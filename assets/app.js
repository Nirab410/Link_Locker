const $ = (s, p = document) => p.querySelector(s);
const $$ = (s, p = document) => [...p.querySelectorAll(s)];

function openModal() {
  $("#quickModal")?.classList.add("show");
}
function closeModal() {
  $("#quickModal")?.classList.remove("show");
}

function setMode(mode) {
  $$("#quickModal .tab").forEach((t) =>
    t.classList.toggle("active", t.dataset.mode === mode),
  );
  ["link", "upload", "note"].forEach((id) =>
    $("#mode-" + id)?.classList.toggle("hidden", id !== mode),
  );
  $("#type").value =
    mode === "upload" ? "Image" : mode === "note" ? "Note" : "Link";
  renderLivePreview();
}

function extractDomain(url) {
  try {
    return new URL(url).hostname.replace(/^www\./, "");
  } catch (e) {
    return "your-link.com";
  }
}

function guessTitleFromUrl(url) {
  try {
    const u = new URL(url);
    // শুধু hostname নাও যদি path কিছু না থাকে
    const parts = u.pathname.split("/").filter(Boolean);
    if (!parts.length) return u.hostname.replace(/^www\./, "");
    // শেষ path segment নাও
    let slug = parts[parts.length - 1];
    // হ্যাশ/UUID হলে (32+ hex char) তার আগের segment নাও
    if (/^[a-f0-9]{8,}$/i.test(slug.replace(/-/g, ""))) {
      slug = parts[parts.length - 2] || u.hostname.replace(/^www\./, "");
    }
    // slug clean করো
    return (
      decodeURIComponent(slug)
        .replace(/[-_]/g, " ")
        .replace(/\.[a-z]{2,5}$/i, "")
        .trim()
        .slice(0, 55) || u.hostname.replace(/^www\./, "")
    );
  } catch (e) {
    return "Untitled link";
  }
}

function renderLivePreview() {
  if (!$("#type")) return;
  const type = $("#type").value;
  const url = ($("#link_url")?.value || "").trim();
  const domain = extractDomain(url);

  const rawTitle = $("#title")?.value.trim();
  const title =
    rawTitle ||
    (type === "Link"
      ? guessTitleFromUrl(url)
      : type === "Note"
        ? "Quick note"
        : "Untitled item");

  const rawDesc = $("#description")?.value.trim();
  const desc =
    rawDesc ||
    (type === "Link"
      ? domain
      : type === "Note"
        ? $("#note_content")?.value.trim() || ""
        : "Your preview will appear here.");

  $("#pv-title").textContent = title || "Untitled item";
  $("#pv-desc").textContent = desc || "Preview";
  $("#pv-type").textContent = type;
  $("#pv-visibility").textContent = $("#visibility").value;

  const stage = $("#pv-stage");

  if (type === "Link") {
    const favicon = url
      ? `https://www.google.com/s2/favicons?sz=128&domain_url=${encodeURIComponent(url)}`
      : "";
    stage.innerHTML = `
      <div class="preview-inner preview-link">
        <div class="prev-link-top"><div class="small">Link preview</div></div>
        <div class="prev-link-body">
          <div class="prev-favicon-wrap">
            <img src="${favicon}" alt="" class="prev-favicon"
              onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
            <div class="prev-favicon-fallback" style="display:none">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
            </div>
          </div>
          <div class="prev-link-info">
            <div class="prev-link-title">${escHtml(title)}</div>
            <div class="prev-domain">${escHtml(domain)}</div>
          </div>
        </div>
      </div>`;
  } else if (type === "Note") {
    const noteVal = ($("#note_content")?.value || "Write something short…")
      .replace(/</g, "&lt;")
      .slice(0, 240);
    stage.innerHTML = `
      <div class="preview-inner preview-note">
        <div class="prev-note-icon">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          Quick note
        </div>
        <div class="prev-note-text">${noteVal}</div>
      </div>`;
  } else {
    const file = $("#file")?.files?.[0];
    if (file && file.type.startsWith("image/")) {
      const r = new FileReader();
      r.onload = (e) =>
        (stage.innerHTML = `<img src="${e.target.result}" alt="preview" style="width:100%;height:100%;object-fit:cover">`);
      r.readAsDataURL(file);
    } else if (file && file.type === "application/pdf") {
      stage.innerHTML = `
        <div class="preview-inner preview-pdf">
          <div class="prev-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          </div>
          <div class="prev-label">PDF Document</div>
          <div class="prev-filename">${escHtml(file.name)}</div>
        </div>`;
    } else {
      stage.innerHTML = `
        <div class="preview-inner preview-pdf">
          <div class="prev-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
          </div>
          <div class="prev-label muted">Choose an image or PDF</div>
        </div>`;
    }
  }
}

function escHtml(str) {
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

function filterCards() {
  const q = ($("#search")?.value || "").toLowerCase().trim();
  const ft = $("#filterType")?.value || "";
  const fv = $("#filterVis")?.value || "";
  $$(".vault-item").forEach((c) => {
    const hay = (c.dataset.search || "").toLowerCase();
    c.style.display =
      (!q || hay.includes(q)) &&
      (!ft || c.dataset.type === ft) &&
      (!fv || c.dataset.visibility === fv)
        ? ""
        : "none";
  });
}

document.addEventListener("click", (e) => {
  if (e.target.closest("[data-open-modal]")) openModal();
  if (e.target.closest("[data-close-modal]") || e.target.id === "quickModal")
    closeModal();
  if (e.target.matches(".tab")) setMode(e.target.dataset.mode);
});

document.addEventListener("input", (e) => {
  if (
    ["title", "description", "link_url", "note_content", "search"].includes(
      e.target.id,
    )
  ) {
    renderLivePreview();
    filterCards();
  }
});

document.addEventListener("change", (e) => {
  if (["file", "visibility", "filterType", "filterVis"].includes(e.target.id)) {
    renderLivePreview();
    filterCards();
  }
});

window.addEventListener("DOMContentLoaded", () => {
  if ($("#quickModal")) setMode("link");
  filterCards();
});

