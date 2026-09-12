@extends('layouts.preview_base')

@section('content')
<div class="container">
  <div style="background: linear-gradient(135deg, rgba(124, 45, 18, 0.08) 0%, rgba(234, 88, 12, 0.04) 100%); border: 1px solid rgba(124, 45, 18, 0.2); border-radius: var(--border-radius); padding: 48px 36px; margin-bottom: 36px;">
    <span class="badge" style="background: #ffedd5; color: #9a3412; margin-bottom: 14px;">🏨 Luxury Stays & Royal Hospitality</span>
    <h2 style="font-size: 38px; font-weight: 800; color: var(--secondary); margin-bottom: 14px; line-height: 1.15;">
      {{ $preview->business_name }} — Your Home Away From Home
    </h2>
    <p style="font-size: 16px; color: #475569; max-width: 720px; margin-bottom: 24px;">
      {{ $preview->custom_content['tagline'] ?? 'Well-appointed executive rooms, grand banquet halls, and multi-cuisine restaurant located right in the heart of the city.' }}
    </p>
    @if(!empty($preview->custom_content['phone']))
      <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $preview->custom_content['phone']) }}?text=Room%20Booking%20Inquiry" class="btn btn-whatsapp">
          🛎️ Check Room Availability & Tariff
        </a>
        <a href="tel:{{ $preview->custom_content['phone'] }}" class="btn btn-primary">
          📞 Call Front Desk: {{ $preview->custom_content['phone'] }}
        </a>
      </div>
    @endif
  </div>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px; margin-bottom: 40px;">
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <div style="font-size: 32px; margin-bottom: 10px;">🛏️</div>
      <h4 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">Executive & Suite Rooms</h4>
      <p style="font-size: 13px; color: #64748b;">Ultra-comfortable king beds, high-speed Wi-Fi, smart LED TV & room service.</p>
    </div>
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <div style="font-size: 32px; margin-bottom: 10px;">🥂</div>
      <h4 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">Grand Banquets & Marriages</h4>
      <p style="font-size: 13px; color: #64748b;">Spacious centralized air-conditioned hall with seating for up to 500 guests.</p>
    </div>
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <div style="font-size: 32px; margin-bottom: 10px;">🚗</div>
      <h4 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">Ample Parking & Airport Pickup</h4>
      <p style="font-size: 13px; color: #64748b;">Secure valet parking for 100+ vehicles with 24-hour travel desk assistance.</p>
    </div>
  </div>
</div>
@endsection
