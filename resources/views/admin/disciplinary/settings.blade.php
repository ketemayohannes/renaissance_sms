<x-admin-layout>
    <x-slot name="header">Discipline Settings</x-slot>

    <div class="space-y-8 pb-12" x-data="{ 
        activeTab: 'infractions',
        showInfractionModal: false,
        showRuleModal: false,
        editMode: false,
        infractionForm: { id: '', name: '', tier: 'minor', description: '', default_penalty: '', requires_parent_notification: false, display_order: 0 },
        ruleForm: { id: '', infraction_definition_id: '', tier: 'minor', occurrence_threshold: 1, time_window_days: '', escalation_action: 'verbal_warning', escalation_description: '', auto_notify_parent: true, legal_reference: '' },
        openInfractionEdit(def) {
            this.editMode = true;
            this.infractionForm = { id: def.id, name: def.name, tier: def.tier, description: def.description || '', default_penalty: def.default_penalty || '', requires_parent_notification: def.requires_parent_notification, display_order: def.display_order };
            this.showInfractionModal = true;
        },
        openRuleEdit(rule) {
            this.editMode = true;
            this.ruleForm = { id: rule.id, infraction_definition_id: rule.infraction_definition_id || '', tier: rule.tier, occurrence_threshold: rule.occurrence_threshold, time_window_days: rule.time_window_days || '', escalation_action: rule.escalation_action, escalation_description: rule.escalation_description || '', auto_notify_parent: rule.auto_notify_parent, legal_reference: rule.legal_reference || '' };
            this.showRuleModal = true;
        },
        resetInfractionForm() {
            this.editMode = false;
            this.infractionForm = { id: '', name: '', tier: 'minor', description: '', default_penalty: '', requires_parent_notification: false, display_order: 0 };
        },
        resetRuleForm() {
            this.editMode = false;
            this.ruleForm = { id: '', infraction_definition_id: '', tier: 'minor', occurrence_threshold: 1, time_window_days: '', escalation_action: 'verbal_warning', escalation_description: '', auto_notify_parent: true, legal_reference: '' };
        }
    }">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <x-breadcrumb :items="[
                    ['label' => 'Behavior Management', 'url' => route('admin.disciplinary.index')],
                    ['label' => 'Discipline Settings', 'url' => '#']
                ]" />
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mt-2 flex items-center gap-3">
                    <span class="w-1.5 h-8 bg-indigo-600 rounded-full"></span>
                    Discipline Engine
                </h1>
                <p class="text-slate-500 font-semibold mt-1 uppercase text-[10px] tracking-[0.2em] italic">Configure infraction types, severity tiers, and automatic escalation rules</p>
            </div>

            <a href="{{ route('admin.disciplinary.index') }}" class="px-6 py-4 bg-white border border-slate-200 rounded-2xl font-black text-[10px] uppercase tracking-widest text-slate-600 hover:bg-slate-50 transition-all flex items-center gap-3 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Behavior Log
            </a>
        </div>

        <!-- Tab Navigation -->
        <div class="bg-white/40 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-xl shadow-slate-200/50 p-2 flex items-center gap-2">
            <button @click="activeTab = 'infractions'" :class="activeTab === 'infractions' ? 'bg-slate-900 text-white shadow-xl' : 'text-slate-500 hover:bg-slate-50'" class="flex-1 px-6 py-3 rounded-[2rem] font-black text-[10px] uppercase tracking-widest transition-all">
                Infraction Definitions
            </button>
            <button @click="activeTab = 'rules'" :class="activeTab === 'rules' ? 'bg-slate-900 text-white shadow-xl' : 'text-slate-500 hover:bg-slate-50'" class="flex-1 px-6 py-3 rounded-[2rem] font-black text-[10px] uppercase tracking-widest transition-all">
                Escalation Rules
            </button>
        </div>

        <!-- ══════ INFRACTION DEFINITIONS TAB ══════ -->
        <div x-show="activeTab === 'infractions'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-black text-slate-900 uppercase tracking-tight">Infraction Directory</h2>
                <button @click="resetInfractionForm(); showInfractionModal = true" class="px-8 py-4 bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest rounded-[2rem] hover:bg-indigo-600 shadow-xl shadow-slate-200 transition-all flex items-center gap-3 group">
                    <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center group-hover:bg-white/20 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </div>
                    New Infraction Type
                </button>
            </div>

            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-white shadow-2xl shadow-slate-200/50 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-0">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-8 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Infraction Name</th>
                                <th class="px-6 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Tier</th>
                                <th class="px-6 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Default Penalty</th>
                                <th class="px-6 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Parent Alert</th>
                                <th class="px-6 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-center">Status</th>
                                <th class="px-8 py-4 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($definitions as $def)
                            <tr class="group hover:bg-slate-50/50 transition-colors {{ !$def->is_active ? 'opacity-50' : '' }}">
                                <td class="px-8 py-6">
                                    <span class="block text-sm font-black text-slate-900">{{ $def->name }}</span>
                                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ $def->code }}</span>
                                    @if($def->description)
                                        <span class="block text-xs text-slate-500 mt-1 max-w-xs truncate">{{ $def->description }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-6 text-center">
                                    @php
                                        $tierColors = [
                                            'minor' => 'bg-emerald-100 text-emerald-600',
                                            'moderate' => 'bg-amber-100 text-amber-600',
                                            'critical' => 'bg-rose-100 text-rose-600',
                                        ];
                                    @endphp
                                    <span class="px-3 py-1.5 {{ $tierColors[$def->tier] ?? 'bg-slate-100 text-slate-500' }} rounded-xl text-[10px] font-black uppercase tracking-widest">
                                        {{ $def->display_tier }}
                                    </span>
                                </td>
                                <td class="px-6 py-6">
                                    <span class="text-xs font-semibold text-slate-600">{{ $def->default_penalty ?? '—' }}</span>
                                </td>
                                <td class="px-6 py-6 text-center">
                                    @if($def->requires_parent_notification)
                                        <span class="px-2 py-1 bg-rose-50 text-rose-500 rounded-lg text-[9px] font-black uppercase">Required</span>
                                    @else
                                        <span class="text-[10px] font-bold text-slate-300">Optional</span>
                                    @endif
                                </td>
                                <td class="px-6 py-6 text-center">
                                    <form action="{{ route('admin.discipline-settings.infractions.toggle', $def) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all {{ $def->is_active ? 'bg-emerald-50 text-emerald-500 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-50 text-slate-400 border-slate-200 hover:bg-slate-100' }}">
                                            {{ $def->is_active ? 'Active' : 'Archived' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <button @click="openInfractionEdit({{ $def->toJson() }})" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-600 font-black text-[10px] uppercase tracking-widest rounded-xl hover:bg-slate-50 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm">
                                        Edit
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-12 py-20 text-center">
                                    <div class="w-20 h-20 bg-slate-50 rounded-[2rem] flex items-center justify-center text-slate-200 mx-auto mb-4">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                    </div>
                                    <h3 class="text-xl font-black text-slate-900 tracking-tight italic">No Infractions Defined</h3>
                                    <p class="text-slate-500 font-semibold mt-2 text-sm">Create your first infraction type to begin building the discipline framework.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ══════ ESCALATION RULES TAB ══════ -->
        <div x-show="activeTab === 'rules'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-black text-slate-900 uppercase tracking-tight">Escalation Rules</h2>
                <button @click="resetRuleForm(); showRuleModal = true" class="px-8 py-4 bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest rounded-[2rem] hover:bg-indigo-600 shadow-xl shadow-slate-200 transition-all flex items-center gap-3 group">
                    <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center group-hover:bg-white/20 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </div>
                    New Escalation Rule
                </button>
            </div>

            @foreach(['minor' => 'Minor', 'moderate' => 'Moderate', 'critical' => 'Critical'] as $tierKey => $tierLabel)
                @php
                    $tierRules = $rules->where('tier', $tierKey);
                    $tierBg = ['minor' => 'emerald', 'moderate' => 'amber', 'critical' => 'rose'][$tierKey];
                @endphp

                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-3 h-3 rounded-full bg-{{ $tierBg }}-500"></span>
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">{{ $tierLabel }} Tier</h3>
                        <span class="text-[9px] font-bold text-slate-400 uppercase">{{ $tierRules->count() }} {{ Str::plural('rule', $tierRules->count()) }}</span>
                    </div>

                    @if($tierRules->count() > 0)
                    <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] border border-white shadow-xl shadow-slate-200/50 overflow-hidden">
                        <table class="w-full text-left border-separate border-spacing-0">
                            <thead>
                                <tr class="bg-{{ $tierBg }}-50/30">
                                    <th class="px-6 py-3 border-b border-slate-100 text-[9px] font-black text-slate-400 uppercase tracking-widest">Applies To</th>
                                    <th class="px-6 py-3 border-b border-slate-100 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Threshold</th>
                                    <th class="px-6 py-3 border-b border-slate-100 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Window</th>
                                    <th class="px-6 py-3 border-b border-slate-100 text-[9px] font-black text-slate-400 uppercase tracking-widest">Action</th>
                                    <th class="px-6 py-3 border-b border-slate-100 text-[9px] font-black text-slate-400 uppercase tracking-widest">Legal Ref</th>
                                    <th class="px-6 py-3 border-b border-slate-100 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                                    <th class="px-6 py-3 border-b border-slate-100 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($tierRules as $rule)
                                <tr class="hover:bg-slate-50/50 transition-colors {{ !$rule->is_active ? 'opacity-50' : '' }}">
                                    <td class="px-6 py-4">
                                        <span class="text-xs font-bold text-slate-700">{{ $rule->infractionDefinition?->name ?? 'All ' . $tierLabel . ' infractions' }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1 bg-{{ $tierBg }}-100 text-{{ $tierBg }}-600 rounded-lg text-xs font-black">≥ {{ $rule->occurrence_threshold }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-xs font-semibold text-slate-600">{{ $rule->time_window_days ? $rule->time_window_days . ' days' : 'Per Year' }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 bg-slate-100 text-slate-700 rounded-lg text-[9px] font-black uppercase tracking-widest">{{ $rule->action_label }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-[10px] font-semibold text-slate-500 italic">{{ $rule->legal_reference ?? '—' }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <form action="{{ route('admin.discipline-settings.rules.toggle', $rule) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 rounded-lg text-[9px] font-black uppercase border transition-all {{ $rule->is_active ? 'bg-emerald-50 text-emerald-500 border-emerald-200' : 'bg-slate-50 text-slate-400 border-slate-200' }}">
                                                {{ $rule->is_active ? 'Active' : 'Off' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button @click="openRuleEdit({{ $rule->toJson() }})" class="px-4 py-2 bg-white border border-slate-200 text-slate-600 font-black text-[10px] uppercase tracking-widest rounded-xl hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm">
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="bg-white/40 backdrop-blur-xl rounded-[2rem] border border-dashed border-slate-200 p-8 text-center">
                        <p class="text-sm font-semibold text-slate-400">No escalation rules configured for this tier.</p>
                    </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- ══════ INFRACTION MODAL ══════ -->
        <div x-show="showInfractionModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4" @keydown.escape.window="showInfractionModal = false">
            <div @click.outside="showInfractionModal = false" class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-xl p-10 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900" x-text="editMode ? 'Edit Infraction Type' : 'New Infraction Type'"></h3>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1 italic">Define a behavioral infraction category</p>
                    </div>
                </div>

                <form :action="editMode ? '/admin/discipline-settings/infractions/' + infractionForm.id : '{{ route('admin.discipline-settings.infractions.store') }}'" method="POST" class="space-y-6">
                    @csrf
                    <template x-if="editMode"><input type="hidden" name="_method" value="PUT"></template>

                    <div class="space-y-2">
                        <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Infraction Name</label>
                        <input type="text" name="name" x-model="infractionForm.name" class="premium-input w-full" required placeholder="e.g. Academic Misconduct">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Severity Tier</label>
                            <select name="tier" x-model="infractionForm.tier" class="premium-select w-full" required>
                                <option value="minor">Minor</option>
                                <option value="moderate">Moderate</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Display Order</label>
                            <input type="number" name="display_order" x-model="infractionForm.display_order" class="premium-input w-full" min="0">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Description</label>
                        <textarea name="description" x-model="infractionForm.description" rows="2" class="premium-input w-full" placeholder="What constitutes this infraction..."></textarea>
                    </div>

                    <div class="space-y-2">
                        <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Default Penalty</label>
                        <input type="text" name="default_penalty" x-model="infractionForm.default_penalty" class="premium-input w-full" placeholder="e.g. Verbal Warning">
                    </div>

                    <label class="flex items-center gap-3 p-4 rounded-2xl bg-rose-50/50 border border-rose-100/50 cursor-pointer">
                        <input type="hidden" name="requires_parent_notification" value="0">
                        <input type="checkbox" name="requires_parent_notification" value="1" x-model="infractionForm.requires_parent_notification" class="w-5 h-5 rounded border-rose-300 text-rose-600 focus:ring-rose-500/20">
                        <div>
                            <span class="block text-[10px] font-black text-rose-900 uppercase tracking-widest">Mandatory Parent Notification</span>
                            <span class="block text-[9px] font-semibold text-rose-600/70 mt-0.5">Automatically flag parent alert when this infraction is reported</span>
                        </div>
                    </label>

                    <div class="flex items-center gap-4 pt-4">
                        <button type="button" @click="showInfractionModal = false" class="flex-1 px-6 py-4 bg-slate-100 text-slate-500 font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-slate-200 transition-all">Cancel</button>
                        <button type="submit" class="flex-1 px-6 py-4 bg-indigo-600 text-white font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-indigo-700 shadow-xl shadow-indigo-200 transition-all" x-text="editMode ? 'Update Definition' : 'Create Definition'"></button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ══════ ESCALATION RULE MODAL ══════ -->
        <div x-show="showRuleModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4" @keydown.escape.window="showRuleModal = false">
            <div @click.outside="showRuleModal = false" class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-xl p-10 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900" x-text="editMode ? 'Edit Escalation Rule' : 'New Escalation Rule'"></h3>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1 italic">Define automatic escalation thresholds</p>
                    </div>
                </div>

                <form :action="editMode ? '/admin/discipline-settings/rules/' + ruleForm.id : '{{ route('admin.discipline-settings.rules.store') }}'" method="POST" class="space-y-6">
                    @csrf
                    <template x-if="editMode"><input type="hidden" name="_method" value="PUT"></template>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Target Tier</label>
                            <select name="tier" x-model="ruleForm.tier" class="premium-select w-full" required>
                                <option value="minor">Minor</option>
                                <option value="moderate">Moderate</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Specific Infraction</label>
                            <select name="infraction_definition_id" x-model="ruleForm.infraction_definition_id" class="premium-select w-full">
                                <option value="">All infractions of this tier</option>
                                @foreach($definitions->where('is_active', true) as $def)
                                    <option value="{{ $def->id }}">{{ $def->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Occurrence Threshold</label>
                            <input type="number" name="occurrence_threshold" x-model="ruleForm.occurrence_threshold" class="premium-input w-full" min="1" required>
                            <p class="text-[9px] font-bold text-slate-400 uppercase px-1">Triggers after N occurrences</p>
                        </div>
                        <div class="space-y-2">
                            <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Time Window (Days)</label>
                            <input type="number" name="time_window_days" x-model="ruleForm.time_window_days" class="premium-input w-full" min="1" placeholder="Empty = per year">
                            <p class="text-[9px] font-bold text-slate-400 uppercase px-1">Leave empty for per academic year</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Escalation Action</label>
                        <select name="escalation_action" x-model="ruleForm.escalation_action" class="premium-select w-full" required>
                            @foreach(\App\Models\EscalationRule::escalationActions() as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Description</label>
                        <textarea name="escalation_description" x-model="ruleForm.escalation_description" rows="2" class="premium-input w-full" placeholder="What happens when this rule triggers..."></textarea>
                    </div>

                    <div class="space-y-2">
                        <label class="px-1 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Legal Reference</label>
                        <input type="text" name="legal_reference" x-model="ruleForm.legal_reference" class="premium-input w-full" placeholder="e.g. Directive 150/2016, Art. 12(3)">
                    </div>

                    <label class="flex items-center gap-3 p-4 rounded-2xl bg-amber-50/50 border border-amber-100/50 cursor-pointer">
                        <input type="hidden" name="auto_notify_parent" value="0">
                        <input type="checkbox" name="auto_notify_parent" value="1" x-model="ruleForm.auto_notify_parent" class="w-5 h-5 rounded border-amber-300 text-amber-600 focus:ring-amber-500/20">
                        <div>
                            <span class="block text-[10px] font-black text-amber-900 uppercase tracking-widest">Auto-Notify Parent</span>
                            <span class="block text-[9px] font-semibold text-amber-600/70 mt-0.5">Automatically set parent notification when escalation triggers</span>
                        </div>
                    </label>

                    <div class="flex items-center gap-4 pt-4">
                        <button type="button" @click="showRuleModal = false" class="flex-1 px-6 py-4 bg-slate-100 text-slate-500 font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-slate-200 transition-all">Cancel</button>
                        <button type="submit" class="flex-1 px-6 py-4 bg-amber-600 text-white font-black text-[10px] uppercase tracking-widest rounded-2xl hover:bg-amber-700 shadow-xl shadow-amber-200 transition-all" x-text="editMode ? 'Update Rule' : 'Create Rule'"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
