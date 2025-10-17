<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BellDevice;
use Illuminate\Http\Request;

class BellDeviceController extends Controller
{
    public function compatible(Request $request)
    {
        $tier = $request->tier;
        $compatibleIds = BellDevice::whereHas('currentPricing', function ($q) use ($tier) {
            $q->where('tier', $tier);
        })->orWhereHas('currentDroPricing', function ($q) use ($tier) {
            $q->where('tier', $tier);
        })->pluck('id')->unique()->toArray();

        return response()->json($compatibleIds);
    }
}