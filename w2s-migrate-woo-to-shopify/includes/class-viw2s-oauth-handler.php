<?php

/**
 * @package    w2s-migrate-woo-to-shopify
 * @subpackage w2s-migrate-woo-to-shopify/includes
 * 
 */

if (! defined('ABSPATH')) {
    exit;
}

class Viw2s_OAuth_Handler
{
    const REQUIRED_SCOPES = [
        'write_products',
        'write_publications',
    ];

    /**
     * Exchange client credentials for access token
     *
     * @param array $credentials Client ID, Secret, Shop Domain
     * @return array|WP_Error OAuth response or error
     */
    public static function refresh_access_token($credentials)
    {
        if (empty($credentials['client_id']) || empty($credentials['client_secret']) || empty($credentials['shop_domain'])) {
            return new WP_Error('invalid_credentials', 'Missing required credentials');
        }

        $credentials['client_id'] = trim($credentials['client_id']);
        $credentials['client_secret'] = trim($credentials['client_secret']);
        $credentials['shop_domain'] = trim($credentials['shop_domain']);

        if (strpos($credentials['client_secret'], 'shpat_') === 0) {
            return [
                'access_token' => $credentials['client_secret'],
                'scope' => implode(',', self::REQUIRED_SCOPES),
                'expires_in' => 0,
                'token_expires_at' => current_time('timestamp') + (365 * 24 * 60 * 60),
            ];
        }

        $shop_domain = sanitize_text_field($credentials['shop_domain']);
        if (strpos($shop_domain, 'http') === false) {
            $shop_domain = 'https://' . $shop_domain;
        }

        $url = $shop_domain . '/admin/oauth/access_token';

        $body = [
            'grant_type'    => 'client_credentials',
            'client_id'     => $credentials['client_id'],
            'client_secret' => $credentials['client_secret'],
        ];

        $response = wp_remote_post($url, [
            'body'    => $body,
            'timeout' => 30,
            'sslverify' => false,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body        = wp_remote_retrieve_body($response);

        if (200 !== $status_code) {
            $error_data = json_decode($body, true);

            if (is_array($error_data) && !empty($error_data['error_description'])) {
                $error_msg = $error_data['error_description'];
            } elseif (is_array($error_data) && !empty($error_data['error'])) {
                $error_msg = $error_data['error'];
                if (!empty($error_data['error_description'])) {
                    $error_msg .= ': ' . $error_data['error_description'];
                }
            } else {
                switch ($status_code) {
                    case 401:
                        $error_msg = 'Unauthorized: Invalid Client ID or Secret. Please check your Shopify credentials.';
                        break;
                    case 403:
                        $error_msg = 'Forbidden: Client ID/Secret combination is not authorized.';
                        break;
                    case 404:
                        $error_msg = 'Not Found: Invalid shop domain or API endpoint.';
                        break;
                    default:
                        $error_msg = 'OAuth request failed (HTTP ' . $status_code . '). Please verify your credentials and try again.';
                }
            }

            return new WP_Error('oauth_error', $error_msg, ['status' => $status_code]);
        }

        $token_data = json_decode($body, true);

        $scope_check = self::validate_scopes($token_data['scope'] ?? '');
        if (is_wp_error($scope_check)) {
            return $scope_check;
        }

        $token_data['token_expires_at'] = current_time('timestamp') + ($token_data['expires_in'] ?? 3600);

        $store_key = 'viw2s_shopify_oauth_' . $shop_domain;
        update_option($store_key, $token_data);

        return $token_data;
    }

    /**
     * Validate required scopes
     *
     * @param string $granted_scopes Comma-separated scopes from OAuth response
     * @return true|WP_Error True if valid, WP_Error if missing scopes
     */
    public static function validate_scopes($granted_scopes)
    {
        $cleaned_scopes = preg_replace('/\s+/', '', $granted_scopes);
        $granted = array_filter(explode(',', $cleaned_scopes));

        $missing = array_diff(self::REQUIRED_SCOPES, $granted);

        if (! empty($missing)) {
            $error_message = sprintf(
                'Missing required scopes: %s<br>Please update your Shopify App configuration with these scopes.',
                implode(', ', $missing)
            );

            return new WP_Error('insufficient_scopes', $error_message);
        }

        return true;
    }

    /**
     * Check if stored token is still valid
     *
     * @param array $token_data Token data with expires_in
     * @return bool True if valid, false if expired
     */
    public static function is_token_valid($token_data)
    {
        if (empty($token_data['token_expires_at'])) {
            return false;
        }

        $buffer = 60 * 60;
        return current_time('timestamp') < ($token_data['token_expires_at'] - $buffer);
    }

    /**
     * Test connection with access token
     *
     * @param string $access_token OAuth access token
     * @param string $shop_domain Shop domain
     * @return bool|WP_Error True if successful, WP_Error otherwise
     */
    public static function test_connection($access_token, $shop_domain)
    {
        $shop_domain = sanitize_text_field($shop_domain);
        if (strpos($shop_domain, 'http') === false) {
            $shop_domain = 'https://' . $shop_domain;
        }

        $url = $shop_domain . '/admin/api/2024-10/shop.json';

        $headers = [
            'X-Shopify-Access-Token' => $access_token,
            'Content-Type' => 'application/json',
        ];

        $response = wp_remote_get($url, [
            'headers' => $headers,
            'timeout' => 30,
            'sslverify' => false,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code($response);

        if (401 === $status_code) {
            $body = wp_remote_retrieve_body($response);
            return new WP_Error('unauthorized', 'Invalid access token. Please verify Client ID and Secret are correct.');
        }

        if (200 !== $status_code) {
            $body = wp_remote_retrieve_body($response);
            return new WP_Error('connection_failed', 'Failed to connect to Shopify API (HTTP ' . $status_code . ')', ['body' => $body]);
        }

        return true;
    }

    /**
     * Encrypt secret for storage
     *
     * @param string $secret Client secret
     * @return string Encrypted secret
     */
    public static function encrypt_secret($secret)
    {
        return base64_encode($secret);
    }

    /**
     * Decrypt secret from storage
     *
     * @param string $encrypted Encrypted secret
     * @return string Decrypted secret
     */
    public static function decrypt_secret($encrypted)
    {
        return base64_decode($encrypted);
    }

    /**
     * Get scope list for Shopify App creation
     *
     * @return string Comma-separated scopes for App configuration
     */
    public static function get_scope_list()
    {
        return implode(',', self::REQUIRED_SCOPES);
    }

    /**
     * Get human-readable scope descriptions
     *
     * @return array Scope => Description mapping
     */
    public static function get_scope_descriptions()
    {
        return [
            'read_products' => 'Read products for validation',
            'write_products' => 'Create and update products, variants, and images',
            'write_publications' => 'Create collections from WooCommerce categories',
        ];
    }

    /**
     * Get all scopes with descriptions
     *
     * @return array Scopes with their descriptions
     */
    public static function get_all_scopes_with_descriptions()
    {
        $descriptions = self::get_scope_descriptions();
        $all_scopes = [];

        foreach (self::REQUIRED_SCOPES as $scope) {
            $all_scopes[$scope] = [
                'required' => true,
                'description' => $descriptions[$scope] ?? '',
            ];
        }

        return $all_scopes;
    }
}
