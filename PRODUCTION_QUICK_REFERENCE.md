# 📋 Production Quick Reference

**Server:** http://203.172.184.47:9000/  
**สำหรับ:** การใช้งาน Production ช่วงแรก

---

## ⚡ Quick Commands

### Setup
```bash
# Run production setup
./production-setup.sh

# Or manual setup
cp .env.production.server .env
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

### Maintenance
```bash
# Clear all cache
php artisan optimize:clear

# Rebuild cache
php artisan optimize

# View logs
tail -f storage/logs/laravel.log

# Check application status
php artisan about
```

### Database
```bash
# Backup database
./backup-db.sh

# Or manual backup
mysqldump -u root -p nitesa > backup.sql
```

---

## 🔧 Important Settings

### .env Configuration
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://203.172.184.47:9000
LOG_LEVEL=error
FILESYSTEM_DISK=public
QUEUE_CONNECTION=sync  # เปลี่ยนเป็น database/redis เมื่อพร้อม
CACHE_DRIVER=file      # เปลี่ยนเป็น redis เมื่อพร้อม
```

### File Permissions
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## ✅ Testing Checklist

- [ ] Login/Logout
- [ ] Dashboard
- [ ] Create/Edit Supervision
- [ ] File Upload/Download
- [ ] Workflow (Submit/Approve/Publish)
- [ ] Email Notifications
- [ ] Activity Log

---

## 🆘 Common Issues

### 500 Error
```bash
php artisan optimize:clear
tail -f storage/logs/laravel.log
```

### File Upload Failed
```bash
php artisan storage:link
chmod -R 775 storage
```

### Session Issues
```bash
rm -rf storage/framework/sessions/*
chmod -R 775 storage/framework/sessions
```

---

## 📞 Quick Links

- 📖 [Full Setup Guide](./PRODUCTION_SERVER_SETUP.md)
- 📖 [Deployment Guide](./PRODUCTION_DEPLOYMENT.md)
- 📋 [Checklist](./DEPLOYMENT_CHECKLIST.md)

---

**Last Updated:** _______________
