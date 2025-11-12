<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PM System - Professional Project Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
            background-color: #f8f9fa;
        }

        /* Navbar - Same as Dashboard */
        .landing-navbar {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
        }

        .navbar-brand i {
            color: #0d6efd;
        }

        .nav-link {
            color: #64748b;
            font-weight: 500;
            margin: 0 0.5rem;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: #0d6efd;
        }

        /* Buttons */
        .btn-primary-custom {
            background-color: #0d6efd;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 6px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-primary-custom:hover {
            background-color: #0b5ed7;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
        }

        .btn-outline-custom {
            border: 2px solid #0d6efd;
            color: #0d6efd;
            background: transparent;
            padding: 0.6rem 1.5rem;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-outline-custom:hover {
            background: #0d6efd;
            color: white;
        }

        /* Hero Section - Clean & Professional */
        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(to bottom, #ffffff 0%, #f8f9fa 100%);
            padding-top: 100px;
            padding-bottom: 50px;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero-content p {
            font-size: 1.25rem;
            color: #64748b;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .hero-stats {
            display: flex;
            gap: 2rem;
            margin-top: 3rem;
            flex-wrap: wrap;
        }

        .hero-stat-item {
            text-align: center;
        }

        .hero-stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: #0d6efd;
            display: block;
        }

        .hero-stat-label {
            font-size: 0.9rem;
            color: #64748b;
            font-weight: 500;
        }

        .hero-image {
            position: relative;
        }

        .dashboard-preview {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            border: 1px solid #e2e8f0;
        }

        .preview-header {
            display: flex;
            gap: 6px;
            margin-bottom: 1rem;
        }

        .preview-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .preview-dot.red { background-color: #ef4444; }
        .preview-dot.yellow { background-color: #f59e0b; }
        .preview-dot.green { background-color: #10b981; }

        .preview-content {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 2rem;
            min-height: 300px;
        }

        .preview-card {
            background: white;
            border-radius: 6px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            border-left: 4px solid #0d6efd;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        /* Features Section */
        .features-section {
            padding: 100px 0;
            background: white;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 1rem;
            color: #1e293b;
        }

        .section-subtitle {
            text-align: center;
            color: #64748b;
            font-size: 1.1rem;
            margin-bottom: 4rem;
        }

        .feature-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 2rem;
            transition: all 0.3s ease;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-color: #0d6efd;
        }

        .feature-icon {
            width: 64px;
            height: 64px;
            background-color: #e7f1ff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: #0d6efd;
            margin-bottom: 1.5rem;
        }

        .feature-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #1e293b;
        }

        .feature-description {
            color: #64748b;
            line-height: 1.6;
            margin: 0;
        }

        /* Benefits Section */
        .benefits-section {
            padding: 100px 0;
            background: #f8f9fa;
        }

        .benefit-item {
            display: flex;
            align-items: start;
            margin-bottom: 2rem;
        }

        .benefit-icon {
            width: 48px;
            height: 48px;
            background-color: #0d6efd;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
            margin-right: 1.5rem;
            flex-shrink: 0;
        }

        .benefit-content h4 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .benefit-content p {
            color: #64748b;
            margin: 0;
            line-height: 1.6;
        }

        /* CTA Section */
        .cta-section {
            padding: 100px 0;
            background: #1e293b;
            text-align: center;
            color: white;
        }

        .cta-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        .cta-description {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 2.5rem;
        }

        /* Footer */
        .landing-footer {
            background: #0f172a;
            color: white;
            padding: 3rem 0 1.5rem;
        }

        .footer-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .footer-links {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 0.5rem;
        }

        .footer-links a {
            color: #94a3b8;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }
            
            .hero-content p {
                font-size: 1.1rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .hero-stats {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="landing-navbar">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <a class="navbar-brand" href="{{ route('landing') }}">
                    <i class="bi bi-kanban-fill me-2"></i>PM System
                </a>
                <div class="d-flex align-items-center gap-3">
                    @guest
                        <a href="{{ route('login') }}" class="btn btn-outline-custom">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Login
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-primary-custom">
                            <i class="bi bi-person-plus me-2"></i>Sign Up Free
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="btn btn-primary-custom">
                            <i class="bi bi-speedometer2 me-2"></i>Go to Dashboard
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1>Modern Project Management for Your Team</h1>
                        <p>
                            Streamline workflows, track progress, and deliver projects on time. 
                            Everything you need in one powerful platform.
                        </p>
                        <div class="d-flex gap-3 mb-4">
                            <a href="{{ route('register') }}" class="btn btn-primary-custom btn-lg px-4">
                                <i class="bi bi-rocket-takeoff me-2"></i>Get Started Free
                            </a>
                            <a href="#features" class="btn btn-outline-custom btn-lg px-4">
                                <i class="bi bi-play-circle me-2"></i>See Features
                            </a>
                        </div>
                        <div class="hero-stats">
                            <div class="hero-stat-item">
                                <span class="hero-stat-number">500+</span>
                                <span class="hero-stat-label">Projects</span>
                            </div>
                            <div class="hero-stat-item">
                                <span class="hero-stat-number">1.2K+</span>
                                <span class="hero-stat-label">Users</span>
                            </div>
                            <div class="hero-stat-item">
                                <span class="hero-stat-number">99.9%</span>
                                <span class="hero-stat-label">Uptime</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mt-5 mt-lg-0">
                    <div class="hero-image">
                        <div class="dashboard-preview">
                            <div class="preview-header">
                                <div class="preview-dot red"></div>
                                <div class="preview-dot yellow"></div>
                                <div class="preview-dot green"></div>
                            </div>
                            <div class="preview-content">
                                <div class="preview-card">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-kanban text-primary me-3" style="font-size: 1.5rem;"></i>
                                        <div>
                                            <strong>Kanban Board</strong>
                                            <small class="d-block text-muted">Drag & drop tasks</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-card" style="border-left-color: #10b981;">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-clock-history text-success me-3" style="font-size: 1.5rem;"></i>
                                        <div>
                                            <strong>Time Tracking</strong>
                                            <small class="d-block text-muted">Automatic time logs</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-card" style="border-left-color: #f59e0b;">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-people text-warning me-3" style="font-size: 1.5rem;"></i>
                                        <div>
                                            <strong>Team Collaboration</strong>
                                            <small class="d-block text-muted">Real-time updates</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <h2 class="section-title">Everything You Need to Succeed</h2>
            <p class="section-subtitle">Powerful features designed for modern project management</p>

            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-kanban"></i>
                        </div>
                        <h3 class="feature-title">Kanban Boards</h3>
                        <p class="feature-description">
                            Visualize your workflow with intuitive drag-and-drop Kanban boards. 
                            Move tasks seamlessly across different stages.
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <h3 class="feature-title">Time Tracking</h3>
                        <p class="feature-description">
                            Track time spent on tasks automatically. Get detailed reports 
                            for accurate billing and productivity insights.
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <h3 class="feature-title">Team Collaboration</h3>
                        <p class="feature-description">
                            Work together with comments, @mentions, and file attachments. 
                            Keep everyone on the same page.
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-bell"></i>
                        </div>
                        <h3 class="feature-title">Smart Notifications</h3>
                        <p class="feature-description">
                            Stay updated with intelligent notifications for deadlines, 
                            mentions, and important project updates.
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h3 class="feature-title">Role-Based Access</h3>
                        <p class="feature-description">
                            Control who can see and do what with flexible permissions 
                            for your entire team.
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <h3 class="feature-title">Analytics & Reports</h3>
                        <p class="feature-description">
                            Get insights into project progress and team performance 
                            with detailed analytics and reports.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="benefits-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <h2 class="section-title text-start mb-4">Why Choose PM System?</h2>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="bi bi-lightning-charge"></i>
                        </div>
                        <div class="benefit-content">
                            <h4>Fast & Efficient</h4>
                            <p>Built for speed. Manage hundreds of projects without slowing down.</p>
                        </div>
                    </div>

                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="bi bi-lock"></i>
                        </div>
                        <div class="benefit-content">
                            <h4>Secure & Reliable</h4>
                            <p>Enterprise-grade security with 99.9% uptime guarantee.</p>
                        </div>
                    </div>

                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="bi bi-puzzle"></i>
                        </div>
                        <div class="benefit-content">
                            <h4>Easy Integration</h4>
                            <p>Connect with your favorite tools and services seamlessly.</p>
                        </div>
                    </div>

                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <i class="bi bi-headset"></i>
                        </div>
                        <div class="benefit-content">
                            <h4>24/7 Support</h4>
                            <p>Our support team is always ready to help you succeed.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="text-center">
                        <i class="bi bi-diagram-3" style="font-size: 15rem; color: #e2e8f0;"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2 class="cta-title">Ready to Get Started?</h2>
            <p class="cta-description">
                Join thousands of teams already using PM System to deliver projects faster.
            </p>
            <div class="d-flex gap-3 justify-content-center">
                <a href="{{ route('register') }}" class="btn btn-light btn-lg px-5">
                    <i class="bi bi-rocket-takeoff me-2"></i>Start Free
                </a>
                <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg px-5">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="landing-footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5 class="footer-title">
                        <i class="bi bi-kanban-fill me-2"></i>PM System
                    </h5>
                    <p class="mb-4" style="color: #94a3b8;">
                        Professional project management solution for modern teams.
                    </p>
                    <div style="color: #64748b;">
                        <p class="mb-0">&copy; {{ date('Y') }} PM System</p>
                        <small>All rights reserved.</small>
                    </div>
                </div>
                <div class="col-lg-2 col-6 mb-4 mb-lg-0">
                    <h6 class="footer-title">Product</h6>
                    <ul class="footer-links">
                        <li><a href="#features">Features</a></li>
                        <li><a href="#">Pricing</a></li>
                        <li><a href="#">Security</a></li>
                        <li><a href="#">Updates</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-6 mb-4 mb-lg-0">
                    <h6 class="footer-title">Company</h6>
                    <ul class="footer-links">
                        <li><a href="#">About</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-6 mb-4 mb-lg-0">
                    <h6 class="footer-title">Resources</h6>
                    <ul class="footer-links">
                        <li><a href="#">Documentation</a></li>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">API</a></li>
                        <li><a href="#">Community</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-6">
                    <h6 class="footer-title">Legal</h6>
                    <ul class="footer-links">
                        <li><a href="#">Privacy</a></li>
                        <li><a href="#">Terms</a></li>
                        <li><a href="#">Cookies</a></li>
                        <li><a href="#">License</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const offsetTop = target.offsetTop - 80;
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>
<body>
    <!-- Navbar -->
    <nav class="landing-navbar">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <a class="navbar-brand" href="{{ route('landing') }}">
                    <i class="bi bi-kanban-fill me-2"></i>PM System
                </a>
                <div class="d-flex align-items-center gap-3">
                    @guest
                        <a href="{{ route('login') }}" class="btn btn-outline-custom">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-primary-custom">
                            <i class="bi bi-person-plus me-1"></i>Get Started
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="btn btn-primary-custom">
                            <i class="bi bi-speedometer2 me-1"></i>Dashboard
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

  