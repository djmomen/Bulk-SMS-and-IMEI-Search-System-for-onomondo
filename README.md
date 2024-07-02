# Bulk SMS and IMEI Search System

## Description

This project is a comprehensive web-based application for sending bulk SMS messages and performing IMEI searches. It's designed to integrate seamlessly with the Onomondo API for efficient SIM card management and SMS sending capabilities. The system offers a user-friendly interface for managing SMS campaigns, retrieving SIM card information, and searching for devices by IMEI number.

## Features

- Send bulk SMS messages to multiple SIM cards simultaneously
- Search for SIM cards by IMEI number with detailed results
- Retrieve and display all SIM cards associated with your account
- Real-time progress tracking for SMS sending jobs with visual feedback
- Interactive dashboard featuring charts for SMS status and processing rate
- Comprehensive activity logging with downloadable log files
- Responsive design ensuring functionality on both desktop and mobile devices

## Technologies Used

- PHP 7.4+
- JavaScript (jQuery 3.6.0, Chart.js, GSAP 3.9.1 for animations)
- HTML5/CSS3
- Bootstrap 5.1.3
- Onomondo API
- HTTP_Request2 2.3.0

## Prerequisites

Before you begin, ensure you have met the following requirements:

- PHP 7.4 or higher installed on your server
- Composer installed for managing PHP dependencies
- Apache or Nginx web server
- MySQL database (optional, for future enhancements)
- An active Onomondo API key

## Installation

Follow these steps to get your development environment set up:

1. Clone the repository:
   ```
   git clone https://github.com/yourusername/bulk-sms-imei-search.git
   ```

2. Navigate to the project directory:
   ```
   cd bulk-sms-imei-search
   ```

3. Install PHP dependencies using Composer:
   ```
   composer require pear/http_request2
   ```

4. Configure your web server (Apache or Nginx) to point to the project's public directory.

5. Create and set permissions for required directories:
   ```
   mkdir jobs logs
   chmod 775 jobs logs
   ```

6. Update the API key in `functions.php`:
   Open the file and replace `'YOUR_API_KEY'` with your actual Onomondo API key.

7. Ensure the `logs/php_errors.log` file exists and is writable:
   ```
   touch logs/php_errors.log
   chmod 664 logs/php_errors.log
   ```



## Configuration

### Apache Configuration

If you're using Apache, ensure that mod_rewrite is enabled and that your virtual host is configured to allow .htaccess files. Here's a sample configuration:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /path/to/bulk-sms-imei-search/public

    <Directory /path/to/bulk-sms-imei-search/public>
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

### Nginx Configuration

If you're using Nginx, here's a sample server block configuration:

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/bulk-sms-imei-search/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```



## Usage

1. Access the application through your web browser by navigating to your configured domain.

2. Sending Bulk SMS:
   - Use the "Send Bulk SMS" form on the main page.
   - Enter SIM IDs (one per line) in the provided textarea.
   - Fill in the "From" field and compose your message.
   - Click "Send SMS" to initiate the bulk sending process.

3. IMEI Search:
   - Use the "IMEI Search" form on the main page.
   - Enter IMEI numbers (one per line) in the provided textarea.
   - Click "Search IMEI" to retrieve SIM information for the given IMEIs.

4. Retrieving All SIM Cards:
   - Click the "Retrieve All SIM Cards" button to fetch a list of all SIM cards associated with your account.

5. Monitoring Jobs:
   - The dashboard on the right side of the page provides real-time updates on SMS sending jobs.
   - View the progress bar, success/failure counts, and processing rate chart.

6. Activity Logging:
   - The Activity Log section at the bottom of the page displays system events.
   - Use the "Download Log" button to save the log file for offline viewing.

## Troubleshooting

If you encounter any issues:

1. Check the `logs/php_errors.log` file for any PHP-related errors.
2. Ensure that all directories (`jobs` and `logs`) have the correct write permissions.
3. Verify that your Onomondo API key is correctly set in the `functions.php` file.
4. If you're having issues with HTTP_Request2, ensure it's correctly installed via Composer.



## Acknowledgements

- [Onomondo API](https://onomondo.com/docs/api/) for providing the SMS and SIM card management capabilities
- [Bootstrap](https://getbootstrap.com) for the responsive front-end framework
- [jQuery](https://jquery.com) for simplifying JavaScript programming
- [Chart.js](https://www.chartjs.org) for beautiful, animated, and interactive charts
- [GSAP (GreenSock Animation Platform)](https://greensock.com/gsap/) for smooth animations
- [HTTP_Request2](https://pear.php.net/package/HTTP_Request2) for handling HTTP requests in PHP
