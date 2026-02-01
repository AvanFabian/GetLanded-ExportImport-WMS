import pandas as pd
import random
import string

COUNT = 25000
FILENAME = 'public/stress_test_products.xlsx'

categories = ['Electronics', 'Home & Garden', 'Automotive', 'Clothing', 'Industrial', 'Toys']
units = ['Piece', 'Set', 'Box', 'Kilogram']
hs_codes = ['8517.12', '6203.42', '8708.99', '3926.90', '7318.15', '9403.20']
origins = ['CN', 'US', 'JP', 'DE', 'ID', 'VN']

data = []

print(f"Generating {COUNT} realistic records for XLSX stress test...")

for i in range(COUNT):
    category = random.choice(categories)
    sku_prefix = category[:3].upper()
    sku = f"{sku_prefix}-{str(i).zfill(6)}"
    
    name = f"{category} Item {i} - {random.choice(['Pro', 'Max', 'Lite', 'Standard'])}"
    
    record = {
        'name': name,
        'sku': sku,
        'category_name': category,
        'unit': random.choice(units),
        'purchase_price': round(random.uniform(10, 500), 2),
        'selling_price': round(random.uniform(20, 1000), 2),
        'min_stock': random.randint(5, 50),
        'description': f"Auto-generated description for {sku}",
        'hs_code': random.choice(hs_codes),
        'origin_country': random.choice(origins),
        'weight_unit': 'KG',
        'weight_value': round(random.uniform(0.1, 50.0), 2)
    }
    data.append(record)

df = pd.DataFrame(data)
df.to_excel(FILENAME, index=False, engine='openpyxl')
print(f"Done! 25,000 records saved to {FILENAME}")
