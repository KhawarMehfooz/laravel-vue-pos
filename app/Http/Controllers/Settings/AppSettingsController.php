<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AppSettingsController extends Controller
{
    public function index()
    {
        $settings = Auth::user()->settings;
        if (!$settings) {
            $settings = Setting::create([
                'user_id' => Auth::id(),
                'business_name' => 'Your Business name',
                'business_location' => 'Your Business Location',
                'business_contact' => '99999999',
                'business_email' => 'email@example.com',
                'currency_symbol' => 'PKR',
                'stock_source_label' => 'Distributor'
            ]);
        }

        return Inertia::render('settings/appSettings', [
            'settings' => $settings
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string|max:30',
            'business_location' => 'required|string|max:50',
            'business_contact' => 'string|max:15',
            'business_email' => 'string|max:20',
            'business_logo',
            'currency_symbol' => 'required|string|max:5',
            'stock_source_label' => 'required|string|max:20'
        ]);

        try {
            DB::beginTransaction();

            $data = $request->only([
                'business_name',
                'business_location',
                'business_contact',
                'business_email',
                'currency_symbol',
                'stock_source_label'
            ]);

            Log::info('Uploaded logo:', ['file' => $request->file('business_logo')]);
            if ($request->hasFile('business_logo')) {

                $logoPath = $request->file('business_logo')->store('logos', 'public');
                $data['business_logo'] = $logoPath;
            }
            $setting = Setting::getOrCreateSettings(auth()->id(), $data);

            DB::commit();

            return redirect()->back()->with('success', 'Settings saved successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save settings', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving settings.'
            ], 500);
        }
    }
}
