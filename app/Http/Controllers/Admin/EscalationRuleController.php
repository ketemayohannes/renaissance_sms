<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EscalationRule;
use App\Models\InfractionDefinition;
use Illuminate\Http\Request;

class EscalationRuleController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'infraction_definition_id' => 'nullable|exists:infraction_definitions,id',
            'tier'                     => 'required|in:minor,moderate,critical',
            'occurrence_threshold'     => 'required|integer|min:1',
            'time_window_days'         => 'nullable|integer|min:1',
            'escalation_action'        => 'required|string|in:' . implode(',', array_keys(EscalationRule::escalationActions())),
            'escalation_description'   => 'nullable|string|max:1000',
            'auto_notify_parent'       => 'boolean',
            'legal_reference'          => 'nullable|string|max:255',
        ]);

        EscalationRule::create([
            'infraction_definition_id' => $request->infraction_definition_id,
            'tier'                     => $request->tier,
            'occurrence_threshold'     => $request->occurrence_threshold,
            'time_window_days'         => $request->time_window_days,
            'escalation_action'        => $request->escalation_action,
            'escalation_description'   => $request->escalation_description,
            'auto_notify_parent'       => $request->boolean('auto_notify_parent', true),
            'legal_reference'          => $request->legal_reference,
            'created_by'               => auth()->id(),
        ]);

        return redirect()->route('admin.discipline-settings.index')
            ->with('success', 'Escalation rule created successfully.');
    }

    public function update(Request $request, EscalationRule $rule)
    {
        $request->validate([
            'infraction_definition_id' => 'nullable|exists:infraction_definitions,id',
            'tier'                     => 'required|in:minor,moderate,critical',
            'occurrence_threshold'     => 'required|integer|min:1',
            'time_window_days'         => 'nullable|integer|min:1',
            'escalation_action'        => 'required|string|in:' . implode(',', array_keys(EscalationRule::escalationActions())),
            'escalation_description'   => 'nullable|string|max:1000',
            'auto_notify_parent'       => 'boolean',
            'legal_reference'          => 'nullable|string|max:255',
        ]);

        $rule->update([
            'infraction_definition_id' => $request->infraction_definition_id,
            'tier'                     => $request->tier,
            'occurrence_threshold'     => $request->occurrence_threshold,
            'time_window_days'         => $request->time_window_days,
            'escalation_action'        => $request->escalation_action,
            'escalation_description'   => $request->escalation_description,
            'auto_notify_parent'       => $request->boolean('auto_notify_parent', true),
            'legal_reference'          => $request->legal_reference,
        ]);

        return redirect()->route('admin.discipline-settings.index')
            ->with('success', 'Escalation rule updated successfully.');
    }

    public function toggleActive(EscalationRule $rule)
    {
        $rule->update(['is_active' => !$rule->is_active]);

        $status = $rule->is_active ? 'enabled' : 'disabled';

        return redirect()->route('admin.discipline-settings.index')
            ->with('success', "Escalation rule {$status} successfully.");
    }
}
