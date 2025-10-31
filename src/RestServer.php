<?php
/**
 * RestServer class file
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\SwaggerGenerator;

use WP_REST_Server;

/**
 * REST Server class.
 *
 * @extends WP_REST_Server
 */
class RestServer extends WP_REST_Server {
	/**
	 * Gets the raw endpoints data from the server.
	 *
	 * @return array<string, array<mixed>>
	 */
	public function get_raw_endpoint_data() {
		return $this->endpoints;
	}
}
