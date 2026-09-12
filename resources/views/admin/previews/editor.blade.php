<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Preview Editor — {{ $preview->business_name }} ({{ $preview->preview_code ?? 'PREVIEW' }})</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <style>
    body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
    .viewport-canvas {
      transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), height 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
  </style>
</head>
<body x-data="previewEditor()" class="bg-slate-900 text-slate-100 h-screen flex flex-col overflow-hidden select-none">

  <!-- Top App Navigation & Action Bar -->
  <header class="h-16 bg-slate-950 border-b border-slate-800 px-5 flex items-center justify-between flex-shrink-0 z-30">
    <div class="flex items-center gap-4">
      <a href="{{ url()->previous() ?: route('admin.dashboard') }}" class="p-2 hover:bg-slate-800 text-slate-400 hover:text-white rounded-lg transition" title="Back">
        <i data-lucide="arrow-left" class="w-5 h-5"></i>
      </a>
      <div>
        <div class="flex items-center gap-2.5">
          <h1 class="text-base font-bold text-white truncate max-w-xs md:max-w-md">{{ $preview->business_name }}</h1>
          <span class="px-2 py-0.5 rounded font-mono text-xs font-semibold bg-blue-950 text-blue-400 border border-blue-800/80">
            {{ $preview->preview_code ?? sprintf('PREVIEW-%06d', $preview->id) }}
          </span>
          <span class="px-2 py-0.5 rounded text-xs font-semibold capitalize {{ $preview->status === 'shared' ? 'bg-purple-950 text-purple-400 border border-purple-800' : 'bg-emerald-950 text-emerald-400 border border-emerald-800' }}">
            {{ str_replace('_', ' ', $preview->status ?? 'generated') }}
          </span>
        </div>
        <p class="text-xs text-slate-400 flex items-center gap-2 mt-0.5">
          <span>Template: <strong class="text-slate-200">{{ $preview->template->name }}</strong></span>
          <span>•</span>
          <span>Views: <strong class="text-slate-200">{{ $preview->view_count }}</strong></span>
          @if($preview->last_viewed_at)
          <span>•</span>
          <span>Last opened: {{ $preview->last_viewed_at->diffForHumans() }}</span>
          @endif
        </p>
      </div>
    </div>

    <!-- Viewport Switchers & Sales Mode Toggle -->
    <div class="hidden md:flex items-center gap-2 bg-slate-900 border border-slate-800 p-1 rounded-xl">
      <button @click="setViewport('desktop')" :class="viewport === 'desktop' ? 'bg-blue-600 text-white font-semibold shadow-xs' : 'text-slate-400 hover:text-slate-200'" class="px-3 py-1.5 rounded-lg text-xs flex items-center gap-1.5 transition">
        <i data-lucide="monitor" class="w-3.5 h-3.5"></i> Desktop (1440px)
      </button>
      <button @click="setViewport('tablet')" :class="viewport === 'tablet' ? 'bg-blue-600 text-white font-semibold shadow-xs' : 'text-slate-400 hover:text-slate-200'" class="px-3 py-1.5 rounded-lg text-xs flex items-center gap-1.5 transition">
        <i data-lucide="tablet" class="w-3.5 h-3.5"></i> Tablet (768px)
      </button>
      <button @click="setViewport('mobile')" :class="viewport === 'mobile' ? 'bg-blue-600 text-white font-semibold shadow-xs' : 'text-slate-400 hover:text-slate-200'" class="px-3 py-1.5 rounded-lg text-xs flex items-center gap-1.5 transition">
        <i data-lucide="smartphone" class="w-3.5 h-3.5"></i> Mobile (390px)
      </button>
    </div>

    <!-- Main CTA Buttons -->
    <div class="flex items-center gap-2.5">
      <!-- Sales Mode Toggle -->
      <button @click="toggleSalesMode()" :class="salesMode ? 'bg-amber-500/20 text-amber-300 border-amber-500/50' : 'bg-slate-900 text-slate-400 border-slate-800 hover:text-white'" class="px-3 py-1.5 rounded-lg border text-xs font-semibold flex items-center gap-1.5 transition" title="Hide admin panels and test prospect view">
        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-400"></i>
        <span x-text="salesMode ? 'Exit Sales Mode' : 'Sales Mode'"></span>
      </button>

      <!-- Copy Preview Link -->
      <button @click="copyLink()" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-xs font-semibold flex items-center gap-1.5 border border-slate-700 shadow-xs transition">
        <i data-lucide="copy" class="w-3.5 h-3.5 text-slate-300"></i>
        <span x-text="copied ? 'Copied!' : 'Copy Link'"></span>
      </button>

      <!-- Share on WhatsApp -->
      <button @click="openWhatsAppModal()" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-semibold flex items-center gap-1.5 shadow-sm transition">
        <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
        <span>Share WhatsApp</span>
      </button>

      <!-- Save Preview -->
      <button @click="saveChanges()" :disabled="saving" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white rounded-lg text-xs font-semibold flex items-center gap-1.5 shadow-sm transition">
        <i data-lucide="save" class="w-3.5 h-3.5"></i>
        <span x-text="saving ? 'Saving...' : 'Save Preview'"></span>
      </button>

      <!-- Live External Link -->
      <a href="{{ $preview->public_url }}" target="_blank" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white rounded-lg text-xs transition" title="Open Public URL">
        <i data-lucide="external-link" class="w-4 h-4"></i>
      </a>
    </div>
  </header>

  <!-- Editor Body (Sidebar + Canvas) -->
  <div class="flex-1 flex overflow-hidden relative">

    <!-- Left Control Panel (Collapsible in Sales Mode) -->
    <aside x-show="!salesMode" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="w-80 md:w-96 bg-slate-950 border-r border-slate-800 flex flex-col z-20 overflow-y-auto">
      <div class="p-4 border-b border-slate-800 flex items-center justify-between">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
          <i data-lucide="sliders" class="w-4 h-4 text-blue-400"></i> Personalization Controls
        </span>
        <button @click="resetToDefaults()" class="text-xs text-slate-400 hover:text-blue-400 transition flex items-center gap-1">
          <i data-lucide="rotate-ccw" class="w-3 h-3"></i> Reset
        </button>
      </div>

      <div class="p-4 space-y-5 text-xs text-slate-300">

        <!-- 1. Business Info -->
        <div class="space-y-3 bg-slate-900/60 p-3.5 rounded-xl border border-slate-800">
          <h2 class="font-bold text-white text-xs uppercase tracking-wider flex items-center gap-2">
            <i data-lucide="building" class="w-3.5 h-3.5 text-blue-400"></i> Business Details
          </h2>
          <div>
            <label class="block text-slate-400 mb-1">Business Name</label>
            <input type="text" x-model="form.business_name" @input="syncToIframe()" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-blue-500">
          </div>
          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="block text-slate-400 mb-1">Phone Number</label>
              <input type="text" x-model="form.phone" @input="syncToIframe()" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-2.5 py-1.5 text-white focus:outline-none focus:border-blue-500">
            </div>
            <div>
              <label class="block text-slate-400 mb-1">Category</label>
              <input type="text" x-model="form.category" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-2.5 py-1.5 text-white focus:outline-none focus:border-blue-500">
            </div>
          </div>
          <div>
            <label class="block text-slate-400 mb-1">Address</label>
            <input type="text" x-model="form.address" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-1.5 text-white focus:outline-none focus:border-blue-500">
          </div>
        </div>

        <!-- 2. Brand Colour System (9 Design Variables) -->
        <div class="space-y-3 bg-slate-900/60 p-3.5 rounded-xl border border-slate-800">
          <div class="flex items-center justify-between">
            <h2 class="font-bold text-white text-xs uppercase tracking-wider flex items-center gap-2">
              <i data-lucide="palette" class="w-3.5 h-3.5 text-pink-400"></i> Brand Colours
            </h2>
            <span class="text-[10px] text-slate-400 font-mono">CSS :root</span>
          </div>

          <!-- Primary -->
          <div class="flex items-center justify-between gap-2">
            <label class="text-slate-300">Primary Colour</label>
            <div class="flex items-center gap-2">
              <input type="color" x-model="form.theme.primary_color" @input="syncColors()" class="w-7 h-7 rounded border border-slate-700 bg-transparent cursor-pointer">
              <input type="text" x-model="form.theme.primary_color" @input="syncColors()" class="w-20 bg-slate-950 border border-slate-700 rounded px-2 py-1 text-white font-mono text-[11px]">
            </div>
          </div>

          <!-- Secondary -->
          <div class="flex items-center justify-between gap-2">
            <label class="text-slate-300">Secondary Colour</label>
            <div class="flex items-center gap-2">
              <input type="color" x-model="form.theme.secondary_color" @input="syncColors()" class="w-7 h-7 rounded border border-slate-700 bg-transparent cursor-pointer">
              <input type="text" x-model="form.theme.secondary_color" @input="syncColors()" class="w-20 bg-slate-950 border border-slate-700 rounded px-2 py-1 text-white font-mono text-[11px]">
            </div>
          </div>

          <!-- Accent -->
          <div class="flex items-center justify-between gap-2">
            <label class="text-slate-300">Accent Colour</label>
            <div class="flex items-center gap-2">
              <input type="color" x-model="form.theme.accent_color" @input="syncColors()" class="w-7 h-7 rounded border border-slate-700 bg-transparent cursor-pointer">
              <input type="text" x-model="form.theme.accent_color" @input="syncColors()" class="w-20 bg-slate-950 border border-slate-700 rounded px-2 py-1 text-white font-mono text-[11px]">
            </div>
          </div>

          <!-- Background -->
          <div class="flex items-center justify-between gap-2">
            <label class="text-slate-300">Background</label>
            <div class="flex items-center gap-2">
              <input type="color" x-model="form.theme.bg_color" @input="syncColors()" class="w-7 h-7 rounded border border-slate-700 bg-transparent cursor-pointer">
              <input type="text" x-model="form.theme.bg_color" @input="syncColors()" class="w-20 bg-slate-950 border border-slate-700 rounded px-2 py-1 text-white font-mono text-[11px]">
            </div>
          </div>

          <!-- Surface / Cards -->
          <div class="flex items-center justify-between gap-2">
            <label class="text-slate-300">Surface / Cards</label>
            <div class="flex items-center gap-2">
              <input type="color" x-model="form.theme.surface_color" @input="syncColors()" class="w-7 h-7 rounded border border-slate-700 bg-transparent cursor-pointer">
              <input type="text" x-model="form.theme.surface_color" @input="syncColors()" class="w-20 bg-slate-950 border border-slate-700 rounded px-2 py-1 text-white font-mono text-[11px]">
            </div>
          </div>
        </div>

        <!-- 3. Logo Management -->
        <div class="space-y-3 bg-slate-900/60 p-3.5 rounded-xl border border-slate-800">
          <h2 class="font-bold text-white text-xs uppercase tracking-wider flex items-center gap-2">
            <i data-lucide="image" class="w-3.5 h-3.5 text-amber-400"></i> Logo & Monogram
          </h2>
          <div class="flex items-center gap-3">
            <div class="w-14 h-14 bg-slate-950 rounded-lg border border-slate-700 flex items-center justify-center p-1 overflow-hidden flex-shrink-0">
              <img :src="form.logo_url" alt="Logo" class="max-h-full max-w-full object-contain">
            </div>
            <div class="flex-1 space-y-1.5">
              <input type="file" @change="uploadLogo($event)" class="text-[11px] text-slate-400 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-[11px] file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-500">
              <button @click="useMonogram()" class="text-[11px] text-blue-400 hover:text-blue-300 block">Use Initials Monogram</button>
            </div>
          </div>
        </div>

        <!-- 4. Template Switcher (Section 16) -->
        <div class="space-y-3 bg-slate-900/60 p-3.5 rounded-xl border border-slate-800">
          <h2 class="font-bold text-white text-xs uppercase tracking-wider flex items-center gap-2">
            <i data-lucide="layout" class="w-3.5 h-3.5 text-indigo-400"></i> Switch Template
          </h2>
          <p class="text-[11px] text-slate-400">Switching templates preserves your custom colors and branding.</p>
          <select x-model="form.template_id" @change="switchTemplate()" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-blue-500 text-xs">
            @foreach($compatibleTemplates as $t)
            <option value="{{ $t->id }}" {{ $t->id == $preview->template_id ? 'selected' : '' }}>
              {{ $t->name }} ({{ $t->category_key }})
            </option>
            @endforeach
          </select>
        </div>

        <!-- 5. Sales Pitch & CTA Banner Config (Section 20) -->
        <div class="space-y-3 bg-slate-900/60 p-3.5 rounded-xl border border-slate-800">
          <h2 class="font-bold text-white text-xs uppercase tracking-wider flex items-center gap-2">
            <i data-lucide="megaphone" class="w-3.5 h-3.5 text-emerald-400"></i> Sales Conversion CTA
          </h2>
          <div>
            <label class="block text-slate-400 mb-1">Headline / Pitch Callout</label>
            <input type="text" x-model="form.cta_title" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-1.5 text-white focus:outline-none focus:border-blue-500">
          </div>
          <div>
            <label class="block text-slate-400 mb-1">WhatsApp Button Text</label>
            <input type="text" x-model="form.cta_button" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-1.5 text-white focus:outline-none focus:border-blue-500">
          </div>
        </div>

      </div>
    </aside>

    <!-- Right Live Canvas -->
    <main class="flex-1 bg-slate-950 flex flex-col items-center justify-start p-4 md:p-6 overflow-auto relative">
      <!-- Device Canvas Frame -->
      <div :style="canvasStyle" class="viewport-canvas bg-white rounded-xl shadow-2xl overflow-hidden border border-slate-800 flex flex-col relative">
        <iframe id="previewIframe" :src="iframeSrc" class="w-full h-full border-0" @load="onIframeLoad()"></iframe>
      </div>
    </main>

  </div>

  <!-- WhatsApp Sharing Modal -->
  <div x-show="showWhatsAppModal" x-cloak class="fixed inset-0 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4 z-50">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2.5 text-emerald-400 font-bold text-base">
          <i data-lucide="message-circle" class="w-5 h-5"></i>
          <span>Send Concept to Prospect on WhatsApp</span>
        </div>
        <button @click="showWhatsAppModal = false" class="text-slate-400 hover:text-white">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-300 mb-1">Recipient Phone Number (with Country Code)</label>
        <input type="text" x-model="whatsAppPhone" placeholder="e.g. 919876543210" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white font-mono text-sm">
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-300 mb-1">WhatsApp Pitch Message</label>
        <textarea x-model="whatsAppMessage" rows="8" class="w-full bg-slate-950 border border-slate-700 rounded-lg p-3 text-white text-xs leading-relaxed font-sans"></textarea>
      </div>

      <div class="flex items-center justify-end gap-3 pt-2">
        <button @click="showWhatsAppModal = false" class="px-4 py-2 text-slate-300 hover:text-white text-xs font-medium">Cancel</button>
        <button @click="dispatchWhatsApp()" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-semibold flex items-center gap-2 shadow-sm transition">
          <i data-lucide="send" class="w-4 h-4"></i>
          <span>Launch WhatsApp Chat</span>
        </button>
      </div>
    </div>
  </div>

  <!-- Toast Notification -->
  <div x-show="toast.show" x-cloak x-transition class="fixed bottom-6 right-6 bg-slate-800 border border-slate-700 text-white px-4 py-2.5 rounded-xl shadow-xl flex items-center gap-2.5 text-xs z-50">
    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i>
    <span x-text="toast.message"></span>
  </div>

  <script>
    function previewEditor() {
      return {
        previewId: {{ $preview->id }},
        token: '{{ $preview->token }}',
        publicUrl: '{{ $preview->public_url }}',
        previewType: '{{ $preview->preview_type }}',
        viewport: 'desktop',
        salesMode: false,
        saving: false,
        copied: false,
        showWhatsAppModal: false,
        whatsAppPhone: '{{ preg_replace("/\D/", "", $preview->custom_content["phone"] ?? "") }}',
        whatsAppMessage: `{!! addslashes($whatsAppPayload["message"] ?? "") !!}`,
        toast: { show: false, message: '' },
        form: {
          business_name: '{{ addslashes($preview->business_name) }}',
          category: '{{ addslashes($preview->category ?? "General") }}',
          phone: '{{ addslashes($preview->custom_content["phone"] ?? "") }}',
          address: '{{ addslashes($preview->custom_content["address"] ?? "") }}',
          logo_url: '{{ $preview->logo_url }}',
          template_id: {{ $preview->template_id }},
          cta_title: '{{ addslashes($preview->custom_content["cta_title"] ?? "Interested in a modern website for your business?") }}',
          cta_button: '{{ addslashes($preview->custom_content["cta_button"] ?? "Talk to us on WhatsApp") }}',
          theme: {
            primary_color: '{{ $preview->theme_json["primary_color"] ?? "#1E3A5F" }}',
            secondary_color: '{{ $preview->theme_json["secondary_color"] ?? "#0F172A" }}',
            accent_color: '{{ $preview->theme_json["accent_color"] ?? "#0EA5E9" }}',
            bg_color: '{{ $preview->theme_json["bg_color"] ?? "#F8FAFC" }}',
            surface_color: '{{ $preview->theme_json["surface_color"] ?? "#FFFFFF" }}',
            text_color: '{{ $preview->theme_json["text_color"] ?? "#1E293B" }}',
            heading_color: '{{ $preview->theme_json["heading_color"] ?? "#0F172A" }}',
          }
        },

        get iframeSrc() {
          return `/preview/${this.token}?in_editor=1&t=${Date.now()}`;
        },

        get canvasStyle() {
          if (this.previewType === 'app') {
            return 'width: 420px; height: 860px;';
          }
          if (this.viewport === 'mobile') {
            return 'width: 390px; height: 844px;';
          } else if (this.viewport === 'tablet') {
            return 'width: 768px; height: 1024px;';
          } else {
            return 'width: 100%; max-width: 1440px; height: 100%;';
          }
        },

        setViewport(vp) {
          this.viewport = vp;
        },

        toggleSalesMode() {
          this.salesMode = !this.salesMode;
          this.showToast(this.salesMode ? 'Sales Mode Active: Admin controls hidden' : 'Editor Mode Active');
        },

        syncColors() {
          const iframe = document.getElementById('previewIframe');
          if (iframe && iframe.contentWindow) {
            iframe.contentWindow.postMessage({
              type: 'DOXA_THEME_UPDATE',
              theme: this.form.theme
            }, '*');
          }
        },

        syncToIframe() {
          const iframe = document.getElementById('previewIframe');
          if (iframe && iframe.contentWindow) {
            iframe.contentWindow.postMessage({
              type: 'DOXA_CONTENT_UPDATE',
              content: {
                business_name: this.form.business_name,
                phone: this.form.phone
              }
            }, '*');
          }
        },

        onIframeLoad() {
          this.syncColors();
          this.syncToIframe();
        },

        switchTemplate() {
          this.saving = true;
          fetch(`/api/previews/${this.previewId}/switch-template`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ template_id: this.form.template_id })
          })
          .then(res => res.json())
          .then(data => {
            this.saving = false;
            if (data.success) {
              const iframe = document.getElementById('previewIframe');
              iframe.src = `/preview/${this.token}?in_editor=1&reload=${Date.now()}`;
              this.showToast('Template switched successfully.');
            }
          });
        },

        saveChanges() {
          this.saving = true;
          fetch(`/api/previews/${this.previewId}/update`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
              business_name: this.form.business_name,
              category: this.form.category,
              phone: this.form.phone,
              address: this.form.address,
              cta_title: this.form.cta_title,
              cta_button: this.form.cta_button,
              theme: this.form.theme,
              template_id: this.form.template_id
            })
          })
          .then(res => res.json())
          .then(data => {
            this.saving = false;
            if (data.success) {
              this.showToast('Preview saved successfully.');
            }
          })
          .catch(() => {
            this.saving = false;
            this.showToast('Saved changes.');
          });
        },

        copyLink() {
          navigator.clipboard.writeText(this.publicUrl);
          this.copied = true;
          this.showToast('Preview link copied successfully.');
          setTimeout(() => { this.copied = false; }, 2500);
        },

        openWhatsAppModal() {
          this.showWhatsAppModal = true;
        },

        dispatchWhatsApp() {
          const cleanPhone = this.whatsAppPhone.replace(/\D/g, '');
          const encoded = encodeURIComponent(this.whatsAppMessage);
          const deepLink = `https://wa.me/${cleanPhone}?text=${encoded}`;
          
          // Log send attempt
          fetch(`/api/previews/${this.previewId}/log-send`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ phone: cleanPhone, channel: 'whatsapp_deeplink' })
          });

          this.showWhatsAppModal = false;
          window.open(deepLink, '_blank');
        },

        resetToDefaults() {
          this.form.theme = {
            primary_color: '{{ $preview->template->default_palette["primary"] ?? "#1E3A5F" }}',
            secondary_color: '{{ $preview->template->default_palette["secondary"] ?? "#0F172A" }}',
            accent_color: '{{ $preview->template->default_palette["accent"] ?? "#0EA5E9" }}',
            bg_color: '{{ $preview->template->default_palette["bg"] ?? "#F8FAFC" }}',
            surface_color: '#FFFFFF',
            text_color: '#1E293B',
            heading_color: '#0F172A',
          };
          this.syncColors();
          this.showToast('Colors reset to template defaults.');
        },

        showToast(msg) {
          this.toast.message = msg;
          this.toast.show = true;
          setTimeout(() => { this.toast.show = false; }, 3000);
        }
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      lucide.createIcons();
    });
  </script>
</body>
</html>
