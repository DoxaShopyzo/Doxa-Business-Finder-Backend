@extends('layouts.preview_app_frame')

@section('app_content')
<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 10px 14px; display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
  <span>🔍</span>
  <span style="font-size: 13px; color: #94a3b8;">Search 5,000+ Branded Products...</span>
</div>

<!-- Flash Sale Card -->
<div style="background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%); color: #ffffff; border-radius: 16px; padding: 16px; margin-bottom: 18px;">
  <span style="font-size: 10px; font-weight: 700; background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 10px;">MEGA SALE</span>
  <h3 style="font-size: 18px; font-weight: 800; margin-top: 4px;">Flat 30% Off Today!</h3>
  <p style="font-size: 11px; opacity: 0.9; margin-top: 2px;">Exclusive app deals with instant home delivery.</p>
</div>

<!-- Category Carousel -->
<div style="display: flex; gap: 8px; overflow-x: auto; padding-bottom: 8px; margin-bottom: 16px;">
  <span style="background: var(--primary); color: #fff; padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap;">All Deals</span>
  <span style="background: #fff; border: 1px solid #e2e8f0; padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 600; white-space: nowrap;">Trending</span>
  <span style="background: #fff; border: 1px solid #e2e8f0; padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 600; white-space: nowrap;">New Arrivals</span>
  <span style="background: #fff; border: 1px solid #e2e8f0; padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 600; white-space: nowrap;">Best Sellers</span>
</div>

<!-- Product Grid -->
<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 20px;">
  <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; text-align: center;">
    <div style="height: 80px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 32px; margin-bottom: 8px;">🛍️</div>
    <div style="font-size: 12px; font-weight: 700; text-align: left; margin-bottom: 4px;">Premium Collection</div>
    <div style="display: flex; justify-content: space-between; align-items: center;">
      <span style="font-size: 12px; font-weight: 800; color: var(--primary);">₹1,299</span>
      <span style="background: #f1f5f9; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700;">+ Add</span>
    </div>
  </div>
  <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; text-align: center;">
    <div style="height: 80px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 32px; margin-bottom: 8px;">✨</div>
    <div style="font-size: 12px; font-weight: 700; text-align: left; margin-bottom: 4px;">Special Edition</div>
    <div style="display: flex; justify-content: space-between; align-items: center;">
      <span style="font-size: 12px; font-weight: 800; color: var(--primary);">₹2,499</span>
      <span style="background: #f1f5f9; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700;">+ Add</span>
    </div>
  </div>
</div>
@endsection
