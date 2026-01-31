<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\ImportService;
use App\Models\ImportJob;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MessyImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_cleans_messy_exim_data()
    {
        // 1. Simulate Messy CSV
        $csvContent = "Commody Code,Product Nam,Net Wt (Kg),Cost Price (USD),Sell Price (IDR),Origin,Category,UoM\n";
        $csvContent .= "8471302000,Gaming Laptop,10 lbs,\"$ 1,200.00\",\"Rp. 18.000.000\",China,Electronics,Pieces\n"; 

        Storage::fake('local');
        $path = 'imports/messy.csv';
        Storage::put($path, $csvContent);

        // 2. Setup Job
        $user = User::factory()->create(['company_id' => 1]);
        $this->actingAs($user);

        $job = ImportJob::create([
            'company_id' => 1,
            'user_id' => $user->id,
            'type' => 'products',
            'file_path' => $path,
            'status' => 'mapping',
            'total_rows' => 2,
            'column_mapping' => [
                'hs_code' => 'Commody Code',
                'name' => 'Product Nam',
                'min_stock' => 'Net Wt (Kg)', // Using min_stock as weight proxy
                'purchase_price' => 'Cost Price (USD)',
                'selling_price' => 'Sell Price (IDR)',
                'origin_country' => 'Origin',
                'category' => 'Category',
                'unit' => 'UoM'
            ]
        ]);

        // 3. Process
        $service = new ImportService();
        $service->process($job);

        // 4. Verify Data Cleaning
        $product1 = Product::where('name', 'Gaming Laptop')->first();
        
        // Assert Currency Cleaning worked
        $this->assertEquals(1200, $product1->purchase_price);
        
        // Assert Weight Conversion: 10 lbs -> 4.53592 kg
        $this->assertEquals(4.53592, $product1->min_stock);

        // Assert Unit Normalization: Pieces -> pcs
        $this->assertEquals('pcs', $product1->unit);

        // Assert Category Creation: 'Electronics' should be created and assigned
        $this->assertNotNull($product1->category_id);
        $this->assertEquals('Electronics', $product1->category->name);
    }
}
