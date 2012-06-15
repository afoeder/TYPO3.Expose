<?php

namespace Foo\ContentManagement\Core;

/* *
 * This script belongs to the Foo.ContentManagement package.              *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;
use Foo\ContentManagement\Annotations as CM;

/**
 * ContentManager to retrieve and Initialize Adapters
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @FLOW3\Scope("singleton")
 */
class ContentManager {
	/**
	 * @var \TYPO3\FLOW3\Configuration\ConfigurationManager
	 * @FLOW3\Inject
	 */
	protected $configurationManager;

	/**
	 * @var \Foo\ContentManagement\Reflection\AnnotationService
	 * @FLOW3\Inject
	 */
	protected $annotationService;

	/**
	 * @var \TYPO3\FLOW3\Object\ObjectManagerInterface
	 * @FLOW3\Inject
	 */
	protected $objectManager;

	/**
	 * Containes initialized Adapters
	 * @var array
	 */
	var $adapters = array();

	/**
	 * Currently active Adapter
	 * 
	 * @var object
	 **/
	var $currentAdapter = null;

	public function __construct(\TYPO3\FLOW3\Configuration\ConfigurationManager $configurationManager, \TYPO3\FLOW3\Object\ObjectManagerInterface $objectManager) {
		$this->configurationManager = $configurationManager;
		$this->objectManager = $objectManager;
		$this->adapters = $this->getAdapters();
	}

	/**
	 * return the Adapter responsible for the class
	 *
	 * @return $groups Array
	 * @CM\Cache
	 */
	public function getAdapterByClass($class){
		$implementations = class_implements("\\" . ltrim($class, "\\"));
		if(in_array("Doctrine\ORM\Proxy\Proxy", $implementations))
			$class = get_parent_class("\\" . ltrim($class, "\\"));

		$this->adapters = $this->getAdapters();
		
		$adaptersByBeings = array();
		foreach ($this->adapters as $adapter) {
			foreach ($adapter->getClasses() as $class) {
				$adaptersByBeings[$class] = get_class($adapter);
			}
		}
		
		$adapterClass = $adaptersByBeings[$class];

		return $this->adapters[$adapterClass];
	}

	public function getAdapter() {
		return $this->currentAdapter;
	}

	public function setAdapterByClass($class) {
		$this->currentAdapter = $this->getAdapterByClass($class);
	}
	
	/**
	 * Returns all active adapters
	 *
	 * @return $adapters
	 */
	public function getAdapters(){
		$settings = $this->getSettings();
		$adapters = array();
		foreach ($settings["Adapters"] as $adapter => $active) {
			if($active == "active"){
				$adapters[$adapter] = $this->objectManager->get($adapter);
			}
		}
		return $adapters;
	}

	public function getClassAnnotations($class) {
		$classConfiguration = $this->annotationService->getClassAnnotations($class);
		return $classConfiguration;
	}

	public function getClass($object) {
		$class = get_class($object);

		$implementations = class_implements("\\" . ltrim($class, "\\"));
		if(in_array("Doctrine\ORM\Proxy\Proxy", $implementations))
			$class = get_parent_class("\\" . ltrim($class, "\\"));
		
		return $class;
	}

	public function getClassShortName($class) {
		return $class;
	}

	/**
	 * returns all active groups
	 *
	 * @return $groups Array
	 * @CM\Cache
	 */
	public function getGroups(){
		$groups = array();
		$adapters = array();
		foreach ($this->adapters as $adapter) {
			foreach ($adapter->getGroups() as $group => $beings) {
				foreach ($beings as $conf) {
					$being = $conf["being"];
					$conf["adapter"] = get_class($adapter);
					$groups[$group]["beings"][$being] = $conf;
				}
			}
		}

		return $groups;
	}

	/**
	 * get the group which the class belongs to
	 *
	 * @param string $class 
	 * @return $group string
	 * @CM\Cache
	 */
	public function getGroupByClass($class){
		foreach ($this->adapters as $adapter) {
			foreach ($adapter->getGroups() as $group => $beings) {
				foreach ($beings as $beingName => $conf) {
					if($class == $beingName)
						break;
				}
			}
		}
		return $group;
	}

	public function getId($object) {
        return $this->getAdapterByClass(get_class($object))->getId($object);
    }

	public function getObject($class, $id = null) {
		$class = ltrim($class, "\\");
		return $this->getAdapterByClass($class)->getObject($class, $id);
	}

	public function createObject($class, $object) {
		$class = ltrim($class, "\\");
		return $this->getAdapterByClass($class)->createObject($class, $object);
	}

	public function deleteObject($class, $id) {
		$class = ltrim($class, "\\");
		return $this->getAdapterByClass($class)->deleteObject($class, $id);
	}

	public function updateObject($class, $object) {
		$class = ltrim($class, "\\");
		return $this->getAdapterByClass($class)->updateObject($class, $object);
	}

	public function getObjects($class) {
		$class = ltrim($class, "\\");
		return $this->getAdapterByClass($class)->getObjects($class);
	}

	public function getPropertyAnnotations($class, $property) {
		$classAnnotations = $this->annotationService->getClassAnnotations($class);
		return $classAnnotations->getPropertyAnnotations($property);
	}

	public function getProperties($object, $context = null) {
		$classAnnotations = $this->getClassAnnotations(get_class($object));
		$classAnnotations->setObject($object);
		return $classAnnotations->getProperties($context);
	}

	public function getShortName($class){
		if(is_object($class))
			$class = get_class($class);

		$parts = explode("\\", $class);
		return array_pop($parts);
	}

	public function getString($object) {
		return sprintf("%s:%s", get_class($object), $this->getId($object));
	}

	public function getSettings($namespace = "Foo.ContentManagement") {
		return $this->configurationManager->getConfiguration(\TYPO3\FLOW3\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, $namespace);
	}

	public function isNewObject($object) {
		return $this->getAdapterByClass(get_class($object))->isNewObject($object);
	}

	public function toString($value) {
		
	}

	public function initQuery($being) {
		return $this->getAdapterByClass($being)->initQuery($being);
	}

	public function executeQuery($being) {
		return $this->getAdapterByClass($being)->executeQuery();
	}

	public function getQuery($being) {
		return $this->getAdapterByClass($being)->getQuery();
	}
}
?>