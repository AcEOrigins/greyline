// Security and Anti-Bot Protection
class SecurityManager {
    constructor() {
        this.honeypotField = document.getElementById('website');
        this.form = document.getElementById('contactForm');
        this.submissionAttempts = 0;
        this.maxAttempts = 3;
        this.blockedIPs = new Set();
        this.initSecurity();
    }

    initSecurity() {
        // Hide honeypot field
        if (this.honeypotField) {
            this.honeypotField.style.display = 'none';
            this.honeypotField.style.position = 'absolute';
            this.honeypotField.style.left = '-9999px';
        }

        // Add form protection
        this.setupFormProtection();
        
        // Add rate limiting
        this.setupRateLimiting();
        
        // Add input sanitization
        this.setupInputSanitization();
    }

    setupFormProtection() {
        if (this.form) {
            this.form.addEventListener('submit', (e) => {
                e.preventDefault();
                
                // Check honeypot
                if (this.honeypotField && this.honeypotField.value.trim() !== '') {
                    console.log('Bot detected via honeypot');
                    this.handleBotDetection();
                    return;
                }

                // Check submission rate
                if (this.submissionAttempts >= this.maxAttempts) {
                    this.showError('Too many submission attempts. Please try again later.');
                    return;
                }

                // Validate form
                if (this.validateForm()) {
                    this.handleFormSubmission();
                }
            });
        }
    }

    setupRateLimiting() {
        // Simple rate limiting using localStorage
        const lastSubmission = localStorage.getItem('lastFormSubmission');
        const now = Date.now();
        
        if (lastSubmission && (now - parseInt(lastSubmission)) < 60000) { // 1 minute
            this.showError('Please wait before submitting another message.');
            return false;
        }
        
        localStorage.setItem('lastFormSubmission', now.toString());
        return true;
    }

    setupInputSanitization() {
        const inputs = document.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            input.addEventListener('input', (e) => {
                // Only sanitize dangerous content, not normal spaces
                e.target.value = this.sanitizeInput(e.target.value, true);
            });
        });
    }

    sanitizeInput(input, allowSpaces = false) {
        // Remove potentially dangerous characters and scripts
        let sanitized = input
            .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
            .replace(/javascript:/gi, '')
            .replace(/on\w+\s*=/gi, '')
            .replace(/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/gi, '');
        if (allowSpaces) {
            // Only collapse multiple spaces, do not remove all spaces
            sanitized = sanitized.replace(/\s{2,}/g, ' ');
        } else {
            sanitized = sanitized.replace(/\s+/g, ' ');
        }
        return sanitized;
    }

    validateForm() {
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const subject = document.getElementById('subject').value.trim();
        const message = document.getElementById('message').value.trim();

        // Basic validation
        if (!name || name.length < 2) {
            this.showError('Please enter a valid name (minimum 2 characters).');
            return false;
        }

        if (!this.isValidEmail(email)) {
            this.showError('Please enter a valid email address.');
            return false;
        }

        if (!subject || subject.length < 5) {
            this.showError('Please enter a subject (minimum 5 characters).');
            return false;
        }

        if (!message || message.length < 10) {
            this.showError('Please enter a message (minimum 10 characters).');
            return false;
        }

        return true;
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    handleFormSubmission() {
        this.submissionAttempts++;
        
        // Show loading state
        const submitBtn = this.form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Sending...';
        submitBtn.disabled = true;

        // Get form data and ensure all fields are captured
        const formData = new FormData();
        
        // Manually add each field to ensure proper collection
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const subject = document.getElementById('subject').value.trim();
        const message = document.getElementById('message').value.trim();
        const website = document.getElementById('website').value.trim(); // honeypot field
        
        // Add all form fields
        formData.append('name', name);
        formData.append('email', email);
        formData.append('subject', subject);
        formData.append('message', message);
        formData.append('website', website); // honeypot field
        
        // Add timestamp for tracking
        formData.append('timestamp', new Date().toISOString());
        formData.append('source', 'greyline-website');
        
        // Debug: Log form data being sent (remove in production)
        console.log('Form data being sent:');
        for (let [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
        }
        
        // Submit to backend
        fetch('https://greylinestudio.com/backend/submit_contact.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // Check if response is ok
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            // Try to parse as JSON, but handle non-JSON responses
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    // If it's not JSON, check if it contains success indicators
                    if (text.toLowerCase().includes('success') || text.toLowerCase().includes('sent')) {
                        return { success: true, message: 'Message sent successfully' };
                    } else {
                        return { success: false, message: text || 'Unknown response from server' };
                    }
                }
            });
        })
        .then(data => {
            if (data.success) {
                this.showSuccess('Message sent successfully! Now let\'s create your account to track your project.');
                this.form.reset();
                
                // Pre-fill registration form with contact form data
                document.getElementById('regEmail').value = email;
                document.getElementById('regFirstName').value = name.split(' ')[0] || '';
                document.getElementById('regLastName').value = name.split(' ').slice(1).join(' ') || '';
                
                // Show registration modal
                setTimeout(() => {
                    const modal = document.getElementById('registrationModal');
                    modal.style.display = 'block';
                    document.body.style.overflow = 'hidden'; // Prevent background scrolling
                }, 1500);
            } else {
                this.showError(data.message || 'There was an error sending your message. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // If we get here, the form might still have been submitted successfully
            // Show a more neutral message
            this.showSuccess('Your message has been submitted. We\'ll get back to you soon.');
            this.form.reset();
        })
        .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    }

    handleBotDetection() {
        this.showError('Invalid submission detected.');
        // Log bot attempt for monitoring
        console.warn('Bot submission attempt detected');
    }

    showError(message) {
        this.showNotification(message, 'error');
    }

    showSuccess(message) {
        this.showNotification(message, 'success');
    }

    showNotification(message, type) {
        // Remove existing notifications
        const existingNotification = document.querySelector('.notification');
        if (existingNotification) {
            existingNotification.remove();
        }

        // Create notification
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        
        // Style notification
        notification.style.cssText = `
            position: fixed;
            top: 100px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 10000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            max-width: 300px;
            ${type === 'error' ? 'background: #ef4444;' : 'background: #10b981;'}
        `;

        document.body.appendChild(notification);

        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);

        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 300);
        }, 5000);
    }
}

// Navigation and UI Interactions
class NavigationManager {
    constructor() {
        this.navbar = document.querySelector('.navbar');
        this.hamburger = document.querySelector('.hamburger');
        this.navMenu = document.querySelector('.nav-menu');
        this.navLinks = document.querySelectorAll('.nav-link');
        this.initNavigation();
    }

    initNavigation() {
        // Mobile menu toggle
        if (this.hamburger) {
            this.hamburger.addEventListener('click', () => {
                this.toggleMobileMenu();
            });
        }

        // Smooth scrolling for navigation links
        this.navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = link.getAttribute('href');
                const targetSection = document.querySelector(targetId);
                
                if (targetSection) {
                    const offsetTop = targetSection.offsetTop - 80; // Account for fixed navbar
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }

                // Close mobile menu if open
                if (this.navMenu.classList.contains('active')) {
                    this.toggleMobileMenu();
                }
            });
        });

        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            this.handleScroll();
        });

        // Active navigation highlighting
        window.addEventListener('scroll', () => {
            this.updateActiveNavLink();
        });
    }

    toggleMobileMenu() {
        this.navMenu.classList.toggle('active');
        this.hamburger.classList.toggle('active');
        
        // Animate hamburger bars
        const bars = this.hamburger.querySelectorAll('.bar');
        bars.forEach((bar, index) => {
            if (this.hamburger.classList.contains('active')) {
                if (index === 0) bar.style.transform = 'rotate(45deg) translate(5px, 5px)';
                if (index === 1) bar.style.opacity = '0';
                if (index === 2) bar.style.transform = 'rotate(-45deg) translate(7px, -6px)';
            } else {
                bar.style.transform = 'none';
                bar.style.opacity = '1';
            }
        });
    }

    handleScroll() {
        if (window.scrollY > 100) {
            this.navbar.style.background = 'rgba(15, 23, 42, 0.98)';
            this.navbar.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.3)';
        } else {
            this.navbar.style.background = 'rgba(15, 23, 42, 0.95)';
            this.navbar.style.boxShadow = 'none';
        }
    }

    updateActiveNavLink() {
        const sections = document.querySelectorAll('section[id]');
        const scrollPos = window.scrollY + 100;

        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.offsetHeight;
            const sectionId = section.getAttribute('id');
            const navLink = document.querySelector(`.nav-link[href="#${sectionId}"]`);

            if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
                this.navLinks.forEach(link => link.classList.remove('active'));
                if (navLink) navLink.classList.add('active');
            }
        });
    }
}

// Scroll Animations
class ScrollAnimationManager {
    constructor() {
        this.observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        this.initScrollAnimations();
    }

    initScrollAnimations() {
        // Add scroll-reveal class to elements
        const elementsToAnimate = document.querySelectorAll('.service-card, .about-content, .contact-content');
        elementsToAnimate.forEach(el => {
            el.classList.add('scroll-reveal');
        });

        // Create intersection observer
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                }
            });
        }, this.observerOptions);

        // Observe elements
        document.querySelectorAll('.scroll-reveal').forEach(el => {
            observer.observe(el);
        });
    }
}

// Code Animation
class CodeAnimationManager {
    constructor() {
        this.codeLines = document.querySelectorAll('.code-line');
        this.initCodeAnimation();
    }

    initCodeAnimation() {
        if (this.codeLines.length > 0) {
            this.codeLines.forEach((line, index) => {
                line.style.opacity = '0';
                line.style.transform = 'translateX(-20px)';
                
                setTimeout(() => {
                    line.style.transition = 'all 0.5s ease';
                    line.style.opacity = '1';
                    line.style.transform = 'translateX(0)';
                }, index * 200);
            });
        }
    }
}

// Performance and Analytics
class PerformanceManager {
    constructor() {
        this.initPerformanceMonitoring();
    }

    initPerformanceMonitoring() {
        // Monitor page load performance
        window.addEventListener('load', () => {
            const loadTime = performance.now();
            console.log(`Page loaded in ${loadTime.toFixed(2)}ms`);
        });

        // Monitor scroll performance
        let scrollTimeout;
        window.addEventListener('scroll', () => {
            if (scrollTimeout) {
                clearTimeout(scrollTimeout);
            }
            scrollTimeout = setTimeout(() => {
                // Throttled scroll handling
            }, 16); // ~60fps
        });
    }
}

// User Registration and Login Manager
class UserManager {
    constructor() {
        this.registrationModal = document.getElementById('registrationModal');
        this.loginModal = document.getElementById('loginModal');
        this.registrationForm = document.getElementById('registrationForm');
        this.loginForm = document.getElementById('loginForm');
        this.initUserManagement();
    }

    initUserManagement() {
        // Modal close buttons
        document.querySelectorAll('.close').forEach(closeBtn => {
            closeBtn.addEventListener('click', () => {
                this.closeAllModals();
            });
        });

        // Close modal when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                this.closeAllModals();
            }
        });

        // Switch between login and registration
        document.getElementById('showLogin').addEventListener('click', (e) => {
            e.preventDefault();
            this.registrationModal.style.display = 'none';
            this.loginModal.style.display = 'block';
        });

        document.getElementById('showRegister').addEventListener('click', (e) => {
            e.preventDefault();
            this.loginModal.style.display = 'none';
            this.registrationModal.style.display = 'block';
        });

        // Handle registration form submission
        this.registrationForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleRegistration();
        });

        // Handle login form submission
        this.loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleLogin();
        });

        // Handle navbar login button
        const navLoginBtn = document.getElementById('navLoginBtn');
        if (navLoginBtn) {
            navLoginBtn.addEventListener('click', () => {
                this.loginModal.style.display = 'block';
                document.body.style.overflow = 'hidden';
            });
        }

        // Check if user is already logged in
        this.checkLoginStatus();
    }

    checkLoginStatus() {
        const user = JSON.parse(localStorage.getItem('user'));
        const navLoginBtn = document.getElementById('navLoginBtn');
        
        if (user && navLoginBtn) {
            navLoginBtn.textContent = `Welcome, ${user.firstName}`;
            navLoginBtn.classList.add('logged-in');
            navLoginBtn.onclick = () => {
                // Redirect to user portal
                window.location.href = '/user-portal.html';
            };
        }
    }

    closeAllModals() {
        this.registrationModal.style.display = 'none';
        this.loginModal.style.display = 'none';
        document.body.style.overflow = 'auto'; // Restore scrolling
    }

    handleRegistration() {
        const formData = {
            email: document.getElementById('regEmail').value.trim(),
            password: document.getElementById('regPassword').value,
            firstName: document.getElementById('regFirstName').value.trim(),
            lastName: document.getElementById('regLastName').value.trim(),
            companyName: document.getElementById('regCompany').value.trim(),
            phone: document.getElementById('regPhone').value.trim()
        };

        // Validation
        if (formData.password !== document.getElementById('regConfirmPassword').value) {
            this.showNotification('Passwords do not match', 'error');
            return;
        }

        if (formData.password.length < 8) {
            this.showNotification('Password must be at least 8 characters long', 'error');
            return;
        }

        // Show loading state
        const submitBtn = this.registrationForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Creating Account...';
        submitBtn.disabled = true;

        // Submit registration
        fetch('https://greylinestudio.com/backend/register_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showNotification(data.message, 'success');
                this.closeAllModals();
                // Redirect to user portal or show success message
                setTimeout(() => {
                    window.location.href = '/user-portal.html';
                }, 2000);
            } else {
                this.showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Registration error:', error);
            this.showNotification('Registration failed. Please try again.', 'error');
        })
        .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    }

    handleLogin() {
        const formData = {
            email: document.getElementById('loginEmail').value.trim(),
            password: document.getElementById('loginPassword').value
        };

        // Show loading state
        const submitBtn = this.loginForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Logging In...';
        submitBtn.disabled = true;

        // Submit login
        fetch('https://greylinestudio.com/backend/login_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showNotification(data.message, 'success');
                this.closeAllModals();
                // Store user data in localStorage
                localStorage.setItem('user', JSON.stringify(data.user));
                // Update navbar button
                this.checkLoginStatus();
                // Redirect to user portal
                setTimeout(() => {
                    window.location.href = '/user-portal.html';
                }, 1500);
            } else {
                this.showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Login error:', error);
            this.showNotification('Login failed. Please try again.', 'error');
        })
        .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    }

    showNotification(message, type) {
        // Remove existing notifications
        const existingNotification = document.querySelector('.notification');
        if (existingNotification) {
            existingNotification.remove();
        }

        // Create notification
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        
        // Style notification
        notification.style.cssText = `
            position: fixed;
            top: 100px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 10001;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            max-width: 300px;
            ${type === 'error' ? 'background: #ef4444;' : 'background: #10b981;'}
        `;

        document.body.appendChild(notification);

        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);

        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 5000);
    }
}

// Initialize all managers when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Initialize security manager first
    const securityManager = new SecurityManager();
    
    // Initialize other managers
    const navigationManager = new NavigationManager();
    const scrollAnimationManager = new ScrollAnimationManager();
    const codeAnimationManager = new CodeAnimationManager();
    const performanceManager = new PerformanceManager();
    const userManager = new UserManager();

    // Add some interactive features
    addInteractiveFeatures();
});

// Additional interactive features
function addInteractiveFeatures() {
    // Add hover effects to service cards
    const serviceCards = document.querySelectorAll('.service-card');
    serviceCards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-10px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Add typing effect to hero title
    const heroTitle = document.querySelector('.hero-title');
    if (heroTitle) {
        const text = heroTitle.textContent;
        heroTitle.textContent = '';
        heroTitle.style.borderRight = '2px solid #6366f1';
        
        let i = 0;
        const typeWriter = () => {
            if (i < text.length) {
                heroTitle.textContent += text.charAt(i);
                i++;
                setTimeout(typeWriter, 100);
            } else {
                heroTitle.style.borderRight = 'none';
            }
        };
        
        // Start typing effect after a short delay
        setTimeout(typeWriter, 500);
    }

    // Add parallax effect to hero section
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const hero = document.querySelector('.hero');
        if (hero) {
            const rate = scrolled * -0.5;
            hero.style.transform = `translateY(${rate}px)`;
        }
    });

    // Add counter animation to stats
    const stats = document.querySelectorAll('.stat h3');
    const animateCounters = () => {
        stats.forEach(stat => {
            const target = parseInt(stat.textContent);
            const increment = target / 100;
            let current = 0;
            
            const updateCounter = () => {
                if (current < target) {
                    current += increment;
                    stat.textContent = Math.ceil(current) + (stat.textContent.includes('%') ? '%' : '+');
                    requestAnimationFrame(updateCounter);
                } else {
                    stat.textContent = target + (stat.textContent.includes('%') ? '%' : '+');
                }
            };
            
            updateCounter();
        });
    };

    // Trigger counter animation when stats section is visible
    const statsSection = document.querySelector('.stats');
    if (statsSection) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    observer.unobserve(entry.target);
                }
            });
        });
        observer.observe(statsSection);
    }
}

// Export for potential module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        SecurityManager,
        NavigationManager,
        ScrollAnimationManager,
        CodeAnimationManager,
        PerformanceManager
    };
} 