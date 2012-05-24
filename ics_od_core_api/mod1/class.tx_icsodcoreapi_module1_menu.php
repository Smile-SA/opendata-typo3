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
 * $Id: class.tx_icsodcoreapi_module1_menu.php 48432 2011-06-07 09:27:20Z emilieprudhomme $
 */



/**
 * Class 'Commands documentation menu' for the 'ics_od_core_api' extension.
 *
 * @author	Tsi <tsi@in-cite.net>
 * @package	TYPO3
 * @subpackage	tx_icsodcoreapi
 */
class tx_icsodcoreapi_module1_menu{
	private $content = '';

	/**
	 * Loads menu
	 *
	 * @param	tx_ics_od_core_api_command		$command: Open Data's Command
	 * @return	void
	 */
	function load($command){
		global $LANG;
		$elementPath = 'command';
		$elementName = 'command';
		$this->command();
		$this->addParameter( $elementPath . '|parameters|' . $command->getParametersCount() );
		$this->parameters( $command, $elementName . '[parameters]', $elementPath . '|parameters');
	}

	/**
	 * Loads menu command
	 *
	 * @return	void
	 */
	private function command(){
		global $LANG;
		$onclick = "return changeForm('command')";
		$this->content .= '<p><a href="javascript:void();" onclick="' . $onclick . '" >' . htmlspecialchars($LANG->getLL('show_command')) . '</a></p>';
	}

	/**
	 * Loads menu add parameter
	 *
	 * @param	string		$elementPath
	 * @return	void
	 */
	private function addParameter($elementPath){
		global $LANG;
		$elementId = str_replace('|', '_', $elementPath);
		$onclick = "return newForm('" . $elementPath . "')";
		$this->content .= '<p>';
		$this->content .= '<label for="' . $elementId . '">' . htmlspecialchars($LANG->getLL('add_parameter')) . '</label>';
		$this->content .= '<input type="image" class="image" ' . t3lib_iconWorks::skinImg($BACK_PATH,'gfx/add.gif') . ' alt="' . htmlspecialchars($LANG->getLL('add_parameter.img')) . '" onclick="' . $onclick . '" id="' . $elementId . '"/>';
		$this->content .= '</p>';
	}

	/**
	 * Loads menu parameters
	 *
	 * @param	object		$obj: command or value
	 * @param	string		$elementName
	 * @param	string		$elementPath
	 * @return	void
	 */
	private function parameters($obj, $elementName, $elementPath){
		$this->content .= '<div class="parameters_content">';
		for( $i=0; $i<$obj->getParametersCount(); $i++ ){
			$this->parameter( $obj->getParameter($i), $elementName . '[' .$i .']', $elementPath . '|' . $i );
		}
		$this->content .= '</div>';
	}

	/**
	 * Loads menu parameter
	 *
	 * @param	tx_icsodcoreapi_parameter		$parameter
	 * @param	string		$elementName
	 * @param	string		$elementPath
	 * @return	void
	 */
	private function parameter($parameter, $elementName, $elementPath){
		global $LANG;
		$this->content .= '<p>';
		$onclick = "return changeForm('" . $elementPath . "')";
		$this->content .= '<a href="javascript:document.tx_icsodcoreapi_module1.submit()" onclick="' . $onclick . '" >' . ( ($parameter->getName()=='')? str_replace('|', '_', $elementPath) : $parameter->getName() ) . '</a>';
		$onclick = "return deleteElement('" . $elementPath . "');";
		$this->content .= '<input type="image" class="image" ' . t3lib_iconWorks::skinImg($BACK_PATH,'gfx/garbage.gif') . ' alt="' . htmlspecialchars($LANG->getLL('del_parameter.img')) . '" onclick="' . $onclick . '" />';
		$this->content .= '</p>';
		if( $parameter->getType() == 'enum' ){
			$this->addValue( $elementPath . '|values|' . $parameter->getValuesCount() );
			$this->values( $parameter, $elementName . '[values]', $elementPath . '|values' );
		}
	}

	/**
	 * Loads menu add value
	 *
	 * @param	string		$elementPath
	 * @return	void
	 */
	private function addValue($elementPath){
		global $LANG;
		$elementId = str_replace('|', '_', $elementPath);
		$onclick = "return newForm('" . $elementPath . "')";
		$this->content .= '<p>';
		$this->content .= '<label for="' . $elementId . '">' . htmlspecialchars($LANG->getLL('add_value')) . '</label>';
		$this->content .= '<input type="image" class="image" ' . t3lib_iconWorks::skinImg($BACK_PATH,'gfx/add.gif') . ' alt="' . htmlspecialchars($LANG->getLL('add_value.img')) . '" onclick="' . $onclick . '" id="' . $elementId . '"/>';
		$this->content .= '</p>';
	}

	/**
	 * Loads menu values
	 *
	 * @param	object		$obj: command or value
	 * @param	string		$elementName
	 * @param	string		$elementPath
	 * @return	void
	 */
	private function values($parameter, $elementName, $elementPath){
		$this->content .= '<div class="values_content">';
		for( $i=0; $i<$parameter->getValuesCount(); $i++ ){
			$this->value( $parameter->getValue($i), $elementName . '['.$i.']', $elementPath.'|'.$i );
		}
		$this->content .= '</div>';
	}

	/**
	 * Loads menu value
	 *
	 * @param	tx_icsodcoreapi_value		$value
	 * @param	string		$elementName
	 * @param	string		$elementPath
	 * @return	void
	 */
	private function value($value, $elementName, $elementPath){
		global $LANG;
		$this->content .= '<p>';
		$onclick = "return changeForm('" . $elementPath . "')";
		$this->content .= '<a href="javascript:document.tx_icsodcoreapi_module1.submit()" onclick="' . $onclick . '" >' . ( ($value->getValue()=='')? str_replace('|', '_', $elementPath) : $value->getValue() ) . '</a>';
		$onclick = "return deleteElement('" . $elementPath . "');";
		$this->content .= '<input type="image" class="image" ' . t3lib_iconWorks::skinImg($BACK_PATH,'gfx/garbage.gif') . ' alt="' . htmlspecialchars($LANG->getLL('del_parameter.img')) . '" onclick="' . $onclick . '" />';
		$this->content .= '</p>';
		$this->addParameter( $elementPath . '|parameters|' . $value->getParametersCount() );
		$this->parameters($value, $elementName . '[parameters]', $elementPath . '|parameters' );
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