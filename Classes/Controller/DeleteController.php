<?php
namespace TYPO3\Admin\Controller;

/* *
 * This script belongs to the TYPO3.Admin package.              *
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

use Doctrine\ORM\Mapping as ORM;
use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Action to confirm the deletion of a being
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class DeleteController extends \TYPO3\Admin\Core\Features\AbstractFeature {

    /**
    * TODO: Document this Property!
    */
    protected $defaultViewObjectName = 'TYPO3\\TypoScript\\View\\TypoScriptView';

    /**
     * @var \TYPO3\FLOW3\Property\PropertyMapper
     * @FLOW3\Inject
     */
    protected $propertyMapper;

    /**
     * Delete objects
     *
     */
    public function deleteAction() {
        $being = $this->request->getArgument('being');
        $ids = array();
        if ($this->request->hasArgument('id')) {
            $ids = array($this->request->getArgument('id')
            );
        } else {
            if ($this->request->hasArgument('ids')) {
                $ids = $this->request->getArgument('ids');
            }
        }
        if (is_array($ids)) {
            foreach ($ids as $id) {
                $this->metaPersistenceManager->deleteObject($being, $id);
            }
            $arguments = array('being' => $this->metaPersistenceManager->getClassShortName($being)
            );
            $this->redirect('index', 'list', null, $arguments);
        }
    }

    /**
     * @param string $type
     * @param array $object
     */
    public function indexAction($type, $object) {
        $object = $this->propertyMapper->convert($object, $type);
        $this->view->assign('object', $object);
        $this->view->assign('type', $type);
    }

}

?>