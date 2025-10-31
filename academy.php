<?php
// Set page title and meta description
$page_title = 'Nirali Makeup Academy - Learn Professional Makeup Artistry';
$meta_description = 'Join Nirali Makeup Academy to learn professional makeup techniques from industry experts. Download our mobile app for course details, tutorials, and more.';
$meta_keywords = 'makeup academy, makeup courses, beauty school, professional makeup training, makeup artist course, beauty education';

// Include config and functions
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Handle video upload
$upload_message = '';
$upload_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['academy_video'])) {
    $video_dir = 'uploads/videos/academy/';
    
    // Create directory if it doesn't exist
    if (!file_exists($video_dir)) {
        mkdir($video_dir, 0777, true);
    }
    
    $file = $_FILES['academy_video'];
    $file_name = basename($file['name']);
    $target_path = $video_dir . $file_name;
    $file_type = strtolower(pathinfo($target_path, PATHINFO_EXTENSION));
    
    // Check if file is a valid video
    $valid_extensions = array('mp4', 'webm', 'ogg');
    $max_size = 50 * 1024 * 1024; // 50MB
    
    if (in_array($file_type, $valid_extensions)) {
        if ($file['size'] <= $max_size) {
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                $upload_message = 'Video uploaded successfully!';
                $upload_success = true;
            } else {
                $upload_message = 'Error uploading file.';
            }
        } else {
            $upload_message = 'File is too large. Maximum size is 50MB.';
        }
    } else {
        $upload_message = 'Only MP4, WebM, and OGG files are allowed.';
    }
}

// Include header
include 'includes/header.php';
?>


<!-- Hero Section -->
<section class="py-5 position-relative overflow-hidden" style="background: linear-gradient(135deg, #fff8f8 0%, #fff0f5 100%);">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-6 pe-lg-5" data-aos="fade-right">
                <h1 class="display-4 fw-bold mb-4" style="color: #2c3e50; line-height: 1.3;">Nirali Makeup Academy</h1>
                <p class="lead mb-4" style="color: #555; font-size: 1.25rem; line-height: 1.6;">
                    Transform your passion for beauty into a successful career with our professional makeup courses.
                </p>
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <a href="#download-app" class="btn btn-primary btn-lg px-4 py-2" style="background: linear-gradient(45deg, #ff6b6b, #ff8e8e); border: none; font-weight: 600;">
                        <i class="fas fa-download me-2"></i> Download App
                    </a>
                    <a href="#courses" class="btn btn-outline-primary btn-lg px-4 py-2" style="border-color: #ff6b6b; color: #ff6b6b; font-weight: 500;">
                        View Courses
                    </a>
                </div>
                <div class="d-flex align-items-center text-muted">
                    <i class="fas fa-star text-warning me-2"></i>
                    <span>4.9/5.0 from 200+ student reviews</span>
                </div>
            </div>
            <div class="col-lg-6 mt-5 mt-lg-0" data-aos="fade-left" data-aos-delay="200">
                <div class="position-relative">
                    <img src="assets/images/hero-2.jpg" alt="Makeup Academy" class="img-fluid rounded-4 shadow-lg" 
                         style="border: 10px solid white; box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;">
                    <div class="position-absolute top-0 start-0 w-100 h-100 rounded-4" 
                         style="background: linear-gradient(45deg, rgba(255,107,107,0.1) 0%, rgba(255,142,142,0.1) 100%);
                                transform: rotate(5deg) scale(0.98);
                                z-index: -1;">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Decorative Elements -->
    <div class="position-absolute top-0 end-0 w-100 h-100" style="z-index: -2; overflow: hidden;">
        <div class="position-absolute" style="width: 600px; height: 600px; background: radial-gradient(circle, rgba(255,107,107,0.1) 0%, rgba(255,142,142,0) 70%); top: -200px; right: -200px;"></div>
    </div>
</section>

<!-- Why Choose Our Academy -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center mb-5" data-aos="fade-up">
                <h2 class="mb-3">Why Choose <span class="text-primary">Our Academy</span></h2>
                <p class="lead">Discover what makes our makeup academy the perfect choice for your beauty education</p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 border-0 shadow-sm p-4 d-flex flex-column">
                    <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-circle mx-auto mb-4" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-chalkboard-teacher fa-2x"></i>
                    </div>
                    <h4 class="text-center mb-3">Expert Instructors</h4>
                    <p class="text-center text-muted mb-0 flex-grow-1">Learn from industry professionals with years of experience in makeup artistry and beauty education.</p>
                </div>
            </div>
            
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 border-0 shadow-sm p-4 d-flex flex-column">
                    <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-circle mx-auto mb-4" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-hands-helping fa-2x"></i>
                    </div>
                    <h4 class="text-center mb-3">Hands-on Training</h4>
                    <p class="text-center text-muted mb-0 flex-grow-1">Get practical, hands-on experience with professional makeup products and tools.</p>
                </div>
            </div>
            
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100 border-0 shadow-sm p-4 d-flex flex-column">
                    <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-circle mx-auto mb-4" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-certificate fa-2x"></i>
                    </div>
                    <h4 class="text-center mb-3">Certification</h4>
                    <p class="text-center text-muted mb-0 flex-grow-1">Earn a recognized certification upon course completion to boost your professional credibility.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include 'includes/courses-section.php'; ?>

<!-- Download App Section -->
<section id="download-app" class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                <div class="position-relative" style="max-width: 350px; margin: 0 auto;">
                    <div class="position-relative" style="padding: 25px; background: #fff; border-radius: 40px; box-shadow: 0 15px 40px rgba(0,0,0,0.1); transition: transform 0.3s ease;">
                       <img src="assets/images/APP.png" alt="Nirali Makeup Academy App" class="img-fluid rounded-4 shadow-sm" style="width: 100%; height: auto; max-height: 600px; object-fit: contain;">
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <h2 class="mb-4">Download Our <span class="text-primary">Mobile App</span></h2>
                <p class="lead mb-4">Take your learning experience to the next level with our mobile application.</p>
                
                <div class="app-features mb-5">
                    <div class="d-flex mb-3">
                        <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-circle me-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas fa-video"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Video Tutorials</h5>
                            <p class="text-muted mb-0">Access hundreds of step-by-step makeup tutorials from our expert instructors.</p>
                        </div>
                    </div>
                    
                    <div class="d-flex mb-3">
                        <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-circle me-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Class Schedule</h5>
                            <p class="text-muted mb-0">Manage your class schedule, receive reminders, and track your progress.</p>
                        </div>
                    </div>
                    
                    <div class="d-flex">
                        <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-circle me-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas fa-comments"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Community Support</h5>
                            <p class="text-muted mb-0">Connect with fellow students and instructors in our exclusive community.</p>
                        </div>
                    </div>
                </div>
                
                <div class="download-buttons">
                    <p class="mb-3">Download the app now and start your beauty journey today!</p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="https://apps.apple.com/in/app/myinstitute/id1472483563" class="btn btn-dark btn-lg px-4">
                            <i class="fab fa-apple me-2"></i> App Store
                        </a>
                        <a href="https://play.google.com/store/apps/details?id=co.haward.acuhd" class="btn btn-dark btn-lg px-4">
                            <i class="fab fa-google-play me-2"></i> Google Play
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<!-- Reels Style Video Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 text-center mb-4" data-aos="fade-up">
                <h2 class="mb-3">Makeup <span class="text-primary">Reels</span></h2>
                <p class="lead">Watch our latest makeup tutorials and transformations</p>
            </div>
        </div>
        
        <div class="reels-container">
            <div class="reels-wrapper" id="reelsWrapper">
                <!-- Reel 1 -->
                <div class="reel-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="reel-video-container">
                        <video class="reel-video" autoplay loop muted playsinline preload="auto" poster="assets/images/1.png" webkit-playsinline="true" x5-playsinline="true" x5-video-player-type="h5" x5-video-player-fullscreen="true">
                            <source src="assets/video/1.mp4" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                        <div class="reel-overlay">
                            <button class="play-btn">
                                <i class="fas fa-play"></i>
                            </button>
                            <div class="reel-actions">
                                <button class="action-btn">
                                    <i class="fas fa-heart"></i>
                                    <span>2.4k</span>
                                </button>
                                <button class="action-btn">
                                    <i class="fas fa-comment"></i>
                                    <span>124</span>
                                </button>
                                <button class="action-btn">
                                    <i class="fas fa-share"></i>
                                    <span>Share</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="reel-caption">
                        <h4>Professional Makeup Techniques</h4>
                        <p>Learn advanced makeup techniques from our expert instructors. #MakeupTutorial #BeautyTips</p>
                    </div>
                </div>
                
                <!-- Reel 2 -->
                <div class="reel-card" data-aos="fade-up" data-aos-delay="150">
                    <div class="reel-video-container">
                        <video class="reel-video" autoplay loop muted playsinline preload="auto" poster="assets/images/video-thumb-2.jpg" webkit-playsinline="true" x5-playsinline="true" x5-video-player-type="h5" x5-video-player-fullscreen="true">
                            <source src="assets/video/2.mp4" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                        <div class="reel-overlay">
                            <button class="play-btn">
                                <i class="fas fa-play"></i>
                            </button>
                            <div class="reel-actions">
                                <button class="action-btn">
                                    <i class="fas fa-heart"></i>
                                    <span>1.8k</span>
                                </button>
                                <button class="action-btn">
                                    <i class="fas fa-comment"></i>
                                    <span>98</span>
                                </button>
                                <button class="action-btn">
                                    <i class="fas fa-share"></i>
                                    <span>Share</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="reel-caption">
                        <h4>Bridal Makeup Masterclass</h4>
                        <p>Discover the secrets to flawless bridal makeup. #BridalMakeup #WeddingSeason</p>
                    </div>
                </div>
                
                <!-- Reel 2 -->
                <div class="reel-card" data-aos="fade-up" data-aos-delay="150">
                    <div class="reel-video-container">
                        <video class="reel-video" autoplay loop muted playsinline preload="auto" poster="assets/images/2.png" webkit-playsinline="true" x5-playsinline="true" x5-video-player-type="h5" x5-video-player-fullscreen="true">
                            <source src="assets/video/3.mp4" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                        <div class="reel-overlay">
                            <button class="play-btn">
                                <i class="fas fa-play"></i>
                            </button>
                            <div class="reel-actions">
                                <button class="action-btn">
                                    <i class="fas fa-heart"></i>
                                    <span>1.8k</span>
                                </button>
                                <button class="action-btn">
                                    <i class="fas fa-comment"></i>
                                    <span>98</span>
                                </button>
                                <button class="action-btn">
                                    <i class="fas fa-share"></i>
                                    <span>Share</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="reel-caption">
                        <h4>Bridal Makeup Masterclass</h4>
                        <p>Student’s Review of Basic Makeup Class✨
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
            .reels-container {
                max-width: 100%;
                overflow: hidden;
            }
            .reels-wrapper {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 1.5rem;
                padding: 1rem 0;
            }
            .reel-card {
                background: #fff;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 4px 15px rgba(0,0,0,0.05);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
            .reel-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            }
            .reel-video-container {
                position: relative;
                width: 100%;
                padding-bottom: 177.78%; /* 9:16 aspect ratio */
                background: #000;
                cursor: pointer;
            }
            .reel-video {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            .reel-overlay {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                background: rgba(0,0,0,0.1);
                opacity: 0;
                transition: opacity 0.3s ease, background 0.3s ease;
                pointer-events: none;
            }
            .reel-card:hover .reel-overlay {
                opacity: 1;
                background: rgba(0,0,0,0.3);
            }
            .play-btn {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                background: rgba(255,255,255,0.9);
                border: none;
                color: var(--primary-color);
                font-size: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: transform 0.3s ease;
            }
            .play-btn:hover {
                transform: scale(1.1);
            }
            .reel-actions {
                position: absolute;
                right: 15px;
                bottom: 80px;
                display: flex;
                flex-direction: column;
                gap: 20px;
            }
            .action-btn {
                background: none;
                border: none;
                color: #fff;
                display: flex;
                flex-direction: column;
                align-items: center;
                font-size: 14px;
                cursor: pointer;
                transition: transform 0.2s ease;
            }
            .action-btn:hover {
                transform: scale(1.1);
            }
            .action-btn i {
                font-size: 24px;
                margin-bottom: 4px;
            }
            .reel-caption {
                padding: 1rem;
            }
            .reel-caption h4 {
                font-size: 1.1rem;
                margin-bottom: 0.5rem;
                color: #333;
            }
            .reel-caption p {
                font-size: 0.9rem;
                color: #666;
                margin-bottom: 0;
                line-height: 1.4;
            }
            @media (max-width: 768px) {
                .reels-wrapper {
                    grid-template-columns: 1fr;
                    max-width: 400px;
                    margin: 0 auto;
                }
                .reel-actions {
                    bottom: 20px;
                    right: 10px;
                }
            }
        </style>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const videos = document.querySelectorAll('.reel-video');
                const playButtons = document.querySelectorAll('.play-btn');
                
                // Function to force video play
                const forcePlayVideo = (video, btn) => {
                    // Make sure video is muted for autoplay
                    video.muted = true;
                    
                    // Create play promise
                    const playPromise = video.play();
                    
                    if (playPromise !== undefined) {
                        playPromise.catch(error => {
                            console.log('Autoplay prevented, showing fallback UI');
                            if (btn) btn.style.display = 'flex';
                            
                            // Add click event to document to start video on first user interaction
                            const playOnInteraction = () => {
                                video.play().then(() => {
                                    if (btn) btn.style.display = 'none';
                                    document.removeEventListener('click', playOnInteraction);
                                    document.removeEventListener('touchstart', playOnInteraction);
                                });
                            };
                            
                            document.addEventListener('click', playOnInteraction, { once: true });
                            document.addEventListener('touchstart', playOnInteraction, { once: true });
                        }).then(() => {
                            // Autoplay started successfully
                            if (btn) btn.style.display = 'none';
                        });
                    }
                };
                
                // Function to handle video play state
                const updateVideoState = (video, btn) => {
                    if (video.paused) {
                        video.play().catch(e => console.log('Play failed:', e));
                        if (btn) btn.style.display = 'none';
                    } else {
                        video.pause();
                        if (btn) btn.style.display = 'flex';
                    }
                };
                
                // Initialize each video
                playButtons.forEach((btn, index) => {
                    const video = videos[index];
                    const container = video.closest('.reel-video-container');
                    
                    // Hide play button initially
                    btn.style.display = 'none';
                    
                    // Set video attributes for autoplay
                    video.setAttribute('muted', 'muted');
                    video.setAttribute('playsinline', 'playsinline');
                    video.setAttribute('webkit-playsinline', 'webkit-playsinline');
                    
                    // Try to force play on load
                    forcePlayVideo(video, btn);
                    
                    // Toggle play/pause on video click
                    container.addEventListener('click', (e) => {
                        if (e.target !== btn) {
                            updateVideoState(video, btn);
                        }
                    });
                    
                    // Toggle play/pause on play button click
                    btn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        updateVideoState(video, btn);
                    });
                    
                    // Show play button when video ends
                    video.addEventListener('ended', () => {
                        video.currentTime = 0;
                        video.play().catch(e => console.log('Loop play failed:', e));
                    });
                    
                    // Handle visibility change
                    document.addEventListener('visibilitychange', () => {
                        if (document.hidden) {
                            video.pause();
                            btn.style.display = 'flex';
                        } else {
                            forcePlayVideo(video, btn);
                        }
                    });
                });
                
                // Add a small delay and try to play videos again
                setTimeout(() => {
                    videos.forEach((video, index) => {
                        const btn = playButtons[index];
                        forcePlayVideo(video, btn);
                    });
                }, 1000);
                
                // Try to play videos after a user interaction (like scrolling)
                let userInteracted = false;
                const handleUserInteraction = () => {
                    if (!userInteracted) {
                        userInteracted = true;
                        videos.forEach((video, index) => {
                            const btn = playButtons[index];
                            forcePlayVideo(video, btn);
                        });
                        window.removeEventListener('scroll', handleUserInteraction);
                        window.removeEventListener('touchstart', handleUserInteraction);
                    }
                };
                
                window.addEventListener('scroll', handleUserInteraction, { once: true });
                window.addEventListener('touchstart', handleUserInteraction, { once: true });
            });
        </script>
    </div>
</section>


<!-- Testimonials -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center mb-5" data-aos="fade-up">
                <h2 class="mb-3">What Our <span class="text-primary">Students</span> Say</h2>
                <p class="lead">Hear from our successful graduates</p>
            </div>
        </div>
        
        <div class="row g-4 justify-content-center">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 border-0 shadow-sm p-4">
                    <div class="mb-3">
                        <h5 class="mb-1">Priya Sharma</h5>
                        <p class="text-muted">Professional Bridal Makeup Graduate</p>
                    </div>
                    <p class="mb-0">"The academy provided me with the skills and confidence to start my own bridal makeup business. The instructors are amazing and the hands-on training was invaluable."</p>
                </div>
            </div>
            
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card h-100 border-0 shadow-sm p-4">
                    <div class="mb-3">
                        <h5 class="mb-1">Ananya Patel</h5>
                        <p class="text-muted">Basic Makeup Course Graduate</p>
                    </div>
                    <p class="mb-0">"As a complete beginner, I was nervous about starting, but the instructors were so patient and supportive. I learned so much in just 6 weeks and now I do makeup for all my friends and family!"</p>
                </div>
            </div>
            
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card h-100 border-0 shadow-sm p-4">
                    <div class="mb-3">
                        <h5 class="mb-1">Meera Desai</h5>
                        <p class="text-muted">Advanced Airbrush Makeup Graduate</p>
                    </div>
                    <p class="mb-0">"The airbrush makeup course completely transformed my career! The techniques I learned helped me land a job at a top beauty studio. The instructors' expertise and personalized attention made all the difference."</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-primary text-white">
    <div class="container text-center">
        <h2 class="display-5 fw-bold mb-4" data-aos="fade-up">Ready to Start Your Makeup Career?</h2>
        <p class="lead mb-5" data-aos="fade-up" data-aos-delay="100">Join hundreds of successful graduates who have transformed their passion into a profession.</p>
        <div class="d-flex justify-content-center gap-3" data-aos="fade-up" data-aos-delay="200">
            <a href="contact.php" class="btn btn-light btn-lg px-4">Enroll Now</a>
        </div>
    </div>
</section>

<?php
// Include footer
include 'includes/footer.php';
?>
