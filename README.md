# Tina Mart Tenanet File Sharing

## 📌 Overview

Tina Mart Tenanet is a secure file-sharing platform that enables users to upload, scan, and download files. The system integrates ClamAV for malware scanning, AWS for cloud storage, and queue processing for efficient file handling.

---

## 🚀 Setup Instructions

### Prerequisites

- **Homebrew** (for macOS users)
- **PHP 8+**
- **Composer** (PHP dependency manager)
- **MySQL** (or compatible database)
- **Redis** (for queue management)
- **AWS CLI** (if using AWS services)
- **Supervisor** (for queue worker management in production)

### 1️⃣ Install Dependencies

```bash
brew install clamav redis mysql php composer
composer install
```

### 2️⃣ Configure ClamAV

```bash
sudo cp /opt/homebrew/etc/clamav/freshclam.conf.sample /opt/homebrew/etc/clamav/freshclam.conf
nano /opt/homebrew/etc/clamav/freshclam.conf
```

**Remove the ****`Example`**** line**, then run:

```bash
sudo mkdir -p /opt/homebrew/var/lib/clamav
sudo chown -R $(whoami) /opt/homebrew/var/lib/clamav
freshclam
```

### 3️⃣ Set Up Environment

Copy the `.env.example` and update configurations:

```bash
cp .env.example .env
nano .env
```

Set the following:

```ini
DB_DATABASE=your_database
DB_USERNAME=your_user
DB_PASSWORD=your_password
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
QUEUE_CONNECTION=redis
```

### 4️⃣ Run Migrations

```bash
php artisan migrate --seed
```

### 5️⃣ Start Services

```bash
php artisan serve &
redis-server &
supervisorctl start all # (for production queue handling)
```

### 6️⃣ Process Queues

```bash
php artisan queue:work
```

---

## 🏗️ Architecture Diagram

```
+------------+        +------------+        +------------+
|  Frontend  | -----> |  Backend   | -----> |   AWS S3   |
+------------+        +------------+        +------------+
                               |
                               v
                          +------------+
                          |  ClamAV    |
                          +------------+
```

---

## 🌩️ AWS Services Used

1. **AWS S3** – Stores uploaded files securely.
2. **AWS Lambda** – Triggers for scanning new uploads (optional).
3. **AWS SQS** – Manages queue processing.
4. **AWS CloudWatch** – Monitors system health and logs.

---

## 🔄 Running Queues

### Locally

```bash
php artisan queue:work --tries=3
```

### In AWS

Using **Amazon SQS**:

1. Set `QUEUE_CONNECTION=sqs` in `.env`.
2. Configure SQS in `config/queue.php`.
3. Start queue worker:
   ```bash
   php artisan queue:work --queue=default
   ```

---

## 📞 Support

For issues, contact **[support@filesharing.com](mailto\:support@filesharing.com)** or open a GitHub issue.

---

© 2025 Tina Mart Tenanet File Sharing

give me readme full file

