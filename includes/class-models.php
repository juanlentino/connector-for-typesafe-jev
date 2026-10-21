<?php
/**
 * The models and aliases the account can send, from GET /v1/models.
 *
 * @package JevConnector
 */

declare( strict_types = 1 );

namespace JevConnector;

defined( 'ABSPATH' ) || exit;

/**
 * Lists models the way the core AI provider connectors do: live from the
 * API, cached for a day, never hardcoded. A new Jev release needs no plugin
 * release. The list is informational: the API accepts versioned ids such as
 * `jev-1.14.0` whether or not they are listed, so nothing is validated
 * against it.
 */
final class Models {

	public const ENDPOINT     = '/v1/models';
	public const CACHE_PREFIX = 'jevc_models_';
	public const TTL          = DAY_IN_SECONDS;

	/**
	 * API key used for this instance.
	 *
	 * @var string
	 */
	private string $api_key;

	/**
	 * Constructor.
	 *
	 * @param string|null $api_key Explicit key, or null to read the stored one.
	 */
	public function __construct( ?string $api_key = null ) {
		$this->api_key = null !== $api_key ? $api_key : Connector::get_api_key();
	}

	/**
	 * The models and aliases, each with name, description and release_date.
	 *
	 * @param bool $refresh Skip the cache and ask the API.
	 * @return list<array{name:string,description:string,release_date:string}>|\WP_Error
	 */
	public function all( bool $refresh = false ) {
		if ( '' === $this->api_key ) {
			return Client::not_configured();
		}

		// Keyed by account, so a key swap never serves the previous account's list.
		$key = self::CACHE_PREFIX . substr( md5( $this->api_key ), 0, 12 );

		if ( ! $refresh ) {
			$cached = get_transient( $key );

			if ( is_array( $cached ) ) {
				return $cached;
			}
		}

		// ponytail: one attempt, no retry loop; a failure is not cached, so the next call retries.
		$response = wp_remote_get(
			Client::API_BASE . self::ENDPOINT,
			array(
				'headers'     => Client::headers( $this->api_key ),
				'timeout'     => (int) apply_filters( 'jevc_http_timeout', 20 ),
				'redirection' => 0,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status = (int) wp_remote_retrieve_response_code( $response );
		$raw    = (string) wp_remote_retrieve_body( $response );

		if ( 200 !== $status ) {
			return Client::error_for_status( $status, $raw );
		}

		$decoded = json_decode( $raw, true );
		$models  = is_array( $decoded ) ? self::parse( $decoded ) : null;

		if ( null === $models ) {
			return new \WP_Error(
				'jevc_invalid_response',
				__( 'TypeSafe returned a response that could not be decoded.', 'connector-for-typesafe-jev' )
			);
		}

		set_transient( $key, $models, self::TTL );

		return $models;
	}

	/**
	 * Read a ModelMetadataList body.
	 *
	 * Entries without a name are dropped; missing text fields become ''.
	 *
	 * @param array<string,mixed> $body Decoded response body.
	 * @return list<array{name:string,description:string,release_date:string}>|null Null when the shape is wrong.
	 */
	public static function parse( array $body ): ?array {
		if ( ! isset( $body['models'] ) || ! is_array( $body['models'] ) ) {
			return null;
		}

		$models = array();

		foreach ( $body['models'] as $entry ) {
			if ( ! is_array( $entry ) || ! isset( $entry['name'] ) || ! is_string( $entry['name'] ) || '' === $entry['name'] ) {
				continue;
			}

			$models[] = array(
				'name'         => $entry['name'],
				'description'  => isset( $entry['description'] ) ? (string) $entry['description'] : '',
				'release_date' => isset( $entry['release_date'] ) ? (string) $entry['release_date'] : '',
			);
		}

		return $models;
	}
}
