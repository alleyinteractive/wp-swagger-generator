<?php
/**
 * Path_Item_Factory class file
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\Swagger_Generator\Factory;

use Alley\WP\Swagger_Generator\Http_Method;
use Alley\WP\Swagger_Generator\Objects\Route;
use cebe\openapi\spec\PathItem;
use InvalidArgumentException;

/**
 * Path Item Factory class.
 *
 * @extends Factory<\cebe\openapi\spec\PathItem, array{
 *   document: \cebe\openapi\spec\OpenApi,
 *   route: \Alley\WP\Swagger_Generator\Objects\Route
 * }>
 */
class Path_Item_Factory extends Factory {
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

				$path->{strtolower( $method->value )} = Operation_Factory::make( $this->generator, $this->forward_arguments( [
					'handler' => $handler,
					'method'  => $method,
				] ) );
			}
		}

		return $path;
	}
}
