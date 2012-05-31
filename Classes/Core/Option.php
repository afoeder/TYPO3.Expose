<?php

namespace Foo\ContentManagement\Core;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
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
 * represents an option for a property
 *
 * TODO: (SK) I do not understand what this does; and why it is needed :-) Especially why does an option need a "selected" state?
 * TODO: (SK) Scope Prototype annotations should be removed globally
 * TODO: (SK) version annotations should be removed globally
 * TODO: (SK) author annotations should be removed globally
 * 
 *       (MN) this class is practically obsolete and can be removed.
 *            It was used by my Form-Architecture to repesent an Option in SelectElements
 *
 * @version $Id: ForViewHelper.php 3346 2009-10-22 17:26:10Z k-fish $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 */
class Option{

    protected $id;
    protected $value;
    protected $selected;

	public function __construct($id, $value, $selected = false){
		$this->id = $id;
		$this->value = $value;
		$this->selected = $selected;
	}

    public function __toString(){
        return $this->getValue();
    }

    public function getValue() {
        return $this->value;
    }

    public function setValue($value) {
        $this->value = $value;
    }

    public function getSelected() {
        return $this->selected;
    }

    public function setSelected($selected) {
        $this->selected = $selected;
    }

    public function getId(){
        return $this->id;
    }

    public function setId($id){
        $this->id = $id;
    }
}

?>