<!DOCTYPE html>
<html lang="lo">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>ລາຍງານປະຈຳປີ {{ $year }}</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;600;700&family=Cinzel:wght@700&display=swap');

/* ═══════════════════════════════════
   Reset
═══════════════════════════════════ */
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

body {
    font-family: 'Noto Sans Lao', 'Phetsarath OT', sans-serif;
    font-size: 9pt;
    color: #111;
    background: #fff;
}

/* ═══════════════════════════════════
   Print setup
═══════════════════════════════════ */
@page {
    size: A4 landscape;
    margin: 12mm 10mm 15mm 10mm;

    @bottom-center {
        content: "ໜ້າ " counter(page) " / " counter(pages);
        font-size: 7pt;
        color: #999;
    }
}

/* ═══════════════════════════════════
   Page break helpers
═══════════════════════════════════ */
.page-break     { page-break-before: always; break-before: page; }
.avoid-break    { page-break-inside: avoid;  break-inside: avoid; }
.keep-together  { page-break-inside: avoid;  break-inside: avoid; }
.keep-with-next { page-break-after: avoid;   break-after: avoid; }

table           { page-break-inside: auto; }
thead           { display: table-header-group; }   /* repeat on every page */
tr              { page-break-inside: avoid; break-inside: avoid; }

/* ═══════════════════════════════════
   Screen toolbar (hidden on print)
═══════════════════════════════════ */
.toolbar {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    background: #1a2744;
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 300;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}
.toolbar a, .toolbar button {
    font-family: inherit;
    font-size: 13px;
    cursor: pointer;
    border: none;
    border-radius: 6px;
    padding: 7px 16px;
    text-decoration: none;
    line-height: 1;
}
.btn-print  { background: #c9991a; color: #fff; font-weight: 700; }
.btn-back   { background: rgba(255,255,255,0.12); color: #fff; }
.toc-links  { display: flex; gap: 6px; margin-left: 12px; }
.toc-links a {
    font-size: 11px;
    color: rgba(255,255,255,0.55);
    background: rgba(255,255,255,0.07);
    padding: 4px 10px;
    border-radius: 4px;
    text-decoration: none;
}
.toc-links a:hover { color: #fff; background: rgba(255,255,255,0.15); }
.toolbar-spacer { flex: 1; }
.toolbar-year {
    font-family: 'Cinzel', serif;
    font-size: 15px;
    font-weight: 700;
    color: #c9991a;
    letter-spacing: 0.05em;
}

.doc-wrap { padding-top: 58px; }

@media print {
    .toolbar  { display: none; }
    .doc-wrap { padding-top: 0; }
}

/* ═══════════════════════════════════
   Document header
═══════════════════════════════════ */
.doc-header {
    text-align: center;
    padding-bottom: 8pt;
    border-bottom: 1.5pt solid #1a2744;
    margin-bottom: 12pt;
}
.motto      { font-size: 7.5pt; letter-spacing: 0.06em; line-height: 1.8; }
.divider    { width: 100pt; border: none; border-top: 0.8pt solid #555; margin: 4pt auto; }
.org-line   { font-size: 9.5pt; font-weight: 700; line-height: 1.7; }
.doc-title  {
    font-family: 'Cinzel', serif;
    font-size: 15pt;
    font-weight: 700;
    color: #1a2744;
    margin-top: 6pt;
    letter-spacing: 0.03em;
}
.doc-sub    { font-size: 10pt; font-weight: 600; margin-top: 2pt; color: #374151; }

/* ═══════════════════════════════════
   Table of Contents (screen only)
═══════════════════════════════════ */
.toc-card {
    border: 0.5pt solid #dde4ef;
    border-radius: 6pt;
    padding: 8pt 12pt;
    margin-bottom: 12pt;
    display: flex;
    flex-wrap: wrap;
    gap: 6pt 16pt;
    align-items: center;
}
.toc-card .toc-label {
    font-size: 7.5pt;
    color: #9ca3af;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    font-weight: 700;
    flex-basis: 100%;
    margin-bottom: 2pt;
}
.toc-item {
    font-size: 8pt;
    color: #1a2744;
    padding: 3pt 8pt;
    border: 0.5pt solid #c3cfe0;
    border-radius: 4pt;
    text-decoration: none;
}
@media print { .toc-card { display: none; } }

/* ═══════════════════════════════════
   Section heading
═══════════════════════════════════ */
.sec-hd {
    display: flex;
    align-items: center;
    gap: 8pt;
    background: #1a2744;
    color: #fff;
    padding: 5pt 10pt;
    margin-top: 0;
    margin-bottom: 0;
}
.sec-hd .sec-num {
    font-family: 'Cinzel', serif;
    font-size: 9pt;
    font-weight: 700;
    color: #c9991a;
    white-space: nowrap;
}
.sec-hd .sec-text {
    font-size: 9pt;
    font-weight: 700;
}

/* ═══════════════════════════════════
   Subsection heading
═══════════════════════════════════ */
.subsec-hd {
    background: #e8edf5;
    color: #1a2744;
    font-weight: 700;
    font-size: 8.5pt;
    padding: 3pt 8pt;
    margin-top: 8pt;
    border-left: 3pt solid #1a2744;
}

/* ═══════════════════════════════════
   Tables
═══════════════════════════════════ */
.rpt-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 7.5pt;
    margin-bottom: 4pt;
}
.rpt-table th {
    background: #dde4ef;
    border: 0.5pt solid #8899b4;
    padding: 3pt 3.5pt;
    text-align: center;
    font-weight: 700;
    font-size: 7pt;
    line-height: 1.35;
    color: #1a2744;
}
.rpt-table td {
    border: 0.5pt solid #c5cedc;
    padding: 2.5pt 4pt;
    vertical-align: middle;
    line-height: 1.3;
}
.rpt-table td.c { text-align: center; }
.rpt-table td.r {
    text-align: right;
    font-variant-numeric: tabular-nums;
    font-feature-settings: "tnum";
}
.rpt-table td.prog { font-size: 7.5pt; }
.rpt-table td.dim  { color: #6b7280; font-size: 7pt; }

.rpt-table tr:nth-child(even) { background: #f9fafb; }
.rpt-table tr:hover { background: #f0f4fa; }
@media print { .rpt-table tr:hover { background: inherit; } }

.rpt-table tr.subtotal td {
    background: #e8edf5 !important;
    font-weight: 700;
    border-top: 0.8pt solid #1a2744;
}
.rpt-table tr.subtotal td.r { color: #1a2744; font-size: 8pt; }

/* ═══════════════════════════════════
   Placeholder (module not built yet)
═══════════════════════════════════ */
.placeholder {
    text-align: center;
    padding: 20pt 0;
    color: #9ca3af;
    border: 0.5pt dashed #c5cedc;
    border-radius: 4pt;
    margin-top: 6pt;
}
.placeholder .ph-icon { font-size: 18pt; margin-bottom: 4pt; }
.placeholder .ph-text { font-size: 8pt; }

/* ═══════════════════════════════════
   Summary / Grand total
═══════════════════════════════════ */
.grand-bar {
    margin-top: 10pt;
    border: 1pt solid #1a2744;
    border-radius: 4pt;
    overflow: hidden;
}
.grand-bar-head {
    background: #1a2744;
    color: #c9991a;
    font-family: 'Cinzel', serif;
    font-weight: 700;
    font-size: 10pt;
    padding: 5pt 10pt;
    letter-spacing: 0.03em;
}
.grand-table th {
    background: #1a2744;
    color: #fff;
    border-color: #3a4e6e;
    font-size: 7.5pt;
}
.grand-table tr.grand-row td {
    background: #fdf3d0;
    font-weight: 700;
    font-size: 9.5pt;
    color: #1a2744;
    border-top: 1.5pt solid #1a2744;
}

/* ═══════════════════════════════════
   Signature block
═══════════════════════════════════ */
.signatures {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24pt;
    margin-top: 20pt;
    text-align: center;
    font-size: 8pt;
}
.sig-space   { height: 36pt; }
.sig-line    { border-top: 0.5pt solid #555; padding-top: 3pt; }
.sig-role    { font-weight: 700; font-size: 8.5pt; }
.sig-name    { color: #6b7280; font-size: 7.5pt; margin-top: 2pt; }
</style>
</head>
<body>

{{-- ═══ Screen toolbar ═══ --}}
<div class="toolbar">
    <button class="btn-print" onclick="window.print()">🖨 ພິມ / PDF</button>
    <a href="javascript:history.back()" class="btn-back">← ກັບໄປ</a>
    <div class="toc-links">
        @foreach($sections as $sec)
            <a href="#sec-{{ $sec['id'] }}">{{ $sec['title'] }}</a>
        @endforeach
    </div>
    <div class="toolbar-spacer"></div>
    <span class="toolbar-year">ສົກ {{ $year }}</span>
</div>

<div class="doc-wrap">

    {{-- ═══ Document header ═══ --}}
    <div class="doc-header">
        <div class="motto">ສາທາລະນະລັດ ປະຊາທິປະໄຕ ປະຊາຊົນລາວ</div>
        <div class="motto">ສັນຕິພາບ &nbsp;·&nbsp; ເອກະລາດ &nbsp;·&nbsp; ປະຊາທິປະໄຕ &nbsp;·&nbsp; ເອກະພາບ &nbsp;·&nbsp; ວັດທະນະຖາວອນ</div>
        <hr class="divider">
        <div class="org-line">ມະຫາວິທະຍາໄລແຫ່ງຊາດ (ມຊ) &nbsp;·&nbsp; ຄະນະວິທະຍາສາດທຳມະຊາດ (ຄວທ)</div>
        <div class="doc-title">ລາຍງານການເງິນປະຈຳສົກ</div>
        <div class="doc-sub">ສົກປີງົບປະມານ &nbsp;{{ $year }}</div>
    </div>

    {{-- ═══ Screen-only TOC ═══ --}}
    <div class="toc-card">
        <div class="toc-label">ສາລະບານ</div>
        @foreach($sections as $i => $sec)
            <a href="#sec-{{ $sec['id'] }}" class="toc-item">
                {{ $i + 1 }}. {{ $sec['title'] }}
            </a>
        @endforeach
    </div>

    {{-- ═══ Sections ═══ --}}
    @foreach($sections as $i => $section)

        @if($i > 0)
            <div class="page-break"></div>
        @endif

        {{-- Section anchor + heading --}}
        <div id="sec-{{ $section['id'] }}" class="keep-with-next">
            <div class="sec-hd">
                <span class="sec-num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                <span class="sec-text">{{ $section['title'] }}</span>
            </div>
        </div>

        {{-- Section content --}}
        @include($section['view'], $section['data'])

    @endforeach

    {{-- ═══ Signature block (last page) ═══ --}}
    <div class="signatures" style="margin-top:24pt;">
        <div>
            <div style="font-size:7.5pt;color:#9ca3af;margin-bottom:2pt;">ຜູ້ຈັດທຳ</div>
            <div class="sig-space"></div>
            <div class="sig-line">
                <div class="sig-role">ຫົວໜ້າພະແນກການເງິນ</div>
                <div class="sig-name">ຊື່ ···················· ນາມສະກຸນ ····················</div>
            </div>
        </div>
        <div>
            <div style="font-size:7.5pt;color:#9ca3af;margin-bottom:2pt;">ຜູ້ກວດສອບ</div>
            <div class="sig-space"></div>
            <div class="sig-line">
                <div class="sig-role">ຮອງຄະນະບໍດີ</div>
                <div class="sig-name">ຊື່ ···················· ນາມສະກຸນ ····················</div>
            </div>
        </div>
        <div>
            <div style="font-size:7.5pt;color:#9ca3af;text-align:right;margin-bottom:2pt;">
                ວຽງຈັນ, ວັນທີ ...... ເດືອນ ......... ປີ {{ $year }}
            </div>
            <div class="sig-space"></div>
            <div class="sig-line">
                <div class="sig-role">ຄະນະບໍດີ ຄວທ</div>
                <div class="sig-name">ຊື່ ···················· ນາມສະກຸນ ····················</div>
            </div>
        </div>
    </div>

</div>{{-- end doc-wrap --}}
</body>
</html>
