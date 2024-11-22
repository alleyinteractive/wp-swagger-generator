<?php
/**
 * Path_Item_Factory class file
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\Swagger_Generator\Factory;

use Alley\WP\Swagger_Generator\Generator;
use cebe\openapi\spec\Info;
use cebe\openapi\spec\OpenApi as Document;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Paths;
use RuntimeException;

use function Alley\WP\Swagger_Generator\sanitize_route_for_openapi;
use function Alley\WP\Swagger_Generator\validate_route_for_openapi;

/**
 * Path Item Factory class.
 *
 * @extends Factory<\cebe\openapi\PathItem>
 */
class Path_Item_Factory extends Factory {
	/**
	 * Constructor.
	 *
	 * @param Generator $generator Generator instance.
	 * @param Document  $document Document instance.
	 * @param string    $route Route.
	 * @param array     $callbacks Callbacks.
	 */
	public function __construct( Generator $generator, public Document $document, public readonly string $route, public readonly array $callbacks ) {
		parent::__construct( $generator );
	}

	/**
	 * Generate the factory object(s).
	 *
	 * @return PathItem
	 */
	public function generate(): PathItem {
		$path = new PathItem( [] );

		foreach ( $this->callbacks as $callback ) {
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

			/**
			 * Filter the path item.
			 *
			 * Supports injecting the summary, description, and other properties for the path item.
			 *
			 * @param PathItem $path_item Path item.
			 * @param string   $route Route.
			 * @param array    $callbacks Route callback methods.
			 */
			$path = apply_filters( 'wp_swagger_generator_path', $path, $route, $callbacks );

			if ( ! $path instanceof PathItem ) {
				throw new RuntimeException( 'Path item must be an instance of ' . PathItem::class );
			}

			$paths[ '/' . rest_get_url_prefix() . $route ] = $path;
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
