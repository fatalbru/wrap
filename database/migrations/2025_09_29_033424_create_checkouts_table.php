<?php

declare(strict_types=1);

use App\Environment;
use App\Models\Customer;
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
        Schema::create('checkouts', function (Blueprint $table) {
            $table->id();
            $table->string('ksuid');
            $table->morphs('checkoutable');
            $table->foreignIdFor(Customer::class)->constrained();
            $table->text('redirect_url');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('min_installments')->default(1);
            $table->integer('max_installments')->default(1);
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
