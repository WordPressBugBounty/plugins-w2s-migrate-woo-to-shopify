<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://villatheme.com
 * @since      1.0.0
 *
 * @package    w2s-migrate-woo-to-shopify
 * @subpackage w2s-migrate-woo-to-shopify/admin
 * @author     Villatheme <support@villatheme.com>
 */
class Vi_W2s_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $vi_w2s The ID of this plugin.
	 */
	private $vi_w2s;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $vi_w2s The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */

	private $setting;
	private $default_data;

	public function __construct( $vi_w2s, $version ) {

		$this->vi_w2s       = $vi_w2s;
		$this->version      = $version;
		$this->setting      = new VI_W2S_IMPORT_WOOCOMMERCE_TO_SHOPIFY_DATA();
		$this->default_data = $this->setting->get_default();

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Vi_W2s_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Vi_W2s_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$current_screen = get_current_screen()->id;

		if (
			$current_screen == 'toplevel_page_vi-w2s-woo-to-shopify' ||
			$current_screen === 'woo-to-shopify_page_w2s-import-woocommerce-to-shopify-clear-data'
		) {
			wp_enqueue_style( $this->vi_w2s . '-accordion', VIW2S_DIR_URL . 'assets/css/accordion.min.css', array(), $this->version );
			wp_enqueue_style( $this->vi_w2s . '-button', VIW2S_DIR_URL . 'assets/css/button.min.css', array(), $this->version );
			wp_enqueue_style( $this->vi_w2s . '-checkbox', VIW2S_DIR_URL . 'assets/css/checkbox.min.css', array(), $this->version );
			wp_enqueue_style( $this->vi_w2s . '-dropdown', VIW2S_DIR_URL . 'assets/css/dropdown.min.css', array(), $this->version );
			wp_enqueue_style( $this->vi_w2s . '-form', VIW2S_DIR_URL . 'assets/css/form.min.css', array(), $this->version );
			wp_enqueue_style( $this->vi_w2s . '-icon', VIW2S_DIR_URL . 'assets/css/icon.min.css', array(), $this->version );
			wp_enqueue_style( $this->vi_w2s . '-input', VIW2S_DIR_URL . 'assets/css/input.min.css', array(), $this->version );
			wp_enqueue_style( $this->vi_w2s . '-label', VIW2S_DIR_URL . 'assets/css/label.min.css', array(), $this->version );
			wp_enqueue_style( $this->vi_w2s . '-message', VIW2S_DIR_URL . 'assets/css/message.min.css', array(), $this->version );
			wp_enqueue_style( $this->vi_w2s . '-progress', VIW2S_DIR_URL . 'assets/css/progress.min.css', array(), $this->version );
			wp_enqueue_style( $this->vi_w2s . '-segment', VIW2S_DIR_URL . 'assets/css/segment.min.css', array(), $this->version );
			wp_enqueue_style( $this->vi_w2s . '-select2', VIW2S_DIR_URL . 'assets/css/select2.min.css', array(), $this->version );
			wp_enqueue_style( $this->vi_w2s . '-step', VIW2S_DIR_URL . 'assets/css/step.min.css', array(), $this->version );
			wp_enqueue_style( $this->vi_w2s . '-table', VIW2S_DIR_URL . 'assets/css/table.min.css', array(), $this->version );
			wp_enqueue_style( $this->vi_w2s . '-transition', VIW2S_DIR_URL . 'assets/css/transition.min.css', array(), $this->version );

			if ( WP_DEBUG ) {
				wp_enqueue_style( $this->vi_w2s . '-style', VIW2S_DIR_URL . 'admin/css/vi-w2s-admin.css', array(), $this->version );
			} else {
				wp_enqueue_style( $this->vi_w2s . '-style', VIW2S_DIR_URL . 'admin/css/vi-w2s-admin.min.css', array(), $this->version );
			}
		}

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Vi_W2s_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Vi_W2s_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$current_screen = get_current_screen()->id;

		if (
			$current_screen === 'toplevel_page_vi-w2s-woo-to-shopify' ||
			$current_screen === 'woo-to-shopify_page_w2s-import-woocommerce-to-shopify-clear-data'
		) {

			wp_enqueue_script( $this->vi_w2s . '-js-accordion', VIW2S_DIR_URL . 'assets/js/accordion.min.js', array( 'jquery' ) );
			wp_enqueue_script( $this->vi_w2s . '-js-checkbox', VIW2S_DIR_URL . 'assets/js/checkbox.min.js', array( 'jquery' ) );
			wp_enqueue_script( $this->vi_w2s . '-js-dropdown', VIW2S_DIR_URL . 'assets/js/dropdown.min.js', array( 'jquery' ) );
			wp_enqueue_script( $this->vi_w2s . '-js-progress', VIW2S_DIR_URL . 'assets/js/progress.min.js', array( 'jquery' ) );
			wp_enqueue_script( $this->vi_w2s . '-js-select2', VIW2S_DIR_URL . 'assets/js/select2.js', array( 'jquery' ) );
			wp_enqueue_script( $this->vi_w2s . '-js-transition', VIW2S_DIR_URL . 'assets/js/transition.min.js', array( 'jquery' ) );

			wp_enqueue_script( $this->vi_w2s . '-js', VIW2S_DIR_URL . 'admin/js/vi-w2s-admin.js', array(
				'jquery',
				'jquery-tiptip'
			), $this->version );
			$viw2s_i18n_params = array(
				'ajaxurl'                              => admin_url( "admin-ajax.php" ),
				'i18n_empty_store_address_error'       => esc_html__( 'Store address can not be empty! ', 'w2s-migrate-woo-to-shopify' ),
				'i18n_empty_store_api_key_error'       => esc_html__( 'API key can not be empty! ', 'w2s-migrate-woo-to-shopify' ),
				'i18n_empty_store_api_secret_error'    => esc_html__( 'API secret can not be empty! ', 'w2s-migrate-woo-to-shopify' ),
				'i18n_empty_choose_store_import_error' => esc_html__( 'Need to select at least one store to import! ', 'w2s-migrate-woo-to-shopify' ),
				'i18n_empty_choose_data_import_error'  => esc_html__( 'Need to select at least one data to import! ', 'w2s-migrate-woo-to-shopify' ),
				'i18n_search_product_placeholder'      => esc_html__( 'Select products', 'w2s-migrate-woo-to-shopify' ),
			);

			wp_localize_script( $this->vi_w2s . '-js', 'viw2s_i18n_params', $viw2s_i18n_params );
		}
	}

	/**
	 * Register Menu Setting link
	 *
	 * @return array
	 * @since    1.0.0
	 */
	function viw2s_add_action_links( $links ) {
		$settings_link = array(
			'<a href="' . admin_url( 'admin.php?page=vi-w2s-woo-to-shopify' ) . '">' . esc_html__( 'Settings', 'w2s-migrate-woo-to-shopify' ) . '</a>',
		);

		return array_merge( $links, $settings_link );
	}

	/**
	 * Register Menu Setting for the admin area.
	 * @return void
	 * @since    1.0.0
	 */
	public function viw2s_admin_menu() {
		add_menu_page(
			esc_html__( 'Woo to Shopify', 'w2s-migrate-woo-to-shopify' ),
			esc_html__( 'Woo to Shopify', 'w2s-migrate-woo-to-shopify' ),
			'manage_woocommerce',
			'vi-w2s-woo-to-shopify',
			array( $this, 'viw2s_page_setting_function' ),
			'dashicons-image-rotate-right',
			3
		);
		do_action( 'viw2s_submenu_clear_data' );
		$menu_slug = 'w2s-import-woocommerce-to-shopify-status';
		add_submenu_page(
			'vi-w2s-woo-to-shopify',
			esc_html__( 'System status', 'w2s-migrate-woo-to-shopify' ),
			esc_html__( 'System Status', 'w2s-migrate-woo-to-shopify' ),
			'manage_woocommerce',
			$menu_slug,
			array( $this, 'page_callback_system_status' )
		);

		$menu_slug = 'w2s-import-woocommerce-to-shopify-logs';
		add_submenu_page(
			'vi-w2s-woo-to-shopify',
			esc_html__( 'Logs', 'w2s-migrate-woo-to-shopify' ),
			esc_html__( 'Logs', 'w2s-migrate-woo-to-shopify' ),
			apply_filters( 'vi_w2s_admin_sub_menu_capability', 'manage_options', $menu_slug ),
			$menu_slug,
			array( $this, 'page_callback_logs' )
		);

	}

	public static function security_recommendation_html() {
		?>
        <div class="w2s-security-warning">
            <div class="vi-ui warning message">
                <div class="header">
					<?php esc_html_e( 'Shopify Admin API security recommendation', 'w2s-migrate-woo-to-shopify' ); ?>
                </div>
                <ul class="list">
                    <li><?php esc_html_e( 'You should enable only what is necessary for your app to work.', 'w2s-migrate-woo-to-shopify' ); ?></li>
                    <li><?php esc_html_e( 'Treat the API key and password like you would any other password, since whoever has access to these credentials has API access to the store.', 'w2s-migrate-woo-to-shopify' ); ?></li>
                    <li><?php esc_html_e( 'Change your API at least once a month', 'w2s-migrate-woo-to-shopify' ); ?></li>
                    <li><?php esc_html_e( 'If you only use API to import data, remove API permissions or delete the API after import completed', 'w2s-migrate-woo-to-shopify' ); ?></li>
                </ul>
            </div>
        </div>
		<?php
	}

	public function page_callback_system_status() {
		?>
        <h2><?php esc_html_e( 'System Status', 'w2s-migrate-woo-to-shopify' ) ?></h2>
        <table id="status" class="widefat">
            <thead>
            <tr>
                <th><?php esc_html_e( 'Option name', 'w2s-migrate-woo-to-shopify' ) ?></th>
                <th><?php esc_html_e( 'Your option value', 'w2s-migrate-woo-to-shopify' ) ?></th>
                <th><?php esc_html_e( 'Minimum recommended value', 'w2s-migrate-woo-to-shopify' ) ?></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td data-export-label="file_get_contents">file_get_contents</td>
                <td>
					<?php
					if ( function_exists( 'file_get_contents' ) ) {
						?>
                        <mark class="yes">&#10004; <code class="private"></code></mark>
						<?php
					} else {
						?>
                        <mark class="error">&#10005;</mark>'
						<?php
					}
					?>
                </td>
                <td><?php esc_html_e( 'Required', 'w2s-migrate-woo-to-shopify' ) ?></td>
            </tr>
            <tr>
                <td data-export-label="file_put_contents">file_put_contents</td>
                <td>
					<?php
					if ( function_exists( 'file_put_contents' ) ) {
						?>
                        <mark class="yes">&#10004; <code class="private"></code></mark>
						<?php
					} else {
						?>
                        <mark class="error">&#10005;</mark>
						<?php
					}
					?>

                </td>
                <td><?php esc_html_e( 'Required', 'w2s-migrate-woo-to-shopify' ) ?></td>
            </tr>
            <tr>
                <td data-export-label="mkdir">mkdir</td>
                <td>
					<?php
					if ( function_exists( 'mkdir' ) ) {
						?>
                        <mark class="yes">&#10004; <code class="private"></code></mark>
						<?php
					} else {
						?>
                        <mark class="error">&#10005;</mark>
						<?php
					}
					?>

                </td>
                <td><?php esc_html_e( 'Required', 'w2s-migrate-woo-to-shopify' ) ?></td>
            </tr>
			<?php
			$max_execution_time = ini_get( 'max_execution_time' );
			$max_input_vars     = ini_get( 'max_input_vars' );
			$memory_limit       = ini_get( 'memory_limit' );
			?>
            <tr>
                <td data-export-label="<?php esc_attr_e( 'PHP Time Limit', 'w2s-migrate-woo-to-shopify' ) ?>"><?php esc_html_e( 'PHP Time Limit', 'w2s-migrate-woo-to-shopify' ) ?></td>
                <td style="<?php if ( $max_execution_time > 0 && $max_execution_time < 300 ) {
					echo esc_attr( 'color:red' );
				} ?>"><?php esc_html_e( $max_execution_time ); ?></td>
                <td><?php esc_html_e( '3000', 'w2s-migrate-woo-to-shopify' ) ?></td>
            </tr>
            <tr>
                <td data-export-label="<?php esc_attr_e( 'PHP Max Input Vars', 'w2s-migrate-woo-to-shopify' ) ?>"><?php esc_html_e( 'PHP Max Input Vars', 'w2s-migrate-woo-to-shopify' ) ?></td>
                <td style="<?php if ( $max_input_vars < 1000 ) {
					echo esc_attr( 'color:red' );
				} ?>"><?php esc_html_e( $max_input_vars ); ?></td>
                <td><?php esc_html_e( '10000', 'w2s-migrate-woo-to-shopify' ) ?></td>
            </tr>
            <tr>
                <td data-export-label="<?php esc_attr_e( 'Memory Limit', 'w2s-migrate-woo-to-shopify' ) ?>"><?php esc_html_e( 'Memory Limit', 'w2s-migrate-woo-to-shopify' ) ?></td>
                <td style="<?php if ( intval( $memory_limit ) < 64 ) {
					echo esc_attr( 'color:red' );
				} ?>"><?php esc_html_e( $memory_limit ); ?></td>
                <td><?php esc_html_e( '512M', 'w2s-migrate-woo-to-shopify' ) ?></td>
            </tr>
            </tbody>
        </table>
		<?php
	}

	public function page_callback_logs() {
		require plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/vi-w2s-admin-logs.php';
	}

	public function viw2s_page_setting_function() {
		require plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/vi-w2s-admin-display.php';
	}

	public static function upgrade_button() {
		?>
        <a href="https://1.envato.market/vnr5Nj"
           target="_blank"
           class="vi-ui button yellow"><?php esc_html_e( 'Upgrade this feature', 'w2s-migrate-woo-to-shopify' ) ?></a>
		<?php
	}

	public function viw2s_ajax_check_store( $viw2s_store_domain, $viw2s_store_api_key, $viw2s_store_api_secret ) {

		$status    = '';
		$api_error = '';

		if ( ! current_user_can( 'administrator' ) ) {
			return array(
				'status'    => 'not_admin',
				'api_error' => '',
			);
		}
		if ( empty( $viw2s_store_domain ) || empty( $viw2s_store_api_key ) || empty( $viw2s_store_api_secret ) ) {
			return array(
				'status'    => 'field_empty',
				'api_error' => '',
			);
		}
		$checkShopifyConnect = $this->setting->get_access_scopes( $viw2s_store_domain, $viw2s_store_api_key, $viw2s_store_api_secret );
//		try {
//
//			$checkShopifyConnect->Shop->get();
//			$status = 'success';
//		} catch ( Exception $exception ) {
//			$api_error = $exception->getMessage();
//		}

		return array(
			'status'    => $checkShopifyConnect['status'],
			'api_error' => $checkShopifyConnect['data'],
		);


	}

	public function viw2s_ajax_search_product() {
		$keysearch           = isset( $_POST['keysearch'] ) ? wc_clean( wp_unslash( $_POST['keysearch'] ) ) : '';
		$exclude_product_ids = isset( $_POST['exclude_product_ids'] ) ? wc_clean( wp_unslash( $_POST['exclude_product_ids'] ) ) : array();
		$product_args        = array(
			'status'         => 'publish',
			'post_type'      => 'product',
			'posts_per_page' => - 1,
			'post__not_in'   => $exclude_product_ids,
			's'              => $keysearch,
			'tax_query'      => array(
				array(
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => array( 'simple', 'variable' ),
				),
			),

		);
		$products_query      = new WP_Query( $product_args );
		$arr_data_product    = array();
		if ( $products_query->have_posts() ) {
			while ( $products_query->have_posts() ) : $products_query->the_post();
				array_push( $arr_data_product, array(
					"id"   => get_the_ID(),
					"text" => get_the_title(),
				) );
			endwhile;
		}
		$response = $arr_data_product;
		wp_send_json( $response );
		die();
	}

	public function viw2s_ajax_search_product_cat() {
		$keysearch = isset( $_POST['keysearch'] ) ? wc_clean( wp_unslash( $_POST['keysearch'] ) ) : '';

		$arr_tax = get_terms( array(
			'taxonomy'   => 'product_cat',
			'orderby'    => 'name',
			'order'      => 'ASC',
			'search'     => $keysearch,
			'hide_empty' => true,
			'fields'     => 'all',
		) );

		$items = array();
		if ( is_array( $arr_tax ) && ! empty( $arr_tax ) ) {
			foreach ( $arr_tax as $tax_item ) {
				$items[] = array(
					'id'   => $tax_item->slug,
					'text' => $tax_item->name
				);

			}
		}

		wp_send_json( $items );
		die();
	}


	public function viw2s_save_option_setting() {
		global $viw2s_settings;
		$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : '';

		if ( $page !== 'vi-w2s-woo-to-shopify' ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if (
			! isset( $_POST['_viw2s_save_setting_nonce'] ) ||
			! wp_verify_nonce( wc_clean( wp_unslash( $_POST['_viw2s_save_setting_nonce'] ) ), 'viw2s_action_save_setting_nonce' )
		) {
			return;
		}
		if ( isset( $_POST['viw2s-save-setting'] ) ) {
			$viw2s_params = get_option( 'viw2s_params ', false ) ? get_option( 'viw2s_params ' ) : $this->default_data;

			$viw2s_store_setting        = isset( $_POST['viw2s_store_setting'] ) ? wc_clean( wp_unslash( $_POST['viw2s_store_setting'] ) ) : array();
			$arr_import_products_option = isset( $_POST['viw2s_import_products_option'] ) ? wc_clean( wp_unslash( $_POST['viw2s_import_products_option'] ) ) : array();

			$arr_store_setting = array();

			if ( sizeof( $viw2s_store_setting ) > 0 ) {
				foreach ( $viw2s_store_setting as $store_item ) {
					$new_store_item           = $store_item;
					$parse_domain             = isset( $store_item['domain'] ) ? wc_clean( parse_url( $store_item['domain'] ) ) : array();
					$domain                   = isset( $parse_domain['host'] ) ? $parse_domain['host'] : $parse_domain['path'];
					$new_store_item['domain'] = $domain;
					$api_key                  = isset( $store_item['api_key'] ) ? wc_clean( $store_item['api_key'] ) : '';
					$api_secret               = isset( $store_item['api_secret'] ) ? wc_clean( $store_item['api_secret'] ) : '';

					if ( $domain && $api_key && $api_secret ) {

						$request = $this->setting->get_access_scopes( $domain, $api_key, $api_secret );
						if ( $request['status'] === 'success' ) {

							$new_store_item['validate'] = 1;

						} else {
							$new_store_item['validate'] = '';
						}

					} else {
						$new_store_item['validate'] = '';
					}
					array_push( $arr_store_setting, $new_store_item );
				}
			}

			$new_viw2s_params = array(
				'viw2s_store_setting'          => $arr_store_setting,
				'viw2s_import_products_option' => $arr_import_products_option
			);

			$new_viw2s_params = wp_parse_args( $new_viw2s_params, $viw2s_params );
			$this->setting->update_option( 'viw2s_params', $new_viw2s_params );
			$viw2s_settings = $new_viw2s_params;

		} else {
			return;
		}


	}

	public function viw2s_ajax_active_import() {
		$status = '';

		if ( defined( "DOING_AUTOSAVE" ) && DOING_AUTOSAVE ) {
			$status = 'error';
		}
		if ( wp_verify_nonce( $_POST['_viw2s_action_import_nonce'], 'viw2s_action_import_nonce' ) ) {
			$viw2s_setting_store = $this->setting->get_params( 'viw2s_store_setting' );
			if ( is_array( $viw2s_setting_store ) && ! empty( $viw2s_setting_store ) && isset( $viw2s_setting_store[0]['validate'] ) && $viw2s_setting_store[0]['validate'] ) {
				$viw2s_get_api_scope = $this->setting->get_access_scopes( $viw2s_setting_store[0]['domain'], $viw2s_setting_store[0]['api_key'], $viw2s_setting_store[0]['api_secret'] );
				if ( $viw2s_get_api_scope['status'] == 'success' ) {
					$viw2s_get_api_access_scope_handle = $this->setting->get_access_scopes_handle( $viw2s_setting_store[0]['domain'], $viw2s_setting_store[0]['api_key'], $viw2s_setting_store[0]['api_secret'] );
					if (
						! empty( $viw2s_get_api_access_scope_handle ) &&
						in_array( 'read_products', $viw2s_get_api_access_scope_handle ) &&
						in_array( 'write_products', $viw2s_get_api_access_scope_handle )
					) {
						$viw2s_get_all_product_data      = $this->setting->viw2s_get_all_product_data();
						$viw2s_get_all_product_cats_data = $this->setting->viw2s_get_all_product_cats();
						update_option( 'viw2s_importing_arr_product', $viw2s_get_all_product_data );
						update_option( 'viw2s_importing_arr_product_categories', $viw2s_get_all_product_cats_data );
						$status = 'success';
						if ( ! empty( $viw2s_get_all_product_data ) ) {
							$viw2s_item_product = $viw2s_get_all_product_data[0];

							$response = array(
								'status'                      => $status,
								'total_products'              => count( $viw2s_get_all_product_data ),
								'total_categories'            => count( $viw2s_get_all_product_cats_data ),
								'current_import_product_type' => $viw2s_item_product["type"] ?? '',
								'current_import_product_id'   => $viw2s_item_product["id"] ?? '',
								'product_index'               => 0,
								'categories_index'            => 0,
								'all_product_data'            => $viw2s_get_all_product_data,
								'all_product_cats_data'       => $viw2s_get_all_product_cats_data
							);
						} else {

							$response = array(
								'status'           => $status,
								'logs'             => '<div><strong class="important_alert">' . esc_html__( 'Not find products to import', 'w2s-migrate-woo-to-shopify' ) . '</strong></div>',
								'total_products'   => 0,
								'total_categories' => count( $viw2s_get_all_product_cats_data ),
							);
						}


					} else {
						$status   = 'permission_not_correct';
						$response = array(
							'status' => $status,
							'logs'   => '<div><strong class="important_alert">' . esc_html__( 'API permission not correct !!!', 'w2s-migrate-woo-to-shopify' ) . '</strong></div>'
						);
					}

				} else {
					$status   = 'error_access_scope';
					$response = array(
						'status' => $status,
					);
				}

			} else {
				$response = array(
					'status' => 'store_setting_error',

				);
			}

		} else {
			$status   = 'error_nonce';
			$response = array(
				'status' => $status,

			);
		}

		wp_send_json( $response );
		die();
	}

	public function viw2s_ajax_import_action() {
		$response = array();
		if ( wp_verify_nonce( wc_clean( wp_unslash( $_POST['_viw2s_action_import_nonce'] ) ), 'viw2s_action_import_nonce' ) ) {
			$viw2s_setting_store = $this->setting->get_params( 'viw2s_store_setting' );
			$domain              = $viw2s_setting_store[0]['domain'] ?? '';
			$api_key             = $viw2s_setting_store[0]['api_key'] ?? '';
			$api_secret          = $viw2s_setting_store[0]['api_secret'] ?? '';
			$viw2s_get_api_scope = $this->setting->get_access_scopes( $domain, $api_key, $api_secret );
			if (
				is_array( $viw2s_get_api_scope ) &&
				isset( $viw2s_get_api_scope['status'] ) &&
				$viw2s_get_api_scope['status'] === 'success'
			) {

				$viw2s_get_api_access_scope_handle = $this->setting->get_access_scopes_handle( $domain, $api_key, $api_secret );
				if (
					! empty( $viw2s_get_api_access_scope_handle ) &&
					in_array( 'read_products', $viw2s_get_api_access_scope_handle ) &&
					in_array( 'write_products', $viw2s_get_api_access_scope_handle )
				) {
					$config          = array(
						'ShopUrl'  => $domain,
						'ApiKey'   => $api_key,
						'Password' => $api_secret
					);
					$VIW2SShopifySDK = PHPShopify\ShopifySDK::config( $config );

					$path = VI_W2S_IMPORT_WOOCOMMERCE_TO_SHOPIFY_DATA::get_cache_path( $domain, $api_key, $api_secret ) . '/';
					VI_W2S_IMPORT_WOOCOMMERCE_TO_SHOPIFY_DATA::create_cache_folder( $path );
					$log_file = $path . 'logs.txt';
					$step     = isset( $_POST['step'] ) ? wc_clean( wp_unslash( $_POST['step'] ) ) : '';
					$logs     = '';
					switch ( $step ) {
						case 'products':
							$get_all_importing_arr_product = get_option( 'viw2s_importing_arr_product', array() );
							$product_index                 = isset( $_POST['product_index'] ) ? wc_clean( wp_unslash( $_POST['product_index'] ) ) : '';
							$current_import_product_id     = isset( $_POST['current_import_product_id'] ) ? wc_clean( wp_unslash( $_POST['current_import_product_id'] ) ) : '';
							$current_import_product_type   = isset( $_POST['current_import_product_type'] ) ? wc_clean( wp_unslash( $_POST['current_import_product_type'] ) ) : '';
							$product_log                   = $path . 'products.txt';
							$list_products_imported_path   = $path . '/list_products_imported.txt';
							$ids_products_imported_path    = $path . '/ids_product_imported.txt';
							$list_products_imported        = [];
							$ids_products_imported         = [];
							if ( is_file( $list_products_imported_path ) ) {
								$list_products_imported = json_decode( file_get_contents( $list_products_imported_path ), true );
							}
							if ( is_file( $ids_products_imported_path ) ) {
								$ids_products_imported = json_decode( file_get_contents( $ids_products_imported_path ), true );
							}
							if (
								! empty( $get_all_importing_arr_product ) &&
								( $product_index !== '' )
							) {
								$viw2s_total_product = count( $get_all_importing_arr_product );
								$product_index ++;
								if ( $product_index <= $viw2s_total_product ) {
									$viw2s_item_product                  = $get_all_importing_arr_product[ $product_index - 1 ];
									$viw2s_item_product_data             = $this->setting->viw2s_product_data_format( $viw2s_item_product["id"], $viw2s_item_product["type"] );
									$viw2s_import_product_images         = array();
									$viw2s_new_import_product_images     = array();
									$viw2s_import_product_progress_label = 'Importing...';

									$error_product_code  = '';
									$error_image_code    = '';
									$viw2s_import_status = '';
									$log                 = array(
										'shopify_id' => '',
										'woo_id'     => $viw2s_item_product["id"],
										'title'      => esc_html__( get_the_title( $viw2s_item_product["id"] ), 'w2s-migrate-woo-to-shopify' ),
										'message'    => '',
									);
									try {
										switch ( $viw2s_item_product_data['status'] ) {
											case 'success':
												$viw2s_import_status                 = $VIW2SShopifySDK->Product->post( $viw2s_item_product_data['data'] );
												$viw2s_import_product_progress_label = sprintf( esc_html__( 'Importing... %s /%s completed', 'w2s-migrate-woo-to-shopify' ), $product_index, $viw2s_total_product );
												$log['shopify_id']                   = $viw2s_import_status['id'];
												$log['message']                      = esc_html__( 'Import successfully', 'w2s-migrate-woo-to-shopify' );
												/*Update data to product metadata*/
												$_w2s_shopify_data = get_post_meta( $viw2s_item_product["id"], '_w2s_shopify_data', true );
												if ( ! empty( $_w2s_shopify_data ) && is_array( $_w2s_shopify_data ) ) {
													$_w2s_shopify_data[ $domain ] = array(
														'_w2s_shopify_product_id' => $viw2s_import_status['id'],
														'_w2s_shopify_variant_id' => $viw2s_import_status['variants'][0]['id'],
														'status'                  => 'imported'
													);
													update_post_meta( $viw2s_item_product["id"], '_w2s_shopify_data', $_w2s_shopify_data );
												} else {
													update_post_meta( $viw2s_item_product["id"], '_w2s_shopify_data', array(
														$domain => array(
															'_w2s_shopify_product_id' => $viw2s_import_status['id'],
															'_w2s_shopify_variant_id' => $viw2s_import_status['variants'][0]['id'],
															'status'                  => 'imported'
														)
													) );
												}
												/*Update data to product metadata for children product*/
												if ( $viw2s_item_product['type'] == 'variable' && ! empty( $viw2s_item_product["children_ids"] ) ) {
													$shopify_variants = $viw2s_import_status['variants'];

													foreach ( $viw2s_item_product["children_ids"] as $children_key => $children_id ) {
														$_w2s_shopify_data_children = get_post_meta( $children_id, '_w2s_shopify_data', true );

														if ( ! empty( $_w2s_shopify_data_children ) && is_array( $_w2s_shopify_data_children ) ) {
															$_w2s_shopify_data_children[ $domain ] = array(
																'_w2s_shopify_product_id' => $viw2s_import_status['id'],
																'_w2s_shopify_variant_id' => $shopify_variants[ $children_key ]['id'],
																'status'                  => 'imported'
															);
															update_post_meta( $children_id, '_w2s_shopify_data', $_w2s_shopify_data_children );
														} else {
															update_post_meta( $children_id, '_w2s_shopify_data', array(
																$domain => array(
																	'_w2s_shopify_product_id' => $viw2s_import_status['id'],
																	'_w2s_shopify_variant_id' => $shopify_variants[ $children_key ]['id'],
																	'status'                  => 'imported'
																)
															) );
														}
													}

												}
												/*Clean $list_products_imported */
												if ( ! empty( $list_products_imported ) && is_array( $list_products_imported ) ) {

													$list_products_imported = array_unique( $list_products_imported, SORT_NUMERIC );
													$key_old_not_exist      = array_search( $viw2s_item_product["id"], $list_products_imported );
													if ( $key_old_not_exist ) {
														unset( $list_products_imported[ $key_old_not_exist ] );
													}
												}

												if ( is_array( $viw2s_import_status ) && key_exists( 'id', $viw2s_import_status ) ) {
													$list_products_imported[ $viw2s_import_status['id'] ] = $viw2s_item_product["id"]; //Set to file array('shopify_id'=>'woo_id')
												}

												/*log to file list_product_imported*/
												file_put_contents( $list_products_imported_path, wp_json_encode( $list_products_imported ) );

												/*log to file ids_product_imported*/
												$ids_products_imported[] = $viw2s_item_product['id'];
												$ids_products_imported   = array_unique( $ids_products_imported );
												file_put_contents( $ids_products_imported_path, wp_json_encode( $ids_products_imported ) );
												break;
											case 'attribute_has_any_product':
											case 'dont_has_any_product':
												$viw2s_import_status = 'error';
												$log['product_url']  = get_edit_post_link( $viw2s_item_product["id"] );

												$log['message'] = esc_html__( 'Products with variants have not been entered correctly', 'w2s-migrate-woo-to-shopify' );

												$viw2s_import_product_progress_label = sprintf( esc_html__( 'Importing... %s /%s was skipped', 'w2s-migrate-woo-to-shopify' ), $product_index, $viw2s_total_product );
												break;
											case 'too_much_attributes':
												$viw2s_import_status = 'error';
												$log['product_url']  = get_edit_post_link( $viw2s_item_product["id"] );

												$log['message'] = esc_html__( 'products with attribute count more than 3', 'w2s-migrate-woo-to-shopify' );


												$viw2s_import_product_progress_label = sprintf( esc_html__( 'Importing... %s /%s was skipped', 'w2s-migrate-woo-to-shopify' ), $product_index, $viw2s_total_product );
												break;
											case 'exist':
												$_w2s_shopify_data = get_post_meta( $viw2s_item_product["id"], '_w2s_shopify_data', true );
												if (
													! empty( $_w2s_shopify_data ) &&
													is_array( $_w2s_shopify_data ) &&
													isset( $_w2s_shopify_data[ $domain ] ) &&
													isset( $_w2s_shopify_data[ $domain ]['_w2s_shopify_product_id'] )
												) {
													$log['product_url'] = esc_url( $domain . '/admin/products/' . $_w2s_shopify_data[ $domain ]['_w2s_shopify_product_id'] );
												}
												$viw2s_import_status = 'exist';

												$log['message']                      = esc_html__( 'The product has been imported to shopify', 'w2s-migrate-woo-to-shopify' );
												$viw2s_import_product_progress_label = sprintf( esc_html__( 'Importing... %s /%s was skipped', 'w2s-migrate-woo-to-shopify' ), $product_index, $viw2s_total_product );
												break;
										}
										$status = 'successful';

									} catch ( Exception $exception ) {
										$viw2s_import_status                 = $exception->getMessage();
										$error_product_code                  = $exception->getCode();
										$viw2s_import_product_progress_label = sprintf( esc_html__( 'Importing... %s /%s error', 'w2s-migrate-woo-to-shopify' ), $product_index, $viw2s_total_product );

										$log['message'] = esc_html__( 'Import successfully', 'w2s-migrate-woo-to-shopify' );
										$status         = 'error_access_scope';
									}
									try {
										if ( $viw2s_item_product_data['status'] == 'success' ) {

											$viw2s_shopify_product_id    = $viw2s_import_status['id'] ?? '';
											$viw2s_import_product_images = $viw2s_item_product_data['data_images'];
											if ( ! empty( $viw2s_import_status ) && is_array( $viw2s_import_status ) && isset( $viw2s_import_status['variants'] ) ) {
												$shopify_arr_variant = $viw2s_import_status['variants'];

												foreach ( $shopify_arr_variant as $index_variant => $shopify_variant_item ) {
													$shopify_variant_id                                           = $shopify_variant_item['id'];
													$viw2s_import_product_images[ $index_variant ]['variant_ids'] = array( $shopify_variant_id );
												}
											}

											if ( ! empty( $viw2s_import_product_images ) ) {
												foreach ( $viw2s_import_product_images as $v1 ) {
													if ( isset( $viw2s_new_import_product_images[ $v1['src'] ] ) ) {
														if ( isset( $v1['variant_ids'] ) && is_array( $viw2s_new_import_product_images[ $v1['src'] ]['variant_ids'] ) && ! empty( $v1['variant_ids'] ) ) {
															array_push( $viw2s_new_import_product_images[ $v1['src'] ]['variant_ids'], $v1['variant_ids'][0] );
														}
													} else {
														$viw2s_new_import_product_images[ $v1['src'] ]['src'] = $v1['src'] ?? [];
														if ( isset( $v1['variant_ids'] ) && is_array( $v1['variant_ids'] ) && ! empty( $v1['variant_ids'] ) ) {
															$viw2s_new_import_product_images[ $v1['src'] ]['variant_ids'] = $v1['variant_ids'];
														}
													}
												}
												$viw2s_import_image_status = $VIW2SShopifySDK->Product( $viw2s_shopify_product_id )->put( [ 'images' => array_values( $viw2s_new_import_product_images ) ] );
											} else {
												$viw2s_import_image_status = 'empty';
											}

										} else {

											$viw2s_import_image_status = 'error';
										}
									} catch ( Exception $exception ) {
										$error_image_code          = $exception->getCode();
										$viw2s_import_image_status = $exception->getMessage();
									}
									$logs_content = $log['title'] . ": " . $log['message'] . ", Shopify product ID: " . $log['shopify_id'] . ", WC product ID: " . $log['woo_id'];
									VI_W2S_IMPORT_WOOCOMMERCE_TO_SHOPIFY_DATA::viw2s_log( $log_file, $logs_content );
									if ( $status === 'error_access_scope' ) {
										$logs = '<strong class="important_alert"> ' . $viw2s_import_status . '</strong>';

									} else {
										$logs .= ' <div>' . $log['title'] . ': <strong > ' . $log['message'] . ' .</strong > ' . ( isset( $log['product_url'] ) ? ' <a href = "' . esc_url( $log['product_url'] ) . '" target = "_blank" rel = "nofollow" > View & edit </a> ' : '' ) . ' </div> ';
									}
//						$new_item_product = $get_all_importing_arr_product[ $product_index ];
									$response = array(
										'logs'                            => $logs,
										'viw2s_import_status'             => $viw2s_import_status,
										'error_product_code'              => $error_product_code,
										'error_image_code'                => $error_image_code,
										'viw2s_import_image_status'       => $viw2s_import_image_status,
										'status'                          => $status,
										'imported_products'               => $product_index,
										'current_import_product_type'     => $viw2s_item_product["type"],
										'current_import_product_id'       => $viw2s_item_product["id"],
										'viw2s_get_all_product_data'      => $viw2s_item_product_data,
										'viw2s_new_import_product_images' => array_values( $viw2s_new_import_product_images ),
										'viw2s_import_product_images'     => $viw2s_import_product_images,
										'message'                         => $viw2s_import_product_progress_label,
									);
								} else {
									$logs     = '/*============================================================================*/';
									$response = array(
										'status'            => 'finish',
										'logs'              => $logs,
										'imported_products' => $product_index,
										'code'              => 'no_data',
										'message'           => sprintf( esc_html__( 'Completed % s /%s ', 'w2s-migrate-woo-to-shopify' ), $viw2s_total_product, $viw2s_total_product ),
									);
								}

							} else {
								$response = array(
									'status'  => 'error',
									'code'    => 'no_data',
									'message' => esc_html__( 'Import error', 'w2s-migrate-woo-to-shopify' ),

								);
							}

							break;
						case 'product_categories':
							$get_all_importing_arr_product_categories = get_option( 'viw2s_importing_arr_product_categories', array() );
							$categories_index                         = isset( $_POST['categories_index'] ) ? wc_clean( wp_unslash( $_POST['categories_index'] ) ) : '';
							$categories_import_status                 = isset( $_POST['status'] ) ? wc_clean( wp_unslash( $_POST['status'] ) ) : '';
							$viw2s_import_product_cat_progress_label  = 'Importing...';
							$logs                                     = '';
							$log                                      = array(
								'shopify_id'         => '',
								'woo_product_cat_id' => '',
								'title'              => '',
								'message'            => '',
							);
							$product_category_log                              = $path . 'product_categories.txt';
							$list_product_categories_imported_path             = $path . '/list_product_categories_imported.txt';
							$list_product_categories_imported                  = [];
							if ( is_file( $list_product_categories_imported_path ) ) {
								$list_product_categories_imported = json_decode( file_get_contents( $list_product_categories_imported_path ), true );
							}
							if (
								! empty( $get_all_importing_arr_product_categories ) &&
								( $categories_index !== '' )
							) {
								$viw2s_total_product_categories = count( $get_all_importing_arr_product_categories );
								$categories_index ++;

								if ( $categories_index <= $viw2s_total_product_categories ) {
									$viw2s_item_product_cat          = $get_all_importing_arr_product_categories[ $categories_index - 1 ];
									$viw2s_item_product_cat_data     = $this->setting->viw2s_format_product_cats( $viw2s_item_product_cat );
									$viw2s_import_product_cat_status = '';
									$code                            = '';
									$status                          = '';
									$log['woo_product_cat_id']       = $viw2s_item_product_cat['term_id'];
									$log['title']                    = esc_html__( $viw2s_item_product_cat['title'], 'w2s-migrate-woo-to-shopify' );
									if ( $viw2s_item_product_cat['status'] === 'success' ) {
										try {
											$viw2s_import_product_cat_status = $VIW2SShopifySDK->CustomCollection()->post( $viw2s_item_product_cat_data );

											$status                                  = 'success';
											$log['message']                          = esc_html__( 'Import successfully', 'w2s-migrate-woo-to-shopify' );
											$viw2s_import_product_cat_progress_label = sprintf( esc_html__( 'Importing... %s /%s completed', 'w2s-migrate-woo-to-shopify' ), $categories_index, $viw2s_total_product_categories );
											$_w2s_shopify_cats_data                  = get_term_meta( $viw2s_item_product_cat['term_id'], '_w2s_shopify_data', true );
											if ( ! empty( $_w2s_shopify_cats_data ) && is_array( $_w2s_shopify_cats_data ) ) {
												$_w2s_shopify_cats_data[ $domain ] = array(
													'_w2s_shopify_product_cat_id' => $viw2s_import_product_cat_status['id'],
													'status'                      => 'imported'
												);
												update_term_meta( $viw2s_item_product_cat['term_id'], '_w2s_shopify_data', $_w2s_shopify_cats_data );
											} else {
												update_term_meta( $viw2s_item_product_cat['term_id'], '_w2s_shopify_data', array(
													$domain => array(
														'_w2s_shopify_product_cat_id' => $viw2s_import_product_cat_status['id'],
														'status'                      => 'imported'
													)
												) );
											}
											/*Clean $list_product_categories_imported */
											if ( ! empty( $list_product_categories_imported ) && is_array( $list_product_categories_imported ) ) {
												$list_product_categories_imported = array_unique( $list_product_categories_imported );
												$key_old_not_exist                = array_search( $viw2s_item_product_cat["term_id"], $list_product_categories_imported );
												if ( $key_old_not_exist ) {
													unset( $list_product_categories_imported[ $key_old_not_exist ] );
												}
											}
											$list_product_categories_imported[ $viw2s_import_product_cat_status['id'] ] = $viw2s_item_product_cat['term_id'];
											/*log to file list_product_categories_imported*/
											file_put_contents( $list_product_categories_imported_path, wp_json_encode( $list_product_categories_imported ) );
										} catch ( Exception $exception ) {
											$code                                    = $exception->getCode();
											$viw2s_import_product_cat_status         = $exception->getMessage();
											$status                                  = 'error';
											$log['message']                          = esc_html__( $exception->getMessage(), 'w2s-migrate-woo-to-shopify' );
											$viw2s_import_product_cat_progress_label = sprintf( esc_html__( 'Importing... %s /%s error', 'w2s-migrate-woo-to-shopify' ), $categories_index, $viw2s_total_product_categories );

										}
									} else if ( $viw2s_item_product_cat['status'] == 'exist' ) {
										$viw2s_import_product_cat_progress_label = sprintf( esc_html__( 'Importing... %s /%s skipped', 'w2s-migrate-woo-to-shopify' ), $categories_index, $viw2s_total_product_categories );
										$log['message']                          = esc_html__( 'This product category has been imported to shopify', 'w2s-migrate-woo-to-shopify' );
										$viw2s_import_product_cat_status         = get_term_meta( $viw2s_item_product_cat['term_id'], '_w2s_shopify_data', true );
										$status                                  = 'exist';
									} else {
										$viw2s_import_product_cat_progress_label = sprintf( esc_html__( 'Importing... %s /%s skipped', 'w2s-migrate-woo-to-shopify' ), $categories_index, $viw2s_total_product_categories );
										$log['message']                          = esc_html__( 'This product category has been import error ', 'w2s-migrate-woo-to-shopify' );
										$viw2s_import_product_cat_status         = get_term_meta( $viw2s_item_product_cat['term_id'], '_w2s_shopify_data', true );
										$status                                  = 'error';
									}
									$logs     .= ' <div>' . $log['title'] . ': <strong > ' . $log['message'] . ' .</strong ></div> ';
									$response = array(
										'status'                          => $status,
										'logs'                            => $logs,
										'categories_index'                => $categories_index,
										'total_categories'                => $viw2s_total_product_categories,
										'viw2s_import_product_cat_status' => $viw2s_import_product_cat_status,
										'viw2s_item_product_cat_data'     => $viw2s_item_product_cat_data,
										'code'                            => $code,
										'message'                         => $viw2s_import_product_cat_progress_label,
									);
								} else {
									$logs     = '/*============================================================================*/';
									$response = array(
										'status'           => 'finish',
										'logs'             => $logs,
										'categories_index' => $categories_index - 1,
										'total_categories' => $viw2s_total_product_categories,
										'code'             => 'no_data',
										'message'          => sprintf( esc_html__( 'Completed % s /%s ', 'w2s-migrate-woo-to-shopify' ), $categories_index - 1, $viw2s_total_product_categories ),
									);
								}
							} else {
								$response = array(
									'status'  => 'error',
									'code'    => 'no_data',
									'message' => esc_html__( 'Import error, please try again! ', 'w2s-migrate-woo-to-shopify' ),

								);
							}
							break;
						default:
							throw new \Exception( 'Unexpected value' );
					}
				} else {
					$logs     = '<strong class="important_alert"> ' . esc_html__( 'API permission must have product write permission!', 'w2s-migrate-woo-to-shopify' ) . '</strong>';
					$response = array(
						'status'  => 'error_access_scope',
						'logs'    => $logs,
						'code'    => 'no_data',
						'message' => esc_html__( 'API permission must have product write permission', 'w2s-migrate-woo-to-shopify' ),
					);
				}

			} else {
				$logs     = '<strong class="important_alert"> ' . esc_html__( $viw2s_get_api_scope['data'] ?? 'Store setting error', 'w2s-migrate-woo-to-shopify' ) . '</strong>';
				$response = array(
					'status'  => 'error_store_setting',
					'logs'    => $logs,
					'code'    => 'no_data',
					'message' => esc_html__( 'Store setting error', 'w2s-migrate-woo-to-shopify' ),
				);
			}

		} else {
			$logs     = '<strong class="important_alert"> ' . esc_html__( 'Import nonce error', 'w2s-migrate-woo-to-shopify' ) . '</strong>';
			$response = array(
				'status'  => 'error_none',
				'logs'    => $logs,
				'code'    => 'no_data',
				'message' => esc_html__( 'Import nonce error', 'w2s-migrate-woo-to-shopify' ),
			);
		}
		wp_send_json( $response );
		die();
	}

	public function print_log_html( $logs ) {
		if ( is_array( $logs ) && count( $logs ) ) {
			foreach ( $logs as $log ) {
				?>
                <p><?php echo esc_html( $log ) ?>
                    <a target="_blank" rel="nofollow"
                       href="<?php echo esc_url( add_query_arg( array(
						   'action'     => 'viw2s_view_log',
						   'viw2s_file' => urlencode( $log ),
						   '_wpnonce'   => wp_create_nonce( 'viw2s_view_log' ),
					   ), admin_url( 'admin-ajax.php' ) ) ) ?>"><?php esc_html_e( 'View', 'w2s-migrate-woo-to-shopify' ) ?>
                    </a>
                </p>
				<?php
			}
		}
	}

	public function generate_log_ajax() {
		/*Check the nonce*/
		if ( empty( $_GET['action'] ) || ! check_admin_referer( $_GET['action'] ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'w2s-migrate-woo-to-shopify' ) );
		}
		if ( empty( $_GET['viw2s_file'] ) ) {
			wp_die( esc_html__( 'No log file selected.', 'w2s-migrate-woo-to-shopify' ) );
		}
		$file = urldecode( wc_clean( $_GET['viw2s_file'] ) );
		if ( ! is_file( $file ) ) {
			wp_die( esc_html__( 'Log file not found.', 'w2s-migrate-woo-to-shopify' ) );
		}
		echo( wp_kses_post( nl2br( file_get_contents( $file ) ) ) );
		exit();
	}
}
