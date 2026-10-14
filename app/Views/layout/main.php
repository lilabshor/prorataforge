<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'ProrataForge - Billing & Subscription Engine') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-page: #f8fafc;
            --bg-card: #ffffff;
            --border-color: #e2e8f0;
            --border-hover: #cbd5e1;
            --text-heading: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --btn-dark: #0f172a;
            --btn-dark-hover: #1e293b;
            --success: #059669;
            --warning: #d97706;
            --danger: #dc2626;
            --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --font-mono: 'JetBrains Mono', 'Courier New', monospace;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-sans);
            background-color: var(--bg-page);
            color: var(--text-body);
            min-height: 100vh;
            line-height: 1.5;
            font-size: 0.9375rem;
            -webkit-font-smoothing: antialiased;
        }

        /* Top Bar */
        .top-navbar {
            background-color: #ffffff;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 40;
        }
        .navbar-inner {
            max-width: 1140px;
            margin: 0 auto;
            padding: 0 1.5rem;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .brand-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            color: var(--text-heading);
            font-weight: 700;
            font-size: 1.125rem;
            letter-spacing: -0.02em;
        }
        .brand-tag {
            font-size: 0.75rem;
            font-weight: 600;
            background-color: #eff6ff;
            color: var(--primary);
            border: 1px solid #bfdbfe;
            padding: 0.15rem 0.45rem;
            border-radius: 4px;
        }
        .nav-menu {
            display: flex;
            align-items: center;
            list-style: none;
            gap: 1.5rem;
        }
        .nav-menu a {
            text-decoration: none;
            color: var(--text-muted);
            font-size: 0.875rem;
            font-weight: 500;
            transition: color 0.15s ease;
        }
        .nav-menu a:hover, .nav-menu a.active {
            color: var(--text-heading);
        }
        .nav-auth {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.8125rem;
        }

        /* Container */
        .page-container {
            max-width: 1140px;
            margin: 2rem auto;
            padding: 0 1.5rem 4rem;
        }

        /* Alerts */
        .alert-box {
            padding: 0.875rem 1.125rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .alert-box.success {
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .alert-box.error {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* Clean Panel Card */
        .panel {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.5rem;
        }
        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border-color);
        }
        .panel-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-heading);
            letter-spacing: -0.015em;
        }
        .panel-subtitle {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-top: 0.2rem;
        }

        /* Clean Tables */
        .table-wrap {
            overflow-x: auto;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
            text-align: left;
        }
        table.data-table th {
            padding: 0.75rem 1rem;
            background-color: #f8fafc;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--border-color);
        }
        table.data-table td {
            padding: 0.95rem 1rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
            color: var(--text-body);
        }
        table.data-table tbody tr:last-child td {
            border-bottom: none;
        }
        table.data-table tbody tr:hover td {
            background-color: #fcfcfd;
        }

        /* Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.55rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.02em;
        }
        .status-badge.active, .status-badge.berhasil, .status-badge.dibayar {
            background-color: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }
        .status-badge.trialing, .status-badge.terbit, .status-badge.pending {
            background-color: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
        }
        .status-badge.canceled, .status-badge.kedaluwarsa, .status-badge.gagal {
            background-color: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
        .status-badge.gratis {
            background-color: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }

        /* Buttons */
        .btn-ui {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-family: inherit;
            font-size: 0.875rem;
            font-weight: 500;
            padding: 0.55rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s ease;
            border: 1px solid transparent;
            line-height: 1.25;
        }
        .btn-ui-primary {
            background-color: var(--primary);
            color: #ffffff;
            box-shadow: var(--shadow-sm);
        }
        .btn-ui-primary:hover {
            background-color: var(--primary-hover);
        }
        .btn-ui-dark {
            background-color: var(--btn-dark);
            color: #ffffff;
            box-shadow: var(--shadow-sm);
        }
        .btn-ui-dark:hover {
            background-color: var(--btn-dark-hover);
        }
        .btn-ui-secondary {
            background-color: #ffffff;
            color: var(--text-heading);
            border-color: var(--border-color);
            box-shadow: var(--shadow-sm);
        }
        .btn-ui-secondary:hover {
            background-color: #f8fafc;
            border-color: var(--border-hover);
        }
        .btn-ui-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.8125rem;
        }
        .btn-ui-danger {
            background-color: #ffffff;
            color: var(--danger);
            border-color: #fca5a5;
        }
        .btn-ui-danger:hover {
            background-color: #fef2f2;
        }

        /* Form Controls */
        .field-group {
            margin-bottom: 1.25rem;
        }
        .field-label {
            display: block;
            margin-bottom: 0.4rem;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--text-heading);
        }
        .field-input {
            width: 100%;
            padding: 0.6rem 0.85rem;
            background-color: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            color: var(--text-heading);
            font-family: inherit;
            font-size: 0.875rem;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .field-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }
        .field-hint {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 0.35rem;
            display: block;
        }

        .mono {
            font-family: var(--font-mono);
        }
    </style>
</head>
<body>
    <header class="top-navbar">
        <div class="navbar-inner">
            <a href="<?= site_url('/') ?>" class="brand-link">
                <span>ProrataForge</span>
                <span class="brand-tag">Billing</span>
            </a>

            <nav>
                <ul class="nav-menu">
                    <li><a href="<?= site_url('playground') ?>">Simulator Prorata</a></li>
                    <li><a href="<?= site_url('langganan') ?>">Langganan</a></li>
                    <li><a href="<?= site_url('tagihan') ?>">Faktur</a></li>
                    <li><a href="<?= site_url('docs') ?>">Dokumentasi API</a></li>
                    <?php if (session('is_admin')): ?>
                        <li><a href="<?= site_url('admin/paket') ?>">Kelola Paket</a></li>
                        <li><a href="<?= site_url('admin/laporan/mrr') ?>">Laporan MRR</a></li>
                    <?php endif ?>
                </ul>
            </nav>

            <div class="nav-auth">
                <?php if (session('user_id')): ?>
                    <span style="color: var(--text-muted);">
                        Sesi: <strong style="color: var(--text-heading);"><?= session('is_admin') ? 'Administrator' : 'Pelanggan' ?></strong>
                    </span>
                    <a href="<?= site_url('demo/logout') ?>" class="btn-ui btn-ui-secondary btn-ui-sm">Keluar</a>
                <?php else: ?>
                    <a href="<?= site_url('demo/user') ?>" class="btn-ui btn-ui-secondary btn-ui-sm">Login User</a>
                    <a href="<?= site_url('demo/admin') ?>" class="btn-ui btn-ui-dark btn-ui-sm">Login Admin</a>
                <?php endif ?>
            </div>
        </div>
    </header>

    <main class="page-container">
        <?php if (session()->getFlashdata('sukses')): ?>
            <div class="alert-box success">
                <span><?= esc(session()->getFlashdata('sukses')) ?></span>
            </div>
        <?php endif ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert-box error">
                <span><?= esc(session()->getFlashdata('error')) ?></span>
            </div>
        <?php endif ?>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert-box error">
                <ul style="margin-left: 1.25rem;">
                    <?php foreach (session()->getFlashdata('errors') as $err): ?>
                        <li><?= esc($err) ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif ?>

        <?= $this->renderSection('content') ?>
    </main>
</body>
</html>
