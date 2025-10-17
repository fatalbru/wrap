<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Environment;
use App\Enums\FrequencyType;
use App\Models\Application;
use App\Models\Price;
use App\Models\Product;
use App\Models\User;
use App\Enums\PaymentVendor;
use App\Enums\ProductType;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create();

        Application::create([
            'name' => 'Orders (Cards)',
            'environment' => Environment::TEST,
            'vendor' => PaymentVendor::MERCADOPAGO_CARD,
            'vendor_secondary_id' => env('TEST_CARDS_MERCADOPAGO_USER_ID'),
            'vendor_id' => env('TEST_CARDS_MERCADOPAGO_APPLICATION_ID'),
            'public_key' => env('TEST_CARDS_MERCADOPAGO_PUBLIC_KEY'),
            'private_key' => env('TEST_CARDS_MERCADOPAGO_ACCESS_TOKEN'),
            'features' => [ProductType::ORDER],
        ]);

        Application::create([
            'name' => 'Subscriptions (Cards)',
            'environment' => Environment::TEST,
            'vendor' => PaymentVendor::MERCADOPAGO_CARD,
            'vendor_secondary_id' => env('TEST_WALLET_MERCADOPAGO_USER_ID'),
            'vendor_id' => env('TEST_WALLET_MERCADOPAGO_APPLICATION_ID'),
            'public_key' => env('TEST_WALLET_MERCADOPAGO_PUBLIC_KEY'),
            'private_key' => env('TEST_WALLET_MERCADOPAGO_ACCESS_TOKEN'),
            'features' => [ProductType::SUBSCRIPTION],
        ]);

        Application::create([
            'name' => 'Orders + Subscriptions (Wallet)',
            'environment' => Environment::TEST,
            'vendor' => PaymentVendor::MERCADOPAGO,
            'vendor_id' => env('TEST_WALLET_MERCADOPAGO_APPLICATION_ID'),
            'vendor_secondary_id' => env('TEST_WALLET_MERCADOPAGO_USER_ID'),
            'public_key' => env('TEST_WALLET_MERCADOPAGO_PUBLIC_KEY'),
            'private_key' => env('TEST_WALLET_MERCADOPAGO_ACCESS_TOKEN'),
            'features' => [ProductType::ORDER, ProductType::SUBSCRIPTION],
        ]);

        tap(Product::factory()->order()->create(), function (Product $product) {
            Price::factory()->for($product)->create();
        });

        tap(Product::factory()->subscription()->create(), function (Product $product) {
            Price::factory()->for($product)->create([
                'frequency' => FrequencyType::MONTHLY,
            ]);
        });

        tap(Product::factory()->subscription()->create(), function (Product $product) {
            Price::factory()->for($product)->create([
                'trial_days' => 30,
                'frequency' => FrequencyType::MONTHLY,
            ]);
        });

        dump($user->createToken('api-token'));
    }
}
