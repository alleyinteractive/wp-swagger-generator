<?php
/**
 * RouteHandler class file.
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\SwaggerGenerator\Objects;

use Alley\WP\SwaggerGenerator\HttpMethod;

use function Mantle\Support\Helpers\collect;

/**
 * Route Handler Object.
 */
readonly class RouteHandler {
	/**
	 * Constructor.
	 *
	 * @param array<string>        $methods HTTP Methods.
	 * @param mixed                $callback Callback.
	 * @param array<string, mixed> $arguments Arguments.
	 */
	public function __construct( public array $methods, public mixed $callback, public array $arguments ) {}

	/**
	 * Get the HTTP methods as enum instances.
	 *
	 * @return array<HttpMethod>
	 */
	public function methods(): array {
		return collect( $this->methods )->map( fn ( string $method ) => HttpMethod::from( strtoupper( $method ) ) )->all();
	}

	/**
	 * Determine if the handler supports a given HTTP method.
	 *
	 * @param HttpMethod $method HTTP Method.
	 * @return bool
	 */
	public function supports_method( HttpMethod $method ): bool {
		return in_array( $method->value, $this->methods, true );
	}
}
