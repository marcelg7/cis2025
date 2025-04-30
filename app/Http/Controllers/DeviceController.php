<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DevicePricing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DeviceController extends Controller {
    public function index(): View {
        $devices = Device::with('pricings')->get();
        return view('devices.index', compact('devices'));
    }

	public function create(): View {
		// Default pricing with keys if no old input exists
		$defaultPricing = [['type' => 'smartpay', 'price' => '', 'term' => 24]];
		return view('devices.create', ['pricings' => old('pricings', $defaultPricing)]);
	}

    public function store(Request $request) {
        $request->validate([
            'manufacturer' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'srp' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:2048',
            'pricings.*.type' => 'required|in:smartpay,byod',
            'pricings.*.price' => 'required|numeric|min:0',
            'pricings.*.term' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) use ($request) {
                    $index = explode('.', $attribute)[1];
                    $type = $request->input("pricings.{$index}.type");
                    if ($type === 'smartpay' && (is_null($value) || $value < 1)) {
                        $fail('The term for SmartPay must be at least 1 month.');
                    }
                    if ($type === 'byod' && !is_null($value) && $value < 0) {
                        $fail('The term for BYOD cannot be negative.');
                    }
                },
            ],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('devices', 'public');
        }

        $device = Device::create([
            'manufacturer' => $request->manufacturer,
            'model' => $request->model,
            'srp' => $request->srp,
            'image' => $imagePath,
        ]);

        foreach ($request->pricings ?? [] as $pricing) {
            DevicePricing::create([
                'device_id' => $device->id,
                'type' => $pricing['type'],
                'price' => $pricing['price'],
                'term' => $pricing['type'] === 'smartpay' ? ($pricing['term'] ?? 24) : ($pricing['term'] ?? null),
            ]);
        }

        return redirect()->route('devices.index')->with('success', 'Device added successfully.');
    }

    public function edit($id): View {
        $device = Device::with('pricings')->findOrFail($id);
        $pricings = old('pricings') ? old('pricings') : $device->pricings->map(function ($pricing) {
            return [
                'type' => $pricing->type,
                'price' => $pricing->price,
                'term' => $pricing->term,
            ];
        })->all();
        return view('devices.edit', compact('device', 'pricings'));
    }

    public function update(Request $request, $id) {
        $device = Device::findOrFail($id);

        $request->validate([
            'manufacturer' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'srp' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:2048',
            'pricings.*.type' => 'required|in:smartpay,byod',
            'pricings.*.price' => 'required|numeric|min:0',
            'pricings.*.term' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) use ($request) {
                    $index = explode('.', $attribute)[1];
                    $type = $request->input("pricings.{$index}.type");
                    if ($type === 'smartpay' && (is_null($value) || $value < 1)) {
                        $fail('The term for SmartPay must be at least 1 month.');
                    }
                    if ($type === 'byod' && !is_null($value) && $value < 0) {
                        $fail('The term for BYOD cannot be negative.');
                    }
                },
            ],
        ]);

        if ($request->hasFile('image')) {
            if ($device->image) {
                Storage::disk('public')->delete($device->image);
            }
            $device->image = $request->file('image')->store('devices', 'public');
        }

        $device->update([
            'manufacturer' => $request->manufacturer,
            'model' => $request->model,
            'srp' => $request->srp,
            'image' => $device->image,
        ]);

        $device->pricings()->delete();
        foreach ($request->pricings ?? [] as $pricing) {
            DevicePricing::create([
                'device_id' => $device->id,
                'type' => $pricing['type'],
                'price' => $pricing['price'],
                'term' => $pricing['type'] === 'smartpay' ? ($pricing['term'] ?? 24) : ($pricing['term'] ?? null),
            ]);
        }

        return redirect()->route('devices.index')->with('success', 'Device updated successfully.');
    }

    public function destroy($id) {
        $device = Device::findOrFail($id);
        if ($device->image) {
            Storage::disk('public')->delete($device->image);
        }
        $device->delete();
        return redirect()->route('devices.index')->with('success', 'Device deleted successfully.');
    }
}