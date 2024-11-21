<?php
/**
 * HelpersTest class file
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\Swagger_Generator\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function Alley\WP\Swagger_Generator\get_route_parameters;
use function Alley\WP\Swagger_Generator\sanitize_route_for_openapi;
use function Alley\WP\Swagger_Generator\validate_route_for_openapi;

/**
 * Helpers test
 *
 * @link https://mantle.alley.com/testing/test-framework.html
 */
class HelpersTest extends TestCase {

	/**
	 * Test the route sanitizer.
	 *
	 * @param string $input Input route.
	 * @param string $expected Expected output route.
	 */
	#[DataProvider( 'route_data_provider' )]
	public function test_sanitize_route_for_openapi( string $input, string $expected ): void {
		$this->assertSame( $expected, sanitize_route_for_openapi( $input ) );
	}

	public static function route_data_provider(): array {
		return [
			[
				'/wp/v2/posts/(?P<id>\d+)',
				'/wp/v2/posts/{id}',
			],
			[
				'/wp/v2/posts/(?P<id>\d+)/revisions/(?P<revision>\d+)',
				'/wp/v2/posts/{id}/revisions/{revision}',
			],
			[
				'/wp/v2/posts/(?P<id>\d+)/revisions/(?P<revision>\d+)/meta/(?P<key>\w+)',
				'/wp/v2/posts/{id}/revisions/{revision}/meta/{key}',
			],
			[
				'/wp-json/wp/v2/themes/(?P<stylesheet>[^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)',
				'/wp-json/wp/v2/themes/{stylesheet}',
			],
			[
				'/wp-json/wp/v2/plugins/(?P<plugin>[^.\/]+(?:\/[^.\/]+)?)',
				'/wp-json/wp/v2/plugins/{plugin}',
			],
			// The following patterns are hard to convert.
			'one-off pattern' => [
				'/wp-json/wp/v2/templates/(?P<parent>([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)[\/\w%-]+)/revisions',
				'/wp-json/wp/v2/templates/{parent}/revisions',
			],
			[
				'/wp-json/wp/v2/users/(?P<user_id>(?:[\d]+|me))/application-passwords/introspect',
				'/wp-json/wp/v2/users/{user_id}/application-passwords/introspect',
			],
			[
				'/wp-json/wp/v2/global-styles/themes/(?P<stylesheet>[^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)',
				'/wp-json/wp/v2/global-styles/themes/{stylesheet}',
			],
			[
				'/wp-json/wp/v2/global-styles/themes/(?P<stylesheet>[\/\s%\w\.\(\)\[\]\@_\-]+)/variations',
				'/wp-json/wp/v2/global-styles/themes/{stylesheet}/variations',
			],
		];
	}

	/**
	 * Test the route validator.
	 *
	 * @param string $route Route to validate.
	 * @param bool $status Expected validation status.
	 */
	#[DataProvider( 'validation_data_provider' )]
	public function test_it_can_validate_a_route( string $route, bool $status ): void {
		$this->assertSame( $status, validate_route_for_openapi( $route ) );
	}

	public static function validation_data_provider(): array {
		return [
			[ '/wp/v2/posts/{id}', true ],
			[ '/wp/v2/example/{id}(.*)', false ],
			[ '/wp-json/wp/v2/plugins/(?P<plugin>[^.\/]+(?:\/[^.\/]+)?)', false ],
		];
	}

	/**
	 * Test the route parameter extractor.
	 *
	 * @param string $route Route to extract parameters from.
	 * @param array<string> $expected Expected parameters.
	 */
	#[DataProvider( 'parameter_data_provider' )]
	public function test_get_route_parameters( string $route, array $expected ): void {
		$this->assertSame( $expected, get_route_parameters( $route ) );
	}

	public static function parameter_data_provider(): array {
		return [
			[ '/wp/v2/posts/{id}', [ 'id' ] ],
			[ '/wp/v2/posts/{id}/another/{example}', [ 'id', 'example' ] ],
			[ '/wp-json/wp/v2/plugins/{plugin}', [ 'plugin' ] ],
			[ '/wp-json/wp/v2/themes/{stylesheet}', [ 'stylesheet' ] ],
			[ '/wp-json/wp/v2/global-styles/themes/{stylesheet}/variations', [ 'stylesheet' ] ],
		];
	}
}
