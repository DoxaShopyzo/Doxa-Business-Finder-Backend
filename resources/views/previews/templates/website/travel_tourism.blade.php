@extends('layouts.preview_base')

@section('content')
<div class="container">
  <div style="background: linear-gradient(135deg, rgba(2, 132, 199, 0.08) 0%, rgba(6, 182, 212, 0.04) 100%); border: 1px solid rgba(2, 132, 199, 0.2); border-radius: var(--border-radius); padding: 48px 36px; margin-bottom: 36px;">
    <span class="badge" style="background: #e0f2fe; color: #0369a1; margin-bottom: 14px;">✈️ Domestic & International Holidays</span>
    <h2 style="font-size: 38px; font-weight: 800; color: var(--secondary); margin-bottom: 14px; line-height: 1.15;">
      {{ $preview->business_name }} — Explore The World With Confidence
    </h2>
    <p style="font-size: 16px; color: #475569; max-width: 720px; margin-bottom: 24px;">
      {{ $preview->custom_content['tagline'] ?? 'All-inclusive holiday tour packages, express tourist visa assistance, flight ticketing, and custom honeymoon itineraries.' }}
    </p>
    @if(!empty($preview->custom_content['phone']))
      <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $preview->custom_content['phone']) }}?text=Holiday%20Tour%20Package%20Inquiry" class="btn btn-whatsapp">
          🌴 Get Tour Itineraries on WhatsApp
        </a>
        <a href="tel:{{ $preview->custom_content['phone'] }}" class="btn btn-primary">
          📞 Speak to Travel Expert
        </a>
      </div>
    @endif
  </div>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px; margin-bottom: 40px;">
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <div style="font-size: 32px; margin-bottom: 10px;">🏖️</div>
      <h4 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">International Tours</h4>
      <p style="font-size: 13px; color: #64748b;">Singapore, Malaysia, Dubai, Thailand, Europe & Bali with premium Indian food.</p>
    </div>
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <div style="font-size: 32px; margin-bottom: 10px;">🏔️</div>
      <h4 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">Domestic Hill Stations</h4>
      <p style="font-size: 13px; color: #64748b;">Kashmir, Himachal, Ooty, Kodaikanal, Kerala & Goa private cab packages.</p>
    </div>
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <div style="font-size: 32px; margin-bottom: 10px;">🛂</div>
      <h4 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">Express Visa Services</h4>
      <p style="font-size: 13px; color: #64748b;">Doorstep document collection, embassy appointment booking, and travel insurance.</p>
    </div>
  </div>
</div>
@endsection
