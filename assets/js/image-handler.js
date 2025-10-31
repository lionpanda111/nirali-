// Function to handle image orientation
document.addEventListener('DOMContentLoaded', function() {
    // Handle service card images
    document.querySelectorAll('.service-card-img img').forEach(img => {
        // Create a new image to check dimensions
        const tempImg = new Image();
        tempImg.src = img.src;
        
        tempImg.onload = function() {
            const container = img.closest('.service-card-img');
            if (this.height > this.width) {
                container.classList.add('portrait');
            } else {
                container.classList.add('landscape');
            }
        };
    });

    // Handle gallery thumbnails
    document.querySelectorAll('.gallery-thumbnail img').forEach(img => {
        const tempImg = new Image();
        tempImg.src = img.src;
        
        tempImg.onload = function() {
            const container = img.closest('.gallery-thumbnail');
            if (this.height > this.width) {
                container.classList.add('portrait');
            } else {
                container.classList.add('landscape');
            }
        };
    });
});
