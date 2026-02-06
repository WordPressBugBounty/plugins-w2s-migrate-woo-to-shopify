<?php

/**
 * AJAX Handler for API Settings
 *
 * Processes OAuth and Legacy credential submissions
 *
 * @package    w2s-migrate-woo-to-shopify
 * @subpackage w2s-migrate-woo-to-shopify/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Viw2s_API_AJAX_Handler {

	public function __construct() {
		add_action( 'wp_ajax_viw2s_save_oauth_credentials', [ $this, 'save_oauth_credentials' ] );
		add_action( 'wp_ajax_viw2s_save_legacy_credentials', [ $this, 'save_legacy_credentials' ] );
		add_action( 'wp_ajax_viw2s_test_connection', [ $this, 'test_connection' ] );
		add_action( 'wp_ajax_viw2s_delete_api_credentials', [ $this, 'delete_api_credentials' ] );
	}

	/**
	 * AJAX: Save OAuth credentials
	 */
	public function save_oauth_credentials() {
		check_ajax_referer( 'viw2s_action_nonce', '_nonce' );

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$shop_domain    = isset( $_POST['shop_domain'] ) ? sanitize_text_field( $_POST['shop_domain'] ) : '';
		$client_id      = isset( $_POST['client_id'] ) ? sanitize_text_field( $_POST['client_id'] ) : '';
		$client_secret  = isset( $_POST['client_secret'] ) ? sanitize_text_field( $_POST['client_secret'] ) : '';

		if ( empty( $shop_domain ) || empty( $client_id ) || empty( $client_secret ) ) {
			wp_send_json_error( 'Missing required fields' );
		}

		$result = Viw2s_API_Settings::save_oauth_credentials( $shop_domain, $client_id, $client_secret );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		// Get granted scopes for display
		$settings = Viw2s_API_Settings::get_settings( $shop_domain );
		$scopes = isset( $settings['token_scope'] ) ? $settings['token_scope'] : '';
		
		wp_send_json_success( [
			'message' => 'OAuth credentials saved successfully',
			'scopes'  => $scopes,
		] );
	}

	/**
	 * AJAX: Save Legacy credentials
	 */
	public function save_legacy_credentials() {
		check_ajax_referer( 'viw2s_action_nonce', '_nonce' );

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$shop_domain  = isset( $_POST['shop_domain'] ) ? sanitize_text_field( $_POST['shop_domain'] ) : '';
		$api_key      = isset( $_POST['api_key'] ) ? sanitize_text_field( $_POST['api_key'] ) : '';
		$api_token    = isset( $_POST['api_token'] ) ? sanitize_text_field( $_POST['api_token'] ) : '';

		if ( empty( $shop_domain ) || empty( $api_key ) || empty( $api_token ) ) {
			wp_send_json_error( 'Missing required fields' );
		}

		// Test connection first
		$connection_test = Viw2s_API_Settings::test_legacy_connection( $api_key, $api_token, $shop_domain );

		if ( is_wp_error( $connection_test ) ) {
			wp_send_json_error( $connection_test->get_error_message() );
		}

		// Update the legacy viw2s_params
		$params = get_option( 'viw2s_params', [] );
		if ( ! isset( $params['viw2s_store_setting'] ) ) {
			$params['viw2s_store_setting'] = [];
		}

		// Find or add store
		$found = false;
		foreach ( $params['viw2s_store_setting'] as &$store ) {
			if ( ( $store['domain'] ?? '' ) === $shop_domain ) {
				$store['api_key'] = $api_key;
				$store['api_secret'] = $api_token;
				$store['validate'] = true;
				$store['oauth_enabled'] = false;
				$found = true;
				break;
			}
		}

		if ( ! $found ) {
			$params['viw2s_store_setting'][] = [
				'domain' => $shop_domain,
				'api_key' => $api_key,
				'api_secret' => $api_token,
				'validate' => true,
				'oauth_enabled' => false,
			];
		}

		update_option( 'viw2s_params', $params );

		wp_send_json_success( [
			'message' => 'Legacy API credentials saved successfully',
		] );
	}

	/**
	 * AJAX: Test connection
	 */
	public function test_connection() {
		check_ajax_referer( 'viw2s_action_nonce', '_nonce' );

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$api_type     = isset( $_POST['api_type'] ) ? sanitize_text_field( $_POST['api_type'] ) : 'legacy';
		$shop_domain  = isset( $_POST['shop_domain'] ) ? sanitize_text_field( $_POST['shop_domain'] ) : '';

		if ( empty( $shop_domain ) ) {
			wp_send_json_error( 'Shop domain is required' );
		}

		if ( 'oauth' === $api_type ) {
			$settings = Viw2s_API_Settings::get_settings( $shop_domain );

			if ( empty( $settings ) ) {
				wp_send_json_error( 'No OAuth credentials found' );
			}

			$result = Viw2s_OAuth_Handler::test_connection(
				$settings['access_token'],
				$shop_domain
			);
		} else {
			$api_key   = isset( $_POST['api_key'] ) ? sanitize_text_field( $_POST['api_key'] ) : '';
			$api_token = isset( $_POST['api_token'] ) ? sanitize_text_field( $_POST['api_token'] ) : '';

			$result = Viw2s_API_Settings::test_legacy_connection( $api_key, $api_token, $shop_domain );
		}

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		wp_send_json_success( [
			'message' => 'Connection verified successfully',
		] );
	}

	/**
	 * AJAX: Delete API credentials
	 */
	public function delete_api_credentials() {
		// Log for debugging
		error_log( 'DELETE AJAX CALLED' );
		error_log( 'POST data: ' . print_r( $_POST, true ) );
		
		check_ajax_referer( 'viw2s_action_nonce', '_nonce' );

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			error_log( 'Permission denied' );
			wp_send_json_error( 'Insufficient permissions' );
		}

		$shop_domain = isset( $_POST['shop_domain'] ) ? sanitize_text_field( $_POST['shop_domain'] ) : '';
		$api_type    = isset( $_POST['api_type'] ) ? sanitize_text_field( $_POST['api_type'] ) : 'legacy';

		error_log( 'Shop domain: ' . $shop_domain );
		error_log( 'API type: ' . $api_type );

		if ( empty( $shop_domain ) ) {
			error_log( 'Shop domain is empty' );
			wp_send_json_error( 'Shop domain is required' );
		}

		$result = Viw2s_API_Settings::delete_credentials( $shop_domain );

		error_log( 'Delete result: ' . ( $result ? 'true' : 'false' ) );

		if ( ! $result ) {
			wp_send_json_error( 'Failed to delete credentials' );
		}

		error_log( 'Sending success response' );
		wp_send_json_success( [
			'message' => 'Credentials deleted successfully',
		] );
	}
}

// Initialize AJAX handlers
new Viw2s_API_AJAX_Handler();
