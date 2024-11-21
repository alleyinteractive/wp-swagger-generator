<?php
/**
 * Generator class file
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\Swagger_Generator;

use cebe\openapi\spec\Info;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Paths;
use Mantle\Support\Traits\Hookable;
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
	 * @var OpenApi
	 */
	protected OpenApi $document;

	/**
	 * Constructor.
	 *
	 * @param string|null $namespace Namespace to limit the generation to, e.g. 'wp/v2'
	 */
	public function __construct(
		public readonly string|null $namespace = null,
		public readonly string|null $version = '1.0.0'
	) {}

	/**
	 * Compile the document.
	 *
	 * @throws RuntimeException If the document has already been compiled.
	 */
	public function compile(): void {
		if ( isset( $this->document ) ) {
			throw new RuntimeException( 'Document already compiled.' );
		}

		$this->document = new OpenApi( [
			/**
			 * Filter the OpenAPI version.
			 *
			 * @param string $version OpenAPI version.
			 */
			'openapi' => apply_filters( 'wp_swagger_generator_openapi_version', '3.0.3' ),
			'info'    => $this->get_info(),
			'paths'   => $this->get_paths(),
		] );

		/**
		 * Filter the OpenAPI document.
		 *
		 * @param OpenApi $document OpenAPI document.
		 */
		$this->document = apply_filters( 'wp_swagger_generator_document', $this->document );

		if ( ! $this->document instanceof OpenApi ) {
			throw new RuntimeException( 'Document must be an instance of OpenApi.' );
		}
	}

	/**
	 * Get the info for the document.
	 *
	 * @return Info
	 */
	protected function get_info(): Info {
		return new Info( [
			/**
			 * Filter the OpenAPI title.
			 *
			 * @param string $title OpenAPI title.
			 */
			'title'       => apply_filters( 'wp_swagger_generator_openapi_title', get_bloginfo( 'name' ) ),
			/**
			 * Filter the OpenAPI description.
			 *
			 * @param string $description OpenAPI description.
			 */
			'description' => apply_filters( 'wp_swagger_generator_openapi_description', __( 'REST API documentation for WordPress.', 'wp-swagger-generator' ) ),
			/**
			 * Filter the OpenAPI version.
			 *
			 * @param string $version OpenAPI version.
			 */
			'version'     => apply_filters( 'wp_swagger_generator_openapi_version', $this->version ),
		] );
	}

	/**
	 * Get the paths.
	 *
	 * @return Paths
	 */
	protected function get_paths(): Paths {
		// Ensure the REST API has been initialized.
		rest_api_init();

		$paths = [];

		// dd(rest_get_server()->get_routes());

		foreach ( rest_get_server()->get_routes( $this->namespace ) as $route => $callbacks ) {
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

			$path = new PathItem(
				apply_filters( 'wp_swagger_generator_path_item', [], $route, $callbacks ),
			);

			foreach ( $callbacks as $callback ) {
				foreach ( array_keys( $callback['methods'] ) as $method ) {
					$method = strtolower( $method );

					$path->{$method} = new Operation(
						apply_filters(
							'wp_swagger_generator_operation',
							[
								'parameters' => $this->get_parameters( $route, $callback, $method ),
								// 'summary'     => '',
								// 'description' => '',
								// 'responses'   => [],
							],
							$route,
							$method,
							$callback,
						),
					);
				}
			}

			$paths[ '/' . rest_get_url_prefix() . $route ] = $path;
		}

		return new Paths( $paths );
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

	/**
	 * Get the OpenAPI document.
	 *
	 * @return OpenApi
	 */
	public function get_document(): OpenApi {
		return $this->document;
	}

	public function write_yaml_to_file( string $file ): void {}

	public function write_json_to_file( string $file ): void {}
}
