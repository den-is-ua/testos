<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Return a paginated list of invoices (placeholder).
     *
     * Replace this placeholder array with a real Eloquent query when a model/table is available:
     *    return Invoice::paginate($perPage);
     *
     * This endpoint returns the standard LengthAwarePaginator->toArray() structure:
     * {
     *   "current_page": 1,
     *   "data": [ ... ],
     *   "from": 1,
     *   "last_page": 5,
     *   "per_page": 5,
     *   "to": 5,
     *   "total": 25
     * }
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 5);
        $page = (int) $request->query('page', 1);

        // Placeholder dataset — in real app replace with a DB/query (Eloquent) call.
        $statuses = ['Paid', 'Pending', 'Unpaid'];
        $methods = ['Credit Card', 'PayPal', 'Bank Transfer'];

        $all = [];
        for ($i = 1; $i <= 22; $i++) {
            $all[] = [
                'invoice' => sprintf('INV%03d', $i),
                'paymentStatus' => $statuses[array_rand($statuses)],
                'totalAmount' => '$' . number_format(rand(100, 900), 2),
                'paymentMethod' => $methods[array_rand($methods)],
            ];
        }

        $total = count($all);
        $offset = ($page - 1) * $perPage;
        $slice = array_slice($all, $offset, $perPage);

        $paginator = new LengthAwarePaginator(
            $slice,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        if ($request->expectsJson()) {
            return response()->json($paginator->toArray());
        }

        return Inertia::render('Home');
    }
}
