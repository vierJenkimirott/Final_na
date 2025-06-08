<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Staff Dashboard')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/educator/educator.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .user-role {
            color: white;
            font-weight: 500;
            margin-right: 15px;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
        }
        .topbar-right {
            display: flex;
            align-items: center;
        }
    </style>
    @yield('css')
</head>
<body>

    <!-- Topbar -->
    <div class="nav-topbar">
        <img src="https://www.passerellesnumeriques.org/wp-content/uploads/2024/05/PN-Logo-English-White-Baseline.png.webp" alt="">
        <div class="topbar-right">
            <span class="user-role">Educator</span>
            <form action="{{ route('logout') }}" method="post" style="display:inline">
                @csrf
                <button type="submit">
                    <svg class="w-6 h-6 text-gray-800" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20 12H8m12 0-4 4m4-4-4-4M9 4H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h2"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>

        <!-- Sidebar -->
    <div class="nav-sidebar">
        <ul class="list-unstyled mb-0">
                <li class="p-3 {{ request()->routeIs('educator.dashboard') ? 'active' : ''}}"><a href="{{ route('educator.dashboard') }}" class="text-decoration-none"><img src="{{asset('images/dashboard.png')}}" alt=""> Dashboard</a></li>
                <li class="p-3 {{ request()->routeIs('educator.violation') ? 'active' : ''}}"><a href="{{ route('educator.violation') }}" class="text-decoration-none"><img src="{{ asset('images/warning (1).png') }}" alt=""> Violations</a></li>
                <li class="p-3 {{ request()->routeIs('educator.behavior') ? 'active' : '' }}"><a href="{{ route('educator.behavior') }}" class="text-decoration-none"><img src="{{ asset('images/online-report.png') }}" alt=""> Behavior Monitoring</a></li>

                <li class="p-3 {{ request()->routeIs('educator.manual') ? 'active' : ''}}"><a href="{{ route('educator.manual') }}" class="text-decoration-none"><img src="{{ asset('images/manual.png') }}" alt="">Student Code of Conduct</a></li>

                <!-- <div class="dropdown-container">
                    <a href="page2.html">General Behavior</a>
                    <a href="page3.html">Schedules</a>
                    <a href="page4.html">Room Rules</a>
                    <a href="page5.html">Dress Code</a>
                    <a href="page6.html">Equipment</a>
                    <a href="page7.html">Center Tasking</a>
                </div> -->
            </ul>
        </div>
        
    <!-- Main Content -->
    <div class="main-content">
        @yield('content')
    </div>
    

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const dropdownBtn = document.querySelector(".dropdown-btn");
            const dropdownContainer = document.querySelector(".dropdown-container");

            if (dropdownBtn && dropdownContainer) {
                dropdownBtn.addEventListener("click", function (event) {
                // Prevent the default link behavior
                event.preventDefault();
            
                // Toggle the dropdown visibility
                const isVisible = dropdownContainer.style.display === "block";
                dropdownContainer.style.display = isVisible ? "none" : "block";
            
                // Navigate to the first page in the dropdown (page2.html)
            })};
        });

        // Close the dropdown if the user clicks outside of it
        window.addEventListener("click", function (event) {
            if (dropdownBtn && dropdownContainer && !dropdownBtn.contains(event.target) && !dropdownContainer.contains(event.target)) {
                dropdownContainer.classList.remove("show");
            }
        });
        

    </script>
    
    <!-- Bootstrap JavaScript -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Chart.js and plugins -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@1.2.1/dist/chartjs-plugin-zoom.min.js"></script>
    <script>
        // Register Chart.js plugins globally
        if (typeof Chart !== 'undefined') {
            Chart.register(ChartDataLabels);
        }
        
    </script>
    @stack('scripts')
    @yield('scripts')
    
</body>
</html>