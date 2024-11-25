<?php
/**
 * GeneratorTest class file
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\Swagger_Generator\Tests\Feature;

use Alley\WP\Swagger_Generator\Generator;
use Alley\WP\Swagger_Generator\Tests\TestCase;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\Writer;

use function Alley\WP\Swagger_Generator\sanitize_route_for_openapi;
use function Alley\WP\Swagger_Generator\validate_route_for_openapi;

/**
 * Generator Test
 */
class GeneratorTest extends TestCase {
	public function test_it_can_generate_openapi_document() {
		$generator = new Generator( 'wp/v2' );
		$generator->compile();

		$document = $generator->get_document();

		file_put_contents( __DIR__ . '/test.yml', Writer::writeToYaml( $document ) );
		dd(
			// Dump the YML.
			Writer::writeToYaml( $document ),
			// $document->toYaml(),
		);

		$this->assertInstanceOf( OpenApi::class, $document );

		// Inspect the paths.
		$paths = $document->paths;

		$this->assertNotEmpty( $paths->count() );
	}

	public function test_it_can_generate_openapi_document_with_prefix() {
		$this->markTestSkipped( 'Test not implemented yet.' );
	}

	/**
	 * Integration test for sanitize_route_for_openapi().
	 */
	public function test_it_can_sanitize_all_core_routes() {
		foreach ( array_keys( rest_get_server()->get_routes() ) as $route ) {
			$route = sanitize_route_for_openapi( $route );

			$this->assertTrue( validate_route_for_openapi( $route ), "Route '$route' is not valid for OpenAPI." );

		}
	}
}
