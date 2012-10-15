<?php
namespace TYPO3\Expose\TypoScriptObjects;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Expose".               *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * Render a Form using the Form framework
 */
class ObjectFormBuilder extends \TYPO3\TypoScript\TypoScriptObjects\AbstractTypoScriptObject {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Expose\TypoScriptObjects\Helpers\BaseFormFactory
	 */
	protected $baseFormFactory;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Validation\ValidatorResolver
	 */
	protected $validatorResolver;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Reflection\ReflectionService
	 */
	protected $reflectionService;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * the class name to build the form for
	 *
	 * @var string
	 */
	protected $className;

	/**
	 * if set, the objects being used
	 *
	 * @var object
	 */
	protected $objects = NULL;

	/**
	 * @var string
	 */
	protected $formIdentifier;

	/**
	 * @var string
	 */
	protected $formPresetName;

	/**
	 * @var string
	 */
	protected $callbackAction;

	/**
	 * @param string $className
	 * @return void
	 */
	public function setClassName($className) {
		$this->className = $className;
	}

	/**
	 * @param string $objects
	 * @return void
	 */
	public function setObjects($objects) {
		$this->objects = $objects;
	}

	/**
	 * @param string $formIdentifier
	 * @return void
	 */
	public function setFormIdentifier($formIdentifier) {
		$this->formIdentifier = $formIdentifier;
	}

	/**
	 * @param string $formPresetName
	 * @return void
	 */
	public function setFormPresetName($formPresetName) {
		$this->formPresetName = $formPresetName;
	}

	/**
	 * @param string $callbackAction
	 * @return void
	 */
	public function setCallbackAction($callbackAction) {
		$this->callbackAction = $callbackAction;
	}

	/**
	 * Evaluate the collection nodes
	 *
	 * @return string
	 */
	public function evaluate() {
		$formDefinition = $this->baseFormFactory->build(array('identifier' => $this->tsValue('formIdentifier')), $this->tsValue('formPresetName'));
		$page = $formDefinition->createPage('page1');

		$forwardFinisher = new \TYPO3\Expose\Finishers\ControllerCallbackFinisher();
		$forwardFinisher->setOption('callbackAction', $this->tsValue('callbackAction'));
		$formDefinition->addFinisher($forwardFinisher);

		$objectNamespaces = array();
		if ($this->objects !== NULL && count($this->objects) > 0) {
			$i = 0;
			$objectIdentifiers = array();
			foreach ($this->tsValue('objects') as $object) {
				$section = $this->createFormForSingleObject($page, $object, 'objects.' . $i);
				$objectNamespaces[] = 'objects.' . $i;
				$i++;
				$formDefinition->getProcessingRule('objects.0')->setDataType(get_class($object));
				$section->setDataType(get_class($object));
			}
		} else {
			$section = $this->createFormForSingleObject($page, NULL, 'objects.0.');
			$section->setDataType(get_class($object));
			$objectNamespaces[] = 'objects.0';
		}

		$this->addValidatorsToForm($formDefinition, $objectNamespaces);
		return $formDefinition;
	}

	/**
	 * @param object $object
	 * @return array
	 */
	protected function getObjectIdentifierArrayForObject($object) {
		if ($this->persistenceManager->isNewObject($object)) {
			return array();
		}
		return array('__identity' => $this->persistenceManager->getIdentifierByObject($object));
	}

	/**
	 * @param \TYPO3\Form\Core\Model\AbstractSection $parentFormElement
	 * @param object $object
	 * @param string $namespace
	 * @return void
	 */
	public function createFormForSingleObject(\TYPO3\Form\Core\Model\AbstractSection $parentFormElement, $object, $namespace = '') {
		$sectionNames = $this->findSections();
		$formDefinition = $parentFormElement->getRootForm();

        $formDefinition->getProcessingRule($parentFormElement->getIdentifier())->getPropertyMappingConfiguration()->setTypeConverterOption('TYPO3\\FLOW3\\Property\\TypeConverter\\PersistentObjectConverter', \TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED, TRUE);
        $formDefinition->getProcessingRule($parentFormElement->getIdentifier())->getPropertyMappingConfiguration()->setTypeConverterOption('TYPO3\\FLOW3\\Property\\TypeConverter\\PersistentObjectConverter', \TYPO3\Flow\Property\TypeConverter\PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED, TRUE);
        $formDefinition->getProcessingRule($parentFormElement->getIdentifier())->getPropertyMappingConfiguration()->allowAllProperties();

		foreach ($sectionNames as $sectionName) {
			if ($parentFormElement instanceof \TYPO3\Form\Core\Model\Page) {
				$this->tsRuntime->pushContext('parentFormElement', $parentFormElement);
				$this->tsRuntime->pushContext('identifier', $sectionName . '.' . $namespace);
				$section = $this->tsRuntime->render($this->path . '/sectionBuilder');
				$this->tsRuntime->popContext();
				$this->tsRuntime->popContext();
			} else {
				$section = $parentFormElement;
			}

			$section->setLabel($this->getLabelForObject($object));
			$this->createElementsForSection($sectionName, $section, $namespace, $object);
		}

		$objectIdentifiers = $this->getObjectIdentifierArrayForObject($object);
		foreach ($objectIdentifiers as $key => $value) {
			$element = $section->createElement($namespace . '.' . $key, 'TYPO3.Expose:Hidden');
			$element->setDefaultValue($value);
		}

		return $section;
    }

	protected function getLabelForObject($object) {
	}

	/**
	 * @param \TYPO3\Form\Core\Model\FormDefinition $formDefinition
	 * @param array $objectNamespaces
	 * @return void
	 */
	protected function addValidatorsToForm(\TYPO3\Form\Core\Model\FormDefinition $formDefinition, array $objectNamespaces) {
		$className = $this->tsValue('className');
		$baseValidator = $this->validatorResolver->getBaseValidatorConjunction($className, array('Default', 'Form'));

		foreach ($baseValidator->getValidators() as $validator) {
			if ($validator instanceof \TYPO3\Flow\Validation\Validator\GenericObjectValidator) {
				foreach ($validator->getPropertyValidators() as $propertyName => $propertyValidatorList) {
					foreach ($objectNamespaces as $objectNamespace) {
						$formElement = $formDefinition->getElementByIdentifier($objectNamespace . '.' . $propertyName);
						if ($formElement !== NULL) {
							foreach ($propertyValidatorList as $propertyValidator) {
								$formElement->addValidator($propertyValidator);
							}
						}
					}
				}
			} else {
				// TODO: implement ELSE-case for other validators
			}
		}
	}

	protected function findSections() {
		// TODO implement
		return array('Default');
	}

	/**
	 * @param string $sectionName
	 * @param \TYPO3\Form\FormElements\Section $section
	 * @param string $namespace
	 * @param object $object
	 */
	public function createElementsForSection($sectionName, \TYPO3\Form\FormElements\Section $section, $namespace, $object) {
		// TODO evaluate $sectionName
		$className = $this->reflectionService->getClassNameByObject($object);
		$propertyNames = $this->reflectionService->getClassPropertyNames($className);
		$classSchema = $this->reflectionService->getClassSchema($className);
		$this->tsRuntime->pushContext('parentFormElement', $section);

		foreach ($propertyNames as $propertyName) {
			$propertySchema = $classSchema->getProperty($propertyName);

			$this->tsRuntime->pushContext('className', $className);
			$this->tsRuntime->pushContext('propertyName', $propertyName);
			$this->tsRuntime->pushContext('formElementIdentifier', $namespace . '.' . $propertyName);
			$this->tsRuntime->pushContext('propertyAnnotations', $this->reflectionService->getPropertyAnnotations($className, $propertyName));
			$this->tsRuntime->pushContext('propertySchema', $propertySchema);
			$this->tsRuntime->pushContext('propertyType', $propertySchema['type']);
			$this->tsRuntime->pushContext('propertyElementType', $propertySchema['elementType']);
			$this->tsRuntime->pushContext('formBuilder', $this);
			$this->tsRuntime->pushContext('propertyValue', \TYPO3\Flow\Reflection\ObjectAccess::getProperty($object, $propertyName));

			$element = $this->tsRuntime->render($this->path . '/elementBuilder');
			if (method_exists($element, 'setFormBuilder')){
				$element->setFormBuilder($this);
			}

			$this->tsRuntime->popContext();
			$this->tsRuntime->popContext();
			$this->tsRuntime->popContext();
			$this->tsRuntime->popContext();
			$this->tsRuntime->popContext();
			$this->tsRuntime->popContext();
			$this->tsRuntime->popContext();
			$this->tsRuntime->popContext();
			$this->tsRuntime->popContext();
		}
		$this->tsRuntime->popContext();
	}
}
?>