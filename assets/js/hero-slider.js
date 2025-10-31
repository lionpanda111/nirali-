document.addEventListener('DOMContentLoaded', function() {
    // Initialize the slider
    $('.hero-slider').slick({
        dots: true,
        arrows: true,
        infinite: true,
        speed: 1000,
        autoplay: true,
        autoplaySpeed: 5000,
        fade: true,
        cssEase: 'linear',
        prevArrow: $('.slick-prev'),
        nextArrow: $('.slick-next'),
        appendDots: $('.slider-dots'),
        responsive: [
            {
                breakpoint: 992,
                settings: {
                    arrows: false
                }
            }
        ]
    });
});
