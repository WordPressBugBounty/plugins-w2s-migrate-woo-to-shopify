<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'VIW2S_ADMIN_Clear_Data' ) ) {
	class VIW2S_ADMIN_Clear_Data {
		protected $settings;


		public function __construct() {
			$this->settings = VI_W2S_IMPORT_WOOCOMMERCE_TO_SHOPIFY_DATA::get_instance();

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_script' ), 25 );
			add_action( 'viw2s_submenu_clear_data', array( $this, 'admin_menu' ), 25 );
			add_action( 'wp_ajax_action_clear_data', array( $this, 'action_clear_data' ) );
			add_action( 'wp_ajax_nopriv_action_clear_data', array( $this, 'action_clear_data' ) );
		}

		public function admin_menu() {
			$menu_slug = 'w2s-import-woocommerce-to-shopify-clear-data';
			add_submenu_page(
				'vi-w2s-woo-to-shopify',
				esc_html__( 'Clear Data', 'w2s-migrate-woocommerce-to-shopify' ),
				esc_html__( 'Clear Data', 'w2s-migrate-woocommerce-to-shopify' ),
				apply_filters( 'villatheme_page_capabilities', 'manage_woocommerce' ),
				$menu_slug,
				array( $this, 'page_callback_clear_data' ),
				4
			);
		}

		public function page_callback_clear_data() {
			$store_setting = $this->settings->get_params( 'viw2s_store_setting' );

			?>
            <div class="wrap">
                <h2><?php esc_html_e( 'Clear Data', 'w2s-migrate-woocommerce-to-shopify' ) ?></h2>
                <div class="w2s-security-warning">
                    <div class="vi-ui red message">
                        <div class="header">
							<?php esc_html_e( 'Important Warring!!!', 'w2s-migrate-woocommerce-to-shopify' ); ?>
                        </div>
                        <p><?php esc_html_e( 'All your data deletion actions cannot be undone. Be very careful and understand what you want to do.', 'w2s-migrate-woocommerce-to-shopify' ); ?></p>

                    </div>
                </div>
                <div class="vi-ui segment viw2s-step-import-settings viw2s-choose-import active" data-step="choose-import">
                    <table class="vi-ui celled table" id="viw2s-table-choose-import">
                        <thead>
                        <tr>
                            <th><?php esc_html_e( 'Choose clear data', 'w2s-migrate-woocommerce-to-shopify' ); ?></th>
                        </tr>
                        </thead>
                        <tbody>
						<?php
						$arr_option_clear = [
							'products'           => 'Products',
							'product-categories' => 'Product categories',
						];
						if ( ! empty( $store_setting ) && is_array( $store_setting ) ) {
							foreach ( $store_setting as $count_store => $store_item ) {
								if ( ! $store_item['validate'] ) {
									continue;
								}
								$domain = $store_item['domain'] ?? '';
								?>
                                <tr class="data_store_import" data-store="<?php echo esc_attr( $domain ); ?>">
                                    <td>
                                        <table class="vi-ui celled table">
                                            <thead>
                                            <tr>
                                                <th class="viw2s_thead_store_choosen_import"><?php esc_html_e( 'Data', 'w2s-migrate-woocommerce-to-shopify' ); ?></th>
                                                <th>
                                                    <strong><?php echo esc_html( $domain ); ?></strong>
                                                    <input type="hidden" class="store_name_clear" value="<?php echo esc_attr( $domain ); ?>">
                                                </th>
                                            </tr>
                                            </thead>
                                            <tbody>
											<?php
											foreach ( $arr_option_clear as $key => $value ) {
												?>
                                                <tr>
                                                    <td><?php echo esc_html( $value ); ?></td>
                                                    <td class="viw2s-clear-<?php echo esc_html( $key ); ?>-data">
                                                        <a href="#" class="vi-ui labeled icon positive button tiny viw2s-clear-data-btn"
                                                           data-clear="<?php echo esc_attr( $key ); ?>"
                                                           data-clear_title="<?php echo esc_attr( $key ); ?>"
                                                        >
                                                            <i class="icon trash alternate outline"></i>
															<?php esc_html_e( 'Clear', 'w2s-migrate-woocommerce-to-shopify' ); ?>
                                                        </a>
                                                        <span class="mess-clear-data"></span>
                                                    </td>
                                                </tr>
												<?php
											}
											?>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
								<?php
							}
						}
						?>
                        </tbody>
                    </table>
                </div>
            </div>
			<?php

		}

		public function admin_enqueue_script( $page ) {
			if ( $page === 'woo-to-shopify_page_w2s-import-woocommerce-to-shopify-clear-data' ) {
				wp_enqueue_script( 'viw2s-clear-data-script', VIW2S_ADMIN_JS . 'clear-data.js', array( 'jquery' ), VIW2S_VERSION );
				wp_localize_script( 'viw2s-clear-data-script', 'viw2s_clear_param', array(
					'url'         => admin_url( 'admin-ajax.php' ),
					'viw2s_nonce' => wp_create_nonce( 'viw2s_clear_data_nonce' ),
				) );
			}
		}

		public function action_clear_data() {
			check_ajax_referer( 'viw2s_clear_data_nonce', 'viw2s_nonce' );
			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				wp_send_json_error();
			}
			$domain              = isset( $_POST['store_name'] ) ? sanitize_text_field( $_POST['store_name'] ) : '';
			$store_setting       = $this->settings->get_params( 'viw2s_store_setting' );
			$viw2s_current_store = $store_setting[0];

			$api_key             = $viw2s_current_store['api_key'] ?? '';
			$api_secret          = $viw2s_current_store['api_secret'] ?? '';
			$data_clear          = isset( $_POST['data_clear'] ) ? sanitize_text_field( $_POST['data_clear'] ) : '';

			$path = VI_W2S_IMPORT_WOOCOMMERCE_TO_SHOPIFY_DATA::get_cache_path( $domain, $api_key, $api_secret ) . '/';

			if ( empty( $domain ) || empty( $data_clear ) ) {
				wp_send_json( [ 'status' => 'error', 'mess' => 'info empty' ] );
			}
			switch ( $data_clear ) {
				case 'products':
					$this->delete_data_products( $domain, $path );
					break;
				case 'product-categories':
					$this->delete_data_product_categories( $domain, $path );
					break;
			}
			$viw2s_history_import = get_option( 'viw2s_history_import', [] );
			unset( $viw2s_history_import[ $domain ][ $data_clear ] );
			if ( empty( $viw2s_history_import[ $domain ] ) ) {
				unset( $viw2s_history_import[ $domain ] );
			}
			if ( empty( $viw2s_history_import ) ) {
				delete_option( 'viw2s_history_import' );
			} else {
				update_option( 'viw2s_history_import', $viw2s_history_import );
			}
			wp_send_json( [ 'status' => 'success', ] );
		}

		public function delete_data_products( $domain, $path ) {

			$viw2s_importing_arr_product = get_option( 'viw2s_importing_arr_product', [] );
			unset( $viw2s_importing_arr_product[ $domain ] );
			if ( empty( $viw2s_importing_arr_product ) ) {
				delete_option( 'viw2s_importing_arr_product' );
			} else {
				update_option( 'viw2s_importing_arr_product', $viw2s_importing_arr_product );
			}
			/*Delete product data*/
			$all_products = get_posts( array(
				'post_type'      => 'product',
				'posts_per_page' => - 1,
				'fields'         => 'ids', // Only get ids product
				'post_status'    => array( 'publish', 'pending', 'draft' ),

			) );
			foreach ( $all_products as $product_id ) {
				$w2s_shopify_data         = (array) get_post_meta( $product_id, '_w2s_shopify_data', true );
				$w2s_shopify_data_history = (array) get_post_meta( $product_id, '_w2s_update_history', true );
				unset( $w2s_shopify_data[ $domain ] );
				unset( $w2s_shopify_data_history[ $domain ] );
				if ( empty( $w2s_shopify_data ) ) {
					delete_post_meta( $product_id, '_w2s_shopify_data' );
				} else {
					update_post_meta( $product_id, '_w2s_shopify_data', $w2s_shopify_data );
				}
				if ( empty( $w2s_shopify_data_history ) ) {
					delete_post_meta( $product_id, '_w2s_update_history' );
				} else {
					update_post_meta( $product_id, '_w2s_update_history', $w2s_shopify_data_history );
				}

				$new_product_data = new WC_Product_Variable( $product_id );
				if ( ! empty( $new_product_data ) ) {
					$product_children = $new_product_data->get_children();
					foreach ( $product_children as $product_children_id ) {
						$w2s_shopify_data_children = (array) get_post_meta( $product_children_id, '_w2s_shopify_data', true );
						unset( $w2s_shopify_data_children[ $domain ] );
						if ( empty( $w2s_shopify_data ) ) {
							delete_post_meta( $product_children_id, '_w2s_shopify_data' );
						} else {
							update_post_meta( $product_children_id, '_w2s_shopify_data', $w2s_shopify_data_children );
						}

					}
				}
			}
			if ( is_file( $path . 'products.txt' ) ) {
				unlink( $path . 'products.txt' );
			}
			if ( is_file( $path . 'ids_product_imported.txt' ) ) {
				unlink( $path . 'ids_product_imported.txt' );
			}
			if ( is_file( $path . 'ids_product_new_imported.txt' ) ) {
				unlink( $path . 'ids_product_new_imported.txt' );
			}
			if ( is_file( $path . 'list_products_imported.txt' ) ) {
				unlink( $path . 'list_products_imported.txt' );
			}
			if ( is_file( $path . 'shopify_products_imported.txt' ) ) {
				unlink( $path . 'shopify_products_imported.txt' );
			}
		}

		public function delete_data_product_categories( $domain, $path ) {
			$viw2s_importing_arr_product_categories = get_option( 'viw2s_importing_arr_product_categories', [] );
			unset( $viw2s_importing_arr_product_categories[ $domain ] );
			if ( empty( $viw2s_importing_arr_product_categories ) ) {
				delete_option( 'viw2s_importing_arr_product_categories' );
			} else {
				update_option( 'viw2s_importing_arr_product_categories', $viw2s_importing_arr_product_categories );
			}
			/*Delete product categories data*/
			$arr_tax = get_terms( array(
				'taxonomy'   => 'product_cat',
				'orderby'    => 'name',
				'order'      => 'ASC',
				'hide_empty' => true,
				'fields'     => 'ids',
			) );
			foreach ( $arr_tax as $tax_item ) {
				$w2s_shopify_data = (array) get_post_meta( $tax_item, '_w2s_shopify_data', true );
				unset( $w2s_shopify_data[ $domain ] );
				if ( empty( $w2s_shopify_data ) ) {
					delete_term_meta( $tax_item, '_w2s_shopify_data' );
				} else {
					update_term_meta( $tax_item, '_w2s_shopify_data', $w2s_shopify_data );
				}
			}
			if ( is_file( $path . 'product_categories.txt' ) ) {
				unlink( $path . 'product_categories.txt' );
			}
			if ( is_file( $path . 'list_product_categories_imported.txt' ) ) {
				unlink( $path . 'list_product_categories_imported.txt' );
			}
			if ( is_file( $path . 'shopify_products_imported.txt' ) ) {
				unlink( $path . 'shopify_products_imported.txt' );
			}

		}

	}


	new VIW2S_ADMIN_Clear_Data();
}



