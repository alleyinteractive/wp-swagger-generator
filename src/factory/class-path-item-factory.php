<?php
/**
 * Path_Item_Factory class file
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\Swagger_Generator\Factory;

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
	 * Supported methods.
	 *
	 * @var string[]
	 */
	public const SUPPORTED_METHODS = [
		'get',
		'post',
		'put',
		'patch',
		'delete',
		'head',
		'options',
		'trace',
	];

	/**
	 * Generate the factory object(s).
	 *
	 * @return PathItem
	 */
	public function generate(): PathItem {
		$this->validate_arguments( [ 'route' => Route::class ] );

		$path = new PathItem( [] );

		foreach ( $this->arguments['handlers'] as $handler ) {
			foreach ( array_keys( $handler['methods'] ) as $method ) {
				$method = strtolower( $method );

				if ( ! in_array( $method, self::SUPPORTED_METHODS, true ) ) {
					continue;
				}

				$path->{$method} = Operation_Factory::make( $this->generator, array_merge( $this->arguments, [
					'handler' => $handler,
					'method'  => $method,
				] ) );
			}
		}

		return $path;
	}
}
