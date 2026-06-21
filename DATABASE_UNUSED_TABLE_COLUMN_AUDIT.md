# รายงานเช็ก Table / Column ที่อาจไม่ได้ใช้ หรือ NULL เยอะ

วันที่เช็ก: 2026-06-19  
Database: `fns_1` ใน Docker container `mysql-container`  
โหมดตรวจสอบ: read-only เท่านั้น (`SELECT`, `information_schema`, และค้นใน codebase)

อัปเดต cleanup: สร้าง migration `2026_06_19_000005_clear_unused_legacy_columns.php` เพื่อลบเฉพาะ candidate ที่ปลอดภัยกว่า และลบแถว `note` ว่างใน `expense_plan_values`

## สรุปสั้น

- หลังจากลบ `expense_calculation_rules` และ `planning_year_field_settings` แล้ว ยังไม่เจอ table ที่ชัดเจนว่า "ไม่ได้ใช้ทั้งตาราง" และควรลบทันที
- table หลักทุกตัวมี row หรือมี model/controller/service/view/FK เกี่ยวข้องอยู่
- จุดที่น่าสงสัยที่สุดตอนนี้เป็นระดับ column legacy มากกว่า table
- column ที่ถูกจัดเป็น legacy และถูก cleanup: `planning_years.review_status`, `planning_years.submitted_by`, และ `academic_income_plans.nuol_pct_1_1` ถึง `nuol_pct_1_4`
- column ที่ NULL เยอะหลายตัวเป็น optional field หรือรองรับ workflow ในอนาคต จึงยังไม่ควรลบทันที

## ภาพรวม Table

| table_name | rows | size | code refs | FK links | status | reason |
|---|---:|---:|---:|---:|---|---|
| `academic_income_items` | 55 | 64 KB | 8 | 3 | used | ใช้เก็บรายการรายรับวิชาการ และถูกใช้ใน report/preview |
| `academic_income_plans` | 2 | 48 KB | 11 | 3 | used, has legacy columns | table ใช้อยู่ แต่มี column `nuol_pct_1_*` ที่ไม่เจอ runtime use |
| `academic_income_setting_sets` | 1 | 32 KB | 2 | 4 | used | เป็นชุด master setting ของรายรับวิชาการ |
| `chart_of_accounts` | 270 | 96 KB | 14 | 4 | used | master ผังบัญชี ถูกผูกกับ expense/report |
| `course_credit_settings` | 68 | 32 KB | 3 | 1 | used | master หน่วยกิต ใช้คำนวณ academic income |
| `credit_unit_price_settings` | 3 | 32 KB | 5 | 1 | used | master ราคาต่อหน่วยกิต |
| `degree_programs` | 68 | 32 KB | 9 | 2 | used | master หลักสูตร/ระดับการศึกษา |
| `departments` | 1 | 32 KB | 15 | 1 | used, low data | มีข้อมูลน้อย แต่ผูกกับ user workflow |
| `expense_pattern_fields` | 33 | 32 KB | 5 | 1 | used | field config ของ expense plan |
| `expense_patterns` | 5 | 32 KB | 5 | 3 | used | master รูปแบบรายการ expense |
| `expense_plan_values` | 1437 | 224 KB | 7 | 1 | used, sparse by design | EAV value table ทำให้หลาย typed column เป็น NULL |
| `expense_plans` | 392 | 192 KB | 10 | 7 | used | transaction หลักของแผนรายจ่าย |
| `expense_sections` | 14 | 32 KB | 10 | 2 | used | master หมวดรายจ่าย |
| `expense_subsection_default_rows` | 196 | 112 KB | 11 | 1 | used | template/default row ของ expense |
| `expense_subsections` | 76 | 48 KB | 12 | 3 | used | master หมวดย่อยรายจ่าย |
| `income_rate_settings` | 4 | 48 KB | 5 | 1 | used | master rate รายรับ |
| `migrations` | 84 | 16 KB | 2 | 0 | system table | Laravel ใช้ track migration ห้ามลบ |
| `nuol_pct_settings` | 3 | 32 KB | 5 | 1 | used | master % NUOL ปัจจุบัน |
| `period_plan_overrides` | 5 | 64 KB | 10 | 4 | used | transaction override สำหรับ period report |
| `planning_year_review_comment_agreements` | 1 | 48 KB | 2 | 2 | used, low data | review workflow |
| `planning_year_review_comments` | 2 | 64 KB | 2 | 4 | used, low data | review workflow |
| `planning_year_review_rounds` | 6 | 64 KB | 4 | 6 | used | review workflow |
| `planning_year_reviewers` | 7 | 48 KB | 4 | 2 | used | reviewer list |
| `planning_years` | 2 | 64 KB | 13 | 7 | used, has legacy columns | table ใช้หนัก แต่มี column น่าสงสัย |
| `registration_fee_items` | 21 | 32 KB | model refs | 1 | used | ใช้ผ่าน `RegistrationFeeSetting::items()` |
| `registration_fee_settings` | 2 | 16 KB | 2 | 1 | used | master ค่าลงทะเบียน |
| `roles` | 10 | 32 KB | 18 | 1 | used | master role |
| `salary_entries` | 20 | 64 KB | 7 | 2 | used | transaction เงินเดือน |
| `salary_plans` | 2 | 80 KB | 8 | 3 | used | transaction/header เงินเดือน |
| `sessions` | 1 | 48 KB | 1 | 0 | system table | Laravel session table ห้ามลบ |
| `users` | 13 | 64 KB | 27 | 13 | used | account/login/review workflow |

## Candidate Column ที่อาจไม่ได้ใช้ / Legacy

| table.column | NULL | code usage | status | reason |
|---|---:|---|---|---|
| `planning_years.review_status` | 0/2 = 0% | ไม่เจอใน `app/resources/routes/database/seeders/tests` ยกเว้น migration/schema | cleaned | ระบบปัจจุบันใช้ `planning_years.status` และ constants ใน `PlanningYear` แทน |
| `planning_years.submitted_by` | 2/2 = 100% | ไม่เจอ runtime code ใช้ | cleaned | ไม่อยู่ใน fillable/cast หลัก และไม่มี controller/service เรียกใช้ |
| `academic_income_plans.nuol_pct_1_1` | 0/2 = 0% | เจอเฉพาะ migration เก่า | cleaned | ปัจจุบันใช้ `nuol_pct_settings` และ snapshot ใน `academic_income_items` |
| `academic_income_plans.nuol_pct_1_2` | 0/2 = 0% | เจอเฉพาะ migration เก่า | cleaned | เหตุผลเดียวกับ `nuol_pct_1_1` |
| `academic_income_plans.nuol_pct_1_3` | 0/2 = 0% | เจอเฉพาะ migration เก่า | cleaned | เหตุผลเดียวกับ `nuol_pct_1_1` |
| `academic_income_plans.nuol_pct_1_4` | 0/2 = 0% | เจอเฉพาะ migration เก่า | cleaned | เหตุผลเดียวกับ `nuol_pct_1_1` |

> หมายเหตุ: column กลุ่มนี้ยังไม่ควรลบทันทีถ้ายังไม่ได้ทดสอบ flow academic income / preview / print ครบ แต่จาก audit ตอนนี้เป็น candidate ที่ชัดที่สุด

## Column ที่ NULL เยอะ แต่ยังมีเหตุผลให้เก็บ

| table.column | NULL | status | reason |
|---|---:|---|---|
| `expense_plan_values.value_boolean` | 1437/1437 = 100% | maybe simplify later | code รองรับ field type boolean แต่ field ปัจจุบันยังไม่มีใช้ |
| `expense_plan_values.value_date` | 1437/1437 = 100% | maybe simplify later | code รองรับ field type date แต่ field ปัจจุบันยังไม่มีใช้ |
| `expense_plans.detail` | 392/392 = 100% | used optional | code ยังใช้เป็น note/detail fallback ใน expense report/manage |
| `expense_sections.description` | 14/14 = 100% | optional | field คำอธิบายใน settings ยังมีใน schema |
| `expense_subsections.description` | 76/76 = 100% | optional | field คำอธิบายใน settings ยังมีใน schema |
| `planning_year_reviewers.notified_at` | 7/7 = 100% | keep | รองรับ notification/review workflow ถึงตอนนี้ยังไม่เคย set |
| `planning_years.current_review_round_id` | 2/2 = 100% | keep | ใช้ตอนเปิด review round |
| `planning_years.review_requested_at` | 2/2 = 100% | keep | ใช้ตอนส่ง review |
| `planning_years.review_closed_at` | 2/2 = 100% | keep | ใช้ตอนปิด review |
| `planning_years.period_1_2_saved_at` | 2/2 = 100% | keep | ใช้ workflow บันทึก period 1-2 |
| `planning_years.period_3_4_saved_at` | 2/2 = 100% | keep | ใช้ workflow บันทึก period 3-4 |
| `course_credit_settings.gov_doc_id` | 68/68 = 100% | keep optional | UI/settings ยังมีช่องเอกสารอ้างอิง |
| `nuol_pct_settings.gov_doc_id` | 3/3 = 100% | keep optional | UI/settings ยังมีช่องเอกสารอ้างอิง |
| `registration_fee_settings.gov_doc_id` | 2/2 = 100% | keep optional | UI/settings ยังมีช่องเอกสารอ้างอิง |
| `users.department_id` | 13/13 = 100% | keep unless workflow removed | admin user create/edit/filter ยังรองรับ department |
| `academic_income_plans.notes` | 2/2 = 100% | optional | note field ยังอยู่ใน model/controller |
| `salary_plans.notes` | 2/2 = 100% | optional | note field ยังอยู่ใน salary plan |
| `planning_years.description` | 2/2 = 100% | optional | คำอธิบายแผนปี |
| `expense_pattern_fields.default_value` | 29/33 = 87.88% | optional | default ของ field บางชนิดเท่านั้น |
| `expense_subsections.parent_id` | 66/76 = 86.84% | keep | เป็น self-tree; NULL หมายถึงหมวด root |
| `course_credit_settings.year1_credit_unit` | 58/68 = 85.29% | keep | ใช้เฉพาะ master/phd year 1 ตามสูตร 60/40 |

## จุดข้อมูลแปลกที่ควรดูต่อ

| item | count | status | reason |
|---|---:|---|---|
| `expense_plan_values` rows ที่ `field_key = 'note'` และ value ทุกช่องเป็น NULL | 7 | cleaned | เป็นแถวเปล่า ไม่ใช่ column/table ที่ต้องลบ schema |
| `expense_plan_values.value_text` NULL | 653/1437 = 45.44% | normal | เพราะ field numeric จะเก็บใน `value_number` |
| `expense_plan_values.value_number` NULL | 791/1437 = 55.05% | normal | เพราะ field text จะเก็บใน `value_text` |

## ข้อเสนอแนะ

1. ถ้าจะ clean schema ต่อ ให้เริ่มจาก candidate column กลุ่มนี้ก่อน:
   - `planning_years.review_status`
   - `planning_years.submitted_by`
   - `academic_income_plans.nuol_pct_1_1`
   - `academic_income_plans.nuol_pct_1_2`
   - `academic_income_plans.nuol_pct_1_3`
   - `academic_income_plans.nuol_pct_1_4`

2. ถ้าจะ clean data ไม่ใช่ schema ให้พิจารณาแถวว่าง:
   - `expense_plan_values` ที่ `field_key = 'note'`
   - ทุก value column เป็น NULL

3. ยังไม่แนะนำให้ลบ column เหล่านี้ตอนนี้:
   - `expense_plan_values.value_boolean`
   - `expense_plan_values.value_date`
   - `planning_year_reviewers.notified_at`
   - `users.department_id`
   - `gov_doc_id` ใน settings tables

เพราะยังมี code หรือ workflow รองรับอยู่ แม้ข้อมูลปัจจุบันจะ NULL เยอะ
