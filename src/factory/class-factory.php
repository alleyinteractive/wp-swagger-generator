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
 * @template TObject of \cebe\openapi\SpecBaseObject|array<\cebe\openapi\SpecBaseObject>
 */
abstract class Factory {
	/**
	 * Create a new static instance from arguments.
	 *
	 * @param mixed ...$arguments Arguments to make from.
	 * @return mixed
	 * @phpstan-return TObject
	 */
	public static function make( ...$arguments ) {
		return ( new static( ...$arguments ) )->generate();
	}

	/**
	 * Constructor.
	 *
	 * @param Generator $generator Generator instance.
	 * @param array     $arguments Arguments for the factory.
	 */
	public function __construct( public readonly Generator $generator, public array $arguments = [] ) {}

	/**
	 * Merge arguments with the factory arguments.
	 *
	 * @param array $arguments Arguments to merge.
	 * @return array
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
		foreach ( $expected as $argument ) {
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
