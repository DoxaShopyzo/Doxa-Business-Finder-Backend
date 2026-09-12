@extends('layouts.preview_base')

@section('content')
<div class="container">
  <div style="background: linear-gradient(135deg, rgba(220, 38, 38, 0.08) 0%, rgba(249, 115, 22, 0.04) 100%); border: 1px solid rgba(220, 38, 38, 0.2); border-radius: var(--border-radius); padding: 48px 36px; margin-bottom: 36px;">
    <span class="badge" style="background: #fee2e2; color: #b91c1c; margin-bottom: 14px;">🍽️ Fresh & Authentic Culinary Delights</span>
    <h2 style="font-size: 38px; font-weight: 800; color: var(--secondary); margin-bottom: 14px; line-height: 1.15;">
      Welcome to {{ $preview->business_name }}
    </h2>
    <p style="font-size: 16px; color: #475569; max-width: 720px; margin-bottom: 24px;">
      {{ $preview->custom_content['tagline'] ?? 'Indulge in authentic traditional recipes, sizzling tandoori treats, and exquisite multi-cuisine family dining.' }}
    </p>
    @if(!empty($preview->custom_content['phone']))
      <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $preview->custom_content['phone']) }}?text=Table%20Reservation%20Inquiry" class="btn btn-whatsapp">
          📲 Reserve Table on WhatsApp
        </a>
        <a href="tel:{{ $preview->custom_content['phone'] }}" class="btn btn-primary">
          🛵 Order Takeaway / Home Delivery
        </a>
      </div>
    @endif
  </div>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px; margin-bottom: 40px;">
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <div style="font-size: 32px; margin-bottom: 10px;">🍲</div>
      <h4 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">Signature Biryani & Gravies</h4>
      <p style="font-size: 13px; color: #64748b;">Slow-cooked dum biryani with rich traditional aroma and spices.</p>
    </div>
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <div style="font-size: 32px; margin-bottom: 10px;">🔥</div>
      <h4 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">Live Clay Tandoor & Grill</h4>
      <p style="font-size: 13px; color: #64748b;">Succulent kebabs, naan, and sizzlers hot off the coal fire.</p>
    </div>
    <div style="background: #fff; border-radius: var(--border-radius); border: 1px solid #e2e8f0; padding: 24px;">
      <div style="font-size: 32px; margin-bottom: 10px;">🎉</div>
      <h4 style="font-size: 18px; font-weight: 800; margin-bottom: 6px;">A/C Family Party Hall</h4>
      <p style="font-size: 13px; color: #64748b;">Comfortable banquet seating for birthday parties and family gatherings.</p>
    </div>
  </div>
</div>
@endsection
