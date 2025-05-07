# Hướng dẫn triển khai ứng dụng Dolive lên VPS

## Giới thiệu

Tài liệu này hướng dẫn các bước cơ bản để triển khai ứng dụng Dolive (bao gồm backend Laravel và service Node.js cho livestreaming) lên một máy chủ ảo (VPS) chạy hệ điều hành Ubuntu (khuyến nghị 20.04 LTS hoặc mới hơn) và cấu hình để chạy trên port 80.

## Yêu cầu hệ thống tối thiểu

*   VPS với ít nhất 2GB RAM, 2 CPU cores, và 25GB dung lượng ổ cứng (khuyến nghị cao hơn cho nhiều stream đồng thời).
*   Hệ điều hành Ubuntu 20.04 LTS hoặc 22.04 LTS.
*   Quyền truy cập root hoặc sudo.
*   Tên miền (domain) đã trỏ về IP của VPS (nếu muốn sử dụng tên miền thay vì IP).

## Phần 1: Cài đặt các thành phần cần thiết trên VPS

Kết nối vào VPS của bạn qua SSH.

### 1. Cập nhật hệ thống

```bash
sudo apt update && sudo apt upgrade -y
```

### 2. Cài đặt Nginx (Web Server)

Nginx sẽ được sử dụng làm reverse proxy cho ứng dụng Laravel và Node.js.

```bash
sudo apt install -y nginx
sudo systemctl start nginx
sudo systemctl enable nginx
```

### 3. Cài đặt PHP và các extensions cần thiết

Ứng dụng Dolive sử dụng Laravel, yêu cầu PHP. Chúng ta sẽ cài PHP 8.1 (hoặc phiên bản tương thích mới hơn).

```bash
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y # Kho PPA phổ biến cho PHP
sudo apt update
sudo apt install -y php8.1-fpm php8.1-cli php8.1-mysql php8.1-sqlite3 php8.1-mbstring php8.1-xml php8.1-curl php8.1-zip php8.1-gd php8.1-intl php8.1-bcmath
```

Kiểm tra phiên bản PHP:
```bash
php -v
```

### 4. Cài đặt Composer (Quản lý dependencies cho PHP)

```bash
curl -sS https://getcomposer.org/installer -o composer-setup.php
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
composer --version
```

### 5. Cài đặt Node.js và npm (Cho service livestreaming)

Chúng ta sẽ cài Node.js phiên bản 20.x (LTS).

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs
```

Kiểm tra phiên bản Node.js và npm:
```bash
node -v
npm -v
```

### 6. Cài đặt FFmpeg (Xử lý video cho livestream)

```bash
sudo apt install -y ffmpeg
ffmpeg -version
```

### 7. Cài đặt Git (Để clone mã nguồn)

```bash
sudo apt install -y git
```

### 8. Cài đặt SQLite (Cơ sở dữ liệu)

Ứng dụng hiện tại được cấu hình để sử dụng SQLite.

```bash
sudo apt install -y sqlite3
```

## Phần 2: Tải mã nguồn và cấu hình ứng dụng Laravel (Backend)

### 1. Clone mã nguồn từ GitHub

Di chuyển đến thư mục bạn muốn chứa dự án (ví dụ: `/var/www`).

```bash
sudo mkdir -p /var/www/dolive_project
cd /var/www/dolive_project
sudo git clone https://github.com/tiendung102k3/dolive1.git .
```

Lưu ý: Nếu kho private, bạn cần cấu hình key SSH hoặc sử dụng token khi clone.

### 2. Cấu hình thư mục Laravel

Di chuyển vào thư mục `dolive_app`:
```bash
cd /var/www/dolive_project/dolive_app
```

Cài đặt dependencies:
```bash
sudo composer install --optimize-autoloader --no-dev
```

Sao chép file `.env.example` thành `.env` và cấu hình:
```bash
sudo cp .env.example .env
sudo nano .env
```

Trong file `.env`, cập nhật các thông tin sau:
*   `APP_NAME=Dolive`
*   `APP_ENV=production`
*   `APP_KEY=` (Sẽ generate ở bước sau)
*   `APP_DEBUG=false`
*   `APP_URL=http://your_domain_or_ip` (Thay `your_domain_or_ip` bằng tên miền hoặc IP của VPS)
*   `DB_CONNECTION=sqlite`
*   `DB_DATABASE=/var/www/dolive_project/dolive_app/database/database.sqlite` (Đảm bảo đường dẫn này đúng và file có thể ghi được bởi web server)
*   `NODE_STREAMING_SERVICE_URL=http://localhost:4000` (Hoặc port bạn cấu hình cho Node.js service)

Generate APP_KEY:
```bash
sudo php artisan key:generate
```

Tạo file database SQLite và cấp quyền:
```bash
sudo touch /var/www/dolive_project/dolive_app/database/database.sqlite
sudo chmod 664 /var/www/dolive_project/dolive_app/database/database.sqlite
sudo chown www-data:www-data /var/www/dolive_project/dolive_app/database/database.sqlite
```

Chạy migrations:
```bash
sudo php artisan migrate --force
```

Cấu hình storage link:
```bash
sudo php artisan storage:link
```

Cấp quyền cho thư mục storage và bootstrap/cache:
```bash
sudo chown -R www-data:www-data /var/www/dolive_project/dolive_app/storage
sudo chown -R www-data:www-data /var/www/dolive_project/dolive_app/bootstrap/cache
sudo chmod -R 775 /var/www/dolive_project/dolive_app/storage
sudo chmod -R 775 /var/www/dolive_project/dolive_app/bootstrap/cache
```

Tối ưu hóa ứng dụng:
```bash
sudo php artisan config:cache
sudo php artisan route:cache
sudo php artisan view:cache
```

## Phần 3: Cấu hình Nginx cho ứng dụng Laravel

Tạo file cấu hình Nginx cho trang web Dolive:

```bash
sudo nano /etc/nginx/sites-available/dolive
```

Thêm nội dung sau vào file, thay `your_domain_or_ip` bằng tên miền hoặc IP của bạn:

```nginx
server {
    listen 80;
    server_name your_domain_or_ip;
    root /var/www/dolive_project/dolive_app/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Kích hoạt site và kiểm tra cấu hình Nginx:

```bash
sudo ln -s /etc/nginx/sites-available/dolive /etc/nginx/sites-enabled/
sudo nginx -t
```

Nếu không có lỗi, khởi động lại Nginx:
```bash
sudo systemctl restart nginx
```

Bây giờ bạn có thể truy cập ứng dụng Laravel qua `http://your_domain_or_ip`.

## Phần 4: Cấu hình và chạy Service Node.js (Streaming Service)

### 1. Cấu hình service Node.js

Di chuyển đến thư mục `dolive_streaming_service`:
```bash
cd /var/www/dolive_project/dolive_streaming_service
```

Cài đặt dependencies:
```bash
sudo npm install
```

Sao chép file `.env.example` (nếu có) hoặc tạo file `.env`:
```bash
sudo cp .env.example .env # Nếu file .env.example tồn tại trong repo
# Hoặc tạo mới:
sudo nano .env
```

Thêm nội dung sau vào file `.env` của service Node.js:
```
STREAMING_SERVICE_PORT=4000
FFMPEG_PATH=/usr/bin/ffmpeg # Hoặc đường dẫn đúng đến ffmpeg nếu khác
# AMQP_URL=amqp://localhost (Nếu bạn quyết định dùng RabbitMQ sau này)
# AMQP_QUEUE_NAME=dolive_stream_jobs
# USE_AMQP=false
```

### 2. Chạy service Node.js bằng PM2

PM2 là một process manager cho Node.js, giúp service chạy nền và tự khởi động lại khi có lỗi.

Cài đặt PM2:
```bash
sudo npm install pm2 -g
```

Khởi động service Node.js bằng PM2:
```bash
cd /var/www/dolive_project/dolive_streaming_service
sudo pm2 start index.js --name dolive-streaming-service
```

Lưu cấu hình PM2 để tự khởi động khi VPS reboot:
```bash
sudo pm2 startup
# Chạy lệnh mà PM2 gợi ý (ví dụ: sudo env PATH=$PATH:/usr/bin /usr/lib/node_modules/pm2/bin/pm2 startup systemd -u your_user --hp /home/your_user)
sudo pm2 save
```

Kiểm tra trạng thái service:
```bash
sudo pm2 list
```

Xem logs của service:
```bash
sudo pm2 logs dolive-streaming-service
```

## Phần 5: Cấu hình Firewall (Nếu có)

Nếu bạn sử dụng firewall (ví dụ: ufw), hãy đảm bảo mở port 80 (HTTP) và 443 (HTTPS nếu bạn cài SSL sau này):

```bash
sudo ufw allow 'Nginx Full'
sudo ufw enable # Nếu chưa enable
sudo ufw status
```

## Phần 6: Hoàn tất và Kiểm tra

*   Truy cập ứng dụng Laravel qua trình duyệt: `http://your_domain_or_ip`.
*   Đăng ký tài khoản, upload video và thử tạo một livestream.
*   Kiểm tra logs của Laravel (`/var/www/dolive_project/dolive_app/storage/logs/laravel.log`) và Node.js service (`pm2 logs dolive-streaming-service`) nếu có lỗi.

## Phần 7: (Tùy chọn) Cấu hình HTTPS với Let's Encrypt

Để bảo mật ứng dụng, bạn nên cấu hình HTTPS.

### 1. Cài đặt Certbot

```bash
sudo apt install -y certbot python3-certbot-nginx
```

### 2. Lấy chứng chỉ SSL

Thay `your_domain_or_ip` bằng tên miền của bạn (phải là tên miền, không phải IP).

```bash
sudo certbot --nginx -d your_domain_or_ip
```

Làm theo hướng dẫn trên màn hình. Certbot sẽ tự động cấu hình Nginx và gia hạn chứng chỉ.

### 3. Kiểm tra gia hạn tự động

```bash
sudo systemctl status certbot.timer
sudo certbot renew --dry-run
```

Sau khi hoàn tất, ứng dụng của bạn sẽ có thể truy cập qua `https://your_domain_or_ip`.

## Xử lý sự cố thường gặp

*   **Lỗi quyền (Permission denied):** Kiểm tra kỹ quyền của các thư mục `storage`, `bootstrap/cache` và file database SQLite. Đảm bảo user `www-data` (hoặc user chạy Nginx/PHP-FPM) có quyền ghi.
*   **Service Node.js không chạy:** Kiểm tra logs bằng `pm2 logs dolive-streaming-service`. Đảm bảo FFmpeg đã được cài đặt và có trong PATH hoặc `FFMPEG_PATH` trong `.env` của Node.js service là đúng.
*   **Không kết nối được đến service Node.js từ Laravel:** Kiểm tra `NODE_STREAMING_SERVICE_URL` trong file `.env` của Laravel. Đảm bảo service Node.js đang chạy trên đúng port và không bị firewall chặn.
*   **Lỗi 500 Server Error:** Kiểm tra logs của Laravel và Nginx (`/var/log/nginx/error.log`).

Chúc bạn triển khai thành công!

