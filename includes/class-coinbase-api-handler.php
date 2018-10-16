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
	 * @param  array  $params
	 * @param  string $method
	 * @return array
	 */
	public static function send_request( $endpoint, $params = array(), $method = 'GET' ) {

        $args = array(
            'method'  => $method,
            'headers' => array(
                'X-CC-Api-Key' => self::$api_key,
                'X-CC-Version' => self::$api_version,
                'Content-Type' => 'application/json'
            )
        );

        $url = self::$api_url . $endpoint;

        if (in_array( $method, array('POST', 'PUT') ) ) {
            $args['body'] = json_encode($params);
        } else {
            $url = add_query_arg( $params, $url );
        }
		// phpcs:ignore
		self::log( 'Coinbase Request Args for ' . $endpoint . ': ' . print_r( $params, true ) );
		$response = wp_remote_request( esc_url_raw($url), $args);

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
    public static function create_charge($params)
    {
        $args = array(
            'name' => is_null($params['name']) ? get_bloginfo('name') : $params['name'],
            'description' => is_null($params['desc']) ? get_bloginfo('description') : $params['desc'],
        );

        if (is_null($params['amount']) || !is_numeric($params['amount'])) {
            self::log('Error: amount is missing or invalid.', 'error');
            return array(false, 'Missing amount.');
        }

        if (is_null($params['currency'])) {
            self::log('Error: if amount if given, currency must be given (in create_charge()).', 'error');
            return array(false, 'Missing currency.');
        }

        $args['pricing_type'] = 'fixed_price';
        $args['local_price'] = array(
            'amount' => $params['amount'],
            'currency' => $params['currency'],
        );


        if (!is_null($params['metadata'])) {
            $args['metadata'] = $params['metadata'];
        }
        if (!is_null($params['redirect'])) {
            $args['redirect_url'] = $params['redirect'];
        }

        if (!is_null($params['cancel'])) {
            $args['cancel_url'] = $params['cancel'];
        }

        $result = self::send_request('charges', $args, 'POST');

        // Cache last-known available payment methods.
        if (!empty($result[1]['data']['addresses'])) {
            update_option(
                'coinbase_payment_methods',
                array_keys($result[1]['data']['addresses']),
                false
            );
        }

        return $result;
    }
}
