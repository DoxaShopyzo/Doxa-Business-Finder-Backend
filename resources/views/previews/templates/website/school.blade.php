@extends('layouts.preview_base')

@section('content')
<div class="container">
  <div style="background: linear-gradient(135deg, rgba(29, 78, 216, 0.08) 0%, rgba(251, 191, 36, 0.04) 100%); border: 1px solid rgba(29, 78, 216, 0.2); border-radius: var(--border-radius); padding: 48px 36px; margin-bottom: 36px;">
    <span class="badge" style="background: #dbeafe; color: #1e40af; margin-bottom: 14px;">🎓 Admissions Open 2026-27</span>
    <h2 style="font-size: 38px; font-weight: 800; color: var(--secondary); margin-bottom: 14px; line-height: 1.15;">
      {{ $preview->business_name }} — Shaping Bright Futures
    </h2>
    <p style="font-size: 16px; color: #475569; max-width: 720px; margin-bottom: 24px;">
      {{ $preview->custom_content['tagline'] ?? 'Holistic education blending values, modern digital smart classrooms, creative arts, and athletic excellence.' }}
    </p>
    @if(!empty($preview->custom_content['phone']))
      <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $preview->custom_content['phone']) }}?text=Admission%20Inquiry" class="btn btn-whatsapp">
          📝 Get Admission Form on WhatsApp
        </a>
        <a href="tel:{{ $preview->custom_content['phone'] }}" class="btn btn-primary">
          📞 Speak to Admission Office
        </a>
      </div>
    @endif
  </div>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px; margin-bottom: 40px;">
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <div style="font-size: 32px; margin-bottom: 10px;">💻</div>
      <h4 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">Smart Classrooms & STEM</h4>
      <p style="font-size: 13px; color: #64748b;">Interactive digital boards, hands-on robotics, and modern computer labs.</p>
    </div>
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <div style="font-size: 32px; margin-bottom: 10px;">🏆</div>
      <h4 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">Sports & Athletics Complex</h4>
      <p style="font-size: 13px; color: #64748b;">Dedicated coaches for football, cricket, skating, badminton, and martial arts.</p>
    </div>
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <div style="font-size: 32px; margin-bottom: 10px;">🚌</div>
      <h4 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">Safe GPS-Tracked Transport</h4>
      <p style="font-size: 13px; color: #64748b;">Fleet of modern buses with live mobile tracking and trained female attendants.</p>
    </div>
  </div>
</div>
@endsection
