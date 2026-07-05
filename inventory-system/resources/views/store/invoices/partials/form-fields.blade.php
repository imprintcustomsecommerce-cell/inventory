@php
    $invoice = $invoice ?? null;
    $showStatus = $showStatus ?? false;
    $selectedCustomer = old('customer_id', $invoice->customer_id ?? request('customer_id'));
@endphp

<div class="space-y-5 p-6">
    <div>
        <label class="label">Title</label>
        <input type="text" name="title" value="{{ old('title', $invoice->title ?? '') }}" required placeholder="e.g. Jersey Order — ABC Riders" class="input @error('title') input-error @enderror">
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
            <label class="label">Discount <span class="font-normal text-zinc-400">(₱, optional)</span></label>
            <input type="number" name="discount" value="{{ old('discount', $invoice->discount ?? '0') }}" min="0" step="0.01" class="input @error('discount') input-error @enderror">
            @error('discount') <p class="form-error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <label class="label">Issue date</label>
            <input type="date" name="issue_date" value="{{ old('issue_date', isset($invoice->issue_date) ? $invoice->issue_date->format('Y-m-d') : now()->format('Y-m-d')) }}" required class="input @error('issue_date') input-error @enderror">
            @error('issue_date') <p class="form-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="label">Due date <span class="font-normal text-zinc-400">(optional)</span></label>
            <input type="date" name="due_date" value="{{ old('due_date', isset($invoice->due_date) ? $invoice->due_date->format('Y-m-d') : '') }}" class="input">
        </div>
    </div>

    @if($showStatus)
        <div class="sm:w-1/2">
            <label class="label">Status</label>
            <select name="status" required class="select">
                @foreach(\App\Models\Invoice::STATUSES as $s)
                    <option value="{{ $s }}" {{ old('status', $invoice->status ?? 'Unpaid') == $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-zinc-400">Paid / Partial / Unpaid is set automatically from payments. Use this only to cancel or re-open.</p>
        </div>
    @endif

    <div>
        <label class="label">Notes <span class="font-normal text-zinc-400">(optional)</span></label>
        <textarea name="notes" rows="2" placeholder="Anything the customer should see…" class="textarea">{{ old('notes', $invoice->notes ?? '') }}</textarea>
    </div>

    <div>
        <label class="label">Terms <span class="font-normal text-zinc-400">(optional)</span></label>
        <textarea name="terms" rows="2" placeholder="Payment terms, bank details…" class="textarea">{{ old('terms', $invoice->terms ?? '') }}</textarea>
    </div>
</div>
