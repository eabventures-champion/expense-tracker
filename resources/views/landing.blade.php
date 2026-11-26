<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CediLedger Cloud — Modern Financial Intelligence & Expense Tracking SaaS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --plum: #1E1B2E;
            --plum-dark: #151321;
            --plum-surface: #27233A;
            --coral: #E66D55;
            --coral-dark: #D05A42;
            --emerald: #2D9F78;
            --emerald-dark: #238261;
            --mustard: #E5A93C;
            --ivory: #F4F5F8;
            --ivory-card: #FFFFFF;
            --ivory-border: #E5E7EF;
            --blue: #4E73DF;
            --text-dark: #14161F;
            --text-muted: #646A7A;
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
            line-height: 1.55;
            -webkit-font-smoothing: antialiased;
            width: 100%;
        }

        .mono {
            font-family: 'JetBrains Mono', monospace;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        /* FIXED STATIC NAVBAR ON SCROLL (DESKTOP & MOBILE) */
        .saas-nav {
            background-color: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--ivory-border);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            width: 100%;
            box-shadow: 0 2px 10px rgba(20, 22, 31, 0.05);
        }

        .saas-nav-inner {
            max-width: 1240px;
            margin: 0 auto;
            padding: 13px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: nowrap;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .brand-mark {
            width: 36px;
            height: 36px;
            background-color: var(--emerald);
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            font-size: 13px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(45, 159, 120, 0.35);
            flex-shrink: 0;
        }

        .brand-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 17px;
            font-weight: 800;
            color: var(--text-dark);
            white-space: nowrap;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 9px 16px;
            font-size: 13px;
            font-weight: 700;
            border-radius: 10px;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.16s ease;
            white-space: nowrap;
            text-align: center;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-emerald {
            background-color: var(--emerald);
            color: #FFFFFF;
            box-shadow: 0 4px 14px rgba(45, 159, 120, 0.3);
        }
        .btn-emerald:hover { background-color: var(--emerald-dark); }

        .btn-plum {
            background-color: var(--plum);
            color: #FFFFFF;
        }
        .btn-plum:hover { background-color: var(--plum-surface); }

        .btn-outline {
            background-color: #FFFFFF;
            color: var(--text-dark);
            border-color: var(--ivory-border);
        }
        .btn-outline:hover { background-color: var(--ivory); }

        /* HERO SECTION */
        .hero-section {
            background-color: var(--plum);
            color: #FFFFFF;
            padding: 132px 24px 76px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            overflow-x: hidden;
        }

        .hero-inner {
            max-width: 1180px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            gap: 44px;
            align-items: center;
        }

        .hero-kicker {
            display: inline-block;
            background-color: rgba(45, 159, 120, 0.2);
            color: #5CE0B5;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            padding: 5px 12px;
            border-radius: 9999px;
            margin-bottom: 16px;
            max-width: 100%;
        }

        .hero-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 42px;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.03em;
            margin-bottom: 16px;
            overflow-wrap: break-word;
        }

        .hero-subtitle {
            font-size: 15.5px;
            color: #B4B8CA;
            margin-bottom: 26px;
            max-width: 540px;
        }

        .hero-cta-group {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .hero-cta-btn {
            padding: 13px 22px;
            font-size: 14.5px;
        }

        .hero-preview-card {
            background-color: var(--plum-surface);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 24px 50px rgba(0, 0, 0, 0.45);
            display: flex;
            flex-direction: column;
            gap: 16px;
            min-width: 0;
        }

        .preview-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 12px;
        }

        .preview-metric-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .preview-metric {
            background-color: rgba(0, 0, 0, 0.22);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 14px;
            min-width: 0;
        }

        /* FEATURES GRID */
        .features-section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 24px;
        }

        .section-heading-wrap {
            text-align: center;
            max-width: 680px;
            margin: 0 auto 42px;
        }

        .section-heading-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 29px;
            font-weight: 800;
            margin-bottom: 10px;
            line-height: 1.25;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .feature-card {
            background-color: #FFFFFF;
            border: 1px solid var(--ivory-border);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 14px rgba(20, 22, 31, 0.04);
        }

        .feature-icon {
            width: 40px;
            height: 40px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
            font-weight: 700;
        }

        .feature-card h3 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 16.5px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .feature-card p {
            font-size: 13.5px;
            color: var(--text-muted);
        }

        .saas-footer {
            background-color: #FFFFFF;
            border-top: 1px solid var(--ivory-border);
            padding: 24px 16px;
            text-align: center;
            font-size: 12.5px;
            color: var(--text-muted);
        }

        /* TABLET BREAKPOINT */
        @media (max-width: 960px) {
            .hero-inner {
                grid-template-columns: 1fr;
                gap: 32px;
            }
            .hero-title {
                font-size: 32px;
            }
            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* MOBILE BREAKPOINT (iPhone SE 375px and phones) */
        @media (max-width: 580px) {
            .saas-nav-inner {
                padding: 10px 12px;
                gap: 6px;
                flex-wrap: nowrap;
            }
            .brand-logo {
                gap: 7px;
            }
            .brand-mark {
                width: 30px;
                height: 30px;
                font-size: 11px;
                border-radius: 8px;
            }
            .brand-title {
                font-size: 14px;
            }
            .nav-actions {
                gap: 6px;
                flex-wrap: nowrap;
                flex-shrink: 0;
            }
            .nav-actions .btn {
                padding: 7px 10px;
                font-size: 11.5px;
                border-radius: 8px;
            }
            .btn-nav-text-long {
                display: none;
            }
            .hero-section {
                padding: 82px 16px 40px;
            }
            .hero-kicker {
                font-size: 10px;
                padding: 4px 10px;
                margin-bottom: 12px;
            }
            .hero-title {
                font-size: 25px;
                line-height: 1.22;
                margin-bottom: 12px;
            }
            .hero-subtitle {
                font-size: 14px;
                margin-bottom: 20px;
            }
            .hero-cta-group {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }
            .hero-cta-btn {
                width: 100%;
                padding: 12px 16px;
                font-size: 13.5px;
            }
            .hero-preview-card {
                padding: 16px;
                border-radius: 14px;
            }
            .preview-metric-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            .features-section {
                padding: 36px 16px;
            }
            .section-heading-wrap {
                margin-bottom: 26px;
            }
            .section-heading-title {
                font-size: 22px;
            }
            .features-grid {
                grid-template-columns: 1fr;
                gap: 14px;
            }
            .feature-card {
                padding: 18px;
            }
        }
    </style>
</head>
<body>
    <header class="saas-nav">
        <div class="saas-nav-inner">
            <a href="{{ route('welcome') }}" class="brand-logo">
                <span class="brand-mark">GH¢</span>
                <span class="brand-title">CediLedger Cloud</span>
            </a>
            <div class="nav-actions">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-plum">Dashboard →</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline">Sign In</a>
                    <a href="{{ route('register') }}" class="btn btn-emerald">
                        <span>Get Started</span>
                        <span class="btn-nav-text-long">Free</span>
                        <span>→</span>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <section class="hero-section">
        <div class="hero-inner">
            <div>
                <span class="hero-kicker">Multi-Tenant Personal Finance SaaS</span>
                <h1 class="hero-title">Your private financial intelligence command center.</h1>
                <p class="hero-subtitle">
                    Create your own isolated workspace in seconds. Track daily, weekly, monthly, and yearly expenses, set category budgets, automate recurring bill reminders, and grow explicit savings vaults in GH¢, USD, GBP, EUR, or NGN.
                </p>
                <div class="hero-cta-group">
                    <a href="{{ route('register') }}" class="btn btn-emerald hero-cta-btn">
                        Start Your Free Workspace →
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-outline hero-cta-btn" style="background-color: rgba(255,255,255,0.08); color: #FFF; border-color: rgba(255,255,255,0.18);">
                        Sign In to Existing Account
                    </a>
                </div>
            </div>

            <div class="hero-preview-card">
                <div class="preview-card-header">
                    <div>
                        <div style="font-size: 10.5px; color: #5CE0B5; text-transform: uppercase; font-weight: 700;">Live SaaS Workspace Preview</div>
                        <div style="font-weight: 700; font-size: 14.5px;">Multi-Horizon Spending Breakdown</div>
                    </div>
                    <span class="mono" style="background-color: rgba(45,159,120,0.2); color: #5CE0B5; padding: 3px 10px; border-radius: 9999px; font-size: 10.5px; font-weight: 700;">
                        2FA Protected
                    </span>
                </div>
                <div class="preview-metric-grid">
                    <div class="preview-metric">
                        <div style="font-size: 11px; color: #A3A7BA;">Monthly Net Cash Flow</div>
                        <div class="mono" style="font-size: 19px; font-weight: 700; color: #5CE0B5; margin-top: 4px;">+GH¢8,950.00</div>
                    </div>
                    <div class="preview-metric">
                        <div style="font-size: 11px; color: #A3A7BA;">Explicit Savings Vaults</div>
                        <div class="mono" style="font-size: 19px; font-weight: 700; color: #F5C469; margin-top: 4px;">GH¢76,700.00</div>
                    </div>
                </div>
                <div style="background-color: rgba(0,0,0,0.22); border-radius: 12px; padding: 14px; font-size: 12px; color: #D5D8E6;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 6px; margin-bottom: 6px;">
                        <strong>Instant Account Onboarding</strong>
                        <span class="mono" style="color: #5CE0B5; font-size: 11px;">100% Isolated Data</span>
                    </div>
                    Every new account automatically receives 16 expense categories, 45+ subcategories, 5 income streams, and 5 payment channels ready to customize.
                </div>
            </div>
        </div>
    </section>

    <section class="features-section">
        <div class="section-heading-wrap">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--emerald); letter-spacing: 0.08em; margin-bottom: 6px;">Built for Clarity & Control</div>
            <h2 class="section-heading-title">Everything you need to master your personal & business cash flow</h2>
            <p style="color: var(--text-muted); font-size: 14px;">Each user gets a dedicated, private workspace with full customization over categories, currencies, and security.</p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon" style="background-color: rgba(45, 159, 120, 0.12); color: var(--emerald);">01</div>
                <h3>Hierarchical Period Explorer</h3>
                <p>Drill down seamlessly from Annual totals to Monthly overviews, Week 1–4 blocks, Monday–Sunday strips, and exact daily transactions.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon" style="background-color: rgba(229, 169, 60, 0.14); color: #C88F28;">02</div>
                <h3>Explicit Savings & Capital Goals</h3>
                <p>Unspent income is never auto-assumed as savings. Track real deposits and withdrawals into Treasury Bills, real estate, and emergency vaults.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon" style="background-color: rgba(230, 109, 85, 0.12); color: var(--coral);">03</div>
                <h3>Category Budgets & Live Alerts</h3>
                <p>Set weekly, monthly, and annual spending ceilings plus per-category allocations with automated breach diagnostics.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon" style="background-color: rgba(78, 115, 223, 0.12); color: var(--blue);">04</div>
                <h3>Fullscreen Chart Presentation</h3>
                <p>Pop any spending trend, category distribution, or income-vs-expense chart into fullscreen presentation mode for deeper inspection.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon" style="background-color: rgba(217, 109, 155, 0.12); color: #D96D9B;">05</div>
                <h3>TOTP 2FA & Account Security</h3>
                <p>Protect your financial records with time-based Two-Factor Authentication (Google Authenticator, Authy) and one-time recovery codes.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon" style="background-color: rgba(30, 27, 46, 0.1); color: var(--plum);">06</div>
                <h3>PDF, Excel & CSV Statements</h3>
                <p>Generate itemised date-range financial statements and export them in one click to printable PDF, Excel (.xls), or CSV.</p>
            </div>
        </div>
    </section>

    <footer class="saas-footer">
        <strong>CediLedger Cloud</strong> — Personal Expense Tracker & Financial Intelligence SaaS Platform
    </footer>
</body>
</html>
