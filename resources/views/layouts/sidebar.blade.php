<aside class="main-sidebar sidebar-dark-primary elevation-4">

    {{-- BRAND --}}
    <a href="/dashboard" class="brand-link">
        <span class="brand-text font-weight-light">Zam Holdings ERP</span>
    </a>

    <div class="sidebar">

        <nav class="mt-2">

            <ul class="nav nav-pills nav-sidebar flex-column text-sm"
                data-widget="treeview"
                role="menu"
                data-accordion="false">
                {{-- DASHBOARD --}}
                <li class="nav-item has-treeview
                    {{ request()->is('dashboard*') || request()->is('reports.supplier-summary') ? 'menu-open' : '' }}">

                    <a href="#"
                        class="nav-link
                       {{ request()->is('dashboard*') || request()->is('reports.supplier-summary') ? 'active' : '' }}">

                        <i class="nav-icon fas fa-tachometer-alt text-primary"></i>

                        <p>
                            Dashboard
                            <i class="right fas fa-angle-left"></i>
                        </p>

                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">
                            <a href="/Operational "
                                class="nav-link {{ request()->is('Operational ') ? 'active' : '' }}">

                                <i class="nav-icon fas fa-tachometer-alt text-info"></i>
                                <p>Operational </p>

                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/dashboard"
                                class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">

                                <i class="nav-icon fas fa-tachometer-alt text-info"></i>
                                <p>Financial</p>

                            </a>

                        </li>
                    </ul>

                </li>

                {{-- CUSTOMERS --}}
                <li class="nav-item has-treeview
                    {{ request()->is('customers*') || request()->is('customers*') ? 'menu-open' : '' }}">

                    <a href="#"
                        class="nav-link
                       {{ request()->is('customers*') || request()->is('customers*') ? 'active' : '' }}">

                        <i class="nav-icon fas fa-shopping-cart text-primary"></i>

                        <p>
                            Customers & Receivables
                            <i class="right fas fa-angle-left"></i>
                        </p>

                    </a>

                    <ul class="nav nav-treeview">
                        <li class="nav-item">

                            <a href="/customers/local"
                                class="nav-link {{ request()->is('customers/local') ? 'active' : '' }}">

                                <i class="far fa-circle nav-icon text-info"></i>
                                <p>Local Customer Statements</p>

                            </a>

                        </li>

                        <li class="nav-item">

                            <a href="{{ route('reports.customer-summary-local') }}"
                                class="nav-link {{ request()->routeIs('reports.customer-summary-local') ? 'active' : '' }}">

                                <i class="far fa-circle nav-icon text-success"></i>
                                <p>Local Customer Outstanding Balances</p>

                            </a>

                        </li>

                        <li class="nav-item">

                            <a href="/customers/export"
                                class="nav-link {{ request()->is('customers/export') ? 'active' : '' }}">

                                <i class="far fa-circle nav-icon text-primary"></i>
                                <p>Export Customer Statements</p>

                            </a>

                        </li>

                        <li class="nav-item">

                            <a href="{{ route('reports.customer-summary-export') }}"
                                class="nav-link {{ request()->routeIs('reports.customer-summary-export') ? 'active' : '' }}">

                                <i class="far fa-circle nav-icon text-success"></i>
                                <p>Export Customer Outstanding Balances
                                </p>

                            </a>

                        </li>

                    </ul>

                </li>

                {{-- SUPPLIERS --}}
                <li class="nav-item has-treeview
                    {{ request()->is('suppliers*') || request()->is('reports.supplier-summary') ? 'menu-open' : '' }}">

                    <a href="#"
                        class="nav-link
                       {{ request()->is('suppliers*') || request()->is('reports.supplier-summary') ? 'active' : '' }}">

                        <i class="nav-icon fas fa-shopping-cart text-primary"></i>

                        <p>
                            Suppliers & Payables
                            <i class="right fas fa-angle-left"></i>
                        </p>

                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">
                            <a href="/suppliers" class="nav-link {{ request()->is('suppliers') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-truck text-warning"></i>
                                <p>Supplier Statements</p>
                            </a>
                        </li>

                        <li class="nav-item">

                            <a href="{{ route('reports.supplier-summary') }}"
                                class="nav-link {{ request()->routeIs('reports.supplier-summary') ? 'active' : '' }}">

                                <i class="far fa-circle nav-icon text-warning"></i>
                                <p>Supplier Outstanding Balances</p>
                            </a>
                        </li>
                    </ul>

                </li>

                {{-- PURCHASES --}}
                <li class="nav-item has-treeview
                    {{ request()->is('purchases*') || request()->is('stock-summary*') ? 'menu-open' : '' }}">

                    <a href="#"
                        class="nav-link
                       {{ request()->is('purchases*') || request()->is('stock-summary*') ? 'active' : '' }}">

                        <i class="nav-icon fas fa-shopping-cart text-primary"></i>

                        <p>
                            Purchasing
                            <i class="right fas fa-angle-left"></i>
                        </p>

                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">

                            <a href="/purchases"
                                class="nav-link {{ request()->is('purchases*') ? 'active' : '' }}">

                                <i class="far fa-circle nav-icon text-primary"></i>
                                <p>Purchase Entry</p>

                            </a>

                        </li>

                        <li class="nav-item">

                            <a href="/stock-summary"
                                class="nav-link {{ request()->is('stock-summary*') ? 'active' : '' }}">

                                <i class="far fa-circle nav-icon text-info"></i>
                                <p>Purchase & Stock Summary</p>

                            </a>

                        </li>

                    </ul>

                </li>

                {{-- SALES --}}
                <li class="nav-item has-treeview
                    {{ request()->is('sales*') || request()->is('export-sales*') ? 'menu-open' : '' }}">

                    <a href="#"
                        class="nav-link
                       {{ request()->is('sales*') || request()->is('export-sales*') ? 'active' : '' }}">

                        <i class="nav-icon fas fa-cash-register text-success"></i>

                        <p>
                            Sales Management
                            <i class="right fas fa-angle-left"></i>
                        </p>

                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">

                            <a href="/sales"
                                class="nav-link {{ request()->is('sales*') && !request()->is('export-sales*') ? 'active' : '' }}">

                                <i class="fas fa-store nav-icon text-success"></i>
                                <p>Local Sales</p>

                            </a>

                        </li>

                        <li class="nav-item">

                            <a href="/export-sales/"
                                class="nav-link {{ request()->is('export-sales*') ? 'active' : '' }}">

                                <i class="fas fa-globe nav-icon text-primary"></i>
                                <p>Export Sales</p>

                            </a>
                        </li>
                    </ul>

                </li>


                {{-- INVENTORY --}}
                <li class="nav-item has-treeview
                    {{ request()->is('purchase-inventories*') || request()->is('inventory-summary*') ? 'menu-open' : '' }}">

                    <a href="#"
                        class="nav-link
                       {{ request()->is('purchase-inventories*') || request()->is('inventory-summary*') ? 'active' : '' }}">

                        <i class="nav-icon fas fa-boxes text-success"></i>

                        <p>
                            Inventory Management
                            <i class="right fas fa-angle-left"></i>
                        </p>

                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">

                            <a href="{{ route('purchase-inventories.index') }}"
                                class="nav-link {{ request()->routeIs('purchase-inventories.*') ? 'active' : '' }}">

                                <i class="far fa-circle nav-icon text-success"></i>
                                <p>Packing Material Purchases</p>

                            </a>

                        </li>

                        <li class="nav-item">

                            <a href="{{ route('inventory.summary') }}"
                                class="nav-link {{ request()->routeIs('inventory.summary') ? 'active' : '' }}">

                                <i class="far fa-circle nav-icon text-info"></i>
                                <p>Packing Material Stock Position</p>

                            </a>

                        </li>

                        <li class="nav-item">

                            <a href="/packing-usages"
                                class="nav-link {{ request()->is('packing-usages*') ? 'active' : '' }}">

                                <i class="nav-icon fas fa-boxes text-warning"></i>
                                <p>Packing Material Usage</p>

                            </a>

                        </li>


                    </ul>

                </li>

                {{-- EXPENSE --}}
                <li class="nav-item has-treeview
                    {{ request()->routeIs('expenses.*') || request()->routeIs('expense-categories.*') ? 'menu-open' : '' }}">

                    <a href="#"
                        class="nav-link
                       {{ request()->routeIs('expenses.*') || request()->routeIs('expense-categories.*') ? 'active' : '' }}">

                        <i class="nav-icon fas fa-receipt text-danger"></i>

                        <p>
                            Expense Management
                            <i class="right fas fa-angle-left"></i>
                        </p>

                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">

                            <a href="{{ route('expenses.index') }}"
                                class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}">

                                <i class="far fa-circle nav-icon text-danger"></i>
                                <p>Expense Entry</p>

                            </a>

                        </li>

                        <li class="nav-item">

                            <a href="{{ route('expense-categories.index') }}"
                                class="nav-link {{ request()->routeIs('expense-categories.*') ? 'active' : '' }}">

                                <i class="far fa-circle nav-icon text-warning"></i>
                                <p>Expense Statements</p>

                            </a>

                        </li>

                    </ul>

                </li>

                {{-- FREIGHT SERVICES --}}
                <li class="nav-item has-treeview
                    {{ request()->is('freight-services*') || request()->is('freights*') ? 'menu-open' : '' }}">

                    <a href="#"
                        class="nav-link
                       {{ request()->is('freight-services*') || request()->is('freights*') ? 'active' : '' }}">

                        <i class="nav-icon fas fa-ship text-primary"></i>

                        <p>
                            Freight Management
                            <i class="right fas fa-angle-left"></i>
                        </p>

                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">

                            <a href="/freight-services"
                                class="nav-link {{ request()->is('freight-services*') ? 'active' : '' }}">

                                <i class="fas fa-concierge-bell nav-icon text-info"></i>
                                <p>Freight Provider Statements</p>

                            </a>

                        </li>

                        <li class="nav-item">

                            <a href="/freights"
                                class="nav-link {{ request()->is('freights*') ? 'active' : '' }}">

                                <i class="fas fa-file-invoice-dollar nav-icon text-warning"></i>
                                <p>Freight & Handling Charges</p>

                            </a>

                        </li>

                        <li class="nav-item">

                            <a href="/landing-costs"
                                class="nav-link {{ request()->is('landing-costs*') ? 'active' : '' }}">

                                <i class="far fa-circle nav-icon text-warning"></i>
                                <p>Freight Cost Analysis</p>

                            </a>

                        </li>

                    </ul>

                </li>



                <li class="nav-item">
                    <a href="{{ route('cashbooks.index') }}"
                        class="nav-link {{ request()->routeIs('cashbooks.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-book"></i>
                        <p> Cash Transfers & Other Payments</p>
                    </a>
                </li>

                <li class="nav-item has-treeview
                    {{ request()->is('freight-services*') || request()->is('freights*') ? 'menu-open' : '' }}">

                    <a href="#"
                        class="nav-link
                       {{ request()->is('freight-services*') || request()->is('freights*') ? 'active' : '' }}">

                        <i class="nav-icon fas fa-ship text-primary"></i>

                        <p>
                            Salary Advances
                            <i class="right fas fa-angle-left"></i>
                        </p>

                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">
                            <a href="{{ route('salary-advances.index') }}"
                                class="nav-link {{ request()->routeIs('salary-advances.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-hand-holding-usd"></i>
                                <p>Salary Advance Entry</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('salary-advances.index') }}"
                                class="nav-link {{ request()->routeIs('salary-advances.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-hand-holding-usd"></i>
                                <p>Salary Advance Statement</p>
                            </a>
                        </li>
                    </ul>

                </li>

                <li class="nav-item">
                    <a href="{{ route('fixed-assets.index') }}"
                        class="nav-link {{ request()->routeIs('fixed-assets.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-building text-primary"></i>
                        <p>Fixed Assets</p>
                    </a>
                </li>

                {{-- ACCOUNTING --}}
                <li class="nav-item has-treeview
                    {{ request()->routeIs('accounting.*') ? 'menu-open' : '' }}">

                    <a href="#"
                        class="nav-link
                       {{ request()->routeIs('accounting.*') ? 'active' : '' }}">

                        <i class="nav-icon fas fa-calculator text-warning"></i>

                        <p>
                            Finance & Accounting
                            <i class="right fas fa-angle-left"></i>
                        </p>

                    </a>

                    <ul class="nav nav-treeview">

                        {{-- CHART OF ACCOUNTS --}}
                        <li class="nav-item">

                            <a href="{{ route('accounting.chart-of-accounts.index') }}"
                                class="nav-link
                               {{ request()->routeIs('accounting.chart-of-accounts.*') ? 'active' : '' }}">

                                <i class="fas fa-sitemap nav-icon text-primary"></i>

                                <p>
                                    Chart of Accounts
                                </p>

                            </a>

                        </li>


                        {{-- ACCOUNT GROUPS --}}
                        <li class="nav-item">

                            <a href="{{ route('accounting.account-groups.index') }}"
                                class="nav-link
                               {{ request()->routeIs('accounting.account-groups.*') ? 'active' : '' }}">

                                <i class="fas fa-layer-group nav-icon text-info"></i>

                                <p>
                                    Account Groups
                                </p>

                            </a>

                        </li>


                        {{-- LEDGERS --}}
                        <li class="nav-item">

                            <a href="{{ route('accounting.ledgers.index') }}"
                                class="nav-link
                               {{ request()->routeIs('accounting.ledgers.*') ? 'active' : '' }}">

                                <i class="fas fa-book-open nav-icon text-success"></i>

                                <p>
                                    Ledgers
                                </p>

                            </a>

                        </li>


                        {{-- SUB LEDGERS --}}
                        <li class="nav-item">

                            <a href="{{ route('accounting.sub-ledgers.index') }}"
                                class="nav-link
                               {{ request()->routeIs('accounting.sub-ledgers.*') ? 'active' : '' }}">

                                <i class="fas fa-list-ol nav-icon text-warning"></i>

                                <p>
                                    Sub Ledgers
                                </p>

                            </a>

                        </li>
                        <li class="nav-item">
                            <a href="{{ route('account-setup.create') }}"
                                class="nav-link {{ request()->routeIs('account-setup.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-sitemap"></i>
                                <p>Account Setup</p>
                            </a>
                        </li>

                        <li class="nav-item">

                            <a href="{{ route('accounting.journal-entries.index') }}"
                                class="nav-link
                       {{ request()->routeIs('accounting.journal-entries.*') ? 'active' : '' }}">

                                <i class="nav-icon fas fa-book text-warning"></i>

                                <p>
                                    Journal Entries
                                </p>

                            </a>

                        </li>

                    </ul>

                </li>

                {{-- REPORTS --}}
                {{-- 13. REPORTS & ANALYTICS --}}
                @php
                $salesProfitabilityActive =
                request()->is('sales-profit-loss*') ||
                request()->is('reports/invoice-profit-analysis') ||
                request()->routeIs('reports.item-profit-analysis');

                $shipmentOperationalActive =
                request()->is('shipment-plans*') ||
                request()->is('packings*') ||
                request()->is('reports/branch-comparison') ||
                request()->is('freight-records*');

                $financialReportsActive =
                request()->is('reports/trial-balance*') ||
                request()->is('reports/balance-sheet*') ||
                request()->routeIs('reports.profit-loss') ||
                request()->routeIs('statements.*') ||
                request()->routeIs('reports.general-ledger');

                $reportsActive =
                $salesProfitabilityActive ||
                $shipmentOperationalActive ||
                $financialReportsActive;
                @endphp

                <li class="nav-item has-treeview {{ $reportsActive ? 'menu-open' : '' }}">

                    <a href="#" class="nav-link {{ $reportsActive ? 'active' : '' }}">
                        <i class="nav-icon fas fa-chart-bar text-success"></i>
                        <p>
                            Reports & Analytics
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">

                        {{-- 13.1 SALES & PROFITABILITY REPORTS --}}
                        <li class="nav-item has-treeview {{ $salesProfitabilityActive ? 'menu-open' : '' }}">

                            <a href="#"
                                class="nav-link {{ $salesProfitabilityActive ? 'active' : '' }}">
                                <i class="nav-icon fas fa-chart-line text-warning"></i>
                                <p>
                                    Sales & Profitability Reports
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>

                            <ul class="nav nav-treeview">

                                <li class="nav-item">
                                    <a href="/sales-profit-loss/"
                                        class="nav-link {{ request()->is('sales-profit-loss*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon text-warning"></i>
                                        <p>Local Sales Profitability Analysis</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="/reports/invoice-profit-analysis"
                                        class="nav-link {{ request()->is('reports/invoice-profit-analysis') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon text-success"></i>
                                        <p>Export Sales Profitability Analysis</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('reports.item-profit-analysis') }}"
                                        class="nav-link {{ request()->routeIs('reports.item-profit-analysis') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon text-success"></i>
                                        <p>Item Profitability Analysis</p>
                                    </a>
                                </li>

                            </ul>
                        </li>

                        {{-- 13.2 SHIPMENT & OPERATIONAL REPORTS --}}
                        <li class="nav-item has-treeview {{ $shipmentOperationalActive ? 'menu-open' : '' }}">

                            <a href="#"
                                class="nav-link {{ $shipmentOperationalActive ? 'active' : '' }}">
                                <i class="nav-icon fas fa-shipping-fast text-info"></i>
                                <p>
                                    Shipment & Operational Reports
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>

                            <ul class="nav nav-treeview">

                                <li class="nav-item">
                                    <a href="/shipment-plans"
                                        class="nav-link {{ request()->is('shipment-plans*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon text-info"></i>
                                        <p>Shipment Planning</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="/packings"
                                        class="nav-link {{ request()->is('packings*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon text-warning"></i>
                                        <p>Shipment Weight Verification</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="/reports/branch-comparison"
                                        class="nav-link {{ request()->is('reports/branch-comparison') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon text-info"></i>
                                        <p>Branch Weight Comparison</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="/freight-records"
                                        class="nav-link {{ request()->is('freight-records*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon text-info"></i>
                                        <p>Freight Records</p>
                                    </a>
                                </li>

                            </ul>
                        </li>

                        {{-- 13.3 FINANCIAL REPORTS --}}
                        <li class="nav-item has-treeview {{ $financialReportsActive ? 'menu-open' : '' }}">

                            <a href="#"
                                class="nav-link {{ $financialReportsActive ? 'active' : '' }}">
                                <i class="nav-icon fas fa-calculator text-primary"></i>
                                <p>
                                    Financial Reports
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>

                            <ul class="nav nav-treeview">

                                <li class="nav-item">
                                    <a href="/reports/trial-balance"
                                        class="nav-link {{ request()->is('reports/trial-balance*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon text-primary"></i>
                                        <p>Trial Balance</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="/reports/balance-sheet"
                                        class="nav-link {{ request()->is('reports/balance-sheet*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon text-info"></i>
                                        <p>Balance Sheet</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('reports.profit-loss') }}"
                                        class="nav-link {{ request()->routeIs('reports.profit-loss') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon text-success"></i>
                                        <p>Profit & Loss Statement</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('statements.index') }}"
                                        class="nav-link {{ request()->routeIs('statements.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon text-success"></i>
                                        <p>Statement of Account</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('reports.general-ledger') }}"
                                        class="nav-link {{ request()->routeIs('reports.general-ledger') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon text-info"></i>
                                        <p>General Ledger Report</p>
                                    </a>
                                </li>

                            </ul>
                        </li>

                    </ul>
                </li>

                {{-- MASTER DATA --}}
                <li class="nav-item has-treeview
                    {{ request()->is('categories*') || request()->is('items*') ? 'menu-open' : '' }}">

                    <a href="#"
                        class="nav-link
                       {{ request()->is('categories*') || request()->is('items*') ? 'active' : '' }}">

                        <i class="nav-icon fas fa-database text-info"></i>

                        <p>
                            Master Data
                            <i class="right fas fa-angle-left"></i>
                        </p>

                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">

                            <a href="/categories"
                                class="nav-link {{ request()->is('categories*') ? 'active' : '' }}">

                                <i class="fas fa-tags nav-icon text-warning"></i>
                                <p>Categories</p>

                            </a>

                        </li>

                        <li class="nav-item">

                            <a href="/items"
                                class="nav-link {{ request()->is('items*') ? 'active' : '' }}">

                                <i class="fas fa-box nav-icon text-success"></i>
                                <p>Items</p>

                            </a>

                        </li>

                    </ul>

                </li>

                <li class="nav-item has-treeview
                    {{ request()->is('categories*') || request()->is('items*') ? 'menu-open' : '' }}">

                    <a href="#"
                        class="nav-link
                       {{ request()->is('categories*') || request()->is('items*') ? 'active' : '' }}">

                        <i class="nav-icon fas fa-database text-info"></i>

                        <p>
                            Human Resources
                            <i class="right fas fa-angle-left"></i>
                        </p>

                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">
                            <a href="{{ route('employees.index') }}"
                                class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Employee Management</p>
                            </a>
                        </li>
                    </ul>

                </li>

                <li class="nav-item has-treeview
                    {{ request()->is('categories*') || request()->is('items*') ? 'menu-open' : '' }}">

                    <a href="#"
                        class="nav-link
                       {{ request()->is('categories*') || request()->is('items*') ? 'active' : '' }}">

                        <i class="nav-icon fas fa-database text-info"></i>

                        <p>
                            System Administration
                            <i class="right fas fa-angle-left"></i>
                        </p>

                    </a>

                    <ul class="nav nav-treeview">
                        @can('roles.manage')
                        <li class="nav-item">
                            <a href="{{ route('roles.index') }}"
                                class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">

                                <i class="nav-icon fas fa-user-shield text-warning"></i>
                                <p>Role Management</p>
                            </a>
                        </li>
                        @endcan

                        @can('branches.manage')
                        <li class="nav-item">
                            <a href="{{ route('branches.index') }}"
                                class="nav-link {{ request()->routeIs('branches.*') ? 'active' : '' }}">

                                <i class="nav-icon fas fa-building text-info"></i>
                                <p>Branch Management</p>
                            </a>
                        </li>
                        @endcan

                        <li class="nav-item">
                            <a href="{{ route('settings.invoice-logo') }}"
                                class="nav-link {{ request()->routeIs('settings.invoice-logo*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-image"></i>
                                <p>Invoice Logo Settings</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('invoice-company-profiles.index') }}"
                                class="nav-link {{ request()->routeIs('invoice-company-profiles.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-file-invoice"></i>
                                <p>Invoice Company Profiles</p>
                            </a>
                        </li>

                    </ul>
                </li>

            </ul>

        </nav>

    </div>

</aside>