<?php
/**
 * Created by PhpStorm.
 * @author Villatheme
 * Created at 8/19/16 5:47 PM UTC+06:00
 *
 * @see https://help.shopify.com/api/reference/location Shopify API Reference for Location
 */

namespace PHPShopify;

/**
 * --------------------------------------------------------------------------
 * Location -> Child Resources
 * --------------------------------------------------------------------------
 * @property-read Metafield $Metafield
 *
 * @method Metafield Metafield(integer $id = null)
 */

class Location extends ShopifyResource
{
    /**
     * @inheritDoc
     */
    protected $resourceKey = 'location';

    /**
     * @inheritDoc
     */
    public $countEnabled = false;

    /**
     * @inheritDoc
     */
    public $readOnly = true;

    /**
     * @inheritDoc
     */
    protected $childResource = array(
        'InventoryLevel',
        'Metafield',
    );
}
