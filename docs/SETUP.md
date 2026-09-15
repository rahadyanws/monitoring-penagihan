# ENVIRONMENT SETUP & DEPLOYMENT GUIDE
## SISTEM MONITORING PENAGIHAN PLN UP3 INDRAMAYU

---

## 1. Kebutuhan Sistem Minimum
- **PHP**: Version 8.2 atau 8.3+ (Extensions: `pdo_pgsql` / `pdo_mysql`, `bcmath`, `mbstring`, `zip`, `gd`, `redis`).
- **Web Server**: Nginx atau Apache.
- **Database**: PostgreSQL 16+ (Disarankan) atau MySQL 8.0+.
- **In-Memory Cache / Queue**: Redis 7.0+.
- **Node.js**: v20+ & NPM (untuk kompilasi asset Tailwind CSS).
- **Composer**: v2.6+.

---

## 2. Langkah Instalasi Lokal (Development)

```bash
# 1. Clone repository
git clone https://github.com/pln-indramayu/monitoring-penagihan.git
cd monitoring-penagihan

# 2. Install PHP dependencies
composer install

# 3. Salin environment file dan generate application key
cp .env.example .env
php artisan key:generate

# 4. Konfigurasi .env sesuai database & redis lokal
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=pln_monitoring
# DB_USERNAME=postgres
# DB_PASSWORD=secret
# QUEUE_CONNECTION=redis

# 5. Jalankan migrasi dan seeder master organisasi
php artisan migrate --seed

# 6. Install Node dependencies & build assets
npm install
npm run dev

# 7. Jalankan antrean worker background (Wajib untuk pemrosesan file Excel)
php artisan queue:work --timeout=600 --tries=3

# 8. Jalankan local development server
php artisan serve
```

---

## 3. Konfigurasi Optimal untuk Upload File Besar (PHP & Nginx)

Untuk mencegah timeout dan batas ukuran upload terlampaui saat import Master Data PLN:

### Di `php.ini`:
```ini
upload_max_filesize = 100M
post_max_size = 100M
memory_limit = 512M
max_execution_time = 300
```

### Di Virtual Host Nginx (`/etc/nginx/sites-available/monitoring-pln`):
```nginx
client_max_body_size 100M;
fastcgi_read_timeout 300;
proxy_read_timeout 300;
```

### Konfigurasi Supervisor untuk Queue Worker (`/etc/supervisor/conf.d/pln-worker.conf`):
```ini
[program:pln-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/monitoring-penagihan/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/monitoring-penagihan/storage/logs/worker.log
```