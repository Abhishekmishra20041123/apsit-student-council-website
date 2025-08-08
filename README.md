# APSIT Student Council Website

A comprehensive web application for APSIT Student Council management, built with PHP, MySQL, HTML, CSS, and JavaScript.

## 🌟 Features

- **Student Council Information** - Complete details about council members and structure
- **Events Management** - Create, manage, and display events
- **Announcements System** - Post and manage announcements
- **Profile Management** - User profiles and preferences
- **Admin Panel** - Complete administrative interface
- **File Upload System** - Upload and manage documents
- **Responsive Design** - Works on all devices
- **Academic Calendar** - View and manage academic schedules
- **Letter Management** - Submit and track letters
- **Meeting Management** - Schedule and manage meetings

## 🛠️ Technology Stack

- **Backend:** PHP 8.0+
- **Database:** MySQL
- **Frontend:** HTML5, CSS3, JavaScript
- **Framework:** Bootstrap
- **Server:** Apache/Nginx

## 📁 Project Structure

```
MY_PROJECT/
├── Admin/              # Admin panel functionality
├── Events/             # Events management system
├── Profile/            # User profile management
├── Announcements/      # Announcements system
├── Calendar/           # Academic calendar
├── resource/           # Study materials and resources
├── Letter/             # Letter management
├── Meeting/            # Meeting management
├── config.php          # Database configuration
├── deployment_config.php # Deployment settings
└── index.html          # Main homepage
```

## 🚀 Quick Start

### Prerequisites
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

### Installation
1. Clone the repository
2. Import the database from `apsit_database_2025-08-08_21-52-22.sql`
3. Configure `config.php` with your database credentials
4. Set up your web server to point to the project directory

### Database Setup
```sql
-- Import the database
mysql -u username -p database_name < apsit_database_2025-08-08_21-52-22.sql
```

## 🌐 Deployment

### Local Development
- Use XAMPP/WAMP for local development
- Access via `http://localhost/MY_PROJECT`

### Production Deployment
- Upload files to web server
- Configure database connection
- Set up email settings in `deployment_config.php`

## 📧 Configuration

### Email Settings
Update `deployment_config.php` with your email settings:
```php
'smtp_username' => 'your-email@gmail.com',
'smtp_password' => 'your-app-password',
'from_email' => 'your-email@gmail.com',
```

## 🔧 Database Tables

- `user_preferences` - User preferences and settings
- `events` - Event management
- `announcements` - Announcement system
- `profile` - User profiles
- `admins` - Admin accounts
- And more...

## 📱 Features Overview

### For Students
- View announcements and events
- Access study materials
- Submit letters and requests
- Manage personal profile

### For Administrators
- Post announcements
- Manage events
- Handle user requests
- Monitor system activity

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## 📄 License

This project is for APSIT Student Council use.

## 📞 Support

For support or questions, contact the development team.

---

**Developed for APSIT Student Council** 🎓
