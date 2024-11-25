<?php
/**
 * Paths_Factory class file
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\Swagger_Generator\Factory;

use cebe\openapi\spec\Paths;
use RuntimeException;

use function Alley\WP\Swagger_Generator\sanitize_route_for_openapi;
use function Alley\WP\Swagger_Generator\validate_route_for_openapi;

/**
 * Path Factory class.
 *
 * @extends Factory<\cebe\openapi\Paths>
 */
class Paths_Factory extends Factory {
	/**
	 * Generate the factory object(s).
	 *
	 * @return Paths
	 */
	public function generate(): Paths {
		$paths = [];

		dd(rest_get_server());

		foreach ( $this->get_routes( $this->generator->namespace ) as $route => $callbacks ) {
			$route = sanitize_route_for_openapi( $route );

			if ( ! validate_route_for_openapi( $route ) ) {
				/**
				 * Filter an invalid route to be included in the OpenAPI document.
				 *
				 * @param string|null $route Route.
				 * @param array       $callbacks Callbacks.
				 */
				$route = apply_filters( 'wp_swagger_generator_invalid_route', $route, $callbacks );

				if ( ! $route || ! validate_route_for_openapi( $route ) ) {
					continue;
				}
			}

			$paths[ '/' . rest_get_url_prefix() . $route ] = Path_Item_Factory::make( $this->generator, $this->forward_arguments( [
				'callbacks' => $callbacks,
				'route'     => $route,
			] ) );
		}

		return new Paths( $paths );
	}

	/**
	 * Retrieve the routes for generation.
	 *
	 * @return array
	 */
	protected function get_routes(): array {
		$server = rest_get_server();

		if ( ! method_exists( $server, 'get_raw_endpoint_data' ) ) {
			throw new RuntimeException( 'REST server does not have a method to get raw endpoint data.' );
		}

		if ( empty( $this->generator->namespace ) ) {
			return $server->get_raw_endpoint_data();
		}

		$prefix = '/' . ltrim( $this->generator->namespace, '/' );
		dd($prefix);
	}
}
