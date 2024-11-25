<?php
/**
 * Operation_Factory class file
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\Swagger_Generator\Factory;

use cebe\openapi\spec\Operation;
use RuntimeException;

use function Alley\WP\Swagger_Generator\filter_out_nulls;

/**
 * Operation Factory class.
 *
 * @todo Request body
 * @todo Responses.
 *
 * @extends Factory<\cebe\openapi\Operation>
 */
class Operation_Factory extends Factory {
	/**
	 * Generate the factory object(s).
	 *
	 * @return Operation
	 */
	public function generate(): Operation {
		if ( ! isset( $this->arguments['route'], $this->arguments['callback'], $this->arguments['method'] ) ) {
			throw new RuntimeException( 'Expected arguments "route", "callback", and "method" arguments to be set.' );
		}

		$operation = new Operation( filter_out_nulls( [
			'parameters'  => Parameter_Factory::make( $this->generator, $this->arguments ),
			'requestBody' => Request_Body_Factory::make( $this->generator, $this->arguments ),
			// 'responses' =>
		] ) );

		/**
		 * Filter the OpenAPI operation.
		 *
		 * @param Operation $operation OpenAPI operation.
		 * @param array     $arguments Arguments for the operation.
		 */
		$operation = apply_filters( 'wp_swagger_generator_operation', $operation, $this->arguments );

		if ( ! $operation instanceof Operation ) {
			throw new RuntimeException( 'Operation must be an instance of ' . Operation::class );
		}

		return $operation;
	}
}
