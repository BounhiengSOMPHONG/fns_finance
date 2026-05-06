# FNS Finance — ລະບົບຈັດການງົບປະມານ

ระบบวางแผนและบริหารงบประมาณสำหรับองค์กร สร้างด้วย Laravel 12  
A financial budget planning and management system for institutional use, built with Laravel 12.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 12, PHP 8.2+ |
| Database | MySQL 8 |
| Frontend | Blade Templates, TailwindCSS 3, Alpine.js 3 |
| Build Tool | Vite 7 |
| PDF Export | mPDF (NotoSansLao font) |
| Authentication | Laravel Breeze |
| UI Language | ລາວ (Lao) |

---

## Roles & Access (6 Roles)

| Role | ສິດທິ / Access |
|---|---|
| `admin` | CRUD ผู้ใช้, Role, แผนก, ผังบัญชี (Chart of Accounts) |
| `head_of_finance` | สร้าง/จัดการแผนงบประมาณประจำปี, จัดสรรงวด, กำหนด Reviewer |
| `head_of_department` | รีวิวแผนงบประมาณที่ได้รับมอบหมาย, เพิ่มความเห็น |
| `deputy_head_of_faculty` | รีวิวและอนุมัติเบื้องต้น |
| `head_of_faculty` | อนุมัติขั้นสุดท้าย, ดู Dashboard Analytics |
| `accountant` | (รอพัฒนา) |

---

## Budget Workflow

```
DRAFT
  └─► [กำหนด Reviewer] ─► PENDING_REVIEW
                                └─► [เริ่มแก้ไข] ─► MODIFYING
                                                        └─► [ส่งขออนุมัติ] ─► PENDING_FINAL_APPROVAL
                                                                                    ├─► APPROVED  ✅
                                                                                    └─► REJECTED  ❌ (กลับไป MODIFYING)
```

---

## Features Implemented ✅

### Admin Module
- [x] CRUD ผู้ใช้งาน (Users) พร้อม filter role/department/status
- [x] CRUD บทบาท (Roles) พร้อม Lao language label
- [x] CRUD แผนก (Departments)
- [x] CRUD ผังบัญชี (Chart of Accounts) — ลำดับชั้น parent-child พร้อมรหัส `XX-XX-XX-XX`
- [x] Bulk delete และ configurable pagination ทุก module
- [x] Modal confirmation แทน browser `confirm()`

### Annual Budget Plan (Head of Finance)
- [x] สร้าง/แก้ไข/ลบแผนงบประมาณประจำปี (fiscal year based)
- [x] จัดการรายการงบ (Budget Line Items) — เพิ่ม/แก้/ลบ/bulk operations
- [x] Bottom-up roll-up — ยอดรวมคำนวณอัตโนมัติจาก leaf nodes ขึ้นไปหา parent
- [x] มอบหมาย Reviewer ผ่าน modal
- [x] ส่งเข้า workflow อนุมัติหลายระดับ

### Budget Installments (งวดงบประมาณ)
- [x] จัดสรรงบตามงวด (งวด 1–2 สำหรับครึ่งปีแรก/หลัง)
- [x] งวด 3–4 สำหรับการปรับแผน 6 เดือน พร้อม validation ไม่เกิน 100%
- [x] Real-time calculation ด้วย Alpine.js
- [x] Document preview สำหรับพิมพ์เอกสารราชการ (A4 Landscape)
- [x] PDF export พร้อม NotoSansLao font

### Review & Approval
- [x] Head of Department รีวิวแผนที่ได้รับมอบหมาย
- [x] ระบบ Comment พร้อม mark/unmark toggle (AJAX)
- [x] Deputy Head of Faculty อนุมัติ/ปฏิเสธ
- [x] Head of Faculty อนุมัติขั้นสุดท้าย
- [x] ติดตาม submission round (รอบที่ส่งอนุมัติ)

### Dashboard & Analytics (Head of Faculty)
- [x] 4 metric cards: งบรวม / ยอดเบิกล่วงหน้า / รายจ่ายจริง / คงเหลือ
- [x] Stacked progress bar แสดงการใช้งบ
- [x] รายการแผนรออนุมัติ

### Notification System
- [x] In-app notifications (database channel)
- [x] Bell icon พร้อมนับ unread
- [x] Mark as read (รายการ / ทั้งหมด)
- [x] แจ้งเตือนเมื่อถูกมอบหมาย Reviewer, ส่งอนุมัติ, เปลี่ยนสถานะ

### Authentication & Security
- [x] Login ด้วย username (ไม่ใช้ email)
- [x] Active status check — auto logout หากบัญชีถูก inactive
- [x] Role-based middleware protection ทุก route
- [x] Smart redirect `/dashboard` → หน้าหลักตาม role

### UI/UX
- [x] Dark-themed sidebar, TailwindCSS design system
- [x] Responsive layout ด้วย Alpine.js
- [x] Full Lao language localization (ພາສາລາວ)
- [x] Profile management

---

## Not Yet Implemented 🔴

- [ ] Advance Request CRUD (ตารางมีแล้ว รอสร้าง UI)
- [ ] Transaction CRUD (ตารางมีแล้ว รอสร้าง UI)
- [ ] Treasury Reconciliation
- [ ] Accountant full features
- [ ] Email notifications
- [ ] Activity audit logs
- [ ] Unit / Feature tests

---

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/                  # CRUD: Users, Roles, Departments, ChartOfAccount
│   │   ├── HeadOfFinance/          # Budget plans, installments, reviewer assignment
│   │   ├── HeadOfFaculty/          # Dashboard analytics, final approval
│   │   ├── HeadOfDepartment/       # Budget review
│   │   ├── DeputyHeadOfFaculty/    # Budget review & approval
│   │   └── Auth/
│   └── Middleware/                 # CheckRole, CheckUserActive
├── Models/                         # 9 Eloquent models
│   ├── BudgetPlan, BudgetLineItem, BudgetPeriodAllocation
│   ├── BudgetPlanComment, BudgetPlanReviewer
│   ├── ChartOfAccount, User, Role, Department
└── Notifications/                  # 3 in-app notification classes

resources/views/                    # 71 Blade templates
routes/                             # 8 role-specific route files
database/migrations/                # 8+ migration files
```

---

## Setup

```bash
# Clone & install
composer install
npm install

# Environment
cp .env.example .env
php artisan key:generate

# Database (import existing SQL dump — do NOT run migrate:fresh)
# Import your .sql file into MySQL first, then:
php artisan db:seed

# Build assets & serve
npm run dev
php artisan serve
```

> **Note:** The database schema is managed via SQL dump. Avoid running `php artisan migrate:fresh` as it may drop production data.

---

## Statistics

| Item | Count |
|---|---|
| Git commits | 86 |
| Blade views | 71 |
| Controllers | 27 |
| Eloquent models | 9 |
| Route files | 8 |
| Roles | 6 |
