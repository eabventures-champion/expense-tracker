<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CediLedger — Personal Expense Tracker & Financial Insights Platform')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <style>
        /* ==========================================================================
           MODERN FINTECH DESIGN SYSTEM
           Refined surface hierarchy, rounded cards, subtle borders & mobile drawer
           ========================================================================== */
        :root {
            --plum: #1E1B2E;
            --plum-dark: #151321;
            --plum-surface: #2B2640;
            --plum-border: rgba(255, 255, 255, 0.08);
            --coral: #E66D55;
            --coral-dark: #D05A42;
            --coral-soft: rgba(230, 109, 85, 0.12);
            --emerald: #2D9F78;
            --emerald-dark: #238261;
            --emerald-soft: rgba(45, 159, 120, 0.12);
            --mustard: #E5A93C;
            --mustard-dark: #C88F28;
            --mustard-soft: rgba(229, 169, 60, 0.14);
            --ivory: #F4F5F8;
            --ivory-card: #FFFFFF;
            --ivory-alt: #F8F9FB;
            --ivory-border: #E5E7EF;
            --blue: #4E73DF;
            --blue-soft: rgba(78, 115, 223, 0.12);
            --rose: #D96D9B;
            --rose-soft: rgba(217, 109, 155, 0.12);
            --charcoal: #1E2028;
            --charcoal-dark: #16181E;
            --charcoal-border: rgba(255, 255, 255, 0.09);
            --text-dark: #14161F;
            --text-muted: #646A7A;
            --text-on-dark: #F7F8FA;
            --text-on-dark-muted: #A3A7BA;

            --radius-xl: 18px;
            --radius-lg: 14px;
            --radius-md: 10px;
            --radius-sm: 8px;
            --radius-pill: 9999px;

            --shadow-xs: 0 1px 2px rgba(20, 22, 31, 0.04);
            --shadow-sm: 0 2px 8px -2px rgba(20, 22, 31, 0.06), 0 1px 3px rgba(20, 22, 31, 0.04);
            --shadow-md: 0 10px 24px -6px rgba(20, 22, 31, 0.08), 0 4px 10px -2px rgba(20, 22, 31, 0.03);
            --shadow-lg: 0 20px 40px -12px rgba(20, 22, 31, 0.16);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--ivory);
            color: var(--text-dark);
            font-size: 13.5px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            max-width: 100%;
            overflow-x: hidden;
        }

        .mono {
            font-family: 'JetBrains Mono', monospace;
            font-variant-numeric: tabular-nums;
        }

        h1, h2, h3, h4, .heading-font {
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            letter-spacing: -0.02em;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        /* APP SHELL */
        .app-shell {
            display: grid;
            grid-template-columns: 252px 1fr;
            min-height: 100vh;
            transition: grid-template-columns 0.24s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .app-shell.sidebar-collapsed {
            grid-template-columns: 74px 1fr;
        }

        /* MOBILE SIDEBAR OVERLAY BACKDROP */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background-color: rgba(15, 17, 26, 0.6);
            backdrop-filter: blur(3px);
            z-index: 45;
        }

        /* MODERN SIDEBAR (3 FIXED ZONES: TOP HEADER+CTA, MIDDLE SCROLLABLE NAV, BOTTOM USER BAR) */
        .sidebar {
            background-color: var(--plum);
            color: var(--text-on-dark);
            border-right: 1px solid var(--plum-border);
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow: hidden;
            z-index: 50;
            transition: width 0.24s cubic-bezier(0.4, 0, 0.2, 1), transform 0.24s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .brand-box {
            padding: 14px 16px;
            border-bottom: 1px solid var(--plum-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            flex-shrink: 0;
            background-color: var(--plum);
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 11px;
            min-width: 0;
        }

        .brand-mark {
            width: 34px;
            height: 34px;
            background-color: var(--emerald);
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            font-size: 12.5px;
            border-radius: 9px;
            box-shadow: 0 4px 12px rgba(45, 159, 120, 0.35);
            flex-shrink: 0;
        }

        .brand-text {
            overflow: hidden;
            white-space: nowrap;
        }

        .brand-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 15.5px;
            font-weight: 800;
            color: #FFFFFF;
            line-height: 1.15;
            letter-spacing: -0.01em;
        }

        .brand-sub {
            font-size: 10.5px;
            color: var(--text-on-dark-muted);
            font-weight: 500;
        }

        .sidebar-toggle-btn {
            width: 28px;
            height: 28px;
            border-radius: 7px;
            border: 1px solid var(--plum-border);
            background-color: rgba(255, 255, 255, 0.06);
            color: var(--text-on-dark-muted);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
            transition: all 0.15s ease;
        }

        .sidebar-toggle-btn:hover {
            background-color: rgba(255, 255, 255, 0.14);
            color: #FFFFFF;
        }

        .topbar-sidebar-toggle {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            border: 1px solid var(--ivory-border);
            background-color: #FFFFFF;
            color: var(--text-dark);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: var(--shadow-xs);
            transition: all 0.15s ease;
            flex-shrink: 0;
        }

        .topbar-sidebar-toggle:hover {
            background-color: var(--ivory);
        }

        .sidebar-quick-action {
            padding: 12px 14px;
            border-bottom: 1px solid var(--plum-border);
            flex-shrink: 0;
            background-color: var(--plum);
        }

        .btn-quick-add {
            width: 100%;
            background-color: var(--coral);
            color: #FFFFFF;
            border: none;
            border-radius: var(--radius-md);
            padding: 9px 12px;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(230, 109, 85, 0.28);
            transition: all 0.18s ease;
            white-space: nowrap;
            overflow: hidden;
        }

        .btn-quick-add:hover {
            background-color: var(--coral-dark);
            transform: translateY(-1px);
        }

        .quick-add-plus {
            display: none;
            font-size: 17px;
            font-weight: 700;
            line-height: 1;
        }

        .kbd-hint {
            background-color: rgba(0, 0, 0, 0.22);
            color: #FFFFFF;
            padding: 2px 6px;
            border-radius: 5px;
            font-size: 10px;
            font-family: 'JetBrains Mono', monospace;
        }

        /* SCROLLABLE NAVIGATION ZONE */
        .sidebar-nav-scroll {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 8px 10px 12px;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.16) transparent;
        }

        .sidebar-nav-scroll::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-nav-scroll::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.18);
            border-radius: 9999px;
        }

        .nav-section-card {
            margin-bottom: 10px;
        }

        .nav-group-label {
            padding: 6px 10px 4px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #8E93A8;
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
        }

        .nav-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 10px;
            color: #C2C6D6;
            font-size: 12.5px;
            font-weight: 500;
            border-radius: var(--radius-sm);
            transition: background-color 0.14s ease, color 0.14s ease;
            white-space: nowrap;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.06);
            color: #FFFFFF;
        }

        /* Sticky active navigation link stays pinned clearly at top/bottom of scroll container without overlapping group headers */
        .nav-link.active {
            position: sticky;
            top: 4px;
            bottom: 4px;
            z-index: 6;
            background-color: var(--plum-surface);
            color: #FFFFFF;
            font-weight: 600;
            border: 1px solid rgba(45, 159, 120, 0.4);
            box-shadow: inset 3px 0 0 var(--emerald), 0 4px 12px rgba(15, 13, 24, 0.55);
        }

        .nav-link-inner {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .nav-icon {
            width: 17px;
            height: 17px;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .nav-badge {
            font-family: 'JetBrains Mono', monospace;
            font-size: 9.5px;
            padding: 2px 6px;
            border-radius: var(--radius-pill);
            font-weight: 600;
        }

        /* SIDEBAR FOOTER WITH USER & LOGOUT */
        .sidebar-footer {
            margin: 0;
            padding: 10px 12px;
            background-color: var(--plum-dark);
            border-top: 1px solid var(--plum-border);
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            white-space: nowrap;
            overflow: hidden;
            flex-shrink: 0;
        }

        .sidebar-user-info {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: var(--plum-surface);
            color: var(--mustard);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 12.5px;
            border: 1px solid var(--plum-border);
            flex-shrink: 0;
        }

        .user-meta-name {
            font-weight: 600;
            color: #FFFFFF;
            font-size: 12px;
            line-height: 1.2;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-meta-role {
            color: var(--text-on-dark-muted);
            font-size: 10.5px;
            font-family: 'JetBrains Mono', monospace;
        }

        .btn-logout-icon {
            width: 30px;
            height: 30px;
            border-radius: 7px;
            border: 1px solid rgba(230, 109, 85, 0.3);
            background-color: rgba(230, 109, 85, 0.12);
            color: #FF8A73;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
            transition: all 0.15s ease;
        }

        .btn-logout-icon:hover {
            background-color: var(--coral);
            color: #FFFFFF;
        }

        /* COLLAPSED SIDEBAR STATES (DESKTOP) */
        @media (min-width: 921px) {
            .app-shell.sidebar-collapsed .brand-box {
                padding: 12px 8px;
                flex-direction: column;
                justify-content: center;
                gap: 8px;
            }

            .app-shell.sidebar-collapsed .brand-text,
            .app-shell.sidebar-collapsed .nav-label,
            .app-shell.sidebar-collapsed .nav-badge,
            .app-shell.sidebar-collapsed .nav-group-label,
            .app-shell.sidebar-collapsed .user-meta-text,
            .app-shell.sidebar-collapsed .quick-add-label,
            .app-shell.sidebar-collapsed .kbd-hint {
                display: none;
            }

            .app-shell.sidebar-collapsed .quick-add-plus {
                display: inline-block;
            }

            .app-shell.sidebar-collapsed .btn-quick-add {
                justify-content: center;
                padding: 9px 0;
            }

            .app-shell.sidebar-collapsed .sidebar-nav-scroll {
                padding: 8px 8px;
            }

            .app-shell.sidebar-collapsed .nav-link {
                justify-content: center;
                padding: 10px 0;
            }

            .app-shell.sidebar-collapsed .nav-link-inner {
                justify-content: center;
            }

            .app-shell.sidebar-collapsed .sidebar-footer {
                flex-direction: column;
                justify-content: center;
                padding: 10px 6px;
                gap: 8px;
            }

            .app-shell.sidebar-collapsed .sidebar-toggle-icon {
                transform: rotate(180deg);
            }
        }

        /* MAIN WORKSPACE */
        .main-workspace {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        /* MODERN TOP COMMAND BAR */
        .topbar {
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            color: var(--text-dark);
            padding: 12px 26px;
            border-bottom: 1px solid var(--ivory-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
            position: sticky;
            top: 0;
            z-index: 30;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
            flex: 1;
        }

        .page-kicker {
            background-color: var(--ivory);
            color: var(--plum);
            border: 1px solid var(--ivory-border);
            font-size: 10.5px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: var(--radius-pill);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            white-space: nowrap;
        }

        .topbar-title {
            font-size: 17.5px;
            font-weight: 700;
            color: var(--text-dark);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        /* HEADER NOTIFICATION BELL & DROPDOWN PANEL */
        .notif-bell-wrapper {
            position: relative;
            display: inline-flex;
            align-items: center;
        }

        .btn-notif-bell {
            position: relative;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            border: 1px solid var(--ivory-border);
            background-color: #FFFFFF;
            color: var(--plum);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.16s ease;
            box-shadow: 0 1px 2px rgba(16, 18, 27, 0.05);
        }

        .btn-notif-bell:hover,
        .btn-notif-bell.active {
            background-color: var(--plum);
            color: #FFFFFF;
            border-color: var(--plum);
        }

        .notif-bell-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            min-width: 19px;
            height: 19px;
            padding: 0 5px;
            border-radius: 9999px;
            background-color: var(--coral);
            color: #FFFFFF;
            font-family: 'JetBrains Mono', monospace;
            font-size: 10.5px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #FFFFFF;
            box-shadow: 0 2px 6px rgba(230, 109, 85, 0.45);
        }

        .notif-dropdown-panel {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            width: 420px;
            max-width: calc(100vw - 28px);
            background-color: #FFFFFF;
            border: 1px solid var(--ivory-border);
            border-radius: 16px;
            box-shadow: 0 20px 45px -10px rgba(16, 18, 27, 0.22), 0 4px 14px rgba(16, 18, 27, 0.08);
            z-index: 120;
            display: none;
            flex-direction: column;
            overflow: hidden;
        }

        .notif-dropdown-panel.open {
            display: flex;
        }

        .notif-panel-header {
            background-color: var(--plum);
            color: #FFFFFF;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        .notif-tabs-bar {
            display: grid;
            grid-template-columns: 1fr 1fr;
            background-color: var(--ivory);
            padding: 5px;
            gap: 5px;
            border-bottom: 1px solid var(--ivory-border);
        }

        .notif-tab-btn {
            border: none;
            background: transparent;
            color: var(--text-muted);
            font-family: inherit;
            font-size: 12px;
            font-weight: 700;
            padding: 8px 10px;
            border-radius: 9px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.15s ease;
        }

        .notif-tab-btn:hover {
            color: var(--text-dark);
        }

        .notif-tab-btn.active {
            background-color: #FFFFFF;
            color: var(--plum);
            box-shadow: 0 1px 4px rgba(16, 18, 27, 0.08);
        }

        .notif-list-scroll {
            max-height: 390px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            divide-y: 1px solid var(--ivory-border);
        }

        .notif-item-card {
            padding: 13px 16px;
            border-bottom: 1px solid var(--ivory-border);
            display: flex;
            flex-direction: column;
            gap: 7px;
            transition: background-color 0.14s ease;
        }

        .notif-item-card:last-child {
            border-bottom: none;
        }

        .notif-item-card.unread {
            background-color: #FDF9F3;
            border-left: 3.5px solid var(--coral);
        }

        .notif-item-card.read {
            background-color: #FFFFFF;
            border-left: 3.5px solid var(--ivory-border);
            opacity: 0.88;
        }

        .notif-item-card:hover {
            background-color: var(--ivory-alt);
        }

        /* CONTENT CONTAINER */
        .content-wrap {
            padding: 22px 26px 44px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            max-width: 1600px;
            width: 100%;
            margin: 0 auto;
        }

        /* MODERN ROUNDED SECTION CARDS */
        .section-block {
            border-radius: var(--radius-lg);
            border: 1px solid var(--ivory-border);
            background-color: var(--ivory-card);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .section-block.theme-plum {
            background-color: var(--plum);
            color: var(--text-on-dark);
            border-color: var(--plum-border);
            box-shadow: var(--shadow-md);
        }

        .section-block.theme-charcoal {
            background-color: var(--charcoal);
            color: var(--text-on-dark);
            border-color: var(--charcoal-border);
            box-shadow: var(--shadow-md);
        }

        .section-block.theme-ivory {
            background-color: var(--ivory-card);
            color: var(--text-dark);
            border-color: var(--ivory-border);
        }

        .section-header {
            padding: 15px 20px;
            border-bottom: 1px solid var(--ivory-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .theme-plum .section-header {
            background-color: rgba(0, 0, 0, 0.16);
            border-bottom-color: var(--plum-border);
        }

        .theme-charcoal .section-header {
            background-color: rgba(0, 0, 0, 0.16);
            border-bottom-color: var(--charcoal-border);
        }

        .theme-ivory .section-header {
            background-color: var(--ivory-card);
            border-bottom-color: var(--ivory-border);
        }

        .section-title {
            font-size: 15px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-body {
            padding: 18px 20px;
        }

        /* MODERN KPI CARDS GRID */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }

        .kpi-cell {
            padding: 17px 19px;
            border-radius: var(--radius-lg);
            background-color: #FFFFFF;
            color: var(--text-dark);
            border: 1px solid var(--ivory-border);
            box-shadow: var(--shadow-sm);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 11px;
            position: relative;
            overflow: hidden;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .kpi-cell:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .kpi-cell::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background-color: var(--plum);
        }

        .kpi-cell.bg-plum::before { background-color: #5E50A1; }
        .kpi-cell.bg-charcoal::before { background-color: var(--mustard); }
        .kpi-cell.bg-emerald::before { background-color: var(--emerald); }
        .kpi-cell.bg-coral::before { background-color: var(--coral); }
        .kpi-cell.bg-mustard::before { background-color: var(--mustard); }
        .kpi-cell.bg-blue::before { background-color: var(--blue); }
        .kpi-cell.bg-rose::before { background-color: var(--rose); }
        .kpi-cell.bg-ivory::before { background-color: var(--emerald); }

        .kpi-cell.bg-emerald {
            background-color: #F4FBF8;
            border-color: rgba(45, 159, 120, 0.25);
        }

        .kpi-cell.bg-blue {
            background-color: #F4F7FE;
            border-color: rgba(78, 115, 223, 0.25);
        }

        .kpi-cell.bg-coral {
            background-color: #FDF6F4;
            border-color: rgba(230, 109, 85, 0.25);
        }

        .kpi-cell.bg-mustard {
            background-color: #FDF9F0;
            border-color: rgba(229, 169, 60, 0.3);
        }

        .kpi-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .kpi-label {
            font-size: 11.5px;
            font-weight: 600;
            color: var(--text-muted);
        }

        .kpi-amount {
            font-family: 'JetBrains Mono', monospace;
            font-size: 21px;
            font-weight: 700;
            letter-spacing: -0.03em;
            line-height: 1.15;
            color: var(--text-dark) !important;
        }

        .kpi-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 11px;
            color: var(--text-muted);
            gap: 6px;
            padding-top: 9px;
            border-top: 1px solid var(--ivory-border);
            flex-wrap: wrap;
        }

        .kpi-footer a {
            color: var(--plum);
            font-weight: 600;
        }

        .delta-tag {
            font-family: 'JetBrains Mono', monospace;
            font-size: 10.5px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: var(--radius-pill);
            display: inline-flex;
            align-items: center;
            gap: 3px;
            white-space: nowrap;
        }

        .delta-tag.up-bad {
            background-color: var(--coral-soft);
            color: var(--coral-dark);
        }
        .delta-tag.down-good,
        .delta-tag.up-good {
            background-color: var(--emerald-soft);
            color: var(--emerald-dark);
        }
        .delta-tag.neutral {
            background-color: var(--ivory);
            color: var(--text-dark);
            border: 1px solid var(--ivory-border);
        }

        /* MODERN BUTTONS */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 14px;
            font-size: 12.5px;
            font-weight: 600;
            border-radius: var(--radius-sm);
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.15s ease;
            white-space: nowrap;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-emerald {
            background-color: var(--emerald);
            color: #FFFFFF;
            box-shadow: 0 2px 6px rgba(45, 159, 120, 0.25);
        }
        .btn-emerald:hover { background-color: var(--emerald-dark); }

        .btn-coral {
            background-color: var(--coral);
            color: #FFFFFF;
            box-shadow: 0 2px 6px rgba(230, 109, 85, 0.25);
        }
        .btn-coral:hover { background-color: var(--coral-dark); }

        .btn-mustard {
            background-color: var(--mustard);
            color: #14161F;
            box-shadow: 0 2px 6px rgba(229, 169, 60, 0.25);
        }
        .btn-mustard:hover { background-color: var(--mustard-dark); color: #FFFFFF; }

        .btn-plum {
            background-color: var(--plum);
            color: #FFFFFF;
            border-color: rgba(255, 255, 255, 0.1);
        }
        .btn-plum:hover { background-color: var(--plum-surface); }

        .btn-blue { background-color: var(--blue); color: #FFFFFF; }
        .btn-ivory {
            background-color: #FFFFFF;
            color: var(--text-dark);
            border-color: var(--ivory-border);
            box-shadow: var(--shadow-xs);
        }
        .btn-ivory:hover { background-color: var(--ivory); }

        .btn-sm {
            padding: 6px 11px;
            font-size: 11.5px;
            border-radius: 7px;
        }

        /* MODERN TABLES */
        .table-wrap {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .data-table th {
            background-color: var(--ivory-alt);
            color: var(--text-muted);
            text-align: left;
            padding: 11px 14px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--ivory-border);
            white-space: nowrap;
        }

        .theme-plum .data-table th,
        .theme-charcoal .data-table th {
            background-color: rgba(0, 0, 0, 0.25);
            color: var(--text-on-dark-muted);
            border-bottom-color: var(--charcoal-border);
        }

        .data-table td {
            padding: 11px 14px;
            border-bottom: 1px solid var(--ivory-border);
            vertical-align: middle;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        .data-table tbody tr {
            transition: background-color 0.12s ease;
        }

        .data-table tbody tr:hover {
            background-color: var(--ivory-alt);
        }

        .theme-plum .data-table td,
        .theme-charcoal .data-table td {
            border-bottom-color: var(--charcoal-border);
        }

        .theme-plum .data-table tbody tr:hover,
        .theme-charcoal .data-table tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.04);
        }

        /* CATEGORY & STATUS BADGES */
        .cat-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            font-size: 11.5px;
            font-weight: 600;
            border-radius: var(--radius-pill);
            border: 1px solid var(--ivory-border);
            white-space: nowrap;
        }

        .cat-swatch {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            flex-shrink: 0;
        }

        /* PROGRESS BARS */
        .progress-track {
            width: 100%;
            height: 8px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-pill);
            overflow: hidden;
        }

        .theme-ivory .progress-track {
            background-color: var(--ivory);
        }

        .progress-fill {
            height: 100%;
            border-radius: var(--radius-pill);
            transition: width 0.3s ease;
        }

        /* FORMS & INPUTS */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .form-label {
            font-size: 11.5px;
            font-weight: 600;
            color: inherit;
            opacity: 0.85;
        }

        .form-control {
            padding: 8px 12px;
            font-size: 13px;
            font-family: inherit;
            border-radius: var(--radius-sm);
            border: 1px solid var(--ivory-border);
            background-color: #FFFFFF;
            color: var(--text-dark);
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .form-control:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.16);
        }

        /* CUSTOM COLOR SWATCH DROPDOWN PICKER */
        .color-select-widget {
            position: relative;
            width: 100%;
        }

        .color-select-trigger {
            width: 100%;
            padding: 8px 12px;
            font-size: 13px;
            font-family: 'JetBrains Mono', monospace;
            border-radius: var(--radius-sm);
            border: 1px solid var(--ivory-border);
            background-color: #FFFFFF;
            color: var(--text-dark);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            text-align: left;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .color-select-trigger:hover,
        .color-select-widget.open .color-select-trigger {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.16);
        }

        .color-select-label-wrap {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
            overflow: hidden;
        }

        .color-swatch-dot {
            width: 16px;
            height: 16px;
            border-radius: 5px;
            flex-shrink: 0;
            display: inline-block;
            border: 1.5px solid rgba(20, 22, 31, 0.18);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
        }

        .color-select-menu {
            position: absolute;
            top: calc(100% + 5px);
            left: 0;
            right: 0;
            background-color: #FFFFFF;
            border: 1px solid var(--ivory-border);
            border-radius: 11px;
            box-shadow: 0 14px 32px -6px rgba(16, 18, 27, 0.22), 0 4px 10px rgba(16, 18, 27, 0.08);
            z-index: 150;
            padding: 5px;
            display: none;
            flex-direction: column;
            gap: 2px;
            max-height: 240px;
            overflow-y: auto;
        }

        .color-select-widget.open .color-select-menu {
            display: flex;
        }

        .color-select-option {
            width: 100%;
            border: none;
            background: transparent;
            padding: 8px 10px;
            border-radius: 7px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 12.5px;
            color: var(--text-dark);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            text-align: left;
            transition: background-color 0.12s ease;
        }

        .color-select-option:hover {
            background-color: var(--ivory);
        }

        .color-select-option.selected {
            background-color: rgba(78, 115, 223, 0.1);
            font-weight: 700;
        }

        /* MODAL DRAWER FOR QUICK ADD / EDIT TRANSACTION */
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background-color: rgba(15, 17, 26, 0.65);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 100;
            padding: 16px;
        }

        .modal-backdrop.open {
            display: flex;
        }

        .modal-box {
            width: 100%;
            max-width: 680px;
            background-color: var(--ivory-card);
            border-radius: var(--radius-xl);
            border: 1px solid var(--ivory-border);
            box-shadow: var(--shadow-lg);
            max-height: 92vh;
            overflow-y: auto;
        }

        .modal-header {
            background-color: var(--plum);
            color: var(--text-on-dark);
            padding: 16px 22px;
            border-bottom: 1px solid var(--plum-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-body {
            padding: 22px;
        }

        /* ALERTS */
        .flash-banner {
            padding: 12px 18px;
            background-color: var(--emerald);
            color: #FFFFFF;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .flash-banner.error {
            background-color: var(--coral);
        }

        /* MODERN PAGINATION */
        .pagination-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .pagination-summary {
            font-size: 12.5px;
            color: var(--text-muted);
        }

        .pagination-list {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .page-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 34px;
            padding: 0 11px;
            font-size: 12.5px;
            font-weight: 600;
            border-radius: var(--radius-sm);
            border: 1px solid var(--ivory-border);
            background-color: #FFFFFF;
            color: var(--text-dark);
            box-shadow: var(--shadow-xs);
            transition: all 0.14s ease;
            user-select: none;
        }

        a.page-btn:hover {
            background-color: var(--ivory);
            border-color: #CFD4E2;
            transform: translateY(-1px);
        }

        .page-btn.active {
            background-color: var(--plum);
            color: #FFFFFF;
            border-color: var(--plum);
            box-shadow: 0 2px 6px rgba(30, 27, 46, 0.22);
        }

        .page-btn.disabled {
            opacity: 0.45;
            cursor: not-allowed;
            background-color: var(--ivory-alt);
            box-shadow: none;
        }

        nav[role="navigation"] svg {
            width: 16px;
            height: 16px;
        }

        /* GRID HELPERS */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .grid-sidebar-right { display: grid; grid-template-columns: 1.65fr 1fr; gap: 20px; }

        /* TABLET & MOBILE RESPONSIVENESS */
        @media (max-width: 1180px) {
            .kpi-grid { grid-template-columns: repeat(2, 1fr) !important; }
            .grid-2, .grid-3, .grid-sidebar-right { grid-template-columns: 1fr; }
        }

        @media (max-width: 920px) {
            .app-shell,
            .app-shell.sidebar-collapsed {
                grid-template-columns: minmax(0, 1fr);
                width: 100%;
                max-width: 100vw;
                overflow-x: hidden;
            }

            .main-workspace,
            .content-wrap,
            .section-block,
            .kpi-grid,
            .grid-2,
            .grid-3,
            .grid-sidebar-right {
                min-width: 0;
                max-width: 100%;
            }

            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                width: 268px;
                height: 100vh;
                transform: translateX(-100%);
                box-shadow: var(--shadow-lg);
            }

            .app-shell.mobile-nav-open .sidebar {
                transform: translateX(0);
            }

            .app-shell.mobile-nav-open .sidebar-backdrop {
                display: block;
            }

            .topbar {
                padding: 12px 16px;
            }

            .topbar-title {
                font-size: 15.5px;
            }

            .content-wrap {
                padding: 16px 14px 36px;
                gap: 16px;
            }

            .section-header,
            .section-body {
                padding: 14px 16px;
            }
        }

        /* SAMPLE TOUR MODE BANNER */
        .sample-tour-banner {
            background: linear-gradient(135deg, var(--plum) 0%, #23283B 100%);
            color: #FFFFFF;
            border-radius: var(--radius-lg);
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            flex-wrap: wrap;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(255, 255, 255, 0.12);
            position: relative;
        }

        .sample-tour-content {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .sample-tour-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        .sample-tour-badges {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            min-width: 0;
        }

        .sample-tour-actions {
            display: flex;
            align-items: center;
            gap: 9px;
            flex-wrap: wrap;
            flex-shrink: 0;
        }

        .btn-hide-tour-banner {
            background-color: rgba(255, 255, 255, 0.1);
            color: #E5E7EF;
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 8px;
            padding: 6px 11px;
            font-size: 11.5px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            transition: all 0.15s ease;
            white-space: nowrap;
        }

        .btn-hide-tour-banner:hover {
            background-color: rgba(230, 109, 85, 0.25);
            border-color: rgba(230, 109, 85, 0.5);
            color: #FFFFFF;
        }

        .sample-tour-hide-mobile {
            display: none;
        }

        @media (max-width: 720px) {
            .sample-tour-banner {
                flex-direction: column;
                align-items: stretch;
                padding: 15px 15px;
                gap: 13px;
            }

            .sample-tour-header-row {
                flex-wrap: nowrap;
                align-items: flex-start;
            }

            .sample-tour-badges {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }

            .sample-tour-hide-mobile {
                display: inline-flex;
                padding: 5px 10px;
                font-size: 11px;
                flex-shrink: 0;
            }

            .sample-tour-hide-desktop {
                display: none;
            }

            .sample-tour-actions {
                width: 100%;
                display: grid;
                grid-template-columns: 1fr;
                gap: 8px;
            }

            .sample-tour-actions .btn {
                width: 100%;
                justify-content: center;
                padding: 10px 12px !important;
                font-size: 12.5px;
            }
        }

        @media (max-width: 580px) {
            .kpi-grid {
                grid-template-columns: 1fr !important;
                gap: 12px;
            }

            .page-kicker {
                display: none;
            }

            .topbar-right {
                width: 100%;
                justify-content: space-between;
            }

            .topbar-right .btn {
                flex: 1;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group[style*="grid-column: span 2"] {
                grid-column: span 1 !important;
            }
        }
    </style>
</head>
<body>
<div class="app-shell" id="appShell">
    <!-- MOBILE BACKDROP OVERLAY -->
    <div class="sidebar-backdrop" onclick="closeMobileSidebar()"></div>

    <!-- NAVIGATION SIDEBAR -->
    <aside class="sidebar" id="appSidebar">
        <!-- FIXED ZONE 1: BRAND & COLLAPSE BUTTON -->
        <div class="brand-box">
            <a href="{{ route('dashboard') }}" class="brand-logo" title="CediLedger — Financial Intelligence">
                <span class="brand-mark">GH¢</span>
                <div class="brand-text">
                    <div class="brand-title">CediLedger</div>
                    <div class="brand-sub">Financial Intelligence</div>
                </div>
            </a>
            <button type="button" class="sidebar-toggle-btn" onclick="toggleSidebar()" title="Collapse / Expand Sidebar ( [ )">
                <svg class="sidebar-toggle-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="transition: transform 0.2s ease;">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
        </div>

        <!-- FIXED ZONE 2: QUICK RECORD CTA -->
        <div class="sidebar-quick-action">
            <button type="button" class="btn-quick-add" onclick="openQuickModal('expense')" title="Record Transaction (Shortcut: N)">
                <span class="quick-add-label">+ Record Transaction</span>
                <span class="quick-add-plus">+</span>
                <span class="kbd-hint">N</span>
            </button>
        </div>

        <!-- SCROLLABLE ZONE 3: ORGANIZED NAVIGATION GROUPS -->
        <div class="sidebar-nav-scroll" id="sidebarNavScroll">
            <div class="nav-section-card">
                <div class="nav-group-label">Overview & Ledger</div>
                <ul class="nav-list">
                    <li>
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" title="Dashboard">
                            <span class="nav-link-inner">
                                <span class="nav-icon" style="color: #2D9F78;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>
                                </span>
                                <span class="nav-label">Dashboard</span>
                            </span>
                            <span class="nav-badge" style="background-color: rgba(45, 159, 120, 0.2); color: #5CE0B5;">LIVE</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('tracking.index') }}" class="nav-link {{ request()->routeIs('tracking.index') ? 'active' : '' }}" title="Period Explorer (D/W/M/Y)">
                            <span class="nav-link-inner">
                                <span class="nav-icon" style="color: #E5A93C;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                </span>
                                <span class="nav-label">Period Explorer</span>
                            </span>
                            <span class="nav-badge" style="background-color: rgba(229, 169, 60, 0.18); color: #F5C469;">D/W/M/Y</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('transactions.index') }}" class="nav-link {{ request()->routeIs('transactions.index') ? 'active' : '' }}" title="All Transactions">
                            <span class="nav-link-inner">
                                <span class="nav-icon" style="color: #4E73DF;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                                </span>
                                <span class="nav-label">All Transactions</span>
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('expenses.index') }}" class="nav-link {{ request()->routeIs('expenses.index') ? 'active' : '' }}" title="Expenses & Receipts">
                            <span class="nav-link-inner">
                                <span class="nav-icon" style="color: #E66D55;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="8 12 12 16 16 12"/><line x1="12" y1="8" x2="12" y2="16"/></svg>
                                </span>
                                <span class="nav-label">Expenses & Receipts</span>
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('income.index') }}" class="nav-link {{ request()->routeIs('income.index') ? 'active' : '' }}" title="Income Records">
                            <span class="nav-link-inner">
                                <span class="nav-icon" style="color: #2D9F78;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="16 12 12 8 8 12"/><line x1="12" y1="16" x2="12" y2="8"/></svg>
                                </span>
                                <span class="nav-label">Income Records</span>
                            </span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section-card">
                <div class="nav-group-label">Planning & Control</div>
                @php
                    $sidebarCategoriesCount = isset($categories)
                        ? $categories->count()
                        : \App\Models\ExpenseCategory::where('user_id', $user->id ?? auth()->id())->count();
                @endphp
                <ul class="nav-list">
                    <li>
                        <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" title="Categories ({{ $sidebarCategoriesCount }})">
                            <span class="nav-link-inner">
                                <span class="nav-icon" style="color: #D96D9B;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                                </span>
                                <span class="nav-label">Categories</span>
                            </span>
                            <span class="nav-badge" style="background-color: rgba(255, 255, 255, 0.08); color: #D5D8E6;">{{ $sidebarCategoriesCount }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('budgets.index') }}" class="nav-link {{ request()->routeIs('budgets.*') ? 'active' : '' }}" title="Budgets & Limits">
                            <span class="nav-link-inner">
                                <span class="nav-icon" style="color: #E5A93C;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-4"/></svg>
                                </span>
                                <span class="nav-label">Budgets & Limits</span>
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('recurring.index') }}" class="nav-link {{ request()->routeIs('recurring.*') ? 'active' : '' }}" title="Recurring Bills">
                            <span class="nav-link-inner">
                                <span class="nav-icon" style="color: #4E73DF;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                                </span>
                                <span class="nav-label">Recurring Bills</span>
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('savings.index') }}" class="nav-link {{ request()->routeIs('savings.*') ? 'active' : '' }}" title="Savings & Goals">
                            <span class="nav-link-inner">
                                <span class="nav-icon" style="color: #E5A93C;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 5c-1.5 0-2.8 1.4-3 2-3.5-1.5-11-.3-11 5 0 1.8 0 3 2 4.5V20h4v-2h3v2h4v-4c1-.5 1.7-1 2-2h2v-4h-2c0-1-.5-1.5-1-2h0V5z"/></svg>
                                </span>
                                <span class="nav-label">Savings & Goals</span>
                            </span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section-card" style="margin-bottom: 0;">
                <div class="nav-group-label">Intelligence & System</div>
                <ul class="nav-list">
                    <li>
                        <a href="{{ route('insights.index') }}" class="nav-link {{ request()->routeIs('insights.*') ? 'active' : '' }}" title="Financial Insights">
                            <span class="nav-link-inner">
                                <span class="nav-icon" style="color: #E66D55;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                                </span>
                                <span class="nav-label">Financial Insights</span>
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" title="Reports & Exports">
                            <span class="nav-link-inner">
                                <span class="nav-icon" style="color: #2D9F78;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                </span>
                                <span class="nav-label">Reports & Exports</span>
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('profile.index') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" title="Profile & 2FA Security">
                            <span class="nav-link-inner">
                                <span class="nav-icon" style="color: #5CE0B5;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                </span>
                                <span class="nav-label">Profile & 2FA</span>
                            </span>
                            @if(($user->two_factor_enabled ?? false))
                                <span class="nav-badge" style="background-color: rgba(45, 159, 120, 0.2); color: #5CE0B5;">2FA</span>
                            @endif
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" title="Settings">
                            <span class="nav-link-inner">
                                <span class="nav-icon" style="color: #A3A7BA;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                                </span>
                                <span class="nav-label">Settings</span>
                            </span>
                        </a>
                    </li>
                    @if(isset($user) && $user->isAdmin())
                        @php
                            $pendingUsersBadgeCount = \App\Models\User::where('approval_status', 'pending')->count();
                        @endphp
                        <li>
                            <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" title="SaaS User Approvals">
                                <span class="nav-link-inner">
                                    <span class="nav-icon" style="color: #F5C469;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                    </span>
                                    <span class="nav-label">User Approvals</span>
                                </span>
                                @if($pendingUsersBadgeCount > 0)
                                    <span class="nav-badge" style="background-color: #E66D55; color: #FFFFFF;">{{ $pendingUsersBadgeCount }}</span>
                                @endif
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        <!-- FIXED ZONE 4: USER PROFILE & LOGOUT -->
        <div class="sidebar-footer">
            <a href="{{ route('profile.index') }}" class="sidebar-user-info" title="View Profile & 2FA Security ({{ $user->name ?? 'Selase Jesse' }})">
                <div class="user-avatar">{{ substr($user->name ?? 'SJ', 0, 1) }}</div>
                <div class="user-meta-text">
                    <div class="user-meta-name">{{ $user->name ?? 'Selase Jesse' }}</div>
                    <div class="user-meta-role">Currency: {{ $user->currency ?? 'GH¢' }}</div>
                </div>
            </a>
            <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="btn-logout-icon" title="Sign Out">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                </button>
            </form>
        </div>
    </aside>

    <!-- MAIN WORKSPACE -->
    <div class="main-workspace">
        @php
            $currentAuthId = $user->id ?? auth()->id();
            $modalCategories = $categories ?? \App\Models\ExpenseCategory::with('subcategories')->where('user_id', $currentAuthId)->orderBy('sort_order')->get();
            $modalPaymentMethods = $paymentMethods ?? \App\Models\PaymentMethod::where('user_id', $currentAuthId)->get();

            // Distinct previously recorded transaction templates (e.g., "Waakye") for 1-click reuse in the Record Transaction modal
            $reusableTxTemplates = isset($user)
                ? \App\Models\Transaction::with(['category', 'subcategory', 'paymentMethod'])
                    ->where('user_id', $currentAuthId)
                    ->orderByDesc('transaction_date')
                    ->orderByDesc('id')
                    ->limit(80)
                    ->get()
                    ->groupBy(fn($t) => strtolower(trim($t->title)) . '|' . $t->type)
                    ->map(function ($group) {
                        $latest = $group->first();
                        return [
                            'title' => $latest->title,
                            'type' => $latest->type,
                            'amount' => number_format((float) $latest->amount, 2, '.', ''),
                            'expense_category_id' => $latest->expense_category_id,
                            'category_name' => $latest->category?->name ?? 'General',
                            'expense_subcategory_id' => $latest->expense_subcategory_id,
                            'subcategory_name' => $latest->subcategory?->name ?? '',
                            'payment_method_id' => $latest->payment_method_id,
                            'merchant_or_source' => $latest->merchant_or_source ?? '',
                            'count' => $group->count(),
                        ];
                    })
                    ->values()
                : collect();

            // Chronological Fresh-Start Setup Progress Metrics
            $setupCatCount = $modalCategories->count();
            $setupPmCount = $modalPaymentMethods->count();
            $setupHasBudget = isset($user) && ((float) $user->overall_monthly_budget > 0 || \App\Models\Budget::where('user_id', $currentAuthId)->exists());
            $setupTxCount = isset($user) ? \App\Models\Transaction::where('user_id', $currentAuthId)->count() : 0;
            $setupGoalsOrRecCount = isset($user)
                ? (\App\Models\RecurringTransaction::where('user_id', $currentAuthId)->count() + \App\Models\FinancialGoal::where('user_id', $currentAuthId)->count())
                : 0;

            $setupSteps = [
                [
                    'num' => 1,
                    'title' => 'Create Expense & Income Categories',
                    'desc' => 'Define your spending & income categories (and optional subcategories) first so every transaction can be classified.',
                    'done' => $setupCatCount > 0,
                    'status_text' => $setupCatCount > 0 ? "{$setupCatCount} configured" : 'Required First',
                    'url' => route('categories.index'),
                    'cta' => '1. Setup Categories →',
                ],
                [
                    'num' => 2,
                    'title' => 'Add Your Payment Channels',
                    'desc' => 'Configure your Mobile Money wallets, Bank Accounts, Debit/Credit Cards, or Cash in Hand.',
                    'done' => $setupPmCount > 0,
                    'status_text' => $setupPmCount > 0 ? "{$setupPmCount} added" : 'Required Second',
                    'url' => route('settings.index'),
                    'cta' => '2. Add Payment Methods →',
                ],
                [
                    'num' => 3,
                    'title' => 'Set Monthly, Weekly & Category Budgets',
                    'desc' => 'Establish your overall period spending ceilings and category budget limits before recording expenses.',
                    'done' => $setupHasBudget,
                    'status_text' => $setupHasBudget ? 'Targets set' : 'Recommended Third',
                    'url' => route('budgets.index'),
                    'cta' => '3. Configure Budgets →',
                ],
                [
                    'num' => 4,
                    'title' => 'Record Your First Income & Expense',
                    'desc' => 'Log your real income inflows and daily expenses to activate live analytics and period drill-downs.',
                    'done' => $setupTxCount > 0,
                    'status_text' => $setupTxCount > 0 ? "{$setupTxCount} recorded" : 'Step 4',
                    'url' => route('transactions.index'),
                    'cta' => '4. Record Transactions →',
                ],
                [
                    'num' => 5,
                    'title' => 'Schedule Recurring Bills & Savings Goals',
                    'desc' => 'Set up upcoming bill reminders and dedicated savings vaults to track capital accumulation.',
                    'done' => $setupGoalsOrRecCount > 0,
                    'status_text' => $setupGoalsOrRecCount > 0 ? "{$setupGoalsOrRecCount} active" : 'Step 5',
                    'url' => route('savings.index'),
                    'cta' => '5. Setup Goals & Bills →',
                ],
            ];

            $completedSetupCount = collect($setupSteps)->where('done', true)->count();
            $nextPendingStep = collect($setupSteps)->firstWhere('done', false);
            $showFreshStartGuide = !($user->has_sample_data ?? false) && $completedSetupCount < 5;

            // Synchronize any existing pending SaaS users into Admin Notifications if not cleared
            if (isset($user) && $user->isAdmin() && !session('notifications_cleared_by_user_' . $user->id)) {
                $pendingSaaSUsers = \App\Models\User::where('approval_status', 'pending')->get();
                foreach ($pendingSaaSUsers as $pUser) {
                    $alreadyNotified = \App\Models\AppNotification::where('user_id', $user->id)
                        ->where('type', 'new_user')
                        ->where('message', 'like', '%' . $pUser->email . '%')
                        ->exists();

                    if (!$alreadyNotified) {
                        \App\Models\AppNotification::create([
                            'user_id' => $user->id,
                            'title' => "New User Registration: {$pUser->name}",
                            'message' => "{$pUser->name} ({$pUser->email}) registered a new SaaS account ({$pUser->currency}) and is awaiting your approval.",
                            'type' => 'new_user',
                            'severity' => 'warning',
                            'action_url' => '/admin/users',
                            'is_read' => false,
                            'read_at' => null,
                        ]);
                    }
                }
            }

            // Synchronize Recurring Bill Reminders into Header Notification Bell when within the reminder_days_before window
            if (isset($user) && !session('notifications_cleared_by_user_' . $user->id)) {
                $activeRecurringBills = \App\Models\RecurringTransaction::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->get();
                $todayStart = now()->startOfDay();

                foreach ($activeRecurringBills as $recBill) {
                    $dueCarbon = $recBill->next_due_date->copy()->startOfDay();
                    $daysUntilDue = (int) $todayStart->diffInDays($dueCarbon, false);
                    $leadDays = (int) ($recBill->reminder_days_before ?? 3);

                    if ($daysUntilDue <= $leadDays) {
                        $dueKeyStr = $dueCarbon->format('d M Y');
                        $alreadyReminded = \App\Models\AppNotification::where('user_id', $user->id)
                            ->where('type', 'recurring_reminder')
                            ->where('title', 'like', '%' . $recBill->title . '%')
                            ->where('message', 'like', '%Due ' . $dueKeyStr . '%')
                            ->exists();

                        if (!$alreadyReminded) {
                            $urgencyPrefix = $daysUntilDue < 0
                                ? 'Overdue by ' . abs($daysUntilDue) . 'd'
                                : ($daysUntilDue === 0 ? 'Due Today' : "Due in {$daysUntilDue}d");
                            \App\Models\AppNotification::create([
                                'user_id' => $user->id,
                                'title' => "Recurring Reminder: {$recBill->title} ({$urgencyPrefix})",
                                'message' => "Scheduled {$recBill->frequency} {$recBill->type} of {$user->currency}" . number_format((float) $recBill->amount, 2) . " • Due {$dueKeyStr} (Reminder {$leadDays}d before).",
                                'type' => 'recurring_reminder',
                                'severity' => $daysUntilDue <= 0 ? 'danger' : 'warning',
                                'action_url' => '/recurring',
                                'is_read' => false,
                                'read_at' => null,
                            ]);
                        }
                    }
                }
            }

            $allBellNotifications = isset($user)
                ? \App\Models\AppNotification::where('user_id', $user->id)
                    ->orderByDesc('created_at')
                    ->orderByDesc('id')
                    ->get()
                : collect();

            $unreadBellNotifications = $allBellNotifications->where('is_read', false)->values();
            $readBellNotifications = $allBellNotifications->where('is_read', true)->values();
            $unreadBellCount = $unreadBellNotifications->count();
            $readBellCount = $readBellNotifications->count();
            $totalBellCount = $allBellNotifications->count();
            $activeNotifTab = session('open_notifications_tab', $unreadBellCount > 0 ? 'unread' : ($readBellCount > 0 ? 'read' : 'unread'));
        @endphp

        <header class="topbar">
            <div class="topbar-left">
                <button type="button" class="topbar-sidebar-toggle" onclick="toggleSidebar()" title="Toggle Navigation Menu">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
                <span class="page-kicker">@yield('kicker', 'OVERVIEW')</span>
                <h1 class="topbar-title">@yield('page_title', 'Executive Financial Overview')</h1>
            </div>
            <div class="topbar-right">
                @if(($user->has_sample_data ?? false))
                    <button type="button"
                            id="restoreSampleTourBannerBtn"
                            class="btn btn-plum btn-sm"
                            style="display: none;"
                            onclick="showSampleTourBanner()"
                            title="Show Sample Tour Mode & Database Reset Banner">
                        ✦ Tour & Reset
                    </button>
                @else
                    <button type="button" class="btn btn-emerald btn-sm" onclick="openSetupRoadmapModal()" title="View Chronological Fresh-Start Setup Guide">
                        🧭 Setup Guide ({{ $completedSetupCount }}/5)
                    </button>
                    <button type="button" class="btn btn-plum btn-sm" onclick="openGuidedTourModal(0)" title="Launch Interactive Platform Tour">
                        ✦ Guided Tour
                    </button>
                @endif
                <button type="button" class="btn btn-coral btn-sm" onclick="openQuickModal('expense')">+ Expense</button>
                <button type="button" class="btn btn-emerald btn-sm" onclick="openQuickModal('income')">+ Income</button>
                <a href="{{ route('reports.export', ['format' => 'csv', 'start_date' => now()->startOfMonth()->toDateString(), 'end_date' => now()->endOfMonth()->toDateString()]) }}" class="btn btn-ivory btn-sm">Export CSV</a>

                <!-- HEADER NOTIFICATION BELL (ADMIN & USER NOTIFICATIONS WITH UNREAD / READ TABS & CLEAR ALL) -->
                <div class="notif-bell-wrapper" id="notifBellWrapper">
                    <button type="button"
                            class="btn-notif-bell {{ session()->has('open_notifications_tab') ? 'active' : '' }}"
                            id="notifBellBtn"
                            onclick="toggleNotifDropdown(event)"
                            aria-label="Notifications"
                            title="Notifications ({{ $unreadBellCount }} unread, {{ $readBellCount }} read)">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        @if($unreadBellCount > 0)
                            <span class="notif-bell-badge" id="notifUnreadBadge">{{ $unreadBellCount }}</span>
                        @endif
                    </button>

                    <div class="notif-dropdown-panel {{ session()->has('open_notifications_tab') ? 'open' : '' }}" id="notifDropdownPanel" onclick="event.stopPropagation()">
                        <div class="notif-panel-header">
                            <div>
                                <div style="display: flex; align-items: center; gap: 7px;">
                                    <strong style="font-size: 13.5px; letter-spacing: -0.01em;">Notifications Center</strong>
                                    <span class="mono" style="font-size: 10px; font-weight: 800; padding: 2px 7px; border-radius: 9999px; background-color: {{ $unreadBellCount > 0 ? 'var(--coral)' : 'rgba(255,255,255,0.14)' }}; color: #FFFFFF;">
                                        {{ $unreadBellCount }} Unread
                                    </span>
                                </div>
                                <div style="font-size: 11px; color: #B4B8CA; margin-top: 2px;">
                                    @if(isset($user) && $user->isAdmin())
                                        Admin User Registrations & System Alerts
                                    @else
                                        Account & Financial Alerts
                                    @endif
                                </div>
                            </div>

                            <div style="display: flex; align-items: center; gap: 6px;">
                                @if($unreadBellCount > 0)
                                    <form action="{{ route('notifications.mark-all-read') }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <button type="submit" class="btn btn-emerald btn-sm" style="padding: 4px 8px; font-size: 10.5px;" title="Mark all unread notifications as read">
                                            ✓ Mark All Read
                                        </button>
                                    </form>
                                @endif
                                @if($totalBellCount > 0)
                                    <form action="{{ route('notifications.clear-all') }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <input type="hidden" name="scope" value="all">
                                        <button type="submit" class="btn btn-coral btn-sm" style="padding: 4px 8px; font-size: 10.5px;" title="Clear all notifications">
                                            🗑 Clear All
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <!-- SEPARATE UNREAD FROM READ TABS -->
                        <div class="notif-tabs-bar">
                            <button type="button"
                                    id="notifTabBtnUnread"
                                    class="notif-tab-btn {{ $activeNotifTab === 'unread' ? 'active' : '' }}"
                                    onclick="switchNotifTab('unread')">
                                <span>Unread</span>
                                <span class="mono" style="font-size: 10.5px; padding: 1px 6px; border-radius: 9999px; background-color: {{ $unreadBellCount > 0 ? 'rgba(230, 109, 85, 0.16)' : 'rgba(100, 106, 122, 0.14)' }}; color: {{ $unreadBellCount > 0 ? 'var(--coral-dark)' : 'var(--text-muted)' }};">
                                    {{ $unreadBellCount }}
                                </span>
                            </button>
                            <button type="button"
                                    id="notifTabBtnRead"
                                    class="notif-tab-btn {{ $activeNotifTab === 'read' ? 'active' : '' }}"
                                    onclick="switchNotifTab('read')">
                                <span>Read</span>
                                <span class="mono" style="font-size: 10.5px; padding: 1px 6px; border-radius: 9999px; background-color: rgba(45, 159, 120, 0.15); color: var(--emerald-dark);">
                                    {{ $readBellCount }}
                                </span>
                            </button>
                        </div>

                        <!-- TAB 1: UNREAD NOTIFICATIONS -->
                        <div id="notifPaneUnread" class="notif-list-scroll" style="display: {{ $activeNotifTab === 'unread' ? 'flex' : 'none' }};">
                            @forelse($unreadBellNotifications as $notif)
                                <div class="notif-item-card unread">
                                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                                        <span class="mono" style="font-size: 10px; font-weight: 800; text-transform: uppercase; padding: 2px 7px; border-radius: 9999px; background-color: {{ $notif->type === 'new_user' ? 'rgba(229, 169, 60, 0.22)' : 'rgba(230, 109, 85, 0.16)' }}; color: {{ $notif->type === 'new_user' ? '#966611' : 'var(--coral-dark)' }};">
                                            {{ $notif->type === 'new_user' ? '👤 New User Approval' : str_replace('_', ' ', $notif->type) }}
                                        </span>
                                        <span class="mono" style="font-size: 10.5px; color: var(--text-muted);">
                                            {{ $notif->created_at ? $notif->created_at->diffForHumans() : 'Just now' }}
                                        </span>
                                    </div>

                                    <div style="font-size: 13px; font-weight: 700; color: var(--text-dark); line-height: 1.35;">
                                        {{ $notif->title }}
                                    </div>
                                    <div style="font-size: 12px; color: var(--text-muted); line-height: 1.45;">
                                        {{ $notif->message }}
                                    </div>

                                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: 4px; flex-wrap: wrap;">
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            @if($notif->action_url)
                                                <a href="{{ route('notifications.visit', $notif->id) }}" class="btn btn-plum btn-sm" style="padding: 4px 10px; font-size: 11px;">
                                                    {{ $notif->type === 'new_user' ? 'Review & Approve →' : 'Open →' }}
                                                </a>
                                            @endif
                                            <form action="{{ route('notifications.toggle-read', $notif->id) }}" method="POST" style="margin: 0;">
                                                @csrf
                                                <button type="submit" class="btn btn-ivory btn-sm" style="padding: 4px 9px; font-size: 11px;" title="Move to Read notifications">
                                                    ✓ Mark Read
                                                </button>
                                            </form>
                                        </div>

                                        <form action="{{ route('notifications.destroy', $notif->id) }}" method="POST" style="margin: 0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 12px; padding: 3px 6px;" title="Remove notification">
                                                ✕
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div style="padding: 32px 20px; text-align: center; color: var(--text-muted); font-size: 12.5px;">
                                    <div style="font-size: 22px; margin-bottom: 6px;">🔔</div>
                                    <strong style="color: var(--text-dark); display: block; margin-bottom: 3px;">No Unread Notifications</strong>
                                    You're all caught up! New user signups and alerts will appear here.
                                </div>
                            @endforelse
                        </div>

                        <!-- TAB 2: READ NOTIFICATIONS -->
                        <div id="notifPaneRead" class="notif-list-scroll" style="display: {{ $activeNotifTab === 'read' ? 'flex' : 'none' }};">
                            @if($readBellCount > 0)
                                <div style="padding: 8px 14px; background-color: var(--ivory-alt); border-bottom: 1px solid var(--ivory-border); display: flex; align-items: center; justify-content: space-between; font-size: 11px; color: var(--text-muted);">
                                    <span>Read Notifications ({{ $readBellCount }})</span>
                                    <form action="{{ route('notifications.clear-all') }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <input type="hidden" name="scope" value="read">
                                        <button type="submit" style="background: none; border: none; color: var(--coral-dark); font-weight: 700; font-size: 11px; cursor: pointer; text-decoration: underline;">
                                            Clear Read Only
                                        </button>
                                    </form>
                                </div>
                            @endif

                            @forelse($readBellNotifications as $notif)
                                <div class="notif-item-card read">
                                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                                        <span class="mono" style="font-size: 10px; font-weight: 700; text-transform: uppercase; padding: 2px 7px; border-radius: 9999px; background-color: var(--ivory); color: var(--text-muted);">
                                            ✓ Read • {{ $notif->type === 'new_user' ? 'New User' : str_replace('_', ' ', $notif->type) }}
                                        </span>
                                        <span class="mono" style="font-size: 10.5px; color: var(--text-muted);">
                                            {{ $notif->read_at ? 'Read ' . $notif->read_at->diffForHumans() : ($notif->created_at ? $notif->created_at->diffForHumans() : '') }}
                                        </span>
                                    </div>

                                    <div style="font-size: 12.5px; font-weight: 700; color: var(--text-dark); line-height: 1.35;">
                                        {{ $notif->title }}
                                    </div>
                                    <div style="font-size: 12px; color: var(--text-muted); line-height: 1.45;">
                                        {{ $notif->message }}
                                    </div>

                                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: 4px; flex-wrap: wrap;">
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            @if($notif->action_url)
                                                <a href="{{ $notif->action_url }}" class="btn btn-ivory btn-sm" style="padding: 3px 9px; font-size: 11px;">
                                                    View Page →
                                                </a>
                                            @endif
                                            <form action="{{ route('notifications.toggle-read', $notif->id) }}" method="POST" style="margin: 0;">
                                                @csrf
                                                <button type="submit" class="btn btn-ivory btn-sm" style="padding: 3px 9px; font-size: 11px;" title="Move back to Unread notifications">
                                                    ↺ Mark Unread
                                                </button>
                                            </form>
                                        </div>

                                        <form action="{{ route('notifications.destroy', $notif->id) }}" method="POST" style="margin: 0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 12px; padding: 3px 6px;" title="Remove notification">
                                                ✕
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div style="padding: 32px 20px; text-align: center; color: var(--text-muted); font-size: 12.5px;">
                                    <div style="font-size: 22px; margin-bottom: 6px;">✓</div>
                                    <strong style="color: var(--text-dark); display: block; margin-bottom: 3px;">No Read Notifications</strong>
                                    Notifications you mark as read or review will be archived here.
                                </div>
                            @endforelse
                        </div>

                        @if(isset($user) && $user->isAdmin())
                            <div style="padding: 10px 16px; background-color: var(--ivory); border-top: 1px solid var(--ivory-border); display: flex; align-items: center; justify-content: space-between; font-size: 11.5px;">
                                <span style="color: var(--text-muted);">SaaS User Approvals Console</span>
                                <a href="{{ route('admin.users.index') }}" style="color: var(--plum); font-weight: 700; text-decoration: underline;">
                                    Manage All Users →
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </header>

        <main class="content-wrap">
            @if(($user->has_sample_data ?? false))
                <section id="sampleTourBannerSection" class="sample-tour-banner">
                    <div class="sample-tour-content">
                        <div class="sample-tour-header-row">
                            <div class="sample-tour-badges">
                                <span class="mono" style="display: inline-flex; align-items: center; gap: 5px; white-space: nowrap; flex-shrink: 0; background-color: var(--mustard); color: #14161F; font-size: 10.5px; font-weight: 800; padding: 3px 10px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.04em;">
                                    ✦ Sample Tour Mode
                                </span>
                                <span style="font-size: 11.5px; font-weight: 700; color: #5CE0B5; letter-spacing: 0.02em; line-height: 1.35;">
                                    Pre-Loaded Demo Workspace • Personal Profile Protected
                                </span>
                            </div>
                            <button type="button"
                                    class="btn-hide-tour-banner sample-tour-hide-mobile"
                                    onclick="hideSampleTourBanner()"
                                    title="Hide this Sample Tour Mode card">
                                ✕ Hide
                            </button>
                        </div>
                        <div style="font-size: 12.5px; color: #E5E7EF; line-height: 1.55;">
                            You are exploring CediLedger with pre-loaded sample data. Take the <strong style="color: #FFFFFF;">Guided Tour</strong> to see how every module works, then click <strong style="color: #F5C469;">Reset &amp; Start Fresh</strong> to wipe the sample database and follow the chronological setup guide with your profile details intact.
                        </div>
                    </div>
                    <div class="sample-tour-actions">
                        <button type="button" class="btn btn-emerald btn-sm" style="padding: 8px 14px;" onclick="openGuidedTourModal(0)">
                            ✦ Start Guided Tour
                        </button>
                        <button type="button" class="btn btn-mustard btn-sm" style="padding: 8px 14px;" onclick="openResetWorkspaceModal()">
                            ↻ Reset Database &amp; Start Fresh
                        </button>
                        <button type="button"
                                class="btn-hide-tour-banner sample-tour-hide-desktop"
                                onclick="hideSampleTourBanner()"
                                title="Hide this Sample Tour Mode banner">
                            ✕ Hide
                        </button>
                    </div>
                </section>
            @endif

            @if($showFreshStartGuide)
                <!-- CHRONOLOGICAL FRESH-START NAVIGATING GUIDE BANNER -->
                <section id="freshStartGuideBannerSection" class="section-block theme-ivory" style="border: 1.5px solid var(--emerald); background-color: #FFFFFF;">
                    <div class="section-header" style="background-color: #F4FBF8; border-bottom-color: rgba(45, 159, 120, 0.2);">
                        <div>
                            <div style="font-size: 10.5px; color: var(--emerald-dark); text-transform: uppercase; font-weight: 800; letter-spacing: 0.07em;">
                                Fresh Start Navigating Guide • Chronological Setup Roadmap ({{ $completedSetupCount }} of 5 Completed)
                            </div>
                            <h2 class="section-title" style="font-size: 15px; margin-top: 2px;">
                                @if($nextPendingStep)
                                    Next Recommended Step: Step {{ $nextPendingStep['num'] }} — {{ $nextPendingStep['title'] }}
                                @else
                                    All Core Setup Steps Completed!
                                @endif
                            </h2>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            @if($nextPendingStep)
                                <a href="{{ $nextPendingStep['url'] }}" class="btn btn-emerald btn-sm">
                                    Go to {{ $nextPendingStep['cta'] }}
                                </a>
                            @endif
                            <button type="button" class="btn btn-ivory btn-sm" onclick="openSetupRoadmapModal()">
                                View Full Setup Guide
                            </button>
                            <button type="button" class="btn btn-ivory btn-sm" onclick="hideFreshStartGuideBanner()" title="Hide this Setup Guide banner">
                                ✕ Hide
                            </button>
                        </div>
                    </div>
                    <div class="section-body" style="padding: 14px 18px;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(195px, 1fr)); gap: 10px;">
                            @foreach($setupSteps as $sItem)
                                @php
                                    $isCurrentNext = $nextPendingStep && $nextPendingStep['num'] === $sItem['num'];
                                @endphp
                                <a href="{{ $sItem['url'] }}"
                                   style="display: flex; flex-direction: column; justify-content: space-between; gap: 8px; padding: 12px 14px; border-radius: 11px; background-color: {{ $sItem['done'] ? '#F4FBF8' : ($isCurrentNext ? '#FDF9F0' : 'var(--ivory-alt)') }}; border: 1.5px solid {{ $sItem['done'] ? 'rgba(45,159,120,0.35)' : ($isCurrentNext ? 'var(--mustard)' : 'var(--ivory-border)') }}; transition: transform 0.15s ease;">
                                    <div>
                                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px; margin-bottom: 5px;">
                                            <span class="mono" style="font-size: 10.5px; font-weight: 800; padding: 2px 7px; border-radius: 9999px; background-color: {{ $sItem['done'] ? 'var(--emerald)' : ($isCurrentNext ? 'var(--mustard)' : 'var(--plum)') }}; color: {{ $isCurrentNext && !$sItem['done'] ? '#14161F' : '#FFFFFF' }};">
                                                @if($sItem['done']) ✓ Step {{ $sItem['num'] }} @else Step {{ $sItem['num'] }} @endif
                                            </span>
                                            <span class="mono" style="font-size: 10px; font-weight: 700; color: {{ $sItem['done'] ? 'var(--emerald-dark)' : ($isCurrentNext ? 'var(--mustard-dark)' : 'var(--text-muted)') }};">
                                                {{ $sItem['status_text'] }}
                                            </span>
                                        </div>
                                        <div style="font-weight: 700; font-size: 12.5px; color: var(--text-dark); line-height: 1.3;">
                                            {{ $sItem['title'] }}
                                        </div>
                                    </div>
                                    <div style="font-size: 11px; font-weight: 700; color: {{ $sItem['done'] ? 'var(--emerald-dark)' : 'var(--plum)' }};">
                                        {{ $sItem['done'] ? 'Review / Edit →' : 'Begin This Step →' }}
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
            @if(session('success'))
                <div class="flash-banner">
                    <span>✓ {{ session('success') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:#FFF;cursor:pointer;font-weight:700;">✕</button>
                </div>
            @endif

            @if($errors->any())
                <div class="flash-banner error">
                    <div>
                        <strong>Please check your input:</strong>
                        <ul style="margin-left: 18px; margin-top: 4px;">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<!-- GLOBAL QUICK-ADD TRANSACTION MODAL (ACCESSIBLE FROM ANY PAGE OR KEYBOARD 'N') -->
<div id="quickTransactionModal" class="modal-backdrop">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <div style="font-size: 10.5px; color: #E5A93C; text-transform: uppercase; font-weight: 700;">Unified Transaction Engine</div>
                <h3 id="quickModalTitle" style="font-size: 16px;">Record Financial Transaction</h3>
            </div>
            <button type="button" class="btn btn-coral btn-sm" onclick="closeQuickModal()">✕ Close</button>
        </div>
        <div class="modal-body">
            @if($modalCategories->isEmpty() || $modalPaymentMethods->isEmpty())
                <div style="margin-bottom: 16px; padding: 14px 16px; background-color: #FDF9F0; border: 1.5px solid var(--mustard); border-radius: 12px; font-size: 12.5px;">
                    <strong style="color: var(--text-dark); display: block; margin-bottom: 4px;">
                        🧭 Prerequisite Setup Needed Before Recording Transactions:
                    </strong>
                    <div style="color: var(--text-muted); margin-bottom: 10px;">
                        @if($modalCategories->isEmpty() && $modalPaymentMethods->isEmpty())
                            You have not created any <strong>Categories</strong> (Step 1) or <strong>Payment Methods</strong> (Step 2) yet. Please set them up first so you can classify your transactions:
                        @elseif($modalCategories->isEmpty())
                            Please create at least one <strong>Expense or Income Category</strong> (Step 1) before recording a transaction:
                        @else
                            Please add at least one <strong>Payment Method</strong> (Step 2) so you can select how this transaction was paid:
                        @endif
                    </div>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        @if($modalCategories->isEmpty())
                            <a href="{{ route('categories.index') }}" class="btn btn-emerald btn-sm">1. Create Categories First →</a>
                        @endif
                        @if($modalPaymentMethods->isEmpty())
                            <a href="{{ route('settings.index') }}" class="btn btn-plum btn-sm">2. Add Payment Methods →</a>
                        @endif
                    </div>
                </div>
            @endif
            <form action="{{ route('transactions.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <!-- ONE-CLICK EXPENSE vs INCOME MODE SWITCHER -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; background-color: var(--ivory); padding: 5px; border-radius: 11px; border: 1px solid var(--ivory-border); margin-bottom: 16px;">
                    <button type="button"
                            id="qmSwitchExpenseBtn"
                            onclick="setQuickModalType('expense')"
                            style="border: none; border-radius: 8px; padding: 9px 12px; font-family: inherit; font-size: 12.5px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.15s ease; background-color: var(--coral); color: #FFFFFF;">
                        <span>↓ Record New Expense Outflow</span>
                    </button>
                    <button type="button"
                            id="qmSwitchIncomeBtn"
                            onclick="setQuickModalType('income')"
                            style="border: none; border-radius: 8px; padding: 9px 12px; font-family: inherit; font-size: 12.5px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.15s ease; background-color: transparent; color: var(--text-muted);">
                        <span>↑ Record New Income Inflow</span>
                    </button>
                </div>

                <div id="qmMissingTypeCategoryAlert" style="display: none; margin-bottom: 14px; padding: 11px 14px; background-color: #FDF9F0; border: 1.5px solid var(--mustard); border-radius: 10px; font-size: 12px; color: var(--text-dark); align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;">
                    <span id="qmMissingTypeCategoryText">You do not have any category created for this transaction type yet.</span>
                    <a href="{{ route('categories.index') }}" class="btn btn-emerald btn-sm" style="padding: 4px 10px; font-size: 11px;">+ Create Category →</a>
                </div>

                <div class="form-grid" style="margin-bottom: 14px;">
                    <div class="form-group">
                        <label class="form-label">Transaction Type</label>
                        <select name="type" id="qm_type" class="form-control" onchange="filterModalCategories()" required>
                            <option value="expense">Expense (Outflow)</option>
                            <option value="income">Income (Inflow)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Amount ({{ $user->currency ?? 'GH¢' }})</label>
                        <input type="number" step="0.01" min="0.01" name="amount" id="qm_amount" class="form-control mono" placeholder="0.00" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Transaction Date</label>
                        <input type="date" name="transaction_date" id="qm_date" class="form-control mono" value="{{ now()->toDateString() }}" required>
                    </div>
                </div>

                @if($reusableTxTemplates->isNotEmpty())
                    <div id="qmReusableTemplatesBox" style="margin-bottom: 14px; padding: 10px 13px; background-color: var(--ivory-alt); border: 1px solid var(--ivory-border); border-radius: 11px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; margin-bottom: 7px;">
                            <span style="font-size: 11px; font-weight: 700; color: var(--text-dark); text-transform: uppercase; letter-spacing: 0.04em;">
                                ↻ Reuse Previously Recorded Item (Auto-Fills Fields)
                            </span>
                            <select id="qmTemplateSelect" class="form-control" style="padding: 4px 9px; font-size: 12px; width: auto; min-width: 220px;" onchange="applyReusableTxTemplate(this.value)">
                                <option value="">— Choose a saved item to reuse —</option>
                            </select>
                        </div>
                        <div id="qmTemplateChipsRow" style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;"></div>
                    </div>
                @endif

                <div class="form-grid" style="margin-bottom: 14px;">
                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label" id="qmTitleLabel">Expense / Income Title</label>
                        <input type="text" name="title" id="qmTitleInput" list="qmReusableTitlesDatalist" class="form-control" placeholder="e.g., Shell V-Power Fuel Refill or MaxMart Family Groceries" oninput="checkTitleAutocompleteMatch(this.value)" required>
                        <datalist id="qmReusableTitlesDatalist"></datalist>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Time</label>
                        <input type="time" name="transaction_time" class="form-control mono" value="{{ now()->format('H:i') }}">
                    </div>
                </div>

                <div class="form-grid" style="margin-bottom: 14px;">
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="expense_category_id" id="qm_category" class="form-control" onchange="populateModalSubcategories()" required>
                            @foreach($modalCategories as $cat)
                                <option value="{{ $cat->id }}" data-type="{{ $cat->type }}" data-subs='@json($cat->subcategories)'>
                                    [{{ strtoupper($cat->type) }}] {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subcategory</label>
                        <select name="expense_subcategory_id" id="qm_subcategory" class="form-control">
                            <option value="">— Optional Subcategory —</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method_id" id="qm_payment_method" class="form-control">
                            @foreach($modalPaymentMethods as $pm)
                                <option value="{{ $pm->id }}">{{ $pm->name }} ({{ $pm->provider }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-grid" style="margin-bottom: 14px;">
                    <div class="form-group">
                        <label class="form-label">Merchant / Source</label>
                        <input type="text" name="merchant_or_source" id="qm_merchant" class="form-control" placeholder="e.g., MaxMart 37, ECG, Client">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Receipt / Document (JPG, PNG, PDF)</label>
                        <input type="file" name="receipt" class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Recurring Schedule?</label>
                        <div style="display:flex;align-items:center;gap:8px;padding-top:4px;">
                            <input type="checkbox" name="is_recurring" value="1" id="qm_recurring" onchange="document.getElementById('qm_freq').style.display = this.checked ? 'block' : 'none'">
                            <label for="qm_recurring" style="font-size:12px;">Mark as recurring</label>
                            <select name="recurring_frequency" id="qm_freq" class="form-control" style="display:none;padding:4px 6px;">
                                <option value="weekly">Weekly</option>
                                <option value="monthly" selected>Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 18px;">
                    <label class="form-label">Notes / Context</label>
                    <textarea name="notes" rows="2" class="form-control" placeholder="Attach detailed notes or breakdown..."></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-ivory" onclick="closeQuickModal()">Cancel</button>
                    <button type="submit" class="btn btn-emerald">Save & Reconcile Ledger</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- INTERACTIVE GUIDED PLATFORM TOUR MODAL (6 STEPS + RESET PROMPT AT FINISH) -->
<div id="guidedTourModal" class="modal-backdrop" onclick="if(event.target === this) closeGuidedTourModal()">
    <div class="modal-box" style="max-width: 640px;">
        <div class="modal-header">
            <div>
                <div style="font-size: 10.5px; color: #5CE0B5; text-transform: uppercase; font-weight: 700; letter-spacing: 0.06em;">Interactive Onboarding Walkthrough</div>
                <h3 id="tourStepHeaderTitle" style="font-size: 17px;">Welcome to CediLedger Financial Intelligence</h3>
            </div>
            <button type="button" class="btn btn-coral btn-sm" onclick="closeGuidedTourModal()">✕ Close Tour</button>
        </div>
        <div class="modal-body" style="display: flex; flex-direction: column; gap: 18px;">
            <!-- Step Progress Dots -->
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px;">
                @for($stepIdx = 0; $stepIdx < 6; $stepIdx++)
                    <div id="tourProgressBar_{{ $stepIdx }}" onclick="renderGuidedTourStep({{ $stepIdx }})" style="flex: 1; height: 6px; border-radius: 9999px; background-color: var(--ivory-border); cursor: pointer; transition: background-color 0.2s ease;"></div>
                @endfor
            </div>

            <!-- Dynamic Step Body -->
            <div id="tourStepContentBox" style="background-color: var(--ivory-alt); border: 1px solid var(--ivory-border); border-radius: 14px; padding: 22px; min-height: 210px; display: flex; flex-direction: column; justify-content: space-between; gap: 14px;">
            </div>

            <!-- Navigation Footer -->
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;">
                <span class="mono" id="tourStepCounterLabel" style="font-size: 12px; color: var(--text-muted); font-weight: 600;">Step 1 of 6</span>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button type="button" class="btn btn-ivory" id="tourPrevBtn" onclick="prevGuidedTourStep()">← Previous</button>
                    <button type="button" class="btn btn-plum" id="tourNextBtn" onclick="nextGuidedTourStep()">Next Step →</button>
                    <button type="button" class="btn btn-mustard" id="tourResetFinishBtn" style="display: none;" onclick="closeGuidedTourModal(); openResetWorkspaceModal();">
                        ↻ Reset Database & Start Fresh Now
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- RESET DATABASE & START FRESH CONFIRMATION MODAL -->
<div id="resetWorkspaceModal" class="modal-backdrop" onclick="if(event.target === this) closeResetWorkspaceModal()">
    <div class="modal-box" style="max-width: 520px;">
        <div class="modal-header">
            <div>
                <div style="font-size: 10.5px; color: #F5C469; text-transform: uppercase; font-weight: 700; letter-spacing: 0.06em;">Fresh Ledger Initialization</div>
                <h3 style="font-size: 17px;">Reset Sample Database & Start Fresh</h3>
            </div>
            <button type="button" class="btn btn-coral btn-sm" onclick="closeResetWorkspaceModal()">✕ Close</button>
        </div>
        <div class="modal-body" style="display: flex; flex-direction: column; gap: 16px;">
            <p style="font-size: 13.5px; color: var(--text-dark); line-height: 1.55;">
                Ready to begin recording your own real financial transactions? Confirming below will wipe all pre-loaded sample transactions and reset your ledger to zero.
            </p>

            <div style="background-color: var(--ivory-alt); border: 1px solid var(--ivory-border); border-radius: 12px; padding: 14px 16px; font-size: 12.5px; display: flex; flex-direction: column; gap: 8px;">
                <div style="color: var(--coral-dark); font-weight: 700;">
                    ✕ What will be cleared:
                    <span style="font-weight: 400; color: var(--text-muted); display: block; margin-top: 2px;">
                        All sample expenses, income records, sample categories, subcategories, payment methods, savings deposits, sample goals, recurring bills, and budget allocations.
                    </span>
                </div>
                <div style="color: var(--emerald-dark); font-weight: 700; padding-top: 6px; border-top: 1px solid var(--ivory-border);">
                    ✓ What is preserved (Never Erased):
                    <span style="font-weight: 400; color: var(--text-muted); display: block; margin-top: 2px;">
                        Your personal profile details ({{ $user->name ?? 'Name' }}, {{ $user->email ?? 'Email' }}, phone, job title, location, bio, avatar, password, 2FA security, and default currency {{ $user->currency ?? 'GH¢' }}). After resetting, a chronological <strong>Setup Navigating Guide</strong> will walk you through creating your categories, payment methods, budgets, and transactions step-by-step.
                    </span>
                </div>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;">
                <form action="{{ route('workspace.tour.complete') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn btn-ivory">Keep Sample Data for Now</button>
                </form>

                <form action="{{ route('workspace.reset') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn btn-emerald">✓ Confirm Reset & Launch Setup Guide</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- CHRONOLOGICAL FRESH-START SETUP NAVIGATING GUIDE MODAL -->
<div id="setupRoadmapModal" class="modal-backdrop" onclick="if(event.target === this) closeSetupRoadmapModal()">
    <div class="modal-box" style="max-width: 660px;">
        <div class="modal-header">
            <div>
                <div style="font-size: 10.5px; color: #5CE0B5; text-transform: uppercase; font-weight: 700; letter-spacing: 0.06em;">Chronological Navigating Guide</div>
                <h3 style="font-size: 17px;">Where to Begin: Your 5-Step Fresh Ledger Setup Roadmap</h3>
            </div>
            <button type="button" class="btn btn-coral btn-sm" onclick="closeSetupRoadmapModal()">✕ Close</button>
        </div>
        <div class="modal-body" style="display: flex; flex-direction: column; gap: 14px;">
            <p style="font-size: 13px; color: var(--text-muted); line-height: 1.55;">
                Your workspace is clean and your personal profile details ({{ $user->name ?? 'Profile' }} • {{ $user->currency ?? 'GH¢' }}) are intact. Follow these <strong>5 chronological steps</strong> in order so all prerequisites are in place before recording transactions:
            </p>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                @foreach($setupSteps as $sStep)
                    @php
                        $isNextAction = $nextPendingStep && $nextPendingStep['num'] === $sStep['num'];
                    @endphp
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 13px 16px; border-radius: 12px; background-color: {{ $sStep['done'] ? '#F4FBF8' : ($isNextAction ? '#FDF9F0' : 'var(--ivory-alt)') }}; border: 1.5px solid {{ $sStep['done'] ? 'rgba(45,159,120,0.35)' : ($isNextAction ? 'var(--mustard)' : 'var(--ivory-border)') }}; flex-wrap: wrap;">
                        <div style="display: flex; align-items: flex-start; gap: 12px; flex: 1; min-width: 240px;">
                            <div class="mono" style="width: 30px; height: 30px; border-radius: 50%; background-color: {{ $sStep['done'] ? 'var(--emerald)' : ($isNextAction ? 'var(--mustard)' : 'var(--plum)') }}; color: {{ $isNextAction && !$sStep['done'] ? '#14161F' : '#FFFFFF' }}; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12.5px; flex-shrink: 0; margin-top: 2px;">
                                {{ $sStep['done'] ? '✓' : $sStep['num'] }}
                            </div>
                            <div>
                                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                    <strong style="font-size: 13.5px; color: var(--text-dark);">{{ $sStep['title'] }}</strong>
                                    <span class="mono" style="font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 9999px; background-color: #FFFFFF; border: 1px solid var(--ivory-border); color: {{ $sStep['done'] ? 'var(--emerald-dark)' : 'var(--text-muted)' }};">
                                        {{ $sStep['status_text'] }}
                                    </span>
                                </div>
                                <div style="font-size: 12px; color: var(--text-muted); margin-top: 3px; line-height: 1.45;">
                                    {{ $sStep['desc'] }}
                                </div>
                            </div>
                        </div>
                        <a href="{{ $sStep['url'] }}" class="btn {{ $sStep['done'] ? 'btn-ivory' : ($isNextAction ? 'btn-emerald' : 'btn-plum') }} btn-sm">
                            {{ $sStep['cta'] }}
                        </a>
                    </div>
                @endforeach
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 8px; border-top: 1px solid var(--ivory-border); flex-wrap: wrap; gap: 10px;">
                <span style="font-size: 12px; color: var(--text-muted);">
                    You can reopen this guide anytime via <strong>🧭 Setup Guide ({{ $completedSetupCount }}/5)</strong> in the top bar.
                </span>
                @if($nextPendingStep)
                    <a href="{{ $nextPendingStep['url'] }}" class="btn btn-emerald">
                        Begin Step {{ $nextPendingStep['num'] }}: {{ $nextPendingStep['title'] }} →
                    </a>
                @else
                    <button type="button" class="btn btn-plum" onclick="closeSetupRoadmapModal()">All Done — Close Guide</button>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    const REUSABLE_TX_TEMPLATES = @json($reusableTxTemplates);
    const QM_CURRENCY = @json($user->currency ?? 'GH¢');

    function openQuickModal(type = 'expense') {
        const modal = document.getElementById('quickTransactionModal');
        setQuickModalType(type);
        modal.classList.add('open');
    }

    function repeatQuickTransaction(txObj) {
        if (!txObj) return;
        openQuickModal(txObj.type || 'expense');
        fillQuickModalFromTemplate(txObj);
    }

    function fillQuickModalFromTemplate(tpl) {
        if (!tpl) return;
        const titleInput = document.getElementById('qmTitleInput');
        const amountInput = document.getElementById('qm_amount');
        const catSelect = document.getElementById('qm_category');
        const subSelect = document.getElementById('qm_subcategory');
        const pmSelect = document.getElementById('qm_payment_method');
        const merchantInput = document.getElementById('qm_merchant');

        if (titleInput) titleInput.value = tpl.title || '';
        if (amountInput && tpl.amount) amountInput.value = Number(tpl.amount).toFixed(2);
        if (catSelect && tpl.expense_category_id) {
            catSelect.value = String(tpl.expense_category_id);
            populateModalSubcategories();
        }
        if (subSelect && tpl.expense_subcategory_id) {
            subSelect.value = String(tpl.expense_subcategory_id);
        }
        if (pmSelect && tpl.payment_method_id) {
            pmSelect.value = String(tpl.payment_method_id);
        }
        if (merchantInput) {
            merchantInput.value = tpl.merchant_or_source || '';
        }
    }

    function applyReusableTxTemplate(idxStr) {
        if (idxStr === '' || idxStr === null) return;
        const tpl = REUSABLE_TX_TEMPLATES[parseInt(idxStr, 10)];
        if (tpl) {
            fillQuickModalFromTemplate(tpl);
        }
    }

    function checkTitleAutocompleteMatch(typedVal) {
        if (!typedVal) return;
        const selectedType = document.getElementById('qm_type')?.value || 'expense';
        const normalized = typedVal.trim().toLowerCase();
        const match = REUSABLE_TX_TEMPLATES.find(t => t.type === selectedType && (t.title || '').trim().toLowerCase() === normalized);
        if (match) {
            const amountInput = document.getElementById('qm_amount');
            if (amountInput && !amountInput.value) {
                amountInput.value = Number(match.amount).toFixed(2);
            }
            const catSelect = document.getElementById('qm_category');
            if (catSelect && match.expense_category_id) {
                catSelect.value = String(match.expense_category_id);
                populateModalSubcategories();
            }
            const subSelect = document.getElementById('qm_subcategory');
            if (subSelect && match.expense_subcategory_id) {
                subSelect.value = String(match.expense_subcategory_id);
            }
            const pmSelect = document.getElementById('qm_payment_method');
            if (pmSelect && match.payment_method_id) {
                pmSelect.value = String(match.payment_method_id);
            }
            const merchantInput = document.getElementById('qm_merchant');
            if (merchantInput && !merchantInput.value) {
                merchantInput.value = match.merchant_or_source || '';
            }
        }
    }

    function refreshReusableTemplatesUI(selectedType) {
        const box = document.getElementById('qmReusableTemplatesBox');
        const selectEl = document.getElementById('qmTemplateSelect');
        const chipsRow = document.getElementById('qmTemplateChipsRow');
        const datalist = document.getElementById('qmReusableTitlesDatalist');
        if (!box || !selectEl || !chipsRow) return;

        const matching = [];
        REUSABLE_TX_TEMPLATES.forEach((t, idx) => {
            if (t.type === selectedType) {
                matching.push({ ...t, _idx: idx });
            }
        });

        if (matching.length === 0) {
            box.style.display = 'none';
            return;
        }

        box.style.display = 'block';
        selectEl.innerHTML = '<option value="">— Select a past ' + selectedType + ' item to reuse —</option>';
        chipsRow.innerHTML = '';
        if (datalist) datalist.innerHTML = '';

        matching.forEach((item, i) => {
            const opt = document.createElement('option');
            opt.value = String(item._idx);
            opt.textContent = `${item.title} (${QM_CURRENCY}${Number(item.amount).toFixed(2)} • ${item.category_name}${item.count > 1 ? ' • ' + item.count + 'x' : ''})`;
            selectEl.appendChild(opt);

            if (datalist) {
                const dOpt = document.createElement('option');
                dOpt.value = item.title;
                datalist.appendChild(dOpt);
            }

            if (i < 6) {
                const chip = document.createElement('button');
                chip.type = 'button';
                chip.className = 'btn btn-ivory btn-sm';
                chip.style.cssText = 'padding: 3px 9px; font-size: 11px; border-radius: 9999px;';
                chip.innerHTML = `↻ <strong>${item.title}</strong> <span class="mono" style="color: var(--text-muted);">(${QM_CURRENCY}${Number(item.amount).toFixed(2)})</span>`;
                chip.addEventListener('click', function () {
                    selectEl.value = String(item._idx);
                    fillQuickModalFromTemplate(item);
                });
                chipsRow.appendChild(chip);
            }
        });
    }

    function setQuickModalType(type = 'expense') {
        const typeSelect = document.getElementById('qm_type');
        if (typeSelect) {
            typeSelect.value = type;
        }
        filterModalCategories();
    }

    function closeQuickModal() {
        document.getElementById('quickTransactionModal').classList.remove('open');
    }

    function filterModalCategories() {
        const selectedType = document.getElementById('qm_type').value;
        const titleEl = document.getElementById('quickModalTitle');
        const expBtn = document.getElementById('qmSwitchExpenseBtn');
        const incBtn = document.getElementById('qmSwitchIncomeBtn');
        const titleLabel = document.getElementById('qmTitleLabel');
        const titleInput = document.getElementById('qmTitleInput');
        const missingAlert = document.getElementById('qmMissingTypeCategoryAlert');
        const missingText = document.getElementById('qmMissingTypeCategoryText');

        if (titleEl) {
            titleEl.textContent = selectedType === 'income'
                ? 'Record New Income Inflow'
                : 'Record New Expense Outflow';
        }

        if (expBtn && incBtn) {
            if (selectedType === 'income') {
                incBtn.style.backgroundColor = 'var(--emerald)';
                incBtn.style.color = '#FFFFFF';
                expBtn.style.backgroundColor = 'transparent';
                expBtn.style.color = 'var(--text-muted)';
            } else {
                expBtn.style.backgroundColor = 'var(--coral)';
                expBtn.style.color = '#FFFFFF';
                incBtn.style.backgroundColor = 'transparent';
                incBtn.style.color = 'var(--text-muted)';
            }
        }

        if (titleLabel && titleInput) {
            if (selectedType === 'income') {
                titleLabel.textContent = 'Income Inflow Title';
                titleInput.placeholder = 'e.g., Monthly Salary, Consulting Retainer, or Dividend Payout';
            } else {
                titleLabel.textContent = 'Expense Outflow Title';
                titleInput.placeholder = 'e.g., Shell V-Power Fuel Refill or MaxMart Family Groceries';
            }
        }

        const catSelect = document.getElementById('qm_category');
        const placeholderOpt = catSelect.querySelector('option[data-placeholder="1"]');
        if (placeholderOpt) {
            placeholderOpt.remove();
        }

        let firstMatching = null;

        Array.from(catSelect.options).forEach(opt => {
            const matches = opt.getAttribute('data-type') === selectedType;
            opt.style.display = matches ? '' : 'none';
            opt.disabled = !matches;
            if (matches && !firstMatching) {
                firstMatching = opt.value;
            }
        });

        if (firstMatching) {
            catSelect.value = firstMatching;
            if (missingAlert) missingAlert.style.display = 'none';
        } else {
            const emptyOpt = document.createElement('option');
            emptyOpt.value = '';
            emptyOpt.setAttribute('data-placeholder', '1');
            emptyOpt.textContent = selectedType === 'income'
                ? '— No Income Category Created Yet —'
                : '— No Expense Category Created Yet —';
            catSelect.insertBefore(emptyOpt, catSelect.firstChild);
            catSelect.value = '';

            if (missingAlert && missingText) {
                missingText.innerHTML = selectedType === 'income'
                    ? 'You have not created an <strong>Income Category</strong> yet (e.g., Salary, Business Income). Create one first to record income:'
                    : 'You have not created an <strong>Expense Category</strong> yet. Create one first to record expenses:';
                missingAlert.style.display = 'flex';
            }
        }
        populateModalSubcategories();
        refreshReusableTemplatesUI(selectedType);
    }

    function populateModalSubcategories() {
        const catSelect = document.getElementById('qm_category');
        const subSelect = document.getElementById('qm_subcategory');
        const selectedOpt = catSelect.options[catSelect.selectedIndex];
        subSelect.innerHTML = '<option value="">— Optional Subcategory —</option>';

        if (!selectedOpt) return;
        const subs = JSON.parse(selectedOpt.getAttribute('data-subs') || '[]');
        subs.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = s.name;
            subSelect.appendChild(opt);
        });
    }

    // Responsive Sidebar Toggle: slide-over drawer on mobile (<= 920px), collapse rail on desktop (> 920px)
    function toggleSidebar() {
        const shell = document.getElementById('appShell');
        if (!shell) return;

        if (window.innerWidth <= 920) {
            shell.classList.toggle('mobile-nav-open');
        } else {
            const isCollapsed = shell.classList.toggle('sidebar-collapsed');
            try {
                localStorage.setItem('cediledger_sidebar_collapsed', isCollapsed ? '1' : '0');
            } catch (e) {}
        }
    }

    function closeMobileSidebar() {
        document.getElementById('appShell')?.classList.remove('mobile-nav-open');
    }

    (function restoreSidebarState() {
        try {
            if (window.innerWidth > 920 && localStorage.getItem('cediledger_sidebar_collapsed') === '1') {
                document.getElementById('appShell')?.classList.add('sidebar-collapsed');
            }
        } catch (e) {}
    })();

    // Ensure the active link in the sidebar is scrolled into view on load
    const SAMPLE_BANNER_HIDE_KEY = 'cediledger_hide_sample_banner_{{ $currentAuthId }}';
    const FRESH_GUIDE_HIDE_KEY = 'cediledger_hide_fresh_guide_{{ $currentAuthId }}';

    function hideSampleTourBanner() {
        const banner = document.getElementById('sampleTourBannerSection');
        const restoreBtn = document.getElementById('restoreSampleTourBannerBtn');
        if (banner) banner.style.display = 'none';
        if (restoreBtn) restoreBtn.style.display = 'inline-flex';
        try {
            localStorage.setItem(SAMPLE_BANNER_HIDE_KEY, '1');
        } catch (e) {}
    }

    function showSampleTourBanner() {
        const banner = document.getElementById('sampleTourBannerSection');
        const restoreBtn = document.getElementById('restoreSampleTourBannerBtn');
        if (banner) banner.style.display = 'flex';
        if (restoreBtn) restoreBtn.style.display = 'none';
        try {
            localStorage.removeItem(SAMPLE_BANNER_HIDE_KEY);
        } catch (e) {}
    }

    function hideFreshStartGuideBanner() {
        const banner = document.getElementById('freshStartGuideBannerSection');
        if (banner) banner.style.display = 'none';
        try {
            localStorage.setItem(FRESH_GUIDE_HIDE_KEY, '1');
        } catch (e) {}
    }

    document.addEventListener('DOMContentLoaded', function () {
        try {
            if (localStorage.getItem(SAMPLE_BANNER_HIDE_KEY) === '1') {
                const banner = document.getElementById('sampleTourBannerSection');
                const restoreBtn = document.getElementById('restoreSampleTourBannerBtn');
                if (banner) banner.style.display = 'none';
                if (restoreBtn) restoreBtn.style.display = 'inline-flex';
            }
            @if(session('fresh_start_initiated'))
                localStorage.removeItem(FRESH_GUIDE_HIDE_KEY);
            @else
                if (localStorage.getItem(FRESH_GUIDE_HIDE_KEY) === '1') {
                    const fgBanner = document.getElementById('freshStartGuideBannerSection');
                    if (fgBanner) fgBanner.style.display = 'none';
                }
            @endif
        } catch (e) {}

        populateModalSubcategories();
        initColorSwatchSelects();
        const activeLink = document.querySelector('#sidebarNavScroll .nav-link.active');
        if (activeLink) {
            activeLink.scrollIntoView({ block: 'nearest' });
        }

        @if(($user->has_sample_data ?? false) && !($user->tour_completed ?? false))
            if (!sessionStorage.getItem('cediledger_tour_shown_once')) {
                sessionStorage.setItem('cediledger_tour_shown_once', '1');
                openGuidedTourModal(0);
            }
        @endif

        @if(session('fresh_start_initiated'))
            openSetupRoadmapModal();
        @endif
    });

    function openSetupRoadmapModal() {
        document.getElementById('setupRoadmapModal')?.classList.add('open');
    }

    function closeSetupRoadmapModal() {
        document.getElementById('setupRoadmapModal')?.classList.remove('open');
    }

    // INTERACTIVE GUIDED TOUR ENGINE
    const guidedTourSteps = [
        {
            kicker: 'Step 1 of 6 • Executive Command Center',
            title: 'Real-Time Financial Summary & 8 KPI Cards',
            body: 'Your workspace is currently pre-loaded with sample financial data so you can see how CediLedger works in action. At the top of the Dashboard, 8 floating KPI cards track your Expenses (Today, Week, Month, Year), Monthly Income, Net Cash Flow Balance, Explicit Savings, and Remaining Monthly Budget.',
            tip: 'Tip: Press "N" on your keyboard or click "+ Record Transaction" in the sidebar from any screen to record a new expense or income entry.'
        },
        {
            kicker: 'Step 2 of 6 • Hierarchical Drill-Down Engine',
            title: 'Spending Breakdown Explorer (Month → Week 1–4 → Daily)',
            body: 'In the Spending Breakdown Explorer on the Dashboard (and the Period Explorer page), you can click Week 1, Week 2, Week 3, or Week 4 to inspect Monday–Sunday spending pills and see the exact transactions recorded inside that week.',
            tip: 'Tip: Click any Monday–Sunday day card to jump straight to that date\'s daily ledger.'
        },
        {
            kicker: 'Step 3 of 6 • Interactive Analytics & Presentation Mode',
            title: 'Fullscreen Chart Presentation Suite',
            body: 'Every chart on the dashboard features an "⛶ Expand" button (or you can click directly on any chart canvas) to pop it into a fullscreen presentation modal. Switch between Daily/Weekly/Yearly trends, Category Doughnut rankings, Income vs Expenditure, and Budget Utilization.',
            tip: 'Tip: Use Fullscreen Presentation Mode during monthly personal or executive financial reviews.'
        },
        {
            kicker: 'Step 4 of 6 • Taxonomy, Budgets & Recurring Commitments',
            title: '16+ Categories, Monthly Ceilings & Controlled Bill Posting',
            body: 'Your workspace comes with 16 default expense categories, 45+ subcategories, and 5 income streams. Under Budgets & Limits and Recurring Bills, you can set category spending caps and post due bills with anti-duplication protection.',
            tip: 'Tip: You can create custom categories and subcategories at any time from the Categories page.'
        },
        {
            kicker: 'Step 5 of 6 • Explicit Savings & 2FA Security',
            title: 'Dedicated Savings Vaults & Two-Factor Authentication',
            body: 'CediLedger never assumes unspent income is automatically saved. Under Savings & Goals, you explicitly record deposits and withdrawals into vaults (e.g., Treasury Bills, Emergency Fund). Under Profile & 2FA, you can update your profile and enable TOTP Two-Factor Authentication.',
            tip: 'Tip: Enable 2FA on your Profile page to protect your financial ledger with Google Authenticator or Authy.'
        },
        {
            kicker: 'Step 6 of 6 • Ready to Start Fresh!',
            title: 'Reset the Sample Database & Keep Your Profile Intact',
            body: 'Now that you have toured the platform with sample data, you can click "Reset Database & Start Fresh Now" below (or the "↻ Reset & Start Fresh" button in the top bar at any time). It will erase all sample transactions, budgets, and goals so you start at zero—while keeping your personal profile details, categories, and payment channels 100% intact!',
            tip: 'Click "↻ Reset Database & Start Fresh Now" below to clear the sample data and begin entering your own records.'
        }
    ];

    let currentGuidedTourIndex = 0;

    function openGuidedTourModal(stepIndex = 0) {
        currentGuidedTourIndex = stepIndex;
        renderGuidedTourStep(currentGuidedTourIndex);
        document.getElementById('guidedTourModal')?.classList.add('open');
    }

    function closeGuidedTourModal() {
        document.getElementById('guidedTourModal')?.classList.remove('open');
    }

    function renderGuidedTourStep(idx) {
        currentGuidedTourIndex = Math.max(0, Math.min(guidedTourSteps.length - 1, idx));
        const step = guidedTourSteps[currentGuidedTourIndex];

        document.getElementById('tourStepHeaderTitle').textContent = step.title;
        document.getElementById('tourStepCounterLabel').textContent = `Step ${currentGuidedTourIndex + 1} of ${guidedTourSteps.length}`;

        for (let i = 0; i < guidedTourSteps.length; i++) {
            const bar = document.getElementById('tourProgressBar_' + i);
            if (bar) {
                bar.style.backgroundColor = i <= currentGuidedTourIndex ? '#2D9F78' : 'var(--ivory-border)';
            }
        }

        const contentBox = document.getElementById('tourStepContentBox');
        contentBox.innerHTML = `
            <div>
                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--emerald); margin-bottom: 6px;">
                    ${step.kicker}
                </div>
                <h4 style="font-size: 17px; font-weight: 800; color: var(--text-dark); margin-bottom: 10px;">
                    ${step.title}
                </h4>
                <p style="font-size: 13.5px; color: var(--text-dark); line-height: 1.6;">
                    ${step.body}
                </p>
            </div>
            <div style="background-color: #FFFFFF; border: 1px solid var(--ivory-border); border-left: 4px solid var(--mustard); padding: 10px 14px; border-radius: 9px; font-size: 12px; color: var(--text-muted);">
                <strong style="color: var(--text-dark);">Pro Tip:</strong> ${step.tip}
            </div>
        `;

        const prevBtn = document.getElementById('tourPrevBtn');
        const nextBtn = document.getElementById('tourNextBtn');
        const resetFinishBtn = document.getElementById('tourResetFinishBtn');

        prevBtn.style.display = currentGuidedTourIndex === 0 ? 'none' : 'inline-flex';

        if (currentGuidedTourIndex === guidedTourSteps.length - 1) {
            nextBtn.style.display = 'none';
            resetFinishBtn.style.display = 'inline-flex';
        } else {
            nextBtn.style.display = 'inline-flex';
            resetFinishBtn.style.display = 'none';
        }
    }

    function nextGuidedTourStep() {
        if (currentGuidedTourIndex < guidedTourSteps.length - 1) {
            renderGuidedTourStep(currentGuidedTourIndex + 1);
        }
    }

    function prevGuidedTourStep() {
        if (currentGuidedTourIndex > 0) {
            renderGuidedTourStep(currentGuidedTourIndex - 1);
        }
    }

    function openResetWorkspaceModal() {
        document.getElementById('resetWorkspaceModal')?.classList.add('open');
    }

    function closeResetWorkspaceModal() {
        document.getElementById('resetWorkspaceModal')?.classList.remove('open');
    }

    // HEADER NOTIFICATION BELL DROPDOWN & UNREAD/READ TABS
    function toggleNotifDropdown(e) {
        if (e) e.stopPropagation();
        const panel = document.getElementById('notifDropdownPanel');
        const btn = document.getElementById('notifBellBtn');
        if (!panel) return;
        const isOpen = panel.classList.toggle('open');
        if (btn) btn.classList.toggle('active', isOpen);
    }

    function closeNotifDropdown() {
        document.getElementById('notifDropdownPanel')?.classList.remove('open');
        document.getElementById('notifBellBtn')?.classList.remove('active');
    }

    function switchNotifTab(tab) {
        const unreadPane = document.getElementById('notifPaneUnread');
        const readPane = document.getElementById('notifPaneRead');
        const unreadBtn = document.getElementById('notifTabBtnUnread');
        const readBtn = document.getElementById('notifTabBtnRead');

        if (tab === 'read') {
            if (unreadPane) unreadPane.style.display = 'none';
            if (readPane) readPane.style.display = 'flex';
            unreadBtn?.classList.remove('active');
            readBtn?.classList.add('active');
        } else {
            if (unreadPane) unreadPane.style.display = 'flex';
            if (readPane) readPane.style.display = 'none';
            unreadBtn?.classList.add('active');
            readBtn?.classList.remove('active');
        }
    }

    document.addEventListener('click', function (e) {
        const wrapper = document.getElementById('notifBellWrapper');
        if (wrapper && !wrapper.contains(e.target)) {
            closeNotifDropdown();
        }
        document.querySelectorAll('.color-select-widget.open').forEach(function (w) {
            if (!w.contains(e.target)) {
                w.classList.remove('open');
            }
        });
    });

    function initColorSwatchSelects() {
        document.querySelectorAll('select[data-color-picker="true"]').forEach(function (selectEl) {
            if (selectEl.dataset.colorUpgraded === '1') return;
            selectEl.dataset.colorUpgraded = '1';

            const wrapper = document.createElement('div');
            wrapper.className = 'color-select-widget';
            selectEl.parentNode.insertBefore(wrapper, selectEl);
            wrapper.appendChild(selectEl);
            selectEl.style.display = 'none';

            const trigger = document.createElement('button');
            trigger.type = 'button';
            trigger.className = 'color-select-trigger';

            const menu = document.createElement('div');
            menu.className = 'color-select-menu';

            function renderTriggerAndOptions() {
                const selectedOpt = selectEl.options[selectEl.selectedIndex] || selectEl.options[0];
                const currentHex = selectedOpt ? selectedOpt.value : '#2D9F78';
                const currentText = selectedOpt ? selectedOpt.textContent.trim() : currentHex;

                trigger.innerHTML = `
                    <span class="color-select-label-wrap">
                        <span class="color-swatch-dot" style="background-color: ${currentHex};"></span>
                        <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${currentText}</span>
                    </span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; color: var(--text-muted);">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                `;

                menu.innerHTML = '';
                Array.from(selectEl.options).forEach(function (opt, idx) {
                    const hex = opt.value;
                    const label = opt.textContent.trim();
                    const isSelected = idx === selectEl.selectedIndex;

                    const itemBtn = document.createElement('button');
                    itemBtn.type = 'button';
                    itemBtn.className = 'color-select-option' + (isSelected ? ' selected' : '');
                    itemBtn.innerHTML = `
                        <span class="color-select-label-wrap">
                            <span class="color-swatch-dot" style="background-color: ${hex};"></span>
                            <span>${label}</span>
                        </span>
                        <span style="color: ${hex}; font-weight: 800; font-size: 12px;">${isSelected ? '✓' : '●'}</span>
                    `;
                    itemBtn.addEventListener('click', function (ev) {
                        ev.stopPropagation();
                        selectEl.selectedIndex = idx;
                        selectEl.dispatchEvent(new Event('change'));
                        renderTriggerAndOptions();
                        wrapper.classList.remove('open');
                    });
                    menu.appendChild(itemBtn);
                });
            }

            trigger.addEventListener('click', function (ev) {
                ev.stopPropagation();
                const wasOpen = wrapper.classList.contains('open');
                document.querySelectorAll('.color-select-widget.open').forEach(w => w.classList.remove('open'));
                if (!wasOpen) {
                    wrapper.classList.add('open');
                }
            });

            renderTriggerAndOptions();
            selectEl.addEventListener('color-picker:refresh', renderTriggerAndOptions);
            wrapper.appendChild(trigger);
            wrapper.appendChild(menu);
        });
    }

    // Keyboard shortcut 'N' to open quick transaction modal, '[' to toggle sidebar
    document.addEventListener('keydown', function (e) {
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) return;
        if (e.key === 'n' || e.key === 'N') {
            e.preventDefault();
            openQuickModal('expense');
        } else if (e.key === '[') {
            e.preventDefault();
            toggleSidebar();
        } else if (e.key === 'Escape') {
            closeQuickModal();
            closeMobileSidebar();
            closeGuidedTourModal();
            closeResetWorkspaceModal();
            closeSetupRoadmapModal();
            closeNotifDropdown();
        }
    });
</script>
@stack('scripts')
</body>
</html>
