<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

/**
 * Settings Controller
 * 
 * Manages platform-wide configuration settings.
 */
class SettingsController extends Controller
{
    /**
     * Display settings page with all setting groups
     */
    public function index()
    {
        $settings = AdminSetting::all()->groupBy('group');

        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update settings (bulk update)
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*.key' => ['required', 'string'],
            'settings.*.value' => ['required'],
        ]);

        foreach ($validated['settings'] as $settingData) {
            $setting = AdminSetting::where('key', $settingData['key'])->first();
            
            if ($setting) {
                $setting->setValue($settingData['value']);
            }
        }

        // Clear settings cache
        Cache::flush();

        return redirect()->back()
            ->with('success', 'Pengaturan berhasil diperbarui.');
    }

    /**
     * Reset a specific setting to default
     */
    public function reset(string $key)
    {
        $setting = AdminSetting::where('key', $key)->first();
        
        if (!$setting) {
            return redirect()->back()
                ->with('error', 'Pengaturan tidak ditemukan.');
        }

        // Reset to default based on key
        $defaults = $this->getDefaultSettings();
        
        if (isset($defaults[$key])) {
            $setting->setValue($defaults[$key]);
            Cache::forget("admin_setting:{$key}");
            
            return redirect()->back()
                ->with('success', 'Pengaturan berhasil direset ke default.');
        }

        return redirect()->back()
            ->with('error', 'Default value tidak tersedia.');
    }

    /**
     * Get default settings values
     * 
     * @return array
     */
    private function getDefaultSettings(): array
    {
        return [
            'billing.error_margin' => 10,
            'billing.profit_margin' => 5,
            'billing.base_credits' => 50,
            'billing.extra_page_multiplier' => 1.5,
            'billing.extra_component_multiplier' => 1.2,
            'generation.max_concurrent' => 3,
            'generation.max_pages' => 20,
            'generation.max_components' => 50,
            'generation.timeout' => 300,
        ];
    }
}
