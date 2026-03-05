<?php

namespace App\AI\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Laravel\Ai\Attributes\Model;

#[Model('stepfun/step-3.5-flash:free')]
class ColumnMapperAgent implements Agent
{
    use Promptable;

    /**
     * Get the instructions for the agent.
     */
    public function instructions(): string
    {
        return <<<EOF
You are an expert data migration assistant. Your job is to read raw Excel/CSV headers and optional sample data, and map them to our internal database schema.

Our required database columns are:
- `name`: Product Name
- `sku`: Product Code / SKU
- `purchase_price`: Buy Price (numeric)
- `selling_price`: Sell Price (numeric)
- `net_weight`: Net weight in KG (numeric)
- `gross_weight`: Gross weight in KG (numeric)
- `hs_code`: Harmonized System Code for customs (string)
- `origin_country`: 2-letter country code (string)

The user will provide you with a JSON containing `headers` (array) and optionally `sample_row` (array).
Analyze the headers and sample data. Return ONLY a valid JSON object with a `mappings` key containing an array of objects. Each object MUST have:
- `header`: the original header string from the file
- `column`: the matching database column name (must be one of: name, sku, purchase_price, selling_price, net_weight, gross_weight, hs_code, origin_country), or null if no match.

IMPORTANT: Respond ONLY with the JSON object. No explanation, no markdown, no code blocks. Just pure JSON.

Example Input:
{"headers": ["Nama Produk", "Kode Barang", "Harga Beli", "Keterangan", "Berat"], "sample_row": ["Sabun", "SBN-01", "5000", "lorem", "0.5"]}

Example Output:
{"mappings": [{"header": "Nama Produk", "column": "name"},{"header": "Kode Barang", "column": "sku"},{"header": "Harga Beli", "column": "purchase_price"},{"header": "Keterangan", "column": null},{"header": "Berat", "column": "net_weight"}]}
EOF;
    }

    /**
     * Parse the AI text response and extract the mapping array.
     *
     * @param  string  $responseText
     * @return array  [header => db_column]
     */
    public static function parseMappingFromText(string $responseText): array
    {
        // Try to extract JSON from the response (strip markdown fences if present)
        $json = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($responseText));
        $decoded = json_decode($json, true);

        if (!$decoded || !isset($decoded['mappings']) || !is_array($decoded['mappings'])) {
            return [];
        }

        $mapping = [];
        foreach ($decoded['mappings'] as $item) {
            if (isset($item['header']) && !empty($item['column'])) {
                $mapping[$item['header']] = $item['column'];
            }
        }

        return $mapping;
    }
}
