<?php
/**
 * Path_Item_Factory class file
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\Swagger_Generator\Factory;

use cebe\openapi\spec\PathItem;
use InvalidArgumentException;

/**
 * Path Item Factory class.
 *
 * @extends Factory<\cebe\openapi\PathItem>
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
		$path = new PathItem( [] );

		if ( empty( $this->arguments['callbacks'] ) || ! is_array( $this->arguments['callbacks'] ) ) {
			throw new InvalidArgumentException( 'Expected argument "callbacks" to be a non-empty array.' );
		}

		foreach ( $this->arguments['callbacks'] as $callback ) {
			foreach ( array_keys( $callback['methods'] ) as $method ) {
				$method = strtolower( $method );

				if ( ! in_array( $method, self::SUPPORTED_METHODS, true ) ) {
					continue;
				}

				$path->{$method} = Operation_Factory::make( $this->generator, $this->forward_arguments( [
					'method'    => $method,
					'callbacks' => [], // Prevent all the callbacks from being forwarded along.
					'callback'  => $callback,
				] ) );
			}
		}

		return $path;
	}
}
