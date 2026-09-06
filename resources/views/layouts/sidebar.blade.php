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

            <ul class="nav nav-pills nav-sidebar flex-column text-sm" data-widget="treeview" role="menu">

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

                <li class="nav-item">
                    <a href="/stock-summary" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Stock Summary</p>
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

                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-warehouse"></i>
                        <p>
                            Inventory
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">
                            <a href="{{ route('purchase-inventories.index') }}" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Purchase Inventories</p>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="{{ route('inventory.summary') }}" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Inventory Summary</p>
                            </a>
                        </li>

                    </ul>
                </li>

                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-warehouse"></i>
                        <p>
                            Expense
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">
                            <a href="{{ route('expenses.index') }}" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Expenses</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('expense-categories.index') }}" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Expense Categories</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-warehouse"></i>
                        <p>
                            Sales
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">
                            <a href="/sales" class="nav-link">
                                <i class="nav-icon fas fa-cash-register"></i>
                                <p>Local Sales</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/export-sales/" class="nav-link">
                                <i class="nav-icon fas fa-cash-register"></i>
                                <p>Export Sales</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/sales-profit-loss/" class="nav-link">
                                <i class="nav-icon fas fa-cash-register"></i>
                                <p>Profit Prediction</p>
                            </a>
                        </li>

                    </ul>
                </li>
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-warehouse"></i>
                        <p>
                            Shipment Plans
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">
                            <a href="/shipment-plans" class="nav-link">
                                <i class="nav-icon fas fa-cash-register"></i>
                                <p>Shipment Plans</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/packings" class="nav-link">
                                <i class="nav-icon fas fa-cash-register"></i>
                                <p>Shipment Packing</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-warehouse"></i>
                        <p>
                            Freight Services
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">
                            <a href="/freight-services" class="nav-link">
                                <i class="nav-icon fas fa-cash-register"></i>
                                <p>Freight Services</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/freights" class="nav-link">
                                <i class="nav-icon fas fa-cash-register"></i>
                                <p>Freight Expenses</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="/packing-usages" class="nav-link">
                        <i class="nav-icon fas fa-chart-line"></i>
                        <p>Packing</p>
                    </a>
                </li>

                {{-- REPORTS --}}
                <li class="nav-header">REPORTS</li>

                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-chart-bar"></i>
                        <p>
                            Reports
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">
                            <a href="" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Trial Balance</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('reports.item-profit-analysis') }}" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Item Profit Analysis</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Balance Sheet</p>
                            </a>
                        </li>

                         <li class="nav-item">
                            <a href="{{ route('reports.customer-summary') }}" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Customer Summary</p>
                            </a>
                        </li>

                         <li class="nav-item">
                            <a href="{{ route('reports.supplier-summary') }}" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Supplier Summary</p>
                            </a>
                        </li>

                    </ul>
                </li>

                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-chart-bar"></i>
                        <p>
                            Freight Management
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">
                            <a href="/freight-records" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Freight Records</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/landing-costs" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Freight Cost</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a href="/reports/branch-comparison" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Branch Comparison</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/reports/invoice-profit-analysis" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Invoice Profit Analysis</p>
                    </a>
                </li>

                <li class="nav-item has-treeview
    {{ request()->routeIs('accounting.*') ? 'menu-open' : '' }}">

                    <a href="#"
                        class="nav-link
       {{ request()->routeIs('accounting.*') ? 'active' : '' }}">

                        <i class="nav-icon fas fa-calculator"></i>

                        <p>
                            Accounting
                            <i class="right fas fa-angle-left"></i>
                        </p>

                    </a>


                    <ul class="nav nav-treeview">


                        {{-- Chart of Accounts --}}


                        <li class="nav-item">

                            <a href="{{ route('accounting.chart-of-accounts.index') }}"
                                class="nav-link
                   {{ request()->routeIs('accounting.chart-of-accounts.*') ? 'active' : '' }}">

                                <i class="far fa-circle nav-icon"></i>

                                <p>
                                    Chart of Accounts
                                </p>

                            </a>

                        </li>




                        {{-- Account Groups --}}

                        <li class="nav-item">

                            <a href="{{ route('accounting.account-groups.index') }}"
                                class="nav-link
                   {{ request()->routeIs('accounting.account-groups.*') ? 'active' : '' }}">

                                <i class="far fa-circle nav-icon"></i>

                                <p>
                                    Account Groups
                                </p>

                            </a>

                        </li>




                        {{-- Ledgers --}}


                        <li class="nav-item">

                            <a href="{{ route('accounting.ledgers.index') }}"
                                class="nav-link
                   {{ request()->routeIs('accounting.ledgers.*') ? 'active' : '' }}">

                                <i class="far fa-circle nav-icon"></i>

                                <p>
                                    Ledgers
                                </p>

                            </a>

                        </li>




                        {{-- Sub Ledgers --}}

                        <li class="nav-item">

                            <a href="{{ route('accounting.sub-ledgers.index') }}"
                                class="nav-link
                   {{ request()->routeIs('accounting.sub-ledgers.*') ? 'active' : '' }}">

                                <i class="far fa-circle nav-icon"></i>

                                <p>
                                    Sub Ledgers
                                </p>

                            </a>

                        </li>




                    </ul>

                </li>

                <li class="nav-item">

                    <a href="{{ route('accounting.journal-entries.index') }}"
                        class="nav-link
       {{ request()->routeIs('accounting.journal-entries.*') ? 'active' : '' }}">

                        <i class="nav-icon fas fa-book"></i>

                        <p>
                            Journal Entries
                        </p>

                    </a>

                </li>

            </ul>

        </nav>

    </div>

</aside>