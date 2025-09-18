<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SettingsRequest;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:admin');
    }

    public function index(Request $request)
    {
        $settings = Setting::all();
        return response()->json($settings);
    }

    public function store(SettingsRequest $request)
    {
        $inputs = $request->input('input', []);
        $values = $request->input('value', []);
        $labels = $request->input('label', []);

        foreach ($inputs as $key => $input) {
            $value = array_key_exists($key, $values) ? $values[$key] : null;
            $label = array_key_exists($key, $labels) ? $labels[$key] : null;

            Setting::updateOrCreate(
                ['input' => $input],
                ['value' => $value, 'label' => $label]
            );
        }

        return response()->json(['status' => 'ok']);
    }

    public function update(SettingsRequest $request, Setting $setting)
    {
        $values = $request->input('value', []);
        if (is_array($values)) {
            $first = reset($values);
            $setting->update(['value' => $first]);
        } else {
            $setting->update(['value' => $values]);
        }

        return response()->json($setting);
    }
}
