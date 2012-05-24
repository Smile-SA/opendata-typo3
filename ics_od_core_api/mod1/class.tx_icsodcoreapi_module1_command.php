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
 * $Id: class.tx_icsodcoreapi_module1_command.php 48432 2011-06-07 09:27:20Z emilieprudhomme $
 */
require_once( t3lib_extMgm::extPath('ics_od_core_api') . 'lib/class.tx_icsodcoreapi_parameter.php' );
require_once( t3lib_extMgm::extPath('ics_od_core_api') . 'mod1/class.tx_icsodcoreapi_module1_parameter.php' );

/**
 * Class 'Commands documentation command' for the 'ics_od_core_api' extension.
 *
 * @author	Tsi <tsi@in-cite.net>
 * @package	TYPO3
 * @subpackage	tx_icsodcoreapi
 */
class tx_icsodcoreapi_module1_command{
	private $content = '';

	/**
	 * Loads command form
	 *
	 * @param	tx_ics_od_core_api_command		$command: Open Data's Command
	 * @param	array		$currentStack:Current element
	 * @param	string		$elementPath
	 * @return	void
	 */
	function load($command, $currentStack, $elementPath){
		$elementName = 'command';
		$this->content .= '<div>';
		//if( ($currentStack[0] == 'command') && (count($currentStack) == 1) ){
		if( ($currentStack[0] == 'command') && (count($currentStack) == 1) && ($elementPath == str_replace('[', '|', str_replace( ']', '', $elementName ) )) ){
			$this->form($command, $elementName);
		}else{
			$this->hiddenForm($command, $elementName);
		}
		$this->content .= '</div>';
		array_shift($currentStack);
		if(  $currentStack[0] == 'parameters'){
			array_shift($currentStack);
		}
		for( $i=0; $i<$command->getParametersCount(); $i++ ){
			$parameter = new tx_icsodcoreapi_module1_parameter();
			$parameter->load(
				$command->getParameter($i),
				$currentStack,
				$elementPath,
				$elementName . '[parameters]',
				$i
			);
			$this->content .= $parameter->getContent();
		}
	}

	/**
	 * Generates form
	 *
	 * @param	tx_icsodcoreapi_command		$command
	 * @param	string		$elementName
	 * @return	void
	 */
	private function form($command, $elementName){
		global $LANG;
		$this->content .= '
			<p>
				<label for="name">' . htmlspecialchars($LANG->getLL('name')) . '</label>
				<input type="text" name="' . $elementName . '[name]" id="name" value="' . $command->getName() . '" />
			</p>
			<p>
				<label for="cmd">' . htmlspecialchars($LANG->getLL('cmd')) . '</label>
				<input type="text" name="' . $elementName . '[cmd]" id="cmd" value="' . $command->getCmd() . '" />
			</p>
			<p>
				<label for="brief">' . htmlspecialchars($LANG->getLL('brief')) . '</label>
				<input type="text" name="' . $elementName . '[brief]" id="brief" value="' . $command->getBrief() . '" />
			</p>
			<p>
				<label for="description" class="description">' . htmlspecialchars($LANG->getLL('description')) . '</label>
				<textarea name="' . $elementName . '[description]" id="description" cols="50" rows="10" >' . $command->getDescription() . '</textarea>
			</p>
			<div class="button">
				<input type="submit" name="update" value="' . htmlspecialchars($LANG->getLL('btn_update')) . '" />
			</div>
		';
	}

	/**
	 * Generates hidden form
	 *
	 * @param	tx_icsodcoreapi_command		$command
	 * @param	string		$elementName
	 * @return	void
	 */
	private function hiddenForm($command, $elementName){
		$this->content .= '
			<input type="hidden" name="' . $elementName . '[name]" value="' . $command->getName() . '" />
			<input type="hidden" name="' . $elementName . '[cmd]" value="' . $command->getCmd() . '" />
			<input type="hidden" name="' . $elementName . '[brief]" value="' . $command->getBrief() . '" />
			<input type="hidden" name="' . $elementName . '[description]" value="' . $command->getDescription() . '" />
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