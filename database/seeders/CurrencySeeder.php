<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Currency;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['country_name' => 'Afghanistan', 'currency_name' => 'Afghan Afghani', 'currency_code' => 'AFN', 'symbol' => '؋', 'exchange_rate' => NULL],
            ['country_name' => 'Albania', 'currency_name' => 'Albanian Lek', 'currency_code' => 'ALL', 'symbol' => 'L', 'exchange_rate' => NULL],
            ['country_name' => 'Algeria', 'currency_name' => 'Algerian Dinar', 'currency_code' => 'DZD', 'symbol' => 'د.ج', 'exchange_rate' => NULL],
            ['country_name' => 'Argentina', 'currency_name' => 'Argentine Peso', 'currency_code' => 'ARS', 'symbol' => '$', 'exchange_rate' => NULL],
            ['country_name' => 'Australia', 'currency_name' => 'Australian Dollar', 'currency_code' => 'AUD', 'symbol' => 'A$', 'exchange_rate' => NULL],
            ['country_name' => 'Bangladesh', 'currency_name' => 'Bangladeshi Taka', 'currency_code' => 'BDT', 'symbol' => '৳', 'exchange_rate' => NULL],
            ['country_name' => 'Brazil', 'currency_name' => 'Brazilian Real', 'currency_code' => 'BRL', 'symbol' => 'R$', 'exchange_rate' => NULL],
            ['country_name' => 'Canada', 'currency_name' => 'Canadian Dollar', 'currency_code' => 'CAD', 'symbol' => 'C$', 'exchange_rate' => NULL],
            ['country_name' => 'China', 'currency_name' => 'Chinese Yuan', 'currency_code' => 'CNY', 'symbol' => '¥', 'exchange_rate' => NULL],
            ['country_name' => 'Denmark', 'currency_name' => 'Danish Krone', 'currency_code' => 'DKK', 'symbol' => 'kr', 'exchange_rate' => NULL],
            ['country_name' => 'Egypt', 'currency_name' => 'Egyptian Pound', 'currency_code' => 'EGP', 'symbol' => '£', 'exchange_rate' => NULL],
            ['country_name' => 'European Union', 'currency_name' => 'Euro', 'currency_code' => 'EUR', 'symbol' => '€', 'exchange_rate' => NULL],
            ['country_name' => 'India', 'currency_name' => 'Indian Rupee', 'currency_code' => 'INR', 'symbol' => '₹', 'exchange_rate' => 1.00],
            ['country_name' => 'Indonesia', 'currency_name' => 'Indonesian Rupiah', 'currency_code' => 'IDR', 'symbol' => 'Rp', 'exchange_rate' => NULL],
            ['country_name' => 'Japan', 'currency_name' => 'Japanese Yen', 'currency_code' => 'JPY', 'symbol' => '¥', 'exchange_rate' => NULL],
            ['country_name' => 'Malaysia', 'currency_name' => 'Malaysian Ringgit', 'currency_code' => 'MYR', 'symbol' => 'RM', 'exchange_rate' => NULL],
            ['country_name' => 'Mexico', 'currency_name' => 'Mexican Peso', 'currency_code' => 'MXN', 'symbol' => '$', 'exchange_rate' => NULL],
            ['country_name' => 'Nepal', 'currency_name' => 'Nepalese Rupee', 'currency_code' => 'NPR', 'symbol' => 'Rs', 'exchange_rate' => NULL],
            ['country_name' => 'Pakistan', 'currency_name' => 'Pakistani Rupee', 'currency_code' => 'PKR', 'symbol' => '₨', 'exchange_rate' => NULL],
            ['country_name' => 'Russia', 'currency_name' => 'Russian Ruble', 'currency_code' => 'RUB', 'symbol' => '₽', 'exchange_rate' => NULL],
            ['country_name' => 'Saudi Arabia', 'currency_name' => 'Saudi Riyal', 'currency_code' => 'SAR', 'symbol' => '﷼', 'exchange_rate' => NULL],
            ['country_name' => 'South Africa', 'currency_name' => 'South African Rand', 'currency_code' => 'ZAR', 'symbol' => 'R', 'exchange_rate' => NULL],
            ['country_name' => 'Sweden', 'currency_name' => 'Swedish Krona', 'currency_code' => 'SEK', 'symbol' => 'kr', 'exchange_rate' => NULL],
            ['country_name' => 'Switzerland', 'currency_name' => 'Swiss Franc', 'currency_code' => 'CHF', 'symbol' => 'CHF', 'exchange_rate' => NULL],
            ['country_name' => 'United Kingdom', 'currency_name' => 'British Pound', 'currency_code' => 'GBP', 'symbol' => '£', 'exchange_rate' => NULL],
            ['country_name' => 'United States', 'currency_name' => 'US Dollar', 'currency_code' => 'USD', 'symbol' => '$', 'exchange_rate' => NULL],
            ['country_name' => 'United Arab Emirates','currency_name' => 'UAE Dirham','currency_code' => 'AED','symbol' => 'د.إ','exchange_rate' => NULL],

        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['currency_code' => $currency['currency_code']],
                $currency
            );
        }
    }
}
