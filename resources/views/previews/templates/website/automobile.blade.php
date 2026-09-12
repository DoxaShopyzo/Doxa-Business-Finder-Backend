@extends('layouts.preview_base')

@section('content')
<div class="container">
  <div style="background: linear-gradient(135deg, rgba(185, 28, 28, 0.08) 0%, rgba(225, 29, 72, 0.04) 100%); border: 1px solid rgba(185, 28, 28, 0.2); border-radius: var(--border-radius); padding: 48px 36px; margin-bottom: 36px;">
    <span class="badge" style="background: #ffe4e6; color: #9f1239; margin-bottom: 14px;">🏎️ Authorized Dealership & Multi-Brand Service</span>
    <h2 style="font-size: 38px; font-weight: 800; color: var(--secondary); margin-bottom: 14px; line-height: 1.15;">
      {{ $preview->business_name }} — Power, Style & Precision Care
    </h2>
    <p style="font-size: 16px; color: #475569; max-width: 720px; margin-bottom: 24px;">
      {{ $preview->custom_content['tagline'] ?? 'Experience seamless vehicle purchases, certified pre-owned deals, quick insurance renewal, and computerized periodic service.' }}
    </p>
    @if(!empty($preview->custom_content['phone']))
      <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $preview->custom_content['phone']) }}?text=Book%20Test%20Drive%20or%20Service" class="btn btn-whatsapp">
          🚦 Book Test Drive on WhatsApp
        </a>
        <a href="tel:{{ $preview->custom_content['phone'] }}" class="btn btn-primary">
          🔧 Service Booking Hotline
        </a>
      </div>
    @endif
  </div>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px; margin-bottom: 40px;">
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <div style="font-size: 32px; margin-bottom: 10px;">🚗</div>
      <h4 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">New Vehicle Showcase</h4>
      <p style="font-size: 13px; color: #64748b;">Instant on-road quotation, low down payment EMI schemes, and fast vehicle delivery.</p>
    </div>
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <div style="font-size: 32px; margin-bottom: 10px;">🛠️</div>
      <h4 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">Express Service Bay</h4>
      <p style="font-size: 13px; color: #64748b;">90-minute periodic maintenance, 3D wheel alignment, and genuine OEM spare parts.</p>
    </div>
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <div style="font-size: 32px; margin-bottom: 10px;">🛡️</div>
      <h4 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">Insurance & Claims Desk</h4>
      <p style="font-size: 13px; color: #64748b;">Cashless claim settlement with all major motor insurers and accident repair support.</p>
    </div>
  </div>
</div>
@endsection
