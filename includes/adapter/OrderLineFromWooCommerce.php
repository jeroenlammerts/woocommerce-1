<?php

declare(strict_types=1);

namespace MyParcelNL\WooCommerce\Includes\Adapter;

use MyParcelNL\Sdk\src\Model\Fulfilment\OrderLine;
use WC_Order_Item;

class OrderLineFromWooCommerce extends OrderLine
{
    public function __construct(WC_Order_Item $wcOrderItem)
    {
        $standardizedDataArray = $this->prepareItemData($wcOrderItem);

        parent::__construct($standardizedDataArray);

    }

    protected function prepareItemData(WC_Order_Item $wcOrderItem): array
    {
        $wcItemData = $wcOrderItem->get_data();

        $data                    = [];
        $price                   = (int) ($wcItemData['subtotal'] * 100.0);
        $vat                     = (int) ($wcItemData['subtotal_tax'] * 100.0);
        $data['price']           = $price;
        $data['vat']             = $vat;
        $data['price_after_vat'] = $price + $vat;
        $data['quantity']        = $wcOrderItem->get_quantity();
        $data['product']         = $this->prepareProductData($wcOrderItem);
        // $data['instructions']
        // $data['uuid']

        return $data;
    }

    protected function prepareProductData(WC_Order_Item $wcOrderItem): array
    {
        $itemData    = $wcOrderItem->get_data();
        $wcProduct   = $wcOrderItem->get_product();

        $data                = [];
        $data['uuid']        = (string) ($itemData['variation_id'] ?: $itemData['product_id']);
        $data['sku']         = $wcProduct->get_sku();
        $data['name']        = $itemData['name'];
        $data['width']       = (int) $wcProduct->get_height() ?: 0;
        $data['length']      = (int) $wcProduct->get_length() ?: 0;
        $data['weight']      = (int) $wcProduct->get_weight() ?: 0;
        $data['width']       = (int) $wcProduct->get_width() ?: 0;
        $data['description'] = $this->makeDescriptionFromMeta($itemData);
        // $data['ean'] // GTIN only via plugins, do we want to handle that?
        // $data['external_identifier']

        return $data;
    }


    protected function makeDescriptionFromMeta(array $itemData): string
    {
        $description = [];

        foreach ($itemData['meta_data'] as $orderItemMeta) {
            $keyValuePair = $orderItemMeta->get_data();

            $description[] = "{$keyValuePair['key']}: {$keyValuePair['value']}";
        }

        return implode(', ', $description);
    }
}