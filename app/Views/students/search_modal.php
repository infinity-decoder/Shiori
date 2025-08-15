<?php
// This file is included in dashboard view. $baseUrl is available there.
// We'll expose CSRF token for JS to use on delete forms.
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
          <input id="searchInput" class="form-control form-control-lg" placeholder="Type name, father name, id, roll, enrollment, CNIC or phone..." autocomplete="off">
        </div>

        <div id="searchResultsArea">
          <div class="table-responsive">
            <table class="table table-hover" id="searchResultsTable">
              <thead class="table-light">
                <tr>
                  <th style="width:70px">ID</th>
                  <th>Name</th>
                  <th>Father</th>
                  <th>Roll</th>
                  <th>Enrollment</th>
                  <th>CNIC</th>
                  <th>Mobile</th>
                  <th>Class</th>
                  <th>Section</th>
                  <th style="width:140px">Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>

          <nav>
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
  const baseUrl = <?= json_encode($baseUrl); ?>;
  const csrfToken = <?= json_encode($csrfToken); ?>;
  const input = document.getElementById('searchInput');
  const tbody = document.querySelector('#searchResultsTable tbody');
  const paginationEl = document.getElementById('searchPagination');
  const modalEl = document.getElementById('searchModal');

  let debounceTimer = null;
  let currentQuery = '';
  let currentPage = 1;
  let lastTotal = 0;
  let perPage = 10;

  function emptyResults() {
    tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4">Type to search...</td></tr>';
    paginationEl.innerHTML = '';
  }

  function noData() {
    tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4">No results found.</td></tr>';
    paginationEl.innerHTML = '';
  }

  function buildRow(item) {
    const tr = document.createElement('tr');

    const tdId = document.createElement('td'); tdId.textContent = item.id; tr.appendChild(tdId);

    const tdName = document.createElement('td'); tdName.textContent = item.student_name; tr.appendChild(tdName);
    const tdFather = document.createElement('td'); tdFather.textContent = item.father_name; tr.appendChild(tdFather);
    const tdRoll = document.createElement('td'); tdRoll.textContent = item.roll_no; tr.appendChild(tdRoll);
    const tdEnroll = document.createElement('td'); tdEnroll.textContent = item.enrollment_no; tr.appendChild(tdEnroll);
    const tdCnic = document.createElement('td'); tdCnic.textContent = item.cnic; tr.appendChild(tdCnic);
    const tdMobile = document.createElement('td'); tdMobile.textContent = item.mobile; tr.appendChild(tdMobile);
    const tdClass = document.createElement('td'); tdClass.textContent = item.class_name; tr.appendChild(tdClass);
    const tdSection = document.createElement('td'); tdSection.textContent = item.section_name; tr.appendChild(tdSection);

    const tdActions = document.createElement('td');

    // view
    const aView = document.createElement('a');
    aView.className = 'btn btn-sm btn-outline-primary me-1';
    aView.href = item.view_url;
    aView.title = 'View';
    aView.innerHTML = '<i class="bi bi-eye"></i>';
    tdActions.appendChild(aView);

    // edit
    const aEdit = document.createElement('a');
    aEdit.className = 'btn btn-sm btn-outline-secondary me-1';
    aEdit.href = item.edit_url;
    aEdit.title = 'Edit';
    aEdit.innerHTML = '<i class="bi bi-pencil"></i>';
    tdActions.appendChild(aEdit);

    // delete form (submits POST to delete endpoint)
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = item.delete_url;
    form.className = 'd-inline-block';
    form.style.margin = '0';

    const inputCsrf = document.createElement('input');
    inputCsrf.type = 'hidden';
    inputCsrf.name = 'csrf_token';
    inputCsrf.value = csrfToken;
    form.appendChild(inputCsrf);

    const btnDelete = document.createElement('button');
    btnDelete.type = 'button';
    btnDelete.className = 'btn btn-sm btn-outline-danger btn-delete';
    btnDelete.innerHTML = '<i class="bi bi-trash"></i>';
    form.appendChild(btnDelete);

    tdActions.appendChild(form);
    tr.appendChild(tdActions);

    // delete handler
    btnDelete.addEventListener('click', function (e) {
      e.preventDefault();
      Swal.fire({
        title: 'Delete record?',
        text: 'This action cannot be undone.',
