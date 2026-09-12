@extends('layouts.preview_base')

@section('content')
<div class="container">
  <div style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.08) 0%, rgba(59, 130, 246, 0.04) 100%); border: 1px solid rgba(30, 58, 138, 0.2); border-radius: var(--border-radius); padding: 48px 36px; margin-bottom: 36px;">
    <span class="badge" style="background: #dbeafe; color: #1e3a8a; margin-bottom: 14px;">⚖️ Corporate Advisory, Legal & Compliance</span>
    <h2 style="font-size: 38px; font-weight: 800; color: var(--secondary); margin-bottom: 14px; line-height: 1.15;">
      {{ $preview->business_name }} — Strategic Solutions for Modern Business
    </h2>
    <p style="font-size: 16px; color: #475569; max-width: 720px; margin-bottom: 24px;">
      {{ $preview->custom_content['tagline'] ?? 'Expert corporate taxation, company incorporation, regulatory compliance audits, and legal advisory.' }}
    </p>
    @if(!empty($preview->custom_content['phone']))
      <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $preview->custom_content['phone']) }}?text=Request%20Corporate%20Consultation" class="btn btn-whatsapp">
          💼 Book Consultation on WhatsApp
        </a>
        <a href="tel:{{ $preview->custom_content['phone'] }}" class="btn btn-primary">
          📞 Call Advisory Team
        </a>
      </div>
    @endif
  </div>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px; margin-bottom: 40px;">
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <div style="font-size: 32px; margin-bottom: 10px;">📊</div>
      <h4 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">GST & Income Tax</h4>
      <p style="font-size: 13px; color: #64748b;">Timely return filing, tax planning, scrutiny assessments, and appeals representation.</p>
    </div>
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <div style="font-size: 32px; margin-bottom: 10px;">📑</div>
      <h4 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">Company Incorporation</h4>
      <p style="font-size: 13px; color: #64748b;">Private Limited, LLP registration, MCA filings, and trademark copyright protection.</p>
    </div>
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <div style="font-size: 32px; margin-bottom: 10px;">🛡️</div>
      <h4 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">Statutory Internal Audit</h4>
      <p style="font-size: 13px; color: #64748b;">Robust financial health check, internal control reviews, and risk minimization.</p>
    </div>
  </div>
</div>
@endsection
