<?php
/**
 * OAuth Token Refresh Cron
 *
 * Automatically refreshes OAuth tokens every 18 hours to prevent expiration
 *
 * @package    w2s-migrate-woo-to-shopify
 * @subpackage w2s-migrate-woo-to-shopify/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Viw2s_OAuth_Token_Refresh_Cron {

	const CRON_HOOK = 'viw2s_oauth_token_refresh';
	const CRON_INTERVAL = 64800;

	public static function init() {
		add_filter( 'cron_schedules', array( __CLASS__, 'add_cron_interval' ) );
		add_action( self::CRON_HOOK, array( __CLASS__, 'refresh_all_tokens' ) );

		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time(), 'viw2s_18hours', self::CRON_HOOK );
		}
	}

	/**
	 * Add custom cron interval
	 *
	 * @param array $schedules Existing schedules
	 * @return array Modified schedules
	 */
	public static function add_cron_interval( $schedules ) {
		$schedules['viw2s_18hours'] = array(
			'interval' => self::CRON_INTERVAL,
			'display'  => esc_html__( 'Every 18 Hours (OAuth Token Refresh)', 'w2s-migrate-woo-to-shopify' ),
		);
		return $schedules;
	}

	/**
	 * Refresh all OAuth tokens
	 */
	public static function refresh_all_tokens() {
		$all_settings = Viw2s_API_Settings::get_settings();

		if ( empty( $all_settings ) || ! is_array( $all_settings ) ) {
			return;
		}

		foreach ( $all_settings as $shop_domain => $settings ) {
			if ( ( $settings['api_type'] ?? 'legacy' ) !== 'oauth' ) {
				continue;
			}

			if ( empty( $settings['client_id'] ) || empty( $settings['client_secret'] ) ) {
				continue;
			}

			$credentials = array(
				'shop_domain'   => $shop_domain,
				'client_id'     => $settings['client_id'],
				'client_secret' => Viw2s_OAuth_Handler::decrypt_secret( $settings['client_secret'] ),
			);

			$result = Viw2s_OAuth_Handler::refresh_access_token( $credentials );

			if ( is_wp_error( $result ) ) {
				continue;
			}

			Viw2s_API_Settings::save_settings( $shop_domain, array(
				'access_token'     => $result['access_token'],
				'token_expires_at' => $result['token_expires_at'],
				'token_scope'      => $result['scope'],
			) );

			$old_settings = get_option( 'viw2s_params', [] );
			if ( isset( $old_settings['viw2s_store_setting'] ) && is_array( $old_settings['viw2s_store_setting'] ) ) {
				foreach ( $old_settings['viw2s_store_setting'] as &$store ) {
					if ( ( $store['domain'] ?? '' ) === $shop_domain || ( $store['shop_domain'] ?? '' ) === $shop_domain ) {
						$store['access_token']     = $result['access_token'];
						$store['token_expires_at'] = $result['token_expires_at'];
						break;
					}
				}
				update_option( 'viw2s_params', $old_settings );
			}
		}
	}

	public static function manual_refresh() {
		self::refresh_all_tokens();
	}

	public static function deactivate() {
		$timestamp = wp_next_scheduled( self::CRON_HOOK );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::CRON_HOOK );
		}
	}
}
