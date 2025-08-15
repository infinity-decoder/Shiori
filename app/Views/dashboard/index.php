<?php
// $user is provided by the controller
?>
<div class="container">
  <div class="row mb-4">
    <div class="col">
      <h1 class="h3 mb-0">Dashboard</h1>
      <p class="text-muted mb-0">Welcome, <?= htmlspecialchars($user['username']); ?>.</p>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card card-soft">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="me-3 fs-2"><i class="bi bi-person-badge"></i></div>
            <div>
              <div class="fw-semibold">You are logged in</div>
              <small class="text-muted">This page is protected by session auth.</small>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- More cards/statistics will come in later milestones -->
  </div>
</div>
