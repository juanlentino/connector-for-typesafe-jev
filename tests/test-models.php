<?php
/**
 * Models list tests against the queued fake transport.
 *
 * @package JevConnector
 */

declare( strict_types = 1 );

use JevConnector\Models;
use PHPUnit\Framework\TestCase;

/**
 * Covers GET /v1/models: request shape, parsing, caching, errors.
 */
final class ModelsTest extends TestCase {

	/**
	 * Reset the shared stub state before each test.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		JevTestState::reset();
	}

	/**
	 * The documented ModelMetadataList body.
	 *
	 * @return array<string,mixed>
	 */
	private function list_body(): array {
		return array(
			'models' => array(
				array(
					'name'         => 'jev-latest',
					'description'  => 'General-purpose system one model.',
					'release_date' => '2026-09-15',
				),
				array(
					'name'         => 'jev-preview',
					'description'  => 'Most recent release.',
					'release_date' => '2026-10-14',
				),
			),
		);
	}

	/**
	 * GET to /v1/models with the bearer key, parsed into name/description/release_date.
	 *
	 * @return void
	 */
	public function test_request_matches_api_contract(): void {
		JevTestState::$responses[] = JevTestState::http( 200, $this->list_body() );

		$models = JevConnector\models();

		$request = JevTestState::$requests[0];
		$this->assertSame( 'https://api.typesafe.ai/v1/models', $request['url'] );
		$this->assertSame( 'GET', $request['args']['method'] );
		$this->assertSame( 'Bearer test-key', $request['args']['headers']['Authorization'] );
		$this->assertSame( array( 'jev-latest', 'jev-preview' ), array_column( $models, 'name' ) );
		$this->assertSame( '2026-10-14', $models[1]['release_date'] );
	}

	/**
	 * A second call is served from the cache; refresh asks again.
	 *
	 * @return void
	 */
	public function test_list_is_cached_until_refresh(): void {
		JevTestState::$responses[] = JevTestState::http( 200, $this->list_body() );
		JevTestState::$responses[] = JevTestState::http( 200, array( 'models' => array( array( 'name' => 'jev-1.14.0' ) ) ) );

		$first  = JevConnector\models();
		$second = JevConnector\models();

		$this->assertSame( $first, $second );
		$this->assertCount( 1, JevTestState::$requests );

		$fresh = JevConnector\models( true );

		$this->assertCount( 2, JevTestState::$requests );
		$this->assertSame( 'jev-1.14.0', $fresh[0]['name'] );
	}

	/**
	 * The cache is per key, so a new key never sees the old account's list.
	 *
	 * @return void
	 */
	public function test_cache_is_scoped_to_the_key(): void {
		JevTestState::$responses[] = JevTestState::http( 200, $this->list_body() );
		JevTestState::$responses[] = JevTestState::http( 200, $this->list_body() );

		( new Models( 'key-a' ) )->all();
		( new Models( 'key-b' ) )->all();

		$this->assertCount( 2, JevTestState::$requests );
	}

	/**
	 * HTTP errors map like ask() errors and are not cached.
	 *
	 * @return void
	 */
	public function test_errors_are_mapped_and_not_cached(): void {
		JevTestState::$responses[] = JevTestState::http( 401, array( 'detail' => 'bad key' ) );
		JevTestState::$responses[] = JevTestState::http( 200, $this->list_body() );

		$error = JevConnector\models();

		$this->assertInstanceOf( WP_Error::class, $error );
		$this->assertSame( 'jevc_http_401', $error->get_error_code() );
		$this->assertCount( 2, JevConnector\models() );
	}

	/**
	 * Without a key nothing is sent.
	 *
	 * @return void
	 */
	public function test_not_configured_sends_nothing(): void {
		$error = ( new Models( '' ) )->all();

		$this->assertSame( 'jevc_not_configured', $error->get_error_code() );
		$this->assertSame( array(), JevTestState::$requests );
	}

	/**
	 * A wrong shape is rejected; nameless entries are dropped.
	 *
	 * @return void
	 */
	public function test_parse(): void {
		$this->assertNull( Models::parse( array( 'data' => array() ) ) );
		$this->assertSame(
			array(
				array(
					'name'         => 'jev-latest',
					'description'  => '',
					'release_date' => '',
				),
			),
			Models::parse( array( 'models' => array( array( 'name' => 'jev-latest' ), array( 'description' => 'no name' ), 'junk' ) ) )
		);
	}
}
