<?php
/**
 * Route class file.
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\Swagger_Generator\REST_API;

use function Alley\WP\Swagger_Generator\sanitize_route_for_openapi;
use function Alley\WP\Swagger_Generator\validate_route_for_openapi;

/**
 * Route Object.
 */
readonly class Route {
	/**
	 * Sanitized route.
	 *
	 * @var string
	 */
	public string|false $sanitized_route;

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
	 * Returns false if the route can't be validated for use in OpenAPI.
	 *
	 * @return string|false
	 */
	public function sanitized_route(): string|false {
		if ( isset( $this->sanitized_route ) ) {
			return $this->sanitized_route;
		}

		$this->sanitized_route = sanitize_route_for_openapi( $this->route );

		if ( ! validate_route_for_openapi( $this->sanitized_route ) ) {
			/**
			 * Filter an invalid route to be included in the OpenAPI document.
			 *
			 * @param string|null $route Sanitized route path.
			 * @param Route       $route Route object.
			 */
			$this->sanitized_route = apply_filters( 'wp_swagger_generator_invalid_route', $this->sanitized_route, $this );

			if ( ! $this->sanitized_route || ! validate_route_for_openapi( $this->sanitized_route ) ) {
				$this->sanitized_route = false;
			}
		}

		return $this->sanitized_route;
	}
}
