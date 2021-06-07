<?php

declare(strict_types=1);

use MyParcelNL\Sdk\src\Model\Fulfilment\OrderLine;
use MyParcelNL\Sdk\src\Model\Fulfilment\Product;


class WCMP_SdkOrderItemFromWcOrderLine extends AbstractOrderItemAdapter
{
    public function __construct(?AbstractOrderItemAdapter $originAdapter, WC_Order_Item $wcOrderItem)
    {
        $itemData    = $wcOrderItem->get_data();    // the order row
        $wcProduct   = $wcOrderItem->get_product(); // WC_Product, the original product
        $description = [];

        foreach ($itemData['meta_data'] as $order_item_meta) {
            $description[] = "{$order_item_meta->data['key']}: {$order_item_meta->data['key']}";
        }

        // price per order line is required for api
        $this->price         = (int) ($itemData['subtotal'] * 100.0);
        $this->vat           = (int) ($itemData['subtotal_tax'] * 100.0);
        $this->priceAfterVat = $this->price + $this->vat;
        $this->product       = (new Product())
            ->setDescription(implode(', ', $description))
            //->setEan(null) // GTIN only via plugins, do we want to handle that?
            //->setExternalIdentifier($faker->uuid)
            ->setName($itemData['name'])
            //->setSku($product->get_sku())
            ->setUuid((string) ($itemData['variation_id'] ?: $itemData['product_id']))
            ->setHeight((int) $wcProduct->get_height() ?: 0)
            ->setLength((int) $wcProduct->get_length() ?: 0)
            ->setWeight((int) $wcProduct->get_weight() ?: 0)
            ->setWidth((int) $wcProduct->get_width() ?: 0);

        $this->orderLine = (new OrderLine())
            //->setUuid($faker->uuid)
            //->setInstructions([
            //    'wrapping' => implode(' ', $faker->words(4))
            //])
            ->setQuantity($wcOrderItem->get_quantity())
            ->setPrice($this->price)
            ->setPriceAfterVat($this->priceAfterVat)
            // TODO: After MY-28691 is merged only passing $vat is sufficient.
            ->setVat(0 === $this->vat ? null : $this->vat)
            ->setProduct($this->product);

    }
}

Abstract Class AbstractOrderItemAdapter {
    protected $product;
    protected $orderLine;

    protected $name;
    protected $description;

    protected $price;
    protected $vat;
    protected $priceAfterVat;

    public function getOrderLine(): ?OrderLine
    {
        return $this->orderLine;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

}
