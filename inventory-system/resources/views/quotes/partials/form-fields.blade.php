@php
    $quote = $quote ?? null;
    $showStatus = $showStatus ?? false;
    $selectedCustomer = old('customer_id', $quote->customer_id ?? request('customer_id'));
@endphp

<div class="space-y-5 p-6">
    <div>
        <label class="label">Title</label>
        <input type="text" name="title" value="{{ old('title', $quote->title ?? '') }}" required placeholder="e.g. Jersey Order — ABC Riders" class="input @error('title') input-error @enderror">
        @error('title') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <label class="label">Customer <span class="font-normal text-zinc-400">(optional)</span></label>
            <select name="customer_id" class="select">
                <option value="">Walk-in / none</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}" {{ (string) $selectedCustomer === (string) $c->id ? 'selected' : '' }}>{{ $c->displayName() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label">Valid until <span class="font-normal text-zinc-400">(optional)</span></label>
            <input type="date" name="valid_until" value="{{ old('valid_until', isset($quote->valid_until) ? $quote->valid_until->format('Y-m-d') : '') }}" class="input">
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        @if($showStatus)
            <div>
                <label class="label">Status</label>
                <select name="status" required class="select">
                    @foreach(\App\Models\Quote::STATUSES as $s)
                        <option value="{{ $s }}" {{ old('status', $quote->status ?? 'Draft') == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div>
            <label class="label">Discount <span class="font-normal text-zinc-400">(₱, optional)</span></label>
            <input type="number" name="discount" value="{{ old('discount', $quote->discount ?? '0') }}" min="0" step="0.01" class="input @error('discount') input-error @enderror">
            @error('discount') <p class="form-error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label class="label">Notes <span class="font-normal text-zinc-400">(optional)</span></label>
        <textarea name="notes" rows="2" placeholder="Anything the customer should see…" class="textarea">{{ old('notes', $quote->notes ?? '') }}</textarea>
    </div>

    <div>
        <label class="label">Terms <span class="font-normal text-zinc-400">(optional)</span></label>
        <textarea name="terms" rows="2" placeholder="Payment terms, lead time, validity…" class="textarea">{{ old('terms', $quote->terms ?? '') }}</textarea>
    </div>
</div>
