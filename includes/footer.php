    <footer class="footer mt-auto py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="text-light mb-3">About Us</h5>
                    <p class="text-light opacity-75">We are committed to providing secure and efficient banking services to our customers. Our modern platform makes managing your finances easier than ever.</p>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="text-light mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="index.php" class="text-light text-decoration-none opacity-75 hover-opacity-100">
                                <i class="fas fa-home me-2"></i>Home
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="about.php" class="text-light text-decoration-none opacity-75 hover-opacity-100">
                                <i class="fas fa-info-circle me-2"></i>About
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="contact.php" class="text-light text-decoration-none opacity-75 hover-opacity-100">
                                <i class="fas fa-envelope me-2"></i>Contact
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="privacy.php" class="text-light text-decoration-none opacity-75 hover-opacity-100">
                                <i class="fas fa-shield-alt me-2"></i>Privacy Policy
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5 class="text-light mb-3">Contact Info</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2 text-light opacity-75">
                            <i class="fas fa-map-marker-alt me-2"></i>123 Banking Street, City
                        </li>
                        <li class="mb-2 text-light opacity-75">
                            <i class="fas fa-phone me-2"></i>+1 234 567 890
                        </li>
                        <li class="mb-2 text-light opacity-75">
                            <i class="fas fa-envelope me-2"></i>support@bankingsystem.com
                        </li>
                    </ul>
                    <div class="social-links mt-3">
                        <a href="#" class="text-light me-3 opacity-75 hover-opacity-100">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-light me-3 opacity-75 hover-opacity-100">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-light me-3 opacity-75 hover-opacity-100">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="#" class="text-light opacity-75 hover-opacity-100">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
            </div>
            <hr class="my-4 border-light opacity-25">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <p class="text-light opacity-75 mb-0">&copy; <?php echo date('Y'); ?> Banking System. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="text-light opacity-75 mb-0">Designed with <i class="fas fa-heart text-danger"></i> for modern banking</p>
                </div>
            </div>
        </div>
    </footer>

    <style>
    .footer {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .hover-opacity-100:hover {
        opacity: 1 !important;
        transition: opacity 0.3s ease;
    }

    .social-links a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        transition: all 0.3s ease;
    }

    .social-links a:hover {
        background: var(--accent-color);
        transform: translateY(-3px);
    }

    @media (max-width: 768px) {
        .footer {
            text-align: center;
        }
        
        .social-links {
            justify-content: center;
        }
    }
    </style>
    </div><!-- /.container -->
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
