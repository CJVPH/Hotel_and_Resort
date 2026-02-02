<?php
require_once '../config/database.php';
require_once '../config/auth.php';
require_once '../includes/photo_functions.php';

// Require admin login
requireAdminLogin();

// Get photos for different sections
$carouselPhotos = getPhotosForSection('carousel');
$poolPhotos = getPhotosForSection('pool');
$spaPhotos = getPhotosForSection('spa');
$restaurantPhotos = getPhotosForSection('restaurant');

// Set page variables for template
$pageTitle = 'Settings';
$currentPage = 'settings';
?>

<?php include 'template_header.php'; ?>
<!-- Page specific styles -->
<style>
    .photo-upload-section { margin-bottom: 3rem; }
    .upload-area { border: 2px dashed #C9A961; border-radius: 15px; padding: 2rem; text-align: center; background: #f8f9fa; transition: all 0.3s ease; cursor: pointer; margin-bottom: 2rem; }
    .upload-area:hover { background: #e9ecef; border-color: #8B7355; }
    .upload-area.dragover { background: rgba(201, 169, 97, 0.1); border-color: #C9A961; }
    .upload-icon { font-size: 3rem; color: #C9A961; margin-bottom: 1rem; }
    .upload-text { color: #666; font-size: 1.1rem; margin-bottom: 1rem; }
    .upload-button { background: linear-gradient(135deg, #C9A961 0%, #8B7355 100%); color: white; border: none; padding: 1rem 2rem; border-radius: 25px; font-weight: 700; cursor: pointer; transition: all 0.3s ease; }
    .upload-button:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(201, 169, 97, 0.3); }
    .photo-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; margin-top: 2rem; }
    .photo-item { position: relative; border-radius: 10px; overflow: hidden; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1); transition: all 0.3s ease; aspect-ratio: 4/3; }
    .photo-item:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2); }
    .photo-item img { width: 100%; height: 100%; object-fit: cover; }
    .photo-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.7); display: flex; align-items: center; justify-content: center; opacity: 0; transition: all 0.3s ease; }
    .photo-item:hover .photo-overlay { opacity: 1; }
    .delete-btn { background: #dc3545; color: white; border: none; padding: 0.5rem 1rem; border-radius: 20px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 0.5rem; }
    .delete-btn:hover { background: #c82333; transform: scale(1.05); }
    .progress-bar { width: 100%; height: 6px; background: #e0e0e0; border-radius: 3px; overflow: hidden; margin-top: 1rem; display: none; }
    .progress-fill { height: 100%; background: linear-gradient(135deg, #C9A961 0%, #8B7355 100%); width: 0%; transition: width 0.3s ease; }
    .upload-status { margin-top: 1rem; padding: 1rem; border-radius: 10px; display: none; }
    .upload-status.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .upload-status.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
</style>

<!-- Main Content -->
<div class="content-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="fas fa-images"></i> Photo Management</h1>
        <p>Upload and manage photos for your website sections</p>
    </div>

            <!-- Carousel Photos -->
            <div class="admin-section photo-upload-section">
                <div class="section-header">
                    <h2><i class="fas fa-images"></i> Carousel Photos</h2>
                    <span style="color: #666; font-size: 0.9rem;">Main homepage slideshow images</span>
                </div>
                
                <div class="upload-area" data-section="carousel">
                    <div class="upload-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <div class="upload-text">
                        Drag & drop carousel images here or click to browse
                    </div>
                    <button type="button" class="upload-button">
                        <i class="fas fa-plus"></i> Add Carousel Photos
                    </button>
                    <input type="file" class="file-input" accept="image/*" multiple style="display: none;">
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                    <div class="upload-status"></div>
                </div>

                <div class="photo-grid" id="carousel-photos">
                    <?php foreach ($carouselPhotos as $photo): ?>
                    <div class="photo-item" data-photo-id="<?php echo $photo['id']; ?>">
                        <img src="../<?php echo $photo['file_path']; ?>" alt="<?php echo htmlspecialchars($photo['original_name']); ?>">
                        <div class="photo-overlay">
                            <button class="delete-btn" onclick="deletePhoto(<?php echo $photo['id']; ?>, 'carousel')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Pool Photos -->
            <div class="admin-section photo-upload-section">
                <div class="section-header">
                    <h2><i class="fas fa-swimming-pool"></i> Pool Photos</h2>
                    <span style="color: #666; font-size: 0.9rem;">Swimming pool and aquatic facilities</span>
                </div>
                
                <div class="upload-area" data-section="pool">
                    <div class="upload-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <div class="upload-text">
                        Drag & drop pool images here or click to browse
                    </div>
                    <button type="button" class="upload-button">
                        <i class="fas fa-plus"></i> Add Pool Photos
                    </button>
                    <input type="file" class="file-input" accept="image/*" multiple style="display: none;">
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                    <div class="upload-status"></div>
                </div>

                <div class="photo-grid" id="pool-photos">
                    <?php foreach ($poolPhotos as $photo): ?>
                    <div class="photo-item" data-photo-id="<?php echo $photo['id']; ?>">
                        <img src="../<?php echo $photo['file_path']; ?>" alt="<?php echo htmlspecialchars($photo['original_name']); ?>">
                        <div class="photo-overlay">
                            <button class="delete-btn" onclick="deletePhoto(<?php echo $photo['id']; ?>, 'pool')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Spa Photos -->
            <div class="admin-section photo-upload-section">
                <div class="section-header">
                    <h2><i class="fas fa-spa"></i> Spa Photos</h2>
                    <span style="color: #666; font-size: 0.9rem;">Spa treatments and wellness facilities</span>
                </div>
                
                <div class="upload-area" data-section="spa">
                    <div class="upload-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <div class="upload-text">
                        Drag & drop spa images here or click to browse
                    </div>
                    <button type="button" class="upload-button">
                        <i class="fas fa-plus"></i> Add Spa Photos
                    </button>
                    <input type="file" class="file-input" accept="image/*" multiple style="display: none;">
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                    <div class="upload-status"></div>
                </div>

                <div class="photo-grid" id="spa-photos">
                    <?php foreach ($spaPhotos as $photo): ?>
                    <div class="photo-item" data-photo-id="<?php echo $photo['id']; ?>">
                        <img src="../<?php echo $photo['file_path']; ?>" alt="<?php echo htmlspecialchars($photo['original_name']); ?>">
                        <div class="photo-overlay">
                            <button class="delete-btn" onclick="deletePhoto(<?php echo $photo['id']; ?>, 'spa')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Restaurant Photos -->
            <div class="admin-section photo-upload-section">
                <div class="section-header">
                    <h2><i class="fas fa-utensils"></i> Restaurant Photos</h2>
                    <span style="color: #666; font-size: 0.9rem;">Dining areas and culinary experiences</span>
                </div>
                
                <div class="upload-area" data-section="restaurant">
                    <div class="upload-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <div class="upload-text">
                        Drag & drop restaurant images here or click to browse
                    </div>
                    <button type="button" class="upload-button">
                        <i class="fas fa-plus"></i> Add Restaurant Photos
                    </button>
                    <input type="file" class="file-input" accept="image/*" multiple style="display: none;">
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                    <div class="upload-status"></div>
                </div>

                <div class="photo-grid" id="restaurant-photos">
                    <?php foreach ($restaurantPhotos as $photo): ?>
                    <div class="photo-item" data-photo-id="<?php echo $photo['id']; ?>">
                        <img src="../<?php echo $photo['file_path']; ?>" alt="<?php echo htmlspecialchars($photo['original_name']); ?>">
                        <div class="photo-overlay">
                            <button class="delete-btn" onclick="deletePhoto(<?php echo $photo['id']; ?>, 'restaurant')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
    // Photo upload and management functionality
    document.addEventListener('DOMContentLoaded', function() {
        initializePhotoUpload();
    });

    function initializePhotoUpload() {
        const uploadAreas = document.querySelectorAll('.upload-area');
        
        uploadAreas.forEach(area => {
            const fileInput = area.querySelector('.file-input');
            const uploadButton = area.querySelector('.upload-button');
            const section = area.dataset.section;
            
            // Click to browse
            uploadButton.addEventListener('click', () => {
                fileInput.click();
            });
            
            area.addEventListener('click', (e) => {
                if (e.target === area || e.target.classList.contains('upload-text') || e.target.classList.contains('upload-icon')) {
                    fileInput.click();
                }
            });
            
            // File selection
            fileInput.addEventListener('change', (e) => {
                handleFileUpload(e.target.files, section, area);
            });
            
            // Drag and drop
            area.addEventListener('dragover', (e) => {
                e.preventDefault();
                area.classList.add('dragover');
            });
            
            area.addEventListener('dragleave', () => {
                area.classList.remove('dragover');
            });
            
            area.addEventListener('drop', (e) => {
                e.preventDefault();
                area.classList.remove('dragover');
                handleFileUpload(e.dataTransfer.files, section, area);
            });
        });
    }

    function handleFileUpload(files, section, uploadArea) {
        if (files.length === 0) return;
        
        const progressBar = uploadArea.querySelector('.progress-bar');
        const progressFill = uploadArea.querySelector('.progress-fill');
        const statusDiv = uploadArea.querySelector('.upload-status');
        
        // Show progress bar
        progressBar.style.display = 'block';
        statusDiv.style.display = 'none';
        
        const formData = new FormData();
        formData.append('section', section);
        
        for (let i = 0; i < files.length; i++) {
            formData.append('photos[]', files[i]);
        }
        
        const xhr = new XMLHttpRequest();
        
        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                progressFill.style.width = percentComplete + '%';
            }
        });
        
        xhr.addEventListener('load', () => {
            progressBar.style.display = 'none';
            
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        statusDiv.className = 'upload-status success';
                        statusDiv.textContent = response.message;
                        statusDiv.style.display = 'block';
                        
                        // Reload the page to show new photos
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        statusDiv.className = 'upload-status error';
                        statusDiv.textContent = response.message;
                        statusDiv.style.display = 'block';
                    }
                } catch (e) {
                    statusDiv.className = 'upload-status error';
                    statusDiv.textContent = 'Upload failed. Please try again.';
                    statusDiv.style.display = 'block';
                }
            } else {
                statusDiv.className = 'upload-status error';
                statusDiv.textContent = 'Upload failed. Please try again.';
                statusDiv.style.display = 'block';
            }
        });
        
        xhr.addEventListener('error', () => {
            progressBar.style.display = 'none';
            statusDiv.className = 'upload-status error';
            statusDiv.textContent = 'Upload failed. Please check your connection.';
            statusDiv.style.display = 'block';
        });
        
        xhr.open('POST', 'upload_photos.php');
        xhr.send(formData);
    }

    function deletePhoto(photoId, section) {
        if (!confirm('Are you sure you want to delete this photo?')) {
            return;
        }
        
        fetch('delete_photo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                photo_id: photoId,
                section: section
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove photo from display
                const photoItem = document.querySelector(`[data-photo-id="${photoId}"]`);
                if (photoItem) {
                    photoItem.remove();
                }
            } else {
                alert('Failed to delete photo: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete photo. Please try again.');
        });
    }
    </script>

<?php include 'template_footer.php'; ?>