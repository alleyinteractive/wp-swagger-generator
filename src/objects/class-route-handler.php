<?php
/**
 * Route_Handler class file.
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\Swagger_Generator\Objects;

use Alley\WP\Swagger_Generator\Http_Method;

use function Mantle\Support\Helpers\collect;

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

	/**
	 * Get the HTTP methods as enum instances.
	 *
	 * @return array<Http_Method>
	 */
	public function methods(): array {
		return collect( $this->methods )->map( fn ( string $method ) => Http_Method::from( strtoupper( $method ) ) )->all();
	}
}
