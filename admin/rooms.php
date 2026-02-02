<?php
require_once '../config/database.php';
require_once '../config/auth.php';

// Require admin login
requireAdminLogin();

$message = '';
$messageType = '';

// Handle form submission for room price updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_price') {
    $roomType = $_POST['room_type'] ?? '';
    $paxGroup = intval($_POST['pax_group'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);
    
    if (!empty($roomType) && $paxGroup > 0 && $price > 0) {
        try {
            $conn = getDBConnection();
            
            // Update or insert room price
            $stmt = $conn->prepare("INSERT INTO room_prices (room_type, pax_group, price) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE price = VALUES(price)");
            $stmt->bind_param("sid", $roomType, $paxGroup, $price);
            
            if ($stmt->execute()) {
                $message = "Room price updated successfully!";
                $messageType = 'success';
            } else {
                $message = "Failed to update room price.";
                $messageType = 'error';
            }
            
            $stmt->close();
            $conn->close();
            
        } catch (Exception $e) {
            error_log("Room price update error: " . $e->getMessage());
            $message = "Database error occurred.";
            $messageType = 'error';
        }
    } else {
        $message = "Please fill in all fields with valid values.";
        $messageType = 'error';
    }
}

// Get current room prices
$roomPrices = [];
try {
    $conn = getDBConnection();
    $result = $conn->query("SELECT * FROM room_prices ORDER BY room_type, pax_group");
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $roomPrices[] = $row;
        }
    }
    
    $conn->close();
    
} catch (Exception $e) {
    error_log("Get room prices error: " . $e->getMessage());
}

// Get room images
$roomImages = [];
try {
    $conn = getDBConnection();
    $result = $conn->query("SELECT * FROM room_images WHERE is_active = 1 ORDER BY room_type, pax_group, room_number, sort_order");
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $roomImages[] = $row;
        }
    }
    
    $conn->close();
    
} catch (Exception $e) {
    error_log("Get room images error: " . $e->getMessage());
}

// Define all 18 individual rooms
$allRooms = [
    // 2 Pax Rooms
    ['number' => '101', 'type' => 'Regular', 'pax' => 2, 'name' => 'Regular Room 101'],
    ['number' => '201', 'type' => 'Deluxe', 'pax' => 2, 'name' => 'Deluxe Room 201'],
    ['number' => '301', 'type' => 'VIP', 'pax' => 2, 'name' => 'VIP Suite 301'],
    ['number' => '102', 'type' => 'Regular', 'pax' => 2, 'name' => 'Regular Room 102'],
    ['number' => '202', 'type' => 'Deluxe', 'pax' => 2, 'name' => 'Deluxe Room 202'],
    ['number' => '302', 'type' => 'VIP', 'pax' => 2, 'name' => 'VIP Suite 302'],
    
    // 4-8 Pax Rooms
    ['number' => '103', 'type' => 'Regular', 'pax' => 8, 'name' => 'Regular Family Room 103'],
    ['number' => '203', 'type' => 'Deluxe', 'pax' => 8, 'name' => 'Deluxe Family Suite 203'],
    ['number' => '303', 'type' => 'VIP', 'pax' => 8, 'name' => 'VIP Family Suite 303'],
    ['number' => '104', 'type' => 'Regular', 'pax' => 8, 'name' => 'Regular Family Room 104'],
    ['number' => '204', 'type' => 'Deluxe', 'pax' => 8, 'name' => 'Deluxe Family Suite 204'],
    ['number' => '304', 'type' => 'VIP', 'pax' => 8, 'name' => 'VIP Family Suite 304'],
    
    // 10-20 Pax Rooms
    ['number' => '105', 'type' => 'Regular', 'pax' => 20, 'name' => 'Regular Group Townhouse 105'],
    ['number' => '205', 'type' => 'Deluxe', 'pax' => 20, 'name' => 'Deluxe Group Townhouse 205'],
    ['number' => '305', 'type' => 'VIP', 'pax' => 20, 'name' => 'VIP Group Townhouse 305'],
    ['number' => '106', 'type' => 'Regular', 'pax' => 20, 'name' => 'Regular Group Townhouse 106'],
    ['number' => '206', 'type' => 'Deluxe', 'pax' => 20, 'name' => 'Deluxe Group Townhouse 206'],
    ['number' => '306', 'type' => 'VIP', 'pax' => 20, 'name' => 'VIP Group Townhouse 306']
];

// Create a lookup for existing images
$imagesByRoom = [];
foreach ($roomImages as $img) {
    $key = $img['room_number'] . '_' . $img['room_type'] . '_' . $img['pax_group'];
    $imagesByRoom[$key] = $img;
}

// Set page variables for template
$pageTitle = 'Room Management';
$currentPage = 'rooms';
?>
<?php include 'template_header.php'; ?>
            <!-- Page Header -->
            <div class="page-header">
                <h1><i class="fas fa-bed"></i> Room Management</h1>
                <p>Manage individual rooms, pricing, and images</p>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Individual Room Management -->
            <div class="admin-section">
                <div class="section-header">
                    <h2><i class="fas fa-images"></i> Individual Room Management</h2>
                    <p>Upload images for each of the 18 individual rooms</p>
                </div>
                
                <!-- Room Type Navigation -->
                <div class="room-nav-tabs" style="display: flex; justify-content: center; margin: 2rem 0; background: rgba(255,255,255,0.1); border-radius: 15px; padding: 0.5rem; backdrop-filter: blur(10px);">
                    <button class="room-nav-tab active" data-room-type="pricing" style="flex: 1; padding: 1rem 2rem; background: linear-gradient(135deg, #C9A961, #8B7355); border: none; border-radius: 10px; color: white; font-weight: 600; cursor: pointer; margin: 0 0.25rem; transition: all 0.3s ease;">
                        <i class="fas fa-dollar-sign"></i> Pricing
                    </button>
                    <button class="room-nav-tab" data-room-type="Regular" style="flex: 1; padding: 1rem 2rem; background: rgba(255,255,255,0.1); border: none; border-radius: 10px; color: #ccc; font-weight: 600; cursor: pointer; margin: 0 0.25rem; transition: all 0.3s ease;">
                        <i class="fas fa-bed"></i> Regular Rooms
                    </button>
                    <button class="room-nav-tab" data-room-type="Deluxe" style="flex: 1; padding: 1rem 2rem; background: rgba(255,255,255,0.1); border: none; border-radius: 10px; color: #ccc; font-weight: 600; cursor: pointer; margin: 0 0.25rem; transition: all 0.3s ease;">
                        <i class="fas fa-crown"></i> Deluxe Rooms
                    </button>
                    <button class="room-nav-tab" data-room-type="VIP" style="flex: 1; padding: 1rem 2rem; background: rgba(255,255,255,0.1); border: none; border-radius: 10px; color: #ccc; font-weight: 600; cursor: pointer; margin: 0 0.25rem; transition: all 0.3s ease;">
                        <i class="fas fa-gem"></i> VIP Suites
                    </button>
                </div>
                
                <!-- Pricing Section -->
                <div id="pricing-section" class="pricing-content" style="display: block;">
                    <!-- Room Pricing Management -->
                    <div class="admin-section" style="background: rgba(255,255,255,0.05); backdrop-filter: blur(10px); border-radius: 20px; padding: 2rem; border: 1px solid rgba(255,255,255,0.1); margin-bottom: 2rem;">
                        <div class="section-header" style="margin-bottom: 2rem;">
                            <h3><i class="fas fa-edit"></i> Update Room Pricing</h3>
                            <p style="color: #ccc; margin: 0.5rem 0 0 0;">Set prices for different room types and guest capacities</p>
                        </div>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="update_price">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="room_type">Room Type</label>
                                    <select id="room_type" name="room_type" required>
                                        <option value="">Select Room Type</option>
                                        <option value="Regular">Regular Room</option>
                                        <option value="Deluxe">Deluxe Room</option>
                                        <option value="VIP">VIP Suite</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="pax_group">Guest Capacity</label>
                                    <select id="pax_group" name="pax_group" required>
                                        <option value="">Select Capacity</option>
                                        <option value="2">2 Guests</option>
                                        <option value="8">4-8 Guests</option>
                                        <option value="20">10-20 Guests</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="price">Price per Night (₱)</label>
                                    <input type="number" id="price" name="price" step="0.01" min="0" required placeholder="0.00">
                                </div>
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Price
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Current Room Prices -->
                    <div class="admin-section" style="background: rgba(255,255,255,0.05); backdrop-filter: blur(10px); border-radius: 20px; padding: 2rem; border: 1px solid rgba(255,255,255,0.1);">
                        <div class="section-header" style="margin-bottom: 2rem;">
                            <h3><i class="fas fa-list"></i> Current Room Prices</h3>
                        </div>
                        
                        <?php if (!empty($roomPrices)): ?>
                        <div class="table-container">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Room Type</th>
                                        <th>Guest Capacity</th>
                                        <th>Price per Night</th>
                                        <th>Last Updated</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($roomPrices as $price): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($price['room_type']); ?></strong>
                                        </td>
                                        <td><?php echo $price['pax_group'] == 8 ? '4-8' : ($price['pax_group'] == 20 ? '10-20' : $price['pax_group']); ?> Guests</td>
                                        <td><strong style="color: #C9A961; font-size: 1.1rem;">₱<?php echo number_format($price['price'], 2); ?></strong></td>
                                        <td><?php echo date('M j, Y g:i A', strtotime($price['updated_at'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-bed"></i>
                            <h3>No Room Prices Set</h3>
                            <p>Use the form above to set room prices for different guest capacities.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Room Grid for Upload -->
                <div id="room-grid-section" class="room-grid" style="display: none; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2rem; margin-top: 2rem;">
                    <?php foreach ($allRooms as $room): ?>
                        <?php 
                        $roomKey = $room['number'] . '_' . $room['type'] . '_' . $room['pax'];
                        $hasImage = isset($imagesByRoom[$roomKey]);
                        $imagePath = $hasImage ? $imagesByRoom[$roomKey]['file_path'] : '';
                        ?>
                        <div class="room-card" data-room-type="<?php echo $room['type']; ?>" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border-radius: 15px; padding: 1.5rem; border: 1px solid rgba(255,255,255,0.2);">
                            <div class="room-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                <div>
                                    <h3 style="color: #C9A961; margin: 0; font-size: 1.2rem;"><?php echo $room['name']; ?></h3>
                                    <p style="color: #ccc; margin: 0.5rem 0 0 0; font-size: 0.9rem;">
                                        <?php echo $room['pax'] == 8 ? '4-8' : ($room['pax'] == 20 ? '10-20' : $room['pax']); ?> Guests
                                    </p>
                                </div>
                                <div class="room-type-badge" style="background: <?php echo $room['type'] === 'Regular' ? '#28a745' : ($room['type'] === 'Deluxe' ? '#007bff' : '#dc3545'); ?>; color: white; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.8rem;">
                                    <?php echo $room['type']; ?>
                                </div>
                            </div>
                            
                            <div class="room-image-container" style="margin-bottom: 1rem;">
                                <?php 
                                // Get images for this specific room
                                $currentImages = [];
                                foreach ($roomImages as $img) {
                                    if ($img['room_number'] == $room['number'] && $img['room_type'] == $room['type'] && $img['pax_group'] == $room['pax']) {
                                        $currentImages[] = $img;
                                    }
                                }
                                ?>
                                
                                <div class="room-images-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(80px, 1fr)); gap: 0.5rem; margin-bottom: 1rem; max-height: 200px; overflow-y: auto;">
                                    <?php if (!empty($currentImages)): 
                                        foreach ($currentImages as $img): ?>
                                        <div class="room-image-item" style="position: relative;">
                                            <img src="../<?php echo $img['file_path']; ?>" alt="Room Image" style="width: 100%; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid #C9A961;">
                                            <button type="button" class="delete-image-btn" data-image-id="<?php echo $img['id']; ?>" style="position: absolute; top: -5px; right: -5px; background: #dc3545; color: white; border: none; border-radius: 50%; width: 20px; height: 20px; font-size: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center;">×</button>
                                        </div>
                                    <?php endforeach; 
                                    else: ?>
                                        <div style="grid-column: 1 / -1; text-align: center; padding: 2rem; background: rgba(255,255,255,0.05); border: 2px dashed #666; border-radius: 10px;">
                                            <i class="fas fa-image" style="font-size: 2rem; color: #666; margin-bottom: 1rem;"></i>
                                            <p style="color: #666; margin: 0;">No images uploaded</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="image-count" style="text-align: center; color: #C9A961; font-size: 0.8rem; margin-bottom: 1rem;">
                                    <?php echo count($currentImages); ?> / 10 images uploaded
                                </div>
                            </div>
                            
                            <form class="room-upload-form" data-room-number="<?php echo $room['number']; ?>" data-room-type="<?php echo $room['type']; ?>" data-pax-group="<?php echo $room['pax']; ?>" style="margin-top: 1rem;">
                                <div class="drag-drop-zone" style="border: 2px dashed #C9A961; border-radius: 15px; padding: 2rem; text-align: center; background: rgba(201, 169, 97, 0.1); cursor: pointer; transition: all 0.3s ease; margin-bottom: 1rem;">
                                    <div class="drag-drop-content">
                                        <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: #C9A961; margin-bottom: 1rem;"></i>
                                        <h4 style="color: #C9A961; margin: 0 0 0.5rem 0;">Drag & Drop Images Here</h4>
                                        <p style="color: #ccc; margin: 0 0 1rem 0; font-size: 0.9rem;">or click to browse files - uploads automatically</p>
                                        <div class="file-info" style="font-size: 0.8rem; color: #999;">
                                            <p>• Up to <?php echo 10 - count($currentImages); ?> images</p>
                                            <p>• JPEG, PNG, WebP formats</p>
                                            <p>• Max 5MB per image</p>
                                        </div>
                                    </div>
                                    <input type="file" name="room_images" accept="image/*" multiple style="display: none;">
                                    
                                    <!-- Progress Bar -->
                                    <div class="progress-bar" style="width: 100%; height: 6px; background: #e0e0e0; border-radius: 3px; overflow: hidden; margin-top: 1rem; display: none;">
                                        <div class="progress-fill" style="height: 100%; background: linear-gradient(135deg, #C9A961 0%, #8B7355 100%); width: 0%; transition: width 0.3s ease;"></div>
                                    </div>
                                    
                                    <!-- Upload Status -->
                                    <div class="upload-status" style="margin-top: 1rem; padding: 1rem; border-radius: 10px; display: none;"></div>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <script>
            // Handle room image uploads and navigation
            document.addEventListener('DOMContentLoaded', function() {
                const uploadForms = document.querySelectorAll('.room-upload-form');
                const navTabs = document.querySelectorAll('.room-nav-tab');
                const roomCards = document.querySelectorAll('.room-card');
                const pricingSection = document.getElementById('pricing-section');
                const roomGridSection = document.getElementById('room-grid-section');
                const deleteButtons = document.querySelectorAll('.delete-image-btn');
                
                // Restore active tab from sessionStorage after page reload
                const savedActiveTab = sessionStorage.getItem('activeRoomTab');
                if (savedActiveTab && savedActiveTab !== 'pricing') {
                    // Find and activate the saved tab
                    const tabToActivate = document.querySelector(`[data-room-type="${savedActiveTab}"]`);
                    if (tabToActivate) {
                        // Clear sessionStorage
                        sessionStorage.removeItem('activeRoomTab');
                        
                        // Simulate click on the saved tab
                        setTimeout(() => {
                            tabToActivate.click();
                        }, 100);
                    }
                }
                
                // Handle room type navigation
                navTabs.forEach(tab => {
                    tab.addEventListener('click', function() {
                        const roomType = this.dataset.roomType;
                        
                        // Update active tab
                        navTabs.forEach(t => {
                            t.classList.remove('active');
                            t.style.background = 'rgba(255,255,255,0.1)';
                            t.style.color = '#ccc';
                        });
                        
                        this.classList.add('active');
                        this.style.background = 'linear-gradient(135deg, #C9A961, #8B7355)';
                        this.style.color = 'white';
                        
                        // Show/hide sections based on selection
                        if (roomType === 'pricing') {
                            pricingSection.style.display = 'block';
                            roomGridSection.style.display = 'none';
                        } else {
                            pricingSection.style.display = 'none';
                            roomGridSection.style.display = 'grid';
                            
                            // Filter room cards by type
                            roomCards.forEach(card => {
                                if (card.dataset.roomType === roomType) {
                                    card.style.display = 'block';
                                } else {
                                    card.style.display = 'none';
                                }
                            });
                        }
                    });
                });
                
                // Handle image deletion
                deleteButtons.forEach(btn => {
                    btn.addEventListener('click', function() {
                        const imageId = this.dataset.imageId;
                        
                        if (confirm('Are you sure you want to delete this image?')) {
                            const formData = new FormData();
                            formData.append('image_id', imageId);
                            
                            fetch('delete_room_image.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Remove image from display without page reload
                                    this.closest('.room-image-item').remove();
                                    
                                    // Update image counter
                                    const roomCard = this.closest('.room-card');
                                    const imageCount = roomCard.querySelector('.image-count');
                                    const currentCount = parseInt(imageCount.textContent.split('/')[0]);
                                    imageCount.textContent = `${currentCount - 1} / 10 images uploaded`;
                                    
                                    // Update file info in upload zone
                                    const fileInfo = roomCard.querySelector('.file-info p');
                                    const remainingSlots = 10 - (currentCount - 1);
                                    fileInfo.textContent = `• Up to ${remainingSlots} images`;
                                } else {
                                    alert('Delete failed: ' + data.error);
                                }
                            })
                            .catch(error => {
                                console.error('Delete error:', error);
                                alert('Delete failed. Please try again.');
                            });
                        }
                    });
                });
                
                // Initialize automatic upload for each form
                uploadForms.forEach(form => {
                    initializeAutoUpload(form);
                });
                
                function initializeAutoUpload(form) {
                    const dragZone = form.querySelector('.drag-drop-zone');
                    const fileInput = form.querySelector('input[type="file"]');
                    const progressBar = form.querySelector('.progress-bar');
                    const progressFill = form.querySelector('.progress-fill');
                    const statusDiv = form.querySelector('.upload-status');
                    
                    // Click to browse files
                    dragZone.addEventListener('click', (e) => {
                        if (e.target === dragZone || e.target.closest('.drag-drop-content')) {
                            fileInput.click();
                        }
                    });
                    
                    // File input change - automatic upload
                    fileInput.addEventListener('change', (e) => {
                        if (e.target.files.length > 0) {
                            handleAutoUpload(e.target.files, form);
                        }
                    });
                    
                    // Drag and drop events
                    dragZone.addEventListener('dragover', (e) => {
                        e.preventDefault();
                        dragZone.style.borderColor = '#8B7355';
                        dragZone.style.background = 'rgba(201, 169, 97, 0.2)';
                    });
                    
                    dragZone.addEventListener('dragleave', (e) => {
                        e.preventDefault();
                        dragZone.style.borderColor = '#C9A961';
                        dragZone.style.background = 'rgba(201, 169, 97, 0.1)';
                    });
                    
                    dragZone.addEventListener('drop', (e) => {
                        e.preventDefault();
                        dragZone.style.borderColor = '#C9A961';
                        dragZone.style.background = 'rgba(201, 169, 97, 0.1)';
                        
                        const files = e.dataTransfer.files;
                        if (files.length > 0) {
                            handleAutoUpload(files, form);
                        }
                    });
                    
                    function handleAutoUpload(files, form) {
                        // Validate files first
                        const validFiles = Array.from(files).filter(file => {
                            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                            if (!validTypes.includes(file.type)) {
                                showStatus(`Invalid file type: ${file.name}. Only JPEG, PNG, and WebP are allowed.`, 'error');
                                return false;
                            }
                            
                            if (file.size > 5 * 1024 * 1024) {
                                showStatus(`File too large: ${file.name}. Maximum size is 5MB.`, 'error');
                                return false;
                            }
                            
                            return true;
                        });
                        
                        if (validFiles.length === 0) {
                            return;
                        }
                        
                        // Show progress bar
                        progressBar.style.display = 'block';
                        statusDiv.style.display = 'none';
                        progressFill.style.width = '0%';
                        
                        const formData = new FormData();
                        
                        // Add all valid files
                        validFiles.forEach((file, index) => {
                            formData.append('room_images[]', file);
                        });
                        
                        formData.append('room_number', form.dataset.roomNumber);
                        formData.append('room_type', form.dataset.roomType);
                        formData.append('pax_group', form.dataset.paxGroup);
                        
                        const xhr = new XMLHttpRequest();
                        
                        // Upload progress
                        xhr.upload.addEventListener('progress', (e) => {
                            if (e.lengthComputable) {
                                const percentComplete = (e.loaded / e.total) * 100;
                                progressFill.style.width = percentComplete + '%';
                            }
                        });
                        
                        // Upload complete
                        xhr.addEventListener('load', () => {
                            progressBar.style.display = 'none';
                            
                            if (xhr.status === 200) {
                                try {
                                    const response = JSON.parse(xhr.responseText);
                                    
                                    if (response.success) {
                                        showStatus(response.message, 'success');
                                        
                                        // Remember current active tab
                                        const activeTab = document.querySelector('.room-nav-tab.active');
                                        const activeRoomType = activeTab ? activeTab.dataset.roomType : 'pricing';
                                        
                                        // Store active tab in sessionStorage
                                        sessionStorage.setItem('activeRoomTab', activeRoomType);
                                        
                                        // Reload the page to show new images
                                        setTimeout(() => {
                                            location.reload();
                                        }, 1500);
                                    } else {
                                        let errorMsg = 'Upload failed: ' + response.error;
                                        showStatus(errorMsg, 'error');
                                    }
                                } catch (e) {
                                    showStatus('Upload failed. Please try again.', 'error');
                                }
                            } else {
                                showStatus(`Upload failed. HTTP Status: ${xhr.status}`, 'error');
                            }
                        });
                        
                        // Upload error
                        xhr.addEventListener('error', () => {
                            progressBar.style.display = 'none';
                            showStatus('Upload failed. Please check your connection.', 'error');
                        });
                        
                        xhr.open('POST', 'simple_upload.php');
                        xhr.send(formData);
                    }
                    
                    function showStatus(message, type) {
                        statusDiv.className = `upload-status ${type}`;
                        statusDiv.textContent = message;
                        statusDiv.style.display = 'block';
                        
                        if (type === 'success') {
                            statusDiv.style.background = '#d4edda';
                            statusDiv.style.color = '#155724';
                            statusDiv.style.border = '1px solid #c3e6cb';
                        } else {
                            statusDiv.style.background = '#f8d7da';
                            statusDiv.style.color = '#721c24';
                            statusDiv.style.border = '1px solid #f5c6cb';
                        }
                        
                        // Hide status after 5 seconds for errors, 3 seconds for success
                        setTimeout(() => {
                            if (type === 'error') {
                                statusDiv.style.display = 'none';
                            }
                        }, type === 'error' ? 5000 : 3000);
                    }
                }
            });
            </script>
<?php include 'template_footer.php'; ?>