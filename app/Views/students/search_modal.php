<?php
// app/Views/students/search_modal.php
// Included by dashboard and students list views.
// Exposes a CSRF token for delete actions rendered by JS inside the modal.
$csrfToken = CSRF::token();
?>

<!-- Search Modal -->
<div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="searchModalLabel"><i class="bi bi-search"></i> Search Students</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <input
            id="searchInput"
            class="form-control form-control-lg"
            placeholder="Type name, father name, id, roll, enrollment, CNIC or phone."
            autocomplete="off">
        </div>

        <div id="searchResultsArea">
          <div class="table-responsive">
            <table class="table table-hover" id="searchResultsTable">
              <thead class="table-light">
                <tr>
                  <th style="width:60px">Photo</th>
                  <th>Name</th>
                  <th>Father</th>
                  <th>Roll</th>
                  <th>Enrollment</th>
                  <th>B.Form</th>
                  <th>Mobile</th>
                  <th>Class</th>
                  <th>Section</th>
                  <th style="width:140px">Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>

          <nav aria-label="Search pagination">
            <ul class="pagination" id="searchPagination"></ul>
          </nav>
        </div>
      </div>

      <div class="modal-footer">
        <small class="text-muted me-auto">Quick search across ID, names and identifiers.</small>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const baseUrl   = <?= json_encode($baseUrl); ?>;
  const csrfToken = <?= json_encode($csrfToken); ?>;

  const input        = document.getElementById('searchInput');
  const tbody        = document.querySelector('#searchResultsTable tbody');
  const paginationEl = document.getElementById('searchPagination');
  const modalEl      = document.getElementById('searchModal');

  let debounceTimer = null;
  let currentQuery  = '';
  let currentPage   = 1;
  let perPage       = 10;
  let lastTotal     = 0;

  function showTypingHint() {
    tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4">Type to search.</td></tr>';
    paginationEl.innerHTML = '';
  }

  function showNoResults() {
    tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4">No results found.</td></tr>';
    paginationEl.innerHTML = '';
  }

  function td(text) {
    const cell = document.createElement('td');
    cell.textContent = text ?? '';
    return cell;
  }
  
  function tdHtml(html) {
    const cell = document.createElement('td');
    cell.innerHTML = html ?? '';
    return cell;
  }

  function buildActionsCell(item) {
    const tdActions = document.createElement('td');

    const btnView = document.createElement('a');
    btnView.href = item.view_url;
    btnView.className = 'btn btn-sm btn-outline-primary me-1';
    btnView.innerHTML = '<i class="bi bi-eye"></i>';
    tdActions.appendChild(btnView);

    const btnEdit = document.createElement('a');
    btnEdit.href = item.edit_url;
    btnEdit.className = 'btn btn-sm btn-outline-secondary me-1';
    btnEdit.innerHTML = '<i class="bi bi-pencil"></i>';
    tdActions.appendChild(btnEdit);

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = item.delete_url;
    form.className = 'd-inline-block';
    form.style.margin = '0';

    const hidden = document.createElement('input');
    hidden.type  = 'hidden';
    hidden.name  = 'csrf_token';
    hidden.value = csrfToken;
    form.appendChild(hidden);

    const btnDelete = document.createElement('button');
    btnDelete.type = 'button';
    btnDelete.className = 'btn btn-sm btn-outline-danger btn-delete';
    btnDelete.innerHTML = '<i class="bi bi-trash"></i>';
    form.appendChild(btnDelete);

    form.addEventListener('click', function (e) {
      const target = e.target.closest('.btn-delete');
      if (!target) return;
      e.preventDefault();
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          title: 'Delete record?',
          text: 'This action cannot be undone.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, delete',
          cancelButtonText: 'Cancel',
        }).then(res => { if (res.isConfirmed) form.submit(); });
      } else {
        if (confirm('Delete this record?')) form.submit();
      }
    });

    tdActions.appendChild(form);
    return tdActions;
  }

  function renderResults(items) {
    if (!Array.isArray(items) || items.length === 0) {
      showNoResults();
      return;
    }
    tbody.innerHTML = '';
    for (const item of items) {
      const tr = document.createElement('tr');
      // Render photo instead of ID
      const photoHtml = `<img src="${item.photo_url}" alt="photo" style="width:40px;height:40px;object-fit:cover;border-radius:4px;">`;
      tr.appendChild(tdHtml(photoHtml));
      
      tr.appendChild(td(item.student_name));
      tr.appendChild(td(item.father_name));
      tr.appendChild(td(item.roll_no));
      tr.appendChild(td(item.enrollment_no));
      tr.appendChild(td(item.b_form)); // Changed from CNIC to B.Form
      tr.appendChild(td(item.mobile));
      tr.appendChild(td(item.class_name));
      tr.appendChild(td(item.section_name));
      tr.appendChild(buildActionsCell(item));
      tbody.appendChild(tr);
    }
  }

  function buildPagination(total, perPage, page) {
    const totalPages = Math.max(1, Math.ceil(total / Math.max(1, perPage)));
    paginationEl.innerHTML = '';
    if (totalPages <= 1) return;

    const addPage = (p, label, disabled = false, active = false) => {
      const li = document.createElement('li');
      li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
      const a = document.createElement('a');
      a.className = 'page-link';
      a.href = '#';
      a.dataset.page = String(p);
      a.textContent = label;
      li.appendChild(a);
      paginationEl.appendChild(li);
    };

    addPage(page - 1, '« Prev', page <= 1, false);

    const start = Math.max(1, page - 3);
    const end   = Math.min(totalPages, page + 3);
    for (let p = start; p <= end; p++) {
      addPage(p, String(p), false, p === page);
    }

    addPage(page + 1, 'Next »', page >= totalPages, false);
  }

  async function runSearch(pageToLoad = 1) {
    currentPage = pageToLoad;

    if (!currentQuery) {
      showTypingHint();
      return;
    }

    tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4">Searching…</td></tr>';
    paginationEl.innerHTML = '';

    const params = new URLSearchParams({
      q: currentQuery,
      page: String(currentPage),
      per_page: String(perPage),
    });

    try {
      const res = await fetch(baseUrl + '/api/search?' + params.toString(), { credentials: 'same-origin' });
      if (!res.ok) {
        if (res.status === 401) {
          window.location = baseUrl + '/login';
          return;
        }
        throw new Error('Search failed: ' + res.status);
      }
      const data = await res.json();
      lastTotal = data.total ?? 0;
      perPage   = data.per_page ?? perPage;

      renderResults(data.results || []);
      buildPagination(lastTotal, perPage, data.page ?? currentPage);
    } catch (err) {
      tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-danger">Error performing search.</td></tr>';
      console.error(err);
    }
  }

  // Debounced typing
  input.addEventListener('input', function () {
    const q = this.value.trim();
    currentQuery = q;
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      if (!currentQuery) {
        showTypingHint();
      } else {
        runSearch(1);
      }
    }, 250);
  });

  // Pagination click (event delegation)
  paginationEl.addEventListener('click', function (e) {
    const a = e.target.closest('a[data-page]');
    if (!a) return;
    e.preventDefault();
    const page = parseInt(a.dataset.page || '1', 10);
    if (!isNaN(page)) runSearch(page);
  });

  // Modal lifecycle: reset on hide; focus on show
  modalEl.addEventListener('hidden.bs.modal', function () {
    input.value = '';
    currentQuery = '';
    currentPage = 1;
    lastTotal = 0;
    showTypingHint();
  });

  modalEl.addEventListener('shown.bs.modal', function () {
    setTimeout(() => input.focus(), 120);
    showTypingHint();
  });

  // Initialize state in case modal is already in DOM
  showTypingHint();
})();
</script>
