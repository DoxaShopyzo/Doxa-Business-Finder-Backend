@extends('layouts.preview_base')

@section('content')
<div class="container">
  <!-- Hero Section -->
  <div style="background: linear-gradient(135deg, rgba(30, 58, 95, 0.08) 0%, rgba(14, 165, 233, 0.04) 100%); border: 1px solid rgba(30, 58, 95, 0.15); border-radius: var(--border-radius); padding: 50px 36px; margin-bottom: 36px;">
    <div style="max-width: 800px;">
      <span class="badge" style="background: rgba(30, 58, 95, 0.1); color: var(--primary); margin-bottom: 14px;">
        ⭐ Verified Business Excellence
      </span>
      <h2 style="font-size: 38px; font-weight: 800; color: var(--secondary); margin-bottom: 14px; line-height: 1.15;">
        Welcome to {{ $preview->business_name }}
      </h2>
      <p style="font-size: 16px; color: #475569; margin-bottom: 24px; line-height: 1.6;">
        {{ $preview->custom_content['tagline'] ?? 'Dedicated to delivering trusted solutions, exceptional service quality, and reliable customer support in ' . ($preview->custom_content['address'] ?? 'our region') . '.' }}
      </p>

      <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        @if(!empty($preview->custom_content['phone']))
          <a href="tel:{{ $preview->custom_content['phone'] }}" class="btn btn-primary" style="font-size: 15px; padding: 12px 24px;">
            📞 Call Now: {{ $preview->custom_content['phone'] }}
          </a>
          <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $preview->custom_content['phone']) }}?text=Hello%20{{ rawurlencode($preview->business_name) }}%2C%20I%20have%20an%20inquiry" class="btn btn-whatsapp" style="font-size: 15px; padding: 12px 24px;">
            💬 Chat on WhatsApp
          </a>
        @endif
      </div>
    </div>
  </div>

  <!-- Services & Offerings -->
  <div style="margin-bottom: 48px;">
    <div style="text-align: center; max-width: 600px; margin: 0 auto 32px auto;">
      <span class="badge badge-primary">Our Solutions</span>
      <h3 style="font-size: 26px; font-weight: 800; color: var(--secondary); margin-top: 8px;">What We Offer</h3>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px;">
      <div style="background: #ffffff; padding: 26px; border-radius: var(--border-radius); border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
        <div style="font-size: 30px; margin-bottom: 12px;">🌟</div>
        <h4 style="font-size: 18px; font-weight: 700; color: var(--secondary); margin-bottom: 8px;">Premium Quality Standards</h4>
        <p style="font-size: 13px; color: #64748b;">We take pride in maintaining strict quality controls, verified materials, and industry best practices.</p>
      </div>

      <div style="background: #ffffff; padding: 26px; border-radius: var(--border-radius); border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
        <div style="font-size: 30px; margin-bottom: 12px;">⚡</div>
        <h4 style="font-size: 18px; font-weight: 700; color: var(--secondary); margin-bottom: 8px;">Fast & Dependable Turnaround</h4>
        <p style="font-size: 13px; color: #64748b;">Quick customer service response, on-time delivery, and personalized customer support.</p>
      </div>

      <div style="background: #ffffff; padding: 26px; border-radius: var(--border-radius); border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
        <div style="font-size: 30px; margin-bottom: 12px;">🤝</div>
        <h4 style="font-size: 18px; font-weight: 700; color: var(--secondary); margin-bottom: 8px;">Transparent Value Pricing</h4>
        <p style="font-size: 13px; color: #64748b;">Clear quotations with no hidden charges, genuine after-sales support, and customer satisfaction guarantee.</p>
      </div>
    </div>
  </div>

  <!-- Highlights & Verification Grid -->
  <div style="background: #ffffff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 36px; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 32px; align-items: center;">
    <div>
      <h3 style="font-size: 24px; font-weight: 800; color: var(--secondary); margin-bottom: 14px;">Why Customers Choose Us</h3>
      <ul style="list-style: none; display: flex; flex-direction: column; gap: 12px;">
        @foreach(($preview->custom_content['features'] ?? ['Verified Local Business', 'Prompt WhatsApp Inquiry Support', 'Top Rated Customer Service']) as $f)
          <li style="display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 600; color: #334155;">
            <span style="color: var(--primary); font-size: 18px;">✔</span> {{ $f }}
          </li>
        @endforeach
      </ul>
    </div>
    <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 12px; padding: 24px; text-align: center;">
      <div style="font-size: 44px; font-weight: 800; color: var(--primary);">{{ number_format($preview->custom_content['rating'] ?? 4.5, 1) }} ⭐</div>
      <div style="font-size: 14px; font-weight: 700; color: #1e293b; margin-top: 4px;">Google Maps Rating</div>
      <div style="font-size: 12px; color: #64748b;">Based on {{ $preview->custom_content['review_count'] ?? 40 }}+ customer reviews</div>
      <div style="margin-top: 14px;">
        <span style="font-size: 11px; background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 12px; font-weight: 600;">Verified Commercial Listing</span>
      </div>
    </div>
  </div>
</div>
@endsection
