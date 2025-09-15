<?php
use Codexpert\DiditVerification\Helper;
if( ! function_exists( 'get_plugin_data' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

/**
 * Gets the site's base URL
 * 
 * @uses get_bloginfo()
 * 
 * @return string $url the site URL
 */
if( ! function_exists( 'cd_site_url' ) ) :
function cd_site_url() {
	$url = get_bloginfo( 'url' );

	return $url;
}
endif;

add_action('wp_ajax_didit_create_verification', 'didit_create_verification');
add_action('wp_ajax_nopriv_didit_create_verification', 'didit_create_verification');

function didit_create_verification() {
    $api_key     = Helper::get_option('didit-verification_basic', 'didit_api_key');
    $workflow_id = '54d514aa-37bf-44ca-9f5b-91c58b8fe037';

    $payload = [
        'workflow_id' => $workflow_id,
        'vendor_data' => 'user-' . get_current_user_id(),
        'callback'    => home_url('/didit/callback/'),
        'metadata'    => [ 'wp_user_id' => get_current_user_id() ],
    ];

    $response = wp_remote_post('https://verification.didit.me/v2/session/', [
        'headers' => [
            'Content-Type' => 'application/json',
            'x-api-key'    => $api_key,
        ],
        'body'    => wp_json_encode($payload),
        'timeout' => 20,
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error(['error' => $response->get_error_message()], 500);
    }

    $code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);

    if ($code >= 200 && $code < 300 && !empty($body['url'])) {
        // ✅ Return JSON for JS to handle
        wp_send_json_success([
            'verification_url' => $body['url']
        ]);
    }

    wp_send_json_error(['error' => 'Could not create Didit session.'], $code);
}


