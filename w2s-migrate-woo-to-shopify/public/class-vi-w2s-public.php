<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://villatheme.com
 * @since      1.0.0
 *
 * @package    w2s-migrate-woo-to-shopify
 * @subpackage w2s-migrate-woo-to-shopify/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 *
 * @package    w2s-migrate-woo-to-shopify
 * @subpackage w2s-migrate-woo-to-shopify/public
 * @author     Villatheme <support@villatheme.com>
 */
class Vi_W2s_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $vi_w2s    The ID of this plugin.
	 */
	private $vi_w2s;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $vi_w2s       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $vi_w2s, $version ) {

		$this->vi_w2s = $vi_w2s;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->vi_w2s, plugin_dir_url( __FILE__ ) . 'css/vi-w2s-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->vi_w2s, plugin_dir_url( __FILE__ ) . 'js/vi-w2s-public.js', array( 'jquery' ), $this->version, false );

	}

}
