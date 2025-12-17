<?php
// app/Views/developer/index.php
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card card-soft shadow-lg border-0 overflow-hidden">
                <div class="row g-0">
                    <!-- Left Column: Profile & Contact -->
                    <div class="col-md-4 text-white p-4 d-flex flex-column align-items-center text-center" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                        <div class="mb-4 mt-3">
                            <div class="rounded-circle bg-white p-1 d-inline-block shadow">
                                <img src="https://profile.infinitydecoder.com/assets/img/me.jpg" 
                                     alt="Profile" 
                                     class="rounded-circle"
                                     style="width: 140px; height: 140px; object-fit: cover;"
                                     onerror="this.src='https://ui-avatars.com/api/?name=Infinity+Decoder&background=random&size=140'">
                            </div>
                        </div>
                        <h2 class="h4 fw-bold mb-1">INFINITY DECODER</h2>
                        <p class="text-white-50 mb-4">Software Developer</p>
                        
                        <div class="w-100 mt-auto">
                            <div class="d-grid gap-2">
                                <a href="https://www.linkedin.com/in/infinitydecoder/" target="_blank" class="btn btn-outline-light btn-sm">
                                    <i class="bi bi-linkedin me-2"></i>LinkedIn
                                </a>
                                <a href="https://infinitydecoder.com" target="_blank" class="btn btn-outline-light btn-sm">
                                    <i class="bi bi-globe me-2"></i>Website
                                </a>
                                <a href="mailto:contact@infinitydecoder.com" class="btn btn-primary btn-sm border-0" style="background-color: #3b82f6;">
                                    <i class="bi bi-envelope me-2"></i>Contact
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Info & Bio -->
                    <div class="col-md-8 p-4 p-lg-5 bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h1 class="h3 fw-bold text-dark mb-0">About Developer</h1>
                            <span class="badge bg-primary rounded-pill">PRO</span>
                        </div>
                        
                        <p class="lead text-muted mb-4">
                            Passionate software developer from Earth 🌎 dedicated to building robust, secure, and user-friendly applications.
                        </p>
                        
                        <div class="mb-4">
                            <h5 class="fw-bold text-secondary text-uppercase small mb-3">Professional Skills</h5>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge bg-light text-dark border">PHP / Wordpress</span>
                                <span class="badge bg-light text-dark border">JavaScript </span>
                                <span class="badge bg-light text-dark border">System Security</span>
                                <span class="badge bg-light text-dark border">Database Architecture</span>
                                <span class="badge bg-light text-dark border">Cyber Security</span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5 class="fw-bold text-secondary text-uppercase small mb-3">Connect</h5>
                            <ul class="list-unstyled text-muted">
                                <li class="mb-2"><i class="bi bi-geo-alt-fill me-2 text-primary"></i> Earth, Solar System</li>
                                <li class="mb-2"><i class="bi bi-link-45deg me-2 text-primary"></i> <a href="https://profile.infinitydecoder.com" class="text-decoration-none text-muted">profile.infinitydecoder.com</a></li>
                                <li class="mb-2"><i class="bi bi-github me-2 text-primary"></i> <a href="https://github.com/infinity-decoder" class="text-decoration-none text-muted">@infinity-decoder</a></li>
                            </ul>
                        </div>

                        <div class="alert alert-light border-start border-4 border-primary bg-light" role="alert">
                            <p class="mb-0 small fst-italic">
                                "Terminal is my battleground & Code flows in my DNA."
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="<?= BASE_URL ?>/dashboard" class="text-muted text-decoration-none small">
                    <i class="bi bi-arrow-left me-1"></i> Return to Application
                </a>
            </div>
        </div>
    </div>
</div>
