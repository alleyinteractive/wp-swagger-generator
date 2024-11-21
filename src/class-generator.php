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
			'openapi' => apply_filters( 'wp_swagger_generator_openapi_version', '3.1.0' ),
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
			$path = new PathItem(
				apply_filters(
					'wp_swagger_generator_path_item',
					[
						'summary'     => '',
						'description' =>  '',
					],
					$route,
					$callbacks,
				),
			);

			foreach ( $callbacks as $callback ) {
				foreach ( array_keys( $callback['methods'] ) as $method ) {
					$method = strtolower( $method );

					// TODO Permissions.

					$path->{$method} = new Operation(
						apply_filters(
							'wp_swagger_generator_operation',
							[
								'summary'     => '',
								'description' => '',
								'responses'   => [],
							],
							$route,
							$method,
							$callback,
						),
					);
				}
			}

			// TODO: Convert regex arguments to OpenAPI format.
			$paths[ '/' . rest_get_url_prefix() . $this->convert_route_for_document( $route ) ] = $path;
		}

		return new Paths( $paths );
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

	/**
	 * Convert the regex of the WordPress REST API route to an OpenAPI path.
	 *
	 * This method will convert the named arguments in the route to the :arg format.
	 *
	 * @param string $route Route to convert.
	 * @return string
	 */
	protected function convert_route_for_document( string $route ): string {
		return preg_replace_callback(
			// '/\{(.+?)\}/',
			'/\(\?P<(\w+)>[^)]+\)/',
			fn ( $matches ) => ':' . $matches[1],
			$route,
		);
	}
}
