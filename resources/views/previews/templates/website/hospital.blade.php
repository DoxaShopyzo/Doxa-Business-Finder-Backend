@extends('layouts.preview_base')

@section('content')
<div class="container">
  <!-- Hero Section -->
  <div style="background: linear-gradient(135deg, rgba(2, 132, 199, 0.08) 0%, rgba(56, 189, 248, 0.03) 100%); border: 1px solid rgba(2, 132, 199, 0.15); border-radius: var(--border-radius); padding: 48px 36px; margin-bottom: 36px;">
    <div style="max-width: 800px;">
      <div style="display: inline-flex; align-items: center; gap: 8px; background: #ffffff; border: 1px solid #e0f2fe; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 700; color: var(--primary); margin-bottom: 18px;">
        <span>🚨 24/7 Trauma, ICU & Emergency Services</span>
      </div>
      <h2 style="font-size: 38px; font-weight: 800; color: var(--secondary); line-height: 1.15; margin-bottom: 16px;">
        Advanced Medical Care & Surgical Excellence at {{ $preview->business_name }}
      </h2>
      <p style="font-size: 16px; color: #475569; margin-bottom: 24px; line-height: 1.6;">
        {{ $preview->custom_content['tagline'] ?? 'Committed to delivering world-class healthcare with state-of-the-art diagnostic infrastructure and dedicated specialist physicians.' }}
      </p>

      <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        @if(!empty($preview->custom_content['phone']))
          <a href="tel:{{ $preview->custom_content['phone'] }}" class="btn btn-primary" style="font-size: 15px; padding: 12px 24px;">
            🏥 Call Emergency: {{ $preview->custom_content['phone'] }}
          </a>
          <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $preview->custom_content['phone']) }}?text=Doctor%20Appointment%20Inquiry" class="btn btn-whatsapp" style="font-size: 15px; padding: 12px 24px;">
            💬 Book OPD on WhatsApp
          </a>
        @endif
      </div>
    </div>
  </div>

  <!-- Key Clinical Capabilities -->
  <div style="margin-bottom: 48px;">
    <div style="text-align: center; max-width: 600px; margin: 0 auto 32px auto;">
      <span class="badge badge-primary">Super Specialities</span>
      <h3 style="font-size: 26px; font-weight: 800; color: var(--secondary); margin-top: 8px;">Comprehensive In-House Clinical Departments</h3>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
      <div style="background: #ffffff; padding: 24px; border-radius: var(--border-radius); border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
        <div style="width: 44px; height: 44px; background: rgba(2, 132, 199, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 22px; margin-bottom: 14px;">❤️</div>
        <h4 style="font-size: 18px; font-weight: 700; color: var(--secondary); margin-bottom: 8px;">Cardiology & ICU</h4>
        <p style="font-size: 13px; color: #64748b;">Comprehensive cardiac emergency care, modern cath lab facilities, and intensive coronary care.</p>
      </div>

      <div style="background: #ffffff; padding: 24px; border-radius: var(--border-radius); border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
        <div style="width: 44px; height: 44px; background: rgba(2, 132, 199, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 22px; margin-bottom: 14px;">🦴</div>
        <h4 style="font-size: 18px; font-weight: 700; color: var(--secondary); margin-bottom: 8px;">Orthopaedics & Joint</h4>
        <p style="font-size: 13px; color: #64748b;">Advanced joint replacement, arthroscopic procedures, trauma fracture stabilization and physio care.</p>
      </div>

      <div style="background: #ffffff; padding: 24px; border-radius: var(--border-radius); border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
        <div style="width: 44px; height: 44px; background: rgba(2, 132, 199, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 22px; margin-bottom: 14px;">👶</div>
        <h4 style="font-size: 18px; font-weight: 700; color: var(--secondary); margin-bottom: 8px;">Pediatrics & NICU</h4>
        <p style="font-size: 13px; color: #64748b;">Dedicated neonatal intensive care, child immunization schedules, and round-the-clock pediatric care.</p>
      </div>

      <div style="background: #ffffff; padding: 24px; border-radius: var(--border-radius); border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
        <div style="width: 44px; height: 44px; background: rgba(2, 132, 199, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 22px; margin-bottom: 14px;">🔬</div>
        <h4 style="font-size: 18px; font-weight: 700; color: var(--secondary); margin-bottom: 8px;">Diagnostics & Lab</h4>
        <p style="font-size: 13px; color: #64748b;">Automated pathology, digital X-Ray, ultrasound imaging, and rapid result reporting.</p>
      </div>
    </div>
  </div>

  <!-- Hospital Highlights & Patient Rating -->
  <div style="background: #ffffff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 36px; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 32px; align-items: center;">
    <div>
      <h3 style="font-size: 24px; font-weight: 800; color: var(--secondary); margin-bottom: 14px;">Why Patients Trust Our Healthcare</h3>
      <ul style="list-style: none; display: flex; flex-direction: column; gap: 12px;">
        @foreach(($preview->custom_content['features'] ?? ['NABH Compliant Infection Control', 'Zero Wait-Time Emergency Protocol', 'Cashless Insurance TPA Desk']) as $f)
          <li style="display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 600; color: #334155;">
            <span style="color: var(--primary); font-size: 18px;">✔</span> {{ $f }}
          </li>
        @endforeach
      </ul>
    </div>
    <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 12px; padding: 24px; text-align: center;">
      <div style="font-size: 44px; font-weight: 800; color: var(--primary);">{{ number_format($preview->custom_content['rating'] ?? 4.5, 1) }} ⭐</div>
      <div style="font-size: 14px; font-weight: 700; color: #1e293b; margin-top: 4px;">Verified Patient Satisfaction</div>
      <div style="font-size: 12px; color: #64748b;">Based on {{ $preview->custom_content['review_count'] ?? 50 }}+ verified reviews on Google Maps</div>
      <div style="margin-top: 16px;">
        <span style="font-size: 11px; background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 12px; font-weight: 600;">Google Verified Healthcare Provider</span>
      </div>
    </div>
  </div>
</div>
@endsection
