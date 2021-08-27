<?php

namespace App\Exports\Products\ThirdParty;

use App\Exports\ExportInterface;
use App\Models\Product;
use Illuminate\Support\Facades\URL;

class Heureka extends ThirdPartyExport implements ExportInterface
{
    protected string $start_of_file = '<?xml version="1.0" encoding="utf-8"?><SHOP>';
    protected string $end_of_file = '</SHOP>';

    /**
     * @return void
     */
    public function export(): void
    {
        $this->prepareFile('Heureka-' . $this->export_option->language . '.xml');

        $this->storage->append($this->file_name, $this->createContent());

        if ($this->is_last) {
            $this->storage->append($this->file_name, $this->end_of_file);
        }
    }

    /**
     * @return string
     */
    private function createContent(): string
    {
        $products = Product::skip($this->offset)->take($this->take)->get();

        $content = '';

        foreach ($products as $product) {
            $content .= $this->createContentItem($product);
        }

        return $content;
    }

    /**
     * @param Product $product
     *
     * @return string
     */
    private function createContentItem(Product $product): string
    {
        $item = new \SimpleXMLElement('<CHILD></CHILD>');

        $item->addChild('ITEM_ID', $product->id);
        $item->addChild('PRODUCTNAME', htmlspecialchars($product->name));
        $item->addChild('PRODUCT', htmlspecialchars($product->name));
        $item->addChild('DESCRIPTION', '<p>' . htmlspecialchars($product->short_description) . '</p>');

        // TODO: otazka
        if ($product->categories->first() !== null) {
            $item->addChild('URL', URL::route('product.show', ['slug' => $product->categories->first()->slug, 'prod' => $product->id . '-' . $product->slug]));
        }

        $item->addChild('IMGURL', URL::to($product->image));

        foreach ($product->images as $image) {
            $item->addChild('IMGURL_ALTERNATIVE', URL::to($image));
        }

        $item->addChild('PRICE_VAT', round($product->retail_price_with_iva * ((100 - $product->discount) / 100)), 2);

        /*$params = $item->addChild('PARAM');

        foreach($product->variants as $variant) {
            $variantValue = $variant->values()->first() ? $variant->values()->first()->value : '';
            $variantCount = $variant->quantity;
            $params->addChild('PARAM_NAME','VEĽKOSŤ');
            $params->addChild('VAL',$variantValue);
        }*/

        $item->addChild('MANUFACTURER', $product->suppliers->first()->name);

        $categories = '';
        $iterator = 1;

        foreach ($product->categories as $category) {
            $categories .= $category;

            if ($iterator < count($product->categories)) {
                $categories .= ' | ';
            }

            $iterator++;
        }

        $item->addChild('CATEGORYTEXT', $categories);

        if ($product->quantity > 0) {
            $item->addChild('DELIVERY_DATE', 3);
        } else {
            $item->addChild('DELIVERY_DATE', 0);
        }

        $delivery = $item->addChild('DELIVERY');
        $delivery->addChild('DELIVERY_ID', 'GLS');
        $delivery->addChild('DELIVERY_PRICE', '5.9');
        $delivery->addChild('DELIVERY_PRICE_COD', '7.4');

        $item_content = '';

        foreach ($item->children() as $child) {
            $item_content .= $child;
        }

        return $item_content;
    }
}
