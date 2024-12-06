<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark"> <!--begin::Sidebar Brand-->
    <div class="sidebar-brand"> <!--begin::Brand Link--> <a href="./index.html" class="brand-link"> <!--begin::Brand Image--> <img src="../../dist/assets/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image opacity-75 shadow"> <!--end::Brand Image--> <!--begin::Brand Text--> <span class="brand-text fw-light">NJANGUI</span> <!--end::Brand Text--> </a> <!--end::Brand Link--> </div> <!--end::Sidebar Brand--> <!--begin::Sidebar Wrapper-->
    <div class="sidebar-wrapper">
        <nav class="mt-2"> <!--begin::Sidebar Menu-->
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                
                {{-- Menu Admin --}}
                @if(Auth::user()->is_role == 1)
                <li class="nav-item"> 
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"> 
                        <i class="nav-icon bi bi-speedometer2"></i>
                        <p>Tableau de Bord</p>
                    </a> 
                </li>
                <li class="nav-item"> 
                    <a href="{{ route('accounts.index') }}" class="nav-link {{ request()->routeIs('accounts.*') ? 'active' : '' }}"> 
                        <i class="nav-icon bi bi-wallet2"></i>
                        <p>Accounts</p>
                    </a> 
                </li>
                <li class="nav-item"> 
                    <a href="{{ route('transfers.index') }}" class="nav-link {{ request()->routeIs('transfers.*') ? 'active' : '' }}"> 
                        <i class="nav-icon bi bi-arrow-left-right"></i>
                        <p>Transfers</p>
                    </a> 
                </li>
                <li class="nav-item"> 
                    <a href="{{ route('loans.index') }}" class="nav-link {{ request()->routeIs('loans.*') ? 'active' : '' }}"> 
                        <i class="nav-icon bi bi-cash-coin"></i>
                        <p>Loans</p>
                    </a> 
                </li>
                <li class="nav-item"> 
                    <a href="" class="nav-link"> 
                        <i class="nav-icon bi bi-people"></i>
                        <p>Membres</p>
                    </a> 
                </li>
                
                {{-- Menu Utilisateur --}}
                @elseif (Auth::user()->is_role == 2)
                <li class="nav-item"> 
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"> 
                        <i class="nav-icon bi bi-palette"></i>
                        <p>Tableau de Bord</p>
                    </a> 
                </li>
                <li class="nav-item"> 
                    <a href="./generate/theme.html" class="nav-link "> 
                        <i class="nav-icon bi bi-table"></i>
                        <p>Paramètres</p>
                    </a> 
                </li>
                @endif
            </ul> <!--end::Sidebar Menu-->
        </nav>
    </div> <!--end::Sidebar Wrapper-->
</aside> <!--end::Sidebar--> <!--begin::App Main-->