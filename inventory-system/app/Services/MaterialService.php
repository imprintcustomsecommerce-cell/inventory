<?php

namespace App\Services;

use App\Models\Material;
use App\Models\MaterialMovement;
use Illuminate\Support\Facades\DB;

class MaterialService
{
    /**
     * Apply a stock movement to a material and record it.
     *
     * @return MaterialMovement|false  false if a stock-out exceeds available stock
     */
    public function record(Material $material, string $type, float $quantity, ?string $reference = null, ?string $remarks = null): MaterialMovement|false
    {
        return DB::transaction(function () use ($material, $type, $quantity, $reference, $remarks) {
            $material = Material::whereKey($material->id)->lockForUpdate()->first();

            if ($type === 'stock_in') {
                $new = $material->current_stock + $quantity;
            } elseif ($type === 'stock_out') {
                if ($quantity > $material->current_stock) {
                    return false;
                }
                $new = $material->current_stock - $quantity;
            } else { // adjustment: quantity is the actual counted stock
                $previous = $material->current_stock;
                $new = $quantity;
                $remarks = "Adjusted from {$previous} to {$quantity}. " . ($remarks ?? '');
            }

            $movement = MaterialMovement::create([
                'material_id' => $material->id,
                'user_id' => auth()->id(),
                'type' => $type,
                'quantity' => $quantity,
                'reference' => $reference,
                'remarks' => $remarks,
            ]);

            $material->update(['current_stock' => $new]);

            return $movement;
        });
    }
}
