/**
 * TasteBook – Digital Recipe Book
 * Main JavaScript File (js/script.js)
 * 
 * ICT 2209 - Web Technologies Mini Project
 * Rajarata University of Sri Lanka
 * 
 * Features Implemented:
 * 1. Dynamic Live Recipe Search & Category Filtering
 * 2. Complete Client-Side Form Validation (Register, Login, Contact, Recipe)
 * 3. Interactive Hero Carousel & Slider Controls with Auto-Play & Pause
 * 4. Smooth Scrolling, Navbar Scroll Effects & Back-To-Top Button
 * 5. Interactive Recipe Features: Ingredient Checklist, Portion Scaler, Database Favorites, Live Image Preview
 * 6. Scroll Reveal Fade-in Animations (IntersectionObserver)
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // =========================================================================
    // FEATURE 1: Dynamic Live Recipe Search & Category Filter
    // =========================================================================
    const searchInput = document.getElementById('recipeSearchInput');
    const categoryFilterBtns = document.querySelectorAll('.category-filter-btn');
    const recipeCards = document.querySelectorAll('.recipe-item-col');
    const recipeCountDisplay = document.getElementById('recipeCountDisplay');
    const noResultsMessage = document.getElementById('noResultsMessage');
    const difficultyFilter = document.getElementById('difficultyFilter');
    const prepTimeFilter = document.getElementById('prepTimeFilter');
    const cookTimeFilter = document.getElementById('cookTimeFilter');
    const recipeSort = document.getElementById('recipeSort');

    let currentCategory = document.querySelector('.category-filter-btn.active')?.getAttribute('data-category')?.toLowerCase() || 'all';
    let currentSearchTerm = '';

    function filterRecipes() {
        let visibleCount = 0;
        const difficulty = difficultyFilter?.value || 'all';
        const maxPrepTime = prepTimeFilter?.value || 'all';
        const maxCookTime = cookTimeFilter?.value || 'all';
        const sortMode = recipeSort?.value || 'newest';
        const sortedCards = Array.from(recipeCards).sort((first, second) => {
            if (sortMode === 'oldest') {
                return Number(first.dataset.created) - Number(second.dataset.created);
            }
            if (sortMode === 'popular') {
                return Number(second.dataset.favorites) - Number(first.dataset.favorites);
            }
            if (sortMode === 'alphabetical') {
                return (first.dataset.title || '').localeCompare(second.dataset.title || '');
            }
            return Number(second.dataset.created) - Number(first.dataset.created);
        });
        const recipeGrid = document.getElementById('recipeCardsGrid');
        sortedCards.forEach(card => recipeGrid?.appendChild(card));

        sortedCards.forEach(card => {
            const title = card.getAttribute('data-title')?.toLowerCase() || '';
            const description = card.getAttribute('data-description')?.toLowerCase() || '';
            const ingredients = card.getAttribute('data-ingredients')?.toLowerCase() || '';
            const category = card.getAttribute('data-category')?.toLowerCase() || 'all';

            const matchesSearch = title.includes(currentSearchTerm) || 
                                  description.includes(currentSearchTerm) || 
                                  ingredients.includes(currentSearchTerm) ||
                                  category.includes(currentSearchTerm);
            const matchesCategory = currentCategory === 'all' || category === currentCategory;
            const matchesDifficulty = difficulty === 'all' || card.dataset.difficulty === difficulty;
            const matchesPrepTime = maxPrepTime === 'all' || Number(card.dataset.prepTime) <= Number(maxPrepTime);
            const matchesCookTime = maxCookTime === 'all' || Number(card.dataset.cookTime) <= Number(maxCookTime);

            if (matchesSearch && matchesCategory && matchesDifficulty && matchesPrepTime && matchesCookTime) {
                card.style.display = 'block';
                // Trigger smooth fade in
                card.classList.add('animate-fade-in');
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Update recipe counter
        if (recipeCountDisplay) {
            recipeCountDisplay.textContent = `${visibleCount} Recipe${visibleCount === 1 ? '' : 's'} Found`;
        }

        // Toggle No Results placeholder
        if (noResultsMessage) {
            if (visibleCount === 0) {
                noResultsMessage.classList.remove('d-none');
            } else {
                noResultsMessage.classList.add('d-none');
            }
        }
    }

    if (searchInput) {
        // If pre-filled from GET query parameter (e.g. from index.php search), trigger immediately
        if (searchInput.value.trim() !== '') {
            currentSearchTerm = searchInput.value.trim().toLowerCase();
            categoryFilterBtns.forEach(btn => {
                if (btn.getAttribute('data-category').toLowerCase() === currentSearchTerm) {
                    categoryFilterBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                }
            });
            filterRecipes();
        }

        searchInput.addEventListener('input', function (e) {
            currentSearchTerm = e.target.value.trim().toLowerCase();
            filterRecipes();
        });

        // Clear search button if exists
        const clearBtn = document.getElementById('clearSearchBtn');
        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                searchInput.value = '';
                currentSearchTerm = '';
                filterRecipes();
                searchInput.focus();
            });
        }
    }

    if (categoryFilterBtns.length > 0) {
        categoryFilterBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                categoryFilterBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentCategory = this.getAttribute('data-category').toLowerCase();
                filterRecipes();
            });
        });
    }

    [difficultyFilter, prepTimeFilter, cookTimeFilter, recipeSort].forEach(control => {
        control?.addEventListener('change', filterRecipes);
    });

    const resetFiltersBtn = document.getElementById('resetRecipeFilters');
    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', function () {
            if (searchInput) searchInput.value = '';
            if (difficultyFilter) difficultyFilter.value = 'all';
            if (prepTimeFilter) prepTimeFilter.value = 'all';
            if (cookTimeFilter) cookTimeFilter.value = 'all';
            if (recipeSort) recipeSort.value = 'newest';
            currentSearchTerm = '';
            currentCategory = 'all';
            categoryFilterBtns.forEach(btn => btn.classList.toggle('active', btn.dataset.category === 'all'));
            filterRecipes();
        });
    }
    filterRecipes();

    // =========================================================================
    // FEATURE 2: Comprehensive Client-side Form Validation
    // =========================================================================

    // Helper: Email format validator regex
    function isValidEmail(email) {
        const re = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        return re.test(String(email).toLowerCase());
    }

    // Helper: Display field error
    function setFieldError(input, message) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        let feedback = input.parentElement.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            input.parentElement.appendChild(feedback);
        }
        feedback.textContent = message;
    }

    // Helper: Display field success
    function setFieldSuccess(input) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
    }

    // 2.1 User Registration Form Validation
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            let isValid = true;
            const usernameInput = document.getElementById('regUsername');
            const emailInput = document.getElementById('regEmail');
            const passwordInput = document.getElementById('regPassword');
            const confirmPasswordInput = document.getElementById('regConfirmPassword');

            // Username validation
            if (!usernameInput.value.trim()) {
                setFieldError(usernameInput, 'Please enter a valid username.');
                isValid = false;
            } else if (usernameInput.value.trim().length < 3) {
                setFieldError(usernameInput, 'Username must be at least 3 characters long.');
                isValid = false;
            } else {
                setFieldSuccess(usernameInput);
            }

            // Email validation
            if (!emailInput.value.trim()) {
                setFieldError(emailInput, 'Email address is required.');
                isValid = false;
            } else if (!isValidEmail(emailInput.value.trim())) {
                setFieldError(emailInput, 'Please provide a valid email address.');
                isValid = false;
            } else {
                setFieldSuccess(emailInput);
            }

            // Password validation
            if (!passwordInput.value) {
                setFieldError(passwordInput, 'Password is required.');
                isValid = false;
            } else if (passwordInput.value.length < 8 || !/[A-Za-z]/.test(passwordInput.value) || !/[0-9]/.test(passwordInput.value)) {
                setFieldError(passwordInput, 'Use at least 8 characters, including a letter and a number.');
                isValid = false;
            } else {
                setFieldSuccess(passwordInput);
            }

            // Confirm Password validation
            if (!confirmPasswordInput.value) {
                setFieldError(confirmPasswordInput, 'Please confirm your password.');
                isValid = false;
            } else if (confirmPasswordInput.value !== passwordInput.value) {
                setFieldError(confirmPasswordInput, 'Passwords do not match.');
                isValid = false;
            } else {
                setFieldSuccess(confirmPasswordInput);
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    }

    // 2.2 User Login Form Validation
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            let isValid = true;
            const emailInput = document.getElementById('loginEmail');
            const passwordInput = document.getElementById('loginPassword');

            if (!emailInput.value.trim()) {
                setFieldError(emailInput, 'Please enter your email.');
                isValid = false;
            } else if (!isValidEmail(emailInput.value.trim())) {
                setFieldError(emailInput, 'Please enter a valid email address.');
                isValid = false;
            } else {
                setFieldSuccess(emailInput);
            }

            if (!passwordInput.value) {
                setFieldError(passwordInput, 'Please enter your password.');
                isValid = false;
            } else {
                setFieldSuccess(passwordInput);
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    }

    // 2.3 Contact Form Validation
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function (e) {
            let isValid = true;
            const nameInput = document.getElementById('contactName');
            const emailInput = document.getElementById('contactEmail');
            const messageInput = document.getElementById('contactMessage');

            if (!nameInput.value.trim()) {
                setFieldError(nameInput, 'Your name is required.');
                isValid = false;
            } else {
                setFieldSuccess(nameInput);
            }

            if (!emailInput.value.trim()) {
                setFieldError(emailInput, 'Your email is required.');
                isValid = false;
            } else if (!isValidEmail(emailInput.value.trim())) {
                setFieldError(emailInput, 'Please enter a valid email.');
                isValid = false;
            } else {
                setFieldSuccess(emailInput);
            }

            if (!messageInput.value.trim()) {
                setFieldError(messageInput, 'Please write your message.');
                isValid = false;
            } else if (messageInput.value.trim().length < 10) {
                setFieldError(messageInput, 'Message must be at least 10 characters.');
                isValid = false;
            } else {
                setFieldSuccess(messageInput);
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    }

    // 2.4 Add Recipe Form Validation
    const recipeForm = document.getElementById('addRecipeForm');
    if (recipeForm) {
        recipeForm.addEventListener('submit', function (e) {
            let isValid = true;
            const titleInput = document.getElementById('recipeTitle');
            const ingredientsInput = document.getElementById('recipeIngredients');
            const instructionsInput = document.getElementById('recipeInstructions');

            if (!titleInput.value.trim()) {
                setFieldError(titleInput, 'Recipe title is required.');
                isValid = false;
            } else {
                setFieldSuccess(titleInput);
            }

            if (!ingredientsInput.value.trim()) {
                setFieldError(ingredientsInput, 'Please list the ingredients.');
                isValid = false;
            } else {
                setFieldSuccess(ingredientsInput);
            }

            if (!instructionsInput.value.trim()) {
                setFieldError(instructionsInput, 'Please provide cooking instructions.');
                isValid = false;
            } else {
                setFieldSuccess(instructionsInput);
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    }

    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', function (e) {
            const currentPassword = document.getElementById('currentPassword');
            const newPassword = document.getElementById('newPassword');
            const confirmPassword = document.getElementById('confirmPassword');
            const changingPassword = currentPassword.value || newPassword.value || confirmPassword.value;
            let isValid = true;

            if (changingPassword && !currentPassword.value) {
                setFieldError(currentPassword, 'Enter your current password.');
                isValid = false;
            }
            if (changingPassword && (newPassword.value.length < 8 || !/[A-Za-z]/.test(newPassword.value) || !/[0-9]/.test(newPassword.value))) {
                setFieldError(newPassword, 'Use at least 8 characters, including a letter and a number.');
                isValid = false;
            }
            if (changingPassword && newPassword.value !== confirmPassword.value) {
                setFieldError(confirmPassword, 'The new passwords do not match.');
                isValid = false;
            }
            if (!isValid) e.preventDefault();
        });
    }

    document.querySelectorAll('[data-share-network]').forEach(link => {
        const url = encodeURIComponent(window.location.href);
        const title = encodeURIComponent(document.title);
        const network = link.dataset.shareNetwork;
        if (network === 'facebook') {
            link.href = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
        } else if (network === 'x') {
            link.href = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
        } else if (network === 'whatsapp') {
            link.href = `https://api.whatsapp.com/send?text=${title}%20${url}`;
        }
    });

    // =========================================================================
    // FEATURE 3: Interactive Carousel & Image Slider Controls
    // =========================================================================
    const heroCarouselElement = document.querySelector('#heroRecipeCarousel');
    if (heroCarouselElement && typeof bootstrap !== 'undefined') {
        const carousel = new bootstrap.Carousel(heroCarouselElement, {
            interval: 4000,
            ride: 'carousel',
            pause: 'hover',
            wrap: true
        });

        // Add pause on mouse hover effect
        heroCarouselElement.addEventListener('mouseenter', () => carousel.pause());
        heroCarouselElement.addEventListener('mouseleave', () => carousel.cycle());
    }

    // =========================================================================
    // FEATURE 4: Smooth Scrolling & Back-To-Top Button
    // =========================================================================
    const backToTopBtn = document.getElementById('backToTopBtn');
    const mainNavbar = document.getElementById('mainNavbar');

    window.addEventListener('scroll', function () {
        // Sticky Navbar Glassmorphism transition
        if (window.scrollY > 50) {
            if (mainNavbar) mainNavbar.classList.add('scrolled');
        } else {
            if (mainNavbar) mainNavbar.classList.remove('scrolled');
        }

        // Back to Top button visibility
        if (backToTopBtn) {
            if (window.scrollY > 300) {
                backToTopBtn.style.display = 'flex';
            } else {
                backToTopBtn.style.display = 'none';
            }
        }
    });

    if (backToTopBtn) {
        backToTopBtn.addEventListener('click', function () {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId !== '#' && targetId.length > 1) {
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    e.preventDefault();
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });

    // =========================================================================
    // FEATURE 5: Interactive Recipe Features
    // =========================================================================

    // 5.1 Interactive Ingredient Checklist
    document.querySelectorAll('.ingredient-item input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            this.closest('.ingredient-item')?.classList.toggle('checked', this.checked);
        });
    });

    // 5.2 Recipe Serving Size / Portion Multiplier Scaler
    const servingDecreaseBtn = document.getElementById('servingDecreaseBtn');
    const servingIncreaseBtn = document.getElementById('servingIncreaseBtn');
    const servingCountDisplay = document.getElementById('servingCountDisplay');
    const ingredientQuantities = document.querySelectorAll('.ingredient-qty');

    if (servingCountDisplay && servingDecreaseBtn && servingIncreaseBtn) {
        const baseServings = parseInt(document.querySelector('.ingredients-checklist')?.dataset.baseServings, 10) || parseInt(servingCountDisplay.textContent, 10) || 4;
        let currentServings = parseInt(servingCountDisplay.textContent, 10) || baseServings;

        function updateIngredientQuantities(newServings) {
            const ratio = newServings / baseServings;
            ingredientQuantities.forEach(qtySpan => {
                const baseVal = parseFloat(qtySpan.getAttribute('data-base-qty'));
                if (!isNaN(baseVal)) {
                    const scaled = (baseVal * ratio);
                    // Format nicely: e.g. 1.5 or integer
                    qtySpan.textContent = (scaled % 1 === 0) ? scaled : scaled.toFixed(1);
                }
            });
            servingCountDisplay.textContent = newServings;
        }

        servingIncreaseBtn.addEventListener('click', function () {
            if (currentServings < 20) {
                currentServings += 1;
                updateIngredientQuantities(currentServings);
            }
        });

        servingDecreaseBtn.addEventListener('click', function () {
            if (currentServings > 1) {
                currentServings -= 1;
                updateIngredientQuantities(currentServings);
            }
        });
    }

    // 5.3 Live Recipe Image Upload Preview (Add Recipe Page)
    const recipeImageInput = document.getElementById('recipeImageInput');
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');
    const imagePreview = document.getElementById('imagePreview');

    if (recipeImageInput && imagePreviewContainer && imagePreview) {
        recipeImageInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    imagePreview.setAttribute('src', e.target.result);
                    imagePreviewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                imagePreviewContainer.style.display = 'none';
            }
        });
    }

    // 5.4 Recipe Print Handler
    const printRecipeBtn = document.getElementById('printRecipeBtn');
    if (printRecipeBtn) {
        printRecipeBtn.addEventListener('click', function () {
            window.print();
        });
    }

    // 5.5 Native recipe sharing with a clipboard fallback
    const shareRecipeBtn = document.getElementById('shareRecipeBtn');
    if (shareRecipeBtn) {
        shareRecipeBtn.addEventListener('click', async function () {
            const shareUrl = new URL(this.dataset.shareUrl, window.location.href).href;
            const shareData = { title: this.dataset.shareTitle, url: shareUrl };
            try {
                if (navigator.share) {
                    await navigator.share(shareData);
                } else if (navigator.clipboard) {
                    await navigator.clipboard.writeText(shareUrl);
                    this.innerHTML = '<i class="bi bi-check2 me-1"></i> Link copied';
                } else {
                    window.prompt('Copy this recipe link:', shareUrl);
                }
            } catch (error) {
                if (error.name !== 'AbortError') {
                    console.error('Could not share the recipe:', error);
                    window.prompt('Copy this recipe link:', shareUrl);
                }
            }
        });
    }

    // =========================================================================
    // FEATURE 6: Scroll Reveal Fade-in Animations (IntersectionObserver)
    // =========================================================================
    if ('IntersectionObserver' in window) {
        const revealElements = document.querySelectorAll('.reveal-on-scroll');
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -40px 0px'
        };

        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        revealElements.forEach(el => revealObserver.observe(el));
    }
});
