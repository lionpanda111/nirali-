<?php
// Database configuration
$host = 'localhost';
$dbname = 'makeup_studio';
$username = 'root';
$password = '';

// Create PDO connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}

// Get videos from database
$videos = [];
try {
    $stmt = $pdo->query("SELECT * FROM videos WHERE is_active = 1 ORDER BY display_order ASC");
    $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching videos: " . $e->getMessage());
}
?>

<!-- Video Reels Section -->
<section id="video-reels" class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center mb-5" data-aos="fade-up">
                <h2 class="display-5 fw-bold mb-3">Makeup <span class="text-primary">Reels</span></h2>
                <p class="lead">Watch our latest makeup tutorials and transformations</p>
                <div class="divider mx-auto"></div>
            </div>
        </div>
        
        <?php if (!empty($videos)): ?>
            <div class="reels-carousel-container">
                <div class="reels-track">
                    <?php foreach ($videos as $video): ?>
                        <div class="reel-item">
                            <div class="reel-wrapper">
                                <video class="reel-video" 
                                       loop muted playsinline
                                       poster="<?php echo htmlspecialchars($video['thumbnail_url']); ?>">
                                    <source src="<?php echo htmlspecialchars($video['video_url']); ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                                <div class="reel-overlay">
                                    <button class="play-pause-btn">
                                        <i class="fas fa-play"></i>
                                    </button>
                                </div>
                                <div class="reel-info">
                                    <h5><?php echo htmlspecialchars($video['title']); ?></h5>
                                    <?php if (!empty($video['description'])): ?>
                                        <p class="small"><?php echo htmlspecialchars($video['description']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-nav prev-nav"><i class="fas fa-chevron-left"></i></button>
                <button class="carousel-nav next-nav"><i class="fas fa-chevron-right"></i></button>
            </div>
            
            <!-- View More Button -->
            <div class="text-center mt-5">
                <a href="https://www.instagram.com/<?php echo INSTAGRAM_USERNAME; ?>" 
                   class="btn btn-primary px-4" 
                   target="_blank"
                   style="background-color: var(--primary-color);
                          border: none;
                          font-weight: 600;
                          letter-spacing: 0.5px;
                          color: #fff;
                          transition: var(--transition);
                          box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);">
                    <i class="fab fa-instagram me-2"></i> View More on Instagram
                </a>
            </div>
            
        <?php else: ?>
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="fas fa-video-slash fa-4x text-muted"></i>
                </div>
                <h4>No videos available</h4>
                <p class="text-muted">Check back later for our latest makeup reels and tutorials.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
/* Reels Carousel Styles */
.reels-carousel-container {
    position: relative;
    width: 100%;
    padding: 0 40px;
    margin: 0 auto;
}

.reels-track {
    display: flex;
    overflow-x: auto;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
    scroll-snap-type: x mandatory;
    gap: 20px;
    padding: 20px 0;
    scroll-padding: 0 20px;
    -ms-overflow-style: none;  /* Hide scrollbar IE and Edge */
    scrollbar-width: none;  /* Hide scrollbar Firefox */
}

/* Hide scrollbar for Chrome, Safari and Opera */
.reels-track::-webkit-scrollbar {
    display: none;
}

.reel-item {
    flex: 0 0 380px;
    scroll-snap-align: start;
    transition: transform 0.3s ease;
    margin: 0 15px;
}

.reel-wrapper {
    position: relative;
    width: 100%;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    background: #000;
    aspect-ratio: 9/16;
    transition: transform 0.3s ease;
}

.reel-item:hover .reel-wrapper {
    transform: scale(1.02);
}

.reel-info {
    padding: 20px;
}

.reel-info h5 {
    font-size: 1.2rem;
    margin-bottom: 8px;
}

.reel-info p {
    font-size: 0.95rem;
}

.reel-wrapper {
    position: relative;
    width: 100%;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    background: #000;
    aspect-ratio: 9/16; /* Maintain reel aspect ratio */
}

.reel-video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
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
    background: rgba(0, 0, 0, 0.3);
    opacity: 1;
    transition: opacity 0.3s ease;
}

.reel-wrapper.playing .reel-overlay {
    opacity: 0;
    pointer-events: none;
}

.play-pause-btn {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    border: 2px solid white;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 24px;
}

.play-pause-btn:hover {
    transform: scale(1.1);
    background: var(--primary-color);
    border-color: var(--primary-color);
}

.reel-info {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 15px;
    background: linear-gradient(transparent, rgba(0,0,0,0.7));
    color: white;
}

.reel-info h5 {
    margin: 0 0 5px;
    font-size: 1rem;
    font-weight: 600;
}

.reel-info p {
    margin: 0;
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.9);
    line-height: 1.4;
}

.carousel-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: white;
    border: none;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 10;
    transition: all 0.3s ease;
}

.carousel-nav:hover {
    background: var(--primary-color);
    color: white;
}

.prev-nav {
    left: 0;
}

.next-nav {
    right: 0;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .reel-item {
        flex: 0 0 280px;
    }
    
    .reels-carousel-container {
        padding: 0 30px;
    }
    
    .carousel-nav {
        width: 35px;
        height: 35px;
        font-size: 0.9rem;
    }
}

@media (max-width: 576px) {
    .reel-item {
        flex: 0 0 250px;
    }
    
    .reels-carousel-container {
        padding: 0 15px;
    }
    
    .carousel-nav {
        width: 30px;
        height: 30px;
        font-size: 0.8rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const track = document.querySelector('.reels-track');
    const prevBtn = document.querySelector('.prev-nav');
    const nextBtn = document.querySelector('.next-nav');
    const reelItems = document.querySelectorAll('.reel-item');
    const videos = document.querySelectorAll('.reel-video');
    const playButtons = document.querySelectorAll('.play-pause-btn');
    
    if (reelItems.length === 0) return;
    
    let currentIndex = 0;
    const itemWidth = reelItems[0].offsetWidth + 20; // 20px gap
    
    // Auto-slide every 4 seconds
    let slideInterval = setInterval(() => {
        nextSlide();
    }, 4000);
    
    // Reset timer on user interaction
    const resetTimer = () => {
        clearInterval(slideInterval);
        slideInterval = setInterval(() => {
            nextSlide();
        }, 4000);
    };
    
    const updateSlider = () => {
        track.scrollTo({
            left: currentIndex * itemWidth,
            behavior: 'smooth'
        });
    };
    
    const nextSlide = () => {
        currentIndex = (currentIndex + 1) % reelItems.length;
        updateSlider();
        resetTimer();
    };
    
    const prevSlide = () => {
        currentIndex = (currentIndex - 1 + reelItems.length) % reelItems.length;
        updateSlider();
        resetTimer();
    };
    
    // Navigation buttons
    prevBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        prevSlide();
    });
    
    nextBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        nextSlide();
    });
    
    // Handle video play/pause
    const togglePlayPause = (video, wrapper, btn) => {
        if (video.paused) {
            video.play()
                .then(() => {
                    wrapper.classList.add('playing');
                    btn.innerHTML = '<i class="fas fa-pause"></i>';
                })
                .catch(error => {
                    console.log('Play failed:', error);
                });
        } else {
            video.pause();
            wrapper.classList.remove('playing');
            btn.innerHTML = '<i class="fas fa-play"></i>';
        }
    };
    
    // Initialize video controls
    videos.forEach((video, index) => {
        const wrapper = video.closest('.reel-wrapper');
        const btn = playButtons[index];
        
        if (!video || !btn) return;
        
        // Mute all videos by default
        video.muted = true;
        
        // Handle play/pause on button click
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            togglePlayPause(video, wrapper, btn);
            resetTimer();
        });
        
        // Toggle play/pause when clicking on video
        video.addEventListener('click', () => {
            togglePlayPause(video, wrapper, btn);
            resetTimer();
        });
        
        // Update button state when video ends
        video.addEventListener('ended', () => {
            video.currentTime = 0;
            video.play();
        });
    });
    
    // Pause videos when they're not in view
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            const video = entry.target;
            const wrapper = video.closest('.reel-wrapper');
            const btn = wrapper?.querySelector('.play-pause-btn');
            
            if (entry.isIntersecting) {
                video.play()
                    .then(() => {
                        wrapper?.classList.add('playing');
                        if (btn) btn.innerHTML = '<i class="fas fa-pause"></i>';
                    })
                    .catch(error => {
                        console.log('Autoplay prevented:', error);
                    });
            } else {
                if (!video.paused) {
                    video.pause();
                    wrapper?.classList.remove('playing');
                    if (btn) btn.innerHTML = '<i class="fas fa-play"></i>';
                }
            }
        });
    }, {
        threshold: 0.7
    });
    
    // Observe each video
    videos.forEach(video => {
        observer.observe(video);
    });
    
    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            itemWidth = reelItems[0]?.offsetWidth + 20;
            updateSlider();
        }, 250);
    });
});
</script>