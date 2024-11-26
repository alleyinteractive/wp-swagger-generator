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
	 * Create a new static instance from arguments.
	 *
	 * @param Generator $generator Generator instance.
	 * @param array     $arguments Arguments for the factory.
	 * @phpstan-param TArguments $arguments
	 *
	 * @return mixed
	 * @phpstan-return TObject
	 */
	public static function make( Generator $generator, array $arguments = [] ): mixed {
		return ( new static( $generator, $arguments ) )->generate(); // @phpstan-ignore-line unsafe usage of new static()
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
	 * Merge arguments with the factory arguments.
	 *
	 * @param array<mixed> $arguments Arguments to merge.
	 * @return array<mixed>
	 */
	public function forward_arguments( array $arguments ): array {
		return array_merge( $this->arguments, $arguments );
	}

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
	 * Generate the factory object(s).
	 *
	 * @return mixed
	 * @phpstan-return TObject
	 */
	abstract function generate(): mixed;
}
