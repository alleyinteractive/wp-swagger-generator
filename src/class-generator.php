<?php
/**
 * Generator class file
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\Swagger_Generator;

use cebe\openapi\spec\OpenApi as Document;
use cebe\openapi\spec\Parameter;
use RuntimeException;

use function Mantle\Support\Helpers\collect;

/**
 * OpenAPI Generator
 *
 * @link https://swagger.io/specification/
 *
 * @todo Include server.
 * @todo Include responses (via components).
 * @todo Include examples.
 * @todo Include headers.
 * @todo Include security headers (query param)
 */
class Generator {
	/**
	 * OpenAPI document.
	 *
	 * @var Document
	 */
	protected Document $document;

	/**
	 * Constructor.
	 *
	 * @param string|null $namespace Namespace to limit the generation to, e.g. 'wp/v2'
	 */
	public function __construct( public readonly string|null $namespace = null, public readonly string|null $version = '1.0.0' ) {}

	/**
	 * Compile the document.
	 *
	 * @throws RuntimeException If the document has already been compiled.
	 */
	public function compile(): void {
		if ( isset( $this->document ) ) {
			throw new RuntimeException( 'Document already compiled.' );
		}

		// Ensure the REST API has been initialized.
		rest_api_init();

		$this->document = Factory\Document_Factory::make( $this )->generate();
	}

	/**
	 * Get the parameters for the operation.
	 *
	 * @param string $route Route.
	 * @param array  $callback Callback.
	 * @param string $method Method.
	 *
	 * @return Parameter[]
	 */
	protected function get_parameters( string $route, array $callback, string $method ): array {
		$parameters = [];

		if ( empty( $callback['args'] ) ) {
			return $parameters;
		}

		$route_parameters = get_route_parameters( $route );
		$request_parameter_type = 'get' === $method ? 'query' : 'path';

		$parameters = collect( $callback['args'] )->map( function ( array $argument, $argument_name ) use ( $route, $route_parameters, $method ): ?Parameter {
			$is_route_parameter = in_array( $argument_name, $route_parameters, true );
			$is_query_parameter = 'get' === $method && ! $is_route_parameter;

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
			// TODO: Support oneof
			return new Parameter( filter_out_nulls( [
				'name'        => $argument_name,
				'description' => $argument['description'] ?? '',
				'in'          => $is_route_parameter ? 'path'                                 : 'query',
				'required'    => $is_route_parameter ? true : ( isset( $argument['required'] ) ? (bool) $argument['required']: false ), // Required can only be true.
				'schema'      => filter_out_nulls( [
					'type'    => $argument['type'] ?? 'string',
					'items'   => $argument['items'] ?? null,
					'enum'    => $argument['enum'] ?? null,
					'default' => $argument['default'] ?? null,
				] ),
			] ) );
		} )->filter()->values()->toArray();

		// TODO: Include global parameters for specific REST API routes (_embedded,
		// _links, etc).

		$parameters = apply_filters( 'wp_swagger_generator_parameters', $parameters, $route, $callback, $method );

		return is_array( $parameters ) ? $parameters : [];
	}

	protected function get_responses(): array {

	}

	/**
	 * Get the OpenAPI document.
	 *
	 * @return Document
	 */
	public function get_document(): Document {
		return $this->document;
	}

	public function write_yaml_to_file( string $file ): void {}

	public function write_json_to_file( string $file ): void {}
}
