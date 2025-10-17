<?php

declare(strict_types=1);

use App\Enums\Environment;
use App\Enums\SubscriptionStatus;
use App\Models\Application;
use App\Models\Customer;
use App\Models\Price;
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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('ksuid');
            $table->foreignIdFor(Application::class)->nullable()->constrained();
            $table->foreignIdFor(Customer::class)->nullable()->constrained();
            $table->foreignIdFor(Price::class)->constrained();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('next_payment_at')->nullable();
            $table->timestamp('trial_started_at')->nullable();
            $table->timestamp('trial_ended_at')->nullable();
            $table->string('status')->default(SubscriptionStatus::PENDING);
            $table->string('vendor_id')->nullable();
            $table->string('vendor')->nullable();
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
        Schema::dropIfExists('subscriptions');
    }
};
