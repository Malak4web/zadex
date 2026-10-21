# Zadex Tickets Backend & Help Desk Dashboard

نظام تذاكر الدعم الفني ولوحة التحكم لـ **Zadex** مبني باستخدام **Laravel 13** وقاعدة بيانات **SQLite**.

---

## 🚀 التشغيل السريع (Quick Start)

### 1. تشغيل خادم Laravel:
```bash
cd backend
php artisan serve
```
سيعمل الخادم على: `http://localhost:8000`

### 2. الوصول إلى لوحة تحكم التذاكر (Admin Dashboard):
افتح الرابط في المتصفح:
👉 **[http://localhost:8000/admin/tickets](http://localhost:8000/admin/tickets)**

---

## 🛠️ الميزات المتاحة:

1. **استقبال التذاكر عبر API:**
   - نقطة النهاية: `POST http://localhost:8000/api/tickets`
   - المعاملات: `name`, `email`, `subject`, `category`, `priority`, `message`.
   - يتم توليد كود تذكرة فريد تلقائياً (مثل `ZX-AR701`).

2. **الاستعلام عن حالة التذكرة:**
   - نقطة النهاية: `GET http://localhost:8000/api/tickets/{ticketNumber}`

3. **لوحة التحكم الإدارية (Admin Dashboard):**
   - إحصائيات فورية (إجمالي التذاكر، المفتوحة، قيد المعالجة، التي تم حلها، التذاكر العاجلة).
   - تصفية وبحث متقدم بالاسم والبريد ورقم التذكرة ومستوى الأولوية والتصنيف.
   - عرض تفاصيل التذكرة وسجل المحادثة كاملاً.
   - الرد المباشر على التذكرة وتحديث حالتها فورياً.
   - إمكانية حذف التذاكر.

4. **قاعدة البيانات:**
   - SQLite محلية سريعة وخفيفة (`database/database.sqlite`).
   - جاهزة ومهاجرة (`php artisan migrate`).
   - مهيأة ببيانات تجريبية أولية (`php artisan db:seed`).

5. **فحص الاختبارات (Testing):**
```bash
php artisan test
```
