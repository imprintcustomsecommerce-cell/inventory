@php $supplier = $supplier ?? null; @endphp

<div class="space-y-5 p-6">
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <label class="label">Supplier name</label>
            <input type="text" name="name" value="{{ old('name', $supplier->name ?? '') }}" required placeholder="e.g. Manila Textile Supply" class="input @error('name') input-error @enderror">
            @error('name') <p class="form-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="label">Contact person <span class="font-normal text-zinc-400">(optional)</span></label>
            <input type="text" name="contact_person" value="{{ old('contact_person', $supplier->contact_person ?? '') }}" class="input">
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <label class="label">Email <span class="font-normal text-zinc-400">(optional)</span></label>
            <input type="email" name="email" value="{{ old('email', $supplier->email ?? '') }}" placeholder="sales@supplier.ph" class="input @error('email') input-error @enderror">
            @error('email') <p class="form-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="label">Phone <span class="font-normal text-zinc-400">(optional)</span></label>
            <input type="text" name="phone" value="{{ old('phone', $supplier->phone ?? '') }}" placeholder="09xx xxx xxxx" class="input">
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <label class="label">Lead time <span class="font-normal text-zinc-400">(optional)</span></label>
            <input type="text" name="lead_time" value="{{ old('lead_time', $supplier->lead_time ?? '') }}" placeholder="e.g. 3–5 days" class="input">
        </div>
    </div>

    <div>
        <label class="label">Address <span class="font-normal text-zinc-400">(optional)</span></label>
        <textarea name="address" rows="2" class="textarea">{{ old('address', $supplier->address ?? '') }}</textarea>
    </div>

    <div>
        <label class="label">Notes <span class="font-normal text-zinc-400">(optional)</span></label>
        <textarea name="notes" rows="3" placeholder="What they supply, pricing notes, terms…" class="textarea">{{ old('notes', $supplier->notes ?? '') }}</textarea>
    </div>
</div>
