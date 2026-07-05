@php $customer = $customer ?? null; @endphp

<div class="space-y-5 p-6">
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <label class="label">Name</label>
            <input type="text" name="name" value="{{ old('name', $customer->name ?? '') }}" required placeholder="Contact / team name" class="input @error('name') input-error @enderror">
            @error('name') <p class="form-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="label">Company <span class="font-normal text-zinc-400">(optional)</span></label>
            <input type="text" name="company" value="{{ old('company', $customer->company ?? '') }}" placeholder="Organization / club" class="input">
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <label class="label">Email <span class="font-normal text-zinc-400">(optional)</span></label>
            <input type="email" name="email" value="{{ old('email', $customer->email ?? '') }}" placeholder="name@example.com" class="input @error('email') input-error @enderror">
            @error('email') <p class="form-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="label">Phone <span class="font-normal text-zinc-400">(optional)</span></label>
            <input type="text" name="phone" value="{{ old('phone', $customer->phone ?? '') }}" placeholder="09xx xxx xxxx" class="input">
        </div>
    </div>

    <div>
        <label class="label">Address <span class="font-normal text-zinc-400">(optional)</span></label>
        <textarea name="address" rows="2" placeholder="Delivery / billing address" class="textarea">{{ old('address', $customer->address ?? '') }}</textarea>
    </div>

    <div>
        <label class="label">Notes <span class="font-normal text-zinc-400">(optional)</span></label>
        <textarea name="notes" rows="3" placeholder="Preferences, sizing, past orders…" class="textarea">{{ old('notes', $customer->notes ?? '') }}</textarea>
    </div>
</div>
