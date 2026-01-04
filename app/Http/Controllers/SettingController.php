<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Display general settings form
     */
    public function general()
    {
        return view('settings.general', [
            'siteName' => Setting::get('site_name', 'My E-commerce'),
            'siteEmail' => Setting::get('site_email', 'admin@example.com'),
            'sitePhone' => Setting::get('site_phone', ''),
            'siteAddress' => Setting::get('site_address', ''),
            'siteLogo' => Setting::get('site_logo', ''),
            'siteFavicon' => Setting::get('site_favicon', ''),
        ]);
    }

    /**
     * Update general settings
     */
    public function update(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_email' => 'required|email|max:255',
            'site_phone' => 'nullable|string|max:50',
            'site_address' => 'nullable|string|max:500',
            'site_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'site_favicon' => 'nullable|image|mimes:ico,png|max:512',
        ]);

        try {
            // Save text settings
            Setting::set('site_name', $validated['site_name']);
            Setting::set('site_email', $validated['site_email']);
            Setting::set('site_phone', $validated['site_phone'] ?? '');
            Setting::set('site_address', $validated['site_address'] ?? '');

            // Handle logo upload
            if ($request->hasFile('site_logo')) {
                // Delete old logo
                $oldLogo = Setting::get('site_logo');
                if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                    Storage::disk('public')->delete($oldLogo);
                }

                // Store new logo
                $logoPath = $request->file('site_logo')->store('settings', 'public');
                Setting::set('site_logo', $logoPath);
            }

            // Handle favicon upload
            if ($request->hasFile('site_favicon')) {
                // Delete old favicon
                $oldFavicon = Setting::get('site_favicon');
                if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                    Storage::disk('public')->delete($oldFavicon);
                }

                // Store new favicon
                $faviconPath = $request->file('site_favicon')->store('settings', 'public');
                Setting::set('site_favicon', $faviconPath);
            }

            return redirect()->route('settings.general')
                ->with('success', 'Settings updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }
}