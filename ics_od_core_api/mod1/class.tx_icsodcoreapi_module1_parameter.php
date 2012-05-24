<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 In Cité Solution <technique@in-cite.net>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/*
 * $Id: class.tx_icsodcoreapi_module1_parameter.php 48432 2011-06-07 09:27:20Z emilieprudhomme $
 */
require_once( t3lib_extMgm::extPath('ics_od_core_api') . 'lib/class.tx_icsodcoreapi_value.php' );
require_once( t3lib_extMgm::extPath('ics_od_core_api') . 'mod1/class.tx_icsodcoreapi_module1_value.php' );
/**
 * Class 'Commands documentation parameter' for the 'ics_od_core_api' extension.
 *
 * @author	technique <technique@in-cite.net>
 * @package	TYPO3
 * @subpackage	tx_icsodcoreapi
 */
class tx_icsodcoreapi_module1_parameter{
	private $content = '';

	/**
	 * Loads form
	 *
	 * @param	tx_icsodcoreapi_parameter		$parameter
	 * @param	array		$currentStack
	 * @param	string		$elementPath
	 * @param	string		$elementName
	 * @param	int		$index
	 * @return	void
	 */
	function load($parameter, $currentStack, $elementPath, $elementName, $index){
		$this->content .= '<div>';
		// if( ($currentStack[0] == $index) && (count($currentStack) == 1) ){
		if( ($currentStack[0] == $index) && (count($currentStack) == 1) && ($elementPath == str_replace('[', '|', str_replace( ']', '', $elementName ) ) . '|' . $index) ){
			$this->form($parameter, $elementName . '[' . $index . ']');
		}else{
			$this->HiddenForm($parameter, $elementName . '[' . $index . ']');
		}
		$this->content .= '</div>';
		array_shift($currentStack);
		if(  $currentStack[0] == 'values'){
			array_shift($currentStack);
		}
		for( $i=0; $i<$parameter->getValuesCount(); $i++){
			$value = new tx_icsodcoreapi_module1_value();
			$value->load(
				$parameter->getValue($i),
				$currentStack,
				$elementPath,
				$elementName . '[' . $index . '][values]',
				$i
			);
			$this->content .= $value->getContent();
		}
	}

	/**
	 * Generates form
	 *
	 * @param	tx_icsodcoreapi_parameter		$parameter
	 * @param	string		$elementName
	 * @return	void
	 */
	private function form($parameter, $elementName){
		global $LANG;
		$this->content .= '
			<p>
				<label for="name">' . htmlspecialchars($LANG->getLL('name')) . '</label>
				<input type="text" name="' . $elementName . '[name]" id="name" value="' . $parameter->getName() . '"/>
			</p>
			<p>
				<label for="type">' . htmlspecialchars($LANG->getLL('type')) . '</label>
				<select name="' . $elementName . '[type]" id="type">
					<option value="enum" ' . ($parameter->getType() == 'enum'? 'selected = "selected"': '') . ' >' . htmlspecialchars($LANG->getLL('type.enum')) . '</option>
					<option value="number" ' . ($parameter->getType() == 'number'? 'selected = "selected"': '') . ' >' . htmlspecialchars($LANG->getLL('type.number')) . '</option>
					<option value="string" ' . ($parameter->getType() == 'string'? 'selected = "selected"': '') . ' >' . htmlspecialchars($LANG->getLL('type.string')) . '</option>
				</select>
			</p>
			<p>
				<label for="mandatory">' . htmlspecialchars($LANG->getLL('mandatory')) . '</label>
				<input type="checkbox" name="' . $elementName . '[mandatory]" id="mandatory" ' . ($parameter->getMandatory()? 'checked="checked"' : '') . ' />
			</p>
			<p>
				<label for="default">' . htmlspecialchars($LANG->getLL('default')) . '</label>
				<input type="text" name="' . $elementName . '[default]" id="default" value="' . $parameter->getDefault() . '"/>
			</p>
			<p>
				<label for="description" class="description">' . htmlspecialchars($LANG->getLL('description')) . '</label>
				<textarea name="' . $elementName . '[description]" id="description" cols="50" rows="10" >' . $parameter->getDescription() . '</textarea>
			</p>
			<div class="button">
				<input type="submit" name="update" value="' . htmlspecialchars($LANG->getLL('btn_update')) . '" />
			</div>
		';
	}

	/**
	 * Generates hidden form
	 *
	 * @param	tx_icsodcoreapi_parameter		$parameter
	 * @param	string		$elementName
	 * @return	void
	 */
	private function hiddenForm($parameter, $elementName){
		$this->content .= '
			<input type="hidden" name="' . $elementName . '[name]" value="' . $parameter->getName() . '" />
			<input type="hidden" name="' . $elementName . '[type]" value="' . $parameter->getType() . '" />
			<input type="hidden" name="' . $elementName . '[mandatory]" value="' . ($parameter->getMandatory()? 'on' : '') . '" />
			<input type="hidden" name="' . $elementName . '[default]" value="' . $parameter->getDefault() . '" />
			<input type="hidden" name="' . $elementName . '[description]" value="' . $parameter->getDescription() . '" />
		';
	}

	/**
	 * Retrieves content
	 *
	 * @return	string
	 */
	function getContent(){
		return $this->content;
	}
}
?>