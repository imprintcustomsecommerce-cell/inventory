@php
    $project = $project ?? null;
    $productTypes = ['Jersey', 'Polo Shirt', 'Round Neck Shirt', 'V-Neck Shirt', 'Jacket / Hoodie'];
@endphp

<div class="space-y-5 p-6">
    <div>
        <label class="label">Project name</label>
        <input type="text" name="project_name" value="{{ old('project_name', $project->project_name ?? '') }}" required placeholder="e.g. ABC Riders Jersey Order" class="input @error('project_name') input-error @enderror">
        @error('project_name') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <label class="label">Customer <span class="font-normal text-zinc-400">(optional)</span></label>
            <input type="text" name="customer_name" value="{{ old('customer_name', $project->customer_name ?? '') }}" placeholder="Customer / team name" class="input">
        </div>
        <div>
            <label class="label">Product type</label>
            <select name="product_type" class="select">
                <option value="">Select product</option>
                @foreach($productTypes as $type)
                    <option value="{{ $type }}" {{ old('product_type', $project->product_type ?? '') == $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <label class="label">Quantity</label>
            <input type="number" name="quantity" value="{{ old('quantity', $project->quantity ?? 1) }}" min="1" required class="input @error('quantity') input-error @enderror">
            @error('quantity') <p class="form-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="label">Quoted price <span class="font-normal text-zinc-400">(₱, optional)</span></label>
            <input type="number" name="quoted_price" value="{{ old('quoted_price', $project->quoted_price ?? '') }}" min="0" step="0.01" class="input @error('quoted_price') input-error @enderror">
            @error('quoted_price') <p class="form-error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <label class="label">Status</label>
            <select name="status" required class="select">
                @foreach(\App\Models\Project::STATUSES as $s)
                    <option value="{{ $s }}" {{ old('status', $project->status ?? 'Pending') == $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label">Due date <span class="font-normal text-zinc-400">(optional)</span></label>
            <input type="date" name="due_date" value="{{ old('due_date', isset($project->due_date) ? $project->due_date->format('Y-m-d') : '') }}" class="input">
        </div>
    </div>

    <div>
        <label class="label">Remarks <span class="font-normal text-zinc-400">(optional)</span></label>
        <textarea name="remarks" rows="3" placeholder="Design notes, production reminders…" class="textarea">{{ old('remarks', $project->remarks ?? '') }}</textarea>
    </div>
</div>
