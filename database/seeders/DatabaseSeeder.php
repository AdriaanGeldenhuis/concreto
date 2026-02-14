<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Default Settings
        $settings = [
            // Theme
            ['key' => 'primary_color', 'value' => '#e67e22', 'group' => 'theme'],
            ['key' => 'secondary_color', 'value' => '#2c3e50', 'group' => 'theme'],
            ['key' => 'bg_color', 'value' => '#f8f9fa', 'group' => 'theme'],
            ['key' => 'text_color', 'value' => '#2c3e50', 'group' => 'theme'],
            ['key' => 'card_color', 'value' => '#ffffff', 'group' => 'theme'],

            // General
            ['key' => 'company_name', 'value' => 'Concreto', 'group' => 'general'],
            ['key' => 'contact_email', 'value' => 'orders@concreto.co.za', 'group' => 'general'],
            ['key' => 'contact_phone', 'value' => '+27 00 000 0000', 'group' => 'general'],
            ['key' => 'contact_address', 'value' => 'Johannesburg, South Africa', 'group' => 'general'],

            // Homepage
            ['key' => 'hero_title', 'value' => 'Quality Building Materials, Delivered', 'group' => 'homepage'],
            ['key' => 'hero_subtitle', 'value' => 'Sand, stone, and construction supplies delivered directly to your site. Order online and track your delivery in real-time.', 'group' => 'homepage'],
            ['key' => 'delivery_info', 'value' => 'Free delivery on orders over R5,000 within 30km', 'group' => 'homepage'],
            ['key' => 'homepage_promo', 'value' => '', 'group' => 'homepage'],

            // Footer
            ['key' => 'footer_about', 'value' => 'Quality building materials delivered to your site. Serving the construction industry with reliable supply and fast delivery.', 'group' => 'footer'],
            ['key' => 'footer_text', 'value' => '© ' . date('Y') . ' Concreto. All rights reserved.', 'group' => 'footer'],

            // Social
            ['key' => 'social_facebook', 'value' => '', 'group' => 'social'],
            ['key' => 'social_instagram', 'value' => '', 'group' => 'social'],

            // Legal
            ['key' => 'terms_content', 'value' => "1. All prices are subject to change without notice.\n2. Payment terms as agreed per customer account.\n3. Delivery times are estimates only.\n4. Claims must be lodged within 24 hours of delivery.\n5. COD customers must pay before delivery.", 'group' => 'legal'],
            ['key' => 'privacy_content', 'value' => "We collect personal information to process your orders and provide our services. Your information is stored securely and never shared with third parties without your consent.", 'group' => 'legal'],

            // Business
            ['key' => 'min_order_value', 'value' => '500', 'group' => 'business'],
            ['key' => 'business_hours', 'value' => 'Mon-Fri 07:00-17:00, Sat 07:00-12:00', 'group' => 'business'],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }

        // Admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@concreto.co.za',
            'phone' => '+27 00 000 0001',
            'password' => 'password',
            'role' => 'admin',
        ]);

        // Staff user
        User::create([
            'name' => 'Staff User',
            'email' => 'staff@concreto.co.za',
            'phone' => '+27 00 000 0002',
            'password' => 'password',
            'role' => 'staff',
        ]);

        // Driver user
        User::create([
            'name' => 'Driver One',
            'email' => 'driver@concreto.co.za',
            'phone' => '+27 00 000 0003',
            'password' => 'password',
            'role' => 'driver',
        ]);

        // Customer user
        $customerUser = User::create([
            'name' => 'Test Customer',
            'email' => 'customer@concreto.co.za',
            'phone' => '+27 00 000 0004',
            'password' => 'password',
            'role' => 'customer',
        ]);

        $customer = Customer::create([
            'user_id' => $customerUser->id,
            'type' => 'COD',
        ]);

        Address::create([
            'customer_id' => $customer->id,
            'label' => 'Site',
            'line1' => '123 Construction Ave',
            'city' => 'Johannesburg',
            'province' => 'Gauteng',
            'postal_code' => '2001',
            'gps_lat' => -26.2041,
            'gps_lng' => 28.0473,
        ]);

        // Categories
        $sand = Category::create(['name' => 'Sand', 'slug' => 'sand', 'description' => 'Various types of sand for construction', 'sort_order' => 1]);
        $stone = Category::create(['name' => 'Stone', 'slug' => 'stone', 'description' => 'Crushed stone and aggregates', 'sort_order' => 2]);
        $cement = Category::create(['name' => 'Cement & Concrete', 'slug' => 'cement-concrete', 'description' => 'Cement, readymix and concrete products', 'sort_order' => 3]);
        $fill = Category::create(['name' => 'Fill & Topsoil', 'slug' => 'fill-topsoil', 'description' => 'Fill material and topsoil', 'sort_order' => 4]);

        // Products
        Product::create(['name' => 'Plaster Sand', 'slug' => 'plaster-sand', 'category_id' => $sand->id, 'unit' => 'ton', 'price' => 450.00, 'description' => 'Fine plaster sand for plastering and bricklaying.']);
        Product::create(['name' => 'River Sand', 'slug' => 'river-sand', 'category_id' => $sand->id, 'unit' => 'ton', 'price' => 380.00, 'description' => 'Clean river sand for concrete mixing.']);
        Product::create(['name' => 'Building Sand', 'slug' => 'building-sand', 'category_id' => $sand->id, 'unit' => 'ton', 'price' => 350.00, 'description' => 'General purpose building sand.']);
        Product::create(['name' => '19mm Stone', 'slug' => '19mm-stone', 'category_id' => $stone->id, 'unit' => 'ton', 'price' => 420.00, 'description' => '19mm crushed stone for concrete and drainage.']);
        Product::create(['name' => '13mm Stone', 'slug' => '13mm-stone', 'category_id' => $stone->id, 'unit' => 'ton', 'price' => 440.00, 'description' => '13mm crushed stone for fine concrete work.']);
        Product::create(['name' => 'G5 Crusher Run', 'slug' => 'g5-crusher-run', 'category_id' => $stone->id, 'unit' => 'ton', 'price' => 280.00, 'description' => 'G5 base material for roads and foundations.']);
        Product::create(['name' => 'Cement 50kg Bag', 'slug' => 'cement-50kg', 'category_id' => $cement->id, 'unit' => 'bag', 'price' => 95.00, 'description' => '50kg bag of Portland cement.']);
        Product::create(['name' => 'Readymix Concrete', 'slug' => 'readymix', 'category_id' => $cement->id, 'unit' => 'm3', 'price' => 1850.00, 'description' => 'Ready-mixed concrete delivered to site.']);
        Product::create(['name' => 'Topsoil', 'slug' => 'topsoil', 'category_id' => $fill->id, 'unit' => 'ton', 'price' => 250.00, 'description' => 'Quality screened topsoil for gardens and landscaping.']);
        Product::create(['name' => 'Building Rubble Fill', 'slug' => 'rubble-fill', 'category_id' => $fill->id, 'unit' => 'ton', 'price' => 180.00, 'description' => 'Clean fill material for foundations.']);
    }
}
