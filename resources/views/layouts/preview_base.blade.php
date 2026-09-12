<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $preview->business_name }} — Official Website Concept Preview</title>
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
      --border-radius: {{ $preview->theme_json['border_radius'] ?? '12px' }};
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: var(--font-family);
      background-color: var(--bg);
      color: #1e293b;
      line-height: 1.6;
      -webkit-font-smoothing: antialiased;
      padding-bottom: 80px;
    }
    .preview-banner {
      background: #0f172a;
      color: #e2e8f0;
      padding: 10px 16px;
      font-size: 13px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 100;
    }
    .preview-banner a {
      color: #38bdf8;
      text-decoration: none;
      font-weight: 600;
      padding: 4px 10px;
      background: rgba(56, 189, 248, 0.15);
      border-radius: 6px;
    }
    header {
      background: #ffffff;
      border-bottom: 1px solid #e2e8f0;
      padding: 16px 24px;
      position: sticky;
      top: 41px;
      z-index: 90;
      box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    }
    .header-container {
      max-width: 1200px;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .brand-group {
      display: flex;
      align-items: center;
      gap: 14px;
    }
    .brand-logo {
      width: 48px;
      height: 48px;
      border-radius: 10px;
      object-fit: cover;
      box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    }
    .brand-name {
      font-size: 20px;
      font-weight: 800;
      color: var(--secondary);
      letter-spacing: -0.5px;
    }
    .brand-category {
      font-size: 12px;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-weight: 600;
    }
    .header-actions {
      display: flex;
      gap: 10px;
      align-items: center;
    }
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 18px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.2s ease;
      cursor: pointer;
      border: none;
    }
    .btn-primary {
      background: var(--primary);
      color: #ffffff;
    }
    .btn-primary:hover { opacity: 0.92; transform: translateY(-1px); }
    .btn-accent {
      background: var(--accent);
      color: #ffffff;
    }
    .btn-whatsapp {
      background: #25D366;
      color: #ffffff;
    }
    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 32px 20px;
    }
    .badge {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
    }
    .badge-primary { background: rgba(30, 58, 95, 0.1); color: var(--primary); }
    .footer-attribution {
      background: #0f172a;
      color: #94a3b8;
      padding: 40px 20px;
      text-align: center;
      font-size: 13px;
      border-top: 1px solid #1e293b;
      margin-top: 60px;
    }
    .footer-attribution p { margin-bottom: 8px; }
    .bottom-cta-bar {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      background: #ffffff;
      border-top: 1px solid #e2e8f0;
      padding: 12px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      z-index: 100;
      box-shadow: 0 -4px 12px rgba(0,0,0,0.05);
    }
    @media (max-width: 768px) {
      .header-actions .btn-call { display: none; }
      .brand-name { font-size: 17px; }
      .bottom-cta-bar { flex-direction: column; gap: 8px; }
    }
  </style>
</head>
<body>
  @if(empty($inEditor))
  <!-- Doxa Sales Concept Bar -->
  <div class="preview-banner">
    <div>
      <span style="background: #2563eb; color: #fff; padding: 2px 6px; border-radius: 4px; font-weight: 700; font-size: 10px; margin-right: 6px;">LIVE PREVIEW</span>
      Concept design generated exclusively for <strong>{{ $preview->business_name }}</strong>
    </div>
    <div>
      @if($preview->preview_type === 'website')
        <a href="{{ url('/api/previews/switch/' . $preview->token . '?to=app') }}" style="margin-right: 8px;">📱 Switch to App View</a>
      @endif
      <a href="tel:{{ $preview->custom_content['phone'] ?? '' }}">📞 Call Sales Team</a>
    </div>
  </div>
  @endif

  <!-- Branded Header -->
  <header>
    <div class="header-container">
      <div class="brand-group">
        <img src="{{ $preview->logo_url }}" alt="{{ $preview->business_name }} Logo" class="brand-logo">
        <div>
          <h1 class="brand-name">{{ $preview->business_name }}</h1>
          <div class="brand-category">{{ $preview->category ?? 'Business' }}</div>
        </div>
      </div>
      <div class="header-actions">
        @if(!empty($preview->custom_content['phone']))
          <a href="tel:{{ $preview->custom_content['phone'] }}" class="btn btn-call" style="background: #f1f5f9; color: #334155;">📞 {{ $preview->custom_content['phone'] }}</a>
          <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $preview->custom_content['phone']) }}?text=Hello%20{{ rawurlencode($preview->business_name) }}" target="_blank" class="btn btn-whatsapp">💬 WhatsApp Inquiry</a>
        @endif
      </div>
    </div>
  </header>

  <main>
    @yield('content')
  </main>

  <!-- Google & Doxa Attribution Footer -->
  <footer class="footer-attribution">
    <p><strong>{{ $preview->business_name }}</strong> • {{ $preview->custom_content['address'] ?? 'Tamil Nadu, India' }}</p>
    <p style="font-size: 11px; opacity: 0.8;">Public listings and ratings sourced from Google Maps Platform • "Powered by Google"</p>
    <p style="font-size: 11px; opacity: 0.6; margin-top: 12px;">Concept website preview generated via Doxa Business Finder SaaS Platform. All rights reserved.</p>
  </footer>

  <!-- Fixed Conversion Bar -->
  <div class="bottom-cta-bar">
    <div style="font-size: 13px; color: #475569;">
      Interested in launching this custom branded platform for <strong>{{ $preview->business_name }}</strong>?
    </div>
    <div style="display: flex; gap: 10px;">
      @if(!empty($preview->custom_content['phone']))
        <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $preview->custom_content['phone']) }}" class="btn btn-whatsapp">💬 Chat with Us</a>
      @endif
      <a href="tel:+919843787854" class="btn btn-primary">🚀 Claim & Activate Platform</a>
    </div>
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
          document.querySelectorAll('.brand-name').forEach(el => el.textContent = content.business_name);
        }
      }
    });
  </script>
</body>
</html>
