<?php

declare(strict_types=1);

use App\Environment;
use App\ProductType;
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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('ksuid');
            $table->string('name');
            $table->text('description');
            $table->string('type')->default(ProductType::ORDER);
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
        Schema::dropIfExists('products');
    }
};
