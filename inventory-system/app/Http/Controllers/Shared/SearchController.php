<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\OnlineOrder;
use App\Models\Product;
use App\Models\Project;
use App\Models\Quote;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    private const LIMIT = 8;

    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        $groups = [];

        if (strlen($q) >= 2) {
            $like = "%{$q}%";

            $groups = [
                'Projects' => Project::query()
                    ->where(fn ($w) => $w->where('project_name', 'like', $like)
                        ->orWhere('customer_name', 'like', $like)
                        ->orWhere('product_type', 'like', $like))
                    ->latest()->limit(self::LIMIT)->get()
                    ->map(fn ($p) => [
                        'label' => $p->project_name,
                        'meta' => $p->customer_name ?? $p->product_type ?? '',
                        'url' => route('projects.show', $p),
                    ]),

                'Customers' => Customer::query()
                    ->where(fn ($w) => $w->where('name', 'like', $like)
                        ->orWhere('company', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('phone', 'like', $like))
                    ->orderBy('name')->limit(self::LIMIT)->get()
                    ->map(fn ($c) => [
                        'label' => $c->name,
                        'meta' => $c->company ?? $c->email ?? '',
                        'url' => route('customers.show', $c),
                    ]),

                'Quotes' => Quote::query()->with('customer')
                    ->where(fn ($w) => $w->where('quote_number', 'like', $like)
                        ->orWhere('title', 'like', $like))
                    ->latest()->limit(self::LIMIT)->get()
                    ->map(fn ($q) => [
                        'label' => $q->quote_number,
                        'meta' => $q->title ?? $q->customer?->name ?? '',
                        'url' => route('quotes.show', $q),
                    ]),

                'Invoices' => Invoice::query()->with('customer')
                    ->where(fn ($w) => $w->where('invoice_number', 'like', $like)
                        ->orWhere('title', 'like', $like))
                    ->latest()->limit(self::LIMIT)->get()
                    ->map(fn ($i) => [
                        'label' => $i->invoice_number,
                        'meta' => $i->title ?? $i->customer?->name ?? '',
                        'url' => route('invoices.show', $i),
                    ]),

                'Products' => Product::query()
                    ->where(fn ($w) => $w->where('name', 'like', $like)
                        ->orWhere('sku', 'like', $like)
                        ->orWhere('brand', 'like', $like))
                    ->orderBy('name')->limit(self::LIMIT)->get()
                    ->map(fn ($p) => [
                        'label' => $p->name,
                        'meta' => $p->sku ?? $p->category ?? '',
                        'url' => route('products.show', $p),
                    ]),

                'Online orders' => OnlineOrder::query()
                    ->where(fn ($w) => $w->where('external_ref', 'like', $like)
                        ->orWhere('buyer_name', 'like', $like)
                        ->orWhere('item_label', 'like', $like))
                    ->latest('ordered_at')->limit(self::LIMIT)->get()
                    ->map(fn ($o) => [
                        'label' => $o->external_ref,
                        'meta' => $o->buyer_name . ' · ' . $o->item_label,
                        'url' => route('online-orders.index'),
                    ]),
            ];
        }

        $total = collect($groups)->sum(fn ($g) => $g->count());

        return role_view('shared.search.index', compact('q', 'groups', 'total'));
    }
}
