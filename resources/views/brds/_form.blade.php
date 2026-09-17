@php
    $brd = $brd ?? null;
    $defaultStakeholders = old('stakeholders', $brd
        ? $brd->stakeholders->map(fn ($s) => [
            'name' => $s->name, 'role' => $s->role, 'department' => $s->department, 'responsibility' => $s->responsibility,
        ])->values()->all()
        : []);
    if (empty($defaultStakeholders)) {
        $defaultStakeholders = [['name' => '', 'role' => '', 'department' => '', 'responsibility' => '']];
    }
@endphp

@if($errors->any())
    <div class="mb-5 p-3.5 rounded-lg bg-red-50 border border-red-200">
        <ul class="text-xs text-red-600 space-y-0.5">
            @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
        </ul>
    </div>
@endif

<form method="POST"
      action="{{ $mode === 'create' ? route('brds.store') : route('brds.update', $brd) }}"
      enctype="multipart/form-data"
      x-data="{
          priority: '{{ old('priority', $brd->priority->value ?? 'medium') }}',
          stakeholders: {{ json_encode($defaultStakeholders) }},
          addStakeholder() { this.stakeholders.push({ name: '', role: '', department: '', responsibility: '' }); },
          removeStakeholder(i) { this.stakeholders.splice(i, 1); },
          submitted: false,
      }"
      @submit="submitted = true">
    @csrf

    <div class="space-y-6">
        <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1.5">Project <span class="text-red-500">*</span></label>
                    <select name="project_id" required
                            class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 bg-white transition-colors">
                        <option value="">Select project...</option>
                        @foreach($projects as $proj)
                            <option value="{{ $proj->id }}" {{ old('project_id', $brd->project_id ?? '') == $proj->id ? 'selected' : '' }}>{{ $proj->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1.5">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required
                           value="{{ old('title', $brd->title ?? '') }}"
                           class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 transition-colors">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5">Department <span class="text-slate-400 font-normal">(optional)</span></label>
                <input type="text" name="department"
                       value="{{ old('department', $brd->department ?? '') }}"
                       placeholder="Enter department"
                       class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 bg-white transition-colors">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5">Direct Manager <span class="text-slate-400 font-normal">(optional)</span></label>
                <input type="text" name="direct_manager"
                       value="{{ old('direct_manager', $brd->direct_manager ?? '') }}"
                       placeholder="Enter direct manager"
                       class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 bg-white transition-colors">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5">Priority <span class="text-red-500">*</span></label>
                <div class="flex items-center gap-1 bg-slate-100 rounded-lg p-1 w-fit">
                    <input type="hidden" name="priority" :value="priority">
                    <button type="button" @click="priority = 'low'"
                            class="px-3 py-1.5 rounded-md text-xs font-mono font-medium transition-all"
                            :class="priority === 'low' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700'">Low</button>
                    <button type="button" @click="priority = 'medium'"
                            class="px-3 py-1.5 rounded-md text-xs font-mono font-medium transition-all"
                            :class="priority === 'medium' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700'">Medium</button>
                    <button type="button" @click="priority = 'high'"
                            class="px-3 py-1.5 rounded-md text-xs font-mono font-medium transition-all"
                            :class="priority === 'high' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700'">High</button>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5">Description <span class="text-red-500">*</span></label>
                <textarea name="description" rows="4" required
                          placeholder="What is this document about..."
                          class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 placeholder-slate-400 resize-none transition-colors">{{ old('description', $brd->description ?? '') }}</textarea>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5">Business Objective <span class="text-slate-400 font-normal">(optional)</span></label>
                <textarea name="objective" rows="3"
                          placeholder="What business goal does this achieve..."
                          class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 placeholder-slate-400 resize-none transition-colors">{{ old('objective', $brd->objective ?? '') }}</textarea>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1.5">Scope <span class="text-slate-400 font-normal">(optional)</span></label>
                <textarea name="scope" rows="3"
                          placeholder="In-scope / out-of-scope items..."
                          class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 placeholder-slate-400 resize-none transition-colors">{{ old('scope', $brd->scope ?? '') }}</textarea>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-3">
                <label class="block text-xs font-medium text-slate-600">Stakeholders <span class="text-slate-400 font-normal">(optional)</span></label>
                <button type="button" @click="addStakeholder()"
                        class="inline-flex items-center gap-1 text-xs font-mono text-[#E26B3D] hover:text-[#c8602a] transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Stakeholder
                </button>
            </div>
            <div class="space-y-2">
                <template x-for="(sh, index) in stakeholders" :key="index">
                    <div class="flex items-start gap-2 p-3 rounded-lg border border-slate-200 bg-stone-50">
                        <div class="flex-1 grid grid-cols-2 gap-2">
                            <input type="text" :name="'stakeholders[' + index + '][name]'"
                                   :value="sh.name" @input="sh.name = $event.target.value"
                                   placeholder="Name"
                                   class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 transition-colors">
                            <input type="text" :name="'stakeholders[' + index + '][role]'"
                                   :value="sh.role" @input="sh.role = $event.target.value"
                                   placeholder="Role"
                                   class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 transition-colors">
                            <input type="text" :name="'stakeholders[' + index + '][department]'"
                                   :value="sh.department" @input="sh.department = $event.target.value"
                                   placeholder="Department"
                                   class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 transition-colors">
                            <input type="text" :name="'stakeholders[' + index + '][responsibility]'"
                                   :value="sh.responsibility" @input="sh.responsibility = $event.target.value"
                                   placeholder="Responsibility"
                                   class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 transition-colors">
                        </div>
                        <button type="button" @click="removeStakeholder(index)" x-show="stakeholders.length > 1"
                                class="p-2 text-slate-300 hover:text-red-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Current Process <span class="normal-case font-normal text-slate-400">("As-Is")</span></h2>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Workflow <span class="text-slate-400 font-normal">(optional)</span></label>
                        <textarea name="as_is_workflow" rows="3"
                                  placeholder="How is this done today..."
                                  class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 placeholder-slate-400 resize-none transition-colors">{{ old('as_is_workflow', $brd->as_is_workflow ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Pain Points <span class="text-slate-400 font-normal">(optional)</span></label>
                        <textarea name="as_is_pain_points" rows="3"
                                  placeholder="What's wrong with the current process..."
                                  class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 placeholder-slate-400 resize-none transition-colors">{{ old('as_is_pain_points', $brd->as_is_pain_points ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Existing Systems / Tools <span class="text-slate-400 font-normal">(optional)</span></label>
                        <textarea name="as_is_existing_systems" rows="2"
                                  placeholder="Systems or tools currently in use..."
                                  class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 placeholder-slate-400 resize-none transition-colors">{{ old('as_is_existing_systems', $brd->as_is_existing_systems ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Proposed Process <span class="normal-case font-normal text-slate-400">("To-Be")</span></h2>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Workflow <span class="text-slate-400 font-normal">(optional)</span></label>
                        <textarea name="to_be_workflow" rows="3"
                                  placeholder="How the process should work..."
                                  class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 placeholder-slate-400 resize-none transition-colors">{{ old('to_be_workflow', $brd->to_be_workflow ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Benefits <span class="text-slate-400 font-normal">(optional)</span></label>
                        <textarea name="to_be_benefits" rows="3"
                                  placeholder="What improves once this is in place..."
                                  class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 placeholder-slate-400 resize-none transition-colors">{{ old('to_be_benefits', $brd->to_be_benefits ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <label class="block text-xs font-medium text-slate-600 mb-1.5">KPIs <span class="text-slate-400 font-normal">(optional)</span></label>
            <textarea name="kpis" rows="3"
                      placeholder="How success will be measured..."
                      class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#E26B3D]/40 focus:border-[#E26B3D] text-slate-700 placeholder-slate-400 resize-none transition-colors">{{ old('kpis', $brd->kpis ?? '') }}</textarea>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <label class="block text-xs font-medium text-slate-600 mb-1.5">Attachments <span class="text-slate-400 font-normal">(optional)</span></label>
            <input type="file" name="attachments[]" multiple
                   class="w-full text-sm text-slate-700 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-[#E26B3D]/10 file:text-[#E26B3D] hover:file:bg-[#E26B3D]/20 focus:outline-none">
            <p class="text-xs text-slate-400 mt-1.5">jpg, png, pdf, doc, xls, ppt, txt, zip — up to 20MB each.</p>

            @if($brd && $brd->attachments->isNotEmpty())
            <div class="mt-4 pt-4 border-t border-slate-100 divide-y divide-slate-100">
                @foreach($brd->attachments as $attachment)
                <div class="flex items-center justify-between py-2.5 gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-800 truncate">{{ $attachment->file_name }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ number_format($attachment->file_size / 1024, 1) }} KB · {{ $attachment->created_at->format('d M Y, H:i') }}</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ Storage::disk('public')->url($attachment->file_path) }}" target="_blank" download
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 transition-colors">
                            Download
                        </a>
                        <button type="button"
                                @click="$dispatch('confirm:delete', { action: '{{ route('brds.attachments.destroy', [$brd, $attachment]) }}' })"
                                class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-[#E26B3D] px-6 py-2.5 text-sm font-medium text-white hover:bg-[#c8602a] transition-colors">
                {{ $mode === 'create' ? 'Submit BRD' : 'Save Changes' }}
            </button>
            <a href="{{ $mode === 'create' ? route('brds.index') : route('brds.show', $brd) }}"
               class="px-6 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                Cancel
            </a>
        </div>
    </div>
</form>
