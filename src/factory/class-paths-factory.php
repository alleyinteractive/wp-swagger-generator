<?php
/**
 * Paths_Factory class file
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\Swagger_Generator\Factory;

use Alley\WP\Swagger_Generator\REST_API\Route;
use cebe\openapi\spec\Paths;
use RuntimeException;

use function Alley\WP\Swagger_Generator\sanitize_route_for_openapi;
use function Alley\WP\Swagger_Generator\validate_route_for_openapi;
use function Mantle\Support\Helpers\collect;

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

		dd($this->get_routes());

		foreach ( $this->get_routes() as $route => $callbacks ) {
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
	 * Mirror WP_REST_Server::get_routes() and normalize the data while preserving a bit more data.
	 *
	 * @return array<string, \Alley\WP\Swagger_Generator\REST_API\Route>
	 */
	protected function get_routes(): array {
		$server = rest_get_server();

		if ( ! method_exists( $server, 'get_raw_endpoint_data' ) ) {
			throw new RuntimeException( 'REST server does not have a method to get raw endpoint data.' );
		}

		$routes = collect( $server->get_raw_endpoint_data() );

		if ( ! empty( $this->generator->namespace ) ) {
			$routes = $routes->where( 'namespace', $this->generator->namespace );
		}

		return $routes->map( function ( array $arguments, string $route ): Route {
			if ( isset( $arguments['callback'] ) ) {
				$arguments = [ $arguments ];
			}

			$compiled = [
				'methods'  => [],
				'handlers' => [],
				'options'  => [],
			];

			foreach ( $arguments as $index => $argument ) {
				if ( is_numeric( $index ) && isset( $argument['methods'] ) ) {
					$compiled['handlers'][] = $argument;

					if ( is_string( $argument['methods'] ) ) {
						$compiled['methods'] = array_merge( $compiled['methods'], explode( ',', $argument['methods'] ) );
					} else {
						$compiled['methods'] = array_merge( $compiled['methods'], $argument['methods'] );
					}
				} else {
					$compiled['options'][ $index ] = $argument;
				}
			}

			$compiled['methods'] = collect( $compiled['methods'] )
				->map( fn ( $method ) => strtolower( $method ) )
				->unique()
				->values()
				->all();

			return new Route(
				route: $route,
				methods: $compiled['methods'],
				handlers: $compiled['handlers'],
				options: $compiled['options'],
			);
		} )->all();
	}
}
