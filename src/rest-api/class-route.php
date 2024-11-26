<?php
/**
 * Route class file.
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\Swagger_Generator\REST_API;

use function Alley\WP\Swagger_Generator\sanitize_route_for_openapi;

/**
 * Route Object.
 */
readonly class Route {
	/**
	 * Constructor.
	 *
	 * @param string              $route Raw route.
	 * @param array<string>       $methods HTTP methods.
	 * @param array<int, array{
	 *   methods: array<string>,
	 *   callback: callable,
	 *   args: array<string, array<string, string>>,
	 *   permission_callback?: callable,
	 * }>                          $handlers Handlers.
	 * @param array<string, mixed> $options Options.
	 */
	public function __construct( public string $route, public array $methods, public array $handlers, public array $options ) {}

	/**
	 * Get the sanitized route.
	 *
	 * @return string
	 */
	public function sanitized_route(): string {
		return sanitize_route_for_openapi( $this->route );
	}
}
