<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    private function warehousesForUser($user)
    {
        // Only stockrooms can hold newly created products; stores receive transfers.
        return $user->isAdmin() ? Warehouse::stockrooms()->orderBy('name')->get() : collect();
    }

    private function guard(Product $product): void
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $user->warehouse_id && $product->warehouse_id !== $user->warehouse_id) {
            abort(403, 'This product belongs to another warehouse.');
        }
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $query = Product::query()->visibleTo($user)->with(['warehouse', 'variants']);

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(fn ($q) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('category', 'like', "%{$s}%")
                ->orWhere('brand', 'like', "%{$s}%"));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($user->isAdmin() && $request->filled('warehouse')) {
            $query->where('warehouse_id', $request->input('warehouse'));
        }

        // Only show products that have a photo. Admins can flip to the
        // missing-image list to fix them.
        $showMissing = $user->isAdmin() && $request->boolean('no_image');
        $query->{$showMissing ? 'whereNull' : 'whereNotNull'}('image_path');

        $missingCount = $user->isAdmin()
            ? Product::query()->visibleTo($user)->whereNull('image_path')->count()
            : 0;

        $products = $query->orderBy('name')->paginate(24)->withQueryString();
        $categories = Product::query()->visibleTo($user)->whereNotNull('category')->distinct()->pluck('category')->sort();
        $warehouses = $this->warehousesForUser($user);

        return view('products.index', compact('products', 'categories', 'warehouses', 'missingCount', 'showMissing'));
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->canCreateItems(), 403, 'This warehouse can only receive stock via transfer.');

        return view('products.create', ['warehouses' => $this->warehousesForUser($request->user())]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->canCreateItems(), 403, 'This warehouse can only receive stock via transfer.');

        $user = $request->user();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'category' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'material' => 'nullable|string|max:255',
            'retail_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:2000',
            'sku' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:4096',
            'sizes' => 'array',
            'sizes.*' => 'string|max:20',
        ]);

        $warehouseId = $user->isAdmin() ? ($data['warehouse_id'] ?? null) : $user->warehouse_id;
        if (!$warehouseId) {
            return back()->withInput()->with('error', 'Please choose a warehouse.');
        }

        if (!Warehouse::whereKey($warehouseId)->where('can_create_items', true)->exists()) {
            return back()->withInput()->with('error', 'Products can only be added to a stockroom. Transfer stock to the store instead.');
        }

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('product-images', 'public')
            : null;

        DB::transaction(function () use ($data, $warehouseId, $imagePath, $request) {
            $product = Product::create([
                'warehouse_id' => $warehouseId,
                'sku' => $data['sku'] ?? null,
                'name' => $data['name'],
                'category' => $data['category'] ?? null,
                'brand' => $data['brand'] ?? null,
                'material' => $data['material'] ?? null,
                'retail_price' => $data['retail_price'] ?? 0,
                'cost_price' => $data['cost_price'] ?? 0,
                'description' => $data['description'] ?? null,
                'image_path' => $imagePath,
            ]);

            $this->syncVariants($product, $data['sizes'] ?? [], $warehouseId);
        });

        return redirect()->route('products.index')->with('success', 'Product created.');
    }

    public function show(Product $product)
    {
        $this->guard($product);
        $product->load(['variants' => fn ($q) => $q->orderBy('id'), 'warehouse']);
        $allSizes = InventoryItem::SIZES;

        return view('products.show', compact('product', 'allSizes'));
    }

    public function edit(Product $product)
    {
        $this->guard($product);

        return view('products.edit', [
            'product' => $product,
            'warehouses' => $this->warehousesForUser(auth()->user()),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $this->guard($product);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'category' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'material' => 'nullable|string|max:255',
            'retail_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:2000',
            'sku' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:4096',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $request->file('image')->store('product-images', 'public');
        }

        if (!auth()->user()->isAdmin()) {
            unset($data['warehouse_id']);
        }
        unset($data['image']);

        $product->update($data);

        return redirect()->route('products.show', $product)->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $this->guard($product);
        // Soft delete the product and its size variants together.
        $product->variants()->delete();
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product moved to trash.');
    }

    /** Add a single size variant to a product. */
    public function addSize(Request $request, Product $product)
    {
        $this->guard($product);
        abort_unless($request->user()->canCreateItems(), 403, 'This warehouse can only receive stock via transfer.');

        $data = $request->validate([
            'size' => ['required', 'string', 'max:20'],
            'current_stock' => 'nullable|numeric|min:0',
            'minimum_stock' => 'nullable|numeric|min:0',
        ]);

        $exists = $product->variants()->where('size', $data['size'])->exists();
        if ($exists) {
            return back()->with('error', "Size {$data['size']} already exists for this product.");
        }

        $product->variants()->create([
            'warehouse_id' => $product->warehouse_id,
            'name' => $product->name,
            'category' => $product->category,
            'size' => $data['size'],
            'unit' => 'pcs',
            'current_stock' => $data['current_stock'] ?? 0,
            'minimum_stock' => $data['minimum_stock'] ?? 0,
            'unit_cost' => $product->cost_price,
            'status' => 'active',
        ]);

        return back()->with('success', "Size {$data['size']} added.");
    }

    public function importForm()
    {
        return view('products.import', ['warehouses' => Warehouse::stockrooms()->orderBy('name')->get()]);
    }

    /** Import products from the Imprint products Excel (one row per product). */
    public function import(Request $request)
    {
        $data = $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        if (!Warehouse::whereKey($data['warehouse_id'])->where('can_create_items', true)->exists()) {
            return back()->with('error', 'Import is only allowed into a stockroom.');
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($request->file('file')->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray(null, true, false, false);
        } catch (\Throwable $e) {
            return back()->with('error', 'Could not read the file. Upload a valid Excel or CSV.');
        }

        if (empty($rows)) {
            return back()->with('error', 'The file appears to be empty.');
        }

        // Pull any images embedded (anchored) in cells, keyed by spreadsheet row.
        $imagesByRow = $this->extractRowImages($worksheet);

        $header = array_map(fn ($h) => trim((string) $h), array_shift($rows));
        $col = array_flip($header);
        $get = fn (array $row, string $name) => isset($col[$name]) && isset($row[$col[$name]]) ? trim((string) $row[$col[$name]]) : null;

        $warehouseId = (int) $data['warehouse_id'];
        $created = 0;
        $updated = 0;
        $variants = 0;
        $skipped = 0;

        DB::transaction(function () use ($rows, $get, $warehouseId, $imagesByRow, &$created, &$updated, &$variants, &$skipped) {
            foreach ($rows as $i => $row) {
                $name = $get($row, 'Product name');
                if (!$name) {
                    $skipped++;
                    continue;
                }

                // Data started at spreadsheet row 2 (row 1 was the header).
                $rowNumber = $i + 2;
                $sku = $get($row, 'Product ID') ?: null;

                $attributes = [
                    'name' => $name,
                    'category' => $get($row, 'Categories') ?: null,
                    'brand' => $get($row, 'Brand name') ?: null,
                    'material' => $get($row, 'Material') ?: null,
                    'retail_price' => (float) ($get($row, 'Retail price') ?? 0),
                    'cost_price' => (float) ($get($row, 'Imported price') ?? 0),
                    'description' => $get($row, 'Description') ?: null,
                ];
                if (isset($imagesByRow[$rowNumber])) {
                    $attributes['image_path'] = $imagesByRow[$rowNumber];
                }

                // Idempotent: match an existing product by SKU (or name) so a
                // re-import updates details/images instead of duplicating.
                $match = $sku
                    ? ['warehouse_id' => $warehouseId, 'sku' => $sku]
                    : ['warehouse_id' => $warehouseId, 'name' => $name];

                $product = Product::where($match)->first();
                if ($product) {
                    $product->update($attributes);
                    $updated++;
                } else {
                    $product = Product::create(array_merge($match, $attributes));
                    $created++;
                }

                // "Product attributes" looks like "SIZE: 2XS, XS, S, M, ..."
                $attr = preg_replace('/^\s*SIZE\s*:/i', '', $get($row, 'Product attributes') ?? '');
                foreach (array_filter(array_map('trim', explode(',', $attr))) as $size) {
                    // firstOrCreate keeps existing stock on re-import.
                    $created_variant = $product->variants()->firstOrCreate(
                        ['size' => $size],
                        [
                            'warehouse_id' => $warehouseId,
                            'name' => $name,
                            'category' => $product->category,
                            'unit' => 'pcs',
                            'current_stock' => 0,
                            'minimum_stock' => 0,
                            'unit_cost' => $product->cost_price,
                            'status' => 'active',
                        ]
                    );
                    if ($created_variant->wasRecentlyCreated) {
                        $variants++;
                    }
                }
            }
        });

        return redirect()->route('products.index')
            ->with('success', "Import complete: {$created} added, {$updated} updated, {$variants} new sizes" . ($skipped ? ", {$skipped} skipped." : '.'));
    }

    /**
     * Save images embedded in a worksheet to storage, keyed by their row.
     *
     * @return array<int, string>
     */
    private function extractRowImages(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $worksheet): array
    {
        $map = [];

        foreach ($worksheet->getDrawingCollection() as $drawing) {
            try {
                $row = (int) preg_replace('/[^0-9]/', '', $drawing->getCoordinates());
                if (!$row) {
                    continue;
                }

                if ($drawing instanceof \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing) {
                    ob_start();
                    call_user_func($drawing->getRenderingFunction(), $drawing->getImageResource());
                    $contents = ob_get_clean();
                    $ext = 'png';
                } else {
                    $path = $drawing->getPath();
                    $contents = $path ? @file_get_contents($path) : null;
                    $ext = $drawing->getExtension() ?: 'png';
                }

                if (!$contents) {
                    continue;
                }

                $stored = 'product-images/' . \Illuminate\Support\Str::random(40) . '.' . $ext;
                Storage::disk('public')->put($stored, $contents);
                $map[$row] = $stored;
            } catch (\Throwable $e) {
                // Skip unreadable images.
            }
        }

        return $map;
    }

    /** Create inventory_item variants for each chosen size. */
    private function syncVariants(Product $product, array $sizes, int $warehouseId): void
    {
        foreach ($sizes as $size) {
            $product->variants()->create([
                'warehouse_id' => $warehouseId,
                'name' => $product->name,
                'category' => $product->category,
                'size' => $size,
                'unit' => 'pcs',
                'current_stock' => 0,
                'minimum_stock' => 0,
                'unit_cost' => $product->cost_price,
                'status' => 'active',
            ]);
        }
    }
}
