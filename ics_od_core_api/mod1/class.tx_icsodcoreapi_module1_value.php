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
 * $Id: class.tx_icsodcoreapi_module1_value.php 48432 2011-06-07 09:27:20Z emilieprudhomme $
 */
require_once( t3lib_extMgm::extPath('ics_od_core_api') . 'lib/class.tx_icsodcoreapi_parameter.php' );
require_once( t3lib_extMgm::extPath('ics_od_core_api') . 'mod1/class.tx_icsodcoreapi_module1_parameter.php' );
/**
 * Class 'Commands documentation value' for the 'ics_od_core_api' extension.
 *
 * @author	technique <technique@in-cite.net>
 * @package	TYPO3
 * @subpackage	tx_icsodcoreapi
 */
class tx_icsodcoreapi_module1_value{
	private $content = '';

	/**
	 * Loads form
	 *
	 * @param	tx_icsodcoreapi_value		$value
	 * @param	array		$currentStack
	 * @param	string		$elementPath
	 * @param	string		$elementName
	 * @param	int		$index
	 * @return	void
	 */
	function load($value, $currentStack, $elementPath, $elementName, $index){
		$this->content .= '<div>';
		// if( ($currentStack[0] == $index) && (count($currentStack) == 1) ){
		if( ($currentStack[0] == $index) && (count($currentStack) == 1) && ($elementPath == str_replace('[', '|', str_replace( ']', '', $elementName ) ) . '|' . $index) ){
			$this->form($value,  $elementName . '[' . $index . ']');
		}else{
			$this->HiddenForm($value, $elementName . '[' . $index . ']');
		}
		$this->content .= '</div>';
		array_shift($currentStack);
		if(  $currentStack[0] == 'parameters'){
			array_shift($currentStack);
		}
		for( $i=0; $i<$value->getParametersCount(); $i++){
			$parameter = new tx_icsodcoreapi_module1_parameter();
			$parameter->load(
				$value->getParameter($i),
				$currentStack,
				$elementPath,
				$elementName . '[' . $index . '][parameters]',
				$i
			);
			$this->content .= $parameter->getContent();
		}
	}

	/**
	 * Generates form
	 *
	 * @param	tx_icsodcoreapi_value		$value
	 * @param	string		$elementName
	 * @return	void
	 */
	private function form($value, $elementName){
		global $LANG;
		$this->content .= '
			<p>
				<label for="value">' . htmlspecialchars($LANG->getLL('value')) . '</label>
				<input type="text" name="' . $elementName . '[value]" id="value" value="' . $value->getValue() . '"/>
			</p>
			<p>
				<label for="description" class="description">' . htmlspecialchars($LANG->getLL('description')) . '</label>
				<textarea name="' . $elementName . '[description]" id="description" cols="50" rows="10" >' . $value->getDescription() . '</textarea>
			</p>
			<div class="button">
				<input type="submit" name="update" value="' . htmlspecialchars($LANG->getLL('btn_update')) . '" />
			</div>
		';
	}

	/**
	 * Generates hidden form
	 *
	 * @param	tx_icsodcoreapi_value		$value
	 * @param	string		$elementName
	 * @return	void
	 */
	private function hiddenForm($value, $elementName){
		$this->content .= '
			<input type="hidden" name="' . $elementName . '[value]" value="' . $value->getValue() . '" />
			<input type="hidden" name="' . $elementName .'[description]" value="' . $value->getDescription() . '" />
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