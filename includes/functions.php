<?php
/**
 * Public helper functions.
 *
 * @package JevConnector
 */

declare( strict_types = 1 );

namespace JevConnector;

defined( 'ABSPATH' ) || exit;

/**
 * Ask Jev one or more typed questions.
 *
 * Example:
 *
 *     $answer = JevConnector\ask(
 *         array( 'comment' => $comment_text ),
 *         array(
 *             'spam' => JevConnector\Question::noul( 'Is this comment spam?' ),
 *         )
 *     );
 *
 *     if ( ! is_wp_error( $answer ) && $answer->noul( 'spam' ) > 0.9 ) {
 *         // Act.
 *     }
 *
 * @param mixed               $state     String, array, or object to evaluate.
 * @param array<string,mixed> $questions Map of question id => question array.
 * @param array<string,mixed> $args      Optional request arguments.
 * @return Response|\WP_Error
 */
function ask( $state, array $questions, array $args = array() ) {
	return ( new Client() )->ask( $state, $questions, $args );
}

/**
 * The models and aliases the account can send in the `model` argument.
 *
 * Cached for a day per API key. Pinning a versioned id from this list with
 * the `jevc_default_model` filter keeps answers stable across Jev releases;
 * the default `jev-latest` follows them.
 *
 * @param bool $refresh Skip the cache and ask the API.
 * @return list<array{name:string,description:string,release_date:string}>|\WP_Error
 */
function models( bool $refresh = false ) {
	return ( new Models() )->all( $refresh );
}

/**
 * Whether the connector is ready to make requests.
 *
 * @return bool
 */
function is_ready(): bool {
	return ( new Client() )->is_configured();
}
