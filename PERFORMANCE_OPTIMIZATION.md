# ⚡ คู่มือการปรับปรุงประสิทธิภาพ (Performance Optimization)

## 📖 ภาพรวม

เอกสารนี้อธิบายการปรับปรุงประสิทธิภาพที่ได้ทำไปแล้วและคำแนะนำเพิ่มเติมสำหรับระบบ NITESA

---

## ✅ การปรับปรุงที่ทำไปแล้ว

### 1. ✅ Eager Loading (N+1 Query Prevention)

ระบบใช้ Eager Loading อย่างถูกต้องในหลายจุด:

#### ตัวอย่างการใช้งาน:
```php
// ✅ ดี - ใช้ Eager Loading
Supervision::with(['school', 'user', 'indicators', 'attachments'])
    ->get();

// ❌ ไม่ดี - N+1 Query Problem
Supervision::all(); // แล้วเรียก $supervision->school ใน loop
```

#### จุดที่ใช้ Eager Loading:
- ✅ `SupervisionList` - `.with(['school', 'user'])`
- ✅ `SupervisionShow` - `.with([...])` ใน mount()
- ✅ `SchoolShow` - `.with(['networkGroupRelation', 'supervisions'])`
- ✅ `SchoolList` - `.with(['networkGroupRelation', 'supervisions'])`
- ✅ `ActivityLogIndex` - `.with(['causer', 'subject'])`

---

### 2. ✅ Dashboard Caching

เพิ่ม Caching สำหรับ Dashboard statistics เพื่อลด Database queries:

#### Cache Duration:
- **Statistics**: 5 นาที (300 วินาที)
- **Academic Years**: 1 ชั่วโมง (3600 วินาที)
- **Yearly Trend**: 1 ชั่วโมง
- **Recent Supervisions**: 1 นาที

#### Cache Keys:
```php
"dashboard.stats.{userId}.{academicYear}"
"dashboard.status_chart.{userId}.{academicYear}"
"dashboard.policy_usage.{academicYear}"
"dashboard.indicator_radar.{academicYear}"
"dashboard.indicator_donut.{academicYear}"
"dashboard.network_group.{academicYear}"
"dashboard.district.{academicYear}"
"dashboard.supervisor_performance.{academicYear}"
"dashboard.recent_supervisions.{userId}"
```

#### Cache Invalidation:
Cache จะถูกล้างอัตโนมัติเมื่อ:
- สร้าง/แก้ไขการนิเทศ
- เปลี่ยนสถานะการนิเทศ (submit, approve, reject, publish)

```php
// เรียกใช้ใน SupervisionForm และ SupervisionShow
DashboardSummary::clearCache($academicYear);
```

---

### 3. ✅ Queue System สำหรับ Notifications

Notifications ทำงานใน background queue:

#### Configuration:
```env
QUEUE_CONNECTION=database
```

#### Benefits:
- ⚡ ไม่บล็อก user request
- 🔄 Retry อัตโนมัติถ้าส่งไม่สำเร็จ
- 📊 Monitor ได้ผ่าน `php artisan queue:monitor`
- 🎯 Scale ได้ด้วย multiple workers

#### Queue Worker:
```bash
# Development
php artisan queue:work

# Production (Supervisor)
# ดู NOTIFICATION_SETUP.md สำหรับการตั้งค่า
```

---

## 🚀 คำแนะนำเพิ่มเติม

### 1. Database Indexing

เพิ่ม Index สำหรับคอลัมน์ที่ค้นหาบ่อย:

```sql
-- Supervision table
CREATE INDEX idx_supervision_status ON supervision(status);
CREATE INDEX idx_supervision_academic_year ON supervision(academicYear);
CREATE INDEX idx_supervision_school_date ON supervision(schoolId, date);
CREATE INDEX idx_supervision_user ON supervision(userId);

-- School table
CREATE INDEX idx_school_district ON school(district);
CREATE INDEX idx_school_network_group ON school(networkGroupId);

-- Indicator table
CREATE INDEX idx_indicator_supervision ON indicator(supervisionId);
CREATE INDEX idx_indicator_level ON indicator(level);

-- Activity Log
CREATE INDEX idx_activity_created_at ON activity_log(created_at);
CREATE INDEX idx_activity_causer ON activity_log(causer_id);
CREATE INDEX idx_activity_subject ON activity_log(subject_type, subject_id);
```

#### Migration Example:
```php
// database/migrations/YYYY_MM_DD_add_performance_indexes.php
public function up()
{
    Schema::table('supervision', function (Blueprint $table) {
        $table->index('status');
        $table->index('academicYear');
        $table->index(['schoolId', 'date']);
        $table->index('userId');
    });
}
```

---

### 2. Query Optimization

#### ใช้ select() เพื่อเลือกเฉพาะคอลัมน์ที่ต้องการ:
```php
// ✅ ดี - เลือกเฉพาะคอลัมน์ที่ใช้
Supervision::select('id', 'schoolId', 'status', 'date')
    ->with(['school:id,name'])
    ->get();

// ❌ ไม่ดี - ดึงทุกคอลัมน์
Supervision::with('school')->get();
```

#### ใช้ chunk() สำหรับข้อมูลจำนวนมาก:
```php
// ✅ ดี - ประมวลผลทีละ batch
Supervision::chunk(100, function ($supervisions) {
    foreach ($supervisions as $supervision) {
        // Process
    }
});

// ❌ ไม่ดี - โหลดทั้งหมดเข้า memory
Supervision::all()->each(function ($supervision) {
    // Process
});
```

---

### 3. Response Caching

#### Cache API Responses:
```php
// app/Http/Controllers/Api/AnalyticsController.php
public function stats(Request $request): JsonResponse
{
    $cacheKey = "api.stats.{$request->user()->id}.{$request->academicYear}";
    
    return Cache::remember($cacheKey, 300, function () use ($request) {
        // Query logic
        return response()->json($data);
    });
}
```

#### Cache Blade Views (สำหรับ static content):
```php
// ใน Controller
Cache::remember('view.schools.list', 3600, function () {
    return view('schools.list', $data)->render();
});
```

---

### 4. Asset Optimization

#### Vite Configuration:
ตรวจสอบ `vite.config.js`:
```js
export default defineConfig({
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    'vendor': ['alpinejs', 'chart.js'],
                }
            }
        }
    }
});
```

#### Image Optimization:
- ใช้ WebP format
- Lazy loading สำหรับ images
- Responsive images (srcset)

---

### 5. Session & Cache Driver

#### สำหรับ Production:
```env
# ใช้ Redis สำหรับ Cache และ Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

#### Benefits:
- ⚡ เร็วกว่า file-based cache
- 🔄 Shared cache ระหว่าง servers
- 📊 Monitor ได้ผ่าน Redis CLI

---

### 6. Database Query Monitoring

#### Enable Query Log:
```php
// app/Providers/AppServiceProvider.php
public function boot()
{
    if (config('app.debug')) {
        DB::listen(function ($query) {
            \Log::info($query->sql, [
                'bindings' => $query->bindings,
                'time' => $query->time
            ]);
        });
    }
}
```

#### ใช้ Laravel Debugbar:
```bash
composer require barryvdh/laravel-debugbar --dev
```

---

## 📊 Performance Metrics

### Target Metrics:

| Metric | Target | Current |
|--------|--------|---------|
| Page Load Time | < 2s | - |
| Database Queries per Page | < 10 | - |
| Cache Hit Rate | > 80% | - |
| Queue Processing Time | < 5s | - |

### Monitoring Tools:

1. **Laravel Telescope** (Development)
```bash
composer require laravel/telescope --dev
php artisan telescope:install
```

2. **Laravel Pulse** (Production)
```bash
composer require laravel/pulse
php artisan pulse:install
```

3. **New Relic / Datadog** (Enterprise)

---

## 🔧 Configuration Checklist

### Development:
- [x] Eager Loading ใช้งาน
- [x] Dashboard Caching
- [x] Queue สำหรับ Notifications
- [ ] Database Indexes
- [ ] Query Optimization
- [ ] Asset Optimization

### Production:
- [ ] Redis Cache Driver
- [ ] Redis Session Driver
- [ ] Redis Queue Driver
- [ ] Database Indexes
- [ ] CDN สำหรับ Static Assets
- [ ] Image Optimization
- [ ] Monitoring Setup
- [ ] Performance Testing

---

## 🧪 Performance Testing

### 1. Load Testing
```bash
# ใช้ Apache Bench
ab -n 1000 -c 10 https://yoursite.com/dashboard

# หรือใช้ Laravel Dusk
php artisan dusk
```

### 2. Database Query Analysis
```sql
-- MySQL
EXPLAIN SELECT * FROM supervision WHERE status = 'PUBLISHED';

-- PostgreSQL
EXPLAIN ANALYZE SELECT * FROM supervision WHERE status = 'PUBLISHED';
```

### 3. Cache Hit Rate
```php
// Monitor cache performance
$hits = Cache::get('cache.hits', 0);
$misses = Cache::get('cache.misses', 0);
$hitRate = $hits / ($hits + $misses) * 100;
```

---

## 📈 Best Practices

### 1. Database
- ✅ ใช้ Indexes สำหรับ foreign keys และ columns ที่ค้นหาบ่อย
- ✅ ใช้ Eager Loading แทน N+1 queries
- ✅ ใช้ select() เพื่อเลือกเฉพาะคอลัมน์ที่ต้องการ
- ✅ ใช้ pagination สำหรับ large datasets
- ✅ ใช้ database transactions สำหรับ multiple operations

### 2. Caching
- ✅ Cache expensive queries (aggregations, statistics)
- ✅ ใช้ appropriate cache duration
- ✅ Clear cache เมื่อข้อมูลเปลี่ยนแปลง
- ✅ ใช้ cache tags สำหรับ grouped invalidation

### 3. Code
- ✅ ใช้ lazy loading สำหรับ relationships ที่ไม่จำเป็น
- ✅ ใช้ chunk() สำหรับ large datasets
- ✅ ใช้ queue สำหรับ heavy operations
- ✅ ใช้ database transactions

### 4. Frontend
- ✅ Lazy load images
- ✅ Minify CSS/JS
- ✅ Use CDN for static assets
- ✅ Implement pagination
- ✅ Use debounce for search

---

## 🐛 Troubleshooting

### ปัญหา: Dashboard ช้า

**สาเหตุ:**
- ไม่มี cache
- Query มากเกินไป
- ไม่มี indexes

**แก้ไข:**
1. ตรวจสอบ cache ทำงานหรือไม่: `php artisan cache:clear`
2. ตรวจสอบ queries: เปิด Debugbar
3. เพิ่ม indexes

### ปัญหา: Queue Jobs ค้าง

**สาเหตุ:**
- Queue worker ไม่ทำงาน
- Jobs fail มากเกินไป

**แก้ไข:**
1. ตรวจสอบ worker: `ps aux | grep queue:work`
2. ดู failed jobs: `php artisan queue:failed`
3. Restart worker: `php artisan queue:restart`

### ปัญหา: Memory Limit

**สาเหตุ:**
- โหลดข้อมูลมากเกินไป
- ไม่ใช้ chunk()

**แก้ไข:**
1. เพิ่ม memory limit ใน `php.ini`
2. ใช้ `chunk()` แทน `all()`
3. ใช้ `cursor()` สำหรับ large datasets

---

## 📚 เอกสารเพิ่มเติม

- [Laravel Performance](https://laravel.com/docs/10.x/performance)
- [Database Optimization](https://laravel.com/docs/10.x/queries#database-performance)
- [Caching](https://laravel.com/docs/10.x/cache)
- [Queues](https://laravel.com/docs/10.x/queues)

---

## 🆘 การติดต่อสนับสนุน

หากมีปัญหาหรือข้อสงสัย:
- 📧 Email: support@nitesa.go.th
- 📖 Logs: `storage/logs/laravel.log`

---

**⚡ Performance Optimization พร้อมใช้งาน!**
