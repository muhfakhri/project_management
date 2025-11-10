<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Dashboard')</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <!-- Toast Notifications -->
  <link href="{{ asset('css/toast.css') }}" rel="stylesheet" />

  <style>
    /* Universal box-sizing */
    *, *::before, *::after {
      box-sizing: border-box;
      max-width: 100%;
    }
    
    /* default navbar height fallback — JS will update this to actual height */
    :root { --navbar-height: 70px; }
    
    /* Prevent horizontal overflow at ALL levels */
    html {
      overflow-x: hidden !important;
      width: 100% !important;
      max-width: 100% !important;
    }
    
    body {
      overflow-x: hidden !important;
      width: 100% !important;
      max-width: 100% !important;
      margin: 0 !important;
      padding: 0;
      padding-top: var(--navbar-height) !important;
      font-size: 0.875rem;
      line-height: 1.5;
      box-sizing: border-box;
    }

    img, video, iframe {
      max-width: 100%;
      height: auto;
    }

    /* Main Content Container */
    .main-content {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      margin: 0;
      padding: 0;
      width: 100% !important;
      max-width: 100% !important;
      box-sizing: border-box !important;
      overflow-x: hidden !important;
      position: relative;
    }

    /* Mobile: No Sidebar, Full Width Content */
    .content {
      flex: 1 1 auto;
      margin-left: 0;
      margin-top: 0; /* spacing handled by body padding-top */
      padding: 1rem;
      padding-bottom: 70px; /* Space for footer */
      transition: margin-left 0.3s ease;
      width: 100%;
      max-width: 100%;
      box-sizing: border-box;
    }

    /* Container Fluid Fix */
    .container-fluid {
      width: 100% !important;
      max-width: 100% !important;
      padding-right: 15px !important;
      padding-left: 15px !important;
      margin-right: 0 !important;
      margin-left: 0 !important;
      box-sizing: border-box !important;
    }

    /* Bootstrap row fix */
    .row {
      margin-right: -15px !important;
      margin-left: -15px !important;
      max-width: 100% !important;
      width: 100% !important;
      box-sizing: border-box !important;
    }

    [class*="col-"] {
      padding-right: 15px !important;
      padding-left: 15px !important;
      max-width: 100% !important;
      box-sizing: border-box !important;
    }

    /* Prevent any element from causing overflow */
    .content > * {
      max-width: 100% !important;
    }
    
    .content .card,
    .content .table-responsive,
    .content .alert,
    .content .breadcrumb {
      max-width: 100% !important;
      box-sizing: border-box !important;
    }
    
    /* Ensure all children respect container width */
    .container-fluid > * {
      max-width: 100% !important;
      box-sizing: border-box !important;
    }

    /* ===================================================================
       LOKASI SETTING SIDEBAR - File: resources/views/layouts/app.blade.php
       Line: 43-56
       Untuk mengubah lebar sidebar ke kanan, edit property box-shadow
       di @media (min-width: 768px) section
       =================================================================== */
    
    /* Desktop Sidebar - Hidden on Mobile */
    .sidebar {
      background-color: #1e293b;
      color: #fff;
      width: 280px;
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      padding: 1rem;
      overflow-y: auto;
      border-right: none;
      display: none; /* Hidden on mobile */
      margin: 0;
      z-index: 1030;
    }

    .sidebar h4 {
      font-weight: bold;
      color: #f8fafc;
      margin-bottom: 1.5rem;
      font-size: 1.1rem;
    }

    .sidebar .nav-link {
      color: #cbd5e1;
      font-size: 0.9rem;
      margin-bottom: 0.35rem;
      padding: 0.625rem 0.75rem;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      border-radius: 0.375rem;
      min-height: 44px;
    }

    .sidebar .nav-link:hover {
      color: #fff;
      background-color: #334155;
    }

    .sidebar .nav-link.fw-bold,
    .sidebar .nav-link.active {
      color: #fff;
      font-weight: 600;
      background-color: #495057;
    }

    .sidebar .collapse .nav-link {
      padding-left: 2rem;
      font-size: 0.85rem;
    }

    .sidebar .nav-item .bi-chevron-down {
      transition: transform 0.3s ease;
      margin-left: auto;
    }

    .sidebar .nav-item .collapsed .bi-chevron-down {
      transform: rotate(0deg);
    }

    .sidebar .nav-item[aria-expanded="true"] .bi-chevron-down {
      transform: rotate(180deg);
    }

    /* Mobile Hamburger Dropdown Menu */
    .mobile-menu {
      display: block; /* Show on mobile */
    }

    .mobile-menu .dropdown-menu {
      width: 100%;
      max-height: 70vh;
      overflow-y: auto;
      border-radius: 0.5rem;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .mobile-menu .dropdown-item {
      padding: 0.75rem 1rem;
      font-size: 0.9rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      min-height: 44px;
    }

    .mobile-menu .dropdown-item:active {
      background-color: #495057;
    }

    .mobile-menu .dropdown-submenu {
      padding-left: 2rem;
      background-color: #f8f9fa;
    }

    /* Footer */
    .app-footer {
      width: 100% !important;
      max-width: 100% !important;
      margin: 0 !important;
      padding: 1rem !important;
      font-size: 0.8rem;
      box-sizing: border-box !important;
      margin-top: auto !important;
      flex-shrink: 0;
    }

    /* Touch-Friendly Buttons */
    .btn {
      min-height: 44px;
      padding: 0.625rem 1rem;
      font-size: 0.875rem;
    }

    .btn-sm {
      min-height: 36px;
      padding: 0.375rem 0.75rem;
      font-size: 0.8rem;
    }

    .btn-group .btn {
      min-width: 44px;
      min-height: 44px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .btn-group .btn i {
      font-size: 0.875rem;
    }

    .btn-group {
      gap: 0.25rem;
    }

    /* Responsive Tables */
    .table-responsive {
      display: block;
      width: 100%;
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
    }

    .table {
      min-width: 600px;
    }

    .table .dropdown-menu {
      z-index: 1050;
    }

    /* Avatar Circle */
    .avatar-circle {
      width: 2.5rem;
      height: 2.5rem;
      border-radius: 50%;
      background: #3b82f6; /* Solid blue - consistent */
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      font-size: 0.875rem;
      overflow: hidden; /* Clip image to circle */
    }

    .avatar-circle img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    /* Card Responsive */
    .card {
      margin-bottom: 1rem;
    }

    .card-body {
      padding: 1rem;
    }

    /* Form Controls */
    .form-control,
    .form-select {
      min-height: 44px;
      font-size: 0.875rem;
      padding: 0.625rem 0.75rem;
    }

    /* Modal Responsive */
    .modal-dialog {
      margin: 0.5rem;
      max-width: calc(100% - 1rem);
    }

    /* Typography */
    h1, .h1 { font-size: 1.75rem; }
    h2, .h2 { font-size: 1.5rem; }
    h3, .h3 { font-size: 1.25rem; }
    h4, .h4 { font-size: 1.1rem; }
    h5, .h5 { font-size: 1rem; }
    h6, .h6 { font-size: 0.875rem; }

    /* ===================================================================
       LOKASI SETTING SIDEBAR MELEBAR KE KANAN (MODE DESKTOP)
       File: resources/views/layouts/app.blade.php
       Line: 252-260
       
       CARA MENGUBAH LEBAR SIDEBAR KE KANAN:
       Edit angka "15px" di box-shadow menjadi lebih besar/kecil
       Contoh: 15px 0 0 0 #1e293b -> angka 15px adalah lebar ke kanan
       
       Warna hitam sidebar: #1e293b
       =================================================================== */
    
    /* ============================================ */
    /* BREAKPOINT: Tablets (768px+) */
    /* ============================================ */
    @media (min-width: 768px) {
      body { 
        font-size: 1rem;
        overflow-x: hidden !important;
      }
      
      /* Show Sidebar, Hide Mobile Menu */
      .sidebar {
        display: block;
        top: 0; /* keep sidebar at top of viewport */
        height: 100vh;
        position: fixed;
        left: 0;
      }
      
      .mobile-menu {
        display: none;
      }
      
      .main-content {
        margin-left: 280px !important;
        width: calc(100% - 280px) !important;
        max-width: calc(100% - 280px) !important;
        position: relative;
        left: 0;
      }
      
      .content {
        flex: 1 1 auto;
        margin-left: 0 !important;
        margin-top: 0;
        padding: 1.5rem;
        padding-bottom: 70px;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
      }
      
      .app-footer {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 1rem 1.5rem !important;
        font-size: 0.875rem;
        box-sizing: border-box !important;
      }
      
      body {
        padding-bottom: 70px; /* Space for fixed footer on desktop */
        overflow-x: hidden !important;
      }
      
      .sidebar .nav-link {
        font-size: 1rem;
        padding: 0.6rem 1rem;
      }
      
      .card-body {
        padding: 1.25rem;
      }
      
      .modal-dialog {
        margin: 1.75rem auto;
        max-width: 720px;
      }
      
      h1, .h1 { font-size: 2rem; }
      h2, .h2 { font-size: 1.75rem; }
      h3, .h3 { font-size: 1.5rem; }
      h4, .h4 { font-size: 1.25rem; }
    }

    /* ===================================================================
       LOKASI SETTING SIDEBAR MELEBAR (DESKTOP 992px+)
       File: resources/views/layouts/app.blade.php
       Line: 327-336
       Edit angka di box-shadow untuk mengubah lebar sidebar ke kanan
       =================================================================== */
    
    /* ============================================ */
    /* BREAKPOINT: Desktop (992px+) */
    /* ============================================ */
    @media (min-width: 992px) {
      .content {
        margin-left: 280px;
        margin-top: 0; /* spacing handled by body padding-top */
        padding: 1.75rem 2rem;
      }
      
      .sidebar {
        width: 280px;
        top: 0;
        height: 100vh;
      }
      
      footer,
      footer.text-center,
      footer.bg-light,
      footer.border-top {
        left: 280px !important;
        width: calc(100vw - 280px) !important;
        max-width: calc(100vw - 280px) !important;
        padding: 1rem 1.75rem !important;
      }
      
      .card-body {
        padding: 1.5rem;
      }
      
      .modal-dialog {
        max-width: 900px;
      }
    }

    /* ===================================================================
       LOKASI SETTING SIDEBAR MELEBAR (LARGE DESKTOP 1200px+)
       File: resources/views/layouts/app.blade.php
       Line: 368-383
       Edit angka di box-shadow untuk mengubah lebar sidebar ke kanan
       =================================================================== */
    
    /* ============================================ */
    /* BREAKPOINT: Large Desktop (1200px+) */
    /* ============================================ */
    @media (min-width: 1200px) {
      .content {
        margin-left: 280px;
        margin-top: 0; /* spacing handled by body padding-top */
        padding: 2rem 2.5rem;
      }
      
      .sidebar {
        width: 280px;
        padding: 1.25rem;
        top: 0;
        height: 100vh;
      }
      
      .sidebar h4 {
        font-size: 1.25rem;
        margin-bottom: 2rem;
      }
      
      .sidebar .nav-link {
        font-size: 1.0625rem;
        padding: 0.75rem 1rem;
      }
      
      footer,
      footer.text-center,
      footer.bg-light,
      footer.border-top {
        width: 100% !important;
        max-width: 100% !important;
        padding: 1rem 2rem !important;
        font-size: 0.9375rem;
        margin: 0 !important;
        box-sizing: border-box !important;
      }
      
      .modal-dialog {
        max-width: 1000px;
      }
    }
  </style>
</head>
<body>

  @include('partials.sidebar')

  <div class="main-content">
    @include('partials.navbar')

    <div class="content">
      <div class="container-fluid">
        @yield('content')
      </div>
    </div>
  </div>

  <!-- Laravel Flash Messages -->
  @if(session('success'))
    <div data-flash-success="{{ session('success') }}" style="display:none;"></div>
  @endif
  @if(session('error'))
    <div data-flash-error="{{ session('error') }}" style="display:none;"></div>
  @endif
  @if(session('warning'))
    <div data-flash-warning="{{ session('warning') }}" style="display:none;"></div>
  @endif
  @if(session('info'))
    <div data-flash-info="{{ session('info') }}" style="display:none;"></div>
  @endif

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Toast Notification System -->
  <script src="{{ asset('js/toast.js') }}"></script>

  <script>
    // Auto-wrap tables with responsive wrapper
    document.addEventListener('DOMContentLoaded', function() {
      const tables = document.querySelectorAll('table:not(.no-responsive)');
      tables.forEach(function(table) {
        if (!table.parentElement.classList.contains('table-responsive')) {
          const wrapper = document.createElement('div');
          wrapper.className = 'table-responsive';
          table.parentNode.insertBefore(wrapper, table);
          wrapper.appendChild(table);
        }
      });
    });

    // Add img-fluid class to images
    document.addEventListener('DOMContentLoaded', function() {
      const images = document.querySelectorAll('img:not(.no-fluid)');
      images.forEach(function(img) {
        if (!img.classList.contains('img-fluid')) {
          img.classList.add('img-fluid');
        }
      });
    });
    
    // Ensure body has padding matching the navbar height so content isn't covered
    (function(){
      function adjustForNavbar(){
        const nav = document.querySelector('.navbar');
        if(!nav) return;
        const h = nav.offsetHeight || 0;
          // apply to CSS variable so browser applies spacing immediately
          document.documentElement.style.setProperty('--navbar-height', h + 'px');
        // if sidebar should remain at top:0, keep it; otherwise you can uncomment to offset
        // const sidebar = document.querySelector('.sidebar');
        // if(sidebar) sidebar.style.top = h + 'px';
      }

      // adjust on load and on resize
      window.addEventListener('DOMContentLoaded', adjustForNavbar);
      window.addEventListener('load', adjustForNavbar);
      window.addEventListener('resize', function(){ setTimeout(adjustForNavbar, 50); });

      // watch for dynamic changes inside navbar (dropdowns, auth changes) and recalc
      const nav = document.querySelector('.navbar');
      if(nav && window.MutationObserver){
        const mo = new MutationObserver(function(){ setTimeout(adjustForNavbar, 20); });
        mo.observe(nav, { childList: true, subtree: true, attributes: true });
      }
    })();
  </script>

</body>
</html>
