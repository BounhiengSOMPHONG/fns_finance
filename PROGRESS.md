# Progress Log

## สิ่งที่ทำไปแล้ว

### 🔐 ระบบ Authentication & Authorization
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php` — Login ด้วย username (ไม่ใช่ email), เช็ค `is_active` ก่อนเข้าระบบ
- `app/Http/Requests/Auth/LoginRequest.php` — Validation rules สำหรับ login ด้วย username
- `app/Http/Middleware/CheckUserActive.php` — Middleware เช็คสถานะ active ของ user, logout อัตโนมัติถ้า inactive
- `app/Http/Middleware/CheckRole.php` — Middleware ตรวจสอบ role, รองรับหลาย role (`role:admin,accountant`)
- `bootstrap/app.php` — ลงทะเบียน middleware alias `check.active` และ `role`
- `routes/auth.php` — ลบ register routes ออก, เหลือ login/logout/password เท่านั้น
- ปิดระบบ Register — เพิ่ม user ผ่าน Admin Dashboard เท่านั้น

### 👤 ระบบ Role-based Routing (แยก route file ต่อ role)
- `routes/web.php` — Smart redirect `/dashboard` ไปหน้า home ตาม role, บังคับ `auth` + `check.active`
- `routes/admin.php` — CRUD resources: users, roles, departments, chart-of-accounts
- `routes/head_of_finance.php` — CRUD แผนงบประมาณประจำปี + line items + submit/unsubmit + start-modifying + submit-final + comments mark + PDF export
- `routes/head_of_department.php` — ดูแผนงบ (เฉพาะที่ถูก assign) + ให้ comment (review)
- `routes/deputy_head_of_faculty.php` — ดูแผนงบ + approve/reject/comment (final approval)
- `routes/head_of_faculty.php` — ดูแผนงบ (PENDING_FINAL_APPROVAL/APPROVED) + approve/reject/comment (final approval)
- `routes/accountant.php` — มีแค่ home page (ยังไม่มี feature)
- `routes/web.php` — เพิ่ม Notification API routes (read, read-all, data) สำหรับทุก role

### 📊 Admin Dashboard (CRUD ครบ)
- `app/Http/Controllers/Admin/UserController.php` — CRUD ผู้ใช้ (search, filter role/department/status, pagination, ป้องกันลบตัวเอง)
- `app/Http/Controllers/Admin/RoleController.php` — CRUD บทบาท (withCount users, ป้องกันลบที่มี user อยู่)
- `app/Http/Controllers/Admin/DepartmentController.php` — CRUD แผนก (withCount, cache department_types 1 ชม.)
- `app/Http/Controllers/Admin/ChartOfAccountController.php` — CRUD ผังบัญชี (รองรับ parent_id hierarchical)
- Views ครบทุก module: `resources/views/admin/{users,roles,departments,chart-of-accounts}/{index,create,edit,show}`

### 💰 ระบบแผนงบประมาณประจำปี (Annual Budget Plan) — ฟีเจอร์หลัก
- `app/Http/Controllers/HeadOfFinance/AnnualBudgetPlanController.php` — Controller หลัก:
  - CRUD แผนงบ (BudgetPlan)
  - เพิ่ม/แก้ไข/ลบ line items (เดี่ยว + bulk)
  - Bottom-Up roll-up: คำนวณยอดรวม parent จาก leaf nodes (`synthesizeTreeAndRollUp`)
  - Submit workflow ใหม่: เลือก reviewer (HoD) ผ่าน modal → บันทึกลง `budget_plan_reviewers` → ส่ง notification
  - `startModifying()` — PENDING_REVIEW → MODIFYING
  - `submitForFinalApproval()` — MODIFYING → PENDING_FINAL_APPROVAL + ส่ง notification ให้ Head of Faculty
  - Mark/Unmark comments (toggle ผ่าน AJAX)
  - PDF export ด้วย mPDF (รองรับฟอนต์ลาว NotoSansLao, A4-L landscape)
  - Preview หน้าจอ
- `app/Http/Controllers/HeadOfDepartment/BudgetReviewController.php` — **ดูเฉพาะแผนที่ถูก assign เป็น reviewer** + ให้ comment (ตรวจสิทธิ์ก่อน show/review)
- `app/Http/Controllers/DeputyHeadOfFaculty/BudgetReviewController.php` — ดูแผน + approve/reject (PENDING_FINAL_APPROVAL → APPROVED/MODIFYING)
- `app/Http/Controllers/HeadOfFaculty/BudgetApprovalController.php` — **ดูแผน PENDING_FINAL_APPROVAL/APPROVED + approve/reject/comment + ส่ง notification กลับ HoF**
- Views: `resources/views/head_of_finance/annual-budget/{index,create,edit,show,preview,pdf}`
- Views: `resources/views/head_of_department/annual-budget/{index,show}`
- Views: `resources/views/deputy_head_of_faculty/annual-budget/{index,show}`
- Views: `resources/views/head_of_faculty/annual-budget/{index,show}`

### 🗃️ Models
- `app/Models/User.php` — username-based auth, timestamps=false, Notifiable trait, relationships: role, department
- `app/Models/Role.php` — role_name
- `app/Models/Department.php` — department_name, department_type
- `app/Models/ChartOfAccount.php` — hierarchical (parent/children self-referencing), `getFormattedCodeAttribute` (60-00-00-00)
- `app/Models/BudgetPlan.php` — fiscal_year, status, created_by, submission_round, timestamps=false, relationships: lineItems, comments, **reviewers (hasMany BudgetPlanReviewer)**, **reviewerUsers (belongsToMany)**, **creator (belongsTo User)**
- `app/Models/BudgetLineItem.php` — budget_plan_id, account_id, amount_regular, amount_academic, timestamps=false
- `app/Models/BudgetPeriodAllocation.php` — budget_line_item_id, period_name, allocated_amount, timestamps=false
- `app/Models/BudgetPlanComment.php` — budget_plan_id, user_id, comment, submission_round, marked_at, marked_by (มี timestamps)
- `app/Models/BudgetPlanReviewer.php` — **NEW** — budget_plan_id, user_id, assigned_by, timestamps, relationships: budgetPlan, user, assigner

### 🗄️ Database Schema (MySQL 8 / fns_finance.sql)
- Tables ที่ใช้งานแล้ว: `users`, `roles`, `departments`, `chart_of_accounts`, `budget_plans`, `budget_line_items`, `budget_period_allocations`, `budget_plan_comments`, **`budget_plan_reviewers`**, **`notifications`**
- Tables ที่มีในฐานข้อมูลแต่ยังไม่มี Laravel code: `advance_requests`, `advance_clearing_items`, `request_workflow_logs`, `transactions`, `transaction_attachments`, `treasury_reconciliation_items`
- Migrations (Laravel): **6 ไฟล์** (remember_token, create budget_plan_comments, add submission_round, add marked_fields, **create budget_plan_reviewers**, **create notifications**)
- Seeders: `RoleSeeder`, `DepartmentSeeder`, `AdminUserSeeder`, `DatabaseSeeder`

### 🔔 ระบบแจ้งเตือน (Notification System)
- `app/Notifications/BudgetPlanReviewRequested.php` — **แจ้ง HoD (reviewer)** เมื่อ HoF ส่งแผนให้กวดสอบ
- `app/Notifications/BudgetPlanFinalApprovalRequested.php` — **แจ้ง Head of Faculty** เมื่อ HoF ส่งขออนุมัติขั้นสุดท้าย
- `app/Notifications/BudgetPlanStatusChanged.php` — **แจ้ง HoF** เมื่อ Head of Faculty อนุมัติ/ส่งกลับแก้ไข
- Notification API routes ใน `web.php`: `GET /notifications/data`, `POST /notifications/{id}/read`, `POST /notifications/read-all`

### 🎨 UI / Layout
- `resources/views/layouts/admin.blade.php` — Layout หลัก (Vite + TailwindCSS + Alpine.js)
- `resources/views/layouts/app.blade.php` — Layout สำหรับ profile
- `resources/views/layouts/guest.blade.php` — Layout สำหรับ login
- `resources/views/components/admin-sidebar.blade.php` — Sidebar ตาม role (@can directive) — **เพิ่มเมนู Head of Faculty: ອະນຸມັດແຜນງົບປະມານ**
- `resources/views/components/admin-header.blade.php` — Header bar + **กระดิ่ง Notification (Alpine.js + AJAX)** แสดง unread count, dropdown list, mark as read
- Home pages ทุก role: `resources/views/{admin,head_of_finance,head_of_department,head_of_faculty,deputy_head_of_faculty,accountant}/home.blade.php`

### ⚡ Performance Optimization
- แก้ Alpine.js โหลดซ้ำ (ลบ CDN ออก)
- แก้ N+1 Query ใน DepartmentController (ใช้ `withCount`)
- เพิ่ม cache สำหรับ department_types

---

## สถานะปัจจุบัน

โปรเจกต์เป็น **ระบบการเงินภายในองค์กร (FNS Finance)** สร้างด้วย Laravel + Breeze + Blade + TailwindCSS + Alpine.js

### ✅ ใช้งานได้แล้ว:
1. **Login/Logout** ด้วย username + ตรวจสอบ is_active
2. **Admin Dashboard** — CRUD ครบ 4 โมดูล (Users, Roles, Departments, Chart of Accounts)
3. **แผนงบประมาณประจำปี (Head of Finance)** — สร้าง/แก้ไข/ลบแผน, เพิ่ม line items, เลือก reviewer + submit, startModifying, submitForFinalApproval, preview, export PDF, mark comments
4. **กวดสอบแผนงบ (Head of Department)** — **เห็นเฉพาะแผนที่ถูก assign** + ให้ comment (ตรวจสิทธิ์)
5. **อนุมัติแผนงบ (Deputy Head of Faculty)** — approve/reject + comment
6. **อนุมัติขั้นสุดท้าย (Head of Faculty)** — **NEW** — ดูแผน PENDING_FINAL_APPROVAL/APPROVED + approve/reject + comment + ส่ง notification กลับ HoF
7. **Role-based access control** — แยก route ตาม role, sidebar แสดงเมนูตาม role
8. **Bottom-Up budget roll-up** — คำนวณยอดรวม parent categories จาก leaf nodes อัตโนมัติ
9. **ระบบ Notification (In-app)** — **NEW** — กระดิ่งแจ้งเตือนใน header สำหรับทุก role, แสดง unread count, mark as read
10. **Reviewer Assignment** — **NEW** — HoF เลือก HoD เป็น reviewer ผ่าน modal ก่อน submit

### 🔄 Workflow งบประมาณ (ใหม่):
```
DRAFT/MODIFYING → (ສົ່ງເພື່ອຂໍຄວາມຄິດເຫັນ + เลือก reviewer) → PENDING_REVIEW
  → (HoF กด ເລີ່ມແກ້ໄຂ) → MODIFYING
    → (ສົ່ງເພື່ອຂໍຄວາມຄິດເຫັນ อีกครั้ง) → PENDING_REVIEW
    → (ສົ່ງເພື່ອຂໍອະນຸມັດຂັ້ນສຸດທ້າຍ) → PENDING_FINAL_APPROVAL
      → (Head of Faculty อະນຸມັດ) → APPROVED ✅
      → (Head of Faculty ປັບປຸງ + comment) → MODIFYING → ...
```

### 🎯 ปุ่มแสดงตามสถานะ (Button Visibility):
| ปุ่ม | แสดงเมื่อ status = |
|------|-------------------|
| ສົ່ງເພື່ອຂໍຄວາມຄິດເຫັນ | DRAFT / MODIFYING |
| ເລີ່ມແກ້ໄຂ | PENDING_REVIEW |
| ສົ່ງເພື່ອຂໍອະນຸມັດຂັ້ນສຸດທ້າຍ | MODIFYING |

---

## การตัดสินใจสำคัญ

- **ใช้ username แทน email** — เพราะเป็นระบบภายในองค์กร ไม่ต้องการ email verification
- **ปิดระบบ Register** — จัดการ user ผ่าน Admin เท่านั้น เพื่อความปลอดภัย
- **แยก route file ตาม role** — ให้แต่ละ role มีไฟล์ routes ของตัวเอง ลดการ conflict ตอนพัฒนาแบบทีม
- **Bottom-Up roll-up (ไม่มี fixed limit)** — Parent category totals คำนวณจากผลรวม leaf nodes เสมอ ไม่มี validation ว่าห้ามเกินเท่าไหร่
- **ใช้ MySQL 8 เป็นหลัก** — Tables สร้างจาก SQL dump โดยตรง ไม่ใช่ Laravel migration ทั้งหมด (มี 6 migration files สำหรับ incremental changes)
- **`synthesizeTreeAndRollUp` ถูก duplicate** — method นี้ copy-paste อยู่ใน **4** controllers (HoF, HoD, Deputy, **HoFac**) แทนที่จะเป็น shared trait/service
- **Timestamps = false ในหลาย model** — เพราะ tables จาก SQL dump ไม่มี `created_at`/`updated_at` columns (ยกเว้น BudgetPlanComment ที่เพิ่มทีหลังผ่าน migration)
- **ใช้ mPDF สำหรับ PDF** — เลือก mPDF เพราะรองรับ Lao font (NotoSansLao) ได้ดี
- **UI เป็นภาษาลาว** — ข้อความทั้งหมดใน views และ controller messages เป็นภาษาลาว

---

## สิ่งที่ยังไม่ได้ทำ

### 🔴 สำคัญ (Core Features ที่ยังขาด)
- **ระบบ Advance Request (เบิกเงินล่วงหน้า)** — Tables มีในฐานข้อมูลแล้ว (`advance_requests`, `advance_clearing_items`, `request_workflow_logs`) แต่ยังไม่มี Laravel Models/Controllers/Views (ยกเว้นข้อมูลตัวอย่างใน SQL dump)
- **ระบบ Transactions** — Table `transactions` มีอยู่แต่ยังไม่มี Laravel code
- **ระบบ Treasury Reconciliation** — Table `treasury_reconciliation_items` มีอยู่แต่ยังไม่มี code
- **Accountant features** — มีแค่ home page
- **Reviewer features** — มี directory `resources/views/reviewer/annual-budget` แต่ว่างเปล่า, ไม่มี controller
- **Roles ที่ยังไม่มี routes/features**: `cashier`, `requester`, `revenue_officer`, `treasurer`, `treasury_reconciliation_officer`

### 🟡 ปานกลาง (Improvements)
- **Refactor `synthesizeTreeAndRollUp`** — ย้ายไปเป็น Trait หรือ Service class แทน copy-paste ใน 4 controllers (HoF, HoD, Deputy, HoFac)
- **Budget Period Allocations UI** — Model มีแล้วแต่ไม่เห็น UI สำหรับจัดสรรงบรายงวด
- **Dashboard analytics** — Home page ของทุก role ยังเป็นหน้าเปล่า/basic ไม่มีกราฟหรือสรุปข้อมูล
- **Transaction Attachments** — Table มีแล้วแต่ไม่มี file upload logic

### 🟢 เล็กน้อย (Nice to Have)
- **Email notifications** — ระบบใช้ database channel เท่านั้น ยังไม่มี email
- **Activity logging** — ไม่มี audit trail ใน Laravel (มีแค่ `request_workflow_logs` ใน DB)
- **Pagination ในหน้า budget plan list** — ตอนนี้ `->get()` ทั้งหมด
- **Unit/Feature tests** — ไม่มี test ที่เขียนเอง

---

## ข้อควรระวัง

### ⚠️ Database
- **Tables ส่วนใหญ่สร้างจาก SQL dump ภายนอก** ไม่ใช่ Laravel migrations — ห้ามรัน `migrate:fresh` หรือ `migrate:reset` เด็ดขาด จะลบ tables ที่ไม่มี migration
- **มี 8 Laravel migration files** สำหรับ incremental changes (remember_token, budget_plan_comments, submission_round, marked_fields, create budget_plan_reviewers (ว่างเปล่า), create budget_plan_reviewers (มี guard), create notifications, **add_columns_to_budget_plan_reviewers**)
- **`budget_plan_comments` table** — เคยมีปัญหา table หายจาก DB (ดู conversation history), migration ใช้ `Schema::hasTable` / `Schema::hasColumn` เพื่อป้องกัน error
- **`budget_plan_reviewers` table** — เคยเจอปัญหาตาราง empty (มีแค่ id + timestamps เพราะ artisan make:migration รัน `142938` ก่อน แล้ว migration ตัวจริง `210000` มี `Schema::hasTable` guard เลยข้าม) → แก้ด้วย migration `224000_add_columns_to_budget_plan_reviewers_table` ใช้ raw SQL `ALTER TABLE ADD COLUMN BIGINT UNSIGNED` เพื่อหลีกเลี่ยง type mismatch กับ FK ของ MySQL
- **`notifications` table** — **NEW** — Standard Laravel notifications table (uuid pk, morphs notifiable, text data, read_at)
- **ฟิลด์ `submission_round`** ใน `budget_plans` — เพิ่มทีหลังผ่าน migration, ถ้า table เดิมไม่มีต้องรัน migrate ก่อน
- **ฟิลด์ `marked_at`, `marked_by`** ใน `budget_plan_comments` — ใช้ raw SQL `ALTER TABLE` เพราะ Blueprint มีปัญหากับ SQLite

### ⚠️ Code Architecture
- **`synthesizeTreeAndRollUp()` อยู่ใน 4 controllers** — AnnualBudgetPlanController, HeadOfDepartment\BudgetReviewController, DeputyHeadOfFaculty\BudgetReviewController, **HeadOfFaculty\BudgetApprovalController** — ถ้าแก้ต้องแก้ทั้ง 4 ที่
- **`$timestamps = false`** ในหลาย models — ระวังอย่าเพิ่ม `created_at`/`updated_at` ใน query ถ้า table ไม่มี columns เหล่านี้
- **BudgetPlan model ไม่มี `version` ใน fillable** — แต่ table มี column `version` อยู่
- **Sidebar ใช้ `@can` directive** — ต้องมี Gate/Policy ตั้งค่าไว้ (ดูที่ AppServiceProvider: Gate::define)
- **ผังบัญชี (Chart of Accounts) เป็น hierarchical** — ใช้ `parent_id` self-referencing, account_code 8 หลัก format XX-XX-XX-XX

### ⚠️ Environment
- **ใช้ MySQL 8** เป็นหลัก — DB อยู่ที่ IP `100.111.1.1:3306` (database: `fns`) — ระวัง command timeout ถ้ารันจาก terminal ที่ไม่ได้อยู่ในเครือข่ายเดียวกัน
- **ฟอนต์ลาว (NotoSansLao)** ต้องอยู่ใน `storage/fonts/` สำหรับ PDF export
- **mPDF dependency** — ต้องติดตั้งผ่าน composer (`mpdf/mpdf`)
- **ภาษาในระบบเป็นภาษาลาว** — ทุก message, label, validation error เป็นภาษาลาว

### ⚠️ Workflow
- **HoD ไม่มีปุ่ม forward/escalate** — HoD ให้ได้แค่ comment, **HoF เป็นคน transition status** ผ่านปุ่ม ເລີ່ມແກ້ໄຂ / ສົ່ງເພື່ອຂໍອະນຸມັດ
- **Deputy Head of Faculty ยังคงมี approve/reject flow เดิม** — ระบบ Head of Faculty approval ถูกสร้างแยก (ไม่ได้ลบ Deputy flow)
- **Head of Faculty สามารถ approve/reject ได้เฉพาะ status PENDING_FINAL_APPROVAL** — HoF ต้องกดปุ่ม ສົ່ງເພື່ອຂໍອະນຸມັດຂັ້ນສຸດທ້າຍ ก่อน
- **Notification ใช้ database channel เท่านั้น** — ไม่มี email, ไม่มี push notification
