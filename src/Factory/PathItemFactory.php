<?php
/**
 * PathItemFactory class file
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\SwaggerGenerator\Factory;

use Alley\WP\SwaggerGenerator\HttpMethod;
use Alley\WP\SwaggerGenerator\Objects\Route;
use cebe\openapi\spec\PathItem;
use InvalidArgumentException;

/**
 * Path Item Factory class.
 *
 * @extends Factory<\cebe\openapi\spec\PathItem, array{
 *   document: \cebe\openapi\spec\OpenApi,
 *   route: \Alley\WP\SwaggerGenerator\Objects\Route
 * }>
 */
class PathItemFactory extends Factory {
	/**
	 * Generate the factory object(s).
	 *
	 * @return PathItem
	 */
	public function generate(): PathItem {
		$this->validate_arguments( [ 'route' => Route::class ] );

		$path = new PathItem( [] );

		// dd($this->arguments);
		// dd($this->arguments['route']);

		foreach ( $this->arguments['route']->handlers as $handler ) {
			foreach ( $handler->methods() as $method ) {
				// dd($handler);

				$path->{strtolower( $method->value )} = OperationFactory::make( $this->generator, $this->forward_arguments( [
					'handler' => $handler,
					'method'  => $method,
				] ) );
			}
		}

		return $path;
	}
}
