<?php
/**
 * OperationFactory class file
 *
 * @package wp-swagger-generator
 */

namespace Alley\WP\SwaggerGenerator\Factory;

use Alley\WP\SwaggerGenerator\HttpMethod;
use Alley\WP\SwaggerGenerator\Objects\Route;
use Alley\WP\SwaggerGenerator\Objects\RouteHandler;
use cebe\openapi\spec\Operation;
use RuntimeException;

use function Alley\WP\SwaggerGenerator\filter_out_nulls;
use function Mantle\Support\Helpers\collect;

/**
 * Operation Factory class.
 *
 * @todo Request body
 * @todo Responses.
 *
 * @extends Factory<\cebe\openapi\Operation, array{
 *   route: \Alley\WP\SwaggerGenerator\Objects\Route,
 *   handler: \Alley\WP\SwaggerGenerator\Objects\RouteHandler,
 *   method: \Alley\WP\SwaggerGenerator\HttpMethod
 * }>
 */
class OperationFactory extends Factory {
	/**
	 * Generate the factory object(s).
	 *
	 * @return Operation
	 */
	public function generate(): Operation {
		$this->validate_arguments( [
			'route'   => Route::class,
			'handler' => RouteHandler::class,
			'method'  => HttpMethod::class,
		] );

		// dd($this->arguments);

		$operation = new Operation( collect( [
			'parameters'  => ParameterFactory::make( $this->generator, $this->arguments ),
			// 'requestBody' => RequestBodyFactory::make( $this->generator, $this->arguments ),
			// 'responses' =>
		] )->filter()->all() );

		/**
		 * Filter the OpenAPI operation.
		 *
		 * @param Operation            $operation OpenAPI operation.
		 * @param array<string, mixed> $arguments Arguments for the operation.
		 * @param string               $sanitized_route Sanitized route.
		 */
		$operation = apply_filters( 'wp_swagger_generator_operation', $operation, $this->arguments, $this->arguments['route']->sanitized_route() );

		if ( ! $operation instanceof Operation ) {
			throw new RuntimeException( 'Operation must be an instance of ' . Operation::class );
		}

		return $operation;
	}
}
