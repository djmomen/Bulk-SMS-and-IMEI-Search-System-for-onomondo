# Deployment Guide for Bulk SMS and IMEI Search System

## Prerequisites

- PHP 7.4 or higher
- Apache or Nginx web server
- MySQL database (optional, for future enhancements)
- Composer (for managing PHP dependencies)

## Steps to Deploy

1. Clone the repository or upload the project files to your web server.

2. Install PHP dependencies:
   ```
   composer install
   ```

3. Configure your web server:
   - Set the document root to the project's public directory
   - Ensure PHP has write permissions to the `jobs` and `logs` directories

4. Create and configure the necessary directories:
   ```
   mkdir jobs logs
   chmod 775 jobs logs
   ```

5. Update the API key in `functions.php`:
   Replace `'YOUR_API_KEY'` with your actual Onomondo API key.

6. Configure error logging:
   Ensure that the `php_errors.log` file exists and is writable:
   ```
   touch logs/php_errors.log
   chmod 664 logs/php_errors.log
   ```

7. Set up a cron job to clean up old job files (optional):
   ```
   0 0 * * * find /path/to/your/project/jobs -name "*.json" -mtime +7 -delete
   ```

8. Restart your web server:
   ```
   sudo service apache2 restart
   ```
   or
   ```
   sudo service nginx restart
   ```

9. Access the application through your web browser and test all functionalities.

## Troubleshooting

- If you encounter permission issues, ensure that your web server has the necessary read/write permissions for the project directories.
- Check the `php_errors.log` file for any error messages.
- Ensure that the `HTTP_Request2` library is properly installed and accessible.

## Updating the Application

To update the application:

1. Pull the latest changes from the repository
2. Run `composer update` to update any dependencies
3. Clear your browser cache to ensure you're using the latest JavaScript and CSS files

## Security Considerations

- Ensure that the `jobs` directory is not publicly accessible
- Use HTTPS to encrypt data in transit
- Regularly update PHP and all dependencies to their latest secure versions

