@extends('layouts.preview_app_frame')

@section('app_content')
<!-- Search Bar -->
<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 10px 14px; display: flex; align-items: center; gap: 8px; margin-bottom: 16px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
  <span style="font-size: 14px; color: #94a3b8;">🔍</span>
  <span style="font-size: 13px; color: #94a3b8;">Search Doctors, Departments & Tests...</span>
</div>

<!-- Emergency / OPD Banner -->
<div style="background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%); color: #ffffff; border-radius: 16px; padding: 16px; margin-bottom: 18px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
  <div style="display: flex; justify-content: space-between; align-items: flex-start;">
    <div>
      <span style="font-size: 10px; font-weight: 700; background: rgba(255,255,255,0.25); padding: 2px 8px; border-radius: 10px;">LIVE OPD TOKEN</span>
      <h3 style="font-size: 16px; font-weight: 800; margin-top: 6px;">Instant Doctor Booking</h3>
      <p style="font-size: 11px; opacity: 0.9; margin-top: 2px;">Skip hospital queues with our direct priority consultation passes.</p>
    </div>
    <span style="font-size: 28px;">🏥</span>
  </div>
  @if(!empty($preview->custom_content['phone']))
    <div style="margin-top: 12px;">
      <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $preview->custom_content['phone']) }}?text=Book%20Hospital%20Appointment" style="display: inline-block; background: #ffffff; color: var(--primary); font-size: 11px; font-weight: 800; padding: 6px 14px; border-radius: 8px; text-decoration: none;">
        Book Appointment Slot →
      </a>
    </div>
  @endif
</div>

<!-- Speciality Quick Grid -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
  <span style="font-size: 13px; font-weight: 800; color: var(--secondary);">Clinical Specialities</span>
  <span style="font-size: 11px; color: var(--primary); font-weight: 700;">View All</span>
</div>
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 20px;">
  <div style="text-align: center;">
    <div style="width: 50px; height: 50px; background: #ffffff; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 22px; margin: 0 auto 6px auto; border: 1px solid #e2e8f0; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">❤️</div>
    <span style="font-size: 10px; font-weight: 700; color: #475569;">Cardio</span>
  </div>
  <div style="text-align: center;">
    <div style="width: 50px; height: 50px; background: #ffffff; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 22px; margin: 0 auto 6px auto; border: 1px solid #e2e8f0; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">🦴</div>
    <span style="font-size: 10px; font-weight: 700; color: #475569;">Ortho</span>
  </div>
  <div style="text-align: center;">
    <div style="width: 50px; height: 50px; background: #ffffff; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 22px; margin: 0 auto 6px auto; border: 1px solid #e2e8f0; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">👶</div>
    <span style="font-size: 10px; font-weight: 700; color: #475569;">Pediatric</span>
  </div>
  <div style="text-align: center;">
    <div style="width: 50px; height: 50px; background: #ffffff; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 22px; margin: 0 auto 6px auto; border: 1px solid #e2e8f0; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">🧠</div>
    <span style="font-size: 10px; font-weight: 700; color: #475569;">Neuro</span>
  </div>
</div>

<!-- Available Doctors List -->
<div style="margin-bottom: 12px; font-size: 13px; font-weight: 800; color: var(--secondary);">Available Doctors Today</div>

<div style="background: #ffffff; border-radius: 14px; padding: 12px; margin-bottom: 10px; border: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
  <div style="display: flex; gap: 10px; align-items: center;">
    <div style="width: 42px; height: 42px; background: #e0f2fe; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px;">👨‍⚕️</div>
    <div>
      <div style="font-size: 13px; font-weight: 800; color: #0f172a;">Senior Consultant Physician</div>
      <div style="font-size: 10px; color: #64748b;">MBBS, MD • 14 Years Exp</div>
      <div style="font-size: 10px; color: #16a34a; font-weight: 700;">🟢 Slots Available Today</div>
    </div>
  </div>
  @if(!empty($preview->custom_content['phone']))
    <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $preview->custom_content['phone']) }}?text=Book%20Dr%20Slot" style="background: var(--primary); color: #fff; font-size: 10px; font-weight: 700; padding: 6px 10px; border-radius: 6px; text-decoration: none;">Book</a>
  @endif
</div>

<div style="background: #ffffff; border-radius: 14px; padding: 12px; margin-bottom: 16px; border: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
  <div style="display: flex; gap: 10px; align-items: center;">
    <div style="width: 42px; height: 42px; background: #fef3c7; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px;">👩‍⚕️</div>
    <div>
      <div style="font-size: 13px; font-weight: 800; color: #0f172a;">Cardiologist & Interventionist</div>
      <div style="font-size: 10px; color: #64748b;">MBBS, DM (Cardiology) • 18 Yrs Exp</div>
      <div style="font-size: 10px; color: #16a34a; font-weight: 700;">🟢 Slots Available Today</div>
    </div>
  </div>
  @if(!empty($preview->custom_content['phone']))
    <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $preview->custom_content['phone']) }}?text=Book%20Dr%20Slot" style="background: var(--primary); color: #fff; font-size: 10px; font-weight: 700; padding: 6px 10px; border-radius: 6px; text-decoration: none;">Book</a>
  @endif
</div>
@endsection
