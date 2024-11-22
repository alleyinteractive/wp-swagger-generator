<?php
/**
 * Factory class file
 *
 * @package wp-swagger-generator
 */
namespace Alley\WP\Swagger_Generator\Factory;

use Alley\WP\Swagger_Generator\Generator;
use Mantle\Support\Traits\Makeable;

/**
 * Base Factory class.
 *
 * @template TObject of \cebe\openapi\SpecBaseObject
 */
abstract class Factory {
	use Makeable;

	/**
	 * Constructor.
	 *
	 * @param Generator $generator Generator instance.
	 */
	public function __construct( public readonly Generator $generator ) {}

	/**
	 * Generate the factory object(s).
	 *
	 * @return mixed
	 * @phpstan-return TObject
	 */
	abstract function generate(): mixed;
}
