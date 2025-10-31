# Nirali Makeup Studio

A professional makeup studio website with appointment booking, gallery, and admin panel.

## Features

- Responsive design for all devices
- Online appointment booking system
- Photo gallery with categories
- Blog section (optional)
- Admin dashboard for managing content
- Contact form with email notifications
- SEO optimized
- Secure authentication system
- Database backup functionality

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher / MariaDB 10.3 or higher
- Apache/Nginx web server
- SSL Certificate (recommended)

## Installation

1. **Clone the repository**
   ```bash
   git clone [repository-url] nirali-makeup-studio
   cd nirali-makeup-studio
   ```

2. **Set up the database**
   - Create a new MySQL database
   - Import the database schema from `database/nirali_makeup.sql`
   - Update database credentials in `includes/config.php`

3. **Configure the website**
   - Rename `includes/config.production.php` to `includes/config.php`
   - Update site URL, email settings, and other configurations
   - Set proper file permissions:
     ```bash
     chmod 755 ./
     chmod 644 .htaccess
     chmod 755 uploads/
     chmod 644 includes/config.php
     ```

4. **Set up .htpasswd protection (recommended)**
   - Run `admin/generate-htpasswd.php` to create a .htpasswd file
   - Move the .htpasswd file outside the web root
   - Update the AuthUserFile path in `admin/.htaccess`
   - Delete the generator script after use

5. **Generate sitemap**
   - Access `sitemap-generator.php` in your browser
   - The sitemap will be automatically generated and saved as `sitemap.xml`
   - Submit the sitemap to search engines

## Security

- Keep PHP and all dependencies updated
- Use strong passwords for all accounts
- Regularly backup your database and files
- Remove unused files and plugins
- Monitor server logs for suspicious activity
- Keep the admin area protected with .htaccess authentication

## Maintenance

### Database Backups
Regularly backup your database using the admin panel or by running:
```bash
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql
```

### Updating
1. Backup your database and files
2. Download the latest version
3. Replace all files except:
   - `includes/config.php`
   - `uploads/` directory
   - Any custom themes or plugins
4. Test the website thoroughly

## File Structure

```
nirali-makeup-studio/
├── admin/                  # Admin panel files
├── assets/                 # Static assets (CSS, JS, images)
│   ├── css/               # Stylesheets
│   ├── js/                # JavaScript files
│   └── images/            # Website images
├── includes/               # PHP includes and configuration
├── uploads/                # User-uploaded files
├── .htaccess              # Apache configuration
├── index.php              # Main entry point
├── robots.txt             # Search engine instructions
└── sitemap.xml            # Generated sitemap
```

## Troubleshooting

### Common Issues

#### 500 Internal Server Error
- Check file permissions
- Verify .htaccess syntax
- Check PHP error logs

#### Database Connection Failed
- Verify database credentials in `includes/config.php`
- Check if MySQL server is running
- Ensure the database user has proper permissions

#### White Screen of Death
- Enable error reporting in `includes/config.php`
- Check PHP version compatibility
- Verify all required PHP extensions are installed

## Support

For support, please contact:
- Email: support@niralimakeupstudio.com
- Phone: +91 98765 43210

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Credits

- [Bootstrap](https://getbootstrap.com/)
- [Font Awesome](https://fontawesome.com/)
- [jQuery](https://jquery.com/)
- [PHPMailer](https://github.com/PHPMailer/PHPMailer) (for email functionality)
