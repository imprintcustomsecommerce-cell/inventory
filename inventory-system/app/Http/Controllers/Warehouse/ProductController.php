<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;

use App\Http\Controllers\Concerns\ExportsCsv;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    use ExportsCsv;

    private function warehousesForUser($user)
    {
        // Only stockrooms can hold newly created products; stores receive transfers.
        return $user->isAdmin() ? Warehouse::stockrooms()->orderBy('name')->get() : collect();
    }

    private function guard(Product $product): void
    {
        $user = auth()->user();
        if ($user->isAdmin() || !$user->warehouse_id) {
            return;
        }

        // Allow if the product lives in the user's warehouse OR holds stock
        // (a size variant) there — matching what Product::scopeVisibleTo shows.
        $visible = $product->warehouse_id === $user->warehouse_id
            || $product->variants()->where('warehouse_id', $user->warehouse_id)->exists();

        if (!$visible) {
            abort(403, 'This product belongs to another warehouse.');
        }
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $query = Product::query()->visibleTo($user)->with(['warehouse', 'variants.warehouse']);

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(fn ($q) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('sku', 'like', "%{$s}%")
                ->orWhere('category', 'like', "%{$s}%")
                ->orWhere('brand', 'like', "%{$s}%"));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by the warehouse that holds the product (any of its variants).
        if ($user->isAdmin() && $request->filled('warehouse')) {
            $query->whereHas('variants', fn ($q) => $q->where('warehouse_id', $request->input('warehouse')));
        }

        // Stock-level filter across a product's variants.
        if ($request->input('stock') === 'low') {
            $query->whereHas('variants', fn ($q) => $q->whereColumn('current_stock', '<=', 'minimum_stock')->where('current_stock', '>', 0));
        } elseif ($request->input('stock') === 'out') {
            $query->whereDoesntHave('variants', fn ($q) => $q->where('current_stock', '>', 0));
        }

        // Optional: products still missing a photo.
        if ($request->boolean('no_image')) {
            $query->whereNull('image_path');
        }

        $missingCount = $user->isAdmin()
            ? Product::query()->visibleTo($user)->whereNull('image_path')->count()
            : 0;

        $products = $query->orderBy('name')->paginate(30)->withQueryString();
        $categories = Product::query()->visibleTo($user)->whereNotNull('category')->distinct()->pluck('category')->sort();
        $warehouses = $user->isAdmin() ? Warehouse::orderBy('name')->get() : collect();

        return role_view('warehouse.products.index', compact('products', 'categories', 'warehouses', 'missingCount'));
    }

    /** Bulk activate / deactivate / trash selected products (admin only). */
    public function bulk(Request $request)
    {
        $data = $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        $products = Product::query()->visibleTo($request->user())->whereIn('id', $data['ids'])->get();

        foreach ($products as $product) {
            match ($data['action']) {
                'activate' => $product->update(['status' => 'active']),
                'deactivate' => $product->update(['status' => 'inactive']),
                'delete' => tap($product, function ($p) {
                    $p->variants()->delete();
                    $p->delete();
                }),
            };
        }

        $verb = ['activate' => 'activated', 'deactivate' => 'deactivated', 'delete' => 'moved to trash'][$data['action']];

        return back()->with('success', $products->count() . ' product(s) ' . $verb . '.');
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->canCreateItems(), 403, 'This warehouse can only receive stock via transfer.');

        return role_view('warehouse.products.create', ['warehouses' => $this->warehousesForUser($request->user())]);
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
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
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
                'status' => $data['status'] ?? 'active',
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
        $product->load(['variants' => fn ($q) => $q->orderBy('id'), 'variants.warehouse', 'warehouse']);
        $allSizes = InventoryItem::SIZES;

        return role_view('warehouse.products.show', compact('product', 'allSizes'));
    }

    public function edit(Product $product)
    {
        $this->guard($product);

        return role_view('warehouse.products.edit', [
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
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
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
            'color' => ['nullable', 'string', 'max:50'],
            'sku' => ['nullable', 'string', 'max:255'],
            'current_stock' => 'nullable|numeric|min:0',
            'minimum_stock' => 'nullable|numeric|min:0',
        ]);

        // A variation is unique by its size + color combination.
        $exists = $product->variants()
            ->where('size', $data['size'])
            ->where('color', $data['color'] ?? null)
            ->exists();
        if ($exists) {
            $label = trim($data['size'] . ' ' . ($data['color'] ?? ''));
            return back()->with('error', "Variation {$label} already exists for this product.");
        }

        $product->variants()->create([
            'warehouse_id' => $product->warehouse_id,
            'name' => $product->name,
            'category' => $product->category,
            'size' => $data['size'],
            'color' => $data['color'] ?? null,
            'sku' => $data['sku'] ?? null,
            'unit' => 'pcs',
            'current_stock' => $data['current_stock'] ?? 0,
            'minimum_stock' => $data['minimum_stock'] ?? 0,
            'unit_cost' => $product->cost_price,
            'status' => 'active',
        ]);

        return back()->with('success', 'Variation added.');
    }

    public function export(Request $request)
    {
        $user = $request->user();
        $query = Product::query()->visibleTo($user)->with('variants.warehouse');

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
            $query->whereHas('variants', fn ($q) => $q->where('warehouse_id', $request->input('warehouse')));
        }

        $rows = [];
        foreach ($query->orderBy('name')->get() as $p) {
            $status = $p->isActive() ? 'Active' : 'Inactive';
            if ($p->variants->isEmpty()) {
                $rows[] = [$p->name, $p->sku, $status, $p->category, $p->brand, '', '', '', '', 0, number_format((float) $p->retail_price, 2, '.', ''), number_format((float) $p->cost_price, 2, '.', '')];
                continue;
            }
            foreach ($p->variants as $v) {
                $rows[] = [
                    $p->name, $p->sku, $status, $p->category, $p->brand,
                    $v->size, $v->color, $v->sku, $v->warehouse?->name, $v->current_stock,
                    number_format((float) $p->retail_price, 2, '.', ''),
                    number_format((float) $p->cost_price, 2, '.', ''),
                ];
            }
        }

        return $this->streamXlsx(
            'products-' . now()->format('Y-m-d') . '.xlsx',
            ['Product', 'SKU', 'Status', 'Category', 'Brand', 'Size', 'Color', 'Variation SKU', 'Warehouse', 'Stock', 'Retail Price', 'Cost Price'],
            $rows
        );
    }

    public function importForm(Request $request)
    {
        $user = $request->user();

        // Admins choose any warehouse (incl. a store); scoped staff import into
        // their own warehouse (a store may import its own products directly).
        $warehouses = $user->isAdmin()
            ? Warehouse::orderBy('name')->get()
            : Warehouse::whereKey($user->warehouse_id)->get();

        return role_view('warehouse.products.import', ['warehouses' => $warehouses]);
    }

    /** Import products from the Imprint products Excel (one row per product). */
    public function import(Request $request)
    {
        $data = $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        // Scoped staff can only import into their own warehouse, whatever was posted.
        $user = $request->user();
        if (!$user->isAdmin() && $user->warehouse_id) {
            $data['warehouse_id'] = $user->warehouse_id;
        }

        // Admins may import into any warehouse; scoped staff only into their own.
        if ($user->isAdmin()) {
            $targetOk = Warehouse::whereKey($data['warehouse_id'])->exists();
        } else {
            $targetOk = Warehouse::whereKey($data['warehouse_id'])
                ->where(fn ($q) => $q->where('can_create_items', true)->orWhere('id', $user->warehouse_id))
                ->exists();
        }

        if (!$targetOk) {
            return back()->with('error', 'Import is only allowed into your own warehouse or a stockroom.');
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

        // Alternate layout: the Imprint "SUMMARY" export — one row per size
        // (e.g. "NEKROS SHORT - XS"), image as a URL, stock in REMAINING.
        // Detect it by its ITEM NAME column and hand off to a dedicated parser.
        $upper = array_flip(array_map(fn ($h) => strtoupper(trim((string) $h)), $header));
        if (isset($upper['ITEM NAME'])) {
            return $this->importSummary($rows, $upper, $warehouseId);
        }

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
     * Import the Imprint "SUMMARY" spreadsheet.
     *
     * Layout: one row per size. Columns used:
     *   IMAGE (URL, only on a product's first size row), CATEGORY,
     *   ITEM NAME ("PRODUCT NAME - SIZE"), REMAINING (stock), SRP (retail price).
     *
     * @param  array<int, array>  $rows
     * @param  array<string, int>  $upper  UPPERCASED header => column index
     */
    private function importSummary(array $rows, array $upper, int $warehouseId)
    {
        // A large sheet plus image downloads can run past the default limit.
        @set_time_limit(0);

        // Case-insensitive cell reader.
        $get = function (array $row, string $name) use ($upper) {
            $name = strtoupper($name);
            return isset($upper[$name]) && isset($row[$upper[$name]]) ? trim((string) $row[$upper[$name]]) : null;
        };
        // "₱1,250.00" -> 1250.00 ; "10,264" -> 10264
        $num = fn (?string $v) => (float) preg_replace('/[^0-9.\-]/', '', (string) $v);

        $created = 0;
        $updated = 0;
        $variants = 0;
        $skipped = 0;
        $images = 0;

        // Remember an image per product name so we don't download it twice.
        $imageForProduct = [];

        DB::transaction(function () use ($rows, $get, $num, $warehouseId, &$created, &$updated, &$variants, &$skipped, &$images, &$imageForProduct) {
            foreach ($rows as $row) {
                $itemName = $get($row, 'ITEM NAME');
                if (!$itemName) {
                    $skipped++;
                    continue; // blank / totals row
                }

                // Split "PRODUCT NAME - SIZE" on the LAST " - ".
                $pos = strripos($itemName, ' - ');
                if ($pos !== false) {
                    $productName = trim(substr($itemName, 0, $pos));
                    $size = trim(substr($itemName, $pos + 3));
                } else {
                    $productName = $itemName;
                    $size = 'One Size';
                }
                if ($productName === '') {
                    $skipped++;
                    continue;
                }

                $category = $get($row, 'CATEGORY') ?: null;
                $price = $num($get($row, 'SRP'));
                $stock = $num($get($row, 'REMAINING'));

                $product = Product::firstOrNew(['warehouse_id' => $warehouseId, 'name' => $productName]);
                $isNew = !$product->exists;
                $product->category = $category;
                $product->retail_price = $price;

                // Download the product image once (URL is on the first size row).
                $imageUrl = $get($row, 'IMAGE');
                if ($imageUrl && !$product->image_path && filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                    if (!array_key_exists($productName, $imageForProduct)) {
                        $imageForProduct[$productName] = $this->downloadImage($imageUrl);
                    }
                    if ($imageForProduct[$productName]) {
                        $product->image_path = $imageForProduct[$productName];
                        $images++;
                    }
                }

                $product->save();
                $isNew ? $created++ : $updated++;

                // One inventory variant per size; stock comes from REMAINING.
                $variant = $product->variants()->where('size', $size)->first();
                if ($variant) {
                    $variant->update([
                        'current_stock' => $stock,
                        'unit_cost' => $product->cost_price ?? 0,
                        'category' => $category,
                    ]);
                } else {
                    $product->variants()->create([
                        'warehouse_id' => $warehouseId,
                        'name' => $productName,
                        'category' => $category,
                        'size' => $size,
                        'unit' => 'pcs',
                        'current_stock' => $stock,
                        'minimum_stock' => 0,
                        'unit_cost' => $product->cost_price ?? 0,
                        'status' => 'active',
                    ]);
                    $variants++;
                }
            }
        });

        $msg = "Import complete: {$created} products added, {$updated} updated, {$variants} sizes, {$images} images.";
        if ($skipped) {
            $msg .= " ({$skipped} blank rows skipped.)";
        }

        return redirect()->route('products.index')->with('success', $msg);
    }

    /**
     * Download a product image from a URL and store it locally (offline-safe
     * afterwards). Returns the stored path, or null if it could not be fetched.
     */
    private function downloadImage(string $url): ?string
    {
        try {
            $response = Http::timeout(15)->get($url);
            if (!$response->successful() || $response->body() === '') {
                return null;
            }

            $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION)) ?: 'jpg';
            $ext = preg_replace('/[^a-z0-9]/', '', $ext) ?: 'jpg';

            $stored = 'product-images/' . Str::random(40) . '.' . $ext;
            Storage::disk('public')->put($stored, $response->body());

            return $stored;
        } catch (\Throwable $e) {
            return null; // no internet / bad URL — import continues without the image
        }
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
