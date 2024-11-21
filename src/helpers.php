<?php
/**
 * wp-swagger-generator Helper Functions
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\Swagger_Generator;

use function Mantle\Support\Helpers\str;

/**
 * Sanitize a route for OpenAPI.
 *
 * Convert a route like '/wp/v2/posts/(?P<id>\d+)' to '/wp/v2/posts/{id}'.
 *
 * @param string $route Route to sanitize.
 * @return string
 */
function sanitize_route_for_openapi( string $route ): string {
	return str( $route )
		->replace_matches( '/\(\?P<([^>]+)>[^)]+\)/', '{\1}' )
		->replace( '}?)/', '/' )
		->explode( '/' )
		->map(
			function ( string $part ) {
				// Remove all characters after '}' while preserving the '}'.
				if ( false !== strpos( $part, '}' ) ) {
					$part = substr( $part, 0, strpos( $part, '}' ) + 1 );
				}

				return $part;
			}
		)
		->implode_str( '/' )
		// Replace some one-off regex patterns from core that are hard to convert.
		->replace( '/\w%-]+)', '' )
		->replace( '})', '}' );
}

/**
 * Validate a route for OpenAPI.
 *
 * @param string $route Route to validate.
 * @return bool
 */
function validate_route_for_openapi( string $route ): bool {
	return ! str( $route )->contains( [
		'?P',
		'(',
		')',
		'<',
		'>',
	] );
}
