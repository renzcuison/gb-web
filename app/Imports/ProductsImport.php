<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductVariant;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProductsImport implements WithMultipleSheets, ToCollection, WithCalculatedFormulas
{
    public int $productsProcessed = 0;
    public int $variantsProcessed = 0;
    public int $rowsSkipped = 0;

    /**
     * Tracks SKUs already seen during this upload so true multi-batch restocks
     * of the exact same product + variant + price tier sum their quantities.
     *
     * @var array<string, bool>
     */
    public array $processedSkusInRun = [];

    protected array $sheetNames = [];
    protected float $cadToPhpRate;
    protected ?int $userId;

    public function __construct(float $cadToPhpRate = 41.50, ?int $userId = null)
    {
        $this->cadToPhpRate = $cadToPhpRate > 0 ? $cadToPhpRate : 41.50;
        $this->userId = $userId
            ?? auth()->id()
            ?? User::where('role', 'admin')->value('id')
            ?? User::value('id');
    }

    public function collection(Collection $rows): void
    {
    }

    public function registerSheetsFromFile(string $filePath): self
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $this->sheetNames = $reader->listWorksheetNames($filePath);

        return $this;
    }

    public function sheets(): array
    {
        if (empty($this->sheetNames)) {
            return [0 => new WorksheetProductsImport($this, 'Sheet1')];
        }

        $handlers = [];
        foreach ($this->sheetNames as $name) {
            $handlers[$name] = new WorksheetProductsImport($this, $name);
        }

        return $handlers;
    }

    public function getCadToPhpRate(): float
    {
        return $this->cadToPhpRate;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }
}

class WorksheetProductsImport implements ToCollection, WithCalculatedFormulas
{
    private const BRAND_ALIASES = [
        'bbw'                  => 'Bath & Body Works',
        'bath & body works'    => 'Bath & Body Works',
        'vs'                   => "Victoria's Secret",
        "victoria's secret"    => "Victoria's Secret",
        'victorias secret'     => "Victoria's Secret",
        'mk'                   => 'Michael Kors',
        'michael kors'         => 'Michael Kors',
        'micheal kors'         => 'Michael Kors',
        'karl lagerfeld'       => 'Karl Lagerfeld',
        'karl legerfeld'       => 'Karl Lagerfeld',
        'karl largerfeld'      => 'Karl Lagerfeld',
        'under armour'         => 'Under Armour',
        'underarmuour'         => 'Under Armour',
        'underarmour'          => 'Under Armour',
        'oakley'               => 'Oakley',
        'oackley'              => 'Oakley',
        "levi's"               => "Levi's",
        'levis'                => "Levi's",
        'u.s. polo assn.'      => 'U.S. Polo Assn.',
        'u.s polo assn.'       => 'U.S. Polo Assn.',
        'us polo assn'         => 'U.S. Polo Assn.',
        'polo'                 => 'Polo',
        'tommy hilfiger'       => 'Tommy Hilfiger',
        'fossil'               => 'Fossil',
        'swatch'               => 'Swatch',
        'guess'                => 'Guess',
        'dockers'              => 'Dockers',
        'coach'                => 'Coach',
        'herschel'             => 'Herschel',
        'carhartt'             => 'Carhartt',
        'gap'                  => 'Gap',
        'babygap'              => 'Gap',
        'gapfit'               => 'Gap',
        'hurley'               => 'Hurley',
        'champion'             => 'Champion',
        'adidas'               => 'Adidas',
        'nike'                 => 'Nike',
        'puma'                 => 'Puma',
        'bass pro shop'        => 'Bass Pro Shop',
        'steve madden'         => 'Steve Madden',
        'lacoste'              => 'Lacoste',
        'anne klein'           => 'Anne Klein',
        'jessica simpson'      => 'Jessica Simpson',
        'bench'                => 'Bench',
        'corelle'              => 'Corelle',
        'thermoflask'          => 'Thermoflask',
        'cetaphil'             => 'Cetaphil',
        'old spice'            => 'Old Spice',
        'jamieson'             => 'Jamieson',
        'nutella'              => 'Nutella',
        'pink sugar'           => 'Pink Sugar',
        'juicy couture'        => 'Juicy Couture',
        'bcbgmaxazria'         => 'BCBGMAXAZRIA',
        'vera wang'            => 'Vera Wang',
        'ck'                   => 'Calvin Klein',
        'calvin klein'         => 'Calvin Klein',
        'baby gear'            => 'Baby Gear',
        'always be ready'      => 'Always Be Ready',
        't-fal'                => 'T-fal',
        'tfal'                 => 'T-fal',
        'old navy'             => 'Old Navy',
        'pekkle'               => 'Pekkle',
        'joe fresh'            => 'Joe Fresh',
        "carter's"             => "Carter's",
        'carters'              => "Carter's",
        'ralph lauren'         => 'Ralph Lauren',
        'zara'                 => 'Zara',
        'disney'               => 'Disney',
        'fisher price'         => 'Fisher-Price',
        'versace'              => 'Versace',
        'bluenotes'            => 'Bluenotes',
    ];

    private const WORD_CORRECTIONS = [
        '/\bfairtale\b/i'                   => 'Fairytale',
        '/\bshampion\b/i'                   => 'Shampoo',
        '/\beyesglasses\b/i'                => 'Eyeglasses',
        '/\bplian\b/i'                      => 'Plain',
        '/\btriangke\b/i'                   => 'Triangle',
        '/\bsprot\b/i'                      => 'Sport',
        '/\bhealther\b/i'                   => 'Heather',
        '/\bbiege\b/i'                      => 'Beige',
        '/\bsliver\b/i'                     => 'Silver',
        '/\byoutheory\s+390\s+tables\b/i'   => 'Youtheory Collagen 390 Tablets',
    ];

    public function __construct(
        protected ProductsImport $parent,
        protected string $sheetTitle
    ) {}

    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            return;
        }

        $firstRowValues = array_map(fn ($v) => strtolower(trim((string) $v)), $rows->first()->toArray());
        if (in_array('product name', $firstRowValues, true) || in_array('selling price (php)', $firstRowValues, true)) {
            $this->importFlatTemplate($rows, $firstRowValues);
            return;
        }

        $layout = $this->detectMonthlyReportLayout($rows);
        $isKidsSheet = stripos($this->sheetTitle, 'kids') !== false;
        $currentBatchEta = null;
        $activeKidsBundle = null;

        DB::transaction(function () use ($rows, $layout, $isKidsSheet, &$currentBatchEta, &$activeKidsBundle) {
            foreach ($rows as $rowIndex => $rowCollection) {
                $row = $rowCollection->toArray();
                $rawName = trim((string) ($row[0] ?? ''));

                if ($rawName === '') {
                    continue;
                }

                if ($this->isBatchHeader($rawName)) {
                    $currentBatchEta = preg_replace('/bacth/i', 'Batch', $rawName);
                    $activeKidsBundle = null;
                    continue;
                }

                if ($this->isHeaderOrTotalRow($rawName, $row, $layout)) {
                    continue;
                }

                $rawCostCad = $layout['cost'] !== null ? ($row[$layout['cost']] ?? null) : null;
                $rawSku     = trim((string) ($row[$layout['sku']] ?? ''));
                $rawPrice   = $row[$layout['price']] ?? null;
                $rawInitQty = $layout['init'] !== null ? ($row[$layout['init']] ?? null) : null;
                $rawStock   = $row[$layout['stock']] ?? null;
                $rawFlag    = $layout['flag'] !== null ? trim((string) ($row[$layout['flag']] ?? '')) : '';

                [$cleanText, $internalNotes] = $this->extractNotes($rawName);
                $cleanText = $this->normalizeTypos($cleanText);

                [$brandName, $titleWithoutBrand] = $this->extractBrand($cleanText);

                $isDecantSheet = stripos($this->sheetTitle, 'decant') !== false;
                [$baseTitle, $variantLabel] = $this->extractVariant($titleWithoutBrand, $isDecantSheet);

                if ($baseTitle === '') {
                    $baseTitle = $titleWithoutBrand !== '' ? $titleWithoutBrand : $cleanText;
                }

                [$categoryName, $tagName] = $this->classifyCategoryAndTag($this->sheetTitle, $cleanText, $rawFlag);

                [$sellingPricePhp, $promoNote] = $this->parseSellingPrice($rawPrice);
                if ($isKidsSheet) {
                    if ($promoNote !== null) {
                        $activeKidsBundle = ['price' => $sellingPricePhp, 'note' => $promoNote];
                    } elseif ($sellingPricePhp > 0) {
                        $activeKidsBundle = null;
                    } elseif ($sellingPricePhp <= 0 && $activeKidsBundle !== null) {
                        $sellingPricePhp = $activeKidsBundle['price'];
                        $promoNote = $activeKidsBundle['note'] . ' (lot item)';
                    }
                }

                $costPricePhp = $this->parseCostPricePhp($rawCostCad);

                $rawStockStr = trim((string) $rawStock);
                $stockQty = str_starts_with($rawStockStr, '=')
                    ? 0
                    : max(0, (int) preg_replace('/[^\d\-]/', '', $rawStockStr));

                $initQty = max(0, (int) preg_replace('/[^\d]/', '', (string) $rawInitQty));

                $isActive = $sellingPricePhp > 0;
                if ($sellingPricePhp <= 0 && $stockQty > 0) {
                    $internalNotes[] = 'Missing selling price in sheet';
                }

                $category = Category::firstOrCreate(
                    ['slug' => Str::slug($categoryName)],
                    ['name' => $categoryName]
                );

                $brand = Brand::firstOrCreate(
                    ['slug' => Str::slug($brandName)],
                    ['name' => $brandName]
                );

                $displayTitle = $brandName !== 'Unbranded' && !str_starts_with(strtolower($baseTitle), strtolower($brandName))
                    ? "{$brandName} {$baseTitle}"
                    : $baseTitle;

                $priceTierSuffix = '';
                if ($sellingPricePhp > 0) {
                    $existingSameSlug = Product::withTrashed()
                        ->where('slug', Str::slug($displayTitle))
                        ->first();

                    if ($existingSameSlug && abs((float) $existingSameSlug->price - $sellingPricePhp) > 0.01) {
                        $existingHasStock = $existingSameSlug->variants()->sum('stock_qty') > 0;
                        if ($existingHasStock && $stockQty > 0) {
                            $priceTierSuffix = '-' . (int) round($sellingPricePhp);
                        }
                    }
                }

                $productSlug = Str::slug($displayTitle) . $priceTierSuffix;
                if ($productSlug === '' || $productSlug === $priceTierSuffix) {
                    $productSlug = 'item-' . substr(md5($rawName . $rowIndex), 0, 10);
                }

                $existingProduct = Product::withTrashed()->where('slug', $productSlug)->first();

                $product = Product::withTrashed()->updateOrCreate(
                    ['slug' => $productSlug],
                    [
                        'category_id' => $category->id,
                        'brand_id'    => $brand->id,
                        'name'        => $displayTitle,
                        'cost_price'  => $costPricePhp > 0 ? $costPricePhp : ($existingProduct?->cost_price ?? 0.00),
                        'price'       => $sellingPricePhp > 0 ? $sellingPricePhp : ($existingProduct?->price ?? 0.00),
                        'is_active'   => $isActive || (bool) ($existingProduct?->is_active ?? false),
                        'deleted_at'  => null,
                    ]
                );

                if ($tagName !== null) {
                    $tag = Tag::firstOrCreate(
                        ['slug' => Str::slug($tagName)],
                        ['name' => $tagName]
                    );
                    $product->tags()->syncWithoutDetaching([$tag->id]);
                }

                $sku = $this->resolveUniqueVariantSku($product, $rawSku, $variantLabel, $productSlug);

                $existingVariant = ProductVariant::withTrashed()
                    ->where('sku', $sku)
                    ->first();

                $alreadySeenInThisUpload = isset($this->parent->processedSkusInRun[$sku]);
                $this->parent->processedSkusInRun[$sku] = true;

                $oldStock = $existingVariant ? (int) $existingVariant->stock_qty : 0;
                $targetStock = $alreadySeenInThisUpload ? ($oldStock + $stockQty) : $stockQty;

                $defaultThreshold = ($initQty >= 3 || $targetStock >= 3) ? 2 : 0;

                $variant = ProductVariant::withTrashed()->updateOrCreate(
                    ['sku' => $sku],
                    [
                        'product_id'          => $product->id,
                        'variant_label'       => $variantLabel,
                        'price_override'      => null,
                        'stock_qty'           => $targetStock,
                        'low_stock_threshold' => $existingVariant?->low_stock_threshold ?? $defaultThreshold,
                        'deleted_at'          => null,
                    ]
                );

                if (!$existingVariant && $targetStock > 0 && $this->parent->getUserId()) {
                    InventoryMovement::create([
                        'variant_id' => $variant->id,
                        'user_id'    => $this->parent->getUserId(),
                        'quantity'   => $targetStock,
                        'type'       => 'in',
                        'reason'     => "Initial Excel Import ({$this->sheetTitle})",
                    ]);
                } elseif ($existingVariant && $targetStock !== $oldStock && $this->parent->getUserId()) {
                    $diff = $targetStock - $oldStock;
                    InventoryMovement::create([
                        'variant_id' => $variant->id,
                        'user_id'    => $this->parent->getUserId(),
                        'quantity'   => abs($diff),
                        'type'       => $diff > 0 ? 'in' : 'out',
                        'reason'     => $alreadySeenInThisUpload
                            ? "Additional Batch Import ({$this->sheetTitle})"
                            : "Excel Inventory Sync ({$this->sheetTitle})",
                    ]);
                }

                $this->syncAttribute($product, 'Source Sheet', $this->sheetTitle);

                if ($currentBatchEta) {
                    $this->syncAttribute($product, 'Batch ETA', $currentBatchEta);
                }
                if ($rawCostCad !== null && is_numeric($rawCostCad) && (float) $rawCostCad > 0) {
                    $this->syncAttribute($product, 'Supplier Cost (CAD)', '$' . number_format((float) $rawCostCad, 2));
                }
                if (!empty($internalNotes)) {
                    $this->syncAttribute($product, 'Admin Note', implode('; ', array_unique($internalNotes)));
                }
                if ($rawFlag !== '') {
                    $this->syncAttribute($product, 'Status Flag', $rawFlag);
                }
                if ($promoNote !== null) {
                    $this->syncAttribute($product, 'Promo Pricing', $promoNote);
                }

                $this->parent->productsProcessed++;
                if (!$alreadySeenInThisUpload) {
                    $this->parent->variantsProcessed++;
                }
            }
        });
    }

    private function detectMonthlyReportLayout(Collection $rows): array
    {
        $titleLower = strtolower(trim($this->sheetTitle));

        if (str_contains($titleLower, 'decant')) {
            return ['cost' => null, 'sku' => 1, 'price' => 2, 'init' => 3, 'stock' => 5, 'flag' => null];
        }

        if (str_contains($titleLower, 'bbw') || str_contains($titleLower, 'vs')) {
            return ['cost' => null, 'sku' => 2, 'price' => 3, 'init' => 4, 'stock' => 6, 'flag' => 7];
        }

        $row0 = $rows->get(0)?->toArray() ?? [];
        $colBHeader = strtolower(trim((string) ($row0[1] ?? '')));
        $colCHeader = strtolower(trim((string) ($row0[2] ?? '')));

        if ($colBHeader === '' && str_contains($colCHeader, 'stock')) {
            return ['cost' => null, 'sku' => 2, 'price' => 3, 'init' => 4, 'stock' => 6, 'flag' => 7];
        }

        return ['cost' => 1, 'sku' => 2, 'price' => 3, 'init' => 4, 'stock' => 6, 'flag' => 7];
    }

    private function isBatchHeader(string $text): bool
    {
        return (bool) preg_match('/\b(batch|bacth)\s*eta\b/i', $text)
            || (bool) preg_match('/\bbatch\s+20\d{2}\b/i', $text)
            || (bool) preg_match('/^\d{1,2}-\d{4}$/', $text);
    }

    private function isHeaderOrTotalRow(string $rawName, array $row, array $layout): bool
    {
        if (preg_match('/^(item|product|description|stock\s*no|grand\s*total|total)\b/i', $rawName)) {
            return true;
        }

        $priceCell = strtolower(trim((string) ($row[$layout['price']] ?? '')));
        return in_array($priceCell, ['amount', 'price', 'selling price'], true);
    }

    private function extractNotes(string $raw): array
    {
        $notes = [];
        $cleaned = preg_replace_callback('/\(([^)]+)\)/', function ($matches) use (&$notes) {
            $note = trim($matches[1]);
            if ($note !== '') {
                $notes[] = $note;
            }
            return ' ';
        }, $raw);

        if (!empty($notes)) {
            $cleaned = preg_replace('/\s+\d{1,3}$/', '', $cleaned);
        }

        $cleaned = trim(preg_replace('/\s+/', ' ', $cleaned));

        return [$cleaned, $notes];
    }

    private function normalizeTypos(string $text): string
    {
        foreach (self::WORD_CORRECTIONS as $pattern => $replacement) {
            $text = preg_replace($pattern, $replacement, $text);
        }

        return trim(preg_replace('/\s+/', ' ', $text));
    }

    private function extractBrand(string $text): array
    {
        $lower = strtolower($text);

        if (str_contains($lower, 'juicy couture')) {
            return ['Juicy Couture', $text];
        }

        $aliases = self::BRAND_ALIASES;
        uksort($aliases, fn ($a, $b) => strlen($b) <=> strlen($a));

        foreach ($aliases as $alias => $canonical) {
            $pattern = '/^' . preg_quote($alias, '/') . '(?:\b|\s+|(?=[A-Z]))/i';
            if (preg_match($pattern, $text, $matches)) {
                $remainder = trim(substr($text, strlen($matches[0])));
                $remainder = ltrim($remainder, ".-/ ");
                return [$canonical, $remainder !== '' ? $remainder : $text];
            }
        }

        return ['Unbranded', $text];
    }

    private function extractVariant(string $title, bool $isDecantSheet): array
    {
        if ($isDecantSheet) {
            return [$title, '10ML Decant'];
        }

        $patterns = [
            '/\b(?:size|us)\s*(\d+(?:\.\d+)?)\b/i',
            '/\b(\d{1,2}-\d{1,2}\s*(?:XXS|XS|Small|Medium|Large|XL|XXL)|(?:XXS|XS|Small|Medium|Large|XL|XXL)\s*\d{1,2}-\d{1,2}|\d+T)\b/i',
            '/\b(XXS|XS|Small|Medium|Large|XL|XXL)\b/i',
            '/\b(\d+(?:\.\d+)?\s*(?:ML|L|G))\b/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $title, $match)) {
                $variantLabel = trim($match[1]);
                if (preg_match('/^(?:size|us)\s*/i', $match[0])) {
                    $variantLabel = 'Size ' . trim($match[1]);
                }

                $stripped = trim(preg_replace('/\s+/', ' ', str_replace($match[0], ' ', $title)));
                return [$stripped !== '' ? $stripped : $title, $variantLabel];
            }
        }

        return [$title, 'Standard'];
    }

    private function classifyCategoryAndTag(string $sheetTitle, string $itemName, string $flag): array
    {
        $sheetLower = strtolower(trim($sheetTitle));
        $itemLower  = strtolower($itemName);
        $flagLower  = strtolower($flag);

        if (
            str_contains($flagLower, 'expir') ||
            preg_match('/\b(nutella|jamieson|vitamin|zinc|supplement|coffee|chocolate|snack|gummies)\b/i', $itemLower)
        ) {
            return ['Pantry & Wellness', null];
        }

        if (str_contains($sheetLower, 'footwear')) {
            return ['Footwear', null];
        }
        if (str_contains($sheetLower, 'cap')) {
            return ['Caps', null];
        }
        if (str_contains($sheetLower, 'wallet')) {
            return ['Wallets', null];
        }
        if (str_contains($sheetLower, 'watch')) {
            return ['Watches', null];
        }
        if (str_contains($sheetLower, 'bag')) {
            return ['Bags', null];
        }
        if (str_contains($sheetLower, 'perfume')) {
            return ['Perfumes & Mists', null];
        }
        if (str_contains($sheetLower, 'decant')) {
            return ['Decants', null];
        }
        if (str_contains($sheetLower, 'bbw') || str_contains($sheetLower, 'vs')) {
            return str_contains($itemLower, 'lotion')
                ? ['Lotions & Body Care', null]
                : ['Perfumes & Mists', null];
        }
        if (str_contains($sheetLower, 'kids')) {
            $cat = str_contains($itemLower, 'cap') ? 'Caps' : 'Clothing';
            return [$cat, 'Kids'];
        }
        if (str_contains($sheetLower, 'teens')) {
            return ['Clothing', 'Teens'];
        }
        if ($sheetLower === 'sheet1') {
            return [str_contains($itemLower, 'cap') ? 'Caps' : 'Clothing', null];
        }

        if (preg_match('/\b(corelle|plate|dinner|bowl|mug|frying|pan|cookware|pot|skillet)\b/i', $itemLower)) {
            return ['Cookware & Dinnerware', null];
        }
        if (preg_match('/\b(thermoflask|flask|tumbler|bottle)\b/i', $itemLower)) {
            return ['Drinkware', null];
        }
        if (preg_match('/\b(towel|kitchen|home|pillow|blanket)\b/i', $itemLower)) {
            return ['Kitchen & Home', null];
        }
        if (preg_match('/\b(cetaphil|lotion|deodorant|old spice|soap|wash|cream|skincare)\b/i', $itemLower)) {
            return ['Lotions & Body Care', null];
        }
        if (preg_match('/\b(perfume|mist|cologne|fragrance)\b/i', $itemLower)) {
            return ['Perfumes & Mists', null];
        }
        if (preg_match('/\b(belt|wallet|watch|bag|sunglass|eyewear)\b/i', $itemLower)) {
            return ['Accessories', null];
        }
        if (preg_match('/\b(shoe|sneaker|slipper|sandal|boot)\b/i', $itemLower)) {
            return ['Footwear', null];
        }
        if (preg_match('/\b(cap|hat|beanie)\b/i', $itemLower)) {
            return ['Caps', null];
        }
        if (preg_match('/\b(shirt|hoodie|legging|short|sando|tank|jacket|dress|pants)\b/i', $itemLower)) {
            return ['Clothing', null];
        }

        return ['General Imports', null];
    }

    private function parseSellingPrice(mixed $raw): array
    {
        if ($raw === null) {
            return [0.00, null];
        }

        $str = trim((string) $raw);
        if ($str === '') {
            return [0.00, null];
        }

        if (preg_match('/^(\d+)\s*for\s*(\d+(?:\.\d+)?)$/i', $str, $m)) {
            $qty = max(1, (int) $m[1]);
            $bundleTotal = (float) $m[2];
            return [round($bundleTotal / $qty, 2), $str];
        }

        $numeric = preg_replace('/[^0-9.]/', '', $str);
        return [$numeric !== '' ? round((float) $numeric, 2) : 0.00, null];
    }

    private function parseCostPricePhp(mixed $rawCad): float
    {
        if ($rawCad === null || trim((string) $rawCad) === '') {
            return 0.00;
        }

        $numeric = preg_replace('/[^0-9.]/', '', (string) $rawCad);
        if ($numeric === '' || (float) $numeric <= 0) {
            return 0.00;
        }

        return round((float) $numeric * $this->parent->getCadToPhpRate(), 2);
    }

    private function resolveUniqueVariantSku(Product $product, string $rawSku, string $variantLabel, string $productSlug): string
    {
        $existingForProduct = ProductVariant::withTrashed()
            ->where('product_id', $product->id)
            ->where('variant_label', $variantLabel)
            ->first();

        if ($existingForProduct) {
            return $existingForProduct->sku;
        }

        $cleanRawSku = preg_replace('/[^A-Z0-9\-]/', '-', strtoupper(trim($rawSku)));
        $cleanRawSku = trim(preg_replace('/-+/', '-', $cleanRawSku), '-');

        $variantSuffix = $variantLabel !== 'Standard'
            ? '-' . strtoupper(Str::slug($variantLabel))
            : '';

        if ($cleanRawSku !== '') {
            $candidate = $cleanRawSku . $variantSuffix;
            $claimedByOtherProduct = ProductVariant::withTrashed()
                ->where('sku', $candidate)
                ->where('product_id', '!=', $product->id)
                ->exists();

            return $claimedByOtherProduct
                ? Str::limit($candidate, 240, '') . '-' . strtoupper(substr(md5($productSlug), 0, 4))
                : Str::limit($candidate, 250, '');
        }

        $fullBase = 'GBO-' . strtoupper($productSlug) . $variantSuffix;
        return Str::limit($fullBase, 250, '');
    }

    private function syncAttribute(Product $product, string $name, string $value): void
    {
        ProductAttribute::updateOrCreate(
            ['product_id' => $product->id, 'name' => $name],
            ['value' => Str::limit($value, 250)]
        );
    }

    private function importFlatTemplate(Collection $rows, array $headers): void
    {
        $idx = array_flip($headers);

        DB::transaction(function () use ($rows, $idx) {
            foreach ($rows->slice(1) as $rowCollection) {
                $row = $rowCollection->toArray();
                $name = trim((string) ($row[$idx['product name'] ?? $idx['name'] ?? 1] ?? ''));
                if ($name === '') {
                    continue;
                }

                $catName   = trim((string) ($row[$idx['category'] ?? 3] ?? '')) ?: 'General Imports';
                $brandName = trim((string) ($row[$idx['brand'] ?? 4] ?? '')) ?: 'Unbranded';
                $sku       = trim((string) ($row[$idx['sku'] ?? 0] ?? ''));
                $variant   = trim((string) ($row[$idx['variant'] ?? $idx['variant_label'] ?? 2] ?? '')) ?: 'Standard';
                $costPhp   = (float) preg_replace('/[^0-9.]/', '', (string) ($row[$idx['cost price (php)'] ?? $idx['cost_price'] ?? 5] ?? '0'));
                $sellPhp   = (float) preg_replace('/[^0-9.]/', '', (string) ($row[$idx['selling price (php)'] ?? $idx['price'] ?? 6] ?? '0'));
                $stockQty  = max(0, (int) ($row[$idx['stock qty'] ?? $idx['stock'] ?? 7] ?? 0));

                $category = Category::firstOrCreate(['slug' => Str::slug($catName)], ['name' => $catName]);
                $brand    = Brand::firstOrCreate(['slug' => Str::slug($brandName)], ['name' => $brandName]);
                $slug     = Str::slug($name);

                $product = Product::withTrashed()->updateOrCreate(
                    ['slug' => $slug],
                    [
                        'category_id' => $category->id,
                        'brand_id'    => $brand->id,
                        'name'        => $name,
                        'cost_price'  => $costPhp,
                        'price'       => $sellPhp,
                        'is_active'   => $sellPhp > 0,
                        'deleted_at'  => null,
                    ]
                );

                $resolvedSku = $this->resolveUniqueVariantSku($product, $sku, $variant, $slug);
                $existing = ProductVariant::withTrashed()->where('sku', $resolvedSku)->first();
                $oldStock = $existing ? (int) $existing->stock_qty : 0;

                $savedVariant = ProductVariant::withTrashed()->updateOrCreate(
                    ['sku' => $resolvedSku],
                    [
                        'product_id'    => $product->id,
                        'variant_label' => $variant,
                        'stock_qty'     => $stockQty,
                        'deleted_at'    => null,
                    ]
                );

                if (!$existing && $stockQty > 0 && $this->parent->getUserId()) {
                    InventoryMovement::create([
                        'variant_id' => $savedVariant->id,
                        'user_id'    => $this->parent->getUserId(),
                        'quantity'   => $stockQty,
                        'type'       => 'in',
                        'reason'     => 'Excel Import',
                    ]);
                } elseif ($existing && $stockQty !== $oldStock && $this->parent->getUserId()) {
                    $diff = $stockQty - $oldStock;
                    InventoryMovement::create([
                        'variant_id' => $savedVariant->id,
                        'user_id'    => $this->parent->getUserId(),
                        'quantity'   => abs($diff),
                        'type'       => $diff > 0 ? 'in' : 'out',
                        'reason'     => 'Excel Inventory Sync',
                    ]);
                }

                $this->parent->productsProcessed++;
                $this->parent->variantsProcessed++;
            }
        });
    }
}