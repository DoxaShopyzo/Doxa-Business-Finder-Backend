@extends('layouts.preview_base')

@section('content')
<div class="container">
  <div style="background: linear-gradient(135deg, rgba(217, 119, 6, 0.08) 0%, rgba(37, 99, 235, 0.04) 100%); border: 1px solid rgba(217, 119, 6, 0.2); border-radius: var(--border-radius); padding: 48px 36px; margin-bottom: 36px;">
    <span class="badge" style="background: #fef3c7; color: #b45309; margin-bottom: 14px;">🚚 Pan-India Freight & Express Cargo</span>
    <h2 style="font-size: 38px; font-weight: 800; color: var(--secondary); margin-bottom: 14px; line-height: 1.15;">
      {{ $preview->business_name }} — Moving Business Forward, On Time
    </h2>
    <p style="font-size: 16px; color: #475569; max-width: 720px; margin-bottom: 24px;">
      {{ $preview->custom_content['tagline'] ?? 'Full truck load (FTL), express part-load cargo, temperature-controlled transport, and modern warehousing facilities.' }}
    </p>
    @if(!empty($preview->custom_content['phone']))
      <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $preview->custom_content['phone']) }}?text=Freight%20Tariff%20and%20Pickup%20Inquiry" class="btn btn-whatsapp">
          📦 Instant Freight Rate on WhatsApp
        </a>
        <a href="tel:{{ $preview->custom_content['phone'] }}" class="btn btn-primary">
          📞 Dispatch Control Desk
        </a>
      </div>
    @endif
  </div>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px; margin-bottom: 40px;">
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <div style="font-size: 32px; margin-bottom: 10px;">🚛</div>
      <h4 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">Full Truck Load (FTL)</h4>
      <p style="font-size: 13px; color: #64748b;">Direct point-to-point dedicated container trucks with guaranteed transit times.</p>
    </div>
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <div style="font-size: 32px; margin-bottom: 10px;">📡</div>
      <h4 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">Live GPS Tracking</h4>
      <p style="font-size: 13px; color: #64748b;">24/7 online vehicle tracking, automated electronic Proof-of-Delivery (e-POD).</p>
    </div>
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <div style="font-size: 32px; margin-bottom: 10px;">🏭</div>
      <h4 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">3PL Warehousing</h4>
      <p style="font-size: 13px; color: #64748b;">Secure palletized storage, inventory management, and regional distribution hub.</p>
    </div>
  </div>
</div>
@endsection
