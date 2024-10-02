<div class="wrap">
	<h2><?php esc_html_e( 'Import WooCommerce to Shopify log files', 'w2s-migrate-woo-to-shopify' ); ?></h2>
	<?php
    $setting = new VI_W2S_IMPORT_WOOCOMMERCE_TO_SHOPIFY_DATA();
	$store_setting       = $setting->get_params( 'viw2s_store_setting' );
    if(is_array($store_setting) && !empty($store_setting)){
	    $patch = $setting ->get_cache_path( $store_setting[0]['domain'], $store_setting[0]['api_key'], $store_setting[0]['api_secret']);
	    $file =   $patch.'/logs.txt';
	    if ( ! is_file( $file ) ) {
		    esc_html_e( 'Log file not found.', 'w2s-migrate-woo-to-shopify' ) ;
	    }else{
		    $this->print_log_html(glob( $file ));
	    }
    }

	?>
</div>