<?php
/**
 * Operation_Factory class file
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\Swagger_Generator\Factory;

use Alley\WP\Swagger_Generator\Http_Method;
use Alley\WP\Swagger_Generator\Objects\Route;
use Alley\WP\Swagger_Generator\Objects\Route_Handler;
use cebe\openapi\spec\Operation;
use RuntimeException;

use function Alley\WP\Swagger_Generator\filter_out_nulls;

/**
 * Operation Factory class.
 *
 * @todo Request body
 * @todo Responses.
 *
 * @extends Factory<\cebe\openapi\Operation, array{
 *   route: \Alley\WP\Swagger_Generator\Objects\Route,
 *   handler: \Alley\WP\Swagger_Generator\Objects\Route_Handler,
 *   method: \Alley\WP\Swagger_Generator\Http_Method
 * }>
 */
class Operation_Factory extends Factory {
	/**
	 * Generate the factory object(s).
	 *
	 * @return Operation
	 */
	public function generate(): Operation {
		$this->validate_arguments( [
			'route'   => Route::class,
			'handler' => Route_Handler::class,
			'method'  => Http_Method::class,
		] );

		// dd($this->arguments);

		$operation = new Operation( filter_out_nulls( [
			'parameters'  => ( new Parameter_Factory( $this->generator, $this->arguments ) )->generate(),
			'requestBody' => Request_Body_Factory::make( $this->generator, $this->arguments ),
			// 'responses' =>
		] ) );

		/**
		 * Filter the OpenAPI operation.
		 *
		 * @param Operation            $operation OpenAPI operation.
		 * @param array<string, mixed> $arguments Arguments for the operation.
		 * @param string               $sanitized_route Sanitized route.
		 */
		$operation = apply_filters( 'wp_swagger_generator_operation', $operation, $this->arguments, $this->arguments->route->sanitized_route );

		if ( ! $operation instanceof Operation ) {
			throw new RuntimeException( 'Operation must be an instance of ' . Operation::class );
		}

		return $operation;
	}
}
