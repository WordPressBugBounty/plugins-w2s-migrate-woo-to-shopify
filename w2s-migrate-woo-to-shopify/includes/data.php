<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_W2S_IMPORT_WOOCOMMERCE_TO_SHOPIFY_DATA {
	private $params;
	private $default;
	private static $prefix;
	protected $my_options;
	protected static $instance = null;

	/**
	 * VI_W2S_IMPORT_WOOCOMMERCE_TO_SHOPIFY_DATA constructor.
	 * Init setting
	 */
	public function __construct() {
		self::$prefix = 'viw2s-';
		global $viw2s_settings;
		if ( ! $viw2s_settings ) {
			$viw2s_settings = get_option( 'viw2s_params', array() );
		}
		$this->default = array(
			'viw2s_store_setting'          => array(),
			'viw2s_import_products_option' => array(
				'product_by_type'               => '',
				'product_collection_id'         => '',
				'product_created_at_min'        => '',
				'product_created_at_max'        => '',
				'product_import_sequence'       => 'title asc',
				'import_product_keep_slug'      => 'on',
				'import_product_categories'     => 'on',
				'import_product_tags'           => 'on',
				'import_product_sku'            => 'on',
				'import_product_status_mapping' => array(
					'publish'        => 'active',
					'draft'          => 'draft',
					'pending_review' => 'archived'
				),
			),

		);

		$this->params = apply_filters( 'viw2s_params', wp_parse_args( $viw2s_settings, $this->default ) );
	}

	public static function get_instance( $new = false ) {
		if ( $new || null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}


	public function get_params( $name = "" ) {
		if ( ! $name ) {
			return $this->params;
		} elseif ( isset( $this->params[ $name ] ) ) {
			return apply_filters( 'viw2s_params' . $name, $this->params[ $name ] );
		} else {
			return false;
		}
	}

	public function get_default( $name = "" ) {
		if ( ! $name ) {
			return $this->default;
		} elseif ( isset( $this->default[ $name ] ) ) {
			return apply_filters( 'w2s_params_default' . $name, $this->default[ $name ] );
		} else {
			return false;
		}
	}

	public static function set( $name, $set_name = false ) {
		if ( is_array( $name ) ) {
			return implode( ' ', array_map( array( 'VI_W2S_IMPORT_WOOCOMMERCE_TO_SHOPIFY_DATA', 'set' ), $name ) );
		} else {
			if ( $set_name ) {
				return esc_attr__( str_replace( '-', '_', self::$prefix . $name ) );

			} else {
				return esc_attr__( self::$prefix . $name );

			}
		}
	}

	/** Get all data product by setting import product
	 *
	 * @return array|void|object|string
	 */
	public function viw2s_get_all_product_data() {
		$domain                    = '';
		$api_key                   = '';
		$api_secret                = '';
		$w2s_shopify_store_setting = self::get_params( 'viw2s_store_setting' );
		if ( ! empty( $w2s_shopify_store_setting ) && isset( $w2s_shopify_store_setting[0]['domain'] ) ) {
			$domain     = $w2s_shopify_store_setting[0]['domain'];
			$api_key    = $w2s_shopify_store_setting['api_key'] ?? '';
			$api_secret = $w2s_shopify_store_setting['api_secret'] ?? '';
		}
		$path                        = self::get_cache_path( $domain, $api_key, $api_secret ) . '/';
		$list_products_imported_path = $path . 'list_products_imported.txt';
		$ids_product_imported_path   = $path . 'ids_product_imported.txt';

		$list_products_imported = [];
		$ids_products_imported  = [];
		if ( is_file( $list_products_imported_path ) ) {
			$list_products_imported = json_decode( file_get_contents( $list_products_imported_path ), true );
		}
		/*Save to 2 file, old file to count and exclude when new import,
		it just merge new file when each new import active.
		New file will clean data after new import*/
		if ( is_file( $ids_product_imported_path ) ) {
			$ids_products_imported = json_decode( file_get_contents( $ids_product_imported_path ), true );
		} else {
			@file_put_contents( $ids_product_imported_path, json_encode( [] ) );
		}
		$products_option     = self::get_params( 'viw2s_import_products_option' );
		$product_create_date = self::viw2s_format_create_date();
		$args                = array(
			'status'  => 'publish',
			'limit'   => - 1,
			'exclude' => array_unique( $ids_products_imported )
		);

		if ( empty( $products_option['product_by_type'] ) || ( in_array( 'all', $products_option['product_by_type'] ) ) ) {
			$args['type'] = array( 'simple', 'variable' );
		} else {
			$args['type'] = $products_option['product_by_type'];
		}

		$product_order_arr = explode( ' ', $products_option['product_import_sequence'] );
		if ( is_array( $product_order_arr ) && count( $product_order_arr ) > 1 ) {
			$args['orderby'] = $product_order_arr[0];
			$args['order']   = strtoupper( $product_order_arr[1] );
		}
		if ( ! empty( $products_option['product_collection_id'] ) ) {
			$args['include'] = $products_option['product_collection_id'];

		}
		if ( ! empty( $products_option['product_exclude_id'] ) ) {
			$args['exclude'] = array_unique( wp_parse_args( $ids_products_imported, $products_option['product_exclude_id'] ) );

		}
		if ( ! empty( $products_option['product_categories_include_id'] ) ) {
			$args['category'] = $products_option['product_categories_include_id'];

		}
		if ( ! empty( $product_create_date ) ) {
			$args['date_created'] = $product_create_date;
		}
		$arr_data_product = array();
		$viw2s_products   = wc_get_products( $args );

		if ( ! empty( $viw2s_products ) ) {
//			$arr_data_product = $this->viw2s_product_data_format( $viw2s_product );
			$arr_ids_check    = [];
			$arr_ids_imported = array(); // array get all id imported for create default list_products_imported.txt (array('shopify_id'=>'woo_id))
			foreach ( $viw2s_products as $key => $viw2s_product_item ) {
				$current_item_id = $viw2s_product_item->get_id();
				if ( ! empty( $args['include'] ) && ! empty( $args['exclude'] ) ) {
					if ( in_array( $current_item_id, $args['exclude'] ) ) {
						continue;
					}
				}
				$arr_ids_check[] = $current_item_id;
				$_shopify_data   = get_post_meta( $current_item_id, '_w2s_shopify_data', true );

				if ( is_array( $_shopify_data ) && key_exists( $domain, $_shopify_data ) ) {
					$_w2s_shopify_product_id = $_shopify_data[ $domain ]['_w2s_shopify_product_id'] ?? '';
					$curent_imported_id      = $list_products_imported[ $_w2s_shopify_product_id ] ?? '';
					if ( $curent_imported_id != $current_item_id ) {
						unset( $_shopify_data[ $domain ] );
						if ( ! empty( $_shopify_data ) ) {
							update_post_meta( $current_item_id, '_w2s_shopify_data', $_shopify_data );
						} else {
							delete_post_meta( $current_item_id, '_w2s_shopify_data' );
						}
					} else {
						if ( ! empty( $_w2s_shopify_product_id ) ) {
							$arr_ids_imported[ $_w2s_shopify_product_id ] = $viw2s_product_item->get_id();
						}
						continue;
					}
				}
				$viw2s_product_item_push = array(
					"id"   => $viw2s_product_item->get_id(),
					"type" => $viw2s_product_item->get_type(),
					"name" => $viw2s_product_item->get_name(),
				);

				if ( $viw2s_product_item->get_type() == 'variable' ) {
					$new_product_data = new WC_Product_Variable( $viw2s_product_item->get_id() );
					$product_children = $new_product_data->get_available_variations();
					foreach ( $product_children as $child_item ) {
						$viw2s_product_item_push['children_ids'][] = $child_item['variation_id'];
					}
				}
				array_push( $arr_data_product, $viw2s_product_item_push );
			}
			if ( ! is_file( $list_products_imported_path ) || empty( $list_products_imported ) ) {
				if ( ! empty( $arr_ids_imported ) ) {
					file_put_contents( $list_products_imported_path, json_encode( $arr_ids_imported ) );
				}
			}
			if ( ! empty( $arr_ids_check ) ) {
				set_transient( 'viw2s_importing_arr_product', implode( ',', $arr_ids_check ), 86400 );
			}
		}

		return $arr_data_product;
	}

	/** Product data format
	 *
	 * @param  $product_id int product_id
	 * @param  $product_type array product_type
	 *
	 * @return array
	 */
	public function viw2s_product_data_format( $product_id, $product_type ) {
		$product_data_format = array();

		$viw2s_product_import      = $this->get_params( 'viw2s_import_products_option' );
		$keep_slug_product         = isset( $viw2s_product_import['import_product_keep_slug'] );
		$import_tags               = isset( $viw2s_product_import['import_product_tags'] );
		$import_category           = isset( $viw2s_product_import['import_product_categories'] );
		$import_sku                = isset( $viw2s_product_import['import_product_sku'] );
		$arr_data_temp             = array();
		$domain                    = '';
		$w2s_shopify_store_setting = self::get_params( 'viw2s_store_setting' );
		if ( ! empty( $w2s_shopify_store_setting ) && isset( $w2s_shopify_store_setting[0]['domain'] ) ) {
			$domain = $w2s_shopify_store_setting[0]['domain'];
		}
		$viw2s_shopify_product_data = get_post_meta( $product_id, '_w2s_shopify_data', true );
		if ( ! empty( $viw2s_shopify_product_data ) && isset( $viw2s_shopify_product_data[ $domain ] ) ) {
			$data_product_item                  = new WC_Product( $product_id );
			$product_data_format['status']      = 'exist';
			$product_data_format['data']        = array(
				'id'    => $product_id,
				'title' => $data_product_item->get_name(),
			);
			$product_data_format['data_images'] = array();
		} else {

			if (
				! empty( $product_id ) && ! empty( $product_type )
			) {
				$data_product_item       = new WC_Product( $product_id );
				$product_arr_data_images = array();
				$viw2s_array_stock       = array();
				$arr_data_temp           = array(
					'title'        => $data_product_item->get_name(),
					'body_html'    => $data_product_item->get_description(),
					'status'       => self::viw2s_status_map( $data_product_item->get_status() ),
					'published_at' => $data_product_item->get_date_created()->date( "Y-m-d\\TH:i:sP" )
				);

				/*Add feature image product to arr (index = 0)*/
				$product_arr_data_images[] = array(
					'src' => wp_get_attachment_image_url( $data_product_item->get_image_id(), 'full' )
				);

				/*Keep slug product*/
				if ( $keep_slug_product ) {
					$arr_data_temp['handle'] = $data_product_item->get_slug();
				}

				/*Import product tags*/
				if ( $import_tags ) {
					$product_tags_id = self::viw2s_get_all_product_tags( $data_product_item->get_id() );

					$arr_data_temp['tags'] = $product_tags_id;
				}

				/*Import variant data*/
				$product_variant = array();

				if ( $product_type === 'simple' ) {
					$new_product_data      = new WC_Product( $data_product_item->get_id() );
					$arr_data_temp['type'] = $new_product_data->get_type();

					if ( $new_product_data->is_on_sale() ) {
						$price            = $new_product_data->get_sale_price();
						$compare_at_price = $new_product_data->get_regular_price();
					} else {
						$price            = $new_product_data->get_price();
						$compare_at_price = null;
					}

					$product_variant_item = array(
						"price"            => $price,
						"compare_at_price" => $compare_at_price,
						'option1'          => null,
						'option2'          => null,
						'option3'          => null,
					);
					/*Import product sku*/
					if ( $import_sku && ! empty( $data_product_item->get_sku() ) ) {
						$product_variant_item['sku'] = $data_product_item->get_sku();
					}
					/*Import manage stock*/
					if ( $new_product_data->get_manage_stock() ) {
						$product_variant_item['inventory_management'] = 'shopify';
						$product_variant_item['inventory_quantity']   = $new_product_data->get_stock_quantity();
						if ( $new_product_data->backorders_allowed() ) {
							$product_variant_item['inventory_policy'] = 'continue';
						} else {
							$product_variant_item['inventory_policy'] = 'deny';

						}
					}
					$product_variant_item = array_merge( $product_variant_item, self::viw2s_add_require_shipping( $data_product_item, $product_variant_item ) );

					$product_variant = array(
						$product_variant_item
					);


					$status = 'success';
				} else {

					$new_product_data  = new WC_Product_Variable( $data_product_item->get_id() );
					$product_children  = $new_product_data->get_children();
					$viw2s_array_stock = array();
					if ( ! empty( $product_children ) ) {
						foreach ( $product_children as $product_child ) {
							$product_child_data = wc_get_product( $product_child );
							$arr_stock_temp     = array();
							if ( $product_child_data->get_manage_stock() ) {
								$arr_stock_temp['inventory_management'] = 'shopify';
								$arr_stock_temp['inventory_quantity']   = $product_child_data->get_stock_quantity();
								if ( $new_product_data->backorders_allowed() ) {
									$arr_stock_temp['inventory_policy'] = 'continue';
								} else {
									$arr_stock_temp['inventory_policy'] = 'deny';

								}
							}
							$viw2s_array_stock[] = $arr_stock_temp;

						}
					}
					$arr_data_temp['type']   = $new_product_data->get_type();
					$product_variations      = $new_product_data->get_variation_attributes();
					$product_variations_data = $new_product_data->get_data();
					if ( count( $product_variations ) > 3 ) {
						$product_data_format['status'] = 'error';
						$product_data_format['data']   = array();
					}
					$product_variations = $new_product_data->get_available_variations();
					$product_attributes = $new_product_data->get_variation_attributes();
					/*Loop attributes*/
					$viw2s_product_attribute = array();

					foreach ( $product_attributes as $key_attribute => $item_attribute ) {
						$name_attribute = wc_attribute_label( $key_attribute );
						array_push( $viw2s_product_attribute, array( "name" => $name_attribute ) );
					}
					if ( ! empty( $viw2s_product_attribute ) ) {
						$arr_data_temp['options'] = $viw2s_product_attribute;
					}
					if ( count( $product_attributes ) <= 3 ) {
						$attribute_has_any_product = false;

						/*Loop variant*/
						$count_variant = 0;
						foreach ( $product_variations as $variation_item ) {
							$price = $variation_item['display_price'];
							if ( $variation_item['display_price'] !== $variation_item['display_regular_price'] ) {
								$compare_at_price = $variation_item['display_regular_price'];
							} else {
								$compare_at_price = null;
							}

							$product_variant_item = array(
								"price"            => $price,
								"compare_at_price" => $compare_at_price,
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
							$new_arr_variation_attributes = array();

							foreach ( $variation_item['attributes'] as $key_attribute => $option_item ) {
								array_push( $new_arr_variation_attributes, $option_item );

							}
							if ( ! empty( array_keys( $product_attributes ) ) ) {
								$count_attribute_has_value = 0;
								foreach ( array_keys( $product_attributes ) as $i => $attribute_key ) {
									$attribute_key = str_replace( ' ', '-', strtolower( $attribute_key ) );
									if ( ! empty( $variation_item['attributes'][ 'attribute_' . $attribute_key ] ) ) {
										$term_attribute                                         = get_term_by( 'slug', $variation_item['attributes'][ 'attribute_' . $attribute_key ], $attribute_key );
										$product_variant_item[ 'option' . (string) ( $i + 1 ) ] = $term_attribute->name ?? $variation_item['attributes'][ 'attribute_' . $attribute_key ];

										$count_attribute_has_value ++;
									} else {
										$product_variant_item[ 'option' . (string) ( $i + 1 ) ] = null;
									}

								}
								if ( $count_attribute_has_value < count( $new_arr_variation_attributes ) ) {
									$attribute_has_any_product = true;
								}
							}

							/*Add shipping weight*/
							if ( ! $variation_item['is_virtual'] && ! $variation_item['is_downloadable'] ) {
								if ( ! empty( $variation_item['weight'] ) ) {
									$product_variant_item['weight']      = number_format( floatval( $variation_item['weight'] ), 1, '.', '' );
									$product_variant_item['weight_unit'] = self::viw2s_map_weight_unit( get_option( 'woocommerce_weight_unit' ) );
								}
								$product_variant_item['requires_shipping'] = true;
							} else {
								$product_variant_item['requires_shipping'] = false;
							}
							/*Add Manager stock*/

							$product_variant_item = wp_parse_args( $product_variant_item, $viw2s_array_stock[ $count_variant ] );


							$product_variant_item['requires_shipping'] = false;
//						wp_parse_args( $product_variant_item, self::viw2s_add_require_shipping( $new_product_data, $product_variant_item ) );
							array_push( $product_variant, $product_variant_item );

							/*Add image variations product to array (index > 0)*/
							$image_current_variant_id  = get_post_meta( $variation_item['variation_id'], '_thumbnail_id', true );
							$product_arr_data_images[] = array(
								'src' => wp_get_attachment_image_url( $image_current_variant_id, 'full' )
							);
							$count_variant ++;
						}
						if ( $attribute_has_any_product ) {
							$status = 'attribute_has_any_product';
						} else {
							$status = 'success';
						}
					} else {
						$status = 'too_much_attributes';
					}
				}
				if ( empty( $product_variant ) ) {
					$status = 'dont_has_any_product';
				}
				/*Add gallery image product to arr (index > count variation)*/
				$ids_gallery_product = $data_product_item->get_gallery_image_ids();
				if ( is_array( $ids_gallery_product ) && ! empty( $ids_gallery_product ) ) {
					foreach ( $ids_gallery_product as $id_gallery_product ) {
						$product_arr_data_images[] = array(
							'src' => wp_get_attachment_image_url( $id_gallery_product, 'full' )
						);
					}
				}

				$arr_data_temp['variants'] = $product_variant;
//			$arr_data_temp['images']            = $product_arr_data_images;
				$product_data_format['status']            = $status;
				$product_data_format['data']              = $arr_data_temp;
				$product_data_format['data_images']       = $product_arr_data_images;
				$product_data_format['viw2s_array_stock'] = $viw2s_array_stock;

			}
		}


		return $product_data_format;

	}

	/** mapping weight unit shopify
	 *
	 * @param string $weight_unit
	 *
	 * @return string|void
	 */
	public function viw2s_map_weight_unit( $weight_unit ) {
		$new_weight_unit = '';
		if ( empty( $weight_unit ) ) {
			return;
		}

		switch ( $weight_unit ) {
			case 'kg':
				$new_weight_unit = 'kg';
				break;
			case 'g':
				$new_weight_unit = 'g';
				break;
			case 'lbs':
				$new_weight_unit = 'lb';
				break;
			case 'oz':
				$new_weight_unit = 'oz';
				break;
		}

		return $new_weight_unit;

	}

	/** mapping product status shopify
	 *
	 * @param string $status_mapping
	 *
	 * @return string|void
	 */
	public function viw2s_status_map( $status_mapping ) {
		$new_status          = '';
		$products_option     = self::get_params( 'viw2s_import_products_option' );
		$products_status_map = $products_option['import_product_status_mapping'];
		if ( empty( $products_status_map ) || ! is_array( $products_status_map ) || empty( $status_mapping ) ) {
			return;
		}

		switch ( $status_mapping ) {
			case 'publish':
				$new_status = $products_status_map['publish'];
				break;
			case 'draft':
				$new_status = $products_status_map['draft'];
				break;
			case 'pending':
				$new_status = $products_status_map['pending_review'];
				break;

		}

		return $new_status;

	}

	/** Add Requires shipping method
	 *
	 * @param object $data_product_item
	 * @param  $product_arr_format
	 *
	 * @return array|string|void
	 */
	public function viw2s_add_require_shipping( $data_product_item, $product_arr_format ) {
		if ( empty( $data_product_item ) ) {
			return $product_arr_format;
		}

		if ( ! $data_product_item->is_virtual() && ! $data_product_item->is_downloadable() ) {
			if ( ! empty( $data_product_item->get_weight() ) ) {
				$product_arr_format['weight']      = number_format( floatval( $data_product_item->get_weight() ), 1, '.', '' );
				$product_arr_format['weight_unit'] = self::viw2s_map_weight_unit( get_option( 'woocommerce_weight_unit' ) );
			}
			$product_arr_format['requires_shipping'] = true;
		} else {
			$product_arr_format['requires_shipping'] = false;
		}

		return $product_arr_format;
	}

	/** Get all product tags name
	 *
	 * @param int $product_id
	 *
	 * @return string
	 */
	public function viw2s_get_all_product_tags( $product_id ) {
		if ( empty( $product_id ) ) {
			return '';
		}
		$product_tags_id        = '';
		$viw2s_product_tags     = get_the_terms( $product_id, 'product_tag' );
		$viw2s_arr_product_tags = array();
		if ( ! empty( $viw2s_product_tags ) && ! is_wp_error( $viw2s_product_tags ) ) {
			foreach ( $viw2s_product_tags as $term ) {
				$viw2s_arr_product_tags[] = $term->name;
			}
		}
		$product_tags_id = implode( ',', $viw2s_arr_product_tags );

		return $product_tags_id;
	}

	/** Get all product tags name
	 *
	 *
	 * @return array|void
	 */
	public function viw2s_get_all_product_cats() {
		$domain                    = '';
		$api_key                   = '';
		$api_secret                = '';
		$w2s_shopify_store_setting = self::get_params( 'viw2s_store_setting' );
		if ( ! empty( $w2s_shopify_store_setting ) && isset( $w2s_shopify_store_setting[0]['domain'] ) ) {
			$domain     = $w2s_shopify_store_setting[0]['domain'];
			$api_key    = $w2s_shopify_store_setting['api_key'] ?? '';
			$api_secret = $w2s_shopify_store_setting['api_secret'] ?? '';
		}
		$path                        = self::get_cache_path( $domain, $api_key, $api_secret ) . '/';
		$viw2s_product_cats = get_terms( array(
			'taxonomy'   => 'product_cat',
			'orderby'    => 'name',
			'order'      => 'ASC',
			'hide_empty' => true,
			'fields'     => 'all',
		) );;
		$list_product_categories_imported_path = $path . '/list_product_categories_imported.txt';

		$viw2s_arr_product_cats = array();
		if ( ! empty( $viw2s_product_cats ) && ! is_wp_error( $viw2s_product_cats ) ) {
			$status                    = 'success';
			$domain                    = '';
			$w2s_shopify_store_setting = self::get_params( 'viw2s_store_setting' );
			if ( ! empty( $w2s_shopify_store_setting ) && isset( $w2s_shopify_store_setting[0]['domain'] ) ) {
				$domain = $w2s_shopify_store_setting[0]['domain'];
			}
			foreach ( $viw2s_product_cats as $term_item ) {

				$_w2s_shopify_data = get_term_meta( $term_item->term_id, '_w2s_shopify_data', true );
				if ( is_array( $_w2s_shopify_data ) && array_key_exists( $domain, $_w2s_shopify_data ) ) {
					$status          = 'exist';
					$_w2s_shopify_id = $_w2s_shopify_data[ $domain ]['_w2s_shopify_product_cat_id'] ?? '';
					if ( ! empty( $_w2s_shopify_id ) ) {
						$arr_ids_imported[ $_w2s_shopify_id ] = $term_item->term_id;
					}
					continue;
				}

				$arr_term = array(
					'title'   => $term_item->name,
					'term_id' => $term_item->term_id,
					'status'  => $status
				);

				array_push( $viw2s_arr_product_cats, $arr_term );
			}
			if ( ! is_file( $list_product_categories_imported_path ) && ! empty( $arr_ids_imported ) ) {
				file_put_contents( $list_product_categories_imported_path, json_encode( $arr_ids_imported ) );
			}
		}

		return $viw2s_arr_product_cats;
	}

	/** Get all product tags name
	 *
	 * @param $product_cat array
	 *
	 * @return array|void
	 */
	public function viw2s_format_product_cats( $product_cat ) {
		if ( empty( $product_cat ) ) {
			return;
		}
		$all_id_product_by_cats = self::viw2s_get_all_id_product_by_cats( $product_cat['term_id'] );

		if ( is_array( $all_id_product_by_cats ) && ! empty( $all_id_product_by_cats ) ) {
			$arr_term = array();
			foreach ( $all_id_product_by_cats as $product_id ) {
				if ( ! empty( self::viw2s_shopify_product_id_by_woo_product_id( $product_id ) ) ) {

					$arr_term[] = [
						"product_id" => self::viw2s_shopify_product_id_by_woo_product_id( $product_id )
						// shopify id product
					];
				}

			}
			if ( ! empty( $arr_term ) ) {
				$product_cat['collects'] = $arr_term;
			}
		}

		return $product_cat;
	}

	/** Get all product id by product cat id
	 *
	 * @param $id_product_cat int
	 *
	 * @return array|void
	 */
	public function viw2s_get_all_id_product_by_cats( $id_product_cat = '' ) {
		if ( empty( $id_product_cat ) ) {
			return;
		}

		return get_posts( array(
			'post_type'      => 'product',
			'posts_per_page' => - 1,
			'fields'         => 'ids', // Only get ids product
			'post_status'    => 'publish',
			'tax_query'      => array(
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => $id_product_cat, /*category id*/
					'operator' => 'IN',
				)
			),
		) );
	}

	/** Get shopify product id by woocommerce product id
	 *
	 * @param $product_id int
	 *
	 * @return int|void
	 */
	public function viw2s_shopify_product_id_by_woo_product_id( $product_id = '' ) {
		if ( empty( $product_id ) ) {
			return;
		}
		$w2s_shopify_product_id    = '';
		$w2s_shopify_store_setting = self::get_params( 'viw2s_store_setting' );
		if ( ! empty( $w2s_shopify_store_setting ) && isset( $w2s_shopify_store_setting[0]['domain'] ) ) {
			$domain               = $w2s_shopify_store_setting[0]['domain'];
			$shopify_product_data = get_post_meta( $product_id, '_w2s_shopify_data', true );

			if ( $shopify_product_data && isset( $shopify_product_data[ $domain ] ) ) {
				$w2s_shopify_product_id = $shopify_product_data[ $domain ]['_w2s_shopify_product_id'];
			}
		}

		return $w2s_shopify_product_id;
	}

	/** Product creation date format for querying:
	 *
	 * @return string
	 */
	public function viw2s_format_create_date() {
		$product_create_date    = '';
		$products_option        = self::get_params( 'viw2s_import_products_option' );
		$product_created_at_min = $products_option['product_created_at_min'];
		$product_created_at_max = $products_option['product_created_at_max'];

		switch ( true ) {
			case ! empty( $product_created_at_min ) && empty( $product_created_at_max ):
				$product_create_date = '>=' . $product_created_at_min;
				break;
			case empty( $product_created_at_min ) && ! empty( $product_created_at_max ):
				$product_create_date = '<=' . $product_created_at_max;
				break;
			case ! empty( $product_created_at_min ) && ! empty( $product_created_at_max ):
				$product_create_date = $product_created_at_min . '...' . $product_created_at_max;
				break;
			default:
				$product_create_date = '';
				break;
		}

		return $product_create_date;
	}

	public function viw2s_array_combinations( $arrays, $i = 0 ) {
		if ( ! isset( $arrays[ $i ] ) ) {
			return array();
		}
		if ( $i == count( $arrays ) - 1 ) {
			return $arrays[ $i ];
		}

		// get combinations from subsequent arrays
		$tmp = self::viw2s_array_combinations( $arrays, $i + 1 );

		$result = array();

		// concat each array from tmp with each element from $arrays[$i]
		foreach ( $arrays[ $i ] as $v ) {
			foreach ( $tmp as $t ) {
				$result[] = is_array( $t ) ?
					array_merge( array( $v ), $t ) :
					array( $v, $t );
			}
		}

		return $result;
	}

	public static function viw2s_log( $log_file, $logs_content ) {
		$logs_content = PHP_EOL . "[" . date( "Y-m-d H:i:s" ) . "] " . $logs_content;
		if ( is_file( $log_file ) ) {
			file_put_contents( $log_file, $logs_content, FILE_APPEND );
		} else {
			file_put_contents( $log_file, $logs_content );
		}
	}


	/**
	 * @param string $domain
	 * @param string $api_key
	 * @param string $api_secret
	 *
	 * @return array
	 */
	static public function get_access_scopes( $domain, $api_key, $api_secret ) {
		$url     = "https://{$api_key}:{$api_secret}@{$domain}/admin/oauth/access_scopes.json";
		$request = wp_remote_get(
			$url, array(
				'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36',
				'timeout'    => 30,
				'headers'    => array( 'Authorization' => 'Basic ' . base64_encode( $api_key . ':' . $api_secret ) ),
			)
		);
		$return  = array(
			'status' => 'error',
			'data'   => '',
			'code'   => '',
		);
		if ( ! is_wp_error( $request ) ) {
			if ( isset( $request['response']['code'] ) ) {
				$return['code'] = $request['response']['code'];
			}
			$body = json_decode( $request['body'], true );
			if ( isset( $body['errors'] ) ) {
				$return['data'] = $body['errors'];
			} else {
				$return['status'] = 'success';
				$return['data']   = $body['access_scopes'];
			}
		} else {
			$return['data'] = $request->get_error_message();
			$return['code'] = $request->get_error_code();
		}

		return $return;
	}

	/**
	 * @param string $domain
	 * @param string $api_key
	 * @param string $api_secret
	 *
	 * @return array
	 */
	static public function get_access_scopes_handle( $domain, $api_key, $api_secret ) {

		$return = array();
		try {
			$access_scopes        = self::get_access_scopes( $domain, $api_key, $api_secret );
			$access_scopes_data   = $access_scopes['data'];
			$access_scopes_status = $access_scopes['status'];
			if ( $access_scopes_status === 'success' && is_array( $access_scopes_data ) && ! empty( $access_scopes_data ) ) {
				foreach ( $access_scopes_data as $access_scopes_handle_item ) {
					$return[] = $access_scopes_handle_item['handle'];
				}
			}
		} catch ( Exception $exception ) {
			$return['error'] = $exception->getMessage();
		}


		return $return;
	}

	/**
	 * @param string $domain
	 * @param string $api_key
	 * @param string $api_secret
	 *
	 * @return array
	 */
	static public function get_shopify_store_info( $domain, $api_key, $api_secret ) {

		$return          = array();
		$config          = array(
			'ShopUrl'  => $domain,
			'ApiKey'   => $api_key,
			'Password' => $api_secret
		);
		$VIW2SShopifySDK = PHPShopify\ShopifySDK::config( $config );
		try {
			$return = array(
				'data' => $VIW2SShopifySDK->Shop->get(),
				'code' => 200
			);

		} catch ( Exception $exception ) {
			$return = array(
				'message' => $exception->getMessage(),
				'code'    => $exception->getCode()
			);
		}

		return $return;
	}

	public static function get_option( $option_name, $default = false ) {
		return get_option( $option_name, $default );
	}

	public static function update_option( $option_name, $option_value ) {
		return update_option( $option_name, $option_value );
	}

	public static function delete_option( $option_name ) {
		return delete_option( $option_name );
	}

	/**
	 * @param $files
	 */
	public static function delete_files( $files ) {
		if ( is_array( $files ) ) {
			if ( count( $files ) ) {
				foreach ( $files as $file ) { // iterate files
					if ( is_file( $file ) ) {
						unlink( $file );
					} // delete file
				}
			}
		} elseif ( is_file( $files ) ) {
			unlink( $files );
		}
	}

	public static function deleteDir( $dirPath ) {
		if ( is_dir( $dirPath ) ) {
			if ( substr( $dirPath, strlen( $dirPath ) - 1, 1 ) != '/' ) {
				$dirPath .= '/';
			}
			$files = glob( $dirPath . '*', GLOB_MARK );
			foreach ( $files as $file ) {
				if ( is_dir( $file ) ) {
					self::deleteDir( $file );
				} else {
					unlink( $file );
				}
			}
			rmdir( $dirPath );
		}
	}

	protected static function create_plugin_cache_folder() {
		if ( ! is_dir( VIW2S_IMPORT_WOOCOMMERCE_TO_SHOPIFY_CACHE ) ) {
			wp_mkdir_p( VIW2S_IMPORT_WOOCOMMERCE_TO_SHOPIFY_CACHE );
			file_put_contents( VIW2S_IMPORT_WOOCOMMERCE_TO_SHOPIFY_CACHE . '.htaccess',
				'
				<IfModule !mod_authz_core.c>Order deny,allow
					Deny from all
					</IfModule>
					<IfModule mod_authz_core.c>
					  <RequireAll>
					    Require all denied
					  </RequireAll>
					</IfModule>
					'
			);
		}
	}

	public static function create_cache_folder( $path ) {
		self::create_plugin_cache_folder();
		if ( ! is_dir( $path ) ) {
			wp_mkdir_p( $path );
		}
	}

	public static function get_cache_path( $domain, $api_key, $api_secret ) {
		return VIW2S_IMPORT_WOOCOMMERCE_TO_SHOPIFY_CACHE . md5( $api_key ) . '_' . md5( $api_secret ) . '_' . $domain;
	}

	public static function implode_args( $args ) {
		foreach ( $args as $key => $value ) {
			if ( is_array( $value ) ) {
				$args[ $key ] = implode( ',', $value );
			}
		}

		return $args;
	}


}