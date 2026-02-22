<?php
/**
 * RouteHandler class file.
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\SwaggerGenerator\Objects;

use Alley\WP\SwaggerGenerator\HttpMethod;

use function Mantle\Support\Helpers\collect;
use function Mantle\Support\Helpers\stringable;

/**
 * Route Handler Object.
 */
readonly class RouteHandler {
	/**
	 * HTTP Methods.
	 *
	 * @var array<string>
	 */
	public readonly array $methods;

	/**
	 * Constructor.
	 *
	 * @param array<string>        $methods HTTP Methods.
	 * @param mixed                $callback Callback.
	 * @param array<string, mixed> $arguments Arguments.
	 */
	public function __construct( array $methods, public mixed $callback, public array $arguments ) {
		// Normalize methods into an array of uppercase strings and remove any comma-separated values.
		$this->methods = collect( $methods )->map( static function ( string $method ) {
			if ( str_contains( $method, ',' ) ) {
				return explode( ',', $method );
			}

			return $method;
		} )->flatten()->map(
			static fn ( string $method ) => strtoupper( trim( $method ) )
		)->all();
	}

	/**
	 * Get the HTTP methods as enum instances.
	 *
	 * @return array<HttpMethod>
	 */
	public function methods(): array {
		if ( is_string( $this->methods ) && str_contains( $this->methods, ',' ) === false ) {
			$this->methods = stringable( $this->methods )->split( ',' )->map( fn ( string $method ) => trim( $method ) )->all();
		}
		dump($this->methods);
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
