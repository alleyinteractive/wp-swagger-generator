<?php
/**
 * Factory class file
 *
 * @package wp-swagger-generator
 */
namespace Alley\WP\Swagger_Generator\Factory;

use Alley\WP\Swagger_Generator\Generator;

/**
 * Base Factory class.
 *
 * @template TObject of \cebe\openapi\SpecObjectInterface|array<\cebe\openapi\SpecObjectInterface>
 * @template TArguments of array<string, mixed>
 */
abstract class Factory {
	/**
	 * Make and generate a factory object.
	 *
	 * @param Generator    $generator Generator instance.
	 * @param array<string, mixed> $arguments Arguments for the factory.
	 * @return mixed
	 *
	 * @phpstan-param   TArguments $arguments
	 * @phpstan-return TObject
	 */
	public static function make( Generator $generator, array $arguments = [] ): mixed {
		return ( new static( $generator, $arguments ) )->generate();
	}

	/**
	 * Constructor.
	 *
	 * @param Generator $generator Generator instance.
	 * @param array     $arguments Arguments for the factory.
	 * @phpstan-param   TArguments $arguments
	 */
	public function __construct( public readonly Generator $generator, public array $arguments = [] ) {}

	/**
	 * Generate the factory object(s).
	 *
	 * @return mixed
	 * @phpstan-return TObject
	 */
	abstract function generate(): mixed;

	/**
	 * Validate that the expected arguments are set.
	 *
	 * @param string[] $expected Expected arguments.
	 * @throws \InvalidArgumentException If an expected argument is not set.
	 */
	protected function validate_arguments( array $expected ): void {
		foreach ( $expected as $index => $argument ) {
			// Validate the type of the argument.
			if ( ! is_numeric( $index ) ) {
				if ( ! isset( $this->arguments[ $index ] ) ) {
					throw new \InvalidArgumentException( sprintf( 'Expected argument "%s" to be set.', $index ) );
				}

				if ( ! $this->arguments[ $index ] instanceof $argument ) {
					throw new \InvalidArgumentException( sprintf( 'Expected argument "%s" to be an instance of "%s".', $index, $argument ) );
				}

				continue;
			}

			if ( ! isset( $this->arguments[ $argument ] ) ) {
				throw new \InvalidArgumentException( sprintf( 'Expected argument "%s" to be set.', $argument ) );
			}
		}
	}

	/**
	 * Forward existing arguments with new ones.
	 *
	 * @param array<string, mixed> $arguments Arguments to forward.
	 * @return array<string, mixed>
	 */
	protected function forward_arguments( array $arguments ): array {
		return array_merge( $this->arguments, $arguments );
	}

	/**
	 * Call another factory with forwarded arguments.
	 *
	 * @param class-string<Factory> $factory_class Factory class to call.
	 * @param array<string, mixed>  $arguments Arguments to forward.
	 * @return mixed
	 */
	protected function call_factory( string $factory_class, array $arguments = [] ): mixed {
		return $factory_class::make( $this->generator, $this->forward_arguments( $arguments ) );
	}
}
