<?php
// $user is provided by controller
?>
<div class="container">
  <div class="row mb-3">
    <div class="col">
      <h1 class="h3 mb-1 fw-bold">Dashboard</h1>
      <p class="text-muted mb-0">Welcome back, <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?>!</p>
    </div>
  </div>

  <div class="row g-3 mb-4" id="stats-row">
    <div class="col-12 col-md-4">
      <div class="card card-soft p-3 h-100" style="background: linear-gradient(135deg, #1E293B 0%, #334155 100%); border-left: 4px solid #60A5FA;">
        <div class="d-flex align-items-center h-100">
          <div class="me-3 fs-1" style="color: #93C5FD;">
            <i class="bi bi-people-fill"></i>
          </div>
          <div>
            <div class="small fw-semibold text-uppercase" style="color: #CBD5E1; opacity: 0.85;">Total Students</div>
            <div id="totalStudents" class="stat-number" style="color: #F1F5F9; font-size: 2rem;">—</div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-4">
      <div class="card card-soft p-3 h-100" style="background: linear-gradient(135deg, #064E3B 0%, #065F46 100%); border-left: 4px solid #34D399;">
        <div class="d-flex align-items-center h-100">
          <div class="me-3 fs-1" style="color: #6EE7B7;">
            <i class="bi bi-journal-bookmark"></i>
          </div>
          <div>
            <div class="small fw-semibold text-uppercase" style="color: #A7F3D0; opacity: 0.85;">Total Classes</div>
            <div id="totalClasses" class="stat-number" style="color: #ECFDF5; font-size: 2rem;">—</div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-4">
      <div class="card card-soft p-3 h-100" style="background: linear-gradient(135deg, #78350F 0%, #92400E 100%); border-left: 4px solid #FBBF24;">
        <div class="d-flex align-items-center h-100">
          <div class="me-3 fs-1" style="color: #FCD34D;">
            <i class="bi bi-diagram-3"></i>
          </div>
          <div>
            <div class="small fw-semibold text-uppercase" style="color: #FED7AA; opacity: 0.85;">Total Sections</div>
            <div id="totalSections" class="stat-number" style="color: #FFFBEB; font-size: 2rem;">—</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-12 col-lg-6">
      <div class="card card-soft p-3 h-100">
        <div class="card-body">
          <h5 class="card-title mb-3 fw-semibold"><i class="bi bi-pie-chart-fill me-2 text-primary"></i>Student Category Distribution</h5>
          <div style="height:300px;">
            <canvas id="categoryChart"></canvas>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="card card-soft p-3 h-100">
        <div class="card-body">
          <h5 class="card-title mb-3 fw-semibold"><i class="bi bi-lightning-charge-fill me-2" style="color: #F59E0B;"></i>Quick Actions</h5>
          <p class="text-muted small">Manage student records, import data, and perform administrative tasks.</p>
          <div class="d-grid gap-2">
            <a href="<?= $baseUrl; ?>/students/create" class="btn btn-primary">
              <i class="bi bi-plus-lg me-2"></i>Add Student Record
            </a>
            <a href="<?= $baseUrl; ?>/students" class="btn btn-outline-primary">
              <i class="bi bi-list-ul me-2"></i>View All Students
            </a>
            <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#searchModal">
              <i class="bi bi-search me-2"></i>Search Students
            </button>
            <div class="row g-2 mt-1">
              <div class="col-6">
                <a href="<?= $baseUrl; ?>/students/import" class="btn btn-info w-100">
                  <i class="bi bi-upload me-1"></i>Import CSV
                </a>
              </div>
              <div class="col-6">
                <a href="<?= $baseUrl; ?>/students/export?all=1" class="btn btn-success w-100">
                  <i class="bi bi-download me-1"></i>Export CSV
                </a>
              </div>
            </div>
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
