<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-rtl@0.4.0/dist/css/bootstrap-rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <title>لوحة التحكم | لوحة الإدارة</title>
    <style>
        :root {
            --primary: #F6BE00;
            --primary-light: #fde6b3;
            --primary-dark: #e5a500;
            --bg-light: #f9fafc;
            --white: #ffffff;
            --gray-text: #4a5568;
            --light-gray: #e2e8f0;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.12);
            --transition: all 0.25s ease;
        }

        body {
            background-color: var(--bg-light);
            font-family: 'Cairo', 'Segoe UI', sans-serif;
            color: var(--gray-text);
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 220px;
            background: linear-gradient(165deg, var(--primary-dark) 0%, var(--primary) 100%);
            padding: 20px;
            color: white;
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: -4px 0 20px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 20px 0;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
            justify-content: center;
        }

        .sidebar-logo {
            width: 40px;
            height: 40px;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }

        .sidebar-title {
            font-size: 22px;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin: 0;
        }

        .sidebar-nav {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 15px;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 16px;
        }

        .sidebar-nav a:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(-5px);
        }

        .sidebar-nav a.active {
            background: rgba(255, 255, 255, 0.2);
            font-weight: bold;
            border-right: 3px solid white;
            border-left: none;
        }

        /* Dropdown Styles - RTL Adjusted */
        .sidebar-dropdown-container {
            position: relative;
        }

        .sidebar-dropdown-toggle {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .sidebar-dropdown-toggle:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .sidebar-dropdown-toggle.active {
            background: rgba(255, 255, 255, 0.2);
            font-weight: bold;
            border-right: 3px solid white;
            border-left: none;
        }

        .dropdown-arrow {
            font-size: 12px;
            transition: transform 0.3s;
            margin-left: 10px;
        }

        .show .dropdown-arrow {
            transform: rotate(180deg);
        }

        .sidebar-dropdown {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 10px 0;
            margin: 5px 0;
            width: 100%;
            display: none;
            backdrop-filter: blur(10px);
        }

        .sidebar-dropdown.show {
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .sidebar-dropdown a {
            padding: 10px 25px 10px 15px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 15px;
        }

        .sidebar-dropdown a.active {
            font-weight: bold;
            background: rgba(255, 255, 255, 0.25);
            border-right: 3px solid white;
            padding-right: 22px;
        }

        .sidebar-dropdown a:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(-3px);
        }

        /* Main Content - Offset for fixed sidebar (right side) */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            margin-right: 220px;
            margin-left: 0;
            width: calc(100% - 220px);
        }

        /* Top Bar - Arabic style */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--light-gray);
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .page-title {
            color: var(--primary-dark);
            font-size: 26px;
            font-weight: 600;
            margin: 0;
        }

        /* Content Area - Scrollable */
        .content-area {
            padding: 30px;
            flex: 1;
            background: linear-gradient(135deg, var(--bg-light) 0%, var(--white) 100%);
            overflow-y: auto;
            min-height: calc(100vh - 70px);
        }

        /* User Info (kept for possible future use) */
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            direction: ltr;
        }

        .user-details {
            text-align: right;
            direction: rtl;
        }

        .user-name {
            font-weight: bold;
            color: var(--gray-text);
            margin: 0;
            font-size: 16px;
        }

        .user-role {
            color: #718096;
            font-size: 14px;
            margin: 0;
        }

        .profile-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary);
            background: var(--primary-light);
        }

        /* Dashboard Widgets - Arabic style */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            border: none;
            border-radius: 20px;
            background: var(--white);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            padding: 25px;
            border-right: 5px solid var(--primary);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card h3 {
            margin: 0 0 15px 0;
            color: #718096;
            font-size: 15px;
            font-weight: 600;
        }

        .stat-card p {
            font-size: 32px;
            font-weight: 700;
            color: var(--gray-text);
            margin: 0;
            direction: ltr;
            text-align: left;
        }

        .stat-card .change {
            font-size: 13px;
            margin-top: 8px;
            color: #4CAF50;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .change.negative {
            color: #F44336;
        }

        /* Recent Activity - Arabic style */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-header h2 {
            margin: 0;
            color: var(--primary-dark);
            font-size: 22px;
            font-weight: 700;
        }

        .activity-container {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 30px;
            border: none;
        }

        .activity-item {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid var(--light-gray);
            align-items: center;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 15px;
            color: white;
            font-size: 16px;
        }

        .activity-details {
            flex: 1;
        }

        .activity-title {
            font-weight: 600;
            color: var(--gray-text);
            margin-bottom: 5px;
        }

        .activity-time {
            color: #a0aec0;
            font-size: 13px;
        }

        /* Quick Actions - Arabic style */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .action-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            box-shadow: var(--shadow-sm);
            cursor: pointer;
            transition: var(--transition);
            border: 2px solid transparent;
        }

        .action-card:hover {
            background: var(--primary-light);
            border-color: var(--primary);
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .action-card i {
            font-size: 32px;
            color: var(--primary-dark);
            margin-bottom: 15px;
            display: block;
        }

        .action-card span {
            font-weight: 600;
            color: var(--gray-text);
            font-size: 16px;
        }

        /* Mobile menu toggle - Arabic */
        .menu-toggle {
            display: none;
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
            position: fixed;
            left: 20px;
            top: 20px;
            z-index: 1001;
        }

        /* Responsive Design - Arabic RTL */
        @media (max-width: 1200px) {

            .stats-container,
            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 200px;
                padding: 15px;
            }

            .main-content {
                margin-right: 200px;
                width: calc(100% - 200px);
            }
        }

        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }

            .sidebar {
                transform: translateX(100%);
                transition: transform 0.3s ease;
                width: 280px;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-right: 0;
                width: 100%;
            }

            .stats-container,
            .quick-actions {
                grid-template-columns: 1fr;
            }

            .content-area {
                padding: 20px;
            }

            .top-bar {
                padding: 15px 20px;
            }
        }

        @media (max-width: 576px) {
            .page-title {
                font-size: 20px;
            }
        }

        /* Arabic Font Support */
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap');

        .arabic-font {
            font-family: 'Cairo', sans-serif;
        }

        body,
        .sidebar-nav a,
        .stat-card h3,
        .activity-title,
        .user-name {
            font-family: 'Cairo', 'Segoe UI', sans-serif;
        }

        /* RTL specific adjustments */
        .sidebar-nav a.active {
            border-right-width: 3px;
            border-left: none;
        }

        .sidebar-dropdown a.active {
            border-right-width: 3px;
            border-left: none;
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            color: #000;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .btn-outline-primary {
            border-color: var(--primary);
            color: var(--primary);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-light);
            border-color: var(--primary);
            color: #000;
        }

        .table thead {
            background-color: var(--primary-light);
            color: #000;
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .pagination .page-link {
            color: var(--primary);
        }

        /* Toggle switches */
        .form-switch .form-check-input {
            width: 3em;
            height: 1.5em;
            cursor: pointer;
        }

        .form-switch .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }
    </style>

    {{-- FIXED: Added @stack so child views can push styles --}}
    @stack('styles')

</head>

<body>
    <button class="menu-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="{{ asset('images/logo.png') }}" alt="شحن العودة" class="sidebar-logo">
                <div class="sidebar-title arabic-font">شحن العودة</div>
            </div>
            <nav class="sidebar-nav">
                <a href="/" class="{{ request()->routeIs('home') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i>
                    <span>الرئيسية</span>
                </a>

                <a href="{{ route('cities.index') }}" class="{{ request()->routeIs('cities.*') ? 'active' : '' }}">
                    <i class="fas fa-city"></i>
                    <span>المدن</span>
                </a>

                <a href="{{ route('drivers.index') }}" class="{{ request()->routeIs('drivers.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span>السائقين</span>
                </a>

                <a href="{{ route('manage-orders.index') }}"
                    class="{{ request()->routeIs('manage-orders.*') ? 'active' : '' }}">
                    <i class="fas fa-boxes"></i>
                    <span>الطلبات</span>
                </a>

                <a href=" {{ route('orders.pay') }}" class="{{ request()->routeIs('orders.pay') ? 'active' : '' }}">
                    <i class="fas fa-check"></i>
                    <span>تشطيب</span>
                </a>

                <div class="sidebar-dropdown-container">
                    <div class="sidebar-dropdown-toggle {{ request()->routeIs('menafests.*') ? 'active' : '' }}"
                        onclick="toggleDropdown(event, this)">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <i class="fas fa-truck"></i>
                            <span>المنافست</span>
                        </div>
                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                    </div>
                    <div class="sidebar-dropdown {{ request()->routeIs('menafests.*') ? 'show' : '' }}">
                        <a href="{{ route('menafests.outgoing') }}"
                            class="{{ request()->routeIs('menafests.outgoing') ? 'active' : '' }}">
                            <i class="fas fa-arrow-right"></i>
                            <span>منافست صادر</span>
                        </a>
                        <a href="{{ route('menafests.incoming') }}"
                            class="{{ request()->routeIs('menafests.incoming') ? 'active' : '' }}">
                            <i class="fas fa-arrow-left"></i>
                            <span>منافست وارد</span>
                        </a>
                    </div>
                </div>

                <div style="margin-top: auto; padding-top: 20px;">
                    <a href="{{ route('settings.index') }}"
                        class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <i class="fas fa-cog"></i>
                        <span>الإعدادات</span>
                    </a>
                </div>
            </nav>
        </div>

        <div class="main-content">
            <div class="top-bar">
                <h1 class="page-title arabic-font">لوحة التحكم الرئيسية</h1>

                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        {{ auth()->user()->name }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile.show') }}">Profile</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="content-area">
                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Toggle dropdown manually
        function toggleDropdown(event, element) {
            event.preventDefault();
            event.stopPropagation();
            const dropdown = element.nextElementSibling;
            const container = element.closest('.sidebar-dropdown-container');

            // Close all other dropdowns first
            document.querySelectorAll('.sidebar-dropdown').forEach(d => {
                if (d !== dropdown) d.classList.remove('show');
            });

            // Toggle current dropdown
            dropdown.classList.toggle('show');

            // Update active state
            if (dropdown.classList.contains('show')) {
                container.classList.add('active');
            } else {
                container.classList.remove('active');
            }
        }

        // Handle sidebar dropdown item clicks
        document.querySelectorAll('.sidebar-dropdown a').forEach(item => {
            item.addEventListener('click', function (e) {
                if (!this.classList.contains('sidebar-dropdown-toggle')) {
                    e.preventDefault();

                    // Remove active class from all items in this dropdown
                    const dropdown = this.closest('.sidebar-dropdown');
                    dropdown.querySelectorAll('a').forEach(a => {
                        a.classList.remove('active');
                    });

                    // Add active class to clicked item
                    this.classList.add('active');

                    // Keep dropdown open
                    dropdown.classList.add('show');
                    const container = this.closest('.sidebar-dropdown-container');
                    container.classList.add('active');

                    // Navigate to the link
                    setTimeout(() => {
                        window.location.href = this.getAttribute('href');
                    }, 100);
                }
            });
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.sidebar-dropdown-container')) {
                document.querySelectorAll('.sidebar-dropdown').forEach(d => {
                    d.classList.remove('show');
                });
            }
        });

        // Mobile sidebar toggle
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
        }

        // Quick action functions (for dashboard widgets)
        function quickAction(action) {
            const actions = {
                'addUser': 'إضافة مستخدم جديد',
                'addCourse': 'إنشاء دورة جديدة',
                'addVideo': 'رفع فيديو جديد',
                'reports': 'عرض التقارير'
            };
            alert(`تم اختيار: ${actions[action]}`);
            // Here you would normally navigate to the appropriate page
        }

        // Close sidebar on mobile when clicking outside
        document.addEventListener('click', function (e) {
            const sidebar = document.querySelector('.sidebar');
            const menuToggle = document.querySelector('.menu-toggle');

            if (window.innerWidth <= 768 &&
                !sidebar.contains(e.target) &&
                !menuToggle.contains(e.target) &&
                sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        });
    </script>

    {{-- FIXED: Added @stack so child views can push scripts --}}
    @stack('scripts')
</body>

</html>