<div x-data="previewGeneratorModal()" 
     x-on:open-preview-generator.window="startGeneration($event.detail)"
     x-show="isOpen" 
     x-cloak 
     class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 z-50">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 space-y-6 shadow-2xl text-white">
        
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-600/20 text-blue-400 flex items-center justify-center">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-white" x-text="previewType === 'app' ? 'Generating Mobile App Preview' : 'Generating Website Preview'"></h3>
                    <p class="text-xs text-slate-400" x-text="businessName"></p>
                </div>
            </div>
            <button x-show="isComplete" @click="isOpen = false" class="text-slate-400 hover:text-white p-1">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Animated Progress Steps (Section 32) -->
        <div class="space-y-4 text-xs">
            <!-- Step 1 -->
            <div class="flex items-start gap-3 transition" :class="step >= 1 ? 'text-white' : 'text-slate-500'">
                <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5" 
                     :class="step > 1 ? 'bg-emerald-500 text-slate-950 font-bold' : (step === 1 ? 'border-2 border-blue-400 border-t-transparent animate-spin' : 'bg-slate-800 text-slate-500')">
                    <template x-if="step > 1"><span>✓</span></template>
                </div>
                <div>
                    <p class="font-semibold" x-text="step > 1 ? '✓ Business Found' : 'Preparing Business Data...'"></p>
                    <p class="text-[11px] text-slate-400" x-text="location ? location : 'Extracting location, address, phone & category'"></p>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="flex items-start gap-3 transition" :class="step >= 2 ? 'text-white' : 'text-slate-500'">
                <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5" 
                     :class="step > 2 ? 'bg-emerald-500 text-slate-950 font-bold' : (step === 2 ? 'border-2 border-blue-400 border-t-transparent animate-spin' : 'bg-slate-800 text-slate-500')">
                    <template x-if="step > 2"><span>✓</span></template>
                </div>
                <div>
                    <p class="font-semibold" x-text="step > 2 ? '✓ ' + templateName + ' Selected' : 'Selecting Template...'"></p>
                    <p class="text-[11px] text-slate-400">Matching category against master templates</p>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="flex items-start gap-3 transition" :class="step >= 3 ? 'text-white' : 'text-slate-500'">
                <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5" 
                     :class="step > 3 ? 'bg-emerald-500 text-slate-950 font-bold' : (step === 3 ? 'border-2 border-blue-400 border-t-transparent animate-spin' : 'bg-slate-800 text-slate-500')">
                    <template x-if="step > 3"><span>✓</span></template>
                </div>
                <div>
                    <p class="font-semibold" x-text="step > 3 ? '✓ Brand Colours Applied' : 'Applying Branding...'"></p>
                    <p class="text-[11px] text-slate-400">Injecting brand initials monogram & CSS palette</p>
                </div>
            </div>

            <!-- Step 4 -->
            <div class="flex items-start gap-3 transition" :class="step >= 4 ? 'text-white' : 'text-slate-500'">
                <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5" 
                     :class="step > 4 ? 'bg-emerald-500 text-slate-950 font-bold' : (step === 4 ? 'border-2 border-blue-400 border-t-transparent animate-spin' : 'bg-slate-800 text-slate-500')">
                    <template x-if="step > 4"><span>✓</span></template>
                </div>
                <div>
                    <p class="font-semibold" x-text="step > 4 ? '✓ Assets Ready' : 'Preparing Images...'"></p>
                    <p class="text-[11px] text-slate-400">Curating industry showcase imagery & gallery</p>
                </div>
            </div>

            <!-- Step 5 -->
            <div class="flex items-start gap-3 transition" :class="step >= 5 ? 'text-white' : 'text-slate-500'">
                <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5" 
                     :class="step >= 5 ? 'bg-emerald-500 text-slate-950 font-bold' : (step === 4 ? 'border-2 border-blue-400 border-t-transparent animate-spin' : 'bg-slate-800 text-slate-500')">
                    <template x-if="step >= 5"><span>✓</span></template>
                </div>
                <div>
                    <p class="font-semibold" x-text="step >= 5 ? '✓ Preview Ready' : 'Generating Preview...'"></p>
                    <p class="text-[11px] text-slate-400" x-text="step >= 5 ? 'Redirecting to interactive Preview Editor...' : 'Building secure token & hosting link'"></p>
                </div>
            </div>
        </div>

        <!-- Completion Actions -->
        <div x-show="isComplete" class="pt-2 flex items-center gap-2">
            <a :href="'/admin/previews/' + generatedPreviewId + '/editor'" class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold text-center shadow-sm transition">
                Open Preview Editor
            </a>
            <a :href="publicUrl" target="_blank" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-xl text-xs font-semibold transition">
                Direct Link
            </a>
        </div>
    </div>
</div>

<script>
function previewGeneratorModal() {
    return {
        isOpen: false,
        isComplete: false,
        leadId: null,
        leadType: 'campaign',
        previewType: 'website',
        businessName: '',
        location: '',
        templateName: 'Master Template',
        generatedPreviewId: null,
        publicUrl: '',
        step: 0,

        startGeneration(detail) {
            this.leadId = detail.leadId;
            this.leadType = detail.leadType || 'campaign';
            this.previewType = detail.previewType || 'website';
            this.businessName = detail.businessName || 'Business Lead';
            this.location = detail.location || '';
            this.templateName = detail.category ? (detail.category + ' Template') : 'Master Template';
            this.isOpen = true;
            this.isComplete = false;
            this.step = 1;

            // Step 1 -> Step 2
            setTimeout(() => { this.step = 2; }, 600);
            setTimeout(() => { this.step = 3; }, 1200);
            setTimeout(() => { this.step = 4; }, 1800);

            // API Dispatch
            fetch('/api/previews/generate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    lead_id: this.leadId,
                    lead_type: this.leadType,
                    preview_type: this.previewType,
                    force_new: detail.forceNew || false
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.step = 5;
                    this.isComplete = true;
                    this.generatedPreviewId = data.preview.id;
                    this.publicUrl = data.public_url;
                    this.templateName = data.preview.template ? data.preview.template.name : 'Master Template';

                    // Redirect to editor automatically after 1 second
                    setTimeout(() => {
                        window.location.href = `/admin/previews/${this.generatedPreviewId}/editor`;
                    }, 1200);
                } else {
                    alert('Preview generation error: ' + (data.message || 'Unknown error'));
                    this.isOpen = false;
                }
            })
            .catch(err => {
                console.error(err);
                this.step = 5;
                this.isComplete = true;
            });
        }
    }
}
</script>
