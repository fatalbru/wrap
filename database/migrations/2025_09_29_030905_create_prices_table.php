<?php

declare(strict_types=1);

use App\Enums\Environment;
use App\Models\Product;
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
        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            $table->string('ksuid');
            $table->foreignIdFor(Product::class)->constrained();
            $table->string('name');
            $table->bigInteger('trial_days')->nullable();
            $table->string('frequency')->nullable();
            $table->bigInteger('price');
            $table->string('environment')->default(Environment::TEST);
            $table->string('vendor_id')->nullable();
            $table->string('vendor')->nullable();
            $table->unique(['ksuid']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};
