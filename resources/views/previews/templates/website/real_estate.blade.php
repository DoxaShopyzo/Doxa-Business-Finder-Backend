@extends('layouts.preview_base')

@section('content')
<div class="container">
  <div style="background: linear-gradient(135deg, rgba(15, 118, 110, 0.08) 0%, rgba(20, 184, 166, 0.04) 100%); border: 1px solid rgba(15, 118, 110, 0.2); border-radius: var(--border-radius); padding: 48px 36px; margin-bottom: 36px;">
    <span class="badge" style="background: #ccfbf1; color: #0f766e; margin-bottom: 14px;">🏡 Premium Real Estate & Plots</span>
    <h2 style="font-size: 38px; font-weight: 800; color: var(--secondary); margin-bottom: 14px; line-height: 1.15;">
      Exclusive Properties & Approved Projects by {{ $preview->business_name }}
    </h2>
    <p style="font-size: 16px; color: #475569; max-width: 720px; margin-bottom: 24px;">
      {{ $preview->custom_content['tagline'] ?? 'DTCP/RERA certified residential plots, premium luxury villas, and prime commercial investments.' }}
    </p>
    @if(!empty($preview->custom_content['phone']))
      <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $preview->custom_content['phone']) }}?text=Interested%20in%20Property%20Site%20Visit" class="btn btn-whatsapp">
          📅 Book Free Site Visit on WhatsApp
        </a>
        <a href="tel:{{ $preview->custom_content['phone'] }}" class="btn btn-primary">
          📞 Speak to Property Advisor
        </a>
      </div>
    @endif
  </div>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; margin-bottom: 40px;">
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <span class="badge" style="background: #dcfce7; color: #15803d; margin-bottom: 8px;">Ready To Build</span>
      <h4 style="font-size: 20px; font-weight: 800; margin-bottom: 8px;">Gated Community Plots</h4>
      <p style="font-size: 13px; color: #64748b; margin-bottom: 14px;">Blacktop tar roads, underground drainage, 24/7 security & water connection ready.</p>
      <div style="font-weight: 800; font-size: 18px; color: var(--primary);">₹1,450 / sq.ft onwards</div>
    </div>
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <span class="badge" style="background: #e0f2fe; color: #0369a1; margin-bottom: 8px;">New Launch</span>
      <h4 style="font-size: 20px; font-weight: 800; margin-bottom: 8px;">3 BHK Independent Luxury Villas</h4>
      <p style="font-size: 13px; color: #64748b; margin-bottom: 14px;">Modern architecture, covered car parking, private terrace & clubhouse access.</p>
      <div style="font-weight: 800; font-size: 18px; color: var(--primary);">₹65 Lakhs onwards</div>
    </div>
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <span class="badge" style="background: #fef3c7; color: #b45309; margin-bottom: 8px;">High ROI</span>
      <h4 style="font-size: 20px; font-weight: 800; margin-bottom: 8px;">Highway Commercial Showrooms</h4>
      <p style="font-size: 13px; color: #64748b; margin-bottom: 14px;">Main arterial road frontage, high footfall zone, ideal for banks & retail brands.</p>
      <div style="font-weight: 800; font-size: 18px; color: var(--primary);">High Rental Yield Potential</div>
    </div>
  </div>
</div>
@endsection
