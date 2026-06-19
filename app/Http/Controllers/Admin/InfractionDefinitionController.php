<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InfractionDefinition;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InfractionDefinitionController extends Controller
{
    public function index()
    {
        $definitions = InfractionDefinition::ordered()->get();

        $rules = \App\Models\EscalationRule::with('infractionDefinition')
            ->orderBy('tier')
            ->orderBy('occurrence_threshold')
            ->get();

        return view('admin.disciplinary.settings', compact('definitions', 'rules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                          => 'required|string|max:255',
            'tier'                          => 'required|in:minor,moderate,critical',
            'description'                   => 'nullable|string|max:1000',
            'default_penalty'               => 'nullable|string|max:500',
            'requires_parent_notification'  => 'boolean',
            'display_order'                 => 'nullable|integer|min:0',
        ]);

        InfractionDefinition::create([
            'name'                         => $request->name,
            'code'                         => Str::slug($request->name, '_'),
            'tier'                         => $request->tier,
            'description'                  => $request->description,
            'default_penalty'              => $request->default_penalty,
            'requires_parent_notification' => $request->boolean('requires_parent_notification'),
            'display_order'                => $request->input('display_order', 0),
            'created_by'                   => auth()->id(),
        ]);

        return redirect()->route('admin.discipline-settings.index')
            ->with('success', 'Infraction definition created successfully.');
    }

    public function update(Request $request, InfractionDefinition $definition)
    {
        $request->validate([
            'name'                          => 'required|string|max:255',
            'tier'                          => 'required|in:minor,moderate,critical',
            'description'                   => 'nullable|string|max:1000',
            'default_penalty'               => 'nullable|string|max:500',
            'requires_parent_notification'  => 'boolean',
            'display_order'                 => 'nullable|integer|min:0',
        ]);

        $definition->update([
            'name'                         => $request->name,
            'code'                         => Str::slug($request->name, '_'),
            'tier'                         => $request->tier,
            'description'                  => $request->description,
            'default_penalty'              => $request->default_penalty,
            'requires_parent_notification' => $request->boolean('requires_parent_notification'),
            'display_order'                => $request->input('display_order', 0),
        ]);

        return redirect()->route('admin.discipline-settings.index')
            ->with('success', 'Infraction definition updated successfully.');
    }

    public function toggleActive(InfractionDefinition $definition)
    {
        $definition->update(['is_active' => !$definition->is_active]);

        $status = $definition->is_active ? 'activated' : 'archived';

        return redirect()->route('admin.discipline-settings.index')
            ->with('success', "Infraction definition {$status} successfully.");
    }
}
