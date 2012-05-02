<?php
namespace Foo\ContentManagement\Adapters;

/*                                                                        *
 * This script belongs to the FLOW3 package "Contacts".                   *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License as published by the Free   *
 * Software Foundation, either version 3 of the License, or (at your      *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        *
 * You should have received a copy of the GNU General Public License      *
 * along with the script.                                                 *
 * If not, see http://www.gnu.org/licenses/gpl.html                       *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Doctrine\ORM\Mapping as ORM;
use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Adapter for the Doctrine engine
 *
 * @version $Id: AbstractValidator.php 3837 2010-02-22 15:17:24Z robert $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 *
 * @FLOW3\Scope("singleton")
 */
class DoctrineAdapter extends \Foo\ContentManagement\Core\Adapters\AbstractAdapter {
    /**
     * @var \TYPO3\FLOW3\Persistence\PersistenceManagerInterface
     * @author Marc Neuhaus <apocalip@gmail.com>
     * @FLOW3\Inject
     */
    protected $persistenceManager;

    /**
     * @FLOW3\Inject
     * @var \TYPO3\FLOW3\Validation\ValidatorResolver
     */
    protected $validatorResolver;

    /**
     * apply filters
     *
     * @param string $beings
     * @param string $filters
     * @return void
     * @author Marc Neuhaus
     */
    public function applyFilters($beings, $filters) {
    }

    public function applyLimit($limit) {
        $this->query->setLimit($limit);
    }

    public function applyOffset($offset) {
        $this->query->setOffset($offset);
    }

    public function applyOrderings($property, $direction = null) {
        if (is_null($direction)) {
            $direction = \TYPO3\FLOW3\Persistence\QueryInterface::ORDER_ASCENDING;
        }

        $this->query->setOrderings(array(
            $property => $direction
        ));
    }

    public function getName($being) {
        return ucfirst($being);
    }

    public function initQuery($being) {
        $repository = str_replace("Domain\\Model", "Domain\\Repository", $being) . "Repository";
        $repository = $this->getRepositoryForModel($being);
        if (\class_exists($repository)) {
            $this->repository = $this->objectManager->get($repository);
            $this->query = $this->repository->createQuery();
        }
    }

    public function getGroups() {
        $this->init();
        $groups = array();
        $classes = $this->configurationManager->getClassesAnnotatedWith(array("active"));
        foreach ($this->settings["Beings"] as $being => $conf) {
            if (isset($conf["active"]) && $conf["active"] == true) {
                if (isset($conf["group"]))
                    $classes[$being] = $conf["group"];
                else
                    $classes[$being] = $this->objectManager->getPackageKeyByObjectName($being);
            }
        }

        foreach ($classes as $class => $packageName) {
            $configuration = $this->configurationManager->getClassConfiguration($class);
            $repository = $this->getRepositoryForModel($class);

            if (class_exists($repository)) {
                $group = $packageName;
                $name = \Foo\ContentManagement\Core\Helper::getShortName($class);

                if (isset($configuration["group"]))
                    $group = strval(current($configuration["group"]));

                if (isset($configuration["label"]))
                    $name = strval(current($configuration["label"]));

                $groups[$group][] = array("being" => $class, "name" => $name);
            }
        }
        return $groups;
    }

    public function getObject($being, $id = null) {
        if (class_exists($being)) {
            if ($id == null) {
                return $this->objectManager->get($being);
            } else {
                return $this->persistenceManager->getObjectByIdentifier($id, $being);
            }
        }
        return null;
    }

    public function getObjects($being) {
        $configuration = $this->configurationManager->getClassConfiguration($being);
        $objects = array();

        if (!isset($this->query) || !is_subclass_of($being, $this->repository->getEntityClassName()))
            $this->initQuery($being);

        if (isset($configuration["class"]["admin\annotations\orderby"])) {
            $this->query->setOrderings(array(
                current($configuration["class"]["admin\annotations\orderby"]) => 'ASC'
            ));
        }
    
        if(isset($this->query) && is_object($this->query))
            $objects = $this->query->execute();

        return $objects;
    }

    public function getId($object) {
        if (is_object($object)) {
            return $this->persistenceManager->getIdentifierByObject($object);
        }
        return null;
    }

    public function getQuery() {
        return $this->query;
    }

    public function getRepositoryForModel($model) {
        $configuration = $this->configurationManager->getClassConfiguration($model);
        if (isset($configuration["repository"])) {
            $annotation = current($configuration["repository"]);
            $repository = $annotation->class;
        } else {
            $repository = \Foo\ContentManagement\Core\Helper::getModelRepository($model);
        }

        return $repository;
    }

    public function getTotal($being) {
        return $this->query->count();
    }


    public function createObject($being, $data) {
        $configuration = $this->configurationManager->getClassConfiguration($being);
        $result = $this->transform($data, $being);

        if (is_a($result, $being)) {
            $repository = $this->objectManager->get($this->getRepositoryForModel($being));
            $repository->add($result);
            $this->persistenceManager->persistAll();
        }
        return $result;
    }

    public function updateObject($being, $id, $data) {
        $configuration = $this->configurationManager->getClassConfiguration($being);
        $result = $this->transform($data, $being);

        if (is_a($result, $being)) {
            $repository = $this->objectManager->get($this->getRepositoryForModel($being));
            $repository->update($result);
            $this->persistenceManager->persistAll();
        }
        return $result;
    }

    public function deleteObject($being, $id) {
        $object = $this->persistenceManager->getObjectByIdentifier($id, $being);
        if ($object == null) return;
        $repositoryObject = $this->objectManager->get($this->getRepositoryForModel($being));
        $repositoryObject->remove($object);
        $this->persistenceManager->persistAll();
    }

    ## Conversion Functions

    public function transform($data, $target) {
        return $data;

        #$object = $this->getObject($target, isset($data["__identity"]) ? $data["__identity"] : null);
        #unset($data["__identity"]);
        #$value = $this->propertyMapper->convert($data, $target, \Foo\ContentManagement\Core\PropertyMappingConfiguration::getConfiguration());
        
        #foreach ($data as $property => $value) {
        #    if(empty($value))
        #        continue;
        #    \TYPO3\FLOW3\Reflection\ObjectAccess::setProperty($object, $property, $value);
        #}

        #return $object;
    }
/*
    public function transform_($data, $target) {
        $data = $this->cleanUpBlanks($data);

        $value = $this->propertyMapper->convert($data, $target, \Foo\ContentManagement\Core\PropertyMappingConfiguration::getConfiguration());

        $this->validationResults = $this->propertyMapper->getMessages();

        $validator = $this->validatorResolver->getBaseValidatorConjunction($target);
        $validationMessages = $validator->validate($value);
        $this->validationResults->merge($validationMessages);
        $errors = $this->validationResults->getFlattenedErrors();

        if (empty($errors))
            return $value;
        else
            return $errors;
    }

    public function cleanUpBlanks($data, $removeEmptyArrays = false, $removeUnchangedDefaults = true) {
        $defaults = array();
        foreach ($data as $key => $value) {
            if($key === "{counter}"){
                $defaults = $value;
            }

            if (is_array($value)) {
                $data[$key] = $this->cleanUpBlanks($value, true);
            }
            if (is_object($value) && !empty($value->FLOW3_Persistence_Entity_UUID)) {
                $data[$key] = $value->FLOW3_Persistence_Entity_UUID;
            }

            if (empty($data[$key]) && $data[$key] !== false && $data[$key] !== 0) {
                if (is_array($data[$key])) {
                    if ($removeEmptyArrays)
                        unset($data[$key]);
                } else {
                    unset($data[$key]);
                }
            }

            if(serialize($value) == serialize($defaults) && $removeUnchangedDefaults)
                unset($data[$key]);
        }
        return $data;
    }
*/
}

?>