<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $preview->business_name }} — Mobile App UI Preview</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-color: {{ $preview->theme_json['primary_color'] ?? '#1E3A5F' }};
      --secondary-color: {{ $preview->theme_json['secondary_color'] ?? '#0F172A' }};
      --accent-color: {{ $preview->theme_json['accent_color'] ?? '#0EA5E9' }};
      --background-color: {{ $preview->theme_json['bg_color'] ?? '#F8FAFC' }};
      --surface-color: {{ $preview->theme_json['surface_color'] ?? '#FFFFFF' }};
      --text-color: {{ $preview->theme_json['text_color'] ?? '#1E293B' }};
      --heading-color: {{ $preview->theme_json['heading_color'] ?? '#0F172A' }};
      --border-color: {{ $preview->theme_json['border_color'] ?? '#E2E8F0' }};
      --button-color: {{ $preview->theme_json['button_color'] ?? ($preview->theme_json['primary_color'] ?? '#1E3A5F') }};

      /* Template Aliases */
      --primary: var(--primary-color);
      --secondary: var(--secondary-color);
      --accent: var(--accent-color);
      --bg: var(--background-color);
      --font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: var(--font-family);
      background-color: #0b1329;
      color: #1e293b;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .desktop-banner {
      color: #94a3b8;
      font-size: 13px;
      margin-bottom: 16px;
      text-align: center;
    }
    .desktop-banner strong { color: #f8fafc; }
    .desktop-banner a {
      color: #38bdf8;
      text-decoration: none;
      font-weight: 600;
      margin-left: 8px;
    }
    /* Smartphone Device Frame */
    .smartphone-bezel {
      width: 100%;
      max-width: 410px;
      height: 844px;
      background: #ffffff;
      border: 12px solid #1e293b;
      border-radius: 48px;
      box-shadow: 0 25px 60px -12px rgba(0,0,0,0.5), 0 0 0 1px #334155;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      position: relative;
    }
    /* Dynamic Island / Notch & Status Bar */
    .status-bar {
      height: 44px;
      background: #ffffff;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 24px;
      font-size: 13px;
      font-weight: 700;
      color: #0f172a;
      z-index: 50;
      position: relative;
      flex-shrink: 0;
    }
    .notch {
      position: absolute;
      top: 8px;
      left: 50%;
      transform: translateX(-50%);
      width: 110px;
      height: 24px;
      background: #000000;
      border-radius: 16px;
    }
    /* In-App Header */
    .app-header {
      padding: 12px 18px;
      background: #ffffff;
      border-bottom: 1px solid #f1f5f9;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-shrink: 0;
    }
    .app-brand {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .app-logo {
      width: 36px;
      height: 36px;
      border-radius: 8px;
      object-fit: cover;
    }
    .app-title {
      font-size: 15px;
      font-weight: 800;
      color: var(--secondary);
      line-height: 1.2;
    }
    .app-sub {
      font-size: 11px;
      color: #64748b;
      font-weight: 500;
    }
    /* Scrollable App Body */
    .app-body {
      flex: 1;
      overflow-y: auto;
      background: var(--bg);
      padding: 16px;
      -webkit-overflow-scrolling: touch;
    }
    /* App Bottom Navigation Bar */
    .app-bottom-nav {
      height: 64px;
      background: #ffffff;
      border-top: 1px solid #e2e8f0;
      display: flex;
      justify-content: space-around;
      align-items: center;
      flex-shrink: 0;
      padding-bottom: 4px;
    }
    .nav-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      font-size: 10px;
      font-weight: 600;
      color: #64748b;
      text-decoration: none;
      gap: 3px;
    }
    .nav-item.active {
      color: var(--primary);
    }
    .home-indicator {
      width: 130px;
      height: 4px;
      background: #0f172a;
      border-radius: 4px;
      margin: 6px auto 4px auto;
      opacity: 0.3;
    }
    /* Mobile responsive override */
    @media (max-width: 480px) {
      body {
        padding: 0;
        background: #ffffff;
      }
      .desktop-banner { display: none; }
      .smartphone-bezel {
        max-width: 100%;
        height: 100vh;
        border: none;
        border-radius: 0;
        box-shadow: none;
      }
    }
  </style>
</head>
<body>
  <div class="desktop-banner">
    📱 <strong>Interactive Mobile App Prototype</strong> for <strong>{{ $preview->business_name }}</strong>
    <a href="tel:{{ $preview->custom_content['phone'] ?? '' }}">📞 Call Sales Rep</a>
  </div>

  <div class="smartphone-bezel">
    <!-- Status Bar with Dynamic Island -->
    <div class="status-bar">
      <span>9:41</span>
      <div class="notch"></div>
      <div style="display: flex; gap: 4px; align-items: center;">
        <span style="font-size: 11px;">5G</span>
        <span>100%</span>
      </div>
    </div>

    <!-- App Header -->
    <div class="app-header">
      <div class="app-brand">
        <img src="{{ $preview->logo_url }}" alt="Logo" class="app-logo">
        <div>
          <div class="app-title">{{ $preview->business_name }}</div>
          <div class="app-sub">{{ $preview->category ?? 'Official App' }}</div>
        </div>
      </div>
      <div>
        @if(!empty($preview->custom_content['phone']))
          <a href="tel:{{ $preview->custom_content['phone'] }}" style="text-decoration: none; font-size: 18px;">📞</a>
        @endif
      </div>
    </div>

    <!-- Scrollable In-App Body -->
    <div class="app-body">
      @yield('app_content')

      <!-- Micro Google Attribution -->
      <div style="text-align: center; font-size: 10px; color: #94a3b8; margin: 24px 0 10px 0;">
        Powered by Google • Concept app UI by Doxa Finder
      </div>
    </div>

    <!-- Bottom Navigation Bar -->
    <div class="app-bottom-nav">
      <a href="#" class="nav-item active">
        <span style="font-size: 16px;">🏠</span>
        <span>Home</span>
      </a>
      <a href="#" class="nav-item">
        <span style="font-size: 16px;">🔍</span>
        <span>Explore</span>
      </a>
      <a href="#" class="nav-item">
        <span style="font-size: 16px;">📅</span>
        <span>Bookings</span>
      </a>
      <a href="#" class="nav-item">
        <span style="font-size: 16px;">👤</span>
        <span>Profile</span>
      </a>
    </div>
    <div class="home-indicator"></div>
  </div>

  <script>
    window.addEventListener('message', function(event) {
      if (event.data && event.data.type === 'DOXA_THEME_UPDATE') {
        const root = document.documentElement;
        const theme = event.data.theme;
        if (theme.primary_color) {
          root.style.setProperty('--primary-color', theme.primary_color);
          root.style.setProperty('--primary', theme.primary_color);
          root.style.setProperty('--button-color', theme.primary_color);
        }
        if (theme.secondary_color) {
          root.style.setProperty('--secondary-color', theme.secondary_color);
          root.style.setProperty('--secondary', theme.secondary_color);
        }
        if (theme.accent_color) {
          root.style.setProperty('--accent-color', theme.accent_color);
          root.style.setProperty('--accent', theme.accent_color);
        }
        if (theme.bg_color) {
          root.style.setProperty('--background-color', theme.bg_color);
          root.style.setProperty('--bg', theme.bg_color);
        }
        if (theme.surface_color) root.style.setProperty('--surface-color', theme.surface_color);
        if (theme.text_color) root.style.setProperty('--text-color', theme.text_color);
        if (theme.heading_color) root.style.setProperty('--heading-color', theme.heading_color);
      }
      if (event.data && event.data.type === 'DOXA_CONTENT_UPDATE') {
        const content = event.data.content;
        if (content.business_name) {
          document.querySelectorAll('.app-brand-title').forEach(el => el.textContent = content.business_name);
        }
      }
    });
  </script>
</body>
</html>
