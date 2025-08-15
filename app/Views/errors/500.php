<?php
// $error may be provided only when debug is true
?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-9 text-center">
      <div class="p-4 card-soft">
        <h1 class="display-5">Server error</h1>
        <p class="lead">Something went wrong on the server.</p>
        <?php if (!empty($error)): ?>
          <pre class="text-start small bg-light p-2 rounded"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></pre>
        <?php else: ?>
          <p class="text-muted">Try again later or check logs.</p>
        <?php endif; ?>
        <a href="<?= $baseUrl; ?>/dashboard" class="btn btn-primary">Back to Dashboard</a>
      </div>
    </div>
  </div>
</div>
