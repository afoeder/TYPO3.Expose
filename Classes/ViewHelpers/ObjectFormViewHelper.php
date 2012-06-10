<?php
namespace Foo\ContentManagement\ViewHelpers;

/*                                                                        *
 * This script belongs to the Foo.ContentManagement package.              *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Main Entry Point to render a Form into a Fluid Template
 *
 * Usage
 * =====
 *
 * <pre>
 * {namespace form=TYPO3\Form\ViewHelpers}
 * <form:render factoryClass="NameOfYourCustomFactoryClass" />
 * </pre>
 *
 * The factory class must implement {@link TYPO3\Form\Factory\FormFactoryInterface}.
 *
 * @api
 */
class ObjectFormViewHelper extends \TYPO3\Form\ViewHelpers\RenderViewHelper {

	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\Form\Persistence\FormPersistenceManagerInterface
	 */
	protected $formPersistenceManager;

	/**
	 * @param string $persistenceIdentifier the persistence identifier for the form.
	 * @param string $factoryClass The fully qualified class name of the factory (which has to implement \TYPO3\Form\Factory\FormFactoryInterface)
	 * @param string $presetName name of the preset to use
	 * @param array $overrideConfiguration factory specific configuration
	 * @return string the rendered form
	 */
	public function render($persistenceIdentifier = NULL, $factoryClass = 'TYPO3\Form\Factory\ArrayFormFactory', $presetName = 'default', array $overrideConfiguration = array()) {
		if (isset($persistenceIdentifier)) {
			$overrideConfiguration = \TYPO3\FLOW3\Utility\Arrays::arrayMergeRecursiveOverrule($this->formPersistenceManager->load($persistenceIdentifier), $overrideConfiguration);
		}

		$factory = $this->objectManager->get($factoryClass);
		$formDefinition = $factory->build($overrideConfiguration, $presetName);
		$response = new \TYPO3\FLOW3\Http\Response($this->controllerContext->getResponse());

		$form = $formDefinition->bind($this->controllerContext->getRequest(), $response);
		return $form->render();
	}
}
?>