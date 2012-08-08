<?php
namespace TYPO3\Admin\Operations;

/*                                                                        *
 * This script belongs to the TYPO3.Admin package.              		  *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Count the number of elements in the context, possibly filtering them before counting.
 */
class GetClassPackageOperation extends \TYPO3\Eel\FlowQuery\Operations\AbstractOperation {

    /**
     * {@inheritdoc}
     *
     * @var boolean
     */
    static protected $final = TRUE;

    /**
     * @var \TYPO3\FLOW3\Object\ObjectManagerInterface
     * @FLOW3\Inject
     */
    protected $objectManager;

    /**
     * {@inheritdoc}
     *
     * @var string
     */
    static protected $shortName = 'getClassPackage';

    /**
     * {@inheritdoc}
     *$
     * @param \TYPO3\Eel\FlowQuery\FlowQuery $flowQuery the FlowQuery object
     * @param array $arguments the arguments for this operation
     * @return mixed|null if the operation is final, the return value
     */
    public function evaluate(\TYPO3\Eel\FlowQuery\FlowQuery $flowQuery, array $arguments) {
        if (count($arguments) == 0) {
            $value = $flowQuery->getContext();
            if (is_array($value)) {
                $value = current($value);
            }
            if (method_exists($value, 'getFirst')) {
                $value = $value->getFirst();
            }
            $className = get_class($value);
            return $this->objectManager->getPackageKeyByObjectName($className);
        } else {
            $flowQuery->pushOperation('empty', array());
            $flowQuery->pushOperation('filter', $arguments);
        }
    }

}

?>