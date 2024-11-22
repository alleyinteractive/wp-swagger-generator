<?php
/**
 * Paths_Factory class file
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\Swagger_Generator\Factory;

use cebe\openapi\spec\OpenApi as Document;
use Alley\WP\Swagger_Generator\Generator;
use cebe\openapi\spec\Info;
use cebe\openapi\spec\Paths;

use function Alley\WP\Swagger_Generator\sanitize_route_for_openapi;
use function Alley\WP\Swagger_Generator\validate_route_for_openapi;

/**
 * Path Factory class.
 *
 * @extends Factory<\cebe\openapi\Paths>
 */
class Paths_Factory extends Factory {
	/**
	 * Constructor.
	 *
	 * @param Generator $generator Generator instance.
	 * @param Document  $document Document instance.
	 */
	public function __construct( Generator $generator, public Document $document ) {
		parent::__construct( $generator );
	}

	/**
	 * Generate the factory object(s).
	 *
	 * @return Paths
	 */
	public function generate(): Paths {
		$paths = [];

		foreach ( rest_get_server()->get_routes( $this->generator->namespace ) as $route => $callbacks ) {
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

			$paths[ '/' . rest_get_url_prefix() . $route ] = Path_Item_Factory::make( $this->generator, $this->document, $route, $callbacks )->generate();
		}

		return new Paths( $paths );
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
}
