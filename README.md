# 🏨 Paradise Hotel & Resort - Complete Booking System

A comprehensive hotel booking and management system with elegant design, full-featured admin panel, and multi-image room management.

## 🌟 Features

### **Public Website**
- ✅ **Room Booking System** - Professional booking experience with image galleries
- ✅ **Multiple Room Types** - Regular, Deluxe, and VIP accommodations
- ✅ **Guest Capacity Options** - 2, 4-8, or 10-20 guests with appropriate configurations
- ✅ **Townhouse Rooms** - Special 10-20 guest rooms with living areas and multiple bedrooms
- ✅ **Image Galleries** - Multiple photos per room (up to 10 images each)
- ✅ **Responsive Design** - Works perfectly on all devices

### **Admin Panel**
- ✅ **Room Management** - Upload up to 10 images per individual room
- ✅ **Drag & Drop Upload** - Easy image uploading with automatic processing
- ✅ **Pricing Control** - Set prices for different room types and guest counts
- ✅ **Reservation Management** - View and manage bookings
- ✅ **User Management** - Manage customer accounts
- ✅ **Photo Management** - Upload website photos (carousel, restaurant, etc.)

### **Room System**
- ✅ **18 Individual Rooms** - Each room can have unique photos
- ✅ **Room Layout** - Top row (101, 201, 301), Bottom row (102, 202, 302) for all capacities
- ✅ **Multiple Images** - Up to 10 photos per room with thumbnail galleries
- ✅ **Image Management** - Individual image deletion and sorting

## 🚀 Quick Start

### **1. Database Setup**
Import `database_setup.sql` into your MySQL database:
- Contains complete schema with all tables
- Includes default room prices and admin account
- Has troubleshooting SQL commands if needed

### **2. Admin Access**
- **URL:** `your-website.com/admin/`
- **Username:** `admin`
- **Password:** `admin123`

### **3. Upload Room Images**
1. Login to admin panel
2. Go to Room Management
3. Select room type tab (Regular/Deluxe/VIP)
4. Drag & drop up to 10 images per room
5. Images appear immediately in booking page

## 📁 Clean File Structure

```
├── admin/                  # Admin panel (production-ready)
│   ├── rooms.php          # Room management with multi-image upload
│   ├── settings.php       # Website photo management
│   ├── reservations.php   # Booking management
│   ├── users.php          # User management
│   └── simple_upload.php  # Clean image upload handler
├── api/                   # API endpoints
├── assets/               # CSS, JS, images
├── config/               # Database and auth configuration
├── uploads/              # Uploaded images
│   └── rooms/individual/ # Individual room photos
├── booking.php           # Customer booking page with galleries
├── index.php            # Homepage
└── database_setup.sql   # Complete database schema + troubleshooting
```

## 🎯 Room System Details

### **18 Individual Rooms**
- **Regular Rooms:** 101, 102, 103, 104, 105, 106
- **Deluxe Rooms:** 201, 202, 203, 204, 205, 206  
- **VIP Suites:** 301, 302, 303, 304, 305, 306

### **Guest Capacities**
- **2 Guests:** Standard rooms with regular beds
- **4-8 Guests:** Family rooms (Regular rooms use double deck beds)
- **10-20 Guests:** Townhouse-style with ground floor living/kitchen, second floor bedrooms

### **Image System**
- **Up to 10 images per room** - Each of the 18 rooms can have unique photos
- **Drag & drop upload** - No upload buttons needed, automatic processing
- **Image galleries** - Customers see all photos in booking page
- **Individual deletion** - Remove specific images with × button
- **Tab persistence** - Stay on current room type after upload

## 🖼️ Image Management

### **Supported Features**
- **Multiple images per room** - No unique constraints blocking uploads
- **Drag & drop interface** - Like settings page, automatic upload
- **File validation** - JPEG, PNG, WebP up to 5MB each
- **Automatic processing** - Unique filenames, database storage
- **Sort ordering** - Images maintain upload order
- **Clean interface** - No debugging clutter, professional appearance

### **Upload Process**
1. Select room type tab (Regular/Deluxe/VIP)
2. Drag images to any room's upload zone
3. Images upload automatically (no button needed)
4. Page reloads and stays on same tab
5. Images appear in booking page galleries

## 🛠️ Technical Details

### **Database Tables**
- `users` - Customer and admin accounts
- `reservations` - Booking records with payment info
- `room_prices` - Admin-controlled pricing
- `room_images` - Individual room photos (supports multiple per room)
- `website_photos` - General website images

### **Key Improvements**
- **No unique constraints** on room images (allows multiple images)
- **Clean code** - All debugging and testing files removed
- **Tab persistence** - Upload stays on current room type
- **Production ready** - No debug output or test files
- **Consolidated SQL** - All database commands in one file

### **Pricing Structure**
| Room Type | 2 Guests | 4-8 Guests | 10-20 Guests |
|-----------|----------|------------|--------------|
| Regular   | ₱1,500   | ₱3,000     | ₱6,000       |
| Deluxe    | ₱2,500   | ₱4,500     | ₱8,500       |
| VIP       | ₱4,000   | ₱7,000     | ₱12,000      |

## 🔧 Troubleshooting

All SQL commands are in `database_setup.sql`:

### **Fresh Installation**
Import the entire `database_setup.sql` file

### **Upload Issues**
Check the troubleshooting section in the SQL file for:
- Removing unique constraints
- Adding missing columns
- Testing multiple image insertion

### **Database Problems**
Use the verification commands in the SQL file to check:
- Table structure
- Index configuration
- Room prices
- Admin account

## 🎨 Design Features

### **Colors**
- **Primary:** Charcoal (#2C3E50, #34495E)
- **Accent:** Gold (#C9A961, #8B7355)
- **Modern design** with glass morphism effects

### **Room Amenities**
- **Realistic Philippine hotel setting**
- **Appropriate amenities** for each room type
- **Double deck beds** only for Regular rooms with 8 and 20 guests
- **Townhouse descriptions** for large group accommodations

## 📱 Mobile Responsive

Fully responsive design works on:
- Desktop computers
- Tablets  
- Mobile phones
- All modern browsers

---

**Paradise Hotel & Resort** - Clean, Professional, Production-Ready Booking System! 🏨✨