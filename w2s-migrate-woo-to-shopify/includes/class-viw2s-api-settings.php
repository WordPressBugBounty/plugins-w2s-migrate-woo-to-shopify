<?php

/**
 * API Settings Handler
 *
 * Manages both Legacy (API Key + Token) and OAuth authentication methods
 * Ported from Pro version but adapted for Free version data structures.
 *
 * @package    w2s-migrate-woo-to-shopify
 * @subpackage w2s-migrate-woo-to-shopify/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Viw2s_API_Settings {

	const SETTINGS_KEY = 'viw2s_shopify_api_settings';

	/**
	 * Get API settings for a store
	 *
	 * @param string $shop_domain Shop domain
	 * @return array API settings
	 */
	public static function get_settings( $shop_domain = '' ) {
		$all_settings = get_option( self::SETTINGS_KEY, [] );

		if ( empty( $shop_domain ) ) {
			return $all_settings;
		}

		return $all_settings[ $shop_domain ] ?? [];
	}

	/**
	 * Save API settings
	 *
	 * @param string $shop_domain Shop domain
	 * @param array  $settings Settings to save
	 * @return bool Success
	 */
	public static function save_settings( $shop_domain, $settings ) {
		$all_settings                           = get_option( self::SETTINGS_KEY, [] );
		$all_settings[ $shop_domain ]           = wp_parse_args( $settings, $all_settings[ $shop_domain ] ?? [] );
		$all_settings[ $shop_domain ]['updated'] = current_time( 'mysql' );

		return update_option( self::SETTINGS_KEY, $all_settings );
	}

	/**
	 * Save OAuth credentials
	 *
	 * @param string $shop_domain Shop domain
	 * @param string $client_id OAuth Client ID
	 * @param string $client_secret OAuth Client Secret
	 * @return bool|WP_Error Success or error
	 */
	public static function save_oauth_credentials( $shop_domain, $client_id, $client_secret ) {
		$shop_domain = sanitize_text_field( trim( $shop_domain ) );
		$client_id   = sanitize_text_field( trim( $client_id ) );
		$client_secret = sanitize_text_field( trim( $client_secret ) );

		if ( empty( $shop_domain ) || empty( $client_id ) || empty( $client_secret ) ) {
			return new WP_Error( 'missing_fields', 'Shop domain, Client ID and Secret are required' );
		}

		// Test credentials by attempting OAuth token exchange
		$credentials = [
			'shop_domain'    => $shop_domain,
			'client_id'      => $client_id,
			'client_secret'  => $client_secret,
		];

		$token_result = Viw2s_OAuth_Handler::refresh_access_token( $credentials );

		if ( is_wp_error( $token_result ) ) {
			return $token_result;
		}

		// Test connection with new token (Step 2)
		$connection_test = Viw2s_OAuth_Handler::test_connection(
			$token_result['access_token'],
			$shop_domain
		);

		if ( is_wp_error( $connection_test ) ) {
			return $connection_test;
		}

		// Save credentials to new OAuth settings table
		$settings = [
			'api_type'            => 'oauth',
			'client_id'           => $client_id,
			'client_secret'       => Viw2s_OAuth_Handler::encrypt_secret( $client_secret ),
			'shop_domain'         => $shop_domain,
			'access_token'        => $token_result['access_token'],
			'token_expires_at'    => $token_result['token_expires_at'],
			'token_scope'         => $token_result['scope'],
			'connection_verified' => true,
		];

		self::save_settings( $shop_domain, $settings );

		// Also update old viw2s_params for compatibility with existing code
		$old_settings = get_option( 'viw2s_params', [] );
		if ( ! isset( $old_settings['viw2s_store_setting'] ) ) {
			$old_settings['viw2s_store_setting'] = [];
		}

		// Find or create store entry
		$found = false;
		foreach ( $old_settings['viw2s_store_setting'] as &$store ) {
			if ( ( $store['shop_domain'] ?? '' ) === $shop_domain || ( $store['domain'] ?? '' ) === $shop_domain ) {
				$store['domain'] = $shop_domain;
				$store['api_key'] = '';
				$store['api_secret'] = '';
				$store['validate'] = true;
				$store['oauth_enabled'] = true;
                $store['api_type'] = 'oauth';
				$found = true;
				break;
			}
		}

		if ( ! $found ) {
			// Remove ALL whitespace from scope string to handle newlines/tabs properly
			$cleaned_scopes = preg_replace( '/\s+/', '', $token_result['scope'] );
			$new_store = [
				'domain' => $shop_domain,
				'shop_domain' => $shop_domain,
				'api_key' => '',
				'api_secret' => '',
				'validate' => true,
				'oauth_enabled' => true,
                'api_type' => 'oauth',
				'get_access_scopes_handle' => array_filter( explode( ',', $cleaned_scopes ) ),
			];

            // Free Version Limit: Ensure only 1 active store if needed,
            // but since JS handles limit, we just prepend here to be safe and make it primary.
            if ( ! empty( $old_settings['viw2s_store_setting'] ) ) {
				foreach ( $old_settings['viw2s_store_setting'] as &$store ) {
					$store['validate'] = false; // Disable old stores
				}
			}
            array_unshift( $old_settings['viw2s_store_setting'], $new_store );

		} else {
			// Also add scope to existing store
			foreach ( $old_settings['viw2s_store_setting'] as &$store ) {
				if ( ( $store['shop_domain'] ?? '' ) === $shop_domain || ( $store['domain'] ?? '' ) === $shop_domain ) {
					// Remove ALL whitespace from scope string to handle newlines/tabs properly
					$cleaned_scopes = preg_replace( '/\s+/', '', $token_result['scope'] );
					$store['get_access_scopes_handle'] = array_filter( explode( ',', $cleaned_scopes ) );
                    // Ensure OAuth fields are set in legacy array too if used by other parts
                    $store['client_id'] = $client_id;
                    $store['client_secret'] = $client_secret; // Note: Storing raw secret in legacy array is risky but might be needed by old code. Ideally encrypt.
					break;
				}
			}
		}

		// Create default import options for this domain if not exist
		if ( ! isset( $old_settings['viw2s_import_option'] ) ) {
			$old_settings['viw2s_import_option'] = [];
		}

		if ( ! isset( $old_settings['viw2s_import_option'][ $shop_domain ] ) ) {
            $default_options = self::get_default_import_options();
            $old_settings['viw2s_import_option'][ $shop_domain ] = $default_options;
            // Also store in flat structure if needed
            $old_settings[ $shop_domain ] = $default_options;
		}

		update_option( 'viw2s_params', $old_settings );

		// Clear any legacy cache
		self::clear_cache( $shop_domain );

		return true;
	}

	/**
	 * Save legacy API credentials
	 *
	 * @param string $shop_domain Shop domain
	 * @param string $api_key API Key
	 * @param string $api_token API Access Token
	 * @return bool|WP_Error Success or error
	 */
	public static function save_legacy_credentials( $shop_domain, $api_key, $api_token ) {
		$shop_domain = sanitize_text_field( $shop_domain );
		$api_key     = sanitize_text_field( $api_key );
		$api_token   = sanitize_text_field( $api_token );

		if ( empty( $shop_domain ) || empty( $api_key ) || empty( $api_token ) ) {
			return new WP_Error( 'missing_fields', 'Shop domain, API Key and Token are required' );
		}

		// Test connection
		$connection_test = self::test_legacy_connection( $api_key, $api_token, $shop_domain );

		if ( is_wp_error( $connection_test ) ) {
			return $connection_test;
		}

		// Save credentials
		$settings = [
			'api_type'            => 'legacy',
			'api_key'             => self::encrypt_secret( $api_key ),
			'api_token'           => $api_token,
			'shop_domain'         => $shop_domain,
			'connection_verified' => true,
		];

		self::save_settings( $shop_domain, $settings );

        // Update legacy params
        $old_settings = get_option( 'viw2s_params', [] );
        if ( ! isset( $old_settings['viw2s_store_setting'] ) ) {
            $old_settings['viw2s_store_setting'] = [];
        }

        $found = false;
        foreach ( $old_settings['viw2s_store_setting'] as &$store ) {
            if ( ( $store['domain'] ?? '' ) === $shop_domain ) {
                $store['api_key'] = $api_key;
                $store['api_secret'] = $api_token; // Legacy uses api_secret field for token
                $store['validate'] = true;
                $store['oauth_enabled'] = false;
                $store['api_type'] = 'legacy';
                $found = true;
                break;
            }
        }

        if (!$found) {
            $new_store = [
                'domain' => $shop_domain,
                'api_key' => $api_key,
                'api_secret' => $api_token,
                'validate' => true,
                'oauth_enabled' => false,
                'api_type' => 'legacy',
            ];
             // Disable all existing stores
			if ( ! empty( $old_settings['viw2s_store_setting'] ) ) {
				foreach ( $old_settings['viw2s_store_setting'] as &$store ) {
					$store['validate'] = false;
				}
			}
            array_unshift( $old_settings['viw2s_store_setting'], $new_store );
        }

        update_option( 'viw2s_params', $old_settings );

		// Clear any OAuth cache
		delete_option( 'viw2s_shopify_oauth_' . $shop_domain );

		return true;
	}

	/**
	 * Test legacy API connection
	 *
	 * @param string $api_key API Key
	 * @param string $api_token API Token
	 * @param string $shop_domain Shop domain
	 * @return bool|WP_Error True if successful
	 */
	public static function test_legacy_connection( $api_key, $api_token, $shop_domain ) {
		$shop_domain = sanitize_text_field( $shop_domain );

		$url = 'https://' . $api_key . ':' . $api_token . '@' . $shop_domain . '/admin/api/2024-10/shop.json';

		$response = wp_remote_get( $url, [
			'timeout' => 30,
            'sslverify' => false,
		] );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $status_code ) {
			if ( 401 === $status_code ) {
				return new WP_Error( 'invalid_credentials', 'Invalid API Key or Token. Please check your Shopify credentials.' );
			}
			return new WP_Error( 'connection_failed', 'Failed to connect to Shopify API', [ 'status' => $status_code ] );
		}

		return true;
	}

	/**
	 * Get valid access token (refresh if needed)
	 *
	 * @param string $shop_domain Shop domain
	 * @return string|WP_Error Access token or error
	 */
	public static function get_access_token( $shop_domain ) {
		$settings = self::get_settings( $shop_domain );

		if ( empty( $settings ) ) {
			// Fallback to legacy settings check
             $old_settings = get_option( 'viw2s_params', [] );
             if ( isset( $old_settings['viw2s_store_setting'] ) ) {
                 foreach($old_settings['viw2s_store_setting'] as $store) {
                     if (($store['domain'] ?? '') === $shop_domain && ($store['validate'] ?? false)) {
                         // Found active legacy store
                         if (isset($store['oauth_enabled']) && $store['oauth_enabled']) {
                             // Is actually OAuth but settings missing? Try to recover or fail.
                             return new WP_Error( 'no_credentials', 'OAuth credentials missing' );
                         }
                         return $store['api_secret'] ?? ''; // Return legacy token
                     }
                 }
             }
			return new WP_Error( 'no_credentials', 'No API credentials found for this shop' );
		}

		// Legacy authentication
		if ( ( $settings['api_type'] ?? 'legacy' ) === 'legacy' ) {
			return $settings['api_token'] ?? '';
		}

		// OAuth - check if token needs refresh
		if ( ! empty( $settings['token_expires_at'] ) ) {
			$buffer = 60 * 60; // 1 hour
			if ( current_time( 'timestamp' ) >= ( $settings['token_expires_at'] - $buffer ) ) {
				$credentials = [
					'shop_domain'    => $shop_domain,
					'client_id'      => $settings['client_id'],
					'client_secret'  => Viw2s_OAuth_Handler::decrypt_secret( $settings['client_secret'] ),
				];

				$result = Viw2s_OAuth_Handler::refresh_access_token( $credentials );

				if ( is_wp_error( $result ) ) {
					return $result;
				}

				// Update stored token
				self::save_settings( $shop_domain, [
					'access_token'     => $result['access_token'],
					'token_expires_at' => $result['token_expires_at'],
					'token_scope'      => $result['scope'],
				] );

				return $result['access_token'];
			}
		}

		return $settings['access_token'] ?? '';
	}

    /**
     * Encrypt secret
     */
    private static function encrypt_secret($secret) {
        return base64_encode($secret);
    }

	/**
	 * Get API authentication header
	 *
	 * @param string $shop_domain Shop domain
	 * @return array|WP_Error Authorization header or error
	 */
	public static function get_auth_header( $shop_domain ) {
		$settings = self::get_settings( $shop_domain );

		if ( ( $settings['api_type'] ?? 'legacy' ) === 'legacy' ) {
			// Legacy uses basic auth embedded in URL usually, or might need custom header?
            // Existing free code usually builds URL with user:pass@domain.
			return [];
		}

		$token = self::get_access_token( $shop_domain );

		if ( is_wp_error( $token ) ) {
			return $token;
		}

		return [
			'Authorization' => 'Bearer ' . $token,
		];
	}

	/**
	 * Check if credentials are set
	 *
	 * @param string $shop_domain Shop domain
	 * @return bool True if credentials exist
	 */
	public static function has_credentials( $shop_domain ) {
		$settings = self::get_settings( $shop_domain );
		return ! empty( $settings );
	}

	/**
	 * Clear cache for a shop
	 *
	 * @param string $shop_domain Shop domain
	 * @return void
	 */
	public static function clear_cache( $shop_domain ) {
		// Clear transients and cache related to this shop
		delete_transient( 'viw2s_shop_cache_' . $shop_domain );
	}

	/**
	 * Delete credentials for a shop
	 *
	 * @param string $shop_domain Shop domain
	 * @return bool Success
	 */
	public static function delete_credentials( $shop_domain ) {
		$all_settings = get_option( self::SETTINGS_KEY, [] );
		unset( $all_settings[ $shop_domain ] );
		self::clear_cache( $shop_domain );
		delete_option( 'viw2s_shopify_oauth_' . $shop_domain );
		update_option( self::SETTINGS_KEY, $all_settings );

		$old_settings = get_option( 'viw2s_params', [] );
		if ( isset( $old_settings['viw2s_store_setting'] ) && is_array( $old_settings['viw2s_store_setting'] ) ) {
			$old_settings['viw2s_store_setting'] = array_filter(
				$old_settings['viw2s_store_setting'],
				function( $store ) use ( $shop_domain ) {
					return ( $store['domain'] ?? '' ) !== $shop_domain && ( $store['shop_domain'] ?? '' ) !== $shop_domain;
				}
			);
			update_option( 'viw2s_params', $old_settings );
		}

		return true;
	}

	/**
	 * Get default import options
     * Adapted for FREE version (Products & Categories only)
	 *
	 * @return array Default import options
	 */
	public static function get_default_import_options() {
		return array(
			'viw2s_import_products--product_by_type'                 => 'all',
			'viw2s_import_products--product_created_at_min'          => '',
			'viw2s_import_products--product_created_at_max'          => '',
			'viw2s_import_products--product_import_sequence'         => 'title asc',
			'viw2s_import_products--product_import_images_size'      => 'full',
			'viw2s_import_products--import_product_keep_slug'        => 'on',
			'viw2s_import_products--import_product_categories'       => 'on',
			'viw2s_import_products--import_product_tags'             => 'on',
			'viw2s_import_products--import_product_sku'              => 'on',
			'viw2s_import_products--import_product_chanel_sales'     => 'web',
			'viw2s_import_products--import_product_status_mapping'   => array(
				'publish'        => 'active',
				'draft'          => 'draft',
				'pending_review' => 'archived',
			),
            // Free version does not support Orders, Customers, Coupons
		);
	}
}
