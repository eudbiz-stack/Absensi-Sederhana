<?php
// File: main.php
// File utama aplikasi dengan layout lengkap

session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('location: login.php');
    exit;
}

// Ambil data user dari session
$current_user = [
    'id' => $_SESSION['user_id'],
    'username' => $_SESSION['username'],
    'namalengkap' => $_SESSION['namalengkap'],
    'jabatan' => $_SESSION['jabatan']
];

// Get current page title
function getPageTitle($module = '', $action = '')
{
    if (empty($module)) return 'Dashboard';

    switch ($module) {
        case 'user':
            switch ($action) {
                case 'tambah':
                    return 'Tambah User';
                case 'ubah':
                    return 'Edit User';
                case 'detail':
                    return 'Detail User';
                default:
                    return 'Data User';
            }
        case 'absensi':
            switch ($action) {
                case 'checkin':
                    return 'Check In';
                case 'checkout':
                    return 'Check Out';
                case 'detail':
                    return 'Detail Absensi';
                case 'laporan':
                    return 'Laporan Absensi';
                default:
                    return 'Data Absensi';
            }
        default:
            return 'Dashboard';
    }
}

$current_module = $_GET['module'] ?? '';
$current_action = $_GET['action'] ?? '';
$page_title = getPageTitle($current_module, $current_action);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Sistem Absensi Karyawan">
    <meta name="author" content="">

    <title><?php echo $page_title; ?> - Sistem Absensi</title>

    <!-- Bootstrap CSS dan Font Awesome dari CDN -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #4e73df;
            --primary-dark: #375a7f;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --info-color: #36b9cc;
            --light-color: #f8f9fc;
            --dark-color: #2c3e50;
            --sidebar-width: 260px;
        }

        /* Reset default styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            background-color: #f8f9fc;
            overflow-x: hidden;
        }

        /* Main layout wrapper */
        .layout-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: linear-gradient(180deg, var(--primary-color) 10%, var(--primary-dark) 100%);
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            overflow-y: auto;
            transition: all 0.3s ease;
        }

        .sidebar .nav-item {
            margin-bottom: 0.25rem;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1rem;
            border-radius: 0.35rem;
            margin: 0 0.75rem;
            transition: all 0.3s;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
            text-decoration: none;
        }

        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .sidebar .nav-link i {
            margin-right: 0.5rem;
            width: 1rem;
            text-align: center;
        }

        .sidebar-brand {
            padding: 1.5rem 1rem;
            text-decoration: none;
            color: #fff;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: block;
        }

        .sidebar-brand:hover {
            color: #fff;
            text-decoration: none;
        }

        .sidebar-brand-text {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .sidebar-heading {
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1rem;
            color: rgba(255, 255, 255, 0.5);
            margin: 1.5rem 0.75rem 0.5rem;
        }

        .sidebar .collapse-item {
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            color: rgba(255, 255, 255, 0.7);
            border-radius: 0.25rem;
            margin: 0.1rem 0.75rem;
            transition: all 0.3s;
            text-decoration: none;
            display: block;
        }

        .sidebar .collapse-item:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
            text-decoration: none;
        }

        .sidebar .collapse-item.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.15);
            font-weight: 500;
        }

        /* Content wrapper - takes up remaining space */
        .content-wrapper {
            flex: 1;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: #f8f9fc;
        }

        /* Topbar */
        .topbar {
            background-color: #fff;
            border-bottom: 1px solid #e3e6f0;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            padding: 0.5rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .topbar .navbar-brand {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark-color);
        }

        .topbar .nav-link {
            color: var(--secondary-color);
            padding: 0.5rem 1rem;
            border-radius: 0.35rem;
            transition: all 0.3s;
        }

        .topbar .nav-link:hover {
            color: var(--primary-color);
            background-color: var(--light-color);
        }

        /* User dropdown */
        .user-dropdown .dropdown-toggle::after {
            display: none;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid var(--light-color);
        }

        /* Main content area */
        .main-content {
            flex: 1;
            padding: 1.5rem;
            background-color: #f8f9fc;
        }

        /* Breadcrumb */
        .breadcrumb {
            background-color: transparent;
            padding: 0;
            margin-bottom: 1.5rem;
        }

        .breadcrumb-item {
            font-size: 0.875rem;
        }

        .breadcrumb-item.active {
            color: var(--secondary-color);
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem 0.5rem 0 0;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Footer */
        .footer {
            background-color: #fff;
            border-top: 1px solid #e3e6f0;
            padding: 1rem 1.5rem;
            margin-top: auto;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: calc(var(--sidebar-width) * -1);
                transition: margin-left 0.3s ease;
            }

            .sidebar.sidebar-toggled {
                margin-left: 0;
            }

            .content-wrapper {
                margin-left: 0;
                width: 100%;
            }

            .main-content {
                padding: 1rem;
            }

            .sidebar-toggle {
                display: inline-block;
            }

            /* Overlay for mobile */
            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 999;
                display: none;
            }

            .sidebar-overlay.show {
                display: block;
            }
        }

        @media (min-width: 769px) {
            .sidebar-toggle {
                display: none;
            }
        }

        /* Utility classes */
        .border-left-primary {
            border-left: 0.25rem solid var(--primary-color);
        }

        .border-left-success {
            border-left: 0.25rem solid var(--success-color);
        }

        .border-left-warning {
            border-left: 0.25rem solid var(--warning-color);
        }

        .border-left-info {
            border-left: 0.25rem solid var(--info-color);
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .text-success {
            color: var(--success-color) !important;
        }

        .text-warning {
            color: var(--warning-color) !important;
        }

        .text-danger {
            color: var(--danger-color) !important;
        }

        .text-info {
            color: var(--info-color) !important;
        }

        /* Table styles for better appearance */
        .table-responsive {
            border-radius: 0.5rem;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05rem;
            padding: 1rem 0.75rem;
        }

        .table td {
            padding: 0.75rem;
            vertical-align: middle;
        }

        /* Button improvements */
        .btn {
            border-radius: 0.35rem;
            font-weight: 500;
            padding: 0.375rem 1rem;
            transition: all 0.3s ease;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        /* Form improvements */
        .form-control {
            border-radius: 0.35rem;
            border: 1px solid #d1d3e2;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        /* Badge improvements */
        .badge {
            font-weight: 500;
            padding: 0.25rem 0.6rem;
            border-radius: 0.25rem;
        }

        .badge-pink {
            color: #fff;
            background-color: #e83e8c;
        }

        /* Pagination improvements */
        .pagination {
            border-radius: 0.35rem;
        }

        .page-link {
            border-radius: 0.35rem;
            margin: 0 0.125rem;
            border: 1px solid #d1d3e2;
            color: var(--primary-color);
        }

        .page-link:hover {
            background-color: var(--light-color);
            border-color: var(--primary-color);
        }

        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        /* Quick action buttons */
        .quick-actions {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 1050;
        }

        .quick-actions .btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-bottom: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            transition: all 0.3s;
        }

        .quick-actions .btn:hover {
            transform: scale(1.1);
        }

        /* Loading spinner */
        .spinner-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            display: none;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
            color: var(--primary-color);
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <nav class="sidebar navbar-nav" id="sidebar">
        <!-- Sidebar Brand -->
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="main.php">
            <div class="sidebar-brand-icon rotate-n-15 mr-2">
                <i class="fas fa-clock"></i>
            </div>
            <div class="sidebar-brand-text">Sistem Absensi</div>
        </a>

        <!-- Divider -->
        <hr class="sidebar-divider my-3" style="border-color: rgba(255,255,255,0.1);">

        <!-- Nav Item - Dashboard -->
        <li class="nav-item">
            <a class="nav-link <?php echo (empty($current_module)) ? 'active' : ''; ?>" href="main.php">
                <i class="fas fa-fw fa-tachometer-alt mr-2"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider" style="border-color: rgba(255,255,255,0.1);">

        <!-- Heading -->
        <div class="sidebar-heading">Manajemen</div>

        <!-- Nav Item - Data User -->
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_module == 'user') ? 'active' : ''; ?>" href="main.php?module=user">
                <i class="fas fa-fw fa-users mr-2"></i>
                <span>Data Karyawan</span>
            </a>
        </li>

        <!-- Nav Item - Absensi -->
        <li class="nav-item">
            <a class="nav-link collapsed <?php echo ($current_module == 'absensi') ? 'active' : ''; ?>"
                href="#" data-toggle="collapse" data-target="#collapseAbsensi"
                aria-expanded="<?php echo ($current_module == 'absensi') ? 'true' : 'false'; ?>">
                <i class="fas fa-fw fa-clock mr-2"></i>
                <span>Absensi</span>
                <i class="fas fa-chevron-down ml-auto"></i>
            </a>
            <div id="collapseAbsensi" class="collapse <?php echo ($current_module == 'absensi') ? 'show' : ''; ?>">
                <div class="py-2">
                    <a class="collapse-item <?php echo ($current_module == 'absensi' && empty($current_action)) ? 'active' : ''; ?>"
                        href="main.php?module=absensi">
                        <i class="fas fa-list mr-2"></i>Data Absensi
                    </a>
                    <a class="collapse-item <?php echo ($current_action == 'checkin') ? 'active' : ''; ?>"
                        href="main.php?module=absensi&action=checkin">
                        <i class="fas fa-sign-in-alt mr-2"></i>Check In
                    </a>
                    <a class="collapse-item <?php echo ($current_action == 'checkout') ? 'active' : ''; ?>"
                        href="main.php?module=absensi&action=checkout">
                        <i class="fas fa-sign-out-alt mr-2"></i>Check Out
                    </a>
                    <a class="collapse-item <?php echo ($current_action == 'laporan') ? 'active' : ''; ?>"
                        href="main.php?module=absensi&action=laporan">
                        <i class="fas fa-chart-bar mr-2"></i>Laporan
                    </a>
                </div>
            </div>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider d-none d-md-block" style="border-color: rgba(255,255,255,0.1);">

        <!-- Sidebar Toggle Button -->
        <div class="text-center d-none d-md-inline mt-3">
            <button class="btn btn-link text-white" id="sidebarToggle">
                <i class="fas fa-angle-left"></i>
            </button>
        </div>
    </nav>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Topbar -->
        <nav class="navbar navbar-expand topbar">
            <!-- Sidebar Toggle (Mobile) -->
            <button class="btn btn-link d-md-none sidebar-toggle" id="sidebarToggleTop">
                <i class="fa fa-bars"></i>
            </button>

            <!-- Breadcrumb -->
            <ol class="breadcrumb mb-0 ml-3">
                <li class="breadcrumb-item">
                    <a href="main.php">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
                <?php if (!empty($current_module)) : ?>
                    <li class="breadcrumb-item">
                        <a href="main.php?module=<?php echo $current_module; ?>">
                            <?php echo ucfirst($current_module); ?>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if (!empty($current_action)) : ?>
                    <li class="breadcrumb-item active">
                        <?php echo ucfirst($current_action); ?>
                    </li>
                <?php endif; ?>
            </ol>

            <!-- Topbar Navbar -->
            <ul class="navbar-nav ml-auto">
                <!-- Clock Display -->
                <li class="nav-item">
                    <span class="nav-link text-primary font-weight-bold" id="realtime-clock">
                        <i class="fas fa-clock mr-1"></i>
                        <span id="current-time"></span>
                    </span>
                </li>

                <!-- User Information -->
                <li class="nav-item dropdown no-arrow ml-3">
                    <a class="nav-link dropdown-toggle user-dropdown" href="#" id="userDropdown"
                        role="button" data-toggle="dropdown">
                        <div class="user-info">
                            <div>
                                <div class="text-dark font-weight-bold text-sm">
                                    <?php echo htmlspecialchars($current_user['namalengkap']); ?>
                                </div>
                                <div class="text-muted text-xs">
                                    <?php echo htmlspecialchars($current_user['jabatan']); ?>
                                </div>
                            </div>
                            <img class="user-avatar"
                                src="https://ui-avatars.com/api/?name=<?php echo urlencode($current_user['namalengkap']); ?>&background=4e73df&color=ffffff">
                        </div>
                    </a>
                    <!-- Dropdown - User Information -->
                    <div class="dropdown-menu dropdown-menu-right shadow">
                        <div class="dropdown-header">
                            <strong><?php echo htmlspecialchars($current_user['namalengkap']); ?></strong>
                            <br><small class="text-muted"><?php echo htmlspecialchars($current_user['jabatan']); ?></small>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">
                            <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                            Profil Saya
                        </a>
                        <a class="dropdown-item" href="#">
                            <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                            Pengaturan
                        </a>
                        <a class="dropdown-item" href="main.php?module=absensi">
                            <i class="fas fa-clock fa-sm fa-fw mr-2 text-gray-400"></i>
                            Riwayat Absensi
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                            Logout
                        </a>
                    </div>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <?php
            // Route untuk menampilkan halaman sesuai module
            if (isset($_GET['module'])) {
                $module = $_GET['module'];

                switch ($module) {
                    case 'user':
                        include "modules/user/user.php";
                        break;
                    case 'absensi':
                        include "modules/absensi/absensi.php";
                        break;
                    default:
                        include "dashboard.php";
                        break;
                }
            } else {
                // Default halaman adalah dashboard
                include "dashboard.php";
            }
            ?>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="copyright text-muted">
                            <span>&copy; <?php echo date('Y'); ?> Sistem Absensi. All rights reserved.</span>
                        </div>
                    </div>
                    <div class="col-md-6 text-right">
                        <div class="text-muted small">
                            Version 1.0 | Last login: <?php echo date('d M Y, H:i'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Quick Actions (Mobile) -->
    <div class="quick-actions d-md-none">
        <a href="main.php?module=absensi&action=checkin" class="btn btn-success">
            <i class="fas fa-sign-in-alt"></i>
        </a>
        <a href="main.php?module=absensi&action=checkout" class="btn btn-warning">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>

    <!-- Logout Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-sign-out-alt mr-2"></i>Konfirmasi Logout
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin logout dari sistem?</p>
                    <small class="text-muted">Pastikan Anda sudah menyimpan semua pekerjaan.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Batal
                    </button>
                    <a href="logout.php" class="btn btn-primary">
                        <i class="fas fa-sign-out-alt mr-1"></i>Ya, Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <script>
        // Real-time clock
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            const dateString = now.toLocaleDateString('id-ID', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
            document.getElementById('current-time').innerHTML = `${timeString}<br><small class="text-xs">${dateString}</small>`;
        }

        // Update clock every second
        setInterval(updateClock, 1000);
        updateClock(); // Initial call

        // Sidebar toggle for mobile
        $('#sidebarToggleTop').click(function() {
            $('#sidebar').toggleClass('active');
        });

        // Sidebar toggle for desktop
        $('#sidebarToggle').click(function() {
            $('body').toggleClass('sidebar-collapsed');
            if ($('body').hasClass('sidebar-collapsed')) {
                $('.sidebar').css('margin-left', '-' + getComputedStyle(document.documentElement).getPropertyValue('--sidebar-width'));
                $('.content-wrapper').css('margin-left', '0');
                $(this).find('i').removeClass('fa-angle-left').addClass('fa-angle-right');
            } else {
                $('.sidebar').css('margin-left', '0');
                $('.content-wrapper').css('margin-left', getComputedStyle(document.documentElement).getPropertyValue('--sidebar-width'));
                $(this).find('i').removeClass('fa-angle-right').addClass('fa-angle-left');
            }
        });

        // Close sidebar when clicking outside (mobile)
        $(document).click(function(e) {
            if ($(window).width() <= 768) {
                if (!$(e.target).closest('#sidebar, #sidebarToggleTop').length) {
                    $('#sidebar').removeClass('active');
                }
            }
        });

        // Tooltip initialization
        $('[data-toggle="tooltip"]').tooltip();

        // Auto-hide alerts
        $('.alert').delay(5000).fadeOut();
    </script>
</body>

</html>