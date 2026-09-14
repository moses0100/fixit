# FixIT — คู่มือใช้งานและอธิบายโค้ด Laravel
อัปเดต 8 กันยายน 2026

## ผลงานเวอร์ชันนี้

ระบบหลัก (MVP) ใช้ Laravel 12 + MySQL + Breeze Authentication + Blade + Bootstrap 5
พัฒนาต่อยอดใน C:\xampp\htdocs\fixit จากงานวันที่ 7 กันยายน

- สมัครสมาชิก เข้าสู่ระบบ ออกจากระบบ และจัดการบัญชี
- User Dashboard และ Admin Dashboard มีจำนวนงานแยกสถานะ
- แจ้งซ่อมพร้อมรายละเอียดอุปกรณ์ เบอร์ติดต่อ ความเร่งด่วน และรูปภาพ
- เลขแจ้งซ่อมอัตโนมัติ REP-ปี-เลข ID อย่างน้อย 5 หลัก
- User ดูได้เฉพาะงานของตนเอง รวมถึงรูปแนบ
- แก้ไข/ยกเลิกได้เฉพาะ pending; ยกเลิกเป็น cancelled ไม่ลบแถวข้อมูล
- Admin ดูทุกงาน ค้นหาเลขงาน ชื่อผู้แจ้ง Serial ยี่ห้อ รุ่น ประเภท และหัวข้อ
- กรองสถานะ/ความเร่งด่วน เรียงใหม่/เก่า และแบ่งหน้า 10 รายการ
- Admin รับงาน ปิดงาน และบันทึกหมายเหตุ
- completed_at บันทึกเมื่อปิดงาน และไม่เปลี่ยนเวลาเมื่อแก้หมายเหตุภายหลัง
- หน้าจอภาษาไทย รองรับมือถือ มี empty state และแสดง validation errors
- บัญชีที่มีประวัติแจ้งซ่อมไม่สามารถลบบัญชี เพื่อรักษาข้อมูลอ้างอิง

## เปิดระบบในครั้งต่อไป

1. เปิด XAMPP และ Start MySQL (port 3306)
2. เปิด Terminal ใน C:\xampp\htdocs\fixit
3. รัน php artisan serve และเปิด Terminal ค้างไว้
4. เข้า http://127.0.0.1:8000

ถ้า port 8000 มี Server เดิมทำงานอยู่แล้ว ให้เปิดเว็บได้เลย ไม่ต้องเปิดซ้ำ
สำหรับ phpMyAdmin เปิด Apache ที่ port 8080 แล้วเข้า http://localhost:8080/phpmyadmin/

หลังแก้ CSS/JS ให้รัน npm run build; แก้ PHP/Blade ไม่ต้อง build
Bootstrap ถูกติดตั้งในโปรเจกต์และ build อยู่ใน public/build จึงไม่ต้องต่ออินเทอร์เน็ตเพื่อโหลด CDN
Breeze/Tailwind เดิมยังเก็บไว้ แต่หน้าระบบใหม่ใช้ Bootstrap ผ่านไฟล์ fixit.css แยกกัน

## บัญชีสาธิต (สำหรับ local เท่านั้น)

| บทบาท | อีเมล | รหัสผ่านสาธิต |
| --- | --- | --- |
| Admin | admin@fixit.test | FixIT-demo-2026! |
| User | user@fixit.test | FixIT-demo-2026! |

รหัสผ่านข้างบนเป็นรหัสของบัญชีสาธิตที่สร้างใหม่ ไม่ใช่รหัสผ่านส่วนตัวของผู้ใช้
AdminSeeder สร้างเฉพาะบัญชีที่ยังไม่มี และไม่เปลี่ยน Role/รหัสผ่านบัญชีที่มีอยู่
Seeder บัญชีสาธิตทำงานเฉพาะ APP_ENV=local หรือ testing
หากนำขึ้นอินเทอร์เน็ต ให้จัดการบัญชีสาธิตและตั้งค่าระบบ production ก่อน

ข้อมูลสาธิต 18 รายการมีเลข DEMO-2026-xxxx และเป็นของ user@fixit.test
อีก 1 รายการชื่อ [สาธิต] ทดสอบแจ้งซ่อมผ่านเบราว์เซอร์ ใช้ทดสอบจริงจน completed
ไม่ใส่ข้อมูลสาธิตลงบัญชีส่วนตัวเดิม

## คำสั่งเตรียมฐานข้อมูล

ใช้เฉพาะ Migration ที่ยังไม่รัน โดยไม่ลบตารางเดิม:

~~~powershell
php artisan migrate --seed
php artisan db:seed --class=DemoRepairSeeder
~~~

AdminSeeder ถูกเรียกผ่าน DatabaseSeeder ส่วน DemoRepairSeeder เลือกรันแยกได้
รัน Seeder ซ้ำได้โดยไม่เพิ่มบัญชีหรืองาน DEMO ซ้ำ
อย่าใช้ migrate:fresh กับฐานข้อมูลที่ต้องการเก็บ เพราะคำสั่งนั้นลบตาราง

## เส้นทางหลัก

| URL / Method | หน้าที่ |
| --- | --- |
| GET / | หน้าแรก |
| GET /dashboard | ภาพรวมงานของบัญชีปัจจุบัน |
| GET /repairs | รายการของฉัน |
| GET /repairs/create | ฟอร์มแจ้งซ่อม |
| POST /repairs | บันทึกงาน |
| GET /repairs/{repair} | รายละเอียด |
| GET /repairs/{repair}/edit | ฟอร์มแก้ไข |
| PUT /repairs/{repair} | บันทึกแก้ไข |
| PATCH /repairs/{repair}/cancel | ยกเลิกงาน |
| GET /repairs/{repair}/image | อ่านรูปหลังตรวจสิทธิ์ |
| GET /admin | ภาพรวม Admin |
| GET /admin/dashboard | redirect ไป /admin เพื่อรองรับ URL ในแผน |
| GET /admin/repairs | งานทั้งหมด |
| GET /admin/repairs/{repair} | รายละเอียดฝั่ง Admin |
| PUT /admin/repairs/{repair}/status | เปลี่ยนสถานะ/หมายเหตุ |
| GET /profile | ข้อมูลบัญชี |

Login ยังกลับ /dashboard ตาม Breeze เดิม; Admin เลือกเมนูภาพรวมผู้ดูแลได้

## อธิบาย MVC แบบนำเสนออาจารย์

ตัวอย่างผู้ใช้กดส่งแจ้งซ่อม:

1. Blade แสดงฟอร์ม และใส่ @csrf
2. ฟอร์ม POST มาที่ /repairs
3. Route ใช้ auth Middleware เพื่อบังคับ Login
4. RepairRequestController@store ตรวจข้อมูลด้วย validate()
5. RepairRequest Model บันทึกข้อมูลลงตาราง repair_requests ด้วย Eloquent
6. Controller redirect ไป named route repairs.show พร้อม flash message
7. Blade แสดงเลขงานและสถานะ

Model รับผิดชอบข้อมูล/Relationship/ค่าที่อนุญาต/Query scope
Controller รับคำขอ ตรวจข้อมูล ตรวจสิทธิ์ และประสานการบันทึก
View ใช้ @extends, @section, @yield, @include, @foreach และ {{ }} เพื่อแสดงผล

## โครงสร้างไฟล์ที่ควรอ่านตามลำดับ

1. routes/web.php — URL และการแบ่งกลุ่ม auth/admin
2. app/Http/Middleware/AdminMiddleware.php — ตรวจ Role
3. bootstrap/app.php — Alias admin ของ Laravel 12
4. database/migrations/2026_09_08_000001_create_repair_requests_table.php — โครงสร้างตาราง
5. app/Models/RepairRequest.php — สถานะ, urgency, fillable, belongsTo และ scopeFilter
6. app/Models/User.php — hasMany repairRequests
7. app/Http/Controllers/RepairRequestController.php — งานฝั่งผู้ใช้และรูปภาพ
8. app/Http/Controllers/AdminController.php — Dashboard/List/Show/UpdateStatus ฝั่ง Admin
9. app/Http/Controllers/DashboardController.php — สรุปงานของผู้ใช้
10. resources/views/layouts/fixit.blade.php — Layout, เมนู, Flash/Error
11. resources/views/dashboard.blade.php — View ร่วมของ Dashboard ทั้งสองบทบาท
12. resources/views/repairs/index.blade.php — ค้นหาและแบ่งหน้า
13. resources/views/repairs/form.blade.php — View ร่วมสำหรับ create/edit
14. resources/views/repairs/show.blade.php — View รายละเอียดและเงื่อนไขแสดงฟอร์ม
15. resources/views/repairs/table.blade.php — ตารางที่ใช้ซ้ำ
16. resources/css/fixit.css — Bootstrap และรูปแบบหน้าจอ
17. database/seeders และ database/factories — ข้อมูลสาธิต
18. tests/Feature/RepairWorkflowTest.php — ตรวจ business flow และสิทธิ์

View ร่วมใช้ตัวแปร $admin เพื่อแสดงส่วนของ Admin; การป้องกันจริงยังอยู่ที่ Route/Middleware/Controller
ไฟล์ resources/views/admin/dashboard.blade.php และ app/View/admin/dashboard.blade.php จากงานเดิมยังเก็บไว้ แต่ Route ใหม่ไม่ได้เรียกใช้

## Mapping กับ PDF ที่ตรวจอ้างอิงได้

| หลักที่ใช้ | หัวข้อบทเรียน |
| --- | --- |
| Bootstrap 5 และ @extends / @yield / Named Routes | สัปดาห์ 4 (PDF หน้า 97) |
| Controller รับคำขอและส่ง View | สัปดาห์ 5 |
| Validation และแสดง error | สัปดาห์ 6 (PDF หน้า 138) |
| Model, Migration, Eloquent | สัปดาห์ 7 (PDF หน้า 152) |
| Search/Query | หัวข้อ Query Builder |
| แก้ไขข้อมูล | หัวข้อแบบฟอร์ม Edit/Update |
| แบ่งหน้า | หัวข้อ Pagination |
| Authentication/Middleware | สัปดาห์ 12 |
| fillable และ Route Prefix | สัปดาห์ 13 (ตัวอย่าง Redirect ตาม Prefix หน้า 300) |

ชื่อ RepairRequest, บทบาท และเงื่อนไขงานซ่อม เป็นการประยุกต์ตาม Project Plan
Breeze Blade ใช้ตามแผนและโปรเจกต์ที่ติดตั้งไว้เดิม; ตัวอย่าง PDF บางส่วนใช้ laravel/ui
ไม่ได้อ้างว่าโค้ดทุกบรรทัดมีในสไลด์
transaction/lockForUpdate และการอ่านรูปผ่าน Controller เป็นรายละเอียด Laravel ที่ใช้รักษาความถูกต้องของระบบเมื่อมีคำขอพร้อมกัน

## กฎสถานะ

~~~text
pending → repairing → completed
pending → cancelled
~~~

- ผู้ใช้ยกเลิกได้เฉพาะงานตนเองที่ pending
- Admin เปลี่ยนตาม TRANSITIONS ที่ Model กำหนด
- ไม่ข้ามจาก pending ไป completed
- ไม่เปิด completed/cancelled กลับเป็นสถานะอื่น
- บันทึกหมายเหตุในสถานะเดิมได้ โดยไม่แก้เวลาปิดงานเดิม
- lockForUpdate อ่านสถานะล่าสุดใน transaction ก่อนแก้ไข เพื่อกันผู้ใช้แก้ข้อมูลพร้อมกับ Admin รับงาน

## รูปภาพและ Storage

รองรับ JPG, PNG, WebP สูงสุด 2 MB ตรวจชนิดจากไฟล์จริงด้วย Validation
เก็บบน Laravel local disk ที่ storage/app/private/repairs
เปิดผ่าน /repairs/{repair}/image หลังตรวจ Login และสิทธิ์เจ้าของ/Admin
ตั้ง Cache-Control เป็น private, no-store

รายละเอียดนี้ปรับจากตัวอย่าง public storage/storage:link ในแผน เพื่อไม่ให้คนอื่นเปิดรูปแนบข้ามสิทธิ์
จึงไม่ต้องรัน storage:link สำหรับรูปแจ้งซ่อมในเวอร์ชันนี้
ไฟล์ใหม่แทนที่ไฟล์เก่าได้ และลบไฟล์ใหม่ที่อัปโหลดค้างหากบันทึกข้อมูลไม่สำเร็จ

## การรักษาข้อมูลและสิทธิ์

- รับค่า user_id/status/ticket_no/admin_note จากเซิร์ฟเวอร์ ไม่รับค่าจากฟอร์ม User
- role ไม่อยู่ใน User::$fillable ผู้สมัครกำหนด Admin เองไม่ได้
- ค้นหาของ User เริ่มจาก relationship ของตนเอง และครอบ orWhere ในกลุ่ม เพื่อไม่ให้ผลค้นหารั่ว
- ข้อความผู้ใช้แสดงผ่าน {{ }} ป้องกันการแทรก HTML
- ทุกฟอร์มแก้ข้อมูลใช้ CSRF; PUT/PATCH/DELETE ใช้ @method
- ไม่มี Route ลบ RepairRequest จริง
- Foreign Key ใช้ restrictOnDelete และห้ามลบบัญชีที่มีประวัติแจ้งซ่อม
- รูปแนบและหน้ารายละเอียดตรวจสิทธิ์ที่ฝั่งเซิร์ฟเวอร์ ไม่พึ่งการซ่อนปุ่ม

## การทดสอบ

~~~powershell
php artisan test --compact
npm run build
~~~

phpunit.xml บังคับ SQLite :memory: แยกจาก MySQL ของโปรเจกต์
ห้ามถอดการตั้งค่านี้ก่อนรัน tests เพราะ RefreshDatabase มีหน้าที่สร้างฐานข้อมูลทดสอบใหม่
Tests ตรวจ:
- Guest/User เข้า Admin ไม่ได้
- สมัครสมาชิกส่ง role=admin ก็ไม่ได้สิทธิ์ Admin
- Create/Upload และการเพิกเฉยต่อฟิลด์ที่ห้ามแก้
- ฟอร์มว่าง ไฟล์ผิดชนิด และรูปเกินขนาด
- เปลี่ยน/ลบรูปเดิมเมื่อแก้ไข
- ดู/แก้ไข/ยกเลิก/เปิดรูปข้ามผู้ใช้ไม่ได้
- แก้/ยกเลิกได้เฉพาะ pending
- ไม่ย้อนสถานะ terminal และบันทึก completed_at ถูกต้อง
- Search/Filter/Pagination และหน้าหลัก render ได้
- ลบบัญชีที่มีประวัติไม่ได้
- Seeder รันซ้ำไม่เกิดข้อมูลซ้ำ

เคยพบ permission denied เมื่อทดสอบจาก sandbox; หลังให้สิทธิ์เขียน cache และใช้ SQLite ชั่วคราวชุดทดสอบผ่าน
PHP lint อย่างเดียวตรวจได้เฉพาะ syntax จึงไม่ได้ใช้เป็นหลักฐานว่าทั้งระบบทำงานครบ

## วิธี Demo

1. Login user@fixit.test
2. เปิดแจ้งซ่อมใหม่ ลองข้อมูลไม่ครบ แล้วกรอกให้ครบพร้อมรูป
3. จดเลข REP ที่ได้รับ เปิดรายละเอียด/ลองแก้ไขตอน pending
4. Logout แล้ว Login admin@fixit.test
5. เมนูจัดการงานซ่อม ค้นหาเลขที่เพิ่งสร้าง
6. เปลี่ยน pending → repairing ใส่หมายเหตุ
7. เปลี่ยน repairing → completed
8. กลับบัญชี User เปิดรายการเดิม ดูหมายเหตุและเวลาปิดงาน
9. ลองเข้า /admin ด้วย User ต้องได้ 403

## สิ่งที่อยู่นอก MVP

ยังไม่มี Timeline ประวัติย้อนหลัง, Notifications, Queue, ชำระเงิน, คลังอะไหล่ หรือ Technician หลายระดับ
แถบ 3 ขั้นตอนบนหน้ารายละเอียดเป็นภาพของสถานะปัจจุบัน ไม่ใช่ตารางประวัติการเปลี่ยนสถานะ
การส่งอีเมลลืมรหัสผ่านยังขึ้นกับ MAIL_* ของเครื่อง ต้องตั้งค่า SMTP หากต้องการส่งเข้าอีเมลจริง
เวอร์ชันนี้พร้อมลองในเครื่องและใช้ศึกษา/นำเสนอ ต้องทดลองด้วยตนเองก่อนส่งอาจารย์
