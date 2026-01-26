# ✅ Production Deployment Checklist

**วันที่:** _______________  
**ผู้ Deploy:** _______________  
**Server:** _______________

---

## 📋 Pre-Deployment

### Code Quality
- [ ] Code Quality Score ≥ 95%
- [ ] All tests passing
- [ ] No debug code (`dd()`, `dump()`, etc.)
- [ ] Code reviewed และ approved
- [ ] `.env.example` updated

### Security
- [ ] `.env` file not in Git
- [ ] `APP_DEBUG=false` in production
- [ ] Strong `APP_KEY` generated
- [ ] Database credentials secure และ strong
- [ ] File permissions correct
- [ ] SSL/HTTPS configured
- [ ] Security headers configured

### Environment
- [ ] Server requirements met
- [ ] PHP 8.1+ installed
- [ ] MySQL 8.0+ installed
- [ ] Redis installed (recommended)
- [ ] Nginx/Apache configured
- [ ] SSL certificate installed

### Backup
- [ ] Database backup created
- [ ] Files backup created
- [ ] Backup verified และ tested

---

## 🚀 Deployment Steps

### 1. Code Deployment
- [ ] Code pulled from repository
- [ ] Branch verified (main/production)
- [ ] Dependencies installed (`composer install --no-dev`)
- [ ] Frontend assets built (`npm run build`)

### 2. Database
- [ ] Database backup created
- [ ] Migrations run (`php artisan migrate --force`)
- [ ] Database indexes verified
- [ ] Seeders run (if needed)

### 3. Configuration
- [ ] `.env` file configured
- [ ] `APP_KEY` generated
- [ ] Database credentials correct
- [ ] Mail configuration correct
- [ ] Cache configuration correct
- [ ] Queue configuration correct

### 4. Optimization
- [ ] Config cached (`php artisan config:cache`)
- [ ] Route cached (`php artisan route:cache`)
- [ ] View cached (`php artisan view:cache`)
- [ ] Event cached (`php artisan event:cache`)
- [ ] Autoloader optimized (`composer dump-autoload --optimize`)

### 5. File Storage
- [ ] Storage link created (`php artisan storage:link`)
- [ ] Permissions set correctly
- [ ] File storage tested

### 6. Queue Workers
- [ ] Supervisor configured
- [ ] Queue workers started
- [ ] Queue workers verified

### 7. Web Server
- [ ] Nginx/Apache configured
- [ ] SSL certificate installed
- [ ] Web server restarted
- [ ] Web server verified

---

## ✅ Post-Deployment Testing

### Functional Testing
- [ ] Login/Logout ทำงาน
- [ ] Dashboard แสดงข้อมูล
- [ ] สร้าง Supervision ทำงาน
- [ ] แก้ไข Supervision ทำงาน
- [ ] Upload ไฟล์ ทำงาน
- [ ] Download ไฟล์ ทำงาน
- [ ] ส่ง Email notification ทำงาน
- [ ] Queue jobs ทำงาน
- [ ] API endpoints ทำงาน
- [ ] Activity Log ทำงาน

### Performance Testing
- [ ] Page load time < 2 seconds
- [ ] API response time < 500ms
- [ ] Database queries optimized
- [ ] Cache working correctly
- [ ] Assets loading correctly

### Security Testing
- [ ] SSL certificate valid
- [ ] HTTPS redirect working
- [ ] Security headers present
- [ ] CSRF protection working
- [ ] SQL injection protection
- [ ] XSS protection
- [ ] File upload restrictions

### Monitoring
- [ ] Logs being written
- [ ] Queue workers running
- [ ] Cache working
- [ ] Database connections OK
- [ ] Error tracking setup (if applicable)

---

## 🔍 Verification

### Server Health
- [ ] CPU usage < 80%
- [ ] Memory usage < 80%
- [ ] Disk space > 20%
- [ ] Network connectivity OK

### Application Health
- [ ] Application accessible
- [ ] No 500 errors
- [ ] No critical errors in logs
- [ ] Queue processing normally
- [ ] Cache hit rate > 70%

### Database Health
- [ ] Database accessible
- [ ] No slow queries
- [ ] Connections within limits
- [ ] Backup successful

---

## 📝 Documentation

- [ ] Deployment documented
- [ ] Configuration documented
- [ ] Backup procedures documented
- [ ] Rollback procedures documented
- [ ] Monitoring setup documented
- [ ] Team notified

---

## 🆘 Rollback Plan

### If Issues Detected
- [ ] Rollback procedure documented
- [ ] Backup available
- [ ] Rollback tested
- [ ] Team notified

### Rollback Steps
1. [ ] Stop application
2. [ ] Restore code from backup
3. [ ] Restore database (if needed)
4. [ ] Restore files (if needed)
5. [ ] Clear cache
6. [ ] Restart services
7. [ ] Verify application

---

## ✅ Sign-off

**Deployed by:** _______________  
**Date:** _______________  
**Time:** _______________  
**Status:** ☐ Success  ☐ Failed  ☐ Partial

**Notes:**
```
_________________________________________________
_________________________________________________
_________________________________________________
```

---

## 📞 Emergency Contacts

- **Technical Lead:** _______________
- **DevOps:** _______________
- **Database Admin:** _______________
- **Server Admin:** _______________

---

**🎉 Deployment Completed!**
