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
- `routes/head_of_finance.php` — CRUD แผนงบประมาณประจำปี + line items + submit/unsubmit + start-modifying + submit-final + comments mark + PDF export + **plans placeholder route** (`/head-of-finance/plans`)
- `routes/head_of_department.php` — **ไม่จำกัด role** แล้ว (เอา `role:head_of_department` middleware ออก) — ทุก role ที่ถูก assign เป็น reviewer เข้าถึงได้, Controller enforce สิทธิ์เองผ่าน `reviewers()->where('user_id')`
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
  - **Refactor:** ใช้ `$item->is_parent` ในกระบวนการ Roll-up สำหรับการแสดงผล Hyphen (-) หน้าชื่อบัญชีย่อยอย่างถูกต้อง แทนการตัด String
  - Submit workflow (**เฉพาะหน้า show เท่านั้น**, ลบปุ่ม submit ออกจากหน้า index แล้ว): เลือก reviewer (**ทุก user ที่ active ยกเว้น HoF เอง, Admin, deputy_head_of_faculty, head_of_faculty และกลุ่มระดับปฏิบัติการ**) ผ่าน modal (scroll ได้, ปุ่มอยู่ล่างสุดเสมอ) → บันทึกลง `budget_plan_reviewers` → ส่ง notification
  - `startModifying()` — PENDING_REVIEW → MODIFYING
  - `submitForFinalApproval()` — MODIFYING → PENDING_FINAL_APPROVAL + ส่ง notification ให้ Head of Faculty
  - Mark/Unmark comments (toggle ผ่าน AJAX)
  - PDF export ด้วย mPDF (รองรับฟอนต์ลาว NotoSansLao, A4-L landscape)
  - Preview หน้าจอ
- `app/Http/Controllers/HeadOfFinance/PlansCtrl.php` — **NEW** — Placeholder controller สำหรับหน้า plans (ยังไม่มีเนื้อหา)
- `app/Http/Controllers/HeadOfDepartment/BudgetReviewController.php` — **ดูเฉพาะแผนที่ถูก assign เป็น reviewer** + ให้ comment (ตรวจสิทธิ์ก่อน show/review)
- `app/Http/Controllers/DeputyHeadOfFaculty/BudgetReviewController.php` — ดูแผน + approve/reject (PENDING_FINAL_APPROVAL → APPROVED/MODIFYING)
- `app/Http/Controllers/HeadOfFaculty/BudgetApprovalController.php` — **ดูแผน PENDING_FINAL_APPROVAL/APPROVED + approve/reject/comment + ส่ง notification กลับ HoF**
- `app/Http/Controllers/HeadOfFinance/BudgetInstallmentController.php` — จัดการแผนงวด (จัดสรรงวด 1 และ งวด 2 สำหรับแผนที่ APPROVED แล้ว พร้อม auto-calculate 6 เดือนและ 6 เดือนท้ายปี) + **การปรับแก้โครงสร้างงบประมาณงวด 3-4** (รอบ 6 เดือนหลัง) พร้อมระบบคำนวณ real-time JavaScript สำหรับการเพิ่ม/ลดยอดงบประมาณ + **สร้างหน้า Document Preview รูปแบบทางการพร้อมโลโก้ภาครัฐ**
- `app/Http/Controllers/HeadOfFaculty/HomeController.php` — **UPDATED** — Dashboard analytics: ดึงข้อมูลจริงจาก DB (ยอดงบประมาณ APPROVED, ยอดผูกพันจาก `advance_requests`, ยอดใช้จ่ายจริงจาก `transactions` + cleared advances, ยอดคงเหลือ) + pending plans + recent advance requests
- Views: `resources/views/head_of_finance/annual-budget/{index,create,edit,show,preview,pdf}`
- Views: `resources/views/head_of_finance/budget-installment/{index,show,preview,show34,preview34}`
- Views: `resources/views/head_of_finance/plans.blade.php` — **NEW** — Placeholder view (ยังไม่มีเนื้อหา)
- Views: `resources/views/head_of_department/annual-budget/{index,show}`
- Views: `resources/views/deputy_head_of_faculty/annual-budget/{index,show}`
- Views: `resources/views/head_of_faculty/annual-budget/{index,show}`
- Views: `resources/views/head_of_faculty/home.blade.php` — **UPDATED** — Rich dashboard: 4 metric cards (ຍອດງົບປະມານ, ຍອດຜູກພັນ, ຍອດໃຊ້ຈ່າຍຈິງ, ຍອດຄົງເຫຼືອ), stacked progress bar (ອັດຕາການນຳໃຊ້ງົບ), pending plans list, recent advance requests feed — **ใช้ `@extends('layouts.admin')` แทน `<x-app-layout>`**

### 🗃️ Models
- `app/Models/User.php` — username-based auth, timestamps=false, Notifiable trait, relationships: role, department, **reviewerAssignments (hasMany BudgetPlanReviewer)**
- `app/Models/Role.php` — role_name, **`role_name_lao` accessor** (แปลงชื่อ role เป็นภาษาลาว เช่น head_of_department → ຫົວໜ້າພາກສ່ວນ)
- `app/Models/Department.php` — department_name, department_type
- `app/Models/ChartOfAccount.php` — hierarchical (parent/children self-referencing), `getFormattedCodeAttribute` (60-00-00-00)
- `app/Models/BudgetPlan.php` — fiscal_year, status, created_by, submission_round, timestamps=false, relationships: lineItems, comments, **reviewers (hasMany BudgetPlanReviewer)**, **reviewerUsers (belongsToMany)**, **creator (belongsTo User)**
- `app/Models/BudgetLineItem.php` — budget_plan_id, account_id, amount_regular, amount_academic, timestamps=false
- `app/Models/BudgetPeriodAllocation.php` — budget_line_item_id, period_name, allocated_amount, timestamps=false
- `app/Models/BudgetPlanComment.php` — budget_plan_id, user_id, comment, submission_round, marked_at, marked_by (มี timestamps)
- `app/Models/BudgetPlanReviewer.php` — **NEW** — budget_plan_id, user_id, assigned_by, timestamps, relationships: budgetPlan, user, assigner

### 🗄️ Database Schema (MySQL 8 / fns_finance.sql)
- Tables ที่ใช้งานแล้ว: `users`, `roles`, `departments`, `chart_of_accounts`, `budget_plans`, `budget_line_items`, `budget_period_allocations`, `budget_plan_comments`, **`budget_plan_reviewers`**, **`notifications`**
- Tables ที่มีในฐานข้อมูลแต่ยังไม่มี Laravel Models/Controllers/Views: `advance_requests`, `advance_clearing_items`, `request_workflow_logs`, `transactions`, `transaction_attachments`, `treasury_reconciliation_items` — **หมายเหตุ:** `advance_requests` และ `transactions` ถูก query แบบ raw DB ใน `HeadOfFaculty\HomeController` แล้ว แต่ยังไม่มี Eloquent Model
- Migrations (Laravel): **8 ไฟล์** (remember_token, create budget_plan_comments, add submission_round, add marked_fields, create budget_plan_reviewers ×2, create notifications, **add_columns_to_budget_plan_reviewers**)
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
- `resources/views/components/admin-sidebar.blade.php` — Sidebar ตาม role: เมนู **ກວດສອບແຜນງົບປະມານ** แสดงเมื่อ user มี `reviewerAssignments` และไม่ใช่ deputy/head_of_faculty — เมนู **ພິຈາລະນາແຜນງົບປະມານ** สำหรับ deputy_head_of_faculty — เมนู **ພາບລວມງົບປະມານ** (link to `head_of_faculty.home`) + **ອະນຸມັດແຜນງົບປະມານ** สำหรับ head_of_faculty — เมนู **ແຜນງວດງົບປະມານ** สำหรับ head_of_finance
- `resources/views/components/admin-header.blade.php` — Header bar + **กระดิ่ง Notification (Alpine.js + AJAX)** แสดง unread count, dropdown list, mark as read
- Home pages ทุก role: `resources/views/{admin,head_of_finance,head_of_department,head_of_faculty,deputy_head_of_faculty,accountant}/home.blade.php`
  - **Head of Faculty** home เป็น rich dashboard พร้อม analytics (ใช้ `@extends('layouts.admin')`)
  - **Admin, HoF, HoD, Deputy, Accountant** home ยังเป็น basic placeholder (ใช้ `<x-app-layout>`)
  - ⚠️ **Layout inconsistency** — HoF home ใช้ `@extends('layouts.admin')` + `@section('content')` ในขณะที่ home pages อื่นใช้ `<x-app-layout>` component

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
4. **ກວດສອບແຜນງົບ (Assigned Reviewer ทุก role)** — **เห็นเฉพาะแผนที่ถูก assign** + ให้ comment, เมนูในซอยด์บาร์ขึ้นเมื่อมี `reviewerAssignments` (ยกเว้น deputy/faculty ใช้เมนูของตัวเองแทน)
5. **อนุมัติแผนงบ (Deputy Head of Faculty)** — approve/reject + comment
6. **อนุมัติขั้นสุดท้าย (Head of Faculty)** — ดูแผน PENDING_FINAL_APPROVAL/APPROVED + **2 ปุ่ม: ປັບປຸງ (reject+comment รวมกัน) / ອະນຸມັດ** + ส่ง notification กลับ HoF — **ปุ่มทั้งสองใช้ Popup Modal แทน confirm()**
7. **Role-based access control** — แยก route ตาม role, sidebar แสดงเมนูตาม role
8. **Bottom-Up budget roll-up** — คำนวณยอดรวม parent categories จาก leaf nodes อัตโนมัติ
9. **ระบบ Notification (In-app)** — **NEW** — กระดิ่งแจ้งเตือนใน header สำหรับทุก role, แสดง unread count, mark as read
10. **Reviewer Assignment** — **NEW** — HoF เลือก HoD เป็น reviewer ผ่าน modal ก่อน submit
11. **แผนงวดงบประมาณ (Budget Period Allocations)** — **NEW** — จัดสรรงบประมาณรายงวด (งวด 1, งวด 2) สำหรับแผนที่เป็น APPROVED พร้อมการคำนวณ 6 เดือนต้นปี/ท้ายปี อัตโนมัติ และฟังก์ชัน **"ภาพรวม" (Preview)** ที่พิมพ์ออกมาเป็นเอกสารทางการได้ทันที
12. **การปรับแก้งวด 3-4 (Budget Revision)** — ระบบให้ Head of Finance ปรับแก้งบฯ ในช่วง 6 เดือนหลัง (งวด 3, 4) โดยมีหน้าสำหรับประเมินและทบทวนงบประมาณแบบ Real-time (JavaScript) ป้องกันงบประมาณเกินกรอบ (Budget Inflation Validation) พร้อมหน้าจอ Document Preview 3-4
13. **Head of Faculty Dashboard Analytics** — **NEW** — หน้า home ของ Head of Faculty แสดง 4 metric cards (ຍອດງົບປະມານ, ຍອດຜູກພັນ, ຍອດໃຊ້ຈ່າຍຈິງ, ຍອດຄົງເຫຼືອ), stacked progress bar, pending plans list, recent advance requests feed — ดึงข้อมูลจริงจาก `budget_line_items`, `advance_requests`, `transactions` tables
14. **Sidebar: ພາບລວມງົບປະມານ** — เมนูใน sidebar สำหรับ Head of Faculty ลิงก์ไปหน้า home (dashboard analytics)
15. **Plans placeholder (HoF)** — Route `/head-of-finance/plans` + `PlansCtrl` controller + placeholder view เป็นรูปแบบ Tailwind modern
16. **🎨 ✅ UI Standardization (Layout Consistency Fix)** — **เสร็จสิ้น** — ทุก home pages ใช้ `@extends('layouts.admin')` แบบ unified:
    - `resources/views/admin/home.blade.php` — Rich dashboard พร้อม 4 quick-action cards (Users, Roles, Departments, Chart of Accounts) ที่สวย
    - `resources/views/head_of_finance/home.blade.php` — Dashboard พร้อม quick links (Annual Budget, Installments, Plans)
    - `resources/views/head_of_department/home.blade.php` — ยินดีต้อนรับ + Budget Review section แบบ gradient
    - `resources/views/deputy_head_of_faculty/home.blade.php` — ยินดีต้อนรับ + Budget Review quick link แบบ gradient
    - `resources/views/accountant/home.blade.php` — ยินดีต้อนรับ + Coming Soon section แบบ gradient
    - `resources/views/profile/edit.blade.php` — Profile page ใช้ admin layout
    - ลบ inline styles ทั้งหมด ใช้ Tailwind CSS utility classes อย่างถูกต้อง
    - ปรับ Tailwind class order ให้ตรงกับ Prettier format
17. **🎨 Header Component Refactoring** — `resources/views/components/admin-header.blade.php` — ทำความเรียบร้อย จัดเรียง Tailwind classes อย่างถูกต้อง ปรับปรุง responsive design
18. **🎨 Popup Modal UI Upgrade (ແທນ confirm())** — **เสร็จสิ้น** — ปรับปรุง UX ทุกปุ่มยืนยันจาก `confirm()` แบบเก่าเป็น Tailwind CSS Popup Modal สวยงาม มี backdrop blur, scale animation, ไอคอนสีตามธีม (เขียว/ส้ม/ม่วง/แดง):
    - **`head_of_finance/annual-budget/show.blade.php`** — ปุ่ม ✏️ ເລີ່ມແກ້ໄຂ (สีส้ม), 🏛️ ສົ່ງເພື່ອຂໍອະນຸມັດຂັ້ນສຸດທ້າຍ (สีม่วง)
    - **`head_of_faculty/annual-budget/show.blade.php`** — ปุ่ม ✅ ອະນຸມັດ (สีเขียว), ↩ ປັບປຸງ (สีส้ม) — พร้อม sync comment จาก textarea เข้า hidden field ใน modal
    - **`admin/users/index.blade.php`** — ปุ่ม 🗑️ ລົບຜູ້ໃຊ້ (สีแดง) — แสดงชื่อเต็มผู้ที่จะลบใน modal
    - **`admin/roles/index.blade.php`** — ปุ่ม 🗑️ ລົບບົດບາດ (สีแดง) — แสดงชื่อ role ใน modal
    - **`admin/departments/index.blade.php`** — ปุ่ม 🗑️ ລົບພະແນກ (สีแดง) — แสดงชื่อแผนกใน modal
    - **`admin/chart-of-accounts/index.blade.php`** — ปุ่ม 🗑️ ລົບບັນຊີ (สีแดง) — แสดงรหัส+ชื่อบัญชีใน modal
    - ทุก modal ใช้ reusable pattern: form action ถูกเซ็ตแบบ dynamic ผ่าน JS, รองรับหลาย rows ในตาราง
19. **🎨 Table Color Standardization** — **เสร็จสิ้น** — ปรับสีตาราง ພາບລວມ ແຜນງົບປະມານປະຈຳປີ ให้เหมือนกันทุก role:
    - หัวตาราง: สีส้ม `#fdba74` / `#fed7aa`
    - แถวเลขคอลัมน์: สีม่วง `#c4b5fd`
    - แถวรวมยอด + แถวหมวดหลัก: สีฟ้า Cyan `#a5f3fc`
    - เส้นตาราง: `border-black`
    - อัพเดตทุก role: `head_of_finance`, `head_of_faculty`, `deputy_head_of_faculty`, `head_of_department`
20. **🎨 Master UI Redesign (Phases 1-5)** — **เสร็จสิ้น** — ปรับปรุง UI ทั้งระบบให้กลายเป็น Minimal & Clean:
    - สร้าง Design Tokens (CSS Variables) และ Reusable Component Classes (เช่น `.card`, `.btn`, `.table-wrapper`, `.form-section`) ใน `app.css`
    - เปลี่ยน Layout Shell ใหม่ให้เป็น Fixed Sidebar + Topbar (`layouts/admin.blade.php`, `admin-sidebar.blade.php`, `admin-header.blade.php`)
    - รีดีไซน์ Home Pages ทั้ง 6 roles ลบ gradient ออก ใช้สีพื้นเรียบ พร้อม `.welcome-banner` และ badge สีต่างๆ
    - รีดีไซน์ CRUD Index Pages (Users, Roles, Departments, Chart of Accounts) ใช้ `.table-wrapper` แบบใหม่
    - รีดีไซน์ CRUD Create/Edit/Show Pages ทั้งหมดให้เข้ากับ design system ใหม่ (grid forms, badges)

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
- **สร้าง Preview HTML สำหรับ Print (Budget Installment)** — เพื่อความยืดหยุ่น สร้างหน้า preview โดยใช้ HTML/CSS ล้วน (`@media print`) ช่วยให้ปริ้นต์จากเบราว์เซอร์ได้สัดส่วน A4 สวยงามเหมือนไฟล์ PDF
- **UI เป็นภาษาลาว** — ข้อความทั้งหมดใน views และ controller messages เป็นภาษาลาว (แก้ไขปุ่ม Tailwind ให้ใช้สีที่มีอยู่ใน bundle แล้วหลีกเลี่ยงสีที่ถูก purge)

---

## สิ่งที่ยังไม่ได้ทำ

### 🔴 สำคัญ (Core Features ที่ยังขาด)
- **ระบบ Advance Request (เบิกเงินล่วงหน้า)** — Tables มีในฐานข้อมูลแล้ว (`advance_requests`, `advance_clearing_items`, `request_workflow_logs`) แต่ยังไม่มี Laravel Models/Controllers/Views (ยกเว้นข้อมูลตัวอย่างใน SQL dump) — **หมายเหตุ:** `advance_requests` ถูก query แบบ raw DB (`DB::table`) ใน `HeadOfFaculty\HomeController` แล้ว
- **ระบบ Transactions** — Table `transactions` มีอยู่แต่ยังไม่มี Laravel Model/Controller/View — **หมายเหตุ:** ถูก query แบบ raw DB ใน `HeadOfFaculty\HomeController` แล้ว
- **ระบบ Treasury Reconciliation** — Table `treasury_reconciliation_items` มีอยู่แต่ยังไม่มี code
- **Accountant features** — มีแค่ home page
- **Reviewer features** — มี directory `resources/views/reviewer/annual-budget` แต่ว่างเปล่า, ไม่มี controller
- **Roles ที่ยังไม่มี routes/features**: `cashier`, `requester`, `revenue_officer`, `treasurer`, `treasury_reconciliation_officer`

### 🟡 ปานกลาง (Improvements)
- **Refactor `synthesizeTreeAndRollUp`** — ย้ายไปเป็น Trait หรือ Service class แทน copy-paste ใน 5 controllers (HoF, HoD, Deputy, HoFac, **BudgetInstallment**)
- **Dashboard analytics** — ✅ **FIXED** — Home pages ของทุก role ปรับปรุงเป็น rich dashboard:
  - **Admin** — ทีม quick-action cards สำหรับ CRUD modules
  - **Head of Finance** — Quick links ไปยัง Annual Budget, Installments, Plans
  - **Head of Department** — Budget Review summary + action button
  - **Deputy Head of Faculty** — Budget Review summary + action button
  - **Accountant** — Welcome section + Coming Soon placeholder

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
- **`synthesizeTreeAndRollUp()` อยู่ใน 5 controllers** — AnnualBudgetPlanController, HeadOfDepartment\BudgetReviewController, DeputyHeadOfFaculty\BudgetReviewController, **HeadOfFaculty\BudgetApprovalController**, **HeadOfFinance\BudgetInstallmentController** — ถ้าแก้ต้องแก้ทั้ง 5 ที่
- **HeadOfFaculty\HomeController ใช้ raw DB queries** — ดึงข้อมูลจาก `advance_requests` และ `transactions` ด้วย `DB::table()` แทน Eloquent Model — เมื่อสร้าง Model เสร็จควร refactor มาใช้ Eloquent
- **✅ Layout ปรับปรุงแล้ว** — ทุก home pages ใช้ `@extends('layouts.admin')` อย่างสม่ำเสมอ
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
- **Reviewer ไม่จำกัด role** — HoF เลือกได้ทุก role ยกเว้น: admin, head_of_faculty, deputy_head_of_faculty, requester, cashier, revenue_officer, treasurer, treasury_reconciliation_officer
- **Reviewer ที่เป็น deputy/head_of_faculty** — ระบบกีดกันไม่ให้ถูก assign และไม่โชว์เมนู ກວດສອບ ซ้อนทับ เพื่อป้องกัน sidebar ซ้ำ
- **HoD ไม่มีปุ่ม forward/escalate** — HoD ให้ได้แค่ comment, **HoF เป็นคน transition status** ผ่านปุ่ม ເລີ່ມແກ້ໄຂ / ສົ່ງເພື່ອຂໍອະນຸມັດ
- **Deputy Head of Faculty ยังคงมี approve/reject flow เดิม** — ระบบ Head of Faculty approval ถูกสร้างแยก (ไม่ได้ลบ Deputy flow)
- **Head of Faculty สามารถ approve/reject ได้เฉพาะ status PENDING_FINAL_APPROVAL** — HoF ต้องกดปุ่ม ສົ່ງເພື່ອຂໍອະນຸມັດຂັ້ນສຸດທ້າຍ ก่อน
- **Notification ใช้ database channel เท่านั้น** — ไม่มี email, ไม่มี push notification
