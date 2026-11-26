<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. expense_categories (handles both expense and income categories with type distinction)
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('slug', 110)->index();
            $table->string('type', 20)->default('expense')->index(); // expense, income
            $table->string('color', 20)->default('#319B79');
            $table->string('bg_color', 20)->default('#29243D');
            $table->string('icon', 60)->default('tag');
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 2. expense_subcategories
        Schema::create('expense_subcategories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_category_id')->constrained('expense_categories')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('slug', 110)->index();
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });

        // 3. payment_methods
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('type', 50)->default('mobile_money'); // mobile_money, bank, card, cash, wallet
            $table->string('provider', 100)->nullable();
            $table->string('account_reference', 80)->nullable();
            $table->string('color', 20)->default('#5377B8');
            $table->string('icon', 60)->default('credit-card');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // 4. recurring_transactions
        Schema::create('recurring_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('expense_category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
            $table->foreignId('expense_subcategory_id')->nullable()->constrained('expense_subcategories')->nullOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
            $table->string('title', 160);
            $table->string('type', 20)->default('expense'); // expense, income
            $table->decimal('amount', 15, 2);
            $table->string('frequency', 30)->default('monthly'); // daily, weekly, monthly, quarterly, yearly
            $table->date('start_date');
            $table->date('next_due_date')->index();
            $table->date('last_processed_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('reminder_days_before')->default(3);
            $table->boolean('auto_record')->default(false);
            $table->string('status', 30)->default('active')->index(); // active, paused, completed
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. transactions (Unified Transaction Engine table for all recorded expenses and income)
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('expense_category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
            $table->foreignId('expense_subcategory_id')->nullable()->constrained('expense_subcategories')->nullOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
            $table->foreignId('recurring_transaction_id')->nullable()->constrained('recurring_transactions')->nullOnDelete();
            $table->string('title', 180);
            $table->string('type', 20)->default('expense')->index(); // expense, income
            $table->string('income_source', 60)->nullable(); // Salary, Business income, Freelance income, Investment income, Other income
            $table->decimal('amount', 15, 2);
            $table->string('currency', 10)->default('GH¢');
            $table->date('transaction_date')->index();
            $table->time('transaction_time')->nullable();
            $table->string('merchant_or_source', 150)->nullable();
            $table->string('reference_code', 80)->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_frequency', 30)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type', 'transaction_date'], 'idx_user_type_date');
            $table->index(['user_id', 'expense_category_id', 'transaction_date'], 'idx_user_cat_date');
        });

        // 6. receipts
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('file_name', 255);
            $table->string('file_path', 255);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('merchant_name', 150)->nullable();
            $table->string('receipt_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 7. budgets
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('period_type', 20)->default('monthly')->index(); // weekly, monthly, quarterly, yearly
            $table->unsignedSmallInteger('year')->index();
            $table->unsignedTinyInteger('month')->nullable()->index();
            $table->unsignedTinyInteger('week_number')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('total_limit', 15, 2);
            $table->decimal('alert_threshold_percent', 5, 2)->default(80.00);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 8. budget_categories
        Schema::create('budget_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained('budgets')->cascadeOnDelete();
            $table->foreignId('expense_category_id')->constrained('expense_categories')->cascadeOnDelete();
            $table->decimal('allocated_amount', 15, 2);
            $table->decimal('alert_threshold_percent', 5, 2)->default(80.00);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['budget_id', 'expense_category_id'], 'uq_budget_category');
        });

        // 9. financial_goals
        Schema::create('financial_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 160);
            $table->string('category', 80)->default('Emergency Fund');
            $table->decimal('target_amount', 15, 2);
            $table->decimal('current_amount', 15, 2)->default(0.00);
            $table->date('start_date')->nullable();
            $table->date('target_date')->nullable();
            $table->string('color', 20)->default('#319B79');
            $table->string('icon', 60)->default('piggy-bank');
            $table->string('priority', 20)->default('high'); // low, medium, high
            $table->string('status', 30)->default('active'); // active, achieved, paused
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 10. savings_records (Explicitly recorded savings allocations, not auto-classified unspent income)
        Schema::create('savings_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('financial_goal_id')->nullable()->constrained('financial_goals')->nullOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
            $table->string('title', 160);
            $table->string('action_type', 20)->default('deposit'); // deposit, withdrawal
            $table->decimal('amount', 15, 2);
            $table->date('record_date')->index();
            $table->string('vault_or_account', 120)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 11. notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 180);
            $table->text('message');
            $table->string('type', 40)->default('budget_alert'); // budget_alert, recurring_reminder, insight, goal_milestone
            $table->string('severity', 20)->default('warning'); // info, success, warning, danger
            $table->string('action_url', 255)->nullable();
            $table->boolean('is_read')->default(false)->index();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('savings_records');
        Schema::dropIfExists('financial_goals');
        Schema::dropIfExists('budget_categories');
        Schema::dropIfExists('budgets');
        Schema::dropIfExists('receipts');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('recurring_transactions');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('expense_subcategories');
        Schema::dropIfExists('expense_categories');
    }
};
