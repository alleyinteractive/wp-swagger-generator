<?php
/**
 * The main plugin function
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\Swagger_Generator;

use Alley\WP\Features\Group;

/**
 * Instantiate the plugin.
 */
function main(): void {
	// Add features here.
	$plugin = new Group();

	$plugin->boot();
}
