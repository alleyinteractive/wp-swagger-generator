<?php
/**
 * Route_Handler class file.
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\Swagger_Generator\Objects;

/**
 * Route Handler Object.
 */
readonly class Route_Handler {
	/**
	 * Constructor.
	 *
	 * @param array<string>        $methods HTTP Methods.
	 * @param mixed                $callback Callback.
	 * @param array<string, mixed> $args Arguments.
	 */
	public function __construct( public array $methods, public mixed $callback, public array $args ) {}
}
