<?php
// $user is provided by controller
?>
<div class="container">
  <div class="row mb-3">
    <div class="col">
      <h1 class="h3 mb-0">Dashboard</h1>
      <p class="text-muted mb-0">Welcome back, <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?>.</p>
    </div>
  </div>

  <div class="row g-3 mb-4" id="stats-row">
    <div class="col-12 col-md-4">
      <div class="card card-soft p-3">
        <div class="d-flex align-items-center">
          <div class="me-3 fs-2 text-primary"><i class="bi bi-people-fill"></i></div>
          <div>
            <div class="text-muted small">Total Students</div>
            <div id="totalStudents" class="stat-number">—</div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-4">
      <div class="card card-soft p-3">
        <div class="d-flex align-items-center">
          <div class="me-3 fs-2 text-success"><i class="bi bi-journal-bookmark"></i></div>
          <div>
            <div class="text-muted small">Total Classes</div>
            <div id="totalClasses" class="stat-number">—</div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-4">
      <div class="card card-soft p-3">
        <div class="d-flex align-items-center">
          <div class="me-3 fs-2 text-warning"><i class="bi bi-diagram-3"></i></div>
          <div>
            <div class="text-muted small">Total Sections</div>
            <div id="totalSections" class="stat-number">—</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-12 col-lg-6">
      <div class="card card-soft p-3">
        <div class="card-body">
          <h5 class="card-title mb-3">Category distribution</h5>
          <div style="height:300px;">
            <canvas id="categoryChart"></canvas>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="card card-soft p-3">
        <div class="card-body">
          <h5 class="card-title mb-3">Quick actions</h5>
          <p class="text-muted">Use the cards and controls to add/search student records.</p>
          <div class="d-grid gap-2 d-md-block">
            <a href="<?= $baseUrl; ?>/students/create" class="btn btn-primary me-2"><i class="bi bi-plus-lg me-1"></i> Add Record</a>
            <a href="<?= $baseUrl; ?>/students" class="btn btn-outline-primary me-2"><i class="bi bi-list-ul me-1"></i> List Students</a>

            <!-- Search opens the modal included below -->
            <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#searchModal">
              <i class="bi bi-search me-1"></i> Search
            </button>

            <?php if (Auth::user()['role'] === 'admin'): ?>
              <a href="<?= $baseUrl; ?>/admin/backup" class="btn btn-outline-danger mt-2"><i class="bi bi-hdd-stack me-1"></i> Backup DB</a>
            <?php endif; ?>

            <a href="<?= $baseUrl; ?>/students/export?all=1" class="btn btn-outline-success mt-2"><i class="bi bi-file-earmark-arrow-down me-1"></i> Export CSV</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- include search modal markup (server renders modal into the page) -->
<?php require BASE_PATH . '/app/Views/students/search_modal.php'; ?>

<script>
  (function () {
    const baseUrl = <?= json_encode($baseUrl); ?>;
    const elTotalStudents = document.getElementById('totalStudents');
    const elTotalClasses  = document.getElementById('totalClasses');
    const elTotalSections = document.getElementById('totalSections');
    const ctx = document.getElementById('categoryChart').getContext('2d');

    let chart = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Loading...'],
        datasets: [{
          data: [1],
          backgroundColor: ['#6b7280'],
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'bottom' }
        }
      }
    });

    async function loadStats() {
      try {
        const res = await fetch(baseUrl + '/api/stats', { credentials: 'same-origin' });
        if (!res.ok) {
          if (res.status === 401) {
            window.location = baseUrl + '/login';
            return;
          }
          throw new Error('Failed to fetch stats: ' + res.status);
        }
        const data = await res.json();

        elTotalStudents.textContent = data.total_students ?? '0';
        elTotalClasses.textContent  = data.total_classes ?? '0';
        elTotalSections.textContent = data.total_sections ?? '0';

        const labels = (data.categories || []).map(c => c.name);
        const values = (data.categories || []).map(c => c.count);

        const palette = [
          '#6366f1', '#06b6d4', '#f59e0b', '#ef4444', '#10b981',
          '#8b5cf6', '#f97316', '#06b6d4', '#ef4444'
        ];

        chart.data.labels = labels.length ? labels : ['No data'];
        chart.data.datasets = [{
          data: values.length ? values : [1],
          backgroundColor: labels.map((_, i) => palette[i % palette.length]),
        }];
        chart.update();

      } catch (err) {
        console.error(err);
        Swal.fire({
          icon: 'error',
          title: 'Could not load dashboard data',
          text: String(err)
        });
      }
    }

    loadStats();
  })();
</script>
