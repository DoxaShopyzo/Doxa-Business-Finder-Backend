@extends('layouts.preview_app_frame')

@section('app_content')
<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 10px 14px; display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
  <span>🔍</span>
  <span style="font-size: 13px; color: #94a3b8;">Search Catalog by SKU, Category, Model...</span>
</div>

<!-- Wholesale Banner -->
<div style="background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: #ffffff; border-radius: 16px; padding: 16px; margin-bottom: 18px;">
  <span style="font-size: 10px; font-weight: 700; background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 10px;">B2B CATALOGUE</span>
  <h3 style="font-size: 18px; font-weight: 800; margin-top: 4px;">Direct Factory Wholesale</h3>
  <p style="font-size: 11px; opacity: 0.9; margin-top: 2px;">Download high-resolution product specs & pricing.</p>
</div>

<!-- Category Sections -->
<div style="font-size: 13px; font-weight: 800; color: var(--secondary); margin-bottom: 12px;">Product Categories</div>

<div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; margin-bottom: 10px; display: flex; align-items: center; justify-content: space-between;">
  <div style="display: flex; align-items: center; gap: 10px;">
    <span style="font-size: 24px;">📦</span>
    <div>
      <div style="font-size: 13px; font-weight: 700;">Standard Commercial Series</div>
      <div style="font-size: 10px; color: #64748b;">48 Models in Stock • Ready Dispatch</div>
    </div>
  </div>
  <span style="font-size: 11px; color: var(--primary); font-weight: 700;">View →</span>
</div>

<div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between;">
  <div style="display: flex; align-items: center; gap: 10px;">
    <span style="font-size: 24px;">⚙️</span>
    <div>
      <div style="font-size: 13px; font-weight: 700;">Heavy-Duty Industrial Series</div>
      <div style="font-size: 10px; color: #64748b;">Custom OEM Engineering Specifications</div>
    </div>
  </div>
  <span style="font-size: 11px; color: var(--primary); font-weight: 700;">View →</span>
</div>

@if(!empty($preview->custom_content['phone']))
  <div style="text-align: center; margin-top: 16px;">
    <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $preview->custom_content['phone']) }}?text=Wholesale%20Catalogue%20Inquiry" style="display: block; background: #25D366; color: #fff; font-size: 12px; font-weight: 800; padding: 10px; border-radius: 8px; text-decoration: none;">
      📲 Request Full PDF Price List on WhatsApp
    </a>
  </div>
@endif
@endsection
