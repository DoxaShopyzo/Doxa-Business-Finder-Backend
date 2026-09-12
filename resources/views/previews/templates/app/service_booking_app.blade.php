@extends('layouts.preview_app_frame')

@section('app_content')
<!-- Header Card -->
<div style="background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%); color: #ffffff; border-radius: 16px; padding: 16px; margin-bottom: 18px;">
  <span style="font-size: 10px; font-weight: 700; background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 10px;">EXPRESS SERVICE</span>
  <h3 style="font-size: 18px; font-weight: 800; margin-top: 4px;">Book Service in 60 Seconds</h3>
  <p style="font-size: 11px; opacity: 0.9; margin-top: 2px;">Verified professionals at your doorstep.</p>
</div>

<!-- Popular Services -->
<div style="font-size: 13px; font-weight: 800; color: var(--secondary); margin-bottom: 12px;">Select Service Type</div>
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 20px;">
  <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 8px; text-align: center;">
    <div style="font-size: 24px; margin-bottom: 4px;">⚡</div>
    <div style="font-size: 11px; font-weight: 700;">Express Check</div>
  </div>
  <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 8px; text-align: center;">
    <div style="font-size: 24px; margin-bottom: 4px;">🔧</div>
    <div style="font-size: 11px; font-weight: 700;">Maintenance</div>
  </div>
  <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 8px; text-align: center;">
    <div style="font-size: 24px; margin-bottom: 4px;">🛡️</div>
    <div style="font-size: 11px; font-weight: 700;">Warranty Care</div>
  </div>
</div>

<!-- Date / Time Slot Picker Mockup -->
<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 16px; margin-bottom: 16px;">
  <div style="font-size: 12px; font-weight: 800; margin-bottom: 10px;">Preferred Appointment Time</div>
  <div style="display: flex; gap: 8px; margin-bottom: 12px;">
    <span style="flex: 1; text-align: center; background: var(--primary); color: #fff; padding: 8px 4px; border-radius: 8px; font-size: 11px; font-weight: 700;">Today</span>
    <span style="flex: 1; text-align: center; background: #f1f5f9; padding: 8px 4px; border-radius: 8px; font-size: 11px; font-weight: 600;">Tomorrow</span>
    <span style="flex: 1; text-align: center; background: #f1f5f9; padding: 8px 4px; border-radius: 8px; font-size: 11px; font-weight: 600;">Weekend</span>
  </div>
  @if(!empty($preview->custom_content['phone']))
    <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $preview->custom_content['phone']) }}?text=Confirm%20Service%20Booking" style="display: block; text-align: center; background: #25D366; color: #fff; font-size: 12px; font-weight: 800; padding: 10px; border-radius: 8px; text-decoration: none;">
      💬 Confirm Booking via WhatsApp
    </a>
  @endif
</div>
@endsection
