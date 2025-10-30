<?php
/**
 * Paths_Factory class file
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\Swagger_Generator\Factory;

use Alley\WP\Swagger_Generator\Objects\Route;
use Alley\WP\Swagger_Generator\Objects\Route_Handler;
use cebe\openapi\spec\Paths;
use Mantle\Support\Arr;
use RuntimeException;

use function Alley\WP\Swagger_Generator\sanitize_route_for_openapi;
use function Alley\WP\Swagger_Generator\validate_route_for_openapi;
use function Mantle\Support\Helpers\collect;

/**
 * Path Factory class.
 *
 * @extends Factory<\cebe\openapi\spec\Paths, array{document: \cebe\openapi\spec\OpenApi}>
 */
class Paths_Factory extends Factory {
	/**
	 * Generate the factory object(s).
	 *
	 * @return Paths<\cebe\openapi\spec\PathItem>
	 */
	public function generate(): Paths {
		$paths = [];

		dump($this->get_routes());

		foreach ( $this->get_routes() as $route ) {
			$sanitized_route = $route->sanitized_route();

			// Skip if the route can't be sanitized for OpenAPI.
			if ( ! $sanitized_route ) {
				continue;
			}

			$paths[ '/' . rest_get_url_prefix() . $sanitized_route ] = ( new Path_Item_Factory( $this->generator, array_merge(
				$this->arguments,
				[ 'route' => $route ],
			) ) )->generate();
		}

		return new Paths( $paths );
	}

	/**
	 * Retrieve the routes for generation.
	 *
	 * Mirror WP_REST_Server::get_routes() and normalize the data while preserving a bit more data.
	 *
	 * @throws RuntimeException If the REST server does not have the expected method or no routes are found.
	 *
	 * @return array<int, \Alley\WP\Swagger_Generator\Objects\Route>
	 */
	protected function get_routes(): array {
		$server = rest_get_server();

		if ( ! method_exists( $server, 'get_raw_endpoint_data' ) ) {
			throw new RuntimeException( 'REST server does not have a method to get raw endpoint data.' );
		}

		/**
		 * Routes from the REST API server.
		 *
		 * @var array<string, array<string, array<int|string, mixed>>>
		 */
		$routes = $server->get_raw_endpoint_data();

		if ( ! is_array( $routes ) || empty( $routes ) ) {
			throw new RuntimeException( 'No routes found from the REST server.' );
		}

		$routes = collect( $routes );

		if ( ! empty( $this->generator->namespace ) ) {
			$routes = $routes->where( 'namespace', $this->generator->namespace );
		}

		return $routes->map( function ( array $arguments, string $route ): Route {
			if ( isset( $arguments['callback'] ) ) {
				$arguments = [ $arguments ];
			}

			$handlers = [];
			$options  = [];

			foreach ( $arguments as $index => $argument ) {
				if ( is_string( $index ) ) {
					$options[ $index ] = $argument;
					continue;
				}

				// Bail if the route handler is invalid.
				if ( ! is_array( $argument ) || ! isset( $argument['methods'] ) ) {
					continue;
				}

				if ( ! isset( $argument['callback'] ) || ! is_callable( $argument['callback'] ) ) {
					continue;
				}

				$handlers[] = new Route_Handler(
					methods: Arr::wrap( $argument['methods'] ),
					callback: $argument['callback'],
					args: $argument['args'] ?? [],
				);
			}

			return new Route(
				route: $route,
				handlers: $handlers,
				options: $options,
			);
		} )->values()->all();
	}
}
