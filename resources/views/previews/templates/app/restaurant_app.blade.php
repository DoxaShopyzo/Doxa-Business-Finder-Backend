@extends('layouts.preview_app_frame')

@section('app_content')
<div style="background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%); color: #ffffff; border-radius: 16px; padding: 16px; margin-bottom: 18px;">
  <div style="display: flex; justify-content: space-between; align-items: center;">
    <div>
      <span style="font-size: 10px; font-weight: 700; background: rgba(255,255,255,0.25); padding: 2px 8px; border-radius: 10px;">HOT & FRESH</span>
      <h3 style="font-size: 18px; font-weight: 800; margin-top: 4px;">Hungry? Order Food!</h3>
      <p style="font-size: 11px; opacity: 0.9; margin-top: 2px;">Delivered hot & tasty in 30 minutes.</p>
    </div>
    <span style="font-size: 32px;">🍕</span>
  </div>
</div>

<!-- Category Chips -->
<div style="display: flex; gap: 8px; overflow-x: auto; padding-bottom: 8px; margin-bottom: 14px;">
  <span style="background: var(--primary); color: #fff; padding: 6px 14px; border-radius: 16px; font-size: 11px; font-weight: 700; white-space: nowrap;">All Menu</span>
  <span style="background: #fff; border: 1px solid #e2e8f0; padding: 6px 14px; border-radius: 16px; font-size: 11px; font-weight: 600; white-space: nowrap;">Biryani</span>
  <span style="background: #fff; border: 1px solid #e2e8f0; padding: 6px 14px; border-radius: 16px; font-size: 11px; font-weight: 600; white-space: nowrap;">Tandoori</span>
  <span style="background: #fff; border: 1px solid #e2e8f0; padding: 6px 14px; border-radius: 16px; font-size: 11px; font-weight: 600; white-space: nowrap;">Chinese</span>
</div>

<!-- Menu Items List -->
<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
  <div style="display: flex; gap: 10px; align-items: center;">
    <div style="width: 44px; height: 44px; background: #fff7ed; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 20px;">🍲</div>
    <div>
      <div style="font-size: 13px; font-weight: 700;">Special Dum Biryani</div>
      <div style="font-size: 11px; color: var(--primary); font-weight: 800;">₹260</div>
    </div>
  </div>
  <span style="background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700;">+ ADD</span>
</div>

<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
  <div style="display: flex; gap: 10px; align-items: center;">
    <div style="width: 44px; height: 44px; background: #fff7ed; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 20px;">🍗</div>
    <div>
      <div style="font-size: 13px; font-weight: 700;">Tandoori Chicken Half</div>
      <div style="font-size: 11px; color: var(--primary); font-weight: 800;">₹220</div>
    </div>
  </div>
  <span style="background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700;">+ ADD</span>
</div>

@if(!empty($preview->custom_content['phone']))
  <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $preview->custom_content['phone']) }}?text=I%20want%20to%20order%20food" style="display: block; text-align: center; background: #25D366; color: #fff; font-size: 12px; font-weight: 800; padding: 10px; border-radius: 8px; text-decoration: none;">
    🛵 Order Direct via WhatsApp
  </a>
@endif
@endsection
