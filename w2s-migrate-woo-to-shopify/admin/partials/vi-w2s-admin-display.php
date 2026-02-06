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

/*Check active*/
if ( isset( $store_setting ) && is_array( $store_setting ) && ( count( $store_setting ) > 0 ) ) {
	$active = true;
	foreach ( $store_setting as $store_item ) {
		if ( ! isset( $store_item['validate'] ) || ! $store_item['validate'] ) {
			$active = false;
		}
	}
}

// Prepare data for the Unified API Table (copied logic from Pro)
$all_connections = [];
if ( ! empty( $store_setting ) && is_array( $store_setting ) ) {
	foreach ( $store_setting as $store_item ) {
		$domain = isset( $store_item['domain'] ) ? $store_item['domain'] : '';

		// Determine API type
		$api_type = 'legacy'; // default
		if ( isset( $store_item['api_type'] ) ) {
			$api_type = $store_item['api_type'];
		} elseif ( isset( $store_item['oauth_enabled'] ) && $store_item['oauth_enabled'] ) {
			$api_type = 'oauth';
		}

		// Get credentials
		if ( $api_type === 'oauth' ) {
			$credential_1 = isset( $store_item['client_id'] ) ? $store_item['client_id'] : '';
			$credential_2 = isset( $store_item['client_secret'] ) ? $store_item['client_secret'] : '';
		} else {
			$credential_1 = isset( $store_item['api_key'] ) ? $store_item['api_key'] : '';
			$credential_2 = isset( $store_item['api_secret'] ) ? $store_item['api_secret'] : '';
		}

		$all_connections[] = [
			'domain'       => $domain,
			'api_type'     => $api_type,
			'credential_1' => $credential_1,
			'credential_2' => $credential_2,
			'validated'    => isset( $store_item['validate'] ) && $store_item['validate'],
			'full_data'    => $store_item,
			'error_code'    => isset( $store_item['error_code'] ) ? $store_item['error_code'] : '',
			'error_message' => isset( $store_item['error_message'] ) ? $store_item['error_message'] : ''
		];
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
        <div class="content <?php if ( ! $active ) {
			echo esc_attr( 'active' );
		} ?> ">
            <form class="vi-ui form" method="post" action="" id="viw2s_setting_form">
				<?php wp_nonce_field( 'viw2s_action_save_setting_nonce', '_viw2s_save_setting_nonce' ); ?>

                <div class="vi-ui tab-connect-api">
                    <h3><?php esc_html_e( 'Connect to Shopify store', 'w2s-migrate-woo-to-shopify' ); ?></h3>
                    <p><?php esc_html_e( 'Please enter your store address and API credentials. You can choose between OAuth (recommended) or Legacy API authentication.', 'w2s-migrate-woo-to-shopify' ); ?></p>
                </div>

                <!-- Unified API Table (From Pro) -->
                <table class="vi-ui compact celled stackable table center aligned viw2s-unified-api-table" id="table_store_info">
                    <thead>
                    <tr>
                        <th>
							<?php esc_html_e( 'Store address', 'w2s-migrate-woo-to-shopify' ); ?>
                            <span class="viw2s-help-tip"
                                  data-tip="<?php esc_attr_e( 'This is store address Eg: myshop.myshopify.com', 'w2s-migrate-woo-to-shopify' ) ?>"></span>
                        </th>
                        <th>
							<?php esc_html_e( 'API Type', 'w2s-migrate-woo-to-shopify' ); ?>
                            <span class="viw2s-help-tip"
                                  data-tip="<?php esc_attr_e( 'OAuth (recommended) or Legacy API authentication', 'w2s-migrate-woo-to-shopify' ) ?>"></span>
                        </th>
                        <th>
							<?php esc_html_e( 'Credential 1', 'w2s-migrate-woo-to-shopify' ); ?>
                            <span class="viw2s-help-tip"
                                  data-tip="<?php esc_attr_e( 'Client ID (OAuth) or API Key (Legacy)', 'w2s-migrate-woo-to-shopify' ) ?>"></span>
                        </th>
                        <th>
							<?php esc_html_e( 'Credential 2', 'w2s-migrate-woo-to-shopify' ); ?>
                            <span class="viw2s-help-tip"
                                  data-tip="<?php esc_attr_e( 'Client Secret (OAuth) or API Access Token (Legacy)', 'w2s-migrate-woo-to-shopify' ) ?>"></span>
                        </th>
                        <th>
							<?php esc_html_e( 'Action', 'w2s-migrate-woo-to-shopify' ); ?>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
					<?php
					if ( ! empty( $all_connections ) ) {
						$conn_index = 0;
						foreach ( $all_connections as $conn ) {
							$api_type_label = $conn['api_type'] === 'oauth' ? 'OAuth' : 'Legacy';
							$cred1_label = $conn['api_type'] === 'oauth' ? 'Client ID' : 'API Key';
							$cred2_label = $conn['api_type'] === 'oauth' ? 'Client Secret' : 'API Token';
							?>
                            <tr data-shop-domain="<?php echo esc_attr( $conn['domain'] ); ?>" data-api-type="<?php echo esc_attr( $conn['api_type'] ); ?>">
                                <td data-label="Store address">
                                    <input type="text"
                                           class="viw2s-store-domain"
                                           name="viw2s_store_setting[<?php echo $conn_index; ?>][domain]"
                                           value="<?php echo esc_attr( $conn['domain'] ); ?>"
                                    >
									<?php
									// Display error for domain field
									if ( ! empty( $conn['error_code'] ) && ! empty( $conn['error_message'] ) ) {
										if ( $conn['error_code'] === 'http_request_failed' ||
										     $conn['error_code'] === 'invalid_credentials' ||
										     $conn['error_code'] === 'connection_failed' ||
										     $conn['error_code'] === 'missing_fields' ||
										     $conn['error_code'] === 'oauth_error' ) {
											?>
                                            <div class="viw2s-inline-error">
                                                <i class="attention icon"></i><?php echo wp_kses_post( $conn['error_message'] ); ?>
                                            </div>
											<?php
										}
									}
									?>
									<?php
									// Preserve other fields
									if ( isset( $conn['full_data'] ) && is_array( $conn['full_data'] ) ) {
										foreach ( $conn['full_data'] as $field_key => $field_value ) {
											if ( in_array( $field_key, ['domain', 'api_type', 'client_id', 'api_key', 'password', 'client_secret'] ) ) {
												continue;
											}
											if ( is_array( $field_value ) ) {
												$field_value = wp_json_encode( $field_value );
											}
											?>
                                            <input type="hidden"
                                                   name="viw2s_store_setting[<?php echo $conn_index; ?>][<?php echo esc_attr( $field_key ); ?>]"
                                                   value="<?php echo esc_attr( $field_value ); ?>">
											<?php
										}
									}
									if ( $conn['api_type'] === 'oauth' ): ?>
									<input type="hidden" name="viw2s_store_setting[<?php echo $conn_index; ?>][existing_oauth]" value="1">
									<?php else: ?>
									<input type="hidden" name="viw2s_store_setting[<?php echo $conn_index; ?>][existing_legacy]" value="1">
									<input type="hidden" name="viw2s_store_setting[<?php echo $conn_index; ?>][existing_api_secret]" value="<?php echo esc_attr( isset( $conn['full_data']['api_secret'] ) ? $conn['full_data']['api_secret'] : '' ); ?>">
									<?php endif; ?>
                                </td>
                                <td data-label="API Type">
                                    <select name="viw2s_store_setting[<?php echo $conn_index; ?>][api_type]" class="viw2s-api-type-select">
                                        <option value="legacy" <?php selected( $conn['api_type'], 'legacy' ); ?>>Legacy</option>
                                        <option value="oauth" <?php selected( $conn['api_type'], 'oauth' ); ?>>OAuth</option>
                                    </select>
                                </td>
                                <td data-label="<?php echo esc_attr( $cred1_label ); ?>">
                                    <small class="viw2s-credential-label"><?php echo esc_html( $cred1_label ); ?></small>
									<?php if ( $conn['api_type'] === 'oauth' ): ?>
                                        <input type="text"
                                               class="viw2s-store-credential-1"
                                               name="viw2s_store_setting[<?php echo $conn_index; ?>][client_id]"
                                               value="<?php echo esc_attr( $conn['credential_1'] ); ?>"
                                        >
									<?php else: ?>
                                        <input type="text"
                                               class="viw2s-store-credential-1"
                                               name="viw2s_store_setting[<?php echo $conn_index; ?>][api_key]"
                                               value="<?php echo esc_attr( $conn['credential_1'] ); ?>"
                                        >
									<?php endif; ?>
									<?php
									if ( ! empty( $conn['error_code'] ) && ! empty( $conn['error_message'] ) ) {
										if ( $conn['error_code'] === '403' ||
										     $conn['error_code'] === 'unauthorized' ||
										     $conn['error_code'] === 'invalid_credentials' ||
										     $conn['error_code'] === 'missing_fields' ||
										     $conn['error_code'] === 'insufficient_scopes' ) {
											?>
                                            <div class="viw2s-inline-error">
                                                <i class="attention icon"></i><?php echo wp_kses_post( $conn['error_message'] ); ?>
                                            </div>
											<?php
										}
									}
									?>
                                </td>
                                <td data-label="<?php echo esc_attr( $cred2_label ); ?>">
                                    <small class="viw2s-credential-label"><?php echo esc_html( $cred2_label ); ?></small>
									<?php if ( $conn['api_type'] === 'oauth' ): ?>
                                        <input type="text"
                                               class="viw2s-store-credential-2"
                                               name="viw2s_store_setting[<?php echo $conn_index; ?>][client_secret]"
                                               value="<?php echo esc_attr( $conn['credential_2'] ); ?>"
                                               placeholder="Leave blank to keep current secret"
                                        >
									<?php else: ?>
                                        <input type="text"
                                               class="viw2s-store-credential-2"
                                               name="viw2s_store_setting[<?php echo $conn_index; ?>][api_secret]"
                                               value="<?php echo esc_attr( $conn['credential_2'] ); ?>"
                                               placeholder="Leave blank to keep current token"
                                        >
									<?php endif; ?>
									<?php
									if ( ! empty( $conn['error_code'] ) && ! empty( $conn['error_message'] ) ) {
										if ( $conn['error_code'] === '401' ||
										     $conn['error_code'] === 'unauthorized' ||
										     $conn['error_code'] === 'invalid_credentials' ||
										     $conn['error_code'] === 'missing_fields' ||
										     $conn['error_code'] === 'insufficient_scopes' ) {
											?>
                                            <div class="viw2s-inline-error">
                                                <i class="attention icon"></i><?php echo wp_kses_post( $conn['error_message'] ); ?>
                                            </div>
											<?php
										}
									}
									?>
                                </td>
                                <td data-label="Action">
                                    <button type="button" class="viw2s-delete-connection-btn vi-ui red basic compact icon button"
                                            data-shop-domain="<?php echo esc_attr( $conn['domain'] ); ?>"
                                            data-api-type="<?php echo esc_attr( $conn['api_type'] ); ?>">
                                        <i class="icon trash alternate outline"></i>
                                    </button>
                                </td>
                            </tr>
							<?php
							$conn_index++;
						}
					} else {
						?>
                        <tr class="viw2s-no-connections">
                            <td colspan="5">
								<?php esc_html_e( 'No API connections configured yet. Click "Add Connection" to get started.', 'w2s-migrate-woo-to-shopify' ); ?>
                            </td>
                        </tr>
					<?php } ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th colspan="4">
                            <div class="viw2s-error-warning" style="<?php if ( $active ) echo esc_attr( 'display:none' ) ?>">
                                <div class="vi-ui negative message">
									<?php esc_html_e( 'You need to enter correct domain and API credentials to be able to import', 'w2s-migrate-woo-to-shopify' ); ?>
                                </div>
                            </div>
                        </th>
                        <th>
                            <div class="vi-wrap-add-button">
                                <button type="button" class="viw2s-show-add-connection vi-ui green labeled icon button tiny">
                                    <i class="icon add"></i><?php esc_html_e( 'Add Connection', 'w2s-migrate-woo-to-shopify' ); ?>
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
                            <ul class="list">
                                <li>
                                    <strong>
                                        <?php esc_html_e( 'Refer to ', 'w2s-migrate-woo-to-shopify' ); ?>
                                        <a href="https://docs.villatheme.com/w2s-migrate-woocommerce-to-shopify/#configuration_child_menu_7909" target="_blank" rel="noopener noreferrer">
                                            <?php esc_html_e( 'this document', 'w2s-migrate-woo-to-shopify' ); ?>
                                        </a>
                                        <?php esc_html_e( ' or the tutorial below to create a custom app and get API credentials', 'w2s-migrate-woo-to-shopify' ); ?>
                                    </strong>
                                </li>
                                <li>
                                    <!--                                        <strong>--><?php //esc_html_e( 'Video guide', 'w2s-migrate-woocommerce-to-shopify' ); ?><!--</strong>-->
                                    <p>
                                        <iframe width="640" height="360" src="https://www.youtube.com/embed/Zu4zi0cCRHU" title="YouTube video player"
                                                frameborder="0"
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                allowfullscreen></iframe>
                                    </p>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <p></p>
                    <div class="w2s-security-warning">
                        <div class="vi-ui yellow large message">
                            <div class="header">
				                <?php esc_html_e( 'IMPORTANT NOTE:', 'w2s-migrate-woo-to-shopify' ); ?>
                            </div>
                            <p><?php esc_html_e( 'You can see the Admin API access token on this page only one time, because the token provides API access to sensitive store data. After revealing the access token, write down or record the token somewhere secure so that you can refer to it again. Treat the token like a password. Share the access token only with developers that you trust. Now the custom app is created and installed successfully, the next step is to get the API credentials and place them to the plugin General settings.', 'w2s-migrate-woo-to-shopify' ); ?></p>
                        </div>
                    </div>
                    <p></p>
                </div>
                <!--Import Product Option-->
				<?php
				$products_option = $VIW2S_Data_default->get_params( 'viw2s_import_products_option' );

				if (
					! empty( $products_option ) &&
					is_array( $products_option ) &&
					( count( $products_option ) > 0 )
				) {

					$import_products_option = $products_option;

				} else {
					$products_option_default = $VIW2S_Data_default->get_default();
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
				$product_status_mapping    = $import_products_option['import_product_status_mapping'] ?? $VIW2S_Data_default->get_params( 'viw2s_import_products_option' )['import_product_status_mapping'];
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
													echo '<option value="' . esc_attr( $item_product_cat ) . '"  selected>' . esc_html( get_term_by( 'slug', $item_product_cat, 'product_cat' )->name ) . '</option>';
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
	$ShopifyStore = null;
	$WooCurrency  = get_option( 'woocommerce_currency' );
	if ( $active ) {
		$domain       = isset( $store_setting[0]['domain'] ) ? $store_setting[0]['domain'] : '';
		$api_key      = isset( $store_setting[0]['api_key'] ) ? $store_setting[0]['api_key'] : '';
		$api_secret   = isset( $store_setting[0]['api_secret'] ) ? $store_setting[0]['api_secret'] : '';

		if ( isset( $store_setting[0]['api_type'] ) && $store_setting[0]['api_type'] === 'oauth' ) {
			if ( ! empty( $store_setting[0]['access_token'] ) ) {
				$api_secret = $store_setting[0]['access_token'];
			} elseif ( class_exists( 'Viw2s_API_Settings' ) && method_exists( 'Viw2s_API_Settings', 'get_access_token' ) ) {
				$token = Viw2s_API_Settings::get_access_token( $domain );
				if ( ! is_wp_error( $token ) ) {
					$api_secret = $token;
				}
			}
		}

		$ShopifyStore = $VIW2S_Data_default->get_shopify_store_info( $domain, $api_key, $api_secret );
	}
	if (
		$ShopifyStore &&
		isset( $ShopifyStore['data'] ) &&
		is_array( $ShopifyStore['data'] ) &&
		isset( $ShopifyStore['data']['currency'] ) &&
		$WooCurrency !== $ShopifyStore['data']['currency']
	) {
		?>
        <div class="viw2s-permission-warning">
            <div class="vi-ui red message">
				<?php printf( esc_html( 'Base currency in WooCommerce %s differs from the one in Shopify %1$s' ), esc_html($WooCurrency), esc_html($ShopifyStore['data']['currency']) ); ?>
            </div>
        </div>
		<?php
	}
	?>
    <form class="vi-ui form viw2s-settings-import-container"
          method="post"
          style="<?php if ( ! $active )
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
								$store_address = $store_item['domain'];
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
                                <input type="hidden" name="store_name" value="<?php echo esc_attr( $store_address ); ?>">
								<?php
								$count++;
							}
						}
						?>
<!--                        <input type="checkbox"-->
<!--                               id="viw2s-import-products-enable"-->
<!--                               class="viw2s-import-element-enable " data-element_name="products"-->
<!--                               name="import_products" checked-->
<!--                        >-->
<!--                        <input type="checkbox"-->
<!--                               id="viw2s-import-products-categories-enable"-->
<!--                               class="viw2s-import-element-enable" data-element_name="product_categories"-->
<!--                               name="import_products_categories"-->
<!--							--><?php //checked( $import_product_categories, 'on' ) ?>
<!--                        >-->
                    </div>

                    <table class="vi-ui celled table">
                        <thead>
                        <tr>
                            <th style="width: 200px;"><?php esc_html_e( 'Data', 'w2s-migrate-woo-to-shopify' ); ?></th>
                            <th><?php esc_html_e( 'Progress', 'w2s-migrate-woo-to-shopify' ); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td><?php esc_html_e( 'Products', 'w2s-migrate-woo-to-shopify' ); ?></td>
                            <td>
                                <div class="vi-ui toggle checkbox ">
                                    <input type="checkbox"
                                           class="viw2s-import-element-enable viw2s-import-products-enable"
                                           data-element_name="products"
                                           name="" checked >
                                    <label></label>
                                </div>
                                <div class="vi-ui indicating progress standard viw2s-import-progress"
                                     id="viw2s-products-progress">
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
                                <div class="vi-ui toggle checkbox ">
                                    <input type="checkbox"
                                           class="viw2s-import-element-enable viw2s-import-product-categories-enable"
                                           data-element_name="product_categories"
                                           name="" checked>
                                    <label></label>
                                </div>
                                <div class="vi-ui indicating progress standard viw2s-import-progress"
                                     id="viw2s-product-categories-progress">
                                    <div class="label"></div>
                                    <div class="bar">
                                        <div class="progress"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <p>
                        <a href="#" class="vi-ui labeled icon positive button tiny viw2s-import-btn"
                           data-target-step="progress-import"><i
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
