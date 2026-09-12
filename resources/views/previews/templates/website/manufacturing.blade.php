@extends('layouts.preview_base')

@section('content')
<div class="container">
  <div style="background: linear-gradient(135deg, rgba(51, 65, 85, 0.08) 0%, rgba(245, 158, 11, 0.04) 100%); border: 1px solid rgba(51, 65, 85, 0.2); border-radius: var(--border-radius); padding: 48px 36px; margin-bottom: 36px;">
    <span class="badge" style="background: #e2e8f0; color: #1e293b; margin-bottom: 14px;">⚙️ Precision Industrial Manufacturing & OEM</span>
    <h2 style="font-size: 38px; font-weight: 800; color: var(--secondary); margin-bottom: 14px; line-height: 1.15;">
      {{ $preview->business_name }} — Engineered for Quality & Scale
    </h2>
    <p style="font-size: 16px; color: #475569; max-width: 720px; margin-bottom: 24px;">
      {{ $preview->custom_content['tagline'] ?? 'State-of-the-art automated production lines, certified quality testing, and reliable bulk supply chain delivery.' }}
    </p>
    @if(!empty($preview->custom_content['phone']))
      <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $preview->custom_content['phone']) }}?text=Request%20for%20Quotation%20(RFQ)" class="btn btn-whatsapp">
          📄 Submit RFQ on WhatsApp
        </a>
        <a href="tel:{{ $preview->custom_content['phone'] }}" class="btn btn-primary">
          📞 Speak to Technical Sales
        </a>
      </div>
    @endif
  </div>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px; margin-bottom: 40px;">
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <div style="font-size: 32px; margin-bottom: 10px;">🏭</div>
      <h4 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">Production Infrastructure</h4>
      <p style="font-size: 13px; color: #64748b;">High-throughput CNC machines, automated assembly, and strict safety protocols.</p>
    </div>
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <div style="font-size: 32px; margin-bottom: 10px;">🛡️</div>
      <h4 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">Quality Testing Lab</h4>
      <p style="font-size: 13px; color: #64748b;">Zero-defect inspection, dimensional tolerance verification, and ISO compliance.</p>
    </div>
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <div style="font-size: 32px; margin-bottom: 10px;">📦</div>
      <h4 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">Domestic & Export Supply</h4>
      <p style="font-size: 13px; color: #64748b;">Custom packaging, moisture-resistant sealing, and on-time freight dispatch.</p>
    </div>
  </div>
</div>
@endsection
