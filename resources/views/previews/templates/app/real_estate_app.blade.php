@extends('layouts.preview_app_frame')

@section('app_content')
<!-- Map / Search Banner -->
<div style="background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%); color: #ffffff; border-radius: 16px; padding: 16px; margin-bottom: 18px;">
  <span style="font-size: 10px; font-weight: 700; background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 10px;">PROPERTIES NEAR YOU</span>
  <h3 style="font-size: 18px; font-weight: 800; margin-top: 4px;">Explore Verified Plots & Villas</h3>
  <p style="font-size: 11px; opacity: 0.9; margin-top: 2px;">Map search with verified DTCP approvals.</p>
</div>

<!-- Property Types -->
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 18px;">
  <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 10px 6px; text-align: center;">
    <div style="font-size: 20px;">🏡</div>
    <div style="font-size: 11px; font-weight: 700; margin-top: 2px;">Villas</div>
  </div>
  <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 10px 6px; text-align: center;">
    <div style="font-size: 20px;">📐</div>
    <div style="font-size: 11px; font-weight: 700; margin-top: 2px;">Plots</div>
  </div>
  <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 10px 6px; text-align: center;">
    <div style="font-size: 20px;">🏢</div>
    <div style="font-size: 11px; font-weight: 700; margin-top: 2px;">Commercial</div>
  </div>
</div>

<!-- Featured Listing Card -->
<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; margin-bottom: 16px;">
  <div style="height: 100px; background: #ccfbf1; display: flex; align-items: center; justify-content: center; font-size: 38px;">🏡</div>
  <div style="padding: 12px;">
    <span style="background: #dcfce7; color: #166534; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px;">READY TO BUILD</span>
    <div style="font-size: 14px; font-weight: 800; margin-top: 6px;">Palm Green Avenue Plots</div>
    <div style="font-size: 11px; color: #64748b;">Gated Layout • 1200 - 2400 sq.ft</div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
      <span style="font-size: 13px; font-weight: 800; color: var(--primary);">₹18.5 Lakhs onwards</span>
      @if(!empty($preview->custom_content['phone']))
        <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $preview->custom_content['phone']) }}?text=Interested%20in%20Palm%20Green%20Avenue" style="background: var(--primary); color: #fff; font-size: 10px; font-weight: 700; padding: 6px 12px; border-radius: 6px; text-decoration: none;">Inquire</a>
      @endif
    </div>
  </div>
</div>
@endsection
