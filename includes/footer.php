    </main> <!-- /page-wrapper -->

    <!-- Back to Top Button -->
    <button type="button" class="btn btn-primary rounded-circle shadow-lg back-to-top-btn" id="backToTopBtn" title="Back to top" aria-label="Back to top">
        <i class="bi bi-chevron-up"></i>
    </button>

    <!-- Professional Footer -->
    <footer class="footer bg-dark text-white pt-5 pb-4 mt-auto">
        <div class="container">
            <div class="row g-4 pb-4 border-bottom border-secondary border-opacity-25">
                <!-- Col 1: Brand -->
                <div class="col-lg-4 col-md-6">
                    <div class="footer-brand d-flex align-items-center mb-3">
                        <span class="brand-icon-wrap me-2">
                            <i class="bi bi-fire"></i>
                        </span>
                        <span class="fs-4 fw-bold text-white">Taste<span class="brand-accent">Book</span></span>
                    </div>
                    <p class="text-secondary small mb-3">
                        TasteBook is a modern interactive Digital Recipe Book platform designed to organize, discover, and celebrate culinary creations with passionate food lovers worldwide.
                    </p>
                </div>

                <!-- Col 2: Quick Links -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="text-uppercase fw-bold text-white mb-3 tracking-wide">Explore</h6>
                    <ul class="list-unstyled footer-links small">
                        <li class="mb-2"><a href="<?php echo $base_path; ?>index.php" class="text-secondary text-decoration-none hover-white"><i class="bi bi-chevron-right me-1 text-primary"></i> Home</a></li>
                        <li class="mb-2"><a href="<?php echo $base_path; ?>recipes.php" class="text-secondary text-decoration-none hover-white"><i class="bi bi-chevron-right me-1 text-primary"></i> Browse Recipes</a></li>
                        <li class="mb-2"><a href="<?php echo $base_path; ?>index.php#categories" class="text-secondary text-decoration-none hover-white"><i class="bi bi-chevron-right me-1 text-primary"></i> Categories</a></li>
                        <li class="mb-2"><a href="<?php echo $base_path; ?>about.php" class="text-secondary text-decoration-none hover-white"><i class="bi bi-chevron-right me-1 text-primary"></i> About TasteBook</a></li>
                        <li class="mb-2"><a href="<?php echo $base_path; ?>contact.php" class="text-secondary text-decoration-none hover-white"><i class="bi bi-chevron-right me-1 text-primary"></i> Contact Us</a></li>
                    </ul>
                </div>

                <!-- Col 3: Community & Account -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="text-uppercase fw-bold text-white mb-3 tracking-wide">Community</h6>
                    <ul class="list-unstyled footer-links small">
                        <?php if (is_logged_in()): ?>
                            <li class="mb-2"><a href="<?php echo $base_path; ?>dashboard.php" class="text-secondary text-decoration-none hover-white"><i class="bi bi-speedometer2 me-1 text-primary"></i> My Dashboard</a></li>
                            <li class="mb-2"><a href="<?php echo $base_path; ?>add_recipe.php" class="text-secondary text-decoration-none hover-white"><i class="bi bi-plus-circle me-1 text-primary"></i> Add New Recipe</a></li>
                            <li class="mb-2"><a href="<?php echo $base_path; ?>my_recipes.php" class="text-secondary text-decoration-none hover-white"><i class="bi bi-journal-text me-1 text-primary"></i> My Recipes</a></li>
                            <li class="mb-2"><a href="<?php echo $base_path; ?>favorites.php" class="text-secondary text-decoration-none hover-white"><i class="bi bi-heart me-1 text-primary"></i> Favorites</a></li>
                            <li class="mb-2">
                                <form action="<?php echo $base_path; ?>auth/logout.php" method="post">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                    <button type="submit" class="btn btn-link p-0 text-danger text-decoration-none small"><i class="bi bi-box-arrow-right me-1"></i> Logout</button>
                                </form>
                            </li>
                        <?php else: ?>
                            <li class="mb-2"><a href="<?php echo $base_path; ?>auth/login.php" class="text-secondary text-decoration-none hover-white"><i class="bi bi-box-arrow-in-right me-1 text-primary"></i> Sign In</a></li>
                            <li class="mb-2"><a href="<?php echo $base_path; ?>auth/register.php" class="text-secondary text-decoration-none hover-white"><i class="bi bi-person-plus me-1 text-primary"></i> Create Account</a></li>
                        <?php endif; ?>
                        <li class="mb-2"><a href="<?php echo $base_path; ?>recipes.php?sort=popular" class="text-secondary text-decoration-none hover-white"><i class="bi bi-star me-1 text-primary"></i> Popular Dishes</a></li>
                    </ul>
                </div>

                <!-- Col 4: Contact & Tech Stack -->
                <div class="col-lg-4 col-md-6">
                    <h6 class="text-uppercase fw-bold text-white mb-3 tracking-wide">Keep in Touch</h6>
                    <p class="text-secondary small mb-3">Questions or ideas for TasteBook? We would love to hear from you.</p>
                    <a href="<?php echo $base_path; ?>contact.php" class="btn btn-outline-light btn-sm rounded-pill px-3 mb-3">
                        <i class="bi bi-envelope me-1"></i> Contact the team
                    </a>
                    <div class="d-flex gap-3 small mb-3" aria-label="Share TasteBook">
                        <a href="https://www.facebook.com/sharer/sharer.php" class="text-secondary text-decoration-none hover-white" data-share-network="facebook" target="_blank" rel="noopener noreferrer">Facebook</a>
                        <a href="https://twitter.com/intent/tweet" class="text-secondary text-decoration-none hover-white" data-share-network="x" target="_blank" rel="noopener noreferrer">X</a>
                        <a href="https://api.whatsapp.com/send" class="text-secondary text-decoration-none hover-white" data-share-network="whatsapp" target="_blank" rel="noopener noreferrer">WhatsApp</a>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="badge bg-secondary bg-opacity-25 text-white border border-secondary border-opacity-25 font-monospace">PHP</span>
                        <span class="badge bg-secondary bg-opacity-25 text-white border border-secondary border-opacity-25 font-monospace">MySQL</span>
                        <span class="badge bg-secondary bg-opacity-25 text-white border border-secondary border-opacity-25 font-monospace">Bootstrap 5</span>
                        <span class="badge bg-secondary bg-opacity-25 text-white border border-secondary border-opacity-25 font-monospace">JavaScript</span>
                    </div>
                </div>
            </div>

            <!-- Bottom Copyright Bar -->
            <div class="row pt-3 align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="text-secondary small mb-0">
                        &copy; <?php echo date('Y'); ?> <strong>TasteBook</strong>. Made for good food and great company.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                    <span class="text-secondary small">
                        Crafted with <i class="bi bi-heart-fill text-danger"></i> for food lovers
                    </span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3.3 Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <!-- Custom JavaScript -->
    <script src="<?php echo $base_path; ?>js/script.js"></script>
</body>
</html>
