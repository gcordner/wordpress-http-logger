<?php
/**
 * Plugin Name:     Http Logger
 * Plugin URI:      https://github.com/gcordner/wordpress-http-logger
 * Description:     Activate to log HTTP requests and responses to wp-content/http_requests.log. Deactivate to stop logging.
 * Author:          Geoff Cordner
 * Author URI:      https://geoffcordner.net
 * Text Domain:     http-logger
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Http_Logger
 */

// Register and add the settings page.
add_action( 'admin_init', 'http_logger_register_settings' );
add_action( 'admin_menu', 'http_logger_settings_page' );

/**
 * Registers the HTTP Logger settings.
 */
function http_logger_register_settings() {
	register_setting( 'http_logger', 'http_logger_active' );
}

/**
 * Renders the HTML for the HTTP Logger settings page.
 */
function http_logger_settings_page() {
	add_options_page( 'HTTP Logger Settings', 'HTTP Logger', 'manage_options', 'http-logger', 'http_logger_settings_page_html' );
}

/**
 * Renders the HTML for the HTTP Logger settings page.
 */
function http_logger_settings_page_html() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'http_logger' );
			do_settings_sections( 'http_logger' );
			?>
			<label for="http_logger_active">Activate Logging:</label>
			<input type="checkbox" id="http_logger_active" name="http_logger_active" value="1" <?php checked( 1, get_option( 'http_logger_active', 0 ), true ); ?> />
			<?php
			submit_button( 'Save Settings' );
			?>
		</form>
	</div>
	<?php
}

// Log HTTP Requests.
add_filter( 'http_request_args', 'log_wp_http_requests', 10, 2 );

/**
 * Log HTTP requests.
 *
 * @param array  $parsed_args The parsed arguments.
 * @param string $url The URL.
 * @return array The parsed arguments.
 */
function log_wp_http_requests( $parsed_args, $url ) {
	if ( get_option( 'http_logger_active' ) == 1 ) {
		$log_message_request = sprintf( "Request to %s: %s\n", $url, print_r( $parsed_args, true ) );
		error_log( $log_message_request, 3, WP_CONTENT_DIR . '/http_requests.log' );
	}
	return $parsed_args;
}

// Log HTTP Responses.
add_filter( 'http_response', 'log_wp_http_responses', 10, 3 );
/**
 * Log HTTP responses.
 *
 * @param mixed  $response     The response.
 * @param array  $parsed_args  The parsed arguments.
 * @param string $url          The URL.
 * @return mixed The response.
 */
function log_wp_http_responses( $response, $parsed_args, $url ) {
	if ( get_option( 'http_logger_active' ) == 1 ) {
		$log_message_response = sprintf( "Response from %s: %s\n", $url, print_r( $response, true ) );
		error_log( $log_message_response, 3, WP_CONTENT_DIR . '/http_requests.log' );
	}
	return $response;
}
