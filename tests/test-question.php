<?php
/**
 * Question builder tests.
 *
 * @package JevConnector
 */

declare( strict_types = 1 );

use JevConnector\Exception;
use JevConnector\Question;
use PHPUnit\Framework\TestCase;

/**
 * Covers the three primitives and map validation.
 */
final class QuestionTest extends TestCase {

	/**
	 * A noul carries only type and instructions unless criteria are given.
	 *
	 * @return void
	 */
	public function test_noul_shape(): void {
		$this->assertSame(
			array(
				'type'         => 'noul',
				'instructions' => 'Is this urgent?',
			),
			Question::noul( 'Is this urgent?' )
		);
	}

	/**
	 * Choice keeps the option map as criteria.
	 *
	 * @return void
	 */
	public function test_choice_shape(): void {
		$question = Question::choice(
			'Which team?',
			array(
				'returns'  => 'Exchanges and refunds',
				'shipping' => 'Delivery status',
			)
		);

		$this->assertSame( 'choice', $question['type'] );
		$this->assertArrayHasKey( 'returns', $question['criteria'] );
	}

	/**
	 * Choice needs at least two options.
	 *
	 * @return void
	 */
	public function test_choice_rejects_single_option(): void {
		$this->expectException( Exception::class );
		Question::choice( 'Which team?', array( 'returns' => 'Only one' ) );
	}

	/**
	 * Score keeps levels ordered and zero-indexed.
	 *
	 * @return void
	 */
	public function test_score_reindexes_levels(): void {
		$question = Question::score(
			'How severe?',
			array(
				5 => 'Cosmetic',
				9 => 'Blocking',
			)
		);

		$this->assertSame( array( 'Cosmetic', 'Blocking' ), $question['criteria'] );
	}

	/**
	 * Score caps out at ten levels.
	 *
	 * @return void
	 */
	public function test_score_rejects_eleven_levels(): void {
		$this->expectException( Exception::class );
		Question::score( 'How severe?', array_fill( 0, 11, 'level' ) );
	}

	/**
	 * An unknown type is refused before any request is made.
	 *
	 * @return void
	 */
	public function test_validate_map_rejects_unknown_type(): void {
		$this->expectException( Exception::class );
		Question::validate_map(
			array(
				'q' => array(
					'type'         => 'vibes',
					'instructions' => 'Anything?',
				),
			)
		);
	}

	/**
	 * An empty map is refused.
	 *
	 * @return void
	 */
	public function test_validate_map_rejects_empty(): void {
		$this->expectException( Exception::class );
		Question::validate_map( array() );
	}

	/**
	 * Instructions must be a non-empty string; REST callers can send anything.
	 *
	 * @return void
	 */
	public function test_validate_map_rejects_non_string_instructions(): void {
		foreach ( array( array( 'x' ), '', 42 ) as $bad ) {
			try {
				Question::validate_map(
					array(
						'q' => array(
							'type'         => 'noul',
							'instructions' => $bad,
						),
					)
				);
				$this->fail( 'Accepted instructions: ' . wp_json_encode( $bad ) );
			} catch ( JevConnector\Exception $e ) {
				$this->assertStringContainsString( 'instructions', $e->getMessage() );
			}
		}
	}

	/**
	 * The REST model pattern takes real ids and refuses anything else.
	 *
	 * @return void
	 */
	public function test_rest_model_pattern(): void {
		$pattern = '/' . JevConnector\REST_Controller::MODEL_PATTERN . '/';

		foreach ( array( 'jev-latest', 'jev-preview', 'jev-1.14.0' ) as $ok ) {
			$this->assertSame( 1, preg_match( $pattern, $ok ), $ok );
		}

		foreach ( array( '', 'jev latest', 'jev-latest<script>', str_repeat( 'a', 65 ), "jev\nlatest" ) as $bad ) {
			$this->assertSame( 0, preg_match( $pattern, $bad ), $bad );
		}
	}
}
