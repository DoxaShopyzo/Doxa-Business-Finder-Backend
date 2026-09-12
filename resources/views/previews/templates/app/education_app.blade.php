@extends('layouts.preview_app_frame')

@section('app_content')
<div style="background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%); color: #ffffff; border-radius: 16px; padding: 16px; margin-bottom: 18px;">
  <span style="font-size: 10px; font-weight: 700; background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 10px;">STUDENT LEARNING APP</span>
  <h3 style="font-size: 18px; font-weight: 800; margin-top: 4px;">Smart Learning Portal</h3>
  <p style="font-size: 11px; opacity: 0.9; margin-top: 2px;">Access syllabus, video lessons & live tests.</p>
</div>

<!-- Student Modules -->
<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 18px;">
  <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; display: flex; align-items: center; gap: 10px;">
    <span style="font-size: 24px;">📚</span>
    <div>
      <div style="font-size: 12px; font-weight: 800;">Digital Library</div>
      <div style="font-size: 10px; color: #64748b;">Free Notes</div>
    </div>
  </div>
  <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; display: flex; align-items: center; gap: 10px;">
    <span style="font-size: 24px;">🎥</span>
    <div>
      <div style="font-size: 12px; font-weight: 800;">Video Lectures</div>
      <div style="font-size: 10px; color: #64748b;">Recorded HD</div>
    </div>
  </div>
</div>

<!-- Notice Board Card -->
<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px; margin-bottom: 16px;">
  <div style="font-size: 12px; font-weight: 800; color: #0f172a; margin-bottom: 8px;">📢 Latest Academy Notices</div>
  <div style="font-size: 11px; color: #475569; line-height: 1.5; margin-bottom: 10px;">
    • Admissions open for the upcoming academic session.<br>
    • Scholarship eligibility examination schedules announced.
  </div>
  @if(!empty($preview->custom_content['phone']))
    <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $preview->custom_content['phone']) }}?text=Student%20Admission%20Details" style="display: block; text-align: center; background: var(--primary); color: #fff; font-size: 11px; font-weight: 700; padding: 8px; border-radius: 6px; text-decoration: none;">
      Contact Academic Counselor
    </a>
  @endif
</div>
@endsection
