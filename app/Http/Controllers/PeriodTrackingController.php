<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Models\ExpenseSubcategory;
use App\Models\PaymentMethod;
use App\Services\SpendingInsightsService;
use App\Services\UnifiedFinancialEngine;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PeriodTrackingController extends Controller
{
    public function __construct(
        private readonly UnifiedFinancialEngine $engine,
        private readonly SpendingInsightsService $insightsService
    ) {}

    /**
     * Multi-Horizon Tracking & Spending Breakdown Explorer Page (Daily, Weekly, Monthly, Yearly).
     */
    public function index(Request $request)
    {
        $user = $this->currentUser();

        $now = now();
        $tab = $request->query('tab', 'explorer'); // explorer, daily, weekly, monthly, yearly
        $date = $request->query('date', $now->toDateString());
        $refDate = Carbon::parse($date);
        $year = (int) $request->query('year', $refDate->year);
        $month = (int) $request->query('month', $refDate->month);

        $explorer = $this->engine->getSpendingBreakdownExplorer($user, $year, $month);
        $daily = $this->engine->getDailyOverview($user, $date);
        $weekly = $this->engine->getWeeklyOverview($user, $date);
        $monthly = $this->engine->getMonthlyOverview($user, $year, $month);
        $yearly = $this->engine->getYearlyOverview($user, $year);

        $categories = ExpenseCategory::with('subcategories')
            ->where('user_id', $user->id)
            ->orderBy('sort_order')
            ->get();
        $paymentMethods = PaymentMethod::where('user_id', $user->id)->get();

        return view('tracking.index', compact(
            'user',
            'tab',
            'date',
            'year',
            'month',
            'explorer',
            'daily',
            'weekly',
            'monthly',
            'yearly',
            'categories',
            'paymentMethods'
        ));
    }

    /**
     * Financial Insights & Spending Intelligence Page.
     */
    public function insights(Request $request)
    {
        $user = $this->currentUser();
        $date = $request->query('date', now()->toDateString());
        $refCarbon = Carbon::parse($date);

        $insights = $this->insightsService->generateInsights($user, $refCarbon);
        $summary = $this->engine->getExecutiveSummary($user, $refCarbon);
        $monthly = $this->engine->getMonthlyOverview($user, (int) $refCarbon->year, (int) $refCarbon->month);
        $yearly = $this->engine->getYearlyOverview($user, (int) $refCarbon->year);

        $categories = ExpenseCategory::with('subcategories')
            ->where('user_id', $user->id)
            ->orderBy('sort_order')
            ->get();
        $paymentMethods = PaymentMethod::where('user_id', $user->id)->get();

        return view('insights.index', compact(
            'user',
            'date',
            'insights',
            'summary',
            'monthly',
            'yearly',
            'categories',
            'paymentMethods'
        ));
    }

    /**
     * Categories & Subcategories Manager.
     */
    public function categories()
    {
        $user = $this->currentUser();

        // Automatically consolidate any duplicate categories with the same normalized name & type
        $this->consolidateDuplicateCategories($user->id);

        $categories = ExpenseCategory::with(['subcategories', 'transactions'])
            ->where('user_id', $user->id)
            ->get()
            ->map(function (ExpenseCategory $cat) {
                $cat->total_spent = (float) $cat->transactions->sum('amount');
                $cat->tx_count = $cat->transactions->count();
                return $cat;
            })
            ->sortBy([
                fn (ExpenseCategory $a, ExpenseCategory $b) => ($a->type === 'expense' ? 0 : 1) <=> ($b->type === 'expense' ? 0 : 1),
                fn (ExpenseCategory $a, ExpenseCategory $b) => (float) $b->total_spent <=> (float) $a->total_spent,
                fn (ExpenseCategory $a, ExpenseCategory $b) => $b->tx_count <=> $a->tx_count,
                fn (ExpenseCategory $a, ExpenseCategory $b) => strcmp($a->name, $b->name),
            ])
            ->values();

        $paymentMethods = PaymentMethod::where('user_id', $user->id)->get();

        return view('categories.index', compact('user', 'categories', 'paymentMethods'));
    }

    /**
     * Consolidate duplicate categories (same user, type, and case-insensitive trimmed name)
     * and duplicate subcategories under the same parent category.
     */
    private function consolidateDuplicateCategories(int $userId): void
    {
        $allCategories = ExpenseCategory::where('user_id', $userId)->orderBy('id')->get();

        $grouped = $allCategories->groupBy(function (ExpenseCategory $cat) {
            $normalizedName = preg_replace('/\s+/', ' ', strtolower(trim((string) $cat->name)));
            return $cat->type . '|' . $normalizedName;
        });

        foreach ($grouped as $group) {
            if ($group->count() <= 1) {
                continue;
            }

            /** @var ExpenseCategory $primary */
            $primary = $group->first();
            $duplicates = $group->slice(1);

            foreach ($duplicates as $dup) {
                // Reassign transactions, subcategories, budget allocations, and recurring bills to the primary category
                \App\Models\Transaction::where('user_id', $userId)
                    ->where('expense_category_id', $dup->id)
                    ->update(['expense_category_id' => $primary->id]);

                ExpenseSubcategory::where('user_id', $userId)
                    ->where('expense_category_id', $dup->id)
                    ->update(['expense_category_id' => $primary->id]);

                $dupAllocations = \App\Models\BudgetCategory::where('expense_category_id', $dup->id)->get();
                foreach ($dupAllocations as $dupAlloc) {
                    $existingPrimaryAlloc = \App\Models\BudgetCategory::where('budget_id', $dupAlloc->budget_id)
                        ->where('expense_category_id', $primary->id)
                        ->first();
                    if ($existingPrimaryAlloc) {
                        $dupAlloc->delete();
                    } else {
                        $dupAlloc->update(['expense_category_id' => $primary->id]);
                    }
                }

                \App\Models\RecurringTransaction::where('user_id', $userId)
                    ->where('expense_category_id', $dup->id)
                    ->update(['expense_category_id' => $primary->id]);

                $dup->delete();
            }
        }

        // Also consolidate any duplicate subcategories under the same parent category
        $allSubcategories = ExpenseSubcategory::where('user_id', $userId)->orderBy('id')->get();
        $subGrouped = $allSubcategories->groupBy(function (ExpenseSubcategory $sub) {
            $normalizedSub = preg_replace('/\s+/', ' ', strtolower(trim((string) $sub->name)));
            return $sub->expense_category_id . '|' . $normalizedSub;
        });

        foreach ($subGrouped as $subGroup) {
            if ($subGroup->count() <= 1) {
                continue;
            }
            $primarySub = $subGroup->first();
            foreach ($subGroup->slice(1) as $dupSub) {
                \App\Models\Transaction::where('user_id', $userId)
                    ->where('expense_subcategory_id', $dupSub->id)
                    ->update(['expense_subcategory_id' => $primarySub->id]);

                $dupSub->delete();
            }
        }
    }

    public function storeCategory(Request $request)
    {
        $user = $this->currentUser();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:expense,income'],
            'color' => ['required', 'string', 'max:20'],
            'bg_color' => ['nullable', 'string', 'max:20'],
            'icon' => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $cleanName = preg_replace('/\s+/', ' ', trim($validated['name']));
        $existing = ExpenseCategory::where('user_id', $user->id)
            ->get()
            ->first(function (ExpenseCategory $cat) use ($cleanName) {
                return preg_replace('/\s+/', ' ', strtolower(trim($cat->name))) === strtolower($cleanName);
            });

        if ($existing) {
            return back()
                ->withInput()
                ->withErrors(['name' => "Category '{$existing->name}' already exists ({$existing->type}). Duplicate category names are not allowed."]);
        }

        ExpenseCategory::create([
            'user_id' => $user->id,
            'name' => $cleanName,
            'slug' => Str::slug($cleanName),
            'type' => $validated['type'],
            'color' => $validated['color'],
            'bg_color' => $validated['bg_color'] ?? '#29243D',
            'icon' => $validated['icon'] ?? 'tag',
            'description' => $validated['description'] ?? null,
            'is_default' => false,
            'sort_order' => 50,
        ]);

        return back()->with('success', 'Custom category created with solid colour badge.');
    }

    public function updateCategory(Request $request, ExpenseCategory $category)
    {
        $user = $this->currentUser();
        abort_if($category->user_id !== $user->id, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['nullable', 'in:expense,income'],
            'color' => ['required', 'string', 'max:20'],
            'bg_color' => ['nullable', 'string', 'max:20'],
            'icon' => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $cleanName = preg_replace('/\s+/', ' ', trim($validated['name']));
        $duplicate = ExpenseCategory::where('user_id', $user->id)
            ->where('id', '!=', $category->id)
            ->get()
            ->first(function (ExpenseCategory $cat) use ($cleanName) {
                return preg_replace('/\s+/', ' ', strtolower(trim($cat->name))) === strtolower($cleanName);
            });

        if ($duplicate) {
            return back()
                ->withInput()
                ->withErrors(['name' => "Another category named '{$duplicate->name}' already exists. Duplicate category names are not allowed."]);
        }

        $category->update([
            'name' => $cleanName,
            'slug' => Str::slug($cleanName),
            'type' => $validated['type'] ?? $category->type,
            'color' => $validated['color'],
            'bg_color' => $validated['bg_color'] ?? $category->bg_color,
            'icon' => $validated['icon'] ?? $category->icon,
            'description' => $validated['description'] ?? $category->description,
        ]);

        return back()->with('success', "Category '{$category->name}' updated.");
    }

    public function destroyCategory(ExpenseCategory $category)
    {
        $user = $this->currentUser();
        abort_if($category->user_id !== $user->id, 403);

        $category->delete();
        return back()->with('success', 'Category deleted.');
    }

    public function storeSubcategory(Request $request)
    {
        $user = $this->currentUser();
        $validated = $request->validate([
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $cleanName = preg_replace('/\s+/', ' ', trim($validated['name']));
        $existingSub = ExpenseSubcategory::where('user_id', $user->id)
            ->where('expense_category_id', $validated['expense_category_id'])
            ->get()
            ->first(function (ExpenseSubcategory $sub) use ($cleanName) {
                return preg_replace('/\s+/', ' ', strtolower(trim($sub->name))) === strtolower($cleanName);
            });

        if ($existingSub) {
            return back()
                ->withInput()
                ->withErrors(['name' => "Subcategory '{$existingSub->name}' already exists in this category."]);
        }

        ExpenseSubcategory::create([
            'expense_category_id' => $validated['expense_category_id'],
            'user_id' => $user->id,
            'name' => $cleanName,
            'slug' => Str::slug($cleanName),
            'description' => $validated['description'] ?? null,
        ]);

        return back()->with('success', 'Subcategory added.');
    }

    public function updateSubcategory(Request $request, ExpenseSubcategory $subcategory)
    {
        $user = $this->currentUser();
        abort_if($subcategory->user_id !== $user->id, 403);

        $validated = $request->validate([
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $parentCat = ExpenseCategory::where('id', $validated['expense_category_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        $cleanName = preg_replace('/\s+/', ' ', trim($validated['name']));
        $duplicateSub = ExpenseSubcategory::where('user_id', $user->id)
            ->where('expense_category_id', $parentCat->id)
            ->where('id', '!=', $subcategory->id)
            ->get()
            ->first(function (ExpenseSubcategory $sub) use ($cleanName) {
                return preg_replace('/\s+/', ' ', strtolower(trim($sub->name))) === strtolower($cleanName);
            });

        if ($duplicateSub) {
            return back()
                ->withInput()
                ->withErrors(['name' => "Subcategory '{$duplicateSub->name}' already exists under '{$parentCat->name}'."]);
        }

        $subcategory->update([
            'expense_category_id' => $parentCat->id,
            'name' => $cleanName,
            'slug' => Str::slug($cleanName),
            'description' => $validated['description'] ?? $subcategory->description,
        ]);

        return back()->with('success', "Subcategory '{$subcategory->name}' updated.");
    }

    public function destroySubcategory(ExpenseSubcategory $subcategory)
    {
        $user = $this->currentUser();
        abort_if($subcategory->user_id !== $user->id, 403);

        $subcategory->delete();
        return back()->with('success', 'Subcategory removed.');
    }
}
