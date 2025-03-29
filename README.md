# Tina Mart Tenanet File Sharing System

## 🖼️ Image List

![File Upload Flow](/assets/email.png)
![User Dashboard](/assets/uploadingprogress.png)
![ClamAV Scanning](/assets/email.png)
![ClamAV Scanning](/assets/SES.png)


A secure file sharing system with malware scanning, cloud storage, and email notifications.

## 🚀 Features

- Secure file uploads with ClamAV malware scanning
- Cloud storage using AWS S3
- Email notifications via AWS SES
- Queue processing with AWS SQS
- Comprehensive monitoring with AWS CloudWatch

## ⚙️ Prerequisites

Ensure you have the following installed:

- Homebrew (macOS users)
- PHP 8+
- Composer (PHP dependency manager)
- MySQL (or compatible database)
- AWS CLI (for AWS configuration)
- ClamAV (for malware scanning)

## 🛠️ Installation

### 1️⃣ Install Dependencies

```bash
brew install clamav redis mysql php composer

Install PHP dependencies:
composer require stancl/tenancy aws/aws-sdk-php laravel/framework
composer install

2️⃣ Configure ClamAV
Set up ClamAV for malware scanning:

sudo cp /opt/homebrew/etc/clamav/freshclam.conf.sample /opt/homebrew/etc/clamav/freshclam.conf
nano /opt/homebrew/etc/clamav/freshclam.conf

Remove the Example line and update configurations, then run:

sudo mkdir -p /opt/homebrew/var/lib/clamav
sudo chown -R $(whoami) /opt/homebrew/var/lib/clamav
freshclam
3️⃣ Set Up Environment
Copy the .env.example and update configurations:

cp .env.example .env
nano .env

Update the following fields in the .env file:

DB_DATABASE=your_database
DB_USERNAME=your_user
DB_PASSWORD=your_password
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
QUEUE_CONNECTION=sqs
FILESYSTEM_DISK=s3
MAIL_MAILER=ses

4️⃣ Run Migrations and Set Up Tables
Create Database Migrations for users and files tables:

Run the following commands to create migration files for users, files, and tenanets tables:

php artisan make:migration create_users_table
php artisan make:migration create_files_table

4️⃣ Run Migrations
Run the database migrations and seed the database:

php artisan migrate --seed

5️⃣ Start Services
Run your Laravel server:
php artisan serve

6️⃣ Process Queues
To process queues locally, run the following command:

php artisan queue:work sqs -v

🏗️ Architecture Diagram

+------------+        +------------+        +------------+
|  Frontend  | -----> |  Backend   | -----> |   AWS S3   |
+------------+        +------------+        +------------+
                               |
                               v
                          +------------+
                          |  ClamAV    |
                          +------------+

🌩️ AWS Services Used
AWS S3: For securely storing uploaded files.

AWS SES: To send email notifications upon successful file uploads.

AWS SQS: Manages queue processing for background jobs.

AWS CloudWatch: Monitors the system's health and logs events.

AWS IAM: Manages security and permissions for accessing AWS resources.

AWS Secrets Manager: Securely stores sensitive information, such as AWS keys and environment variables.

🔄 Running Queues
Locally
To run the queue worker locally, use:

bash
Copy
Edit
php artisan queue:work --tries=3
In AWS
Set QUEUE_CONNECTION=sqs in .env.

Configure AWS SQS in config/queue.php.

Start the queue worker on AWS:

bash
Copy
Edit
php artisan queue:work --queue=default
📧 Email Notifications
After a successful file upload, an email will be sent to the uploader with the following details:

File Name: The name of the uploaded file.

File Size: The size of the uploaded file.

Download Link: A temporary, pre-signed URL for downloading the file.

This is done using Amazon SES for reliable and cost-effective email delivery.

📊 Monitoring & Logging
All application logs, including errors and critical events, are sent to AWS CloudWatch for real-time monitoring and analysis.

📞 Support
For issues or support, feel free to reach out via email at support@filesharing.com or open an issue on the project’s GitHub repository.

© 2025 Tina Mart Tenanet File Sharing
You can copy this content into a file named `README.md` to use it in your project. Let me know if you need further adjustments!

