# Paradise Hotel & Resort - Reservation System

A modern, professional hotel reservation website with user authentication and booking management.

## Features

- ✨ Modern, responsive design
- 🔐 User registration and login system
- 📅 Room reservation with date validation
- 💰 Automatic price calculation
- 📊 Reservation confirmation page
- 🎨 Beautiful UI with gradient backgrounds
- 📱 Mobile-friendly responsive design

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server (XAMPP recommended)
- Web browser

## Installation

1. **Copy files to your web server**
   - Place all files in your XAMPP `htdocs` folder (or your web server directory)

2. **Create the database**
   - Open phpMyAdmin (usually at `http://localhost/phpmyadmin`)
   - Create a new database named `hotel_reservation`
   - Import the `database_setup.sql` file OR run the SQL commands manually

3. **Configure database connection**
   - Open `config/database.php`
   - Update the database credentials if needed:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');  // Your MySQL password
     define('DB_NAME', 'hotel_reservation');
     ```

4. **Start your web server**
   - Start Apache and MySQL in XAMPP
   - Open your browser and navigate to `http://localhost/Hotel and Resort/`

## Usage

1. **Register a new account**
   - Click "Register" in the navigation bar
   - Fill in your details and create an account

2. **Login**
   - Use your username/email and password to login

3. **Make a reservation**
   - Select check-in and check-out dates
   - Choose room type (Regular, Deluxe, or VIP)
   - Select number of guests
   - View the automatically calculated price
   - Submit your reservation

4. **View confirmation**
   - After booking, you'll see a confirmation page with all details
   - You can print the confirmation if needed

## Room Types & Pricing

- **Regular Room**
  - 2 Pax: ₱1,500
  - 4-8 Pax: ₱3,000
  - 10-20 Pax: ₱6,000

- **Deluxe Room**
  - 2 Pax: ₱2,500
  - 4-8 Pax: ₱4,500
  - 10-20 Pax: ₱8,500

- **VIP Room**
  - 2 Pax: ₱4,000
  - 4-8 Pax: ₱7,000
  - 10-20 Pax: ₱12,000

## File Structure

```
Hotel and Resort/
├── assets/
│   ├── css/
│   │   └── style.css          # Main stylesheet
│   └── js/
│       └── script.js          # JavaScript functions
├── config/
│   ├── database.php           # Database configuration
│   └── auth.php               # Authentication functions
├── index.php                  # Home page & reservation form
├── login.php                  # Login page
├── register.php               # Registration page
├── logout.php                 # Logout handler
├── process.php                # Reservation processing
├── confirmation.php           # Reservation confirmation
├── database_setup.sql         # Database schema
└── README.md                  # This file
```

## Security Features

- Password hashing using PHP `password_hash()`
- SQL injection prevention with prepared statements
- Session management for user authentication
- Input validation and sanitization
- CSRF protection ready (can be enhanced)

## Customization

### Change Colors
Edit the gradient colors in `assets/css/style.css`:
```css
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
```

### Modify Room Prices
Edit the `calculatePrice()` function in `assets/js/script.js`

### Change Hotel Name
Update the hotel name in:
- `index.php` (navigation and hero section)
- `login.php` and `register.php` (page titles)

## Troubleshooting

**Database connection error:**
- Check if MySQL is running
- Verify database credentials in `config/database.php`
- Ensure database `hotel_reservation` exists

**Session not working:**
- Make sure `session_start()` is called before any output
- Check PHP session configuration

**Styles not loading:**
- Verify file paths are correct
- Check if CSS/JS files are accessible

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## License

This project is open source and available for educational purposes.

## Support

For issues or questions, please check the code comments or refer to PHP/MySQL documentation.

