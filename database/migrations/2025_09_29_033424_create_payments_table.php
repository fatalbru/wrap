<?php

declare(strict_types=1);

use App\Environment;
use App\Models\Customer;
use App\PaymentStatus;
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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('ksuid');
            $table->morphs('payable');
            $table->foreignIdFor(Customer::class)->constrained();
            $table->bigInteger('amount');
            $table->string('status')->default(PaymentStatus::PENDING);
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->string('decline_reason')->nullable();
            $table->string('vendor_id')->nullable();
            $table->json('vendor_data');
            $table->string('environment')->default(Environment::TEST);
            $table->unique(['ksuid']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
