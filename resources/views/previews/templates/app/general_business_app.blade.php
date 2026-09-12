@extends('layouts.preview_app_frame')

@section('app_content')
<!-- Hero App Banner -->
<div style="background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%); color: #ffffff; border-radius: 16px; padding: 18px 16px; margin-bottom: 18px;">
  <span style="font-size: 10px; font-weight: 700; background: rgba(255,255,255,0.25); padding: 2px 8px; border-radius: 10px;">OFFICIAL MOBILE APP</span>
  <h3 style="font-size: 18px; font-weight: 800; margin-top: 6px; line-height: 1.2;">{{ $preview->business_name }}</h3>
  <p style="font-size: 11px; opacity: 0.9; margin-top: 4px;">{{ $preview->custom_content['tagline'] ?? 'Instant service requests, real-time customer support, and direct booking.' }}</p>
</div>

<!-- Quick Action Shortcuts -->
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 18px;">
  <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 10px 4px; text-align: center;">
    <div style="font-size: 20px;">⭐</div>
    <div style="font-size: 10px; font-weight: 700; margin-top: 4px;">Services</div>
  </div>
  <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 10px 4px; text-align: center;">
    <div style="font-size: 20px;">💬</div>
    <div style="font-size: 10px; font-weight: 700; margin-top: 4px;">Chat</div>
  </div>
  <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 10px 4px; text-align: center;">
    <div style="font-size: 20px;">📞</div>
    <div style="font-size: 10px; font-weight: 700; margin-top: 4px;">Call</div>
  </div>
  <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 10px 4px; text-align: center;">
    <div style="font-size: 20px;">📍</div>
    <div style="font-size: 10px; font-weight: 700; margin-top: 4px;">Locate</div>
  </div>
</div>

<!-- Featured Offerings -->
<div style="font-size: 13px; font-weight: 800; color: var(--secondary); margin-bottom: 10px;">Popular Offerings</div>

<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 12px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
  <div>
    <div style="font-size: 13px; font-weight: 700;">Standard Consultation / Service</div>
    <div style="font-size: 10px; color: #64748b;">Fast appointment • 100% verified staff</div>
  </div>
  @if(!empty($preview->custom_content['phone']))
    <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $preview->custom_content['phone']) }}?text=Inquiry%20from%20Mobile%20App" style="background: var(--primary); color: #fff; font-size: 11px; font-weight: 700; padding: 6px 12px; border-radius: 6px; text-decoration: none;">Inquire</a>
  @endif
</div>

<!-- Business Details Card -->
<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px; margin-top: 14px;">
  <div style="font-size: 12px; font-weight: 800; margin-bottom: 6px;">Commercial Information</div>
  <div style="font-size: 11px; color: #475569; line-height: 1.6;">
    📍 {{ $preview->custom_content['address'] ?? 'Local Address on File' }}<br>
    ⭐ {{ number_format($preview->custom_content['rating'] ?? 4.5, 1) }} ({{ $preview->custom_content['review_count'] ?? 30 }}+ reviews)<br>
    🕒 Open Today • Fast Turnaround
  </div>
</div>
@endsection
