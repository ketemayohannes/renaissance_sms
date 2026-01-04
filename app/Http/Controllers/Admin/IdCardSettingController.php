<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IdCardSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IdCardSettingController extends Controller
{
    public function index()
    {
        $settings = IdCardSetting::first() ?? IdCardSetting::create([
            'school_name' => 'Renaissance School',
            'primary_color' => '#1e3a8a',
            'secondary_color' => '#3b82f6',
            'text_color' => '#ffffff',
            'front_fields' => ['student_id', 'full_name', 'grade', 'gender', 'date_of_birth'],
            'back_fields' => ['guardian_name', 'guardian_phone', 'blood_group'],
            'back_content' => "1. This card is the property of Renaissance School.\n2. If found, please return to the school office.\n3. Student must carry this card at all times within school premises.",
            'show_barcode' => true,
            'show_qr_code' => false,
            'photo_shape' => 'rounded',
        ]);

        return view('admin.id-card-settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = IdCardSetting::first();
        
        $validated = $request->validate([
            'school_name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'primary_color' => 'required|string|max:7',
            'secondary_color' => 'required|string|max:7',
            'text_color' => 'required|string|max:7',
            'front_fields' => 'nullable|array',
            'back_fields' => 'nullable|array',
            'back_content' => 'nullable|string',
            'show_barcode' => 'boolean',
            'show_qr_code' => 'boolean',
            'photo_shape' => 'required|in:rounded,circle,square',
        ]);

        // Handle file upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $validated['logo_path'] = $request->file('logo')->store('id-cards', 'public');
        }

        // Handle checkboxes not sent if unchecked
        $validated['show_barcode'] = $request->has('show_barcode');
        $validated['show_qr_code'] = $request->has('show_qr_code');
        $validated['front_fields'] = $request->input('front_fields', []);
        $validated['back_fields'] = $request->input('back_fields', []);

        $settings->update($validated);

        return back()->with('success', 'ID card settings updated successfully.');
    }
}
