<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banking System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #1a1a2e;
            --secondary-color: #0f3460;
            --accent-color: #e94560;
            --text-light: #ffffff;
            --text-dark: #16213e;
            --overlay-color: rgba(0, 0, 0, 0.7);
            --card-bg: rgba(255, 255, 255, 0.1);
            --card-border: rgba(255, 255, 255, 0.2);
        }

        /* Custom Animations */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        @keyframes slideIn {
            from { transform: translateX(-100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes fadeInUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--primary-color);
            color: var(--text-light);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Navbar Styles */
        .navbar {
            background: rgba(26, 26, 46, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 0;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            background: rgba(26, 26, 46, 0.98);
            padding: 0.5rem 0;
        }

        .navbar-brand {
            font-weight: 600;
            color: var(--text-light) !important;
            font-size: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .navbar-brand::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--accent-color);
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        .navbar-brand:hover::after {
            transform: translateX(0);
        }

        .navbar-brand img {
            height: 40px;
            margin-right: 10px;
        }

        .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            transition: all 0.3s ease;
            position: relative;
            margin: 0 0.2rem;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--accent-color);
            z-index: -1;
            transform: scaleX(0);
            transform-origin: right;
            transition: transform 0.3s ease;
            border-radius: 4px;
        }

        .nav-link:hover::before {
            transform: scaleX(1);
            transform-origin: left;
        }

        /* Welcome Section */
        .welcome-section {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 6rem 0;
            margin-bottom: 0;
            overflow: hidden;
        }

        .welcome-content {
            position: relative;
            z-index: 2;
            animation: fadeInUp 1s ease-out;
        }

        .welcome-section h1 {
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            background: linear-gradient(45deg, var(--text-light), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: pulse 2s infinite;
        }

        .welcome-section p {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 2rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
            animation: fadeInUp 1s ease-out 0.3s backwards;
        }

        /* Feature Cards */
        .features-section {
            position: relative;
            background: rgba(15, 52, 96, 0.3);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin-top: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            transform-style: preserve-3d;
            perspective: 1000px;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                45deg,
                transparent,
                rgba(255, 255, 255, 0.1),
                transparent
            );
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px) rotateX(5deg);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border-color: var(--accent-color);
        }

        .feature-card:hover::before {
            transform: translateX(100%);
        }

        .feature-icon {
            font-size: 3rem;
            color: var(--accent-color);
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            animation: float 3s ease-in-out infinite;
            transform: translateZ(20px);
        }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(45deg, var(--accent-color), #ff6b6b);
            border: none;
            padding: 1rem 2.5rem;
            font-weight: 500;
            font-size: 1.1rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 50px;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, #ff6b6b, var(--accent-color));
            z-index: -1;
            transition: opacity 0.4s ease;
            opacity: 0;
        }

        .btn-primary:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 10px 20px rgba(233, 69, 96, 0.3);
        }

        .btn-primary:hover::before {
            opacity: 1;
        }

        .btn-light {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--text-light);
            padding: 1rem 2.5rem;
            font-weight: 500;
            font-size: 1.1rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 50px;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
        }

        .btn-light::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(-100%);
            transition: transform 0.4s ease;
        }

        .btn-light:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 255, 255, 0.1);
        }

        .btn-light:hover::before {
            transform: translateX(0);
        }

        /* Loading Animation */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--primary-color);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: opacity 0.5s ease-out;
        }

        .loading-overlay.fade-out {
            opacity: 0;
            pointer-events: none;
        }

        .loader {
            width: 50px;
            height: 50px;
            border: 3px solid var(--accent-color);
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .welcome-section h1 {
                font-size: 2.5rem;
            }
            .welcome-section p {
                font-size: 1rem;
            }
            .feature-card {
                margin-bottom: 1rem;
            }
            .features-section {
                padding: 1rem;
            }
        }

        /* Update the slide styles to ensure images are visible */
        .hero-slider {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            z-index: -1;
            background-color: var(--primary-color);
            overflow: hidden;
        }

        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1.5s ease-in-out;
            z-index: 1;
        }

        .slide.active {
            opacity: 1;
            z-index: 2;
        }

        .slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 0;
        }

        .slide::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                to bottom,
                rgba(26, 26, 46, 0.65),
                rgba(15, 52, 96, 0.75)
            );
            z-index: 1;
        }

        /* Add styles for admin buttons */
        .admin-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
            margin: 2rem 0;
        }

        .admin-button {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--text-light);
            padding: 1.5rem 2rem;
            font-weight: 500;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            border-radius: 10px;
            text-align: center;
            min-width: 200px;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 0.5rem;
            height: 120px;
        }

        .admin-button:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            color: var(--text-light);
        }

        .admin-button i {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .admin-button span {
            text-align: center;
            line-height: 1.4;
            display: block;
            width: 100%;
        }

        /* Update filter button styles */
        .filter-button {
            padding: 0.5rem 1.5rem;
            font-size: 0.9rem;
            background: var(--accent-color);
            border: none;
            color: white;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .filter-button:hover {
            background: #d13a52;
            transform: translateY(-2px);
        }

        /* Update table button styles */
        .table .btn-group .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        /* Update card header styles */
        .card-header {
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .card-title {
            margin: 0;
            padding: 0.5rem 0;
            font-size: 1.5rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay">
        <div class="loader"></div>
    </div>

    <!-- Image Slider -->
    <div class="hero-slider">
        <div class="slide active">
            <img src="/images/hero-bg.jpg" alt="Hero Background" onerror="this.onerror=null; this.src='images/hero-bg.jpg';">
        </div>
        <div class="slide">
            <img src="/images/banking-bg.jpg" alt="Banking Background" onerror="this.onerror=null; this.src='images/banking-bg.jpg';">
        </div>
        <div class="slide">
            <img src="/images/finance-bg.jpg" alt="Finance Background" onerror="this.onerror=null; this.src='images/finance-bg.jpg';">
        </div>
        <div class="slide">
            <img src="/images/modern-banking.jpg" alt="Modern Banking" onerror="this.onerror=null; this.src='images/modern-banking.jpg';">
        </div>
        <div class="slide">
            <img src="/images/secure-banking.jpg" alt="Secure Banking" onerror="this.onerror=null; this.src='images/secure-banking.jpg';">
        </div>
    </div>

    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="images/bank-logo.png" alt="Bank Logo" class="d-inline-block align-top">
                Banking System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i>Home</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user me-1"></i><?php echo ucfirst($_SESSION['name'] ?? 'User'); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                        <?php if($_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item"><a class="nav-link" href="manage_users.php"><i class="fas fa-users me-1"></i>Manage Users</a></li>
                            <li class="nav-item"><a class="nav-link" href="reports.php"><i class="fas fa-chart-bar me-1"></i>Reports</a></li>
                        <?php endif; ?>
                        <?php if($_SESSION['role'] === 'collector'): ?>
                            <li class="nav-item"><a class="nav-link" href="collect_payments.php"><i class="fas fa-money-bill me-1"></i>Collect Payments</a></li>
                            <li class="nav-item"><a class="nav-link" href="collector_payment_history.php"><i class="fas fa-history me-1"></i>Payment History</a></li>
                        <?php endif; ?>
                        <?php if($_SESSION['role'] === 'customer'): ?>
                            <li class="nav-item"><a class="nav-link" href="customer_dashboard.php"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link" href="apply_loan.php"><i class="fas fa-file-signature me-1"></i>Apply for Loan</a></li>
                            <li class="nav-item"><a class="nav-link" href="myloans.php"><i class="fas fa-list me-1"></i>My Loans</a></li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php#login-form"><i class="fas fa-sign-in-alt me-1"></i>Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="register.php#register-form"><i class="fas fa-user-plus me-1"></i>Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <?php if(!isset($_SESSION['user_id'])): ?>
    <div class="welcome-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 welcome-content">
                    <h1 class="display-4 fw-bold mb-4 animate__animated animate__fadeInUp">Welcome to Modern Banking</h1>
                    <p class="lead mb-4 animate__animated animate__fadeInUp animate__delay-1s">Experience the future of banking with our secure, efficient, and user-friendly platform. Get started today and discover a new way to manage your finances.</p>
                    <div class="d-flex gap-3 animate__animated animate__fadeInUp animate__delay-2s">
                        <a href="register.php" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>Get Started
                        </a>
                        <a href="login.php" class="btn btn-light">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="features-section animate__animated animate__fadeInRight animate__delay-3s">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="feature-card text-center">
                                    <i class="fas fa-hand-holding-usd feature-icon"></i>
                                    <h4>Easy Loans</h4>
                                    <p class="text-light">Quick approval process with competitive rates</p>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="feature-card text-center">
                                    <i class="fas fa-shield-alt feature-icon"></i>
                                    <h4>Secure Transactions</h4>
                                    <p class="text-light">Bank-grade security for all your transactions</p>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="feature-card text-center">
                                    <i class="fas fa-clock feature-icon"></i>
                                    <h4>24/7 Support</h4>
                                    <p class="text-light">Round-the-clock customer service</p>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="feature-card text-center">
                                    <i class="fas fa-mobile-alt feature-icon"></i>
                                    <h4>Mobile Banking</h4>
                                    <p class="text-light">Bank anywhere, anytime</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="container mt-4">

    <script>
    // Add this debugging code at the start of your script
    document.addEventListener('DOMContentLoaded', function() {
        // Check if images are loading
        const slides = document.querySelectorAll('.slide img');
        slides.forEach((img, index) => {
            console.log(`Checking image ${index + 1}:`, img.src);
            img.onload = function() {
                console.log(`Image ${index + 1} loaded successfully:`, this.src);
            };
            img.onerror = function() {
                console.error(`Failed to load image ${index + 1}:`, this.src);
                // Try alternative path
                const currentSrc = this.src;
                const newSrc = currentSrc.startsWith('/') ? currentSrc.substring(1) : '/' + currentSrc;
                console.log(`Trying alternative path:`, newSrc);
                this.src = newSrc;
            };
        });
    });

    // Update the slider script
    document.addEventListener('DOMContentLoaded', function() {
        const slides = document.querySelectorAll('.slide');
        let currentSlide = 0;
        let isTransitioning = false;

        // Preload images
        slides.forEach(slide => {
            const img = slide.querySelector('img');
            if (img) {
                img.onerror = function() {
                    console.error('Failed to load image:', this.src);
                };
                img.onload = function() {
                    console.log('Successfully loaded image:', this.src);
                };
            }
        });

        function nextSlide() {
            if (isTransitioning) return;
            isTransitioning = true;

            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');

            setTimeout(() => {
                isTransitioning = false;
            }, 1500);
        }

        // Change slide every 5 seconds
        setInterval(nextSlide, 5000);
    });

    // Loading Overlay
    window.addEventListener('load', function() {
        const overlay = document.querySelector('.loading-overlay');
        overlay.classList.add('fade-out');
        setTimeout(() => {
            overlay.style.display = 'none';
        }, 500);
    });

    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // Enhanced hover effects for feature cards
    document.querySelectorAll('.feature-card').forEach(card => {
        card.addEventListener('mousemove', function(e) {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const rotateX = (y - centerY) / 10;
            const rotateY = -(x - centerX) / 10;
            
            card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(10px)`;
        });

        card.addEventListener('mouseleave', function() {
            card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateZ(0)';
        });
    });

    // Smooth scroll for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Add animation classes to elements
    document.addEventListener('DOMContentLoaded', function() {
        const elements = document.querySelectorAll('.feature-card, .welcome-content > *');
        elements.forEach((element, index) => {
            element.classList.add('animate-on-scroll');
            element.style.animationDelay = `${index * 0.2}s`;
        });
    });
    </script>
</body>
</html> 
