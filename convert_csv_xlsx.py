import pandas as pd
import os

files = ['dummy_products', 'dummy_suppliers', 'dummy_customers']
base_dir = 'public'

print("Starting conversion...")
for name in files:
    csv_path = os.path.join(base_dir, f'{name}.csv')
    xlsx_path = os.path.join(base_dir, f'{name}.xlsx')
    
    if os.path.exists(csv_path):
        try:
            df = pd.read_csv(csv_path)
            df.to_excel(xlsx_path, index=False)
            print(f"Converted {name}.csv to {name}.xlsx")
        except Exception as e:
            print(f"Failed to convert {name}: {e}")
    else:
        print(f"File not found: {csv_path}")

print("All done.")
