<?php
require_once 'auth.php';
require_once '../config/database.php';

$message = '';
$messageType = '';

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['images'])) {
    $roomType = $_POST['room_type'] ?? '';
    
    if (empty($roomType)) {
        $message = 'Please select a room type!';
        $messageType = 'error';
    } else {
        $uploadDir = '../uploads/rooms/' . strtolower($roomType) . '/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $uploadedCount = 0;
        $conn = getDBConnection();
        
        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $fileName = $_FILES['images']['name'][$key];
                $fileSize = $_FILES['images']['size'][$key];
                $fileType = $_FILES['images']['type'][$key];
                
                // Validate file type
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!in_array($fileType, $allowedTypes)) {
                    continue;
                }
                
                // Validate file size (max 5MB)
                if ($fileSize > 5 * 1024 * 1024) {
                    continue;
                }
                
                // Generate unique filename
                $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                $uniqueFileName = uniqid() . '_' . time() . '.' . $fileExtension;
                $filePath = $uploadDir . $uniqueFileName;
                
                // Move uploaded file
                if (move_uploaded_file($tmpName, $filePath)) {
                    // Save to database
                    $relativePath = 'uploads/rooms/' . strtolower($roomType) . '/' . $uniqueFileName;
                    $displayOrder = $uploadedCount;
                    
                    $stmt = $conn->prepare("INSERT INTO room_images (room_type, image_path, display_order) VALUES (?, ?, ?)");
                    $stmt->bind_param("ssi", $roomType, $relativePath, $displayOrder);
                    
                    if ($stmt->execute()) {
                        $uploadedCount++;
                    }
                    $stmt->close();
                }
            }
        }
        
        $conn->close();
        
        if ($uploadedCount > 0) {
            $message = "Successfully uploaded $uploadedCount image(s)!";
            $messageType = 'success';
        } else {
            $message = 'No images were uploaded. Please check file types and sizes.';
            $messageType = 'error';
        }
    }
}

// Get existing images for each room type
$conn = getDBConnection();
$roomTypes = ['Regular', 'Deluxe', 'VIP'];
$existingImages = [];

foreach ($roomTypes as $type) {
    $stmt = $conn->prepare("SELECT id, image_path, display_order FROM room_images WHERE room_type = ? ORDER BY display_order ASC");
    $stmt->bind_param("s", $type);
    $stmt->execute();
    $result = $stmt->get_result();
    $existingImages[$type] = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Room Images - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        .admin-header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .upload-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        .image-item {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            aspect-ratio: 1;
        }
        .image-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .delete-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(220, 53, 69, 0.9);
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }
        .delete-btn:hover {
            background: rgba(220, 53, 69, 1);
        }
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }
        .file-input-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }
        .file-input-label {
            display: block;
            padding: 2rem;
            border: 2px dashed #667eea;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            background: #f8f9fa;
            transition: all 0.3s;
        }
        .file-input-label:hover {
            background: #e9ecef;
            border-color: #5568d3;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-hotel"></i>
                <span>Paradise Hotel & Resort - Admin</span>
            </div>
            <div class="nav-menu">
                <span class="nav-user"><i class="fas fa-user-circle"></i> Administrator</span>
                <a href="index.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="../index.php" class="nav-link"><i class="fas fa-home"></i> View Site</a>
            </div>
        </div>
    </nav>

    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-images"></i> Upload Room Images</h1>
            <p>Upload images for Regular, Deluxe, and VIP rooms</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'error'; ?>" style="margin-bottom: 2rem;">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php foreach ($roomTypes as $roomType): ?>
            <div class="upload-section">
                <h2><i class="fas fa-bed"></i> <?php echo $roomType; ?> Room</h2>
                
                <form method="POST" enctype="multipart/form-data" style="margin-bottom: 2rem;">
                    <input type="hidden" name="room_type" value="<?php echo $roomType; ?>">
                    
                    <div class="form-group">
                        <label>Select Images (Multiple files allowed, max 5MB each)</label>
                        <div class="file-input-wrapper">
                            <input type="file" name="images[]" id="images_<?php echo $roomType; ?>" multiple accept="image/*" required>
                            <label for="images_<?php echo $roomType; ?>" class="file-input-label">
                                <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; margin-bottom: 0.5rem; color: #667eea;"></i><br>
                                <span>Click to select images or drag and drop</span><br>
                                <small style="color: #666;">JPEG, PNG, GIF, WebP (Max 5MB each)</small>
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-upload"></i> Upload Images
                    </button>
                </form>

                <?php if (!empty($existingImages[$roomType])): ?>
                    <h3>Existing Images (<?php echo count($existingImages[$roomType]); ?>)</h3>
                    <div class="images-grid">
                        <?php foreach ($existingImages[$roomType] as $image): ?>
                            <div class="image-item">
                                <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" alt="<?php echo $roomType; ?> Room">
                                <form method="POST" action="delete_image.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this image?');">
                                    <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                    <input type="hidden" name="image_path" value="<?php echo htmlspecialchars($image['image_path']); ?>">
                                    <button type="submit" class="delete-btn" title="Delete image">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color: #999; text-align: center; padding: 2rem;">No images uploaded yet for <?php echo $roomType; ?> Room</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>

