<?php
/**
 * REST_Server class file
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\Swagger_Generator;

use WP_REST_Server;

/**
 * REST Server class.
 *
 * @extends WP_REST_Server
 */
class REST_Server extends WP_REST_Server {
	/**
	 * Gets the raw endpoints data from the server.
	 *
	 * @return array
	 */
	public function get_raw_endpoint_data() {
		return $this->endpoints;
	}
}
