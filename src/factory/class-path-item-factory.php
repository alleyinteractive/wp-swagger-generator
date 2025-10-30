<?php
/**
 * Path_Item_Factory class file
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\Swagger_Generator\Factory;

use Alley\WP\Swagger_Generator\HTTP_Methods;
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

		foreach ( $this->arguments['handlers'] as $handler ) {
			foreach ( array_keys( $handler['methods'] ) as $method ) {
				$method = strtolower( $method );

				if ( ! HTTP_Methods::tryFrom( strtoupper( $method ) ) ) {
					throw new InvalidArgumentException( sprintf( 'Unsupported HTTP method "%s" for route "%s".', $method, $this->arguments['route']->route ) );
				}
				// if ( ! in_array( $method, self::SUPPORTED_METHODS, true ) ) {
				// 	continue;
				// }

				$path->{$method} = Operation_Factory::make( $this->generator, array_merge( $this->arguments, [
					'handler' => $handler,
					'method'  => $method,
				] ) );
			}
		}

		return $path;
	}
}
