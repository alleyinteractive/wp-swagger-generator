<?php
/**
 * Document_Factory class file
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\Swagger_Generator\Factory;

use cebe\openapi\spec\Info;
use cebe\openapi\spec\OpenApi as Document;
use RuntimeException;

/**
 * Document Factory class.
 *
 * @extends Factory<\cebe\openapi\OpenApi>
 */
class Document_Factory extends Factory {
	/**
	 * Generate the factory object(s).
	 *
	 * The document is created and then paths are added to allow the paths to
	 * add additional components/responses/schemas to the document.
	 *
	 * @return Document
	 */
	public function generate(): Document {
		$document = new Document( [
			/**
			 * Filter the OpenAPI version.
			 *
			 * @param string $version OpenAPI version.
			 */
			'openapi'    => apply_filters( 'wp_swagger_generator_openapi_version', '3.0.3' ),
			'info'       => $this->get_info(),

			// TODO
			'servers'    => [],
			'components' => [],
		] );

		$document->paths = Paths_Factory::make( $this->generator, [ 'document' => $document ] );

		/**
		 * Filter the OpenAPI document.
		 *
		 * @param \cebe\openapi\spec\OpenApi $document OpenAPI document.
		 */
		$document = apply_filters( 'wp_swagger_generator_document', $document );

		if ( ! $document instanceof Document ) {
			throw new RuntimeException( 'Document must be an instance of ' . Document::class );
		}

		return $document;
	}

	/**
	 * Get the info for the document.
	 *
	 * @return Info
	 */
	protected function get_info(): Info {
		return new Info( [
			/**
			 * Filter the OpenAPI Document title.
			 *
			 * @param string $title OpenAPI Document title.
			 */
			'title'       => apply_filters( 'wp_swagger_generator_document_title', get_bloginfo( 'name' ) ),
			/**
			 * Filter the OpenAPI Document description.
			 *
			 * @param string $description OpenAPI Document description.
			 */
			'description' => apply_filters( 'wp_swagger_generator_document_description', __( 'REST API documentation for WordPress.', 'wp-swagger-generator' ) ),
			/**
			 * Filter the OpenAPI Document API version.
			 *
			 * @param string $version OpenAPI Document API version.
			 */
			'version'     => apply_filters( 'wp_swagger_generator_document_version', $this->generator->version ),
		] );
	}
}
