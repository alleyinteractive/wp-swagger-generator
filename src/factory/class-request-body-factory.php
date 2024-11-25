<?php
/**
 * Parameter_Factory class file
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\Swagger_Generator\Factory;

use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\RequestBody;
use cebe\openapi\spec\Schema;
use RuntimeException;

use function Alley\WP\Swagger_Generator\filter_out_nulls;
use function Alley\WP\Swagger_Generator\get_route_parameters;
use function Mantle\Support\Helpers\collect;

/**
 * Operation Request Body Factory class.
 *
 * The request body if generated will reference a schema added as a component to
 * the OpenAPI document.
 *
 * @extends Factory<\cebe\openapi\RequestBody>
 */
class Request_Body_Factory extends Factory {
	/**
	 * Generate the factory object(s).
	 *
	 * @return ?RequestBody
	 */
	public function generate(): ?RequestBody {
		$this->validate_arguments( [ 'document', 'route', 'callback', 'method' ] );

		// Bail for requests that don't have a request body.
		if ( in_array( $this->arguments['method'], [ 'get', 'head', 'options' ], true ) ) {
			return null;
		}

		// $schema = new Schema( [
		// 	'type'       => 'object',
		// 	'properties' => collect( $this->arguments['callback']['args'] )->map( function ( array $argument, $argument_name ) {
		// 		// Skip arguments that are route parameters.
		// 		if ( in_array( $argument_name, get_route_parameters( $this->arguments['route'] ), true ) ) {
		// 			return null;
		// 		}

		// 	} )->filter()->values()->toArray(),
		// ] );
		// dd($schema);

		// dd($this->arguments);

		$route_parameters = get_route_parameters( $this->arguments['route'] );
		$request_parameter_type = 'get' === $this->arguments['method'] ? 'query' : 'path';

		$request_body_arguments = collect( $this->arguments['callback']['args'] )
			->filter( fn ( array $argument, string $argument_name ) => ! in_array( $argument_name, $route_parameters, true ) )
			->dd()
			;

		return null;

		// 	->map( function ( array $argument, $argument_name ) use ( $route_parameters ): ?Parameter {
		// 	// Skip arguments that are route parameters.
		// 	if ( in_array( $argument_name, $route_parameters, true ) ) {
		// 		return null;
		// 	}

		// 	dd($this->arguments, $argument, $argument_name);
		// 	$is_query_parameter = 'get' === $this->arguments['method'] && ! $is_route_parameter;

		// 	// Force some query parameters to always be a query parameter.
		// 	if ( in_array( $argument_name, [ 'context', '_embedded', '_fields', '_link' ], true ) ) {
		// 		$is_query_parameter = true;
		// 	}

		// 	if ( 'context' === $argument_name ) {
		// 		return null;
		// 	}

		// 	/**
		// 	 * Filter whether an argument for a REST API route is a query parameter.
		// 	 *
		// 	 * @param bool   $is_query_parameter Whether the argument is a query parameter.
		// 	 * @param string $argument           Argument name.
		// 	 * @param string $route              OpenAPI route.
		// 	 * @param array  $argument           Route callback argument.
		// 	 * @param string $method             HTTP method.
		// 	 */
		// 	$is_query_parameter = apply_filters( 'wp_swagger_generator_is_query_parameter', $is_query_parameter, $argument_name, $route, $argument, $method );

		// 	// Skip arguments that will be handled in the request body.
		// 	if ( ! $is_route_parameter && ! $is_query_parameter ) {
		// 		return null;
		// 	}

		// 	// TODO: break out to standalone class.
		// 	// TODO: Support object AND array returns.
		// 	// TODO: Support oneof
		// 	return new Parameter( filter_out_nulls( [
		// 		'name'        => $argument_name,
		// 		'description' => $argument['description'] ?? '',
		// 		'in'          => $is_route_parameter ? 'path'                                 : 'query',
		// 		'required'    => $is_route_parameter ? true : ( isset( $argument['required'] ) ? (bool) $argument['required']: false ), // Required can only be true.
		// 		'schema'      => filter_out_nulls( [
		// 			'type'    => $argument['type'] ?? 'string',
		// 			'items'   => $argument['items'] ?? null,
		// 			'enum'    => $argument['enum'] ?? null,
		// 			'default' => $argument['default'] ?? null,
		// 		] ),
		// 	] ) );
		// } )->filter()->values()->toArray();
	}
}
