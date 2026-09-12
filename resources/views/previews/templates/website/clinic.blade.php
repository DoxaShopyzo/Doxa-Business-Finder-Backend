@extends('layouts.preview_base')

@section('content')
<div class="container">
  <div style="background: linear-gradient(135deg, rgba(13, 148, 136, 0.08) 0%, rgba(45, 212, 191, 0.04) 100%); border: 1px solid rgba(13, 148, 136, 0.2); border-radius: var(--border-radius); padding: 44px 32px; margin-bottom: 36px;">
    <span class="badge" style="background: #ccfbf1; color: #0f766e; margin-bottom: 14px;">Specialist Medical Consultation</span>
    <h2 style="font-size: 36px; font-weight: 800; color: var(--secondary); margin-bottom: 12px; line-height: 1.2;">
      Compassionate Consultation & Expert Clinic Care at {{ $preview->business_name }}
    </h2>
    <p style="font-size: 16px; color: #475569; max-width: 720px; margin-bottom: 24px;">
      {{ $preview->custom_content['tagline'] ?? 'Dedicated outpatient diagnostic care, specialist consultation, and individualized wellness guidance.' }}
    </p>
    @if(!empty($preview->custom_content['phone']))
      <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="tel:{{ $preview->custom_content['phone'] }}" class="btn btn-primary">📞 Call For Appointment</a>
        <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $preview->custom_content['phone']) }}?text=Appointment%20Booking" class="btn btn-whatsapp">💬 Instant WhatsApp Slot</a>
      </div>
    @endif
  </div>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; margin-bottom: 40px;">
    <div style="background: #fff; padding: 24px; border-radius: var(--border-radius); border: 1px solid #e2e8f0;">
      <div style="font-size: 24px; margin-bottom: 10px;">🩺</div>
      <h4 style="font-weight: 700; margin-bottom: 6px;">Specialist Consultation</h4>
      <p style="font-size: 13px; color: #64748b;">Comprehensive clinical diagnosis with experienced senior medical consultants.</p>
    </div>
    <div style="background: #fff; padding: 24px; border-radius: var(--border-radius); border: 1px solid #e2e8f0;">
      <div style="font-size: 24px; margin-bottom: 10px;">🧪</div>
      <h4 style="font-weight: 700; margin-bottom: 6px;">Point-of-Care Tests</h4>
      <p style="font-size: 13px; color: #64748b;">Rapid blood glucose, ECG, vitals screening, and computerized diagnostic reports.</p>
    </div>
    <div style="background: #fff; padding: 24px; border-radius: var(--border-radius); border: 1px solid #e2e8f0;">
      <div style="font-size: 24px; margin-bottom: 10px;">💊</div>
      <h4 style="font-weight: 700; margin-bottom: 6px;">Pharmacy & Follow-up</h4>
      <p style="font-size: 13px; color: #64748b;">Convenient medicine dispensing desk and digital follow-up consultation reminders.</p>
    </div>
  </div>
</div>
@endsection
