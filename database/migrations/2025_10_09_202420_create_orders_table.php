<?php

declare(strict_types=1);

use App\Enums\Environment;
use App\Models\Application;
use App\Models\Customer;
use App\Enums\OrderStatus;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('ksuid');
            $table->foreignIdFor(Application::class)->nullable()->constrained();
            $table->foreignIdFor(Customer::class)->constrained();
            $table->string('status')->default(OrderStatus::PENDING);
            $table->string('vendor_id')->nullable();
            $table->string('vendor')->nullable();
            $table->json('vendor_data');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
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
        Schema::dropIfExists('orders');
    }
};
