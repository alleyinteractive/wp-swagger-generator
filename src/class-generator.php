<?php
/**
 * Generator class file
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\Swagger_Generator;

use cebe\openapi\spec\OpenApi as Document;
use Mantle\Testing\Doubles\Spy_REST_Server;
use RuntimeException;
use WP_REST_Server;

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
	 * Original REST server reference.
	 *
	 * @var WP_REST_Server
	 */
	protected WP_REST_Server $original_rest_server;

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

		$this->replace_rest_server();

		$this->document = Factory\Document_Factory::make( $this );

		$this->restore_rest_server();
	}

	/**
	 * Replace the REST server with the one from this plugin.
	 *
	 * We need to replace the REST server with the one from this plugin so that
	 * we can properly inspect the raw endpoint data.
	 */
	protected function replace_rest_server(): void {
		// Remove the existing REST server if it exists.
		if ( isset( $GLOBALS['wp_rest_server'] ) ) {
			// Bail if the REST server is already the one from this plugin.
			if ( $GLOBALS['wp_rest_server'] instanceof REST_Server || $GLOBALS['wp_rest_server'] instanceof Spy_REST_Server ) {
				return;
			}

			$this->original_rest_server = $GLOBALS['wp_rest_server'];

			unset( $GLOBALS['wp_rest_server'] );
		}

		// Replace the REST server with either the spy from Mantle testing if
		// testing or the one from this plugin.
		add_filter(
			'wp_rest_server_class',
			fn () => class_exists( Spy_REST_Server::class ) && defined( 'MANTLE_IS_TESTING' ) && MANTLE_IS_TESTING
				? Spy_REST_Server::class
				: REST_Server::class,
			PHP_INT_MAX,
		);

		rest_api_init();
	}

	/**
	 * Restore the original REST server.
	 */
	protected function restore_rest_server(): void {
		if ( isset( $this->original_rest_server ) ) {
			$GLOBALS['wp_rest_server'] = $this->original_rest_server;
		}
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
