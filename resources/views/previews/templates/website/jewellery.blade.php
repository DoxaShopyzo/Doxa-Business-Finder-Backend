@extends('layouts.preview_base')

@section('content')
<div class="container">
  <div style="background: linear-gradient(135deg, #1e1b18 0%, #29221b 100%); color: #fff; border-radius: var(--border-radius); padding: 50px 36px; margin-bottom: 36px; border: 1px solid rgba(245, 158, 11, 0.3);">
    <div style="display: inline-block; background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid #f59e0b; padding: 4px 12px; border-radius: 16px; font-size: 12px; font-weight: 700; margin-bottom: 16px;">
      ✨ 100% BIS 916 HALLMARKED PURITY
    </div>
    <h2 style="font-size: 38px; font-weight: 800; color: #fff; line-height: 1.2; margin-bottom: 14px;">
      Timeless Royal Gold & Diamond Jewellery at {{ $preview->business_name }}
    </h2>
    <p style="font-size: 16px; color: #d1d5db; max-width: 680px; margin-bottom: 24px;">
      {{ $preview->custom_content['tagline'] ?? 'Handcrafted bridal jewels, certified lightweight gold, and bespoke certified solitaires created for your most cherished milestones.' }}
    </p>
    @if(!empty($preview->custom_content['phone']))
      <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $preview->custom_content['phone']) }}?text=Check%20Today%20Gold%20Rate" class="btn btn-whatsapp" style="background: #f59e0b; color: #18181b;">
          🏆 Get Today's Gold Rate & Offers
        </a>
        <a href="tel:{{ $preview->custom_content['phone'] }}" class="btn" style="background: rgba(255,255,255,0.15); color: #fff;">
          📞 Call Showroom Desk
        </a>
      </div>
    @endif
  </div>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 40px;">
    <div style="background: #fff; padding: 24px; border-radius: var(--border-radius); border: 1px solid #e2e8f0; text-align: center;">
      <div style="font-size: 32px; margin-bottom: 10px;">👑</div>
      <h4 style="font-weight: 800; font-size: 18px; margin-bottom: 6px;">Bridal Heritage Sets</h4>
      <p style="font-size: 13px; color: #64748b;">Antiques, Kundan, and Temple Jewellery designed for royal bridal occasions.</p>
    </div>
    <div style="background: #fff; padding: 24px; border-radius: var(--border-radius); border: 1px solid #e2e8f0; text-align: center;">
      <div style="font-size: 32px; margin-bottom: 10px;">💎</div>
      <h4 style="font-weight: 800; font-size: 18px; margin-bottom: 6px;">Certified Diamonds</h4>
      <p style="font-size: 13px; color: #64748b;">IGI/GIA certified solitaire rings, necklace sets, and diamond bangles.</p>
    </div>
    <div style="background: #fff; padding: 24px; border-radius: var(--border-radius); border: 1px solid #e2e8f0; text-align: center;">
      <div style="font-size: 32px; margin-bottom: 10px;">🪙</div>
      <h4 style="font-weight: 800; font-size: 18px; margin-bottom: 6px;">Monthly Gold Schemes</h4>
      <p style="font-size: 13px; color: #64748b;">Zero wastage purchase plans with bonus contribution upon maturity.</p>
    </div>
  </div>
</div>
@endsection
