<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — DopiFuture</title>
    <meta name="description" content="@yield('meta_description', 'DopiFuture — Digital Education Platform')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Tailwind CSS 4 via CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <style type="text/tailwindcss">
        @theme {
            --font-sans: 'Inter', ui-sans-serif, system-ui, sans-serif;
            --color-primary: #2844E1;
            --color-primary-deep: #003AC9;
            --color-dark-navy: #030E25;
            --color-page-bg: #F5F4F9;
            --color-input-bg: #F4F8FF;
            --color-search-bg: #EBEAEF;
            --color-table-header: #F4F8FF;
            --color-row-border: #D1D1D1;
            --color-txt: #030E25;
            --color-txt-sec: #303339;
            --color-txt-muted: #8E8D93;
            --color-txt-light: #A0A0A0;
            --color-active-green: #0E9F6E;
            --color-success-green: #00A36C;
            --color-error-red: #E33131;
            --color-grad-start: #1B57EC;
            --color-grad-end: #003AC9;
        }

        body {
            font-family: var(--font-sans);
            background: var(--color-page-bg);
            color: var(--color-txt);
            min-height: 100vh;
            line-height: 1.6;
        }

        /* ═══ SIDEBAR — Figma node-id: 1285-14822 ═══ */
        .dp-sidebar {
            width: 224px;
            min-height: 100vh;
            background: var(--color-dark-navy);
            position: fixed;
            top: 0; left: 0;
            z-index: 40;
            display: flex;
            flex-direction: column;
            padding: 16px;
            border-radius: 0 20px 20px 0;
            transition: transform 0.3s ease;
            overflow-y: auto;
        }

        .dp-sidebar-logo {
            padding: 24px 8px 32px;
            text-align: center;
        }

        .dp-sidebar-logo a {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #fff;
        }

        .dp-sidebar-logo .logo-img {
            width: 100px; height: 100px;
            border-radius: 16px;
            object-fit: cover;
        }

        .dp-sidebar-logo .logo-text {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        /* Nav Items — Figma F-28: NO icons, text only */
        .dp-nav-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 16px;
            line-height: 20px;
            font-weight: 500;
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            transition: all 0.2s;
            margin-bottom: 2px;
        }

        .dp-nav-item:hover {
            color: rgba(255,255,255,0.9);
            background: rgba(255,255,255,0.06);
        }

        .dp-nav-item.active {
            background: var(--color-primary);
            color: #ffffff;
        }

        .dp-nav-item svg { display: none; /* Figma F-28: no icons */ }

        .dp-nav-section {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.25);
            padding: 20px 16px 6px;
        }

        /* ═══ COLLAPSIBLE SUB-MENU ═══ */
        .dp-nav-parent {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 16px;
            line-height: 20px;
            font-weight: 500;
            color: rgba(255,255,255,0.6);
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 2px;
            user-select: none;
        }
        .dp-nav-parent:hover {
            color: rgba(255,255,255,0.9);
            background: rgba(255,255,255,0.06);
        }
        .dp-nav-parent.open {
            color: rgba(255,255,255,0.95);
            background: rgba(255,255,255,0.08);
        }
        .dp-nav-parent .chevron {
            transition: transform 0.25s ease;
            flex-shrink: 0;
        }
        .dp-nav-parent.open .chevron {
            transform: rotate(180deg);
        }
        .dp-nav-children {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            padding-left: 12px;
        }
        .dp-nav-children.open {
            max-height: 600px;
        }
        .dp-nav-sub-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.04em;
            color: rgba(255,255,255,0.3);
            padding: 10px 16px 4px;
        }
        .dp-nav-children .dp-nav-item {
            font-size: 13px;
            padding: 8px 16px;
        }

        /* ═══ MAIN WRAPPER ═══ */
        .dp-main {
            margin-left: 224px;
            min-height: 100vh;
        }

        /* ═══ TOP BAR ═══ */
        .dp-topbar {
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            background: #fff;
            border-bottom: 1px solid var(--color-row-border);
        }

        .dp-topbar-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--color-txt);
            line-height: 36px;
        }

        .dp-topbar-search {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--color-search-bg);
            border-radius: 34px;
            padding: 10px 16px;
            width: 300px;
        }

        .dp-topbar-search input {
            border: none;
            background: transparent;
            outline: none;
            font-size: 12px;
            color: var(--color-txt);
            width: 100%;
            font-family: inherit;
        }

        .dp-topbar-search input::placeholder { color: var(--color-txt-muted); }

        .dp-topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .dp-icon-btn {
            width: 36px; height: 36px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 8px;
            color: var(--color-txt-sec);
            cursor: pointer;
            transition: background 0.2s;
            border: none;
            background: transparent;
        }

        .dp-icon-btn:hover { background: var(--color-search-bg); }

        .dp-avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--color-primary), var(--color-primary-deep));
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 600; color: #fff;
        }

        .dp-user-link {
            display: flex; align-items: center; gap: 8px;
            text-decoration: none; color: var(--color-txt);
            font-size: 14px; font-weight: 500;
        }

        /* ═══ CONTENT ═══ */
        .dp-content { padding: 24px; }

        /* ═══ STAT CARDS (Gradient) ═══ */
        .dp-stat-card {
            background: linear-gradient(135deg, var(--color-grad-start), var(--color-grad-end));
            border-radius: 16px;
            padding: 20px;
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .dp-stat-card::after {
            content: '';
            position: absolute;
            top: -20px; right: -20px;
            width: 80px; height: 80px;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
        }

        .dp-stat-card .s-icon {
            width: 40px; height: 40px;
            border-radius: 10px;
            background: rgba(255,255,255,0.15);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 12px;
        }

        .dp-stat-card .s-value {
            font-size: 28px;
            font-weight: 700;
            line-height: 1.2;
        }

        .dp-stat-card .s-label {
            font-size: 14px;
            line-height: 20px;
            opacity: 0.85;
            margin-top: 4px;
        }

        .dp-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        /* Yellow variant — Figma §4.7 Study Space */
        .dp-stat-card-yellow {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            border-radius: 16px;
            padding: 20px;
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        .dp-stat-card-yellow .s-value { font-size: 28px; font-weight: 700; line-height: 1.2; }
        .dp-stat-card-yellow .s-label { font-size: 14px; line-height: 20px; opacity: 0.85; margin-top: 4px; }

        /* Profile Avatar — Figma §4.8 */
        .dp-profile-avatar {
            width: 120px; height: 120px;
            border-radius: 50%;
            border: 3px solid var(--color-primary);
            background: linear-gradient(135deg, var(--color-grad-start), var(--color-grad-end));
            display: flex; align-items: center; justify-content: center;
            font-size: 36px; font-weight: 700; color: #fff;
            margin: 0 auto 16px;
        }

        /* ═══ CARD CONTAINER ═══ */
        .dp-card {
            background: #ffffff;
            border-radius: 30px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            margin-bottom: 20px;
        }

        .dp-card-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--color-txt);
            margin-bottom: 16px;
        }

        /* ═══ SEARCH INPUT ═══ */
        .dp-search {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--color-search-bg);
            border-radius: 34px;
            padding: 10px 16px;
        }

        .dp-search input {
            border: none;
            background: transparent;
            font-size: 12px;
            color: var(--color-txt);
            outline: none;
            font-family: inherit;
            width: 100%;
        }

        .dp-search input::placeholder { color: var(--color-txt-muted); }

        /* ═══ DATA TABLE — Figma ═══ */
        .dp-table {
            width: 100%;
            border-collapse: collapse;
        }

        .dp-table thead th {
            background: transparent;
            padding: 14px 20px;
            font-size: 14px;
            font-weight: 500;
            color: var(--color-txt-sec);
            text-align: left;
            border-bottom: 1px solid var(--color-row-border);
            height: 52px;
            font-family: 'Nunito', sans-serif;
        }

        .dp-table tbody td {
            padding: 12px 20px;
            font-size: 12px;
            color: var(--color-txt);
            border-bottom: 1px solid var(--color-row-border);
            height: 56px;
            vertical-align: middle;
        }

        .dp-table tbody tr:last-child td { border-bottom: none; }
        .dp-table tbody tr:hover { background: #fafbfc; }

        .dp-table .muted { color: var(--color-txt-light); }

        /* Cancelled row — red left border like Figma */
        .dp-table tbody tr.dp-row-cancelled { position: relative; }
        .dp-table tbody tr.dp-row-cancelled td:first-child::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: #EF4444;
            border-radius: 0 2px 2px 0;
        }

        /* Action icon buttons — outlined circles like Figma */
        .dp-action-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 1px solid var(--color-row-border);
            background: transparent;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            color: var(--color-txt-muted);
            padding: 0;
        }
        .dp-action-icon:hover {
            background: #f3f4f6;
            border-color: #d1d5db;
        }
        .dp-action-icon.dp-action-delete {
            color: var(--color-error-red);
            border-color: rgba(239,68,68,0.3);
        }
        .dp-action-icon.dp-action-delete:hover {
            background: rgba(239,68,68,0.08);
        }
        .dp-action-icon.dp-action-primary {
            color: var(--color-primary);
            border-color: rgba(40,68,225,0.3);
        }
        .dp-action-icon.dp-action-primary:hover {
            background: rgba(40,68,225,0.08);
        }

        /* Avatar in table */
        .dp-td-avatar {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dp-td-avatar .av {
            width: 28px; height: 28px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--color-primary), var(--color-primary-deep));
            color: #fff;
            font-size: 10px; font-weight: 600;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        /* ═══ BADGES ═══ */
        .dp-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 100px;
            font-size: 12px;
            font-weight: 500;
        }

        .dp-badge-active { background: rgba(14,159,110,0.1); color: var(--color-active-green); }
        .dp-badge-error { background: rgba(227,49,49,0.1); color: var(--color-error-red); }
        .dp-badge-inactive { background: rgba(142,141,147,0.1); color: var(--color-txt-muted); }
        .dp-badge-pending { background: rgba(40,68,225,0.1); color: var(--color-primary); }

        /* ═══ BUTTONS ═══ */
        .dp-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--color-primary);
            color: #fff;
            font-size: 13px;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 24px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            font-family: inherit;
        }

        .dp-btn:hover {
            background: var(--color-primary-deep);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(40,68,225,0.3);
        }

        .dp-btn-success { background: var(--color-active-green); }
        .dp-btn-success:hover { background: #059669; }

        .dp-btn-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: var(--color-primary);
            color: #fff;
            font-size: 16px;
            font-weight: 500;
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            width: 100%;
            font-family: inherit;
            transition: all 0.2s;
        }

        .dp-btn-submit:hover { background: var(--color-primary-deep); }
        .dp-btn-submit:disabled { background: #E0E0E0; cursor: not-allowed; }

        .dp-btn-ghost {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: transparent;
            color: var(--color-txt-sec);
            font-size: 13px;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 8px;
            border: 1px solid var(--color-row-border);
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            transition: all 0.15s;
        }

        .dp-btn-ghost:hover { background: var(--color-input-bg); }

        /* Action Icons */
        .dp-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px; min-height: 32px;
            border-radius: 6px;
            cursor: pointer;
            border: none;
            background: transparent;
            transition: all 0.15s;
            gap: 4px;
        }

        .dp-action:hover { background: var(--color-input-bg); }

        .dp-action-edit { color: var(--color-txt-muted); }
        .dp-action-delete { color: var(--color-error-red); }
        .dp-action-view { color: var(--color-primary); }
        .dp-action-reset { color: var(--color-primary-deep); }

        /* ═══ PROGRESS BAR ═══ */
        .dp-progress {
            height: 8px;
            background: #E5E5E5;
            border-radius: 4px;
            overflow: hidden;
        }

        .dp-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--color-grad-start), var(--color-primary));
            border-radius: 4px;
            transition: width 0.5s ease;
        }

        /* ═══ FORM FIELDS ═══ */
        .dp-form-group { margin-bottom: 16px; }

        .dp-form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: var(--color-txt);
            margin-bottom: 8px;
        }

        .dp-form-input,
        .dp-form-select,
        .dp-form-textarea {
            width: 100%;
            background: var(--color-input-bg);
            border: none;
            border-radius: 8px;
            padding: 14px 16px;
            font-size: 14px;
            color: var(--color-txt);
            outline: none;
            font-family: inherit;
            transition: box-shadow 0.2s;
        }

        .dp-form-input::placeholder { color: var(--color-txt-light); }
        .dp-form-input:focus,
        .dp-form-select:focus,
        .dp-form-textarea:focus {
            box-shadow: 0 0 0 2px rgba(40,68,225,0.2);
        }

        .dp-form-textarea { min-height: 100px; resize: vertical; }

        .dp-form-error {
            color: var(--color-error-red);
            font-size: 12px;
            margin-top: 4px;
        }

        .dp-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        /* ═══ MODAL (legacy .show pattern for dashboard) ═══ */
        .dp-modal-overlay.show { opacity: 1; pointer-events: auto; }

        .dp-modal {
            background: #fff;
            border-radius: 24px;
            padding: 32px;
            width: 100%;
            max-width: 520px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }

        .dp-modal-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--color-txt);
            margin-bottom: 4px;
        }

        .dp-modal-subtitle {
            font-size: 14px;
            color: var(--color-txt-sec);
            margin-bottom: 24px;
        }

        .dp-modal-close {
            position: absolute;
            top: 24px; right: 24px;
            width: 28px; height: 28px;
            display: flex; align-items: center; justify-content: center;
            border: none; background: transparent;
            color: var(--color-txt-sec);
            cursor: pointer;
            border-radius: 6px;
        }

        .dp-modal-close:hover { background: var(--color-search-bg); }

        /* ═══ TABS ═══ */
        .dp-tabs {
            display: flex;
            gap: 24px;
            border-bottom: 1px solid var(--color-row-border);
            margin-bottom: 20px;
        }

        .dp-tab {
            padding: 12px 0;
            font-size: 14px;
            font-weight: 500;
            color: var(--color-txt-light);
            text-decoration: none;
            border-bottom: 2px solid transparent;
            cursor: pointer;
            transition: all 0.2s;
        }

        .dp-tab:hover { color: var(--color-txt-sec); }

        .dp-tab.active {
            color: var(--color-primary-deep);
            border-bottom-color: var(--color-primary-deep);
        }

        .dp-tab .tab-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 18px; height: 18px;
            padding: 0 5px;
            border-radius: 50%;
            background: var(--color-error-red);
            color: #fff;
            font-size: 10px;
            font-weight: 600;
            margin-left: 6px;
        }

        /* ═══ TOAST ═══ */
        .dp-toast {
            background: var(--color-success-green);
            color: #fff;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
        }

        .dp-toast-error {
            background: rgba(227,49,49,0.08);
            color: var(--color-error-red);
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 16px;
        }

        /* ═══ PAGINATION ═══ */
        .dp-pagination {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 16px 0;
            font-size: 12px;
        }

        .dp-pagination a,
        .dp-pagination span {
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid var(--color-row-border);
            background: #fff;
            color: var(--color-txt);
            font-size: 12px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .dp-pagination a:hover { background: var(--color-input-bg); }
        .dp-pagination .active-page { background: var(--color-primary); color: #fff; border-color: var(--color-primary); }
        .dp-pagination .disabled { opacity: 0.4; pointer-events: none; }

        /* ═══ ALERT ROW ═══ */
        .dp-alert-row { background: rgba(227,49,49,0.05) !important; }
        .dp-alert-row .alert-num { color: var(--color-error-red); font-weight: 600; }

        /* ═══ SIDEBAR FOOTER ═══ */
        .dp-sidebar-footer {
            margin-top: auto;
            padding-top: 12px;
            border-top: 1px solid rgba(255,255,255,0.08);
        }

        .dp-lang-btns {
            display: flex;
            gap: 4px;
            margin-top: 8px;
            padding: 0 4px 8px;
        }

        .dp-lang-btns form { flex: 1; }

        .dp-lang-btns button {
            width: 100%;
            padding: 6px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.2s;
        }

        /* ═══ MOBILE MENU ═══ */
        .dp-hamburger {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--color-txt-sec);
            margin-right: 12px;
        }

        .dp-sidebar-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 35;
            display: none;
        }

        .dp-sidebar-overlay.show { display: block; }

        /* ═══ MODAL — Figma node-id: 1405-9077 ═══ */
        .dp-modal-overlay {
            position: fixed; inset: 0;
            background: rgba(3,14,37,0.5);
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }

        .dp-modal-card {
            background: #fff;
            border-radius: 16px;
            padding: 32px;
            width: 480px;
            max-width: 90vw;
            max-height: 85vh;
            overflow-y: auto;
            position: relative;
            box-shadow: 0 24px 48px rgba(0,0,0,0.2);
        }

        .dp-modal-close {
            position: absolute; top: 16px; right: 16px;
            background: none; border: none; cursor: pointer;
            color: var(--color-txt-muted);
            transition: color 0.2s;
        }
        .dp-modal-close:hover { color: var(--color-error-red); }
        .dp-modal-close svg { width: 20px; height: 20px; }

        .dp-modal-title {
            font-size: 18px; font-weight: 600;
            color: var(--color-txt); margin-bottom: 4px;
        }
        .dp-modal-subtitle {
            font-size: 13px; color: var(--color-txt-muted);
            margin: 0 0 24px 0;
        }


        /* ═══ RESPONSIVE ═══ */
        @media (max-width: 1024px) {
            .dp-sidebar { transform: translateX(-100%); }
            .dp-sidebar.open { transform: translateX(0); }
            .dp-main { margin-left: 0; }
            .dp-hamburger { display: block; }
        }

        @media (max-width: 768px) {
            .dp-topbar-search { display: none; }
            .dp-content { padding: 16px; }
            .dp-stats-grid { grid-template-columns: repeat(2, 1fr); }
            .dp-form-grid { grid-template-columns: 1fr; }
            .dp-modal-card { padding: 20px; }
        }

        @media (max-width: 480px) {
            .dp-stats-grid { grid-template-columns: 1fr; }
        }
    </style>
    @yield('styles')
</head>

<body>
    @include('portal.partials._toast')
    @php
        $user = auth()->user();
        $cr = request()->route()?->getName() ?? '';
    @endphp

    {{-- Mobile overlay --}}
    <div class="dp-sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    {{-- ═══ SIDEBAR ═══ --}}
    <aside class="dp-sidebar" id="dpSidebar">
        <div class="dp-sidebar-logo">
            <a href="{{ route('portal.dashboard') }}">
                <img src="{{ asset('images/dopifuture-logo-gorsel.png') }}" alt="DopiFuture Icon" class="logo-img" style="border-radius: 0; object-fit: contain;">
                <img src="{{ asset('images/dopifuture-logo-yazi.png') }}" alt="DopiFuture" style="height: 28px; object-fit: contain; filter: invert(1) brightness(100);">
            </a>
        </div>

        <nav style="flex:1; display:flex; flex-direction:column; gap:2px;">
            {{-- Figma sidebar: flat list, no section headers --}}
            <a href="{{ route('portal.dashboard') }}" class="dp-nav-item {{ str_starts_with($cr, 'portal.dashboard') || str_starts_with($cr, 'portal.schools') || str_starts_with($cr, 'portal.classes') || str_starts_with($cr, 'portal.users') || str_starts_with($cr, 'portal.licenses') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Administration
            </a>


            <a href="{{ route('portal.reports.app', 'mission-way') }}" class="dp-nav-item {{ ($cr === 'portal.reports.app' && request()->route('app')?->slug === 'mission-way') || ($activeApp ?? '') === 'mission-way' ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                Mission WAY
            </a>

            <a href="{{ route('portal.reports.app', 'way-startup') }}" class="dp-nav-item {{ ($cr === 'portal.reports.app' && request()->route('app')?->slug === 'way-startup') || ($activeApp ?? '') === 'way-startup' ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Startup
            </a>

            <a href="{{ route('portal.reports.app', 'role-galaxy') }}" class="dp-nav-item {{ ($cr === 'portal.reports.app' && request()->route('app')?->slug === 'role-galaxy') || ($activeApp ?? '') === 'role-galaxy' ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                Role Galaxy
            </a>

            <a href="{{ route('portal.reports.app', 'study-space') }}" class="dp-nav-item {{ ($cr === 'portal.reports.app' && request()->route('app')?->slug === 'study-space') || ($activeApp ?? '') === 'study-space' ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                Study Space
            </a>

            <a href="{{ route('portal.reports.app', 'way-ai-coach') }}" class="dp-nav-item {{ ($cr === 'portal.reports.app' && request()->route('app')?->slug === 'way-ai-coach') || ($activeApp ?? '') === 'way-ai-coach' ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                WAY AI Coach
            </a>

            {{-- Figma F-28: no section headers — flat list --}}

            <a href="{{ route('portal.reports') }}" class="dp-nav-item {{ $cr === 'portal.reports' ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Reports
            </a>

            <a href="{{ route('portal.users.index', ['role' => 'student']) }}" class="dp-nav-item {{ str_starts_with($cr, 'portal.users') && request('role') === 'student' ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Students
            </a>

            <a href="{{ route('portal.users.index', ['role' => 'teacher']) }}" class="dp-nav-item {{ str_starts_with($cr, 'portal.users') && request('role') === 'teacher' ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V9a2 2 0 012-2h2a2 2 0 012 2v9a2 2 0 01-2 2h-2z"/></svg>
                Teachers
            </a>

            <a href="{{ route('portal.classes.index') }}" class="dp-nav-item {{ str_starts_with($cr, 'portal.classes') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Classes
            </a>

            <a href="{{ route('portal.profile') }}" class="dp-nav-item {{ str_starts_with($cr, 'portal.profile') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Profile
            </a>

            @if($user && $user->hasAnyRole(['super-admin','admin']))
            <a href="{{ route('admin.dashboard') }}" class="dp-nav-item">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Admin Panel
            </a>
            @endif
        </nav>

        {{-- Footer --}}
        <div class="dp-sidebar-footer">
            <form action="{{ route('portal.logout') }}" method="POST">
                @csrf
                <button type="submit" class="dp-nav-item" style="width:100%; color: rgba(255,255,255,0.45); border:none; cursor:pointer; font-family:inherit; background:transparent;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Log Out
                </button>
            </form>

        </div>
    </aside>

    {{-- ═══ MAIN ═══ --}}
    <div class="dp-main">
        <header class="dp-topbar">
            <div style="display:flex; align-items:center;">
                <button class="dp-hamburger" onclick="toggleSidebar()">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <span class="dp-topbar-title">@yield('page-title', 'Dashboard')</span>
            </div>

            <div class="dp-topbar-search" style="position:relative;">
                <svg width="16" height="16" fill="none" stroke="var(--color-txt-muted)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="dp-student-search" placeholder="Search students..." autocomplete="off">
                <div id="dp-search-results" style="display:none;position:absolute;top:100%;left:0;right:0;background:var(--color-card-bg);border:1px solid var(--color-row-border);border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.12);max-height:280px;overflow-y:auto;z-index:999;margin-top:4px;"></div>
            </div>

            <div class="dp-topbar-right">
                <a href="{{ route('portal.profile') }}" class="dp-user-link">
                    <div class="dp-avatar">{{ strtoupper(substr($user->name ?? 'U', 0, 1) . substr($user->surname ?? '', 0, 1)) }}</div>
                    <span class="dp-username" style="display:none;">{{ $user->name ?? '' }}</span>
                </a>
            </div>
        </header>

        <div class="dp-content">
            @if(session('success'))
                <div class="dp-toast" id="dpToast">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="dp-toast-error">{{ session('error') }}</div>
            @endif

            @yield('content')
        </div>

        <footer style="text-align:center; padding:24px; font-size:12px; color:var(--color-txt-muted);">
            &copy; {{ date('Y') }} DopiFuture. All rights reserved.
        </footer>
    </div>

    <script>
        function toggleSidebar(){
            document.getElementById('dpSidebar').classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        }
        function closeSidebar(){
            document.getElementById('dpSidebar').classList.remove('open');
            document.getElementById('sidebarOverlay').classList.remove('show');
        }
        function toggleSchoolMenu(el){
            el.classList.toggle('open');
            var ch = el.nextElementSibling;
            if(ch) ch.classList.toggle('open');
        }
        var t=document.getElementById('dpToast');
        if(t) setTimeout(function(){t.style.display='none';},4000);

        // Show username on desktop
        if(window.innerWidth >= 1024){
            var un = document.querySelector('.dp-username');
            if(un) un.style.display = 'inline';
        }

        // ── Student Search (Live) ──
        (function(){
            var inp = document.getElementById('dp-student-search');
            var res = document.getElementById('dp-search-results');
            if(!inp || !res) return;
            var timer = null;
            inp.addEventListener('input', function(){
                clearTimeout(timer);
                var q = inp.value.trim();
                if(q.length < 2){ res.style.display='none'; return; }
                timer = setTimeout(function(){
                    fetch('{{ route("portal.api.students.search") }}?q=' + encodeURIComponent(q), {
                        headers: {'X-Requested-With':'XMLHttpRequest'}
                    })
                    .then(function(r){ return r.json(); })
                    .then(function(data){
                        if(!data.length){ res.innerHTML='<div style="padding:16px;text-align:center;color:var(--color-txt-muted);font-size:13px;">No students found</div>'; res.style.display='block'; return; }
                        res.innerHTML = data.map(function(s){
                            return '<a href="'+s.url+'" style="display:flex;align-items:center;gap:10px;padding:10px 14px;text-decoration:none;color:inherit;border-bottom:1px solid var(--color-row-border);transition:background .15s;" onmouseover="this.style.background=\'var(--color-hover)\';" onmouseout="this.style.background=\'transparent\';">'
                                +'<div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-deep));color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;flex-shrink:0;">'+s.initials+'</div>'
                                +'<div><div style="font-weight:500;font-size:13px;">'+s.name+'</div><div style="font-size:11px;color:var(--color-txt-muted);">'+s.email+'</div></div>'
                                +'</a>';
                        }).join('');
                        res.style.display='block';
                    });
                }, 300);
            });
            inp.addEventListener('keydown', function(e){ if(e.key==='Escape'){ res.style.display='none'; inp.value=''; }});
            document.addEventListener('click', function(e){ if(!inp.contains(e.target)&&!res.contains(e.target)) res.style.display='none'; });
        })();
    </script>

    @yield('scripts')
</body>
</html>