<?php
// Lookups Management View
$title = $title ?? 'Manage Lookups | Shiori';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="bi bi-list-columns-reverse text-primary"></i> Manage Lookups</h1>
            <p class="text-muted">Manage classes, sections, sessions, and categories for your student information system.</p>
        </div>
    </div>

    <?php if ($msg = Auth::getFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($msg = Auth::getFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <ul class="nav nav-tabs mb-4" id="lookupsTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="classes-tab" data-bs-toggle="tab" data-bs-target="#classes" type="button">Classes</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="sections-tab" data-bs-toggle="tab" data-bs-target="#sections" type="button">Sections</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="sessions-tab" data-bs-toggle="tab" data-bs-target="#sessions" type="button">Sessions</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categories" type="button">Categories</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="fcategories-tab" data-bs-toggle="tab" data-bs-target="#fcategories" type="button">Family Categories</button>
        </li>
    </ul>

    <div class="tab-content" id="lookupsTabContent">
        
        <!-- CLASSES TAB -->
        <div class="tab-pane fade show active" id="classes" role="tabpanel">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card card-soft mb-4">
                        <div class="card-header bg-light"><strong>Classes</strong></div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Students</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($classes as $class): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($class['name']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= ($class['is_active'] ?? 1) ? 'success' : 'secondary' ?>"><?= ($class['is_active'] ?? 1) ? 'Active' : 'Inactive' ?></span>
                                            </td>
                                            <td><span class="badge bg-info"><?= $class['student_count'] ?? 0 ?></span></td>
                                            <td>
                                                <form method="POST" action="<?= BASE_URL ?>/lookups/toggle" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                                                    <input type="hidden" name="type" value="class">
                                                    <input type="hidden" name="id" value="<?= $class['id'] ?>">
                                                    <button class="btn btn-sm btn-outline-primary" title="Toggle Status">
                                                        <?= ($class['is_active'] ?? 1) ? '<i class="bi bi-toggle-on"></i>' : '<i class="bi bi-toggle-off"></i>' ?>
                                                    </button>
                                                </form>

                                                <?php if (($class['student_count'] ?? 0) == 0): ?>
                                                    <form method="POST" action="<?= BASE_URL ?>/lookups/classes/delete" class="d-inline" onsubmit="return confirm('Delete this class?');">
                                                        <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                                                        <input type="hidden" name="id" value="<?= $class['id'] ?>">
                                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-muted small">In use</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($classes)): ?>
                                        <tr><td colspan="4" class="text-muted text-center">No classes found</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card card-soft">
                        <div class="card-header bg-primary text-white"><strong>Add New Class</strong></div>
                        <div class="card-body">
                            <form method="POST" action="<?= BASE_URL ?>/lookups/classes/store">
                                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                                <div class="mb-3">
                                   <label class="form-label">Class Name</label>
                                    <input type="text" name="name" class="form-control" placeholder="e.g., Class 1, KG, Nursery" required>
                                    <div class="form-text">Enter class name (e.g., Play Group, Nursery, Class 1-10)</div>
                                </div>
                                <button class="btn btn-primary w-100">Add Class</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTIONS TAB -->
        <div class="tab-pane fade" id="sections" role="tabpanel">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card card-soft mb-4">
                        <div class="card-header bg-light"><strong>Sections</strong></div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Students</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sections as $section): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($section['name']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= ($section['is_active'] ?? 1) ? 'success' : 'secondary' ?>"><?= ($section['is_active'] ?? 1) ? 'Active' : 'Inactive' ?></span>
                                            </td>
                                            <td><span class="badge bg-info"><?= $section['student_count'] ?? 0 ?></span></td>
                                            <td>
                                                <form method="POST" action="<?= BASE_URL ?>/lookups/toggle" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                                                    <input type="hidden" name="type" value="section">
                                                    <input type="hidden" name="id" value="<?= $section['id'] ?>">
                                                    <button class="btn btn-sm btn-outline-primary" title="Toggle Status">
                                                        <?= ($section['is_active'] ?? 1) ? '<i class="bi bi-toggle-on"></i>' : '<i class="bi bi-toggle-off"></i>' ?>
                                                    </button>
                                                </form>

                                                <?php if (($section['student_count'] ?? 0) == 0): ?>
                                                    <form method="POST" action="<?= BASE_URL ?>/lookups/sections/delete" class="d-inline" onsubmit="return confirm('Delete this section?');">
                                                        <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                                                        <input type="hidden" name="id" value="<?= $section['id'] ?>">
                                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-muted small">In use</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($sections)): ?>
                                        <tr><td colspan="4" class="text-muted text-center">No sections found</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card card-soft">
                        <div class="card-header bg-primary text-white"><strong>Add New Section</strong></div>
                        <div class="card-body">
                            <form method="POST" action="<?= BASE_URL ?>/lookups/sections/store">
                                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                                <div class="mb-3">
                                    <label class="form-label">Section Name</label>
                                    <input type="text" name="name" class="form-control" placeholder="e.g., A, B, C" required>
                                    <div class="form-text">Enter section name (e.g., A, B, C, D)</div>
                                </div>
                                <button class="btn btn-primary w-100">Add Section</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SESSIONS TAB -->
        <div class="tab-pane fade" id="sessions" role="tabpanel">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card card-soft mb-4">
                        <div class="card-header bg-light"><strong>Academic Sessions</strong></div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Session Year</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sessions as $session): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($session['session_year']) ?></td>
                                            <td>
                                                <?php if ($session['is_active']): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="POST" action="<?= BASE_URL ?>/lookups/toggle" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                                                    <input type="hidden" name="type" value="session">
                                                    <input type="hidden" name="id" value="<?= $session['id'] ?>">
                                                    <button class="btn btn-sm btn-outline-primary" title="Toggle Status">
                                                        <?= ($session['is_active'] ?? 1) ? '<i class="bi bi-toggle-on"></i>' : '<i class="bi bi-toggle-off"></i>' ?>
                                                    </button>
                                                </form>
                                                
                                                <form method="POST" action="<?= BASE_URL ?>/lookups/sessions/delete" class="d-inline" onsubmit="return confirm('Delete this session? This action cannot be undone.');">
                                                    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                                                    <input type="hidden" name="id" value="<?= $session['id'] ?>">
                                                    <button class="btn btn-sm btn-outline-danger" title="Delete Session"><i class="bi bi-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($sessions)): ?>
                                        <tr><td colspan="3" class="text-muted text-center">No sessions found</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card card-soft">
                        <div class="card-header bg-primary text-white"><strong>Add New Session</strong></div>
                        <div class="card-body">
                            <form method="POST" action="<?= BASE_URL ?>/lookups/sessions/store">
                                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                                <div class="mb-3">
                                    <label class="form-label">Session Year</label>
                                    <input type="text" name="session_year" class="form-control" placeholder="2040-2041" pattern="\d{4}-\d{4}" required>
                                    <div class="form-text">Format: YYYY-YYYY (e.g., 2040-2041)</div>
                                </div>
                                <button class="btn btn-primary w-100">Add Session</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CATEGORIES TAB -->
        <div class="tab-pane fade" id="categories" role="tabpanel">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card card-soft mb-4">
                        <div class="card-header bg-light"><strong>Student Categories</strong></div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Students</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $cat): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($cat['name']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= ($cat['is_active'] ?? 1) ? 'success' : 'secondary' ?>"><?= ($cat['is_active'] ?? 1) ? 'Active' : 'Inactive' ?></span>
                                            </td>
                                            <td><span class="badge bg-info"><?= $cat['student_count'] ?? 0 ?></span></td>
                                            <td>
                                                <form method="POST" action="<?= BASE_URL ?>/lookups/toggle" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                                                    <input type="hidden" name="type" value="category">
                                                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                                    <button class="btn btn-sm btn-outline-primary" title="Toggle Status">
                                                        <?= ($cat['is_active'] ?? 1) ? '<i class="bi bi-toggle-on"></i>' : '<i class="bi bi-toggle-off"></i>' ?>
                                                    </button>
                                                </form>

                                                <?php if (($cat['student_count'] ?? 0) == 0): ?>
                                                    <form method="POST" action="<?= BASE_URL ?>/lookups/categories/delete" class="d-inline" onsubmit="return confirm('Delete this category?');">
                                                        <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                                                        <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-muted small">In use</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($categories)): ?>
                                        <tr><td colspan="4" class="text-muted text-center">No categories found</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card card-soft">
                        <div class="card-header bg-primary text-white"><strong>Add New Category</strong></div>
                        <div class="card-body">
                            <form method="POST" action="<?= BASE_URL ?>/lookups/categories/store">
                                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                                <div class="mb-3">
                                    <label class="form-label">Category Name</label>
                                    <input type="text" name="name" class="form-control" placeholder="e.g., Regular, Special" required>
                                </div>
                                <button class="btn btn-primary w-100">Add Category</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAMILY CATEGORIES TAB -->
        <div class="tab-pane fade" id="fcategories" role="tabpanel">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card card-soft mb-4">
                        <div class="card-header bg-light"><strong>Family Categories</strong></div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Students</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($familyCategories as $fc): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($fc['name']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= ($fc['is_active'] ?? 1) ? 'success' : 'secondary' ?>"><?= ($fc['is_active'] ?? 1) ? 'Active' : 'Inactive' ?></span>
                                            </td>
                                            <td><span class="badge bg-info"><?= $fc['student_count'] ?? 0 ?></span></td>
                                            <td>
                                                <form method="POST" action="<?= BASE_URL ?>/lookups/toggle" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                                                    <input type="hidden" name="type" value="fcategory">
                                                    <input type="hidden" name="id" value="<?= $fc['id'] ?>">
                                                    <button class="btn btn-sm btn-outline-primary" title="Toggle Status">
                                                        <?= ($fc['is_active'] ?? 1) ? '<i class="bi bi-toggle-on"></i>' : '<i class="bi bi-toggle-off"></i>' ?>
                                                    </button>
                                                </form>

                                                <?php if (($fc['student_count'] ?? 0) == 0): ?>
                                                    <form method="POST" action="<?= BASE_URL ?>/lookups/familycategories/delete" class="d-inline" onsubmit="return confirm('Delete this family category?');">
                                                        <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                                                        <input type="hidden" name="id" value="<?= $fc['id'] ?>">
                                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-muted small">In use</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($familyCategories)): ?>
                                        <tr><td colspan="4" class="text-muted text-center">No family categories found</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card card-soft">
                        <div class="card-header bg-primary text-white"><strong>Add New Family Category</strong></div>
                        <div class="card-body">
                            <form method="POST" action="<?= BASE_URL ?>/lookups/familycategories/store">
                                <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
                                <div class="mb-3">
                                    <label class="form-label">Family Category Name</label>
                                    <input type="text" name="name" class="form-control" placeholder="e.g., Civilian, Military" required>
                                </div>
                                <button class="btn btn-primary w-100">Add Family Category</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check for hash in URL to active correct tab
    const hash = window.location.hash;
    if (hash) {
        const triggerEl = document.querySelector(`button[data-bs-target="${hash}"]`);
        if (triggerEl) {
            const tab = new bootstrap.Tab(triggerEl);
            tab.show();
        }
    }
    
    // Update hash when tab changes
    const tabEls = document.querySelectorAll('button[data-bs-toggle="tab"]');
    tabEls.forEach(tabEl => {
        tabEl.addEventListener('shown.bs.tab', event => {
            const target = event.target.getAttribute('data-bs-target');
            if(target) {
                history.replaceState(null, null, target);
            }
        });
    });
});
</script>
