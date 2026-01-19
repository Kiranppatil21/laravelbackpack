{{-- Vertical Header Navigation --}}
<header data-bs-theme="{{ $theme ?? 'system' }}" class="navbar-expand-lg top">
    <div class="container-fluid">
        {{-- Mobile Hamburger Toggle --}}
        <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        {{-- Brand/Logo (visible on mobile) --}}
        <a class="navbar-brand d-lg-none" href="{{ url(backpack_theme_config('home_link')) }}">
            @if(backpack_theme_config('project_logo'))
                <img src="{{ backpack_theme_config('project_logo') }}" class="project-logo" style="height: 32px;" alt="{{ backpack_theme_config('project_name') }}">
            @else
                <i class="la la-shield-alt"></i>
            @endif
        </a>
    </div>
    
    <div class="collapse navbar-collapse" id="navbar-menu">
        <div class="d-print-none navbar navbar-expand-lg navbar-light">
            <div class="container-fluid">
                <ul class="navbar-nav">
                    {{-- Main Navigation Items --}}
                    @include(backpack_view('inc.sidebar_content'))
                </ul>

                {{-- Right Side Navigation --}}
                <ul class="nav navbar-nav d-flex flex-row flex-shrink-0">
                    {{-- Theme Switcher --}}
                    @includeWhen(backpack_theme_config('options.showColorModeSwitcher'), backpack_view('layouts.partials.switch_theme'))
                    
                    {{-- Topbar Left Content --}}
                    @include(backpack_view('inc.topbar_left_content'))
                    
                    {{-- Topbar Right Content --}}
                    @include(backpack_view('inc.topbar_right_content'))
                    
                    {{-- User Dropdown Menu --}}
                    @include(backpack_view('inc.menu_user_dropdown'))
                </ul>
            </div>
        </div>
    </div>
</header>
