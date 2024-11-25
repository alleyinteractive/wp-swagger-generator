<?php
/**
 * Parameter_Factory class file
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\Swagger_Generator\Factory;

use cebe\openapi\spec\Parameter;
use RuntimeException;

use function Alley\WP\Swagger_Generator\filter_out_nulls;
use function Alley\WP\Swagger_Generator\get_route_parameters;
use function Mantle\Support\Helpers\collect;

/**
 * Operation Parameter Factory class.
 *
 * @todo TODO: Include global parameters for specific REST API routes (_embedded, _links, etc).
 *
 * @extends Factory<array<\cebe\openapi\Parameter>>
 */
class Parameter_Factory extends Factory {
	/**
	 * Generate the factory object(s).
	 *
	 * @return array<Parameter>
	 */
	public function generate(): array {
		$this->validate_arguments( [ 'route', 'callback', 'method' ] );

		$parameters = [];

		if ( empty( $this->arguments['callback']['args'] ) ) {
			return $parameters;
		}

		$route_parameters = get_route_parameters( $this->arguments['route'] );
		$request_parameter_type = 'get' === $this->arguments['method'] ? 'query' : 'path';

		return collect( $this->arguments['callback']['args'] )->map( function ( array $argument, $argument_name ) use ( $route_parameters ): ?Parameter {
			$is_route_parameter = in_array( $argument_name, $route_parameters, true );
			$is_query_parameter = 'get' === $this->arguments['method'] && ! $is_route_parameter;

			// Force some query parameters to always be a query parameter.
			if ( in_array( $argument_name, [ 'context', '_embedded', '_fields', '_link' ], true ) ) {
				$is_query_parameter = true;
			}

			if ( 'context' === $argument_name ) {
				return null;
			}

			/**
			 * Filter whether an argument for a REST API route is a query parameter.
			 *
			 * @param bool   $is_query_parameter Whether the argument is a query parameter.
			 * @param string $argument           Argument name.
			 * @param string $route              OpenAPI route.
			 * @param array  $argument           Route callback argument.
			 * @param string $method             HTTP method.
			 */
			$is_query_parameter = apply_filters( 'wp_swagger_generator_is_query_parameter', $is_query_parameter, $argument_name, $route, $argument, $method );

			// Skip arguments that will be handled in the request body.
			if ( ! $is_route_parameter && ! $is_query_parameter ) {
				return null;
			}

			// TODO: break out to standalone class.
			// TODO: Support object AND array returns.
			// TODO: Support oneof for multiple types (array and object)
			return new Parameter( filter_out_nulls( [
				'name'        => $argument_name,
				'description' => $argument['description'] ?? '',
				'in'          => $is_route_parameter ? 'path' : 'query',
				'required'    => $is_route_parameter ? true : ( isset( $argument['required'] ) ? (bool) $argument['required']: false ), // Required can only be true.
				'schema'      => filter_out_nulls( [
					'type'    => $argument['type'] ?? 'string',
					'items'   => $argument['items'] ?? null,
					'enum'    => $argument['enum'] ?? null,
					'default' => $argument['default'] ?? null,
				] ),
			] ) );
		} )->filter()->values()->toArray();
	}
}
