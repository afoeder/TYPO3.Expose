<?php
 
namespace Foo\ContentManagement\ViewHelpers;

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
 * @version $Id: ForViewHelper.php 3346 2009-10-22 17:26:10Z k-fish $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @FLOW3\Scope("prototype")
 */
class ApiViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {
	/**
	 * Iterates through elements of $each and renders child nodes
	 *
	 * @param string $get
	 * @param string $as
	 * @return string Rendered string
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @api
	 */
	public function render($get, $as = null) {
		if($as == null)
			$as = $get;
		
		if(\Foo\ContentManagement\Core\API::has($get)){
			$this->templateVariableContainer->add($as, \Foo\ContentManagement\Core\API::get($get));
		}elseif(is_callable("\Foo\ContentManagement\Core\Api::get" . ucfirst($get))){
			$function = "get" . ucfirst($get);
			$result = call_user_func("\Foo\ContentManagement\Core\Api::get" . ucfirst($get));
			$this->templateVariableContainer->add($as, $result);
		}
		$output = $this->renderChildren();
		$this->templateVariableContainer->remove($as);
		
		return $output;
	}
}

?>
