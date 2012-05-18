<?php
namespace Foo\ContentManagement\Annotations;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * @Annotation
 */
final class Inline implements SingleAnnotationInterface {
	/**
	 * @var integer
	 */
	public $amount = 3;

	/**
	 * @var string
	 **/
	protected $variant = "Foo.ContentManagement:InlineTabular";
	
	/**
	 * @param string $value
	 */
	public function __construct(array $values = array()) {
		$this->amount = isset($values['amount']) ? $values['amount'] : $this->amount;
		$this->variant = isset($values['variant']) ? $values['variant'] : $this->variant;
	}

	public function getAmount() {
		return $this->amount;
	}

	public function getVariant() {
		return $this->variant;
	}
}

?>