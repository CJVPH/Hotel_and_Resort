<?php
require_once 'config/database.php';
require_once 'config/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paradise Hotel & Resort - Book Your Stay</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-hotel"></i>
                <span>Paradise Hotel & Resort</span>
            </div>
            <div class="nav-menu">
                <?php if (isLoggedIn()): ?>
                    <span class="nav-user"><i class="fas fa-user-circle"></i> Welcome, <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?>!</span>
                    <a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
                <?php else: ?>
                    <a href="login.php" class="nav-link"><i class="fas fa-sign-in-alt"></i> Login</a>
                    <a href="register.php" class="nav-link btn-nav-register"><i class="fas fa-user-plus"></i> Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <!-- Dynamic configuration options for multi-pax bookings -->
            <h1>Welcome to Paradise Hotel & Resort</h1>
            <p>Experience luxury, comfort, and unforgettable memories</p>
            <div class="hero-features">
                <div class="feature-item">
                    <i class="fas fa-wifi"></i>
                    <span>Free WiFi</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-swimming-pool"></i>
                    <span>Swimming Pool</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-utensils"></i>
                    <span>Restaurant</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-spa"></i>
                    <span>Spa & Wellness</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
            <div class="section-header">
            <h2><i class="fas fa-calendar-check"></i> Make a Reservation</h2>
            <?php if (!isLoggedIn()): ?>
                <p class="login-prompt">Please <a href="login.php">login</a> or <a href="register.php">register</a> to make a reservation</p>
            <?php endif; ?>
        </div>

        <div class="reservation-layout">
                <!-- Left Side: Reservation Form -->
                <div class="reservation-form-container">
                    <form id="reservationForm" action="process.php" method="post" class="reservation-form">
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Guest Name</label>
                        <input type="text" name="name" placeholder="Enter your full name" required value="<?php echo isLoggedIn() ? htmlspecialchars($_SESSION['full_name'] ?? '') : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-users"></i> Number of Guests</label>
                        <select name="guests" id="guests" onchange="calculatePrice()" required>
                            <option value="">Select Pax</option>
                            <option value="2">2 Pax</option>
                            <option value="8">4–8 Pax</option>
                            <option value="20">10–20 Pax</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-calendar-alt"></i> Check-in Date</label>
                        <input type="date" name="checkin" id="checkin" required min="<?php echo date('Y-m-d'); ?>" onchange="validateDates()">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-calendar-alt"></i> Check-out Date</label>
                        <input type="date" name="checkout" id="checkout" required min="<?php echo date('Y-m-d'); ?>" onchange="validateDates(); calculatePrice()">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <button type="button" id="showAvailabilityBtn" class="btn-secondary">Show Availability</button>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-bed"></i> Room Type</label>
                    <div class="room-options">
                        <div class="room-card" onclick="selectRoom('Regular')">
                            <input type="radio" name="room" id="room-regular" value="Regular" onchange="displayRoomPreview('Regular'); calculatePrice()" required>
                            <label for="room-regular">
                                <i class="fas fa-home"></i>
                                <span class="room-name">Regular</span>
                                <span class="room-desc">Comfortable & Affordable</span>
                            </label>
                        </div>
                        <div class="room-card" onclick="selectRoom('Deluxe')">
                            <input type="radio" name="room" id="room-deluxe" value="Deluxe" onchange="displayRoomPreview('Deluxe').then(() => calculatePrice());">
                            <label for="room-deluxe">
                                <i class="fas fa-star"></i>
                                <span class="room-name">Deluxe</span>
                                <span class="room-desc">Premium Comfort</span>
                            </label>
                        </div>
                        <div class="room-card" onclick="selectRoom('VIP')">
                            <input type="radio" name="room" id="room-vip" value="VIP" onchange="displayRoomPreview('VIP').then(() => calculatePrice());">
                            <label for="room-vip">
                                <i class="fas fa-crown"></i>
                                <span class="room-name">VIP</span>
                                <span class="room-desc">Ultimate Luxury</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="price-summary">
                    <div class="price-display">
                        <label><i class="fas fa-money-bill-wave"></i> Total Price</label>
                        <div class="price-value">
                            <span class="currency">₱</span>
                            <span id="price-display">0</span>
                        </div>
                        <input type="hidden" id="price" name="price" value="0">
                    </div>
                </div>

                <!-- Dynamic configuration options for multi-pax bookings -->
                <div id="configurationOptions" style="margin-top:1rem;">
                    <!-- JS will inject bedroom/amenity options here for 4-8 and 10-20 pax -->
                </div>

                        <button type="submit" class="btn-reserve">
                            <i class="fas fa-check-circle"></i> Reserve Now
                        </button>
                    </form>
                </div>

                <!-- Right Side: Room Preview -->
                <div class="room-preview-container">
                    <div class="room-preview-header">
                        <h3><i class="fas fa-images"></i> Room Preview</h3>
                        <p class="room-preview-subtitle">Select a room type to view details</p>
                    </div>
                    <div id="roomPreview" class="room-preview-content">
                        <div class="room-preview-placeholder">
                            <i class="fas fa-bed"></i>
                            <p>Please select a room type to see images and details</p>
                        </div>
                    </div>
                </div>
        </div>

        <!-- Features Section -->
        <div class="features-section">
            <div class="container">
                <h2>Why Choose Us?</h2>
                <div class="features-grid">
                    <div class="feature-box">
                        <i class="fas fa-shield-alt"></i>
                        <h3>Secure Booking</h3>
                        <p>Your information is safe with us</p>
                    </div>
                    <div class="feature-box">
                        <i class="fas fa-clock"></i>
                        <h3>24/7 Support</h3>
                        <p>We're here to help anytime</p>
                    </div>
                    <div class="feature-box">
                        <i class="fas fa-tag"></i>
                        <h3>Best Prices</h3>
                        <p>Competitive rates guaranteed</p>
                    </div>
                    <div class="feature-box">
                        <i class="fas fa-heart"></i>
                        <h3>Luxury Experience</h3>
                        <p>Unforgettable stays await</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Paradise Hotel & Resort. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
    <!-- Availability modal -->
    <div id="availabilityModal" class="modal" style="display:none;">
        <div class="modal-backdrop"></div>
        <div class="modal-dialog">
            <div class="modal-header">
                <h3><i class="fas fa-calendar-alt"></i> Availability</h3>
                <button type="button" class="modal-close" id="availabilityClose">&times;</button>
            </div>
            <div class="modal-body">
                <div style="display:flex;gap:0.5rem;align-items:center;margin-bottom:0.75rem;">
                    <label style="min-width:80px">Room</label>
                    <select id="availRoom">
                        <option value="Regular">Regular</option>
                        <option value="Deluxe">Deluxe</option>
                        <option value="VIP">VIP</option>
                    </select>
                    <label style="min-width:60px;margin-left:0.5rem;">Pax</label>
                    <select id="availPax">
                        <option value="2">2</option>
                        <option value="8">8</option>
                        <option value="20">20</option>
                    </select>
                    <button id="availLoad" class="btn-primary" style="margin-left:auto;">Load</button>
                </div>
                <div id="calendarContainer"></div>
            </div>
        </div>
    </div>
    <script>
        // Expose login state for JS
        window.isLoggedIn = <?php echo isLoggedIn() ? 'true' : 'false'; ?>;
    </script>
</body>
</html>
