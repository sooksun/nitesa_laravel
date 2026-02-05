# 🐳 คู่มือติดตั้ง Docker สำหรับ NITESA

## 📋 ความต้องการของระบบ

- Docker 20.10+
- Docker Compose 2.0+
- พื้นที่ดิสก์ 2GB+
- RAM 2GB+

---

## ⚡ Quick Setup

### 1. Clone โปรเจคไปยังเซิร์ฟเวอร์

```bash
cd /DATA/AppData/www
git clone https://github.com/sooksun/nitesa_laravel.git nitesa
cd nitesa
```

### 2. ตั้งค่า Environment

```bash
# Copy environment file
cp .env.docker .env

# แก้ไขค่าที่จำเป็น
nano .env
```

**ค่าที่ต้องแก้ไข:**
- `DB_PASSWORD` - รหัสผ่าน database
- `MAIL_*` - ตั้งค่า email
- `GOOGLE_*` - ตั้งค่า Google OAuth (ถ้าใช้)

### 3. รัน Setup Script

```bash
chmod +x docker-setup.sh
./docker-setup.sh
```

---

## 🔧 Manual Setup

### 1. สร้าง .env

```bash
cp .env.docker .env
nano .env
```

### 2. Generate APP_KEY

```bash
# ใช้ OpenSSL
APP_KEY=$(openssl rand -base64 32)
echo "APP_KEY=base64:$APP_KEY"
```

### 3. Build และ Start Containers

```bash
# Build images
docker compose build

# Start containers
docker compose up -d

# ดู status
docker compose ps
```

### 4. Run Migrations

```bash
docker compose exec app php artisan migrate --force
```

### 5. Create Storage Link

```bash
docker compose exec app php artisan storage:link
```

### 6. Cache Configuration

```bash
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

---

## 📊 Container Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                        Port 9000                            │
│                           │                                 │
│                     ┌─────▼─────┐                          │
│                     │   Nginx   │                          │
│                     │  (Alpine) │                          │
│                     └─────┬─────┘                          │
│                           │                                 │
│                     ┌─────▼─────┐                          │
│                     │  PHP-FPM  │                          │
│                     │   (8.3)   │                          │
│                     └─────┬─────┘                          │
│                           │                                 │
│              ┌────────────┼────────────┐                   │
│              │                         │                    │
│        ┌─────▼─────┐             ┌─────▼─────┐             │
│        │   MySQL   │             │   Redis   │             │
│        │   (8.0)   │             │  (Alpine) │             │
│        └───────────┘             └───────────┘             │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## 🛠️ คำสั่งที่ใช้บ่อย

### Container Management

```bash
# ดู containers ทั้งหมด
docker compose ps

# Start containers
docker compose up -d

# Stop containers
docker compose down

# Restart containers
docker compose restart

# ดู logs
docker compose logs -f

# ดู logs เฉพาะ app
docker compose logs -f app
```

### เข้าถึง Container

```bash
# เข้า app container
docker compose exec app sh

# เข้า database
docker compose exec db mysql -u nitesa -p

# เข้า redis
docker compose exec redis redis-cli
```

### Artisan Commands

```bash
# Run migrations
docker compose exec app php artisan migrate

# Clear cache
docker compose exec app php artisan optimize:clear

# Tinker
docker compose exec app php artisan tinker

# Queue work (ถ้าไม่ใช้ queue container)
docker compose exec app php artisan queue:work
```

### Composer & NPM

```bash
# Install dependencies
docker compose exec app composer install

# Update dependencies
docker compose exec app composer update

# NPM (ถ้าต้องการ rebuild assets)
docker compose exec app npm install
docker compose exec app npm run build
```

---

## 🔄 การอัพเดทแอพพลิเคชัน

```bash
cd /DATA/AppData/www/nitesa

# Pull latest code
git pull origin main

# Rebuild containers (ถ้า Dockerfile มีการเปลี่ยนแปลง)
docker compose build

# Restart containers
docker compose up -d

# Run migrations
docker compose exec app php artisan migrate --force

# Clear and rebuild cache
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

---

## 💾 Backup & Restore

### Backup Database

```bash
# Backup
docker compose exec db mysqldump -u nitesa -p nitesa > backup_$(date +%Y%m%d_%H%M%S).sql

# หรือใช้ script
docker compose exec db sh -c 'mysqldump -u root -p"$MYSQL_ROOT_PASSWORD" nitesa' > backup.sql
```

### Restore Database

```bash
docker compose exec -T db mysql -u nitesa -p nitesa < backup.sql
```

### Backup Uploaded Files

```bash
tar -czvf storage_backup_$(date +%Y%m%d).tar.gz storage/app/public
```

---

## 🔍 Troubleshooting

### Container ไม่ start

```bash
# ดู logs
docker compose logs app
docker compose logs nginx
docker compose logs db

# ตรวจสอบ port ที่ใช้งาน
netstat -tulpn | grep 9000
```

### Database connection error

```bash
# ตรวจสอบ MySQL container
docker compose exec db mysql -u nitesa -p -e "SHOW DATABASES;"

# ตรวจสอบ connection จาก app
docker compose exec app php artisan tinker
>>> DB::connection()->getPdo();
```

### Permission errors

```bash
docker compose exec app chmod -R 775 storage bootstrap/cache
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### Clear all cache

```bash
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear
```

---

## 🔒 Security Notes

1. **เปลี่ยน default passwords** ในไฟล์ `.env`
2. **ไม่ expose port 3306** ออกภายนอก (ปัจจุบัน map เป็น 3307)
3. **ตั้งค่า firewall** ให้เฉพาะ port 9000 เข้าถึงได้
4. **Backup ข้อมูลสม่ำเสมอ**

---

## 📞 Support

หากมีปัญหา:

1. ตรวจสอบ logs: `docker compose logs -f`
2. ตรวจสอบ container status: `docker compose ps`
3. ดูเอกสาร: `PRODUCTION_SERVER_SETUP.md`
