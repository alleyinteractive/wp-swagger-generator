<?php
/**
 * DocumentFactory class file
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\SwaggerGenerator\Factory;

use Alley\WP\SwaggerGenerator\Spec\Document;
use cebe\openapi\spec\Info;
use cebe\openapi\spec\Server;
use RuntimeException;

/**
 * Document Factory class.
 *
 * @todo Add swagger and not openapi support.
 *
 * @extends Factory<Document, array{}>
 */
class DocumentFactory extends Factory {
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
			'openapi'    => apply_filters( 'wp_swagger_generator_openapi_version', '3.0.4' ),
			'info'       => $this->get_info(),
			'servers'    => $this->get_servers(),
			'components' => [],
		] );

		$document->paths = PathsFactory::make( $this->generator, [ 'document' => $document ] );

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
		$info = [
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
		];

		/**
		 * Filter the Swagger document information section.
		 *
		 * @link https://swagger.io/docs/specification/v3_0/api-general-info/
		 *
		 * @param array{
		 *   title: string,
		 *   description: string,
		 *   version?: string,
		 *   termsOfService?: string,
		 *   contact?: array{name?: string, url?: string, email?: string},
		 *   license?: array{name: string, url?: string}
		 * } $info Document information.
		 */
		return new Info( array_filter( apply_filters( 'wp_swagger_generator_document_info', $info ) ) );
	}

	/**
	 * Get the servers for the document.
	 *
	 * @return Server[]
	 */
	protected function get_servers(): array {
		/**
		 * Filter the OpenAPI servers.
		 *
		 * @link https://swagger.io/docs/specification/v3_0/api-host-and-base-path/
		 *
		 * @param Server[] $servers OpenAPI servers.
		 */
		return (array) apply_filters( 'wp_swagger_generator_servers', [
			new Server( [
				'url'         => get_rest_url(),
				'description' => 'REST API server',
			] ),
		] );
	}
}
