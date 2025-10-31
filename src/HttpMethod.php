<?php
/**
 * HttpMethod enum file
 *
 * @package wp-swagger-generator
 */

declare(strict_types=1);

namespace Alley\WP\SwaggerGenerator;

/**
 * HTTP Methods enum.
 */
enum HttpMethod: string {
	case GET     = 'GET';
	case POST    = 'POST';
	case PUT     = 'PUT';
	case PATCH   = 'PATCH';
	case DELETE  = 'DELETE';
	case HEAD    = 'HEAD';
	case OPTIONS = 'OPTIONS';
	case TRACE   = 'TRACE';
}
