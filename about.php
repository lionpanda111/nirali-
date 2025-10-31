<?php
// Set the page title and meta description for header
$page_title = 'About Us';
$meta_description = 'Learn about Nirali Makeup Studio - your premier destination for professional makeup services. Meet our talented team and discover our story.';
$meta_keywords = 'about us, makeup studio, beauty services, professional makeup artists, bridal makeup, party makeup';

// Include header
include 'includes/header.php';
?>

<!-- Page Header -->
<section class="page-header bg-light" style="background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('assets/images/hero-1.jpg') no-repeat center center/cover; color: #fff; padding: 120px 0; margin-top: -20px; position: relative; overflow: hidden;">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="display-4 fw-bold mb-3" data-aos="fade-up">About Nirali Makeup Studio</h1>
                <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">Transforming beauty with passion and precision since 2016</p>
               
            </div>
        </div>
    </div>
    <!-- Removed wave shape for cleaner design -->
</section>

<!-- About Section -->
<section class="py-5" style="background-color: #ffffff;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8" data-aos="fade-up">
                <div class="text-center mb-5">
                    <h2 class="display-5 fw-bold mb-4" style="color: #2c3e50;">Welcome to Nirali Makeup Studio</h2>
                    <div class="divider mx-auto" style="width: 80px; height: 3px; background: #ffc107; margin: 0 auto 20px;"></div>
                    <p class="text-muted mb-0">Where Beauty Meets Artistry Since 2018</p>
                </div>
                
                <div class="text-center mb-5">
                    <p class="lead mb-4" style="line-height: 1.8; color: #555;">
                        Welcome to Nirali Makeup Studio, where beauty meets artistry and every face tells a story. Founded in 2018 by Nirali Patel, our studio has been transforming clients with exceptional makeup artistry and personalized beauty solutions in a comfortable, welcoming environment.
                    </p>
                    <p class="mb-4" style="line-height: 1.8; color: #555;">
                        What began as a small studio has blossomed into a premier beauty destination, renowned for our meticulous attention to detail and unwavering commitment to excellence.
                    </p>
                </div>

                <div class="row g-4 mt-5">
                    <div class="col-md-4 text-center">
                        <div class="p-4 rounded-3 h-100" style="background-color: #fff8e6; border-left: 4px solid #ffc107;">
                            <i class="fas fa-palette fa-2x mb-3" style="color: #ffc107;"></i>
                            <h5 class="fw-bold mb-2">Creative Artistry</h5>
                            <p class="text-muted mb-0">Innovative makeup techniques tailored to enhance your natural beauty</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="p-4 rounded-3 h-100" style="background-color: #fff8e6; border-left: 4px solid #ffc107;">
                            <i class="fas fa-spa fa-2x mb-3" style="color: #ffc107;"></i>
                            <h5 class="fw-bold mb-2">Premium Experience</h5>
                            <p class="text-muted mb-0">Luxurious treatments with high-quality, cruelty-free products</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="p-4 rounded-3 h-100" style="background-color: #fff8e6; border-left: 4px solid #ffc107;">
                            <i class="fas fa-heart fa-2x mb-3" style="color: #ffc107;"></i>
                            <h5 class="fw-bold mb-2">Personalized Care</h5>
                            <p class="text-muted mb-0">Customized beauty solutions for your unique style and needs</p>
                        </div>
                    </div>
                </div>

                
            </div>
        </div>
    </div>
</section>
<!-- Why Choose Us Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center" data-aos="fade-up">
                <h2 class="mb-3">The Nirali Difference</h2>
                <p class="lead">We go above and beyond to make your experience exceptional</p>
            </div>
        </div>
        
        <!-- Image Slider -->
        <div class="row justify-content-center mb-5">
    <div class="col-lg-10" data-aos="fade-up">
        <div id="niraliSlider" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="3000">
            <div class="carousel-inner rounded-3 overflow-hidden shadow-lg">
                <div class="carousel-item active">
                    <img src="assets/images/hero-1.jpg" class="d-block w-100" alt="Nirali Makeup Studio" style="height: 500px; object-fit: cover;">
                    <div class="carousel-caption d-none d-md-block" style="background: rgba(0,0,0,0.5); border-radius: 10px; padding: 20px;">
                        <h3>Experience the Nirali Difference</h3>
                        <p>Where every detail matters and every client is special</p>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="assets/images/hero-3.jpg" class="d-block w-100" alt="Nirali Makeup Studio" style="height: 500px; object-fit: cover;">
                    <div class="carousel-caption d-none d-md-block" style="background: rgba(0,0,0,0.5); border-radius: 10px; padding: 20px;">
                        <h3>Professional Excellence</h3>
                        <p>Delivering exceptional results with every service</p>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="assets/images/about.png" class="d-block w-100" alt="About Nirali Makeup Studio" style="height: 500px; object-fit: cover;">
                    <div class="carousel-caption d-none d-md-block" style="background: rgba(0,0,0,0.5); border-radius: 10px; padding: 20px;">
                        <h3>Our Story</h3>
                        <p>Transforming beauty with passion and precision since 2018</p>
                    </div>
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#niraliSlider" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#niraliSlider" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
</div>
        
        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 border-0 shadow-sm hover-effect">
                    <div class="card-body text-center p-4">
                        <div class="icon-box bg-warning bg-opacity-10 text-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                            <i class="fas fa-palette fa-2x"></i>
                        </div>
                        <h4>Creative Artistry</h4>
                        <p class="text-muted">Our talented artists stay updated with the latest trends and techniques to create stunning looks that enhance your natural beauty.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 border-0 shadow-sm hover-effect">
                    <div class="card-body text-center p-4">
                        <div class="icon-box bg-warning bg-opacity-10 text-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                            <i class="fas fa-spa fa-2x"></i>
                        </div>
                        <h4>Premium Products</h4>
                        <p class="text-muted">We use only the highest quality, professional-grade makeup products that are gentle on your skin and deliver flawless results.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100 border-0 shadow-sm hover-effect">
                    <div class="card-body text-center p-4">
                        <div class="icon-box bg-warning bg-opacity-10 text-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                            <i class="fas fa-heart fa-2x"></i>
                        </div>
                        <h4>Personalized Service</h4>
                        <p class="text-muted">Every client receives personalized attention and customized makeup solutions tailored to their unique features and preferences.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- Our Studio Section -->
<section class="py-5" style="background-color: #f8f9fa;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 " data-aos="fade-up">
                <h2 class="mb-4 text-center">A Tranquil Beauty Haven</h2>
                <p class="mb-5 text-center mx-auto" style="max-width: 800px;">Step into our elegant and serene studio, designed to provide you with the ultimate beauty experience. Our space is thoughtfully curated to make you feel comfortable and pampered from the moment you arrive.</p>
                
                <div class="row g-4 justify-content-center">
                    <div class="col-md-5">
                        <div class="d-flex align-items-center p-4 bg-white rounded-3 shadow-sm h-100">
                            <div class="me-4 text-warning">
                                <i class="fas fa-check-circle fa-lg"></i>
                            </div>
                            <div>
                                <h5 class="mb-1 fw-bold">Luxurious Ambiance</h5>
                                <p class="mb-0 text-muted">Relax in our beautifully designed space</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="d-flex align-items-center p-4 bg-white rounded-3 shadow-sm h-100">
                            <div class="me-4 text-warning">
                                <i class="fas fa-check-circle fa-lg"></i>
                            </div>
                            <div>
                                <h5 class="mb-1 fw-bold">Comfortable Seating</h5>
                                <p class="mb-0 text-muted">Ergonomic chairs for your comfort</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="d-flex align-items-center p-4 bg-white rounded-3 shadow-sm h-100">
                            <div class="me-4 text-warning">
                                <i class="fas fa-check-circle fa-lg"></i>
                            </div>
                            <div>
                                <h5 class="mb-1 fw-bold">Natural Lighting</h5>
                                <p class="mb-0 text-muted">Perfect for accurate color matching</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="d-flex align-items-center p-4 bg-white rounded-3 shadow-sm h-100">
                            <div class="me-4 text-warning">
                                <i class="fas fa-check-circle fa-lg"></i>
                            </div>
                            <div>
                                <h5 class="mb-1 fw-bold">Private Rooms</h5>
                                <p class="mb-0 text-muted">For bridal and special occasion makeup</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 row justify-content-center">
                    
                    <a href="contact.php" class="btn btn-warning">Get Directions</a>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- Testimonial Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center mb-5" data-aos="fade-up">
                <h2 class="mb-3">What Our Clients Say</h2>
                <p class="lead">Don't just take our word for it - hear from our satisfied clients.</p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="testimonial-card h-100 p-4 bg-white rounded-3 shadow-sm d-flex flex-column">
                    <div class="mb-4">
                        <i class="fas fa-quote-left text-warning fa-2x mb-3 d-block"></i>
                        <p class="mb-4">"Nirali Makeup Studio transformed me for my wedding day! The team was professional, and the makeup stayed flawless all night. Highly recommended!"</p>
                    </div>
                    <div class="mt-auto text-center">
                        <h5 class="mb-1">Neha Mehta</h5>
                        <p class="text-muted mb-0">Bride</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card h-100 p-4 bg-white rounded-3 shadow-sm d-flex flex-column">
                    <div class="mb-4">
                        <i class="fas fa-quote-left text-warning fa-2x mb-3 d-block"></i>
                        <p class="mb-4">"I've been going to Nirali for all my special occasions. Their attention to detail and understanding of different skin tones is remarkable."</p>
                    </div>
                    <div class="mt-auto text-center">
                        <h5 class="mb-1">Riya Kapoor</h5>
                        <p class="text-muted mb-0">Regular Client</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card h-100 p-4 bg-white rounded-3 shadow-sm d-flex flex-column">
                    <div class="mb-4">
                        <i class="fas fa-quote-left text-warning fa-2x mb-3 d-block"></i>
                        <p class="mb-4">"The team at Nirali Makeup Studio is incredibly talented. They made me feel comfortable and beautiful for my graduation. Will definitely be back!"</p>
                    </div>
                    <div class="mt-auto text-center">
                        <h5 class="mb-1">Ayesha Khan</h5>
                        <p class="text-muted mb-0">Graduate</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


  



<style>
/* Custom Styles */
.wave-shape {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    overflow: hidden;
    line-height: 0;
    transform: rotate(180deg);
}

.wave-shape svg {
    position: relative;
    display: block;
    width: calc(100% + 1.3px);
    height: 80px;
}

.hover-effect {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-effect:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
}

.team-card .team-img {
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

.team-card .team-img img {
    transition: transform 0.5s ease;
}

.team-card:hover .team-img img {
    transform: scale(1.1);
}

.team-social {
    position: absolute;
    bottom: -50px;
    left: 0;
    right: 0;
    background: rgba(0, 0, 0, 0.7);
    padding: 15px 0;
    transition: all 0.3s ease;
}

.team-card:hover .team-social {
    bottom: 0;
}

.icon-box {
    transition: all 0.3s ease;
}

.card:hover .icon-box {
    background-color: var(--bs-warning) !important;
    color: white !important;
}

.back-to-top {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 50px;
    height: 50px;
    background: var(--bs-warning);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    z-index: 999;
}

.back-to-top.visible {
    opacity: 1;
    visibility: visible;
}

.back-to-top:hover {
    background: var(--bs-dark);
    color: white;
}

.bg-pattern {
    background-image: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M20 20c0-2.21 1.79-4 4-4s4 1.79 4 4-1.79 4-4 4-4-1.79-4-4zM0 0h40v40H0V0z' fill='%23ffffff' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E");
}
</style>

<script>
// Back to Top Button
window.addEventListener('scroll', function() {
    var backToTop = document.getElementById('backToTop');
    if (window.pageYOffset > 300) {
        backToTop.classList.add('visible');
    } else {
        backToTop.classList.remove('visible');
    }
});

document.getElementById('backToTop').addEventListener('click', function(e) {
    e.preventDefault();
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});

// Initialize AOS Animation
AOS.init({
    duration: 800,
    easing: 'ease-in-out',
    once: true
});
</script>

<?php
// Include footer
include 'includes/footer.php';
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize carousel with autoplay
    var myCarousel = document.querySelector('#niraliSlider');
    var carousel = new bootstrap.Carousel(myCarousel, {
        interval: 3000, // 3 seconds
        ride: 'carousel'
    });
});
</script>