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
                <li class="nav-item">
                    <a href="/dashboard"
                       class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">

                        <i class="nav-icon fas fa-tachometer-alt text-info"></i>
                        <p>Dashboard</p>

                    </a>
                </li>


                {{-- SUPPLIERS --}}
                <li class="nav-item">
                    <a href="/suppliers"
                       class="nav-link {{ request()->is('suppliers*') ? 'active' : '' }}">

                        <i class="nav-icon fas fa-truck text-warning"></i>
                        <p>Suppliers</p>

                    </a>
                </li>


                {{-- CUSTOMERS --}}
                <li class="nav-item">
                    <a href="/customers"
                       class="nav-link {{ request()->is('customers*') ? 'active' : '' }}">

                        <i class="nav-icon fas fa-users text-success"></i>
                        <p>Customers</p>

                    </a>
                </li>


                {{-- TRANSACTIONS --}}
                <li class="nav-header">TRANSACTIONS</li>


                {{-- PURCHASES --}}
                <li class="nav-item has-treeview
                    {{ request()->is('purchases*') || request()->is('stock-summary*') ? 'menu-open' : '' }}">

                    <a href="#"
                       class="nav-link
                       {{ request()->is('purchases*') || request()->is('stock-summary*') ? 'active' : '' }}">

                        <i class="nav-icon fas fa-shopping-cart text-primary"></i>

                        <p>
                            Purchases
                            <i class="right fas fa-angle-left"></i>
                        </p>

                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">

                            <a href="/purchases"
                               class="nav-link {{ request()->is('purchases*') ? 'active' : '' }}">

                                <i class="far fa-circle nav-icon text-primary"></i>
                                <p>Purchases</p>

                            </a>

                        </li>

                        <li class="nav-item">

                            <a href="/stock-summary"
                               class="nav-link {{ request()->is('stock-summary*') ? 'active' : '' }}">

                                <i class="far fa-circle nav-icon text-info"></i>
                                <p>Trading Goods Summary</p>

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
                            Inventory
                            <i class="right fas fa-angle-left"></i>
                        </p>

                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">

                            <a href="{{ route('purchase-inventories.index') }}"
                               class="nav-link {{ request()->routeIs('purchase-inventories.*') ? 'active' : '' }}">

                                <i class="far fa-circle nav-icon text-success"></i>
                                <p>Purchase Inventories</p>

                            </a>

                        </li>

                        <li class="nav-item">

                            <a href="{{ route('inventory.summary') }}"
                               class="nav-link {{ request()->routeIs('inventory.summary') ? 'active' : '' }}">

                                <i class="far fa-circle nav-icon text-info"></i>
                                <p>Inventory Summary</p>

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
                            Expense
                            <i class="right fas fa-angle-left"></i>
                        </p>

                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">

                            <a href="{{ route('expenses.index') }}"
                               class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}">

                                <i class="far fa-circle nav-icon text-danger"></i>
                                <p>Expenses</p>

                            </a>

                        </li>

                        <li class="nav-item">

                            <a href="{{ route('expense-categories.index') }}"
                               class="nav-link {{ request()->routeIs('expense-categories.*') ? 'active' : '' }}">

                                <i class="far fa-circle nav-icon text-warning"></i>
                                <p>Expense Categories</p>

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
                            Sales
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

                        <li class="nav-item">

                            <a href="/sales-profit-loss/"
                               class="nav-link {{ request()->is('sales-profit-loss*') ? 'active' : '' }}">

                                <i class="fas fa-chart-line nav-icon text-warning"></i>
                                <p>Profit Prediction</p>

                            </a>

                        </li>

                    </ul>

                </li>


                {{-- SHIPMENT PLANS --}}
                <li class="nav-item has-treeview
                    {{ request()->is('shipment-plans*') || request()->is('packings*') ? 'menu-open' : '' }}">

                    <a href="#"
                       class="nav-link
                       {{ request()->is('shipment-plans*') || request()->is('packings*') ? 'active' : '' }}">

                        <i class="nav-icon fas fa-shipping-fast text-info"></i>

                        <p>
                            Shipment Plans
                            <i class="right fas fa-angle-left"></i>
                        </p>

                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">

                            <a href="/shipment-plans"
                               class="nav-link {{ request()->is('shipment-plans*') ? 'active' : '' }}">

                                <i class="fas fa-clipboard-list nav-icon text-info"></i>
                                <p>Shipment Plans</p>

                            </a>

                        </li>

                        <li class="nav-item">

                            <a href="/packings"
                               class="nav-link {{ request()->is('packings*') ? 'active' : '' }}">

                                <i class="fas fa-box-open nav-icon text-warning"></i>
                                <p>Shipment Packing</p>

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
                            Freight Services
                            <i class="right fas fa-angle-left"></i>
                        </p>

                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">

                            <a href="/freight-services"
                               class="nav-link {{ request()->is('freight-services*') ? 'active' : '' }}">

                                <i class="fas fa-concierge-bell nav-icon text-info"></i>
                                <p>Freight Services</p>

                            </a>

                        </li>

                        <li class="nav-item">

                            <a href="/freights"
                               class="nav-link {{ request()->is('freights*') ? 'active' : '' }}">

                                <i class="fas fa-file-invoice-dollar nav-icon text-warning"></i>
                                <p>Freight Expenses</p>

                            </a>

                        </li>

                    </ul>

                </li>


                {{-- PACKING --}}
                <li class="nav-item">

                    <a href="/packing-usages"
                       class="nav-link {{ request()->is('packing-usages*') ? 'active' : '' }}">

                        <i class="nav-icon fas fa-boxes text-warning"></i>
                        <p>Packing</p>

                    </a>

                </li>


                {{-- REPORTS --}}
                <li class="nav-header">REPORTS</li>


                {{-- REPORTS --}}
                <li class="nav-item has-treeview
                    {{ request()->is('reports/*') && !request()->is('reports/branch-comparison')
                    && !request()->is('reports/invoice-profit-analysis') ? 'menu-open' : '' }}">

                    <a href="#"
                       class="nav-link
                       {{ request()->is('reports/*') && !request()->is('reports/branch-comparison')
                       && !request()->is('reports/invoice-profit-analysis') ? 'active' : '' }}">

                        <i class="nav-icon fas fa-chart-bar text-success"></i>

                        <p>
                            Reports
                            <i class="right fas fa-angle-left"></i>
                        </p>

                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">

                            <a href=""
                               class="nav-link">

                                <i class="far fa-circle nav-icon text-primary"></i>
                                <p>Trial Balance</p>

                            </a>

                        </li>

                        <li class="nav-item">

                            <a href="{{ route('reports.item-profit-analysis') }}"
                               class="nav-link {{ request()->routeIs('reports.item-profit-analysis') ? 'active' : '' }}">

                                <i class="far fa-circle nav-icon text-success"></i>
                                <p>Item Profit Analysis</p>

                            </a>

                        </li>

                        <li class="nav-item">

                            <a href=""
                               class="nav-link">

                                <i class="far fa-circle nav-icon text-info"></i>
                                <p>Balance Sheet</p>

                            </a>

                        </li>

                        <li class="nav-item">

                            <a href="{{ route('reports.customer-summary') }}"
                               class="nav-link {{ request()->routeIs('reports.customer-summary') ? 'active' : '' }}">

                                <i class="far fa-circle nav-icon text-success"></i>
                                <p>Customer Summary</p>

                            </a>

                        </li>

                        <li class="nav-item">

                            <a href="{{ route('reports.supplier-summary') }}"
                               class="nav-link {{ request()->routeIs('reports.supplier-summary') ? 'active' : '' }}">

                                <i class="far fa-circle nav-icon text-warning"></i>
                                <p>Supplier Summary</p>

                            </a>

                        </li>

                    </ul>

                </li>


                {{-- FREIGHT MANAGEMENT --}}
                <li class="nav-item has-treeview
                    {{ request()->is('freight-records*') || request()->is('landing-costs*') ? 'menu-open' : '' }}">

                    <a href="#"
                       class="nav-link
                       {{ request()->is('freight-records*') || request()->is('landing-costs*') ? 'active' : '' }}">

                        <i class="nav-icon fas fa-anchor text-primary"></i>

                        <p>
                            Freight Management
                            <i class="right fas fa-angle-left"></i>
                        </p>

                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">

                            <a href="/freight-records"
                               class="nav-link {{ request()->is('freight-records*') ? 'active' : '' }}">

                                <i class="far fa-circle nav-icon text-info"></i>
                                <p>Freight Records</p>

                            </a>

                        </li>

                        <li class="nav-item">

                            <a href="/landing-costs"
                               class="nav-link {{ request()->is('landing-costs*') ? 'active' : '' }}">

                                <i class="far fa-circle nav-icon text-warning"></i>
                                <p>Freight Cost</p>

                            </a>

                        </li>

                    </ul>

                </li>


                {{-- BRANCH COMPARISON --}}
                <li class="nav-item">

                    <a href="/reports/branch-comparison"
                       class="nav-link {{ request()->is('reports/branch-comparison') ? 'active' : '' }}">

                        <i class="nav-icon fas fa-code-branch text-info"></i>
                        <p>Branch Comparison</p>

                    </a>

                </li>


                {{-- INVOICE PROFIT --}}
                <li class="nav-item">

                    <a href="/reports/invoice-profit-analysis"
                       class="nav-link {{ request()->is('reports/invoice-profit-analysis') ? 'active' : '' }}">

                        <i class="nav-icon fas fa-file-invoice-dollar text-success"></i>
                        <p>Invoice Profit Analysis</p>

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
                            Accounting
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

                    </ul>

                </li>


                {{-- JOURNAL ENTRIES --}}
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

            </ul>

        </nav>

    </div>

</aside>