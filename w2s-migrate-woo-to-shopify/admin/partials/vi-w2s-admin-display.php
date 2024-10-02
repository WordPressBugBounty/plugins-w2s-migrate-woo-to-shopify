<?php
$VIW2S_Data_default           = new VI_W2S_IMPORT_WOOCOMMERCE_TO_SHOPIFY_DATA();
$viw2s_setting_params_default = $VIW2S_Data_default->get_default();
$viw2s_setting_params         = get_option( 'viw2s_params', false ) ? get_option( 'viw2s_params' ) : $viw2s_setting_params_default;

$store_setting                     = $VIW2S_Data_default->get_params( 'viw2s_store_setting' );
$active                            = false;
$viw2s_get_api_access_scope_handle = array();
$domain                            = '';
$api_key                           = '';
$api_secret                        = '';
if ( isset( $store_setting ) && is_array( $store_setting ) && ( count( $store_setting ) > 0 ) ) {
	$domain                            = $store_setting[0]['domain'] ?? '';
	$api_key                           = $store_setting[0]['api_key'] ?? '';
	$api_secret                        = $store_setting[0]['api_secret'] ?? '';
	$config                            = array(
		'ShopUrl'  => $domain,
		'ApiKey'   => $api_key,
		'Password' => $api_secret
	);
	$viw2s_get_api_access_scope_handle = $VIW2S_Data_default->get_access_scopes_handle( $domain, $api_key, $api_secret );
	if (
		isset( $store_setting[0]['validate'] ) &&
		$store_setting[0]['validate']
	) {
		$active = true;
	}

}

?>

<div class="wrap">
    <h2><?php esc_html_e( 'Migrate WooCommerce to Shopify', 'w2s-migrate-woo-to-shopify' ); ?></h2>

	<?php $this->security_recommendation_html() ?>
    <p></p>

    <div class="vi-ui styled fluid accordion ">
        <div class='title'>
            <i class="dropdown icon"></i>
            <span><?php esc_html_e( 'General settings', 'w2s-migrate-woo-to-shopify' ); ?></span>
        </div>
        <div class="content <?php if ( ! $active || empty( $viw2s_get_api_access_scope_handle ) || ! in_array( 'write_products', $viw2s_get_api_access_scope_handle ) ) {
			echo esc_attr( 'active' );
		} ?> ">
            <form class="vi-ui form" method="post" action="" id="viw2s_setting_form">
				<?php wp_nonce_field( 'viw2s_action_save_setting_nonce', '_viw2s_save_setting_nonce' ); ?>

                <table class="vi-ui compact celled stackable table center aligned" id="table_store_info">
                    <thead>
                    <tr>
                        <th>
							<?php esc_html_e( 'Store address', 'w2s-migrate-woo-to-shopify' ); ?>
                            <span class="viw2s-help-tip"
                                  data-tip="<?php esc_attr_e( 'This is store address Eg: myshop.myshopify.com', 'w2s-migrate-woo-to-shopify' ) ?>"></span>
                        </th>
                        <th>
							<?php esc_html_e( 'API key', 'w2s-migrate-woo-to-shopify' ); ?>
                            <span class="viw2s-help-tip"
                                  data-tip="<?php esc_attr_e( 'This is api key', 'w2s-migrate-woo-to-shopify' ) ?>"></span>
                        </th>
                        <th>
							<?php esc_html_e( 'API access token or API secret', 'w2s-migrate-woo-to-shopify' ); ?>
                            <span class="viw2s-help-tip"
                                  data-tip="<?php esc_attr_e( 'This is API access token( with custom app) or API secret ( with private app)', 'w2s-migrate-woo-to-shopify' ) ?>"></span>
                        </th>
                        <th>
							<?php esc_html_e( 'Action', 'w2s-migrate-woo-to-shopify' ); ?>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
					<?php
					if (
						! empty( $store_setting ) &&
						is_array( $store_setting ) &&
						( count( $store_setting ) > 0 )
					) {
						$count_store = 0;
						foreach ( wc_clean( $store_setting ) as $store_item ) {
						    if($count_store > 0){
						        break;
                            }
							$store_address          = isset( $store_item['domain'] ) ? wc_clean( $store_item['domain'] ) : '';
							$api_key                = isset( $store_item['api_key'] ) ? wc_clean( $store_item['api_key'] ) : '';
							$api_secret             = isset( $store_item['api_secret'] ) ? wc_clean( $store_item['api_secret'] ) : '';
							$store_validate         = isset( $store_item['validate'] ) ? wc_clean( $store_item['validate'] ) : false;
							$class_error_domain     = '';
							$class_error_api_key    = '';
							$class_error_api_secret = '';

							$text_error_domain     = '';
							$text_error_api_key    = '';
							$text_error_api_secret = '';
							if ( ! $store_validate ) {
								$warning      = $VIW2S_Data_default->get_access_scopes( $store_address, $api_key, $api_secret );
								$warning_code = isset( $warning['code'] ) ? $warning['code'] : '';
								switch ( $warning_code ) {
									case 'http_request_failed':
										$class_error_domain = 'error';
										$text_error_domain  = isset( $warning['data'] ) ? $warning['data'] : 'error';
										break;
									case '403':
										$class_error_api_key = 'error';
										$text_error_api_key  = isset( $warning['data'] ) ? $warning['data'] : 'error';
										break;
									case '401':
										$class_error_api_secret = 'error';
										$class_error_api_key    = 'error';
										$text_error_api_key     = isset( $warning['data'] ) ? $warning['data'] : 'error';
										$text_error_api_secret  = isset( $warning['data'] ) ? $warning['data'] : 'error';
										break;
								}


							}

							?>
                            <tr>
                                <td data-label="Store address" class="<?php echo esc_attr( $class_error_domain ); ?>">
                                    <input type="text"
                                           name="viw2s_store_setting[<?php echo esc_attr( $count_store ); ?>][domain]"
                                           class="viw2s_store_domain"
                                           id="viw2s_domain-<?php echo esc_attr( $count_store ); ?>"
                                           value="<?php echo esc_attr( $store_address ); ?>"
                                           placeholder="eg: myshop.myshopify.com">
                                    <label for="viw2s_domain-<?php echo esc_attr( $count_store ); ?>"></label>
									<?php
									if ( ! empty( $text_error_domain ) ) {
										?>
                                        <div>
                                            <i class="attention icon"></i><?php esc_html_e( $text_error_domain, 'w2s-migrate-woo-to-shopify' ); ?>
                                        </div>
										<?php
									}
									?>

                                </td>
                                <td data-label="API key" class="<?php echo esc_attr( $class_error_api_key ); ?>">
                                    <input type="text"
                                           name="viw2s_store_setting[<?php echo esc_attr( $count_store ); ?>][api_key]"
                                           class="viw2s_store_api_key"
                                           id="viw2s_api_key-<?php echo esc_attr( $count_store ); ?>"
                                           value="<?php echo esc_attr( $api_key ); ?>"
                                    >
                                    <label for="viw2s_api_key-<?php echo esc_attr( $count_store ); ?>"></label>
									<?php
									if ( ! empty( $text_error_api_key ) ) {
										?>
                                        <div>
                                            <i class="attention icon"></i><?php esc_html_e( $text_error_api_key, 'w2s-migrate-woo-to-shopify' ); ?>
                                        </div>
										<?php
									}
									?>
                                </td>
                                <td data-label="API secret(Password)"
                                    class="<?php echo esc_attr( $class_error_api_secret ); ?>">
                                    <input type="text"
                                           name="viw2s_store_setting[<?php echo esc_attr( $count_store ); ?>][api_secret]"
                                           class="viw2s_store_api_secret"
                                           id="viw2s_api_secret-<?php echo esc_attr( $count_store ); ?>"
                                           value="<?php echo esc_attr( $api_secret ); ?>">
                                    <label for="viw2s_api_secret-<?php echo esc_attr( $count_store ); ?>"></label>
									<?php
									if ( ! empty( $text_error_api_secret ) ) {
										?>
                                        <div>
                                            <i class="attention icon"></i><?php esc_html_e( $text_error_api_secret, 'w2s-migrate-woo-to-shopify' ); ?>
                                        </div>
										<?php
									}
									?>
                                </td>
                                <td data-label="Action">
                                    <div class="viw2s-wrap_btn">
                                        <a href="#" class="vi-ui red basic compact icon button remove-store">
                                            <i class="icon trash alternate outline"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
							<?php
							$count_store ++;
						}
					} else {
						?>
                        <tr>
                            <td data-label="Store address">
                                <input type="text" name="viw2s_store_setting[0][domain]"
                                       class="viw2s_store_domain"
                                       id="viw2s_domain-0"
                                       value=""
                                       placeholder="eg: myshop.myshopify.com"
                                >
                                <label for="viw2s_domain-0"></label>
                            </td>
                            <td data-label="API key">
                                <input type="text" name="viw2s_store_setting[0][api_key]"
                                       class="viw2s_store_api_key"
                                       id="viw2s_api_key-0"
                                       value=""
                                >
                                <label for="viw2s_api_key-0"></label>
                            </td>
                            <td data-label="API secret(Password)">
                                <input type="text" name="viw2s_store_setting[0][api_secret]"
                                       class="viw2s_store_api_secret"
                                       id="viw2s_api_secret-0"
                                       value="">
                                <label for="viw2s_api_secret-0"></label>
                            </td>
                            <td data-label="Action">
                                <div class="viw2s-wrap_btn">
                                    <a href="#" class="vi-ui red basic compact icon button remove-store">
                                        <i class="icon trash alternate outline"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
					<?php } ?>

                    </tbody>
                    <tfoot>
                    <tr>
                        <th colspan="3">
                            <div class="viw2s-error-warning"
                                 style="<?php if ( $active )
								     echo esc_attr( 'display:none' ) ?>">
                                <div class="vi-ui negative message">
									<?php esc_html_e( 'You need to enter correct all domain, API key and API secret to be able to import', 'w2s-migrate-woo-to-shopify' ); ?>
                                </div>
                            </div>
							<?php
							if (
								$active &&
								( empty( $viw2s_get_api_access_scope_handle ) ||
								  ! in_array( 'write_products', $viw2s_get_api_access_scope_handle ) )
							) {
								?>
                                <div class="viw2s-permission-warning">
                                    <div class="vi-ui negative message">
										<?php esc_html_e( 'API permission must have product write permission!', 'w2s-migrate-woo-to-shopify' ); ?>
                                    </div>
                                </div>
								<?php
							}
							?>
                        </th>
                        <th>
                            <div class="vi-wrap-add-button">
                                <button class="vi-ui green labeled icon button tiny viw2s-add-store-options">
                                    <i class="icon add"></i><?php esc_html_e( 'Add store', 'w2s-migrate-woo-to-shopify' ); ?>
                                </button>
                            </div>
                        </th>
                    </tr>
                    </tfoot>
                </table>
                <!--Guide video get api key-->
                <div class="title active">
                    <i class="dropdown icon"></i>
					<?php esc_html_e( 'Learn how to get API key', 'import-shopify-to-woocommerce' ) ?>
                </div>
                <div class="content active">
                    <div class="w2s-guide-get-api">
                        <div class="vi-ui white big message">
                            <div class="header">
				                <?php esc_html_e( 'Guide get API key', 'w2s-migrate-woocommerce-to-shopify' ); ?>
                            </div>
                            <ul class="list">
                                <li>
                                    <strong><?php esc_html_e( 'Step 1: Creating your Shopify development store', 'w2s-migrate-woocommerce-to-shopify' ); ?></strong>
                                </li>
                                <li>
                                    <strong><?php esc_html_e( 'Step 2: Enable cutom app', 'w2s-migrate-woocommerce-to-shopify' ); ?></strong>
                                    <p><?php echo sprintf( esc_html__( 'On the Shopify dashboard, go to the App settings as in the picture below >> Click "Develop apps". Then, follow the steps in %s to enable custom app development from the Shopify admin.', 'w2s-migrate-woocommerce-to-shopify' ), '<a target="_blank" href="https://help.shopify.com/en/manual/apps/custom-apps#:~:text=From%20your%20Shopify%20admin%2C%20go%20to%20Apps,then%20click%20Allow%20custom%20app%20development."  rel="noopener">this instruction </a>' ); ?>
                                        <a href="https://docs.villatheme.com/wp-content/uploads/2022/06/Screenshot-11.png"
                                           target="_blank"><?php esc_html_e( 'See image guide', 'w2s-migrate-woocommerce-to-shopify' ); ?></a>
                                    </p>
                                </li>
                                <li>
                                    <strong><?php esc_html_e( 'Step 3: Create an app', 'w2s-migrate-woocommerce-to-shopify' ); ?></strong>
                                    <p><?php echo sprintf( esc_html__( 'Click "Create an app" to create a custom app. And follow the next steps in %s to create a custom app.', 'w2s-migrate-woocommerce-to-shopify' ), '<a href="https://help.shopify.com/en/manual/apps/custom-apps#:~:text=Create%20the%20app,Create%20app." target="_blank">this instruction</a>' ); ?>
                                        <a href="https://docs.villatheme.com/wp-content/uploads/2022/06/Screenshot-10.png"
                                           target="_blank"><?php esc_html_e( 'See image guide', 'w2s-migrate-woocommerce-to-shopify' ); ?></a>
                                    </p>
                                    <p><?php echo sprintf( esc_html__( 'Note: In the past, Shopify used to allow users to create private apps, but this feature was removed, as mentioned in %s. If any users who have been using our plugin since then, the private app credentials in your plugin settings will still be kept and work properly.', 'w2s-migrate-woocommerce-to-shopify' ), '<a href="https://help.shopify.com/en/manual/apps/private-apps#:~:text=Private%20apps%20are%20deprecated%20and%20can%27t%20be%20created%20as%20of%20January%202022.%20Ask%20your%20app%20developer%20to%20create%20a%20custom%20app.%20Like%20private%20apps%2C%20custom%20apps%20are%20built%20exclusively%20for%20your%20shop%2C%20but%20they%20don%27t%20require%20open%20API%20access%20to%20your%20store%20or%20access%20to%20your%20Shopify%20admin." target="_blank" rel="noopener">this statement</a>' ); ?>
                                        <a href="https://docs.villatheme.com/wp-content/uploads/2022/06/Screenshot-14.png"
                                           target="_blank"><?php esc_html_e( 'See image detail scopes', 'w2s-migrate-woocommerce-to-shopify' ); ?></a>
                                    </p>
                                </li>
                                <li>
                                    <strong><?php esc_html_e( 'Step 4: Assign API scopes', 'w2s-migrate-woocommerce-to-shopify' ); ?></strong>
                                    <p><?php echo sprintf( esc_html__( 'After successfully creating a custom app, the next step is to assign API scopes to it. Please visit %s for specific steps.', 'w2s-migrate-woocommerce-to-shopify' ), '<a href="https://help.shopify.com/en/manual/apps/custom-apps#:~:text=Select%20API%20scopes,least%20one%20scope." target="_blank" >this instruction</a>' ); ?></p>
                                </li>
                                <li>
                                    <strong><?php esc_html_e( 'Step 5: Install the app', 'w2s-migrate-woocommerce-to-shopify' ); ?></strong>
                                    <p><?php esc_html_e( 'After you\'ve set API scopes for your app, you can install the app. You\'ll get your API access tokens after you install. Depending on what API scopes you assigned to the app, you\'ll get an Admin API access token, a Storefront API access token, or both.', 'w2s-migrate-woocommerce-to-shopify' ); ?></p>
                                </li>
                                <li>
                                    <strong><?php esc_html_e( 'Video guide', 'w2s-migrate-woocommerce-to-shopify' ); ?></strong>
                                    <p>
                                        <iframe width="640" height="360" src="https://www.youtube.com/embed/8rcq_jGkJSk" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                    </p>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <p></p>
                    <div class="w2s-security-warning">
                        <div class="vi-ui yellow large message">
                            <div class="header">
				                <?php esc_html_e( 'IMPORTANT NOTE:', 'w2s-migrate-woocommerce-to-shopify' ); ?>
                            </div>
                            <p><?php esc_html_e( 'You can see the Admin API access token on this page only one time, because the token provides API access to sensitive store data. After revealing the access token, write down or record the token somewhere secure so that you can refer to it again. Treat the token like a password. Share the access token only with developers that you trust. Now the custom app is created and installed successfully, the next step is to get the API credentials and place them to the plugin General settings.', 'w2s-migrate-woocommerce-to-shopify' ); ?></p>
                        </div>
                    </div>
                    <p></p>
                </div>
                <!--Import Product Option-->
				<?php
				$products_option = $this->setting->get_params( 'viw2s_import_products_option' );

				if (
					! empty( $products_option ) &&
					is_array( $products_option ) &&
					( count( $products_option ) > 0 )
				) {

					$import_products_option = $products_option;

				} else {
					$products_option_default = $this->default_data;
					$import_products_option = $products_option_default['viw2s_import_products_option' ];
				}
				$product_by_type           = $import_products_option['product_by_type'] ?? array();
				$product_collection_id     = $import_products_option['product_collection_id'] ?? '';
				$product_exclude_id        = $import_products_option['product_exclude_id'] ?? '';
				$product_cat_include_id    = $import_products_option['product_categories_include_id'] ?? '';
				$product_created_at_min    = $import_products_option['product_created_at_min'] ?? '';
				$product_created_at_max    = $import_products_option['product_created_at_max'] ?? '';
				$product_import_sequence   = $import_products_option['product_import_sequence'] ?? 'title asc';
				$product_keep_slug         = $import_products_option['import_product_keep_slug'] ?? '';
				$import_product_categories = $import_products_option['import_product_categories'] ?? '';
				$import_product_tags       = $import_products_option['import_product_tags'] ?? '';
				$import_product_sku        = $import_products_option['import_product_sku'] ?? '';
				$product_status_mapping    = $import_products_option['import_product_status_mapping'] ?? $this->setting->get_parrams( 'viw2s_import_products_option' )['import_product_status_mapping'];
				?>
                <div class="vi-ui segment transition visible"
                     id="viw2s-import-products-options">
                    <h3><?php esc_html_e( 'Import Products options', 'import-shopify-to-woocommerce' ) ?></h3>
                    <div class="viw2s-import-products-options-content">
                        <div class="viw2s-import-products-options-heading">
                            <div class="viw2s-save-products-options-container">
                                <span class="vi-ui labeled icon primary button tiny viw2s-save-products-options">
                                    <i class="icon save"></i><?php esc_html_e( 'Save', 'w2s-migrate-woo-to-shopify' ); ?>
                                </span>
                            </div>
                            <i class="close icon viw2s-import-products-options-close"></i>
                            <h3><?php esc_html_e( 'Import Products options', 'w2s-migrate-woo-to-shopify' ); ?></h3>
                        </div>
                        <table class="form-table">
                            <tbody>
                            <tr>
                                <th>
                                    <label for="viw2s_product_by_type"><?php esc_html_e( 'Filter by product type', 'w2s-migrate-woo-to-shopify' ); ?></label>
                                </th>
                                <td>
                                    <select class="vi-ui fluid dropdown" id="viw2s_product_by_type"
                                            name="viw2s_import_products_option[product_by_type][]">
                                        <option value="all"><?php esc_html_e( 'Simple & Variable', 'w2s-migrate-woo-to-shopify' ); ?></option>
                                        <option value="simple"
											<?php
											if ( is_array( $product_by_type ) && in_array( 'simple', $product_by_type ) ) {
												echo esc_attr( 'selected' );
											}
											?>
                                        ><?php esc_html_e( 'Only Simple', 'w2s-migrate-woo-to-shopify' ); ?></option>
                                        <option value="variable"
											<?php
											if ( is_array( $product_by_type ) && in_array( 'variable', $product_by_type ) ) {
												echo esc_attr( 'selected' );
											}
											?>
                                        ><?php esc_html_e( 'Only variable', 'w2s-migrate-woo-to-shopify' ); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="viw2s_product_collection_id"><?php esc_html_e( 'Include product', 'w2s-migrate-woo-to-shopify' ); ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui fluid viw2s_wrap_select2">
                                        <select class="viw2s_search_import_product" id="viw2s_product_collection_id"
                                                data-type="include"
                                                name="viw2s_import_products_option[product_collection_id][]"
                                                multiple="multiple">
											<?php
											if ( ! empty( $product_collection_id ) ) {
												foreach ( $product_collection_id as $item_product_include ) {
													echo '<option value="' . esc_attr( $item_product_include ) . '"  selected>' . esc_html( get_the_title( $item_product_include ) ) . '</option>';
												}
											}
											?>
                                        </select>

                                    </div>
                                    <span class="explanatory-text"><?php esc_html_e( 'Choose product you want to import', 'w2s-migrate-woo-to-shopify' ); ?> </span>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="viw2s_product_exclude_id"><?php esc_html_e( 'Exclude product', 'w2s-migrate-woo-to-shopify' ); ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui fluid viw2s_wrap_select2">
                                        <select class="viw2s_search_import_product" id="viw2s_product_exclude_id"
                                                data-type="exclude"
                                                name="viw2s_import_products_option[product_exclude_id][]"
                                                multiple="multiple">
											<?php
											if ( ! empty( $product_exclude_id ) ) {
												foreach ( $product_exclude_id as $item_product_exclude ) {
													echo '<option value="' . esc_attr( $item_product_exclude ) . '"  selected>' . esc_html( get_the_title( $item_product_exclude ) ) . '</option>';
												}
											}
											?>
                                        </select>
                                    </div>
                                    <span class="explanatory-text"><?php esc_html_e( 'Choose product you don\'t want to import', 'w2s-migrate-woo-to-shopify' ); ?> </span>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="viw2s_product_categories_include_id"><?php esc_html_e( 'Include by Product categories', 'w2s-migrate-woo-to-shopify' ); ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui fluid viw2s_wrap_select2">
                                        <select class="viw2s_search_import_product_cat"
                                                id="viw2s_product_categories_include_id"
                                                data-type="include"
                                                name="viw2s_import_products_option[product_categories_include_id][]"
                                                multiple="multiple">
											<?php
											if ( ! empty( $product_cat_include_id ) ) {
												foreach ( $product_cat_include_id as $item_product_cat ) {
													echo '<option value="' . esc_attr( $item_product_cat ) . '"  selected>' . esc_html__( get_term_by( 'slug', $item_product_cat, 'product_cat' )->name, 'w2s-migrate-woo-to-shopify' ) . '</option>';
												}
											}
											?>
                                        </select>
                                    </div>
                                    <span class="explanatory-text"><?php esc_html_e( 'Filter include product by product categories.', 'w2s-migrate-woo-to-shopify' ); ?> </span>
                                </td>
                            </tr>

                            <tr>
                                <th>
                                    <label for="viw2s_product_created_at_min"><?php esc_html_e( 'Import products created date', 'w2s-migrate-woo-to-shopify' ); ?></label>
                                </th>
                                <td>
                                    <div class="vi_wrap_input viw2s_wrap_date_input">
                                        <div class="vi-ui right labeled input vi_label_input vi_date_from">
                                            <label for="viw2s_product_created_at_min"
                                                   class="vi-ui label"><?php esc_html_e( 'From', 'w2s-migrate-woo-to-shopify' ); ?></label>
                                            <input type="date"
                                                   name="viw2s_import_products_option[product_created_at_min]"
                                                   id="viw2s_product_created_at_min"
                                                   value="<?php echo esc_attr( $product_created_at_min ); ?>">
                                        </div>
                                        <div class="vi-ui labeled input vi_label_input vi_date_to">
                                            <label for="viw2s_product_created_at_max"
                                                   class="vi-ui label"><?php esc_html_e( 'To', 'w2s-migrate-woo-to-shopify' ); ?></label>
                                            <input type="date"
                                                   name="viw2s_import_products_option[product_created_at_max]"
                                                   id="viw2s_product_created_at_max"
                                                   value="<?php echo esc_attr( $product_created_at_max ); ?>">
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>

                                <th>
                                    <label for="viw2s_product_import_sequence"><?php esc_html_e( 'Import Products sequence', 'w2s-migrate-woo-to-shopify' ); ?></label>
                                </th>
                                <td>
                                    <select class="vi-ui fluid dropdown"
                                            name="viw2s_import_products_option[product_import_sequence]"
                                            id="viw2s_product_import_sequence">
                                        <option value="title asc" <?php selected( $product_import_sequence, "title asc" ); ?> ><?php esc_html_e( 'Order by Title Ascending', 'w2s-migrate-woo-to-shopify' ); ?></option>
                                        <option value="title desc" <?php selected( $product_import_sequence, "title desc" ); ?>><?php esc_html_e( 'Order by Title Descending', 'w2s-migrate-woo-to-shopify' ); ?></option>
                                        <option value="created_at asc" <?php selected( $product_import_sequence, "created_at asc" ); ?>><?php esc_html_e( 'Order by Created Date Ascending', 'w2s-migrate-woo-to-shopify' ); ?></option>
                                        <option value="created_at desc" <?php selected( $product_import_sequence, "created_at desc" ); ?>><?php esc_html_e( 'Order by Created Date Descending', 'w2s-migrate-woo-to-shopify' ); ?></option>
                                        <option value="updated_at asc" <?php selected( $product_import_sequence, "updated_at asc" ); ?>><?php esc_html_e( 'Order by Updated Date Ascending', 'w2s-migrate-woo-to-shopify' ); ?></option>
                                        <option value="updated_at desc" <?php selected( $product_import_sequence, "updated_at desc" ); ?>><?php esc_html_e( 'Order by Updated Date Descending', 'w2s-migrate-woo-to-shopify' ); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="viw2s_product_keep_slug"><?php esc_html_e( 'Keep Product Slug', 'w2s-migrate-woo-to-shopify' ); ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="checkbox"
                                               name="viw2s_import_products_option[import_product_keep_slug]"
                                               id="viw2s_product_keep_slug"
											<?php checked( $product_keep_slug, 'on' ) ?>
                                        >
                                    </div>
                                    <span class="explanatory-text top"><?php esc_html_e( 'keep the slug of the product when importing', 'w2s-migrate-woo-to-shopify' ); ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="viw2s_import_product_categories"><?php esc_html_e( 'Import Product Categories', 'w2s-migrate-woo-to-shopify' ); ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="checkbox"
                                               name="viw2s_import_products_option[import_product_categories]"
                                               id="viw2s_import_product_categories"
											<?php checked( $import_product_categories, 'on' ) ?>
                                        >
                                    </div>
                                    <span class="explanatory-text top"><?php esc_html_e( 'Import product categories', 'w2s-migrate-woo-to-shopify' ); ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="viw2s_import_product_tags"><?php esc_html_e( 'Import Products Tags', 'w2s-migrate-woo-to-shopify' ); ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="checkbox"
                                               name="viw2s_import_products_option[import_product_tags]"
                                               id="viw2s_import_product_tags"
											<?php checked( $import_product_tags, 'on' ) ?>
                                        >
                                    </div>
                                    <span class="explanatory-text top"><?php esc_html_e( 'Import product tags', 'w2s-migrate-woo-to-shopify' ); ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="viw2s_import_product_sku"><?php esc_html_e( 'Import Products SKU', 'w2s-migrate-woo-to-shopify' ); ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="checkbox"
                                               name="viw2s_import_products_option[import_product_sku]"
                                               id="viw2s_import_product_sku"
											<?php checked( $import_product_sku, 'on' ); ?>
                                        >
                                    </div>
                                    <span class="explanatory-text top"><?php esc_html_e( 'Import product SKU', 'w2s-migrate-woo-to-shopify' ); ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for=""><?php esc_html_e( 'Product Status Mapping', 'w2s-migrate-woo-to-shopify' ); ?></label>
                                </th>
                                <td>
                                    <table class="vi-ui table">
                                        <thead>
                                        <tr>
                                            <th><?php esc_html_e( 'From Woocommerce', 'w2s-migrate-woo-to-shopify' ); ?></th>
                                            <th><?php esc_html_e( 'To Shopify', 'w2s-migrate-woo-to-shopify' ); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td><?php esc_html_e( 'Publish', 'w2s-migrate-woo-to-shopify' ); ?></td>
                                            <td>
                                                <select class="vi-ui fluid dropdown"
                                                        name="viw2s_import_products_option[import_product_status_mapping][publish]"
                                                >
                                                    <option value="active" <?php selected( $product_status_mapping['publish'], 'active' ); ?> ><?php esc_html_e( 'Active', 'w2s-migrate-woo-to-shopify' ); ?></option>
                                                    <option value="archived" <?php selected( $product_status_mapping['publish'], 'archived' ); ?> ><?php esc_html_e( 'Archived', 'w2s-migrate-woo-to-shopify' ); ?></option>
                                                    <option value="draft" <?php selected( $product_status_mapping['publish'], 'draft' ); ?> ><?php esc_html_e( 'Draft', 'w2s-migrate-woo-to-shopify' ); ?></option>
                                                    <option value="not_import" <?php selected( $product_status_mapping['publish'], 'not_import' ); ?> ><?php esc_html_e( 'Not import', 'w2s-migrate-woo-to-shopify' ); ?></option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php esc_html_e( 'Draft', 'w2s-migrate-woo-to-shopify' ); ?></td>
                                            <td>
                                                <select class="vi-ui fluid dropdown"
                                                        name="viw2s_import_products_option[import_product_status_mapping][draft]"
                                                >
                                                    <option value="active" <?php selected( $product_status_mapping['draft'], 'active' ); ?> ><?php esc_html_e( 'Active', 'w2s-migrate-woo-to-shopify' ); ?></option>
                                                    <option value="archived" <?php selected( $product_status_mapping['draft'], 'archived' ); ?> ><?php esc_html_e( 'Archived', 'w2s-migrate-woo-to-shopify' ); ?></option>
                                                    <option value="draft" <?php selected( $product_status_mapping['draft'], 'draft' ); ?> ><?php esc_html_e( 'Draft', 'w2s-migrate-woo-to-shopify' ); ?></option>
                                                    <option value="not_import" <?php selected( $product_status_mapping['draft'], 'not_import' ); ?> ><?php esc_html_e( 'Not import', 'w2s-migrate-woo-to-shopify' ); ?></option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php esc_html_e( 'Pending Review', 'w2s-migrate-woo-to-shopify' ); ?></td>
                                            <td>
                                                <select class="vi-ui fluid dropdown"
                                                        name="viw2s_import_products_option[import_product_status_mapping][pending_review]"
                                                >
                                                    <option value="active" <?php selected( $product_status_mapping['pending_review'], 'active' ); ?> ><?php esc_html_e( 'Active', 'w2s-migrate-woo-to-shopify' ); ?></option>
                                                    <option value="archived" <?php selected( $product_status_mapping['pending_review'], 'archived' ); ?> ><?php esc_html_e( 'Archived', 'w2s-migrate-woo-to-shopify' ); ?></option>
                                                    <option value="draft" <?php selected( $product_status_mapping['pending_review'], 'draft' ); ?> ><?php esc_html_e( 'Draft', 'w2s-migrate-woo-to-shopify' ); ?></option>
                                                    <option value="not_import" <?php selected( $product_status_mapping['pending_review'], 'not_import' ); ?> ><?php esc_html_e( 'Not import', 'w2s-migrate-woo-to-shopify' ); ?></option>
                                                </select>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <p>
                    <button type="submit" class="vi-ui labeled icon primary tiny button viw2s-save-settings"
                            name="viw2s-save-setting">
                        <i class="icon save"></i><?php esc_html_e( 'Save', 'w2s-migrate-woo-to-shopify' ); ?>
                    </button>
                </p>


            </form>

        </div>
    </div>
    <p></p>
	<?php
	/*Check currency Shopify and WooCommerce*/
	$ShopifyStore = $this->setting->get_shopify_store_info( $domain, $api_key, $api_secret );
	$WooCurrency  = get_option( 'woocommerce_currency' );
	if (
		$active &&
		isset( $ShopifyStore['data'] ) &&
		is_array( $ShopifyStore['data'] ) &&
		isset( $ShopifyStore['data']['currency'] ) &&
		$WooCurrency !== $ShopifyStore['data']['currency']
	) {
		?>
        <div class="viw2s-permission-warning">
            <div class="vi-ui red message">
				<?php printf( esc_html__( 'Base currency in WooCommerce %s differs from the one in Shopify %s', 'w2s-migrate-woo-to-shopify' ), $WooCurrency, $ShopifyStore['data']['currency'] ); ?>
            </div>
        </div>
		<?php
	}
	?>
    <form class="vi-ui form viw2s-settings-import-container"
          method="post"
          style="<?php if ( ! $active || empty( $viw2s_get_api_access_scope_handle ) || ! in_array( 'write_products', $viw2s_get_api_access_scope_handle ) )
		      echo esc_attr( 'display:none' ) ?>">
		<?php wp_nonce_field( 'viw2s_action_import_nonce', '_viw2s_action_import_nonce' ); ?>
        <div class="vi-ui segment">

            <div class="viw2s-wrap-import-settings">

                <!--Progress import-->
                <div class="vi-ui segment viw2s-step-import-settings viw2s-progress-import active"
                     data-step="progress-import">
                    <div class="viw2s_input_hidden">
						<?php
						if (
							isset( $viw2s_setting_params['viw2s_store_setting'] ) &&
							is_array( $viw2s_setting_params['viw2s_store_setting'] ) &&
							( count( $viw2s_setting_params['viw2s_store_setting'] ) > 0 )
						) {
                            $count = 0;
							foreach ( $viw2s_setting_params['viw2s_store_setting'] as $store_item ) {
							    if($count > 0){
							        break;
                                }
								$store_address = $store_item['domain'] ?? '';
								$class_icon    = '';
								$disabled      = '';
								if ( $store_item['validate'] ) {
									$class_icon = 'green';
								} else {
									$class_icon = 'grey';
									$disabled   = 'disabled';
								}
								?>

                                <input type="checkbox"
                                       class="viw2s-choose-store"
                                       name="viw2s_store_setting[0][choosen]" <?php echo esc_attr( $disabled ); ?>
									<?php checked( $store_item['validate'], true ); ?>
                                >
								<?php
								$count++;
							}
						}
						?>
                        <input type="checkbox"
                               id="viw2s-import-products-enable"
                               class="viw2s-import-element-enable " data-element_name="products"
                               name="import_products" checked
                        >
                        <input type="checkbox"
                               id="viw2s-import-products-categories-enable"
                               class="viw2s-import-element-enable" data-element_name="product_categories"
                               name="import_products_categories"
							<?php checked( $import_product_categories, 'on' ) ?>
                        >
                    </div>

					<?php
					if (
						isset( $viw2s_setting_params['viw2s_store_setting'] ) &&
						is_array( $viw2s_setting_params['viw2s_store_setting'] ) &&
						( count( $viw2s_setting_params['viw2s_store_setting'] ) > 0 )
					) {
						$count = 0;
						foreach ( $viw2s_setting_params['viw2s_store_setting'] as $store_item ) {
							if($count > 0){
								break;
							}
							$store_address = $store_item['domain'] ?? '';
							if ( $store_item['validate'] ) {
								?>

                                <table class="vi-ui celled table">
                                    <thead>
                                    <tr>
                                        <th style="width: 200px;"><?php esc_html_e( 'Data', 'w2s-migrate-woo-to-shopify' ); ?></th>
                                        <th><?php esc_html_e( 'Progress', 'w2s-migrate-woo-to-shopify' ); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
									<?php

									?>
                                    <tr>
                                        <td><?php esc_html_e( 'Products', 'w2s-migrate-woo-to-shopify' ); ?></td>
                                        <td>
                                            <div class="vi-ui indicating progress standard viw2s-import-progress"
                                                 style="visibility: hidden"
                                                 id="<?php echo esc_attr( 'viw2s-products-progress' ) ?>">
                                                <div class="label"></div>
                                                <div class="bar">
                                                    <div class="progress"></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?php esc_html_e( 'Products Categories', 'w2s-migrate-woo-to-shopify' ); ?></td>
                                        <td>
                                            <div class="vi-ui indicating progress standard viw2s-import-progress"
                                                 style="visibility: hidden"
                                                 id="<?php echo esc_attr( 'viw2s-product-categories-progress' ) ?>">
                                                <div class="label"></div>
                                                <div class="bar">
                                                    <div class="progress"></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
								<?php
							}
							$count++;
						}
					}
					?>
                    <p>
                        <a href="#" class="vi-ui labeled icon positive button tiny viw2s-import-btn"
                           data-target-step="progress-import""><i
                                class="icon cloud download"></i><?php esc_html_e( 'Import', 'w2s-migrate-woo-to-shopify' ); ?>
                        </a>
                    </p>
                </div>
            </div>
            <div class="viw2s_wrap_logs">
                <h4><?php esc_html_e( 'Logs:', 'w2s-migrate-woo-to-shopify' ); ?></h4>
                <div class="vi-ui segment viw2s-logs"></div>
            </div>
        </div>
    </form>
	<?php do_action( 'villatheme_support_w2s-migrate-woo-to-shopify' ); ?>
</div>
