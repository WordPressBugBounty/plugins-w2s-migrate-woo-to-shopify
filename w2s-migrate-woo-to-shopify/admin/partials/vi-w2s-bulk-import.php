<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Viw2s_Bulk_Import {
	public static $instance = null;
	public static $store_name = null;
	public $settings = null;
	public $store_settings = null;
	public $api_url = '';
	public $access_token = '';
	public static $graphql_version = '2025-01';
	public $rate = null;
	public $inventory_location = '';
	public $product_weight_unit = 'kg';

	public $currency_code = 'USD';

	public function __construct( $store_name = '' ) {
		$this->settings = new VI_W2S_IMPORT_WOOCOMMERCE_TO_SHOPIFY_DATA();
		if ( ! $store_name ) {
			return;
		}
		$this->setup( $store_name );
	}

	public static function instance( $store_name ) {
		if ( is_null( self::$instance ) || self::$store_name !== $store_name ) {
			self::$instance = new self( $store_name );
		}

		return self::$instance;
	}

	public function setup( $store_name ) {
		$this->store_settings      = $this->settings->get_setting_by_store_name( $store_name );
		$domain                    = $this->store_settings['domain'] ?? '';
		$api_secret                = $this->store_settings['api_secret'] ?? '';
		$this->api_url             = sprintf( "https://%s/admin/api/%s/graphql.json", $domain, self::$graphql_version );
		$this->access_token        = $api_secret;
		self::$store_name          = $store_name;
		$this->product_weight_unit = get_option( 'woocommerce_weight_unit' );
		$this->get_rate_limit();
	}

	public function query( $query, $variables = null ) {
		$response = wp_remote_post( $this->api_url, [
			'headers' => [
				'Content-Type'           => 'application/json',
				'X-Shopify-Access-Token' => $this->access_token,
			],
			'body'    => json_encode( [
				'query'     => $query,
				'variables' => $variables,
			] ),
		] );

		if ( is_wp_error( $response ) ) {
			return [ 'error' => $response->get_error_message() ];
		}

		$body = wp_remote_retrieve_body( $response );

		return json_decode( $body, true );
	}

	public function get_rate_limit() {
		$query  = <<<QUERY
		query {
	        location {
	            id
	        }
	        shop {
	            currencyCode
	        }
        }
QUERY;
		$result = $this->query( $query );
		$return = array(
			'cost' => '',
			'rate' => [
				'product' => 1,
			]
		);

		if ( isset( $result['data']['location']['id'] ) ) {
			$this->inventory_location = apply_filters( 'viw2s_shopify_inventory_location', $result['data']['location']['id'] );
		}

		if ( isset( $result['extensions']['cost']['throttleStatus'] ) ) {
			$return['cost'] = $result['extensions']['cost']['throttleStatus'];
		}

		if ( isset( $result['data']['shop']['currencyCode'] ) ) {
			$this->currency_code = $result['data']['shop']['currencyCode'];
		}

		$this->calculate_data_per_query( $return );
		$this->rate = $return['rate'];

		return $return;
	}

	public function calculate_data_per_query( &$return ) {
		$maximum_cost = $current_cost = 2000;
		$restore_rate = 100;
		if ( isset( $return['cost']['maximumAvailable'] ) ) {
			$maximum_cost = $return['cost']['maximumAvailable'];
		}
		if ( isset( $return['cost']['restoreRate'] ) ) {
			$restore_rate = $return['cost']['restoreRate'];
		}
		if ( isset( $return['cost']['currentlyAvailable'] ) ) {
			$current_cost = $return['cost']['currentlyAvailable'];
		}

		$cost_per_product          = 60;
		$product_rate              = min( $current_cost / $cost_per_product, 5 );
		$return['rate']['product'] = apply_filters( 'viw2s_shopify_product_import_rate', $product_rate );
	}

	public function product_rate() {
		if ( $this->rate === null ) {
			$this->get_rate_limit();
		}

		return $this->rate['product'];
	}

	public function import_product( $product_data_format ) {

		$query = <<<QUERY
		mutation createProduct( \$productSet: ProductSetInput! ) {
			productSet(input: \$productSet ) {
			    product {
			        id
			        variants( first: 100 ) {
			        nodes {
			            id
			            inventoryItem {
			                id
			            }
			        }
			      }
			    }
			    userErrors {
			        code
			        field
			        message
			    }
		    }
		}
QUERY;

		$result = $this->query( $query, array(
			'productSet' => $product_data_format
		) );

		return $result;
	}


	public function update_inventory_item( $variable ) {
		$query  = <<<QUERY
		mutation inventoryItemUpdate( \$id:ID!, \$input: InventoryItemInput! ) {
		 inventoryItemUpdate( id: \$id, input: \$input ) {
			   inventoryItem {
			        id
			   }
			   userErrors {
			        field
			        message
			    }
			}
		}
QUERY;
		$result = $this->query( $query, $variable );

		return $result;
	}

	public function get_inventory_item_update( $data_product_item ) {
		$inventory_item_update = array();
		if ( empty( $data_product_item ) ) {
			return $inventory_item_update;
		}
		if ( ! $data_product_item->is_virtual() && ! $data_product_item->is_downloadable() ) {
			if ( ! empty( $data_product_item->get_weight() ) ) {
				$inventory_item_update['measurement']['weight']['value'] = (float) number_format( floatval( $data_product_item->get_weight() ), 3, '.', '' );
				$inventory_item_update['measurement']['weight']['unit']  = $this->map_weight_unit( $this->product_weight_unit );
			}
			$inventory_item_update['requiresShipping'] = true;
		} else {
			$inventory_item_update['requiresShipping'] = false;
		}

		return $inventory_item_update;
	}

	public function map_weight_unit( $weight_unit ) {
		$new_weight_unit = '';
		if ( empty( $weight_unit ) ) {
			return '';
		}

		switch ( $weight_unit ) {
			case 'kg':
				$new_weight_unit = 'KILOGRAMS';
				break;
			case 'g':
				$new_weight_unit = 'GRAMS';
				break;
			case 'lbs':
				$new_weight_unit = 'POUNDS';
				break;
			case 'oz':
				$new_weight_unit = 'OUNCES';
				break;
		}

		return $new_weight_unit;

	}


	public function get_product_data_format( $product_id ) {
		$product_data_format             = array();
		$prefix                          = 'viw2s_import_products--';
		$domain_store                    = self::$store_name;
		$keep_slug_product               = $this->settings->get_params( $domain_store, $prefix . 'import_product_keep_slug' );
		$import_attribute_simple_product = $this->settings->get_params( $domain_store, $prefix . 'import_attribute_simple_product' );
		$import_tags                     = $this->settings->get_params( $domain_store, $prefix . 'import_product_tags' );
		$import_sku                      = $this->settings->get_params( $domain_store, $prefix . 'import_product_sku' );
		$import_product_metafields       = $this->settings->get_params( $domain_store, $prefix . 'import_product_metafields' );
		$import_product_images_size      = $this->settings->get_params( $domain_store, 'viw2s_import_products--product_import_images_size' ) ?? 'full';
		$arr_data_temp                   = array();

		$viw2s_shopify_product_data = get_post_meta( $product_id, '_w2s_shopify_data', true );
		$data_product_item          = wc_get_product( $product_id );
		$product_type               = $data_product_item->get_type();
		if ( ! empty( $viw2s_shopify_product_data ) && isset( $viw2s_shopify_product_data[ $domain_store ] ) ) {
			/* Consider the case when product is removed on shopify */
			$product_data_format['status'] = 'exist';
			$product_data_format['data']   = array(
				'id'    => $product_id,
				'title' => $data_product_item->get_name(),
			);
		} else {
			if ( ! empty( $product_id ) && ! empty( $product_type ) ) {
				$product_arr_data_images = array();
				$arr_data_temp           = array(
					'title'           => $data_product_item->get_name(),
					'descriptionHtml' => $data_product_item->get_description(),
					'status'          => strtoupper( $this->settings->viw2s_status_map( $data_product_item->get_status(), $domain_store ) ),
					'productType'     => $product_type
				);

				/* Add product feature image */
				$image_url = wp_get_attachment_image_url( $data_product_item->get_image_id(), $import_product_images_size );
				if ( $image_url && strpos( $image_url, 'localhost' ) === false ) {
					$product_arr_data_images[] = array(
						'contentType'    => 'IMAGE',
						'originalSource' => $image_url,
						'alt'            => get_post_meta( $data_product_item->get_image_id(), '_wp_attachment_image_alt', true )
					);
				}

				/* Add all gallery images*/
				$gallery_image_ids = $data_product_item->get_gallery_image_ids();
				if ( ! empty( $gallery_image_ids ) ) {
					foreach ( $gallery_image_ids as $image_id ) {
						$image_url = wp_get_attachment_image_url( $image_id, $import_product_images_size );
						if ( $image_url && strpos( $image_url, 'localhost' ) === false ) {
							$product_arr_data_images[] = array(
								'contentType'    => 'IMAGE',
								'originalSource' => $image_url,
								'alt'            => get_post_meta( $image_id, '_wp_attachment_image_alt', true )
							);
						}
					}
				}

				/*Keep slug product*/
				if ( $keep_slug_product ) {
					$arr_data_temp['handle'] = urldecode( $data_product_item->get_slug() );
				}

				/*Import product tags*/
				if ( $import_tags ) {
					$product_tags_id = $this->settings->viw2s_get_all_product_tags( $data_product_item->get_id() );
					if ( ! empty( $product_tags_id ) ) {
						$arr_data_temp['tags'] = $product_tags_id;
					}
				}

				/*Import variant data*/
				$product_variant           = array();
				$arr_inventory_item_update = array();
				if ( $product_type === 'simple' ) {
					$arr_data_temp['productOptions'][] = array(
						'position' => 0,
						'name'     => 'Title',
						'values'   => [
							'name' => 'Default Title'
						]
					);

					$status = 'success';

					if ( $import_attribute_simple_product ) {
						$simple_attributes = $data_product_item->get_attributes();
						$namespace         = ! empty( $this->settings->get_params( $domain_store, $prefix . 'import_attribute_simple_product_namespace' ) ) ? $this->settings->get_params( $domain_store, $prefix . 'import_attribute_simple_product_namespace' ) : 'global';
						if ( ! empty( $simple_attributes ) ) {
							$simple_attributes_meta = [];
							foreach ( $simple_attributes as $attribute_name => $attribute ) {
								$attribute_data = $attribute->get_data() ?? [];
								$options        = $attribute_data['options'] ?? [];
								if ( ! empty( $options ) ) {
									$simple_attributes_meta[] = array(
										"key"       => $attribute_name,
										"value"     => implode( ',', $options ),
										"type"      => 'single_line_text_field',
										"namespace" => $namespace
									);
								}
							}
							if ( ! empty( $simple_attributes_meta ) ) {
								$arr_data_temp['metafields'] = wp_parse_args( $arr_data_temp['metafields'], $simple_attributes_meta );
							}
						}
					}

					if ( $data_product_item->is_on_sale() ) {
						$price            = $data_product_item->get_sale_price();
						$compare_at_price = $data_product_item->get_regular_price();
					} else {
						$price            = $data_product_item->get_price();
						$compare_at_price = null;
					}

					$product_variant_item = array(
						"price"          => $price,
						"compareAtPrice" => $compare_at_price,
						'position'       => 1,
						'optionValues'   => [
							'optionName' => 'Title',
							'name'       => 'Default Title'
						],
					);
					/*Import product sku*/
					if ( $import_sku && ! empty( $data_product_item->get_sku() ) ) {
						$product_variant_item['sku'] = $data_product_item->get_sku();
					}
					/*Import manage stock*/
					$inventory_item_update = $this->get_inventory_item_update( $data_product_item );
					if ( $data_product_item->get_manage_stock() ) {
						$inventory_item_update['tracked']              = true;
						$product_variant_item['inventoryQuantities'][] = array(
							'locationId' => $this->inventory_location,
							'name'       => 'available',
							'quantity'   => $data_product_item->get_stock_quantity(),
						);
						if ( $data_product_item->backorders_allowed() ) {
							$product_variant_item['inventoryPolicy'] = 'CONTINUE';
						} else {
							$product_variant_item['inventoryPolicy'] = 'DENY';
						}
					} else {
						$inventory_item_update['tracked'] = false;
					}
					/*Import barcode to variant if feature enable*/
					if ( ! empty( $import_product_metafields ) && is_array( $import_product_metafields ) ) {

						foreach ( $import_product_metafields as $import_product_meta_item ) {
							$woo_product_metakey = $import_product_meta_item['metakey'] ?? '';
							$import_product_type = $import_product_meta_item['type'] ?? '';
							if ( get_post_meta( $data_product_item->get_id(), $woo_product_metakey, true ) ) {
								$woo_product_metavalue = get_post_meta( $data_product_item->get_id(), $woo_product_metakey, true );
								if ( $import_product_type === 'product_barcode' ) {
									$product_variant_item['barcode'] = $woo_product_metavalue;
								}
							}

						}
					}

					$product_variant[]           = $product_variant_item;
					$arr_inventory_item_update[] = $inventory_item_update;

				} else {
					$product_attributes   = $data_product_item->get_attributes();
					$product_variations   = $data_product_item->get_available_variations();
					$product_option_names = [];
					$product_options      = [];

					/* Format shopify product options */
					foreach ( $product_attributes as $key => $attribute ) {
						if ( $attribute->get_variation() ) {
							$attribute_options = array();
							$option_key        = wc_sanitize_taxonomy_name( $key );
							if ( $attribute->is_taxonomy() ) {
								$option_name     = wc_attribute_label( $option_key );
								$attribute_terms = $attribute->get_terms();
								foreach ( $attribute_terms as $attribute_term ) {
									$attribute_options[] = array(
										'name' => $attribute_term->name,
									);
								}
							} else {
								$option_name = wc_attribute_label( $option_key, $data_product_item );
								foreach ( $attribute->get_options() as $option ) {
									$attribute_options[] = array(
										'name' => $option,
									);
								}
							}
							$product_option_names[ $key ] = $option_name;
							$product_options[]            = array(
								'position' => $attribute->get_position(),
								'name'     => $option_name,
								'values'   => $attribute_options,
							);
						}
					}
					if ( count( $product_options ) <= 3 ) {
						$arr_data_temp['productOptions'] = $product_options;

						$attribute_has_any_product = false;

						/*Loop variant*/
						foreach ( $product_variations as $variation_item ) {

							$variation = wc_get_product( $variation_item['variation_id'] );

							$price = $variation_item['display_price'];
							if ( $variation_item['display_price'] !== $variation_item['display_regular_price'] ) {
								$compare_at_price = $variation_item['display_regular_price'];
							} else {
								$compare_at_price = null;
							}

							$product_variant_item = array(
								"price"          => $price,
								"compareAtPrice" => $compare_at_price,
							);

							/*Add sku*/
							if ( $import_sku ) {
								if ( ! empty( $variation_item['sku'] ) && ( $variation_item['sku'] !== $data_product_item->get_sku() ) ) {
									$product_variant_item['sku'] = $variation_item['sku'];
								} else {
									$product_variant_item['sku'] = $data_product_item->get_sku();
								}
							}

							/*Add option value*/
							$variant_options           = array();
							$count_attribute_has_value = 0;
							foreach ( $variation_item['attributes'] as $key => $value ) {
								$key = str_replace( 'attribute_', '', $key );
								if ( ! empty( $value ) ) {
									$variant_options[] = array(
										'optionName' => $product_option_names[ $key ],
										'name'       => $variation->get_attribute( wc_sanitize_taxonomy_name( $key ) ),
									);
									$count_attribute_has_value ++;
								}
							}

							if ( $count_attribute_has_value < count( $product_option_names ) ) {
								$attribute_has_any_product = true;
							} else {
								$product_variant_item['optionValues'] = $variant_options;
							}

							/*Import manage stock*/
							$inventory_item_update = $this->get_inventory_item_update( $variation );
							if ( $variation->get_manage_stock() ) {
								$inventory_item_update['tracked']              = true;
								$product_variant_item['inventoryQuantities'][] = array(
									'locationId' => $this->inventory_location,
									'name'       => 'available',
									'quantity'   => $variation->get_stock_quantity(),
								);
								if ( $variation->backorders_allowed() ) {
									$product_variant_item['inventoryPolicy'] = 'CONTINUE';
								} else {
									$product_variant_item['inventoryPolicy'] = 'DENY';

								}
							} else {
								$inventory_item_update['tracked'] = false;
							}

							/*Add image variations product to array (index > 0)*/
							$image_current_variant_id = get_post_meta( $variation_item['variation_id'], '_thumbnail_id', true );
							$variant_thumb            = wp_get_attachment_image_url( $image_current_variant_id, $import_product_images_size );
							if ( $variant_thumb && strpos( $variant_thumb, 'localhost' ) === false ) {
								$variant_image             = array(
//									'contentType'    => 'IMAGE',
									'originalSource' => wp_get_attachment_image_url( $image_current_variant_id, $import_product_images_size ),
									'alt'            => get_post_meta( $image_current_variant_id, '_wp_attachment_image_alt', true )
								);
								$product_arr_data_images[] = $product_variant_item['file'] = $variant_image;
							}

							/* Add to variant list */
							$product_variant[]           = $product_variant_item;
							$arr_inventory_item_update[] = $inventory_item_update;
						}

						if ( $attribute_has_any_product ) {
							$status = 'attribute_has_any_product';
						} else {
							$status = 'success';
						}
					} else {
						$status = 'too_much_attributes';
					}

					if ( empty( $product_variant ) ) {
						$status = 'dont_has_any_product';
					}
				}

				/* Some case many variations same image link, need to unique*/
				$product_arr_data_images = array_values(
					array_intersect_key(
						$product_arr_data_images,
						array_unique(array_column($product_arr_data_images, 'originalSource'))
					)
				);

				$arr_data_temp['files']                       = $product_arr_data_images;
				$arr_data_temp['variants']                    = $product_variant;
				$product_data_format['data']                  = $arr_data_temp;
				$product_data_format['status']                = $status;
				$product_data_format['inventory_item_update'] = $arr_inventory_item_update;
			}
		}

		return $product_data_format;
	}

	public function set_time_limit( $limit = 3000 ) {
		if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) { // phpcs:ignore PHPCompatibility.IniDirectives.RemovedIniDirectives.safe_modeDeprecatedRemoved
			@set_time_limit( $limit ); // @codingStandardsIgnoreLine
		}
	}

}