@php
    $purchase = $purchase ?? null;
    $showStatus = $showStatus ?? false;
    $selectedSupplier = old('supplier_id', $purchase->supplier_id ?? request('supplier_id'));
@endphp

<div class="space-y-5 p-6">
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <label class="label">Supplier <span class="font-normal text-zinc-400">(optional)</span></label>
            <select name="supplier_id" class="select">
                <option value="">Unassigned</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" {{ (string) $selectedSupplier === (string) $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label">Deliver to <span class="font-normal text-zinc-400">(stockroom)</span></label>
            <select name="warehouse_id" class="select">
                <option value="">Unassigned</option>
                @foreach($warehouses as $w)
                    <option value="{{ $w->id }}" {{ (string) old('warehouse_id', $purchase->warehouse_id ?? '') === (string) $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <label class="label">Order date</label>
            <input type="date" name="order_date" value="{{ old('order_date', isset($purchase->order_date) ? $purchase->order_date->format('Y-m-d') : now()->format('Y-m-d')) }}" required class="input @error('order_date') input-error @enderror">
            @error('order_date') <p class="form-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="label">Expected delivery <span class="font-normal text-zinc-400">(optional)</span></label>
            <input type="date" name="expected_date" value="{{ old('expected_date', isset($purchase->expected_date) ? $purchase->expected_date->format('Y-m-d') : '') }}" class="input">
        </div>
    </div>

    @if($showStatus)
        <div class="sm:w-1/2">
            <label class="label">Status</label>
            <select name="status" required class="select">
                @foreach(\App\Models\PurchaseOrder::STATUSES as $s)
                    <option value="{{ $s }}" {{ old('status', $purchase->status ?? 'Draft') == $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-zinc-400">Receiving updates status automatically. Use this only to cancel or re-open.</p>
        </div>
    @endif

    <div>
        <label class="label">Notes <span class="font-normal text-zinc-400">(optional)</span></label>
        <textarea name="notes" rows="3" placeholder="Delivery instructions, payment terms…" class="textarea">{{ old('notes', $purchase->notes ?? '') }}</textarea>
    </div>
</div>
