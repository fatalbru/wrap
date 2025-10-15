<?php

use App\Environment;
use App\PaymentVendor;
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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('environment')->default(Environment::TEST);
            $table->string('name');
            $table->string('vendor_secondary_id')->nullable();
            $table->string('vendor_id')->nullable();
            $table->string('vendor')->default(PaymentVendor::MERCADOPAGO);
            $table->text('public_key');
            $table->text('private_key');
            $table->json('features')->default('[]');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
