<?php
namespace TYPO3\Expose\Annotations;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Expose".               *
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
final class Type extends ContextAwareAnnotation implements SingleAnnotationInterface {

	/**
	 * @var string
	 */
	public $name = 'string';

	/**
	 * @var array
	 */
	public $subtype = NULL;

	/**
	 * @param array $values
	 */
	public function __construct(array $values = array()) {
		$this->name = isset($values['value']) ? $values['value'] : $this->name;
		$this->name = isset($values['name']) ? $values['name'] : $this->name;
		$this->subtype = isset($values['subtype']) ? $values['subtype'] : $this->subtype;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->name;
	}

	/**
	 * @return object
	 */
	public function getInstance() {
		return new $this->name();
	}
}

?>