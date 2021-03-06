<?php
namespace TYPO3\Expose\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Expose".          *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * Action to display a list of records of the same type
 *
 */
class ListController extends AbstractController {
	/**
	 * @var string
	 */
	protected $typoScriptPath = 'listController<TYPO3.Expose:RecordList>';

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Reflection\ReflectionService
	 */
	protected $reflectionService;

	/**
	 * @var \TYPO3\Flow\Object\ObjectManager
	 * @Flow\Inject
	 */
	protected $objectManager;

	/**
	 * List objects, all being of the same $type.
	 *
	 * TODO: Filtering of this list, bulk
	 *
	 * @param string $type
	 * @param string $format
	 * @return void
	 */
	public function indexAction($type, $format = 'table') {
		if ($type === 'TYPO3\TYPO3CR\Domain\Model\NodeInterface') {
				// If we deal with nodes, we want the content list controller to take over
			$this->forward('index', 'contentlist', 'TYPO3.Expose', $this->request->getArguments());
		}

		$classSchema = $this->reflectionService->getClassSchema($type);
		$exposeSchema = $this->getSchema($type);

		if ($classSchema->getRepositoryClassName() !== NULL) {
			$queryMethod = $exposeSchema['queryMethod'];
			$query = $this->objectManager->get($classSchema->getRepositoryClassName())->$queryMethod();
		} else {
			$query = $this->persistenceManager->createQueryForType($type);
		}

		$objects = $query->execute();
		// $this->redirectToNewFormIfNoObjectsFound($objects);
		$this->view->assign('type', $type);
		$this->view->assign('format', $format);
		$this->view->assign('objects', $objects);
	}

	/**
	 * @param \TYPO3\Flow\Persistence\QueryResultInterface $result
	 * @return void
	 */
	protected function redirectToNewFormIfNoObjectsFound(\TYPO3\Flow\Persistence\QueryResultInterface $result) {
		if (count($result) === 0) {
			$arguments = array('type' => $this->arguments['type']->getValue());
			$this->redirect('index', 'new', NULL, $arguments);
		}
	}

}

?>