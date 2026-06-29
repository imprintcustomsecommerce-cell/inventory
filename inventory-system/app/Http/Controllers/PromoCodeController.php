<?php

namespace App\Http\Controllers;

use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PromoCodeController extends Controller
{
    public function index()
    {
        $codes = PromoCode::latest()->get();

        return view('promo-codes.index', compact('codes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:40|unique:promo_codes,code',
            'type' => ['required', Rule::in(['percent', 'fixed'])],
            'value' => 'required|numeric|min:0',
            'min_subtotal' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'description' => 'nullable|string|max:255',
        ]);

        $data['code'] = strtoupper($data['code']);
        $data['active'] = true;

        PromoCode::create($data);

        return back()->with('success', "Promo code {$data['code']} created.");
    }

    public function toggle(PromoCode $promoCode)
    {
        $promoCode->update(['active' => !$promoCode->active]);

        return back()->with('success', "{$promoCode->code} " . ($promoCode->active ? 'activated' : 'deactivated') . '.');
    }

    public function destroy(PromoCode $promoCode)
    {
        $promoCode->delete();

        return back()->with('success', 'Promo code deleted.');
    }
}
