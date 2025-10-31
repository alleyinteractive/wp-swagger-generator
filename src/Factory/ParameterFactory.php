<?php
/**
 * ParameterFactory class file
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\SwaggerGenerator\Factory;

use Alley\WP\SwaggerGenerator\HttpMethod;
use Alley\WP\SwaggerGenerator\Objects\Route;
use Alley\WP\SwaggerGenerator\Objects\RouteHandler;
use cebe\openapi\spec\Parameter;
use RuntimeException;

use function Alley\WP\SwaggerGenerator\filter_out_nulls;
use function Alley\WP\SwaggerGenerator\get_route_parameters;
use function Mantle\Support\Helpers\collect;
use function Mantle\Support\Helpers\memo;

/**
 * Operation Parameter Factory class.
 *
 * @link https://swagger.io/docs/specification/v3_0/describing-parameters/
 *
 * @todo TODO: Include global parameters for specific REST API routes (_embedded, _links, etc).
 *
 * @extends Factory<array<\cebe\openapi\Parameter>, array{
 *   route: \Alley\WP\SwaggerGenerator\Objects\Route,
 *   handler: \Alley\WP\SwaggerGenerator\Objects\RouteHandler,
 *   method: \Alley\WP\SwaggerGenerator\HttpMethod
 * }>
 */
class ParameterFactory extends Factory {
	/**
	 * Get the names of parameters that are always query parameters.
	 *
	 * @return string[]
	 */
	public static function get_constant_query_parameter_names(): array {
		return memo( function (): array {
			/**
			 * Filter which route parameters are always query parameters.
			 *
			 * @param string[] $parameters Route parameters that are always query parameters.
			 */
			return apply_filters( 'wp_swagger_generator_constant_query_parameter_names', [ 'context', '_embedded', '_fields', '_link' ] );
		}, [] );
	}
	/**
	 * Generate the factory object(s).
	 *
	 * @return array<Parameter>
	 */
	public function generate(): array {
		$this->validate_arguments( [
			'route'   => Route::class,
			'handler' => RouteHandler::class,
			'method'  => HttpMethod::class,
		] );

		$route   = $this->arguments['route'];
		$handler = $this->arguments['handler'];
		// dd($handler);

		$sanitized_route = $route->sanitized_route();

		if ( ! $sanitized_route ) {
			throw new RuntimeException( 'Cannot generate parameters for an invalid route.' );
		}

		dump($handler->arguments);

		if ( empty( $handler->arguments ) ) {
			return [];
		}

		$parameters = [];

		$route_parameters = get_route_parameters( $sanitized_route );
		$is_get_request   = $handler->supports_method( HttpMethod::GET );
		dump('is_get_request', $is_get_request);
		// $request_parameter_type = $ 'get' === $this->arguments['method'] ? 'query' : 'path';

		foreach ( $this->arguments['handler']->arguments as $argument_name => $argument ) {
			$is_route_parameter = in_array( $argument_name, $route_parameters, true );
			$is_query_parameter = $is_get_request && ! $is_route_parameter;

			// Force some query parameters to always be a query parameter.
			if ( in_array( $argument_name, self::get_constant_query_parameter_names(), true ) ) {
				$is_query_parameter = true;
			}

			/**
			 * Filter whether an argument for a REST API route is a query parameter.
			 *
			 * @param bool   $is_query_parameter Whether the argument is a query parameter.
			 * @param string $argument_name 	 Argument name.
			 * @param string $sanitized_route    Sanitized OpenAPI route.
			 * @param array  $argument           Route arguments passed to register_rest_route.
			 * @param array  $all_arguments	     All route arguments.
			 */
			$is_query_parameter = (bool) apply_filters( 'wp_swagger_generator_is_query_parameter', $is_query_parameter, $argument_name, $sanitized_route, $argument, $this->arguments );

			$parameter = [
				'name'        => $argument_name,
				'description' => $argument['description'] ?? '',
				'in'          => $is_route_parameter ? 'path' : ( $is_query_parameter ? 'query' : null ),
				'type'        => $argument['type'] ?? 'string',
				'required'    => match ( true ) {
					$is_route_parameter => true,
					! empty( $argument['required'] ) && (bool) $argument['required'] => (bool) $argument['required'],
					default => null, // Null so it is omitted if not required.
				},
				'enum'        => $argument['enum'] ?? null,
			];

			dump(filter_out_nulls( $parameter ));
			$parameters[] = new Parameter( filter_out_nulls( $parameter ) );
		}

		dd('here');
		dd($parameters);

		return collect( $this->arguments['handler']['arguments'] )->map( function ( array $argument, string $argument_name ) use ( $route_parameters ): ?Parameter {
			$is_route_parameter = in_array( $argument_name, $route_parameters, true );
			$is_query_parameter = 'get' === $this->arguments['method'] && ! $is_route_parameter;



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
