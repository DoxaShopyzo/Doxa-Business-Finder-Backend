@extends('layouts.preview_base')

@section('content')
<div class="container">
  <div style="background: linear-gradient(135deg, rgba(67, 56, 202, 0.08) 0%, rgba(99, 102, 241, 0.04) 100%); border: 1px solid rgba(67, 56, 202, 0.2); border-radius: var(--border-radius); padding: 48px 36px; margin-bottom: 36px;">
    <span class="badge" style="background: #e0e7ff; color: #3730a3; margin-bottom: 14px;">🏛️ NAAC Accredited • 100% Placement Support</span>
    <h2 style="font-size: 38px; font-weight: 800; color: var(--secondary); margin-bottom: 14px; line-height: 1.15;">
      {{ $preview->business_name }} — Excellence in Higher Education
    </h2>
    <p style="font-size: 16px; color: #475569; max-width: 720px; margin-bottom: 24px;">
      {{ $preview->custom_content['tagline'] ?? 'Empowering ambitious youth with industry-aligned undergraduate & postgraduate programs, incubation labs, and multinational placements.' }}
    </p>
    @if(!empty($preview->custom_content['phone']))
      <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $preview->custom_content['phone']) }}?text=Course%20Details%20and%20Scholarship%20Inquiry" class="btn btn-whatsapp">
          📜 Get Degree Brochure on WhatsApp
        </a>
        <a href="tel:{{ $preview->custom_content['phone'] }}" class="btn btn-primary">
          📞 Contact Campus Counselor
        </a>
      </div>
    @endif
  </div>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px; margin-bottom: 40px;">
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <div style="font-size: 32px; margin-bottom: 10px;">💼</div>
      <h4 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">Top Tier Recruiters</h4>
      <p style="font-size: 13px; color: #64748b;">Annual campus placement drives by top multinational IT, finance, and core engineering firms.</p>
    </div>
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <div style="font-size: 32px; margin-bottom: 10px;">🔬</div>
      <h4 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">Research & Innovation Center</h4>
      <p style="font-size: 13px; color: #64748b;">Startup incubation cell, patent support, and industry-sponsored corporate research labs.</p>
    </div>
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <div style="font-size: 32px; margin-bottom: 10px;">🏅</div>
      <h4 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">Merit & Sports Scholarships</h4>
      <p style="font-size: 13px; color: #64748b;">Generous tuition fee waivers for meritorious students and national sports champions.</p>
    </div>
  </div>
</div>
@endsection
