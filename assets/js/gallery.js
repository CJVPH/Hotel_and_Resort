// ============================================
// GALLERY FUNCTIONALITY
// ============================================

// Global variables for lightbox
let currentImageIndex = 0;
let images = [];
let lightbox, lightboxImage, lightboxCurrent;

document.addEventListener('DOMContentLoaded', function() {
    console.log('Gallery JS Loaded');
    console.log('Gallery items found:', document.querySelectorAll('.gallery-item').length);
    console.log('Lightbox element found:', document.getElementById('lightbox') ? 'Yes' : 'No');
    
    // Add a small delay to ensure all elements are loaded
    setTimeout(() => {
        initializeLightbox();
    }, 100);
});

// ============================================
// LIGHTBOX FUNCTIONALITY
// ============================================

function openLightbox(index) {
    console.log('Opening lightbox for image:', index);
    
    if (!lightbox) {
        console.error('Lightbox element not found!');
        return;
    }
    
    if (!images || images.length === 0) {
        console.error('No images available!');
        return;
    }
    
    if (index < 0 || index >= images.length) {
        console.error('Invalid image index:', index);
        return;
    }
    
    currentImageIndex = index;
    updateLightboxImage();
    
    // Show lightbox
    lightbox.style.display = 'flex';
    lightbox.style.opacity = '0';
    
    // Force reflow
    lightbox.offsetHeight;
    
    // Animate in
    lightbox.style.opacity = '1';
    document.body.style.overflow = 'hidden';
    
    console.log('Lightbox opened successfully');
}

function closeLightbox() {
    console.log('Closing lightbox');
    lightbox.style.opacity = '0';
    setTimeout(() => {
        lightbox.style.display = 'none';
        document.body.style.overflow = '';
        console.log('Lightbox closed');
    }, 300);
}

function showPreviousImage() {
    if (images.length <= 1) return;
    currentImageIndex = (currentImageIndex - 1 + images.length) % images.length;
    console.log('Previous image:', currentImageIndex);
    updateLightboxImage();
}

function showNextImage() {
    if (images.length <= 1) return;
    currentImageIndex = (currentImageIndex + 1) % images.length;
    console.log('Next image:', currentImageIndex);
    updateLightboxImage();
}

function updateLightboxImage() {
    if (lightboxImage && images[currentImageIndex]) {
        console.log('Updating lightbox image:', images[currentImageIndex].src);
        lightboxImage.src = images[currentImageIndex].src;
        lightboxImage.alt = images[currentImageIndex].alt;
        
        if (lightboxCurrent) {
            lightboxCurrent.textContent = currentImageIndex + 1;
        }
    }
}

function initializeLightbox() {
    const galleryItems = document.querySelectorAll('.gallery-item');
    lightbox = document.getElementById('lightbox');
    lightboxImage = document.getElementById('lightbox-image');
    const lightboxClose = document.querySelector('.lightbox-close');
    const lightboxPrev = document.getElementById('lightbox-prev');
    const lightboxNext = document.getElementById('lightbox-next');
    lightboxCurrent = document.getElementById('lightbox-current');
    const lightboxTotal = document.getElementById('lightbox-total');
    
    console.log('Initializing lightbox...');
    console.log('Gallery items:', galleryItems.length);
    console.log('Lightbox element:', lightbox);
    
    if (!lightbox) {
        console.log('Lightbox element not found!');
        return;
    }
    
    if (galleryItems.length === 0) {
        console.log('No gallery items found!');
        return;
    }
    
    // Map images
    images = Array.from(galleryItems).map(item => {
        const img = item.querySelector('img');
        return {
            src: img.src,
            alt: img.alt
        };
    });
    
    console.log('Images mapped:', images.length);
    
    // Update total count
    if (lightboxTotal) {
        lightboxTotal.textContent = images.length;
    }
    
    // Open lightbox when clicking on gallery item
    galleryItems.forEach((item, index) => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Gallery item clicked:', index);
            openLightbox(index);
        });
        
        // Add cursor pointer style
        item.style.cursor = 'pointer';
        
        // Add visual feedback
        item.addEventListener('mousedown', function() {
            this.style.transform = 'scale(0.98)';
        });
        
        item.addEventListener('mouseup', function() {
            this.style.transform = '';
        });
    });
    
    // Close lightbox
    if (lightboxClose) {
        lightboxClose.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            console.log('Close button clicked');
            closeLightbox();
        });
    }
    
    // Navigation buttons
    if (lightboxPrev) {
        lightboxPrev.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            console.log('Previous button clicked');
            showPreviousImage();
        });
    }
    
    if (lightboxNext) {
        lightboxNext.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            console.log('Next button clicked');
            showNextImage();
        });
    }
    
    // Close lightbox when clicking outside image
    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) {
            console.log('Clicked outside image');
            closeLightbox();
        }
    });
    
    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (lightbox.style.display === 'flex') {
            console.log('Key pressed in lightbox:', e.key);
            switch(e.key) {
                case 'Escape':
                    closeLightbox();
                    break;
                case 'ArrowLeft':
                    showPreviousImage();
                    break;
                case 'ArrowRight':
                    showNextImage();
                    break;
            }
        }
    });
    
    // Initialize lightbox styles
    lightbox.style.opacity = '0';
    lightbox.style.transition = 'opacity 0.3s ease';
    lightbox.style.display = 'none';
    
    console.log('Lightbox initialization complete');
    
    // Test functions - you can call these from console
    window.testLightbox = function() {
        console.log('Testing lightbox...');
        if (images.length > 0) {
            console.log('Opening first image...');
            openLightbox(0);
        } else {
            console.log('No images to test with');
        }
    };
    
    window.testClick = function() {
        console.log('Testing click on first gallery item...');
        const firstItem = document.querySelector('.gallery-item');
        if (firstItem) {
            console.log('Found first item, triggering click...');
            firstItem.click();
        } else {
            console.log('No gallery items found');
        }
    };
    
    window.debugGallery = function() {
        console.log('=== GALLERY DEBUG INFO ===');
        console.log('Gallery items:', document.querySelectorAll('.gallery-item').length);
        console.log('Lightbox element:', document.getElementById('lightbox'));
        console.log('Images array:', images);
        console.log('Lightbox image element:', lightboxImage);
        console.log('=========================');
    };
}

// ============================================
// GALLERY ANIMATIONS
// ============================================

// Intersection Observer for gallery items animation
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const galleryObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observe gallery items for animation
document.addEventListener('DOMContentLoaded', function() {
    const galleryItems = document.querySelectorAll('.gallery-item');
    
    galleryItems.forEach((item, index) => {
        // Set initial state
        item.style.opacity = '0';
        item.style.transform = 'translateY(30px)';
        item.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
        
        // Observe for animation
        galleryObserver.observe(item);
    });
});

// ============================================
// SMOOTH SCROLLING FOR GALLERY LINKS
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll for booking button
    const bookingButtons = document.querySelectorAll('.btn-large');
    
    bookingButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Add a subtle animation before redirect
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
    });
});

// ============================================
// LAZY LOADING FOR GALLERY IMAGES
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    const galleryImages = document.querySelectorAll('.gallery-item img');
    
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                
                // Add loading animation
                img.style.opacity = '0';
                img.style.transition = 'opacity 0.3s ease';
                
                img.onload = () => {
                    img.style.opacity = '1';
                };
                
                // If image is already loaded
                if (img.complete) {
                    img.style.opacity = '1';
                }
                
                imageObserver.unobserve(img);
            }
        });
    });
    
    galleryImages.forEach(img => {
        imageObserver.observe(img);
    });
});

// ============================================
// ERROR HANDLING
// ============================================

window.addEventListener('error', function(e) {
    console.log('Gallery JavaScript error:', e.error);
});

// Handle image loading errors
document.addEventListener('DOMContentLoaded', function() {
    const galleryImages = document.querySelectorAll('.gallery-item img');
    
    galleryImages.forEach(img => {
        img.addEventListener('error', function() {
            console.log('Failed to load image:', this.src);
            
            // Replace with placeholder or hide the item
            const galleryItem = this.closest('.gallery-item');
            if (galleryItem) {
                galleryItem.style.display = 'none';
            }
        });
    });
});