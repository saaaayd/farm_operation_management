<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'buyer_id')) {
                $table->dropForeign(['buyer_id']);
                $table->foreign('buyer_id')
                    ->references('id')
                    ->on('buyers')
                    ->cascadeOnDelete();
            }

            if (!Schema::hasColumn('sales', 'user_id')) {
                $table->foreignId('user_id')
                    ->after('buyer_id')
                    ->constrained()
                    ->cascadeOnDelete();
            }

            if (!Schema::hasColumn('sales', 'unit_price')) {
                $table->decimal('unit_price', 10, 2)
                    ->after('quantity')
                    ->default(0);
            }

            if (!Schema::hasColumn('sales', 'payment_method')) {
                $table->enum('payment_method', ['cash', 'bank_transfer', 'check', 'credit'])
                    ->after('sale_date')
                    ->default('cash');
            }

            if (!Schema::hasColumn('sales', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'paid', 'partial', 'overdue'])
                    ->after('payment_method')
                    ->default('pending');
            }

            if (!Schema::hasColumn('sales', 'delivery_date')) {
                $table->date('delivery_date')
                    ->nullable()
                    ->after('payment_status');
            }

            if (!Schema::hasColumn('sales', 'delivery_address')) {
                $table->string('delivery_address')
                    ->nullable()
                    ->after('delivery_date');
            }

            if (!Schema::hasColumn('sales', 'notes')) {
                $table->text('notes')
                    ->nullable()
                    ->after('delivery_address');
            }
        });

        Schema::table('buyers', function (Blueprint $table) {
            if (!Schema::hasColumn('buyers', 'user_id')) {
                $table->foreignId('user_id')
                    ->after('id')
                    ->constrained()
                    ->cascadeOnDelete();
            }

            if (!Schema::hasColumn('buyers', 'contact_person')) {
                $table->string('contact_person')->nullable()->after('name');
            }

            if (!Schema::hasColumn('buyers', 'email')) {
                $table->string('email')->nullable()->after('contact_person');
            }

            if (!Schema::hasColumn('buyers', 'phone')) {
                $table->string('phone', 32)->nullable()->after('email');
            }

            if (!Schema::hasColumn('buyers', 'type')) {
                $table->string('type')->default('individual')->after('phone');
            }

            if (!Schema::hasColumn('buyers', 'status')) {
                $table->string('status')->default('active')->after('type');
            }

            if (!Schema::hasColumn('buyers', 'payment_terms')) {
                $table->string('payment_terms')->nullable()->after('status');
            }

            if (!Schema::hasColumn('buyers', 'credit_limit')) {
                $table->decimal('credit_limit', 12, 2)->nullable()->after('payment_terms');
            }

            if (!Schema::hasColumn('buyers', 'notes')) {
                $table->text('notes')->nullable()->after('credit_limit');
            }
        });

        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'user_id')) {
                $table->foreignId('user_id')
                    ->after('date')
                    ->constrained()
                    ->cascadeOnDelete();
            }

            if (!Schema::hasColumn('expenses', 'payment_method')) {
                $table->string('payment_method')
                    ->nullable()
                    ->after('user_id');
            }

            if (!Schema::hasColumn('expenses', 'receipt_number')) {
                $table->string('receipt_number')
                    ->nullable()
                    ->after('payment_method');
            }

            if (!Schema::hasColumn('expenses', 'notes')) {
                $table->text('notes')
                    ->nullable()
                    ->after('receipt_number');
            }

            if (!Schema::hasColumn('expenses', 'related_entity_type')) {
                $table->string('related_entity_type')
                    ->nullable()
                    ->after('notes');
            }

            if (!Schema::hasColumn('expenses', 'related_entity_id')) {
                $table->unsignedBigInteger('related_entity_id')
                    ->nullable()
                    ->after('related_entity_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('sales', 'delivery_address')) {
                $table->dropColumn('delivery_address');
            }
            if (Schema::hasColumn('sales', 'delivery_date')) {
                $table->dropColumn('delivery_date');
            }
            if (Schema::hasColumn('sales', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
            if (Schema::hasColumn('sales', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
            if (Schema::hasColumn('sales', 'unit_price')) {
                $table->dropColumn('unit_price');
            }
            if (Schema::hasColumn('sales', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }

            if (Schema::hasColumn('sales', 'buyer_id')) {
                $table->dropForeign(['buyer_id']);
                $table->foreign('buyer_id')
                    ->references('id')
                    ->on('users')
                    ->cascadeOnDelete();
            }
        });

        Schema::table('buyers', function (Blueprint $table) {
            $columns = [
                'notes',
                'credit_limit',
                'payment_terms',
                'status',
                'type',
                'phone',
                'email',
                'contact_person',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('buyers', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('buyers', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });

        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'related_entity_id')) {
                $table->dropColumn('related_entity_id');
            }
            if (Schema::hasColumn('expenses', 'related_entity_type')) {
                $table->dropColumn('related_entity_type');
            }
            if (Schema::hasColumn('expenses', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('expenses', 'receipt_number')) {
                $table->dropColumn('receipt_number');
            }
            if (Schema::hasColumn('expenses', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
            if (Schema::hasColumn('expenses', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};
