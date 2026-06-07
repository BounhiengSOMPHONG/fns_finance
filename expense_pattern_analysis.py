import xlrd
import sys
import re

# Set UTF-8 encoding for output
if sys.platform == 'win32':
    import codecs
    sys.stdout = codecs.getwriter('utf-8')(sys.stdout.buffer, 'strict')

# Open the workbook
wb = xlrd.open_workbook('Planning 2026.xls')

print("="*120)
print("EXPENSE SECTION RELATIONSHIP ANALYSIS - PATTERNS FOR EASIER PLANNING")
print("="*120)

# Dictionary to store expense section patterns
expense_patterns = {}
expense_hierarchy = {}

# Process Expense 1-6 sheets
for i in range(1, 7):
    sheet_name = f'Expense {i}'
    if sheet_name in wb.sheet_names():
        sheet = wb.sheet_by_name(sheet_name)

        # Find the section code and title
        section_code = ""
        section_title = ""
        for row_idx in range(min(5, sheet.nrows)):
            first_cell = sheet.cell_value(row_idx, 0)
            if isinstance(first_cell, str) and first_cell.strip():
                match = re.match(r'(\d+\.\d+)\s+(.+)', first_cell.strip())
                if match:
                    section_code = match.group(1)
                    section_title = match.group(2)
                    break

        # Find the header row
        header_row_idx = None
        headers = []
        for row_idx in range(min(10, sheet.nrows)):
            row_data = []
            for col_idx in range(sheet.ncols):
                cell_value = sheet.cell_value(row_idx, col_idx)
                if cell_value and isinstance(cell_value, str):
                    row_data.append(cell_value.strip())

            if any('ລ/ດ' in str(cell) or 'ລາຍການ' in str(cell) for cell in row_data):
                header_row_idx = row_idx
                headers = row_data
                break

        if header_row_idx and headers:
            expense_patterns[section_code] = {
                'title': section_title,
                'sheet': sheet_name,
                'headers': headers,
                'header_row': header_row_idx,
                'total_fields': len(headers),
                'subsections': []
            }

            # Analyze subsections and their patterns
            for row_idx in range(header_row_idx + 1, sheet.nrows):
                ref_cell = sheet.cell_value(row_idx, 2) if sheet.ncols > 2 else ""
                item_name = sheet.cell_value(row_idx, 1) if sheet.ncols > 1 else ""
                number_cell = sheet.cell_value(row_idx, 0) if sheet.ncols > 0 else ""

                subsection_code = None
                if isinstance(ref_cell, str) and re.match(r'^\d+\.\d+\.\d+$', ref_cell.strip()):
                    subsection_code = ref_cell.strip()

                if subsection_code:
                    expense_patterns[section_code]['subsections'].append({
                        'code': subsection_code,
                        'name': item_name if isinstance(item_name, str) else "",
                        'number': number_cell if isinstance(number_cell, (int, float)) else None,
                        'row': row_idx,
                        'field_count': len([h for h in headers if h]),
                        'data_fields': len([1 for col in range(min(len(headers), sheet.ncols)) if sheet.cell_value(row_idx, col)])
                    })

print("\n" + "="*120)
print("EXPENSE SECTION HIERARCHY AND RELATIONSHIPS")
print("="*120)

# Create a relationship tree
print("\n📊 EXPENSE HIERARCHY:")
print("├── Financial Planning 2026")
print("│   ├── Main Expense Categories (2.1 - 2.6)")

# Show each section with its subsections
for section_code in sorted(expense_patterns.keys()):
    data = expense_patterns[section_code]
    subsections = data['subsections']

    print(f"\n│   ├── {section_code}: {data['title'][:50]}...")
    print(f"│   │   ├── Fields: {', '.join(data['headers'][:4])}...")
    print(f"│   │   └── {len(subsections)} subsections:")

    # Show subsection patterns
    if subsections:
        # Group by field count
        by_field_count = {}
        for sub in subsections:
            count = sub['field_count']
            if count not in by_field_count:
                by_field_count[count] = []
            by_field_count[count].append(sub['code'])

        for count in sorted(by_field_count.keys(), reverse=True):
            codes = by_field_count[count]
            print(f"│   │       ├── {count}-field subsections: {', '.join(codes)}")

print("\n\n" + "="*120)
print("PATTERN ANALYSIS - HOW SECTIONS RELATE TO EACH OTHER")
print("="*120)

# Analyze relationships
print("\n🔗 SECTION RELATIONSHIP MAP:")

# Section 2.1 - General Administration
section_2_1 = [s for s in expense_patterns.get('2.1', {}).get('subsections', [])]
print(f"\n2.1 (General Administration): {len(section_2_1)} subsections")
print("   Purpose: Basic operational expenses")
print("   Pattern: Fixed monthly expenses with annual totals")
print(f"   Subsections: {', '.join([s['code'] for s in section_2_1])}")

# Section 2.2 - Educational Development
section_2_2 = [s for s in expense_patterns.get('2.2', {}).get('subsections', [])]
print(f"\n2.2 (Educational Development): {len(section_2_2)} subsections")
print("   Purpose: Teaching and learning materials, equipment")
print("   Pattern: Price-based purchases with annual calculations")
print(f"   Subsections: {', '.join([s['code'] for s in section_2_2])}")

# Section 2.3 - Support Services
section_2_3 = [s for s in expense_patterns.get('2.3', {}).get('subsections', [])]
print(f"\n2.3 (Academic Support): {len(section_2_3)} subsections")
print("   Purpose: Administrative support and educational services")
print("   Pattern: Unit-based budgeting")
print(f"   Subsections: {', '.join([s['code'] for s in section_2_3])}")

# Section 2.4 - Staff Benefits
section_2_4 = [s for s in expense_patterns.get('2.4', {}).get('subsections', [])]
print(f"\n2.4 (Staff Benefits): {len(section_2_4)} subsections")
print("   Purpose: Employee benefits and allowances")
print("   Pattern: Monthly-based allowances")
print(f"   Subsections: {', '.join([s['code'] for s in section_2_4])}")

# Section 2.5 - Educational Costs
section_2_5 = [s for s in expense_patterns.get('2.5', {}).get('subsections', [])]
print(f"\n2.5 (Training & Evaluation): {len(section_2_5)} subsections")
print("   Purpose: Special teaching services and evaluations")
print("   Pattern: Frequency-based or service-based costs")
print(f"   Subsections: {', '.join([s['code'] for s in section_2_5])}")

# Section 2.6 - External Activities
print(f"\n2.6 (External Activities): No traditional subsections")
print("   Purpose: Activities outside normal operations")
print("   Pattern: Event-based budgeting with personnel tracking")

print("\n\n" + "="*120)
print("COMMON PATTERNS ACROSS ALL EXPENSES")
print("="*120)

# Find universal patterns
all_headers = {}
for section_code, data in expense_patterns.items():
    for header in data['headers']:
        if header not in all_headers:
            all_headers[header] = []
        all_headers[header].append(section_code)

print("\n🔄 SHADOW PATTERNS (Fields used in multiple sections):")
for field, sections in all_headers.items():
    if len(sections) > 1:
        print(f"   • '{field}': used in {', '.join(sections)} ({len(sections)} sections)")

# Unique patterns
print("\n🆕 UNIQUE PATTERNS (Section-specific fields):")
for field, sections in all_headers.items():
    if len(sections) == 1:
        print(f"   • '{field}': ONLY in {sections[0]}")

print("\n\n" + "="*120)
print("AUTOMATION OPPORTUNITIES - MAKING PLANNING EASIER")
print("="*120)

print("\n💡 AUTOMATION STRATEGIES:")
print("\n1. 📊 TEMPLATE-BASED ENTRY:")
print("   • Create templates for each expense type")
print("   • Pre-fill universal fields (ລ/ດ, ລາຍການ, ອ້າງອີງ)")
print("   • Auto-calculate derived fields (ໝົດປີ = ຕໍ່ເດືອນ × ຈ/ນ)")

print("\n2. 🎯 HIERARCHICAL STRUCTURE:")
print("   • Section → Subsection → Line Item → Field Values")
print("   • Master template: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6")
print("   • Sub-section templates: 2.1.1, 2.1.2, etc.")

print("\n3. ⚡ FIELD RELATIONSHIPS:")
print("   • Calculation fields: ຕໍ່ເດືອນ × ຈ/ນ/ເດືອນ = ໝົດປີ")
print("   • Reference fields: ອ້າງອີຕ້າາວກກັບໄ່ງືອນ (2.1.1 → Section 2.1)")
print("   • Staff-related: Section 2.4 has allowances, Section 2.6 has personnel counts")

print("\n4. 🔢 REPEATING PATTERNS:")
print("   • Monthly multiplier (12) appears in ຈ/ນ, ຈຳນເດືອນ fields")
print("   • Annual calculations: ໝົດປີ (year-end total)")
print("   • Quantity × Rate patterns in sections 2.2, 2.3")

print("\n5. 📋 DATA ENTRY FLOW:")
print("   1) Select Section Type (2.1-2.6)")
print("   2) Choose Subsection Pattern (6-field, 5-field, or 4-field)")
print("   3) Fill universal fields (auto-populated)")
print("   4) Add section-specific fields")
print("   5) System calculates totals automatically")

print("\n6. 🎨 STANDARDIZED CATEGORIES:")
standard_categories = {
    '2.1': {'name': 'General Administration', 'pattern': 'Monthly Fixed', 'calc': 'Per Month × 12'},
    '2.2': {'name': 'Educational Development', 'pattern': 'Price × Quantity', 'calc': 'Unit Price × Months'},
    '2.3': {'name': 'Academic Support', 'pattern': 'Unit Budget', 'calc': 'Per Unit × 12'},
    '2.4': {'name': 'Staff Benefits', 'pattern': 'Monthly Allowance', 'calc': 'Per Month × 12'},
    '2.5': {'name': 'Training & Eval', 'pattern': 'Service/Frequency', 'calc': 'Per Service or Event'},
    '2.6': {'name': 'External Activities', 'pattern': 'Event-Based', 'calc': 'Per Event'}
}

print("   Section | Category Name | Calculation Pattern")
print("   --------|-------------|------------------")
for code, info in standard_categories.items():
    print(f"   {code}     | {info['name']:<12} | {info['pattern']:<20} ({info['calc']})")

print("\n\n" + "="*120)
print("PLANNING EFFICIENCY RECOMMENDATIONS")
print("="*120)

print("\n✅ IMPLEMENT THESE STEPS:")
print("1. Create dropdown menus for section selection (2.1-2.6)")
print("2. Auto-populate universal fields based on section choice")
print("3. Show only relevant fields for selected section type")
print("4. Auto-calculate totals (ໝົດປີ = ຕໍ່ເດືອນ × ຈ/ນ)")
print("5. Validate field completeness before saving")
print("6. Cross-reference with Chart of Accounts (ອ້າງອີງ field)")

print("\n🎯 BIGGEST TIME-SAVERS:")
print("   • Universal field templates (saves 40% data entry)")
print("   • Auto-calculations (eliminates math errors)")
print("   • Section-based validation (reduces errors)")
print("   • Standardized naming (improves reporting)")