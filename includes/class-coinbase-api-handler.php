<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sends API requests to Coinbase.
 */
class Coinbase_API_Handler {

	/** @var string/array Log variable function. */
	public static $log;
	/**
	 * Call the $log variable function.
	 *
	 * @param string $message Log message.
	 * @param string $level   Optional. Default 'info'.
	 *     emergency|alert|critical|error|warning|notice|info|debug
	 */
	public static function log( $message, $level = 'info' ) {
		return call_user_func( self::$log, $message, $level );
	}

	/** @var string Coinbase API url. */
	public static $api_url = 'https://api.commerce.coinbase.com/';

	/** @var string Coinbase API version. */
	public static $api_version = '2018-03-22';

	/** @var string Coinbase API key. */
	public static $api_key;

	/**
	 * Get the response from an API request.
	 * @param  string $endpoint
	 * @param  array  $args
	 * @param  string $method
	 * @return array
	 */
	public static function send_request( $endpoint, $args = array(), $method = 'GET' ) {
		$url = esc_url_raw( add_query_arg( $args, self::$api_url . $endpoint ) );
		// phpcs:ignore
		self::log( 'Coinbase Request Args for ' . $endpoint . ': ' . print_r( $args, true ) );
		$args = array(
			'method'  => $method,
			'headers' => array(
				'X-CC-Api-Key' => self::$api_key,
				'X-CC-Version' => self::$api_version,
			),
		);

		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			self::log( 'WP response error: ' . $response->get_error_message() );
			return array( false, $response->get_error_message() );
		} else {
			$result = json_decode( $response['body'], true );
			if ( ! empty( $result['warnings'] ) ) {
				foreach ( $result['warnings'] as $warning ) {
					self::log( 'API Warning: ' . $warning );
				}
			}

			$code = $response['response']['code'];

			if ( in_array( $code, array( 200, 201 ), true ) ) {
				return array( true, $result );
			} else {
				$e      = empty( $result['error']['message'] ) ? '' : $result['error']['message'];
				$errors = array(
					400 => 'Error response from API: ' . $e,
					401 => 'Authentication error, please check your API key.',
					429 => 'Coinbase API rate limit exceeded.',
				);

				if ( array_key_exists( $code, $errors ) ) {
					$msg = $errors[ $code ];
				} else {
					$msg = 'Unknown response from API: ' . $code;
				}
				self::log( $msg );

				return array( false, $code );
			}
		}
	}

	/**
	 * Check if authentication is successful.
	 * @return bool|string
	 */
	public static function check_auth() {
		$result = self::send_request( 'checkouts', array( 'limit' => 0 ) );

		if ( ! $result[0] ) {
			return 401 === $result[1] ? false : 'error';
		}

		return true;
	}

	/**
	 * Create a new charge request.
	 * @param  int    $amount
	 * @param  string $currency
	 * @param  array  $metadata
	 * @param  string $redirect
	 * @param  string $name
	 * @param  string $desc
	 * @return array
	 */
	public static function create_charge( $amount = null, $currency = null, $metadata = null,
										$redirect = null, $name = null, $desc = null ) {
		$args = array(
			'name'        => is_null( $name ) ? get_bloginfo( 'name' ) : $name,
			'description' => is_null( $desc ) ? get_bloginfo( 'description' ) : $desc,
		);

		if ( is_null( $amount ) ) {
			$args['pricing_type'] = 'no_price';
		} elseif ( is_null( $currency ) ) {
			self::log( 'Error: if amount if given, currency must be given (in create_charge()).', 'error' );
			return array( false, 'Missing currency.' );
		} else {
			$args['pricing_type'] = 'fixed_price';
			$args['local_price']  = array(
				'amount'   => $amount,
				'currency' => $currency,
			);
		}

		if ( ! is_null( $metadata ) ) {
			$args['metadata'] = $metadata;
		}
		if ( ! is_null( $redirect ) ) {
			$args['redirect_url'] = $redirect;
		}

		$result = self::send_request( 'charges', $args, 'POST' );

		// Cache last-known available payment methods.
		if ( ! empty( $result[1]['data']['addresses'] ) ) {
			update_option(
				'coinbase_payment_methods',
				array_keys( $result[1]['data']['addresses'] ),
				false
			);
		}

		return $result;
	}
}
