@once
<style>
    .admin-resource {
        display: flex;
        flex-direction: column;
        gap: .9rem;
    }

    .admin-resource-bar {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 1rem;
        align-items: end;
        background: #fff;
        border: 1px solid var(--fns-gray-200);
        border-left: 4px solid var(--fns-gold);
        border-radius: 8px;
        padding: 1rem;
        box-shadow: 0 1px 8px rgba(26,39,68,.06);
    }

    .admin-resource-kicker {
        color: var(--fns-gray-600);
        font-size: .7rem;
        font-weight: 850;
        margin-bottom: .25rem;
    }

    .admin-resource-title {
        color: var(--fns-navy);
        font-size: 1.08rem;
        font-weight: 850;
        line-height: 1.25;
        margin: 0;
    }

    .admin-resource-copy {
        color: var(--fns-gray-600);
        font-size: .78rem;
        line-height: 1.55;
        margin-top: .28rem;
    }

    .admin-resource-actions,
    .admin-filter-actions,
    .admin-row-actions {
        display: flex;
        align-items: center;
        gap: .45rem;
        flex-wrap: wrap;
    }

    .admin-resource-actions {
        justify-content: flex-end;
    }

    .admin-resource .fns-btn svg,
    .admin-icon-btn svg,
    .admin-empty svg {
        width: 14px;
        height: 14px;
        flex: 0 0 auto;
    }

    .admin-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .7rem;
    }

    .admin-stat {
        background: #fff;
        border: 1px solid var(--fns-gray-200);
        border-radius: 8px;
        padding: .8rem .9rem;
        box-shadow: 0 1px 6px rgba(26,39,68,.05);
    }

    .admin-stat span {
        display: block;
        color: var(--fns-gray-600);
        font-size: .72rem;
        font-weight: 750;
        margin-bottom: .28rem;
    }

    .admin-stat strong {
        color: var(--fns-navy);
        font-family: 'Cinzel', serif;
        font-size: 1.35rem;
        line-height: 1;
    }

    .admin-filter-panel,
    .admin-table-panel {
        background: #fff;
        border: 1px solid var(--fns-gray-200);
        border-radius: 8px;
        box-shadow: 0 1px 8px rgba(26,39,68,.06);
        overflow: hidden;
    }

    .admin-filter-panel {
        padding: .85rem;
    }

    .admin-filter-form {
        display: grid;
        grid-template-columns: minmax(240px, 1fr) repeat(var(--filter-cols, 0), minmax(170px, .32fr)) auto;
        gap: .55rem;
        align-items: end;
    }

    .admin-filter-form-simple {
        grid-template-columns: minmax(240px, 1fr) auto;
    }

    .admin-field {
        display: flex;
        flex-direction: column;
        gap: .25rem;
        min-width: 0;
    }

    .admin-field label {
        color: var(--fns-gray-600);
        font-size: .7rem;
        font-weight: 800;
    }

    .admin-field input,
    .admin-field select {
        width: 100%;
        min-height: 2.25rem;
        border: 1px solid var(--fns-gray-200);
        border-radius: 7px;
        color: #111827;
        font-size: .8rem;
        padding: .45rem .65rem;
        background: #fff;
        box-shadow: none;
    }

    .admin-field input:focus,
    .admin-field select:focus {
        border-color: var(--fns-gold);
        box-shadow: 0 0 0 3px rgba(201,153,26,.14);
        outline: none;
    }

    .admin-table-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .8rem;
        padding: .8rem .95rem;
        border-bottom: 1px solid var(--fns-gray-200);
    }

    .admin-table-head h2 {
        color: var(--fns-navy);
        font-size: .9rem;
        font-weight: 850;
        margin: 0;
    }

    .admin-table-head span {
        color: var(--fns-gray-600);
        font-size: .74rem;
        font-weight: 750;
    }

    .admin-table-scroll {
        overflow-x: auto;
    }

    .admin-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 760px;
    }

    .admin-table thead {
        background: #f8f7f4;
    }

    .admin-table th {
        color: var(--fns-gray-600);
        font-size: .7rem;
        font-weight: 850;
        padding: .62rem .85rem;
        text-align: left;
        white-space: nowrap;
    }

    .admin-table td {
        border-top: 1px solid var(--fns-gray-200);
        color: #374151;
        font-size: .8rem;
        padding: .72rem .85rem;
        vertical-align: middle;
    }

    .admin-table tbody tr:hover {
        background: rgba(26,39,68,.025);
    }

    .admin-cell-strong {
        color: var(--fns-navy);
        font-weight: 850;
    }

    .admin-muted {
        color: var(--fns-gray-600);
        font-size: .74rem;
    }

    .admin-code {
        display: inline-flex;
        align-items: center;
        min-height: 1.65rem;
        border: 1px solid rgba(26,39,68,.14);
        border-radius: 6px;
        background: rgba(26,39,68,.045);
        color: var(--fns-navy);
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        font-size: .78rem;
        font-weight: 800;
        padding: .15rem .45rem;
    }

    .admin-pill {
        display: inline-flex;
        align-items: center;
        min-height: 1.55rem;
        border-radius: 999px;
        border: 1px solid;
        font-size: .7rem;
        font-weight: 850;
        line-height: 1;
        padding: .22rem .55rem;
        white-space: nowrap;
    }

    .admin-pill-navy { background: rgba(26,39,68,.07); border-color: rgba(26,39,68,.16); color: var(--fns-navy); }
    .admin-pill-green { background: rgba(26,74,46,.09); border-color: rgba(26,74,46,.2); color: #166534; }
    .admin-pill-gold { background: rgba(201,153,26,.12); border-color: rgba(201,153,26,.25); color: #80610f; }
    .admin-pill-red { background: rgba(139,26,26,.08); border-color: rgba(139,26,26,.18); color: #991b1b; }
    .admin-pill-gray { background: #f3f4f6; border-color: #e5e7eb; color: #4b5563; }

    .admin-row-actions {
        justify-content: flex-end;
        flex-wrap: nowrap;
    }

    .admin-icon-btn {
        display: inline-grid;
        place-items: center;
        width: 30px;
        height: 30px;
        border: 1px solid var(--fns-gray-200);
        border-radius: 7px;
        color: var(--fns-navy);
        background: #fff;
        cursor: pointer;
        transition: background .15s ease, border-color .15s ease, color .15s ease;
    }

    .admin-icon-btn:hover {
        background: rgba(201,153,26,.08);
        border-color: rgba(201,153,26,.32);
        color: #80610f;
    }

    .admin-icon-btn-danger {
        color: #991b1b;
    }

    .admin-icon-btn-danger:hover {
        background: rgba(139,26,26,.08);
        border-color: rgba(139,26,26,.22);
        color: #7f1d1d;
    }

    .admin-empty {
        display: grid;
        place-items: center;
        gap: .4rem;
        padding: 2rem 1rem;
        color: var(--fns-gray-600);
        text-align: center;
    }

    .admin-empty svg {
        width: 28px;
        height: 28px;
        opacity: .45;
    }

    .admin-pagination {
        padding: .8rem .95rem;
        border-top: 1px solid var(--fns-gray-200);
    }

    @media (max-width: 1180px) {
        .admin-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 900px) {
        .admin-resource-bar,
        .admin-filter-form {
            grid-template-columns: 1fr;
        }

        .admin-resource-actions,
        .admin-filter-actions {
            justify-content: flex-start;
        }
    }

    @media (max-width: 640px) {
        .admin-stats {
            grid-template-columns: 1fr;
        }

        .admin-table-head {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>
@endonce
