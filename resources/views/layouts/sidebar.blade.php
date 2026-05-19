<aside class="main-sidebar sidebar-dark-primary elevation-4">

    {{-- BRAND --}}
    <a href="/dashboard" class="brand-link">
        <span class="brand-text font-weight-light">Zam Holdings ERP</span>
    </a>

    {{-- SIDEBAR --}}
    <div class="sidebar">

        {{-- USER PANEL --}}
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="info">
                <a href="#" class="d-block">{{ Auth::user()->name ?? 'Admin' }}</a>
            </div>
        </div>

        {{-- MENU --}}
        <nav class="mt-2">

            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">

                {{-- DASHBOARD --}}
                <li class="nav-item">
                    <a href="/dashboard" class="nav-link">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                {{-- MASTER DATA --}}
                <li class="nav-header">MASTER DATA</li>

                <li class="nav-item">
                    <a href="/categories" class="nav-link">
                        <i class="nav-icon fas fa-list"></i>
                        <p>Categories</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="/items" class="nav-link">
                        <i class="nav-icon fas fa-box"></i>
                        <p>Items</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="/suppliers" class="nav-link">
                        <i class="nav-icon fas fa-truck"></i>
                        <p>Suppliers</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="/customers" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Customers</p>
                    </a>
                </li>

                {{-- TRANSACTIONS --}}
                <li class="nav-header">TRANSACTIONS</li>

                <li class="nav-item">
                    <a href="/purchases" class="nav-link">
                        <i class="nav-icon fas fa-shopping-cart"></i>
                        <p>Purchases</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="/sales" class="nav-link">
                        <i class="nav-icon fas fa-cash-register"></i>
                        <p>Sales</p>
                    </a>
                </li>

                {{-- REPORTS --}}
                <li class="nav-header">REPORTS</li>

                <li class="nav-item">
                    <a href="/reports/sales" class="nav-link">
                        <i class="nav-icon fas fa-chart-line"></i>
                        <p>Sales Report</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="/reports/purchases" class="nav-link">
                        <i class="nav-icon fas fa-chart-bar"></i>
                        <p>Purchase Report</p>
                    </a>
                </li>

            </ul>

        </nav>

    </div>

</aside>