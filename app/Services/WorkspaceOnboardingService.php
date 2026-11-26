<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\Budget;
use App\Models\BudgetCategory;
use App\Models\ExpenseCategory;
use App\Models\ExpenseSubcategory;
use App\Models\FinancialGoal;
use App\Models\PaymentMethod;
use App\Models\Receipt;
use App\Models\RecurringTransaction;
use App\Models\SavingsRecord;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WorkspaceOnboardingService
{
    /**
     * Provision a complete, isolated personal financial workspace for a user,
     * pre-populated with realistic sample data (from the local seeder dataset)
     * so they can take the interactive Guided Platform Tour and then reset to start fresh.
     */
    public function initializeUserWorkspace(User $user, bool $withSampleTransactions = true): void
    {
        DB::transaction(function () use ($user, $withSampleTransactions) {
            // 1. Default Payment Methods
            $paymentMethodsData = [
                ['name' => 'MTN MoMo Wallet', 'type' => 'mobile_money', 'provider' => 'MTN Ghana', 'account_reference' => '024 *** 8910', 'color' => '#E5A93C', 'icon' => 'smartphone', 'is_default' => true],
                ['name' => 'Ecobank Visa Debit', 'type' => 'card', 'provider' => 'Ecobank Ghana', 'account_reference' => '4582 •••• 7712', 'color' => '#4E73DF', 'icon' => 'credit-card', 'is_default' => false],
                ['name' => 'Stanbic Current Account', 'type' => 'bank', 'provider' => 'Stanbic Bank', 'account_reference' => '90400081234', 'color' => '#2D9F78', 'icon' => 'landmark', 'is_default' => false],
                ['name' => 'Telecel Cash', 'type' => 'mobile_money', 'provider' => 'Telecel', 'account_reference' => '020 *** 3412', 'color' => '#E66D55', 'icon' => 'wallet', 'is_default' => false],
                ['name' => 'Cash in Hand', 'type' => 'cash', 'provider' => 'Physical Cash', 'account_reference' => ($user->currency ?: 'GH¢') . ' Notes', 'color' => '#D96D9B', 'icon' => 'banknote', 'is_default' => false],
            ];

            $paymentMethods = [];
            foreach ($paymentMethodsData as $pm) {
                $paymentMethods[$pm['name']] = PaymentMethod::updateOrCreate(
                    ['user_id' => $user->id, 'name' => $pm['name']],
                    array_merge($pm, ['user_id' => $user->id])
                );
            }

            // 2. 16 Required Expense Categories + Subcategories
            $expenseCategoriesData = [
                ['name' => 'Food & Groceries', 'color' => '#2D9F78', 'bg_color' => '#1E1B2E', 'icon' => 'utensils', 'subs' => ['Supermarket & Provisions', 'Local Market & Fresh Produce', 'Restaurants & Dining Out', 'Bakery & Snacks']],
                ['name' => 'Transportation', 'color' => '#4E73DF', 'bg_color' => '#1E2028', 'icon' => 'car', 'subs' => ['Ride Hailing (Uber / Bolt)', 'Public Transit', 'Vehicle Servicing & Repairs', 'Parking & Tolls']],
                ['name' => 'Fuel', 'color' => '#E5A93C', 'bg_color' => '#1E1B2E', 'icon' => 'fuel', 'subs' => ['Shell V-Power / Petrol', 'TotalEnergies Fuel', 'GOIL Super XP', 'Generator Fuel']],
                ['name' => 'Accommodation & Rent', 'color' => '#1E1B2E', 'bg_color' => '#F4F5F8', 'icon' => 'home', 'subs' => ['Monthly Rent Reserve', 'Estate Service Charge', 'Home Maintenance & Plumbing', 'Security Levy']],
                ['name' => 'Utilities', 'color' => '#E66D55', 'bg_color' => '#1E2028', 'icon' => 'zap', 'subs' => ['ECG Prepaid Electricity', 'GWCL Water Bill', 'Fibre Broadband & Wi-Fi', 'Mobile Airtime & Data Bundles', 'Waste Management (Zoomlion)']],
                ['name' => 'Shopping', 'color' => '#D96D9B', 'bg_color' => '#1E1B2E', 'icon' => 'shopping-bag', 'subs' => ['Household Appliances', 'Home Decor & Kitchen', 'Electronics & Accessories', 'Personal Care Items']],
                ['name' => 'Clothing & Fashion', 'color' => '#D96D9B', 'bg_color' => '#1E2028', 'icon' => 'shirt', 'subs' => ['Corporate & Native Wear', 'Footwear & Bags', 'Tailoring & Alterations', 'Laundry & Dry Cleaning']],
                ['name' => 'Health & Medical', 'color' => '#2D9F78', 'bg_color' => '#1E2028', 'icon' => 'heart-pulse', 'subs' => ['Pharmacy & Prescriptions', 'Hospital & Lab Consultations', 'Dental & Optical Care', 'Gym & Fitness Membership']],
                ['name' => 'Entertainment', 'color' => '#E66D55', 'bg_color' => '#1E1B2E', 'icon' => 'film', 'subs' => ['Cinema & Events', 'Weekend Hangouts', 'Sports & Recreation', 'Music & Gaming']],
                ['name' => 'Education', 'color' => '#4E73DF', 'bg_color' => '#1E1B2E', 'icon' => 'graduation-cap', 'subs' => ['Professional Certifications', 'Books & Research journals', 'Online Courses', 'Tuition & Seminars']],
                ['name' => 'Family & Personal', 'color' => '#E5A93C', 'bg_color' => '#1E2028', 'icon' => 'users', 'subs' => ['Parents & Family Support', 'Barber & Grooming', 'Gifts & Celebrations', 'Childcare & Dependents']],
                ['name' => 'Church & Donations', 'color' => '#2D9F78', 'bg_color' => '#1E1B2E', 'icon' => 'heart-handshake', 'subs' => ['Tithe & Firstfruit', 'Sunday Offering', 'Charity & Community Support', 'Project Seed']],
                ['name' => 'Business Expenses', 'color' => '#4E73DF', 'bg_color' => '#1E2028', 'icon' => 'briefcase', 'subs' => ['Cloud Hosting & Domains', 'Client Meetings', 'Software Tools & SaaS', 'Office Supplies & Printing']],
                ['name' => 'Travel & Holidays', 'color' => '#E66D55', 'bg_color' => '#1E2028', 'icon' => 'plane', 'subs' => ['Domestic Flights & Transport', 'Hotels & Resorts', 'Holiday Activities', 'Travel Logistics']],
                ['name' => 'Subscriptions', 'color' => '#D96D9B', 'bg_color' => '#1E1B2E', 'icon' => 'repeat', 'subs' => ['Netflix & Showmax', 'Spotify Premium', 'ChatGPT Plus & Cloud Tools', 'Apple iCloud / Google One']],
                ['name' => 'Miscellaneous', 'color' => '#646A7A', 'bg_color' => '#F4F5F8', 'icon' => 'layers', 'subs' => ['MoMo E-Levy & Transfer Fees', 'Bank Charges', 'Emergency Unplanned', 'General Sundries']],
            ];

            $categories = [];
            $subcategories = [];

            foreach ($expenseCategoriesData as $idx => $catData) {
                $category = ExpenseCategory::updateOrCreate(
                    ['user_id' => $user->id, 'name' => $catData['name'], 'type' => 'expense'],
                    [
                        'slug' => Str::slug($catData['name']),
                        'color' => $catData['color'],
                        'bg_color' => $catData['bg_color'],
                        'icon' => $catData['icon'],
                        'description' => 'Tracked expenditure under ' . $catData['name'],
                        'is_default' => true,
                        'sort_order' => $idx + 1,
                    ]
                );
                $categories[$catData['name']] = $category;
                $subcategories[$catData['name']] = [];

                foreach ($catData['subs'] as $subName) {
                    $sub = ExpenseSubcategory::updateOrCreate(
                        ['expense_category_id' => $category->id, 'name' => $subName],
                        [
                            'user_id' => $user->id,
                            'slug' => Str::slug($subName),
                            'description' => $subName . ' (' . $catData['name'] . ')',
                        ]
                    );
                    $subcategories[$catData['name']][$subName] = $sub;
                }
            }

            // 3. 5 Standard Income Categories
            $incomeCategoriesData = [
                ['name' => 'Salary', 'color' => '#2D9F78', 'icon' => 'wallet'],
                ['name' => 'Business income', 'color' => '#4E73DF', 'icon' => 'briefcase'],
                ['name' => 'Freelance income', 'color' => '#E5A93C', 'icon' => 'laptop'],
                ['name' => 'Investment income', 'color' => '#D96D9B', 'icon' => 'trending-up'],
                ['name' => 'Other income', 'color' => '#E66D55', 'icon' => 'coins'],
            ];

            foreach ($incomeCategoriesData as $idx => $incData) {
                $category = ExpenseCategory::updateOrCreate(
                    ['user_id' => $user->id, 'name' => $incData['name'], 'type' => 'income'],
                    [
                        'slug' => Str::slug($incData['name']),
                        'color' => $incData['color'],
                        'bg_color' => '#1E1B2E',
                        'icon' => $incData['icon'],
                        'description' => 'Recorded inflow from ' . $incData['name'],
                        'is_default' => true,
                        'sort_order' => 20 + $idx,
                    ]
                );
                $categories[$incData['name']] = $category;
            }

            if (!$withSampleTransactions) {
                return;
            }

            // Clear any prior demo records for deterministic seeding
            Transaction::where('user_id', $user->id)->delete();
            Budget::where('user_id', $user->id)->delete();
            RecurringTransaction::where('user_id', $user->id)->delete();
            FinancialGoal::where('user_id', $user->id)->delete();
            SavingsRecord::where('user_id', $user->id)->delete();
            AppNotification::where('user_id', $user->id)->delete();

            // 4. Recurring Transactions
            $recurringData = [
                ['title' => 'ECG Prepaid Electricity Top-Up', 'type' => 'expense', 'cat' => 'Utilities', 'sub' => 'ECG Prepaid Electricity', 'pm' => 'MTN MoMo Wallet', 'amount' => '450.00', 'frequency' => 'monthly', 'start_date' => '2026-01-03', 'next_due_date' => '2026-12-03', 'last_processed_date' => '2026-11-03', 'reminder_days_before' => 3, 'auto_record' => true],
                ['title' => 'Fibre Broadband Home Internet', 'type' => 'expense', 'cat' => 'Utilities', 'sub' => 'Fibre Broadband & Wi-Fi', 'pm' => 'MTN MoMo Wallet', 'amount' => '380.00', 'frequency' => 'monthly', 'start_date' => '2026-01-05', 'next_due_date' => '2026-12-05', 'last_processed_date' => '2026-11-05', 'reminder_days_before' => 3, 'auto_record' => true],
                ['title' => 'ChatGPT Plus & Cloud Workspace Suite', 'type' => 'expense', 'cat' => 'Subscriptions', 'sub' => 'ChatGPT Plus & Cloud Tools', 'pm' => 'Ecobank Visa Debit', 'amount' => '340.00', 'frequency' => 'monthly', 'start_date' => '2026-01-12', 'next_due_date' => '2026-11-28', 'last_processed_date' => '2026-10-28', 'reminder_days_before' => 5, 'auto_record' => false],
                ['title' => 'Weekly Shell V-Power Fuel Refill', 'type' => 'expense', 'cat' => 'Fuel', 'sub' => 'Shell V-Power / Petrol', 'pm' => 'Ecobank Visa Debit', 'amount' => '320.00', 'frequency' => 'weekly', 'start_date' => '2026-09-01', 'next_due_date' => '2026-11-27', 'last_processed_date' => '2026-11-20', 'reminder_days_before' => 2, 'auto_record' => false],
                ['title' => 'Parents Monthly Upkeep & Allowance', 'type' => 'expense', 'cat' => 'Family & Personal', 'sub' => 'Parents & Family Support', 'pm' => 'MTN MoMo Wallet', 'amount' => '600.00', 'frequency' => 'monthly', 'start_date' => '2026-01-25', 'next_due_date' => '2026-11-26', 'last_processed_date' => '2026-10-25', 'reminder_days_before' => 3, 'auto_record' => false],
                ['title' => 'Estate Security & Facility Service Charge', 'type' => 'expense', 'cat' => 'Accommodation & Rent', 'sub' => 'Estate Service Charge', 'pm' => 'Stanbic Current Account', 'amount' => '1200.00', 'frequency' => 'quarterly', 'start_date' => '2026-01-15', 'next_due_date' => '2027-01-15', 'last_processed_date' => '2026-10-15', 'reminder_days_before' => 7, 'auto_record' => false],
            ];

            foreach ($recurringData as $rec) {
                $cat = $categories[$rec['cat']];
                $sub = $subcategories[$rec['cat']][$rec['sub']] ?? null;
                $pm = $paymentMethods[$rec['pm']];
                RecurringTransaction::create([
                    'user_id' => $user->id,
                    'expense_category_id' => $cat->id,
                    'expense_subcategory_id' => $sub?->id,
                    'payment_method_id' => $pm->id,
                    'title' => $rec['title'],
                    'type' => $rec['type'],
                    'amount' => $rec['amount'],
                    'frequency' => $rec['frequency'],
                    'start_date' => $rec['start_date'],
                    'next_due_date' => $rec['next_due_date'],
                    'last_processed_date' => $rec['last_processed_date'],
                    'reminder_days_before' => $rec['reminder_days_before'],
                    'auto_record' => $rec['auto_record'],
                    'status' => 'active',
                    'notes' => 'Scheduled ' . $rec['frequency'] . ' commitment',
                ]);
            }

            // 5. Historical 2025 Benchmark Transactions
            $historical2025 = [
                ['2025-10-05', 'Monthly Executive Salary', 'income', 'Salary', null, 'Stanbic Current Account', '13500.00', '09:00:00'],
                ['2025-10-12', 'October Household Groceries & Provisions', 'expense', 'Food & Groceries', 'Supermarket & Provisions', 'Ecobank Visa Debit', '1850.00', '14:20:00'],
                ['2025-10-19', 'October Fuel & Commute', 'expense', 'Fuel', 'Shell V-Power / Petrol', 'MTN MoMo Wallet', '1120.00', '08:30:00'],
                ['2025-11-03', 'Monthly Executive Salary', 'income', 'Salary', null, 'Stanbic Current Account', '13500.00', '09:00:00'],
                ['2025-11-08', 'Annual Rent & Facility Reserve Allocation', 'expense', 'Accommodation & Rent', 'Monthly Rent Reserve', 'Stanbic Current Account', '3100.00', '11:15:00'],
                ['2025-11-15', 'November Groceries & Dining', 'expense', 'Food & Groceries', 'Supermarket & Provisions', 'Ecobank Visa Debit', '1920.00', '16:40:00'],
                ['2025-11-22', 'November Transport & Fuel', 'expense', 'Transportation', 'Vehicle Servicing & Repairs', 'MTN MoMo Wallet', '1480.00', '10:05:00'],
                ['2025-12-03', 'Monthly Executive Salary & Bonus', 'income', 'Salary', null, 'Stanbic Current Account', '16800.00', '09:00:00'],
                ['2025-12-18', 'Festive Family Shopping & Holidays', 'expense', 'Travel & Holidays', 'Hotels & Resorts', 'Ecobank Visa Debit', '4200.00', '15:10:00'],
            ];

            foreach ($historical2025 as $row) {
                $this->insertSampleTx($user, $categories, $subcategories, $paymentMethods, $row);
            }

            // 6. Jan - Oct 2026 Monthly Transactions
            $months2026 = [
                1 => ['inc' => '14800.00', 'food' => '1420.00', 'fuel' => '980.00', 'rent' => '2200.00', 'util' => '790.00', 'sub' => '310.00', 'church' => '1100.00'],
                2 => ['inc' => '14800.00', 'food' => '1380.00', 'fuel' => '920.00', 'rent' => '2200.00', 'util' => '740.00', 'sub' => '310.00', 'church' => '1050.00'],
                3 => ['inc' => '15400.00', 'food' => '1510.00', 'fuel' => '1040.00', 'rent' => '2200.00', 'util' => '810.00', 'sub' => '310.00', 'church' => '1150.00'],
                4 => ['inc' => '15200.00', 'food' => '1640.00', 'fuel' => '1090.00', 'rent' => '2200.00', 'util' => '830.00', 'sub' => '330.00', 'church' => '1100.00'],
                5 => ['inc' => '15900.00', 'food' => '1490.00', 'fuel' => '1010.00', 'rent' => '2200.00', 'util' => '780.00', 'sub' => '330.00', 'church' => '1200.00'],
                6 => ['inc' => '16100.00', 'food' => '1580.00', 'fuel' => '1150.00', 'rent' => '2200.00', 'util' => '860.00', 'sub' => '350.00', 'church' => '1150.00'],
                7 => ['inc' => '15800.00', 'food' => '1620.00', 'fuel' => '1180.00', 'rent' => '2200.00', 'util' => '840.00', 'sub' => '360.00', 'church' => '1180.00'],
                8 => ['inc' => '16400.00', 'food' => '1710.00', 'fuel' => '1210.00', 'rent' => '2200.00', 'util' => '890.00', 'sub' => '390.00', 'church' => '1250.00'],
                9 => ['inc' => '16200.00', 'food' => '1680.00', 'fuel' => '1160.00', 'rent' => '2200.00', 'util' => '870.00', 'sub' => '460.00', 'church' => '1200.00'],
                10 => ['inc' => '16800.00', 'food' => '1590.00', 'fuel' => '1140.00', 'rent' => '2000.00', 'util' => '850.00', 'sub' => '540.00', 'church' => '1040.00'],
            ];

            foreach ($months2026 as $m => $vals) {
                $mStr = str_pad((string) $m, 2, '0', STR_PAD_LEFT);
                $this->insertSampleTx($user, $categories, $subcategories, $paymentMethods, ["2026-{$mStr}-02", "Monthly Lead Consultant Salary", 'income', 'Salary', null, 'Stanbic Current Account', $vals['inc'], '09:15:00']);
                $this->insertSampleTx($user, $categories, $subcategories, $paymentMethods, ["2026-{$mStr}-04", "Monthly Rent Reserve & Facility Allocation", 'expense', 'Accommodation & Rent', 'Monthly Rent Reserve', 'Stanbic Current Account', $vals['rent'], '10:30:00']);
                $this->insertSampleTx($user, $categories, $subcategories, $paymentMethods, ["2026-{$mStr}-07", "Supermarket & Household Groceries", 'expense', 'Food & Groceries', 'Supermarket & Provisions', 'Ecobank Visa Debit', $vals['food'], '15:20:00']);
                $this->insertSampleTx($user, $categories, $subcategories, $paymentMethods, ["2026-{$mStr}-11", "ECG Electricity & Fibre Internet", 'expense', 'Utilities', 'ECG Prepaid Electricity', 'MTN MoMo Wallet', $vals['util'], '11:45:00']);
                $this->insertSampleTx($user, $categories, $subcategories, $paymentMethods, ["2026-{$mStr}-15", "Shell V-Power Monthly Fuel", 'expense', 'Fuel', 'Shell V-Power / Petrol', 'Ecobank Visa Debit', $vals['fuel'], '08:20:00']);
                $this->insertSampleTx($user, $categories, $subcategories, $paymentMethods, ["2026-{$mStr}-19", "Tithe & Kingdom Stewardship", 'expense', 'Church & Donations', 'Tithe & Firstfruit', 'MTN MoMo Wallet', $vals['church'], '09:00:00']);
                $this->insertSampleTx($user, $categories, $subcategories, $paymentMethods, ["2026-{$mStr}-23", "Digital SaaS & Media Subscriptions", 'expense', 'Subscriptions', 'ChatGPT Plus & Cloud Tools', 'Ecobank Visa Debit', $vals['sub'], '18:00:00']);
            }

            // 7. November 2026 Transactions (Weeks 1..4)
            $november2026Transactions = [
                ['2026-11-02', 'Senior Systems Architect Monthly Salary', 'income', 'Salary', null, 'Stanbic Current Account', '13200.00', '09:05:00', 'Enterprise Payroll'],
                ['2026-11-12', 'Fintech Diagnostic Advisory Retainer', 'income', 'Business income', null, 'Stanbic Current Account', '2800.00', '14:30:00', 'Diagnostic Client Project'],
                ['2026-11-20', 'Treasury Bill Coupon & Dividend Payout', 'income', 'Investment income', null, 'Stanbic Current Account', '1400.00', '11:00:00', '91-Day GoG T-Bill'],
                ['2026-11-02', 'MaxMart Family Groceries & Fresh Produce', 'expense', 'Food & Groceries', 'Supermarket & Provisions', 'Ecobank Visa Debit', '420.00', '16:15:00', 'MaxMart 37'],
                ['2026-11-03', 'ECG Prepaid Meter Top-Up', 'expense', 'Utilities', 'ECG Prepaid Electricity', 'MTN MoMo Wallet', '450.00', '10:12:00', 'ECG Power App'],
                ['2026-11-05', 'Fibre Broadband High-Speed Internet', 'expense', 'Utilities', 'Fibre Broadband & Wi-Fi', 'MTN MoMo Wallet', '380.00', '08:45:00', 'Telecel Fibre'],
                ['2026-11-06', 'Airport City Bolt Rides & Toll Pass', 'expense', 'Transportation', 'Ride Hailing (Uber / Bolt)', 'MTN MoMo Wallet', '200.00', '18:20:00', 'Bolt Ghana'],
                ['2026-11-08', 'Sunday Tithe & Sanctuary Offering', 'expense', 'Church & Donations', 'Tithe & Firstfruit', 'MTN MoMo Wallet', '850.00', '09:30:00', 'Chosen PCF'],
                ['2026-11-10', 'Shell V-Power Full Tank Refill', 'expense', 'Fuel', 'Shell V-Power / Petrol', 'Ecobank Visa Debit', '350.00', '07:50:00', 'Shell East Legon'],
                ['2026-11-12', 'Cloud Server Hosting & Domain Renewal', 'expense', 'Business Expenses', 'Cloud Hosting & Domains', 'Ecobank Visa Debit', '520.00', '13:10:00', 'Hostinger / AWS'],
                ['2026-11-14', 'Weekend Cinema & Lounge Hangout', 'expense', 'Entertainment', 'Cinema & Events', 'Ecobank Visa Debit', '380.00', '20:15:00', 'Silverbird Cinemas'],
                ['2026-11-16', 'Comprehensive Dental & Wellness Checkup', 'expense', 'Health & Medical', 'Dental & Optical Care', 'Ecobank Visa Debit', '480.00', '11:25:00', 'Nyaho Medical Centre'],
                ['2026-11-18', 'Melcom Kitchen Appliance & Home Care', 'expense', 'Shopping', 'Household Appliances', 'Ecobank Visa Debit', '540.00', '15:40:00', 'Melcom Plus'],
                ['2026-11-19', 'Bespoke Kaftan & Corporate Shirt Tailoring', 'expense', 'Clothing & Fashion', 'Corporate & Native Wear', 'MTN MoMo Wallet', '460.00', '17:05:00', 'Osu Couture Studio'],
                ['2026-11-20', 'Shell V-Power Weekly Fuel Top-Up', 'expense', 'Fuel', 'Shell V-Power / Petrol', 'MTN MoMo Wallet', '320.00', '08:10:00', 'Shell Airport'],
                ['2026-11-22', 'Monthly Accommodation & Estate Reserve', 'expense', 'Accommodation & Rent', 'Monthly Rent Reserve', 'Stanbic Current Account', '1500.00', '10:00:00', 'Cantonments Residences'],
                ['2026-11-23', 'Vehicle Brake Pad & Synthetic Oil Service', 'expense', 'Transportation', 'Vehicle Servicing & Repairs', 'MTN MoMo Wallet', '580.00', '14:15:00', 'AutoParts & Service Hub'],
                ['2026-11-24', 'Netflix, Spotify & AI Workspace Bundle', 'expense', 'Subscriptions', 'Netflix & Showmax', 'Ecobank Visa Debit', '620.00', '19:30:00', 'Digital Subscriptions'],
                ['2026-11-25', 'Palace Hypermarket Weekly Food & Groceries', 'expense', 'Food & Groceries', 'Local Market & Fresh Produce', 'MTN MoMo Wallet', '260.00', '09:40:00', 'Palace Mall Spintex'],
                ['2026-11-25', 'Executive Lunch & Client Coffee Session', 'expense', 'Food & Groceries', 'Restaurants & Dining Out', 'Ecobank Visa Debit', '140.00', '13:15:00', 'Vida e Caffè Labone'],
            ];

            foreach ($november2026Transactions as $row) {
                $this->insertSampleTx($user, $categories, $subcategories, $paymentMethods, $row);
            }

            // 8. November 2026 Master Budget + Category Allocations
            $novBudget = Budget::create([
                'user_id' => $user->id,
                'name' => 'November 2026 Master Spending Plan',
                'period_type' => 'monthly',
                'year' => 2026,
                'month' => 11,
                'start_date' => '2026-11-01',
                'end_date' => '2026-11-30',
                'total_limit' => '10000.00',
                'alert_threshold_percent' => '80.00',
                'notes' => 'Structured monthly ceiling across core living, mobility, and growth categories.',
                'is_active' => true,
            ]);

            $budgetAllocations = [
                'Accommodation & Rent' => '1500.00',
                'Food & Groceries' => '1100.00',
                'Utilities' => '950.00',
                'Church & Donations' => '1000.00',
                'Transportation' => '850.00',
                'Fuel' => '800.00',
                'Subscriptions' => '450.00',
                'Entertainment' => '350.00',
                'Business Expenses' => '700.00',
                'Shopping' => '650.00',
                'Health & Medical' => '600.00',
                'Clothing & Fashion' => '550.00',
                'Family & Personal' => '500.00',
            ];

            foreach ($budgetAllocations as $catName => $limit) {
                if (isset($categories[$catName])) {
                    BudgetCategory::create([
                        'budget_id' => $novBudget->id,
                        'expense_category_id' => $categories[$catName]->id,
                        'allocated_amount' => $limit,
                        'alert_threshold_percent' => 80.00,
                        'notes' => 'Monthly allocation for ' . $catName,
                    ]);
                }
            }

            // 9. Financial Goals & Explicit Savings Records
            $goal1 = FinancialGoal::create([
                'user_id' => $user->id,
                'title' => '6-Month Treasury Bill Emergency Reserve',
                'category' => 'Emergency Fund',
                'target_amount' => '45000.00',
                'current_amount' => '28500.00',
                'start_date' => '2026-01-10',
                'target_date' => '2027-03-31',
                'color' => '#2D9F78',
                'icon' => 'shield-check',
                'priority' => 'high',
                'status' => 'active',
                'notes' => 'Invested in 91-day and 182-day Government Treasury Bills.',
            ]);

            $goal2 = FinancialGoal::create([
                'user_id' => $user->id,
                'title' => 'Aburi Ridge Land & Building Deposit',
                'category' => 'Real Estate',
                'target_amount' => '80000.00',
                'current_amount' => '34000.00',
                'start_date' => '2026-02-01',
                'target_date' => '2027-08-30',
                'color' => '#E5A93C',
                'icon' => 'home',
                'priority' => 'high',
                'status' => 'active',
                'notes' => 'Dedicated capital reserve for plot acquisition and architectural drawings.',
            ]);

            $goal3 = FinancialGoal::create([
                'user_id' => $user->id,
                'title' => 'AI & Product Studio Hardware Upgrade',
                'category' => 'Business Growth',
                'target_amount' => '18000.00',
                'current_amount' => '14200.00',
                'start_date' => '2026-06-01',
                'target_date' => '2026-12-20',
                'color' => '#4E73DF',
                'icon' => 'cpu',
                'priority' => 'medium',
                'status' => 'active',
                'notes' => 'Workstation and mobile test kit reserve.',
            ]);

            $savingsEntries = [
                [$goal1->id, 'Stanbic Current Account', 'Q1-Q3 Treasury Bill Rollover Principal', 'deposit', '25000.00', '2026-09-15', 'Stanbic Money Market Trust'],
                [$goal2->id, 'Stanbic Current Account', 'Land Acquisition Escrow Tranche I', 'deposit', '31500.00', '2026-10-10', 'Ecobank Dollar/Cedi Vault'],
                [$goal3->id, 'Ecobank Visa Debit', 'Hardware Studio Sinking Fund', 'deposit', '13000.00', '2026-10-25', 'Achieve by Petra'],
                [$goal1->id, 'Stanbic Current Account', 'November Emergency Reserve Allocation', 'deposit', '3500.00', '2026-11-04', 'Stanbic Money Market Trust'],
                [$goal2->id, 'Stanbic Current Account', 'November Aburi Plot Top-Up', 'deposit', '2500.00', '2026-11-14', 'Ecobank Dollar/Cedi Vault'],
                [$goal3->id, 'MTN MoMo Wallet', 'November Studio Equipment Allocation', 'deposit', '1200.00', '2026-11-21', 'Achieve by Petra'],
            ];

            foreach ($savingsEntries as $se) {
                SavingsRecord::create([
                    'user_id' => $user->id,
                    'financial_goal_id' => $se[0],
                    'payment_method_id' => $paymentMethods[$se[1]]->id,
                    'title' => $se[2],
                    'action_type' => $se[3],
                    'amount' => $se[4],
                    'record_date' => $se[5],
                    'vault_or_account' => $se[6],
                    'notes' => 'Explicitly recorded savings transfer',
                ]);
            }

            $user->update(['has_sample_data' => true]);
        });
    }

    /**
     * Reset a user's financial database to a completely clean slate after they finish the guided tour,
     * clearing all sample transactions, categories, subcategories, payment methods, budgets, goals,
     * savings, and recurring bills so they can build their own setup from Step 1—while strictly
     * preserving their personal profile details, credentials, and 2FA settings.
     */
    public function resetWorkspaceToFreshStart(User $user): void
    {
        DB::transaction(function () use ($user) {
            // Delete uploaded receipt files for this user
            $receipts = Receipt::where('user_id', $user->id)->get();
            foreach ($receipts as $receipt) {
                if ($receipt->file_path) {
                    Storage::disk('public')->delete($receipt->file_path);
                }
            }

            Receipt::where('user_id', $user->id)->delete();
            Transaction::where('user_id', $user->id)->delete();
            SavingsRecord::where('user_id', $user->id)->delete();
            FinancialGoal::where('user_id', $user->id)->delete();
            RecurringTransaction::where('user_id', $user->id)->delete();
            AppNotification::where('user_id', $user->id)->delete();

            // Clear category budget allocations and budgets
            $userBudgets = Budget::where('user_id', $user->id)->get();
            foreach ($userBudgets as $budget) {
                BudgetCategory::where('budget_id', $budget->id)->delete();
                $budget->delete();
            }

            // Clear sample subcategories, categories, and payment methods so user starts completely fresh
            ExpenseSubcategory::where('user_id', $user->id)->delete();
            ExpenseCategory::where('user_id', $user->id)->delete();
            PaymentMethod::where('user_id', $user->id)->delete();

            // Reset financial targets to 0.00 while strictly preserving all personal profile details
            $user->update([
                'has_sample_data' => false,
                'tour_completed' => true,
                'overall_monthly_budget' => '0.00',
                'weekly_spending_limit' => '0.00',
                'yearly_spending_target' => '0.00',
            ]);
        });
    }

    private function insertSampleTx(
        User $user,
        array $categories,
        array $subcategories,
        array $paymentMethods,
        array $row
    ): void {
        [$date, $title, $type, $catName, $subName, $pmName, $amount, $time] = $row;
        $merchant = $row[8] ?? $catName;

        $cat = $categories[$catName] ?? null;
        $sub = ($subName && isset($subcategories[$catName][$subName])) ? $subcategories[$catName][$subName] : null;
        $pm = $paymentMethods[$pmName] ?? reset($paymentMethods);

        Transaction::create([
            'user_id' => $user->id,
            'expense_category_id' => $cat?->id,
            'expense_subcategory_id' => $sub?->id,
            'payment_method_id' => $pm->id,
            'title' => $title,
            'type' => $type,
            'income_source' => $type === 'income' ? $catName : null,
            'amount' => $amount,
            'currency' => $user->currency ?: 'GH¢',
            'transaction_date' => $date,
            'transaction_time' => $time,
            'merchant_or_source' => $merchant,
            'reference_code' => 'CL-' . strtoupper(Str::random(7)),
            'is_recurring' => false,
            'notes' => $title . ' recorded via Unified Transaction Engine.',
        ]);
    }
}
