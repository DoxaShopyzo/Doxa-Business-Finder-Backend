@extends('layouts.preview_base')

@section('content')
<div class="container">
  <div style="background: linear-gradient(135deg, rgba(120, 53, 15, 0.08) 0%, rgba(217, 119, 6, 0.04) 100%); border: 1px solid rgba(120, 53, 15, 0.2); border-radius: var(--border-radius); padding: 48px 36px; margin-bottom: 36px;">
    <span class="badge" style="background: #fef3c7; color: #92400e; margin-bottom: 14px;">🛋️ Luxury Interiors & Custom Furniture</span>
    <h2 style="font-size: 38px; font-weight: 800; color: var(--secondary); margin-bottom: 14px; line-height: 1.15;">
      {{ $preview->business_name }} — Crafting Beautiful Living Spaces
    </h2>
    <p style="font-size: 16px; color: #475569; max-width: 720px; margin-bottom: 24px;">
      {{ $preview->custom_content['tagline'] ?? 'Turnkey interior design, custom-made teakwood & modular furniture, and 3D architectural rendering.' }}
    </p>
    @if(!empty($preview->custom_content['phone']))
      <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $preview->custom_content['phone']) }}?text=Free%20Interior%20Design%20Consultation" class="btn btn-whatsapp">
          📐 Request Free 3D Design on WhatsApp
        </a>
        <a href="tel:{{ $preview->custom_content['phone'] }}" class="btn btn-primary">
          📞 Call Interior Specialist
        </a>
      </div>
    @endif
  </div>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px; margin-bottom: 40px;">
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <div style="font-size: 32px; margin-bottom: 10px;">🍳</div>
      <h4 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">Modular Kitchens</h4>
      <p style="font-size: 13px; color: #64748b;">German hardware, marine plywood carcasses, anti-scratch acrylic finishes.</p>
    </div>
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <div style="font-size: 32px; margin-bottom: 10px;">🛏️</div>
      <h4 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">Wardrobes & Bedrooms</h4>
      <p style="font-size: 13px; color: #64748b;">Space-saving floor-to-ceiling sliding wardrobes with integrated sensor lighting.</p>
    </div>
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <div style="font-size: 32px; margin-bottom: 10px;">🛋️</div>
      <h4 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">Handcrafted Living Furniture</h4>
      <p style="font-size: 13px; color: #64748b;">Solid teakwood dining sets, customized recliners, and designer accent units.</p>
    </div>
  </div>
</div>
@endsection
