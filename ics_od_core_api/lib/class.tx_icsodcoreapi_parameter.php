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
 * $Id: class.tx_icsodcoreapi_parameter.php 48432 2011-06-07 09:27:20Z emilieprudhomme $
 */

require_once( t3lib_extMgm::extPath('ics_od_core_api') . 'lib/class.tx_icsodcoreapi_value.php' );

/**
 * Objet 'parameter' for the 'ics_od_core_api' extension.
 *
 * @author	Tsi <tsi@in-cite.net>
 * @package	TYPO3
 * @subpackage	tx_icsodcoreapi
 */
class tx_icsodcoreapi_parameter{

	private $name = '', $type = '', $description = '', $mandatory = false, $default = '';
	private $values = array();

	/**
	 * Loads a document xml
	 *
	 * @param	XMLReader		$XMLReader $xmlreader: ...
	 * @return	void
	 */
	function loadXML(XMLReader $xmlreader){
		// Check the node name.
		if ($xmlreader->name != 'parameter')
			throw new Exception('parameter element expected. ' . $xmlreader->name . ' found.');
		// Load attributes.
		$this->setName( $xmlreader->getAttribute('name') );
		$this->setType( $xmlreader->getAttribute('type') );
		$this->setMandatory($xmlreader->getAttribute('mandatory')==1);
		$this->setDefault( $xmlreader->getAttribute('default') );
		if ($xmlreader->isEmptyElement)
			return;
		if (!$xmlreader->read())
			throw new Exception('Unable to read the parameter node sub elements.');
		while( $xmlreader->nodeType != XMLReader::END_ELEMENT ){
			if( $xmlreader->nodeType == XMLReader::ELEMENT ){
				switch( $xmlreader->name ){
					case 'description':
						// Load description
						if (!$xmlreader->isEmptyElement){
							$this->setDescription($xmlreader->readString());
							while (($xmlreader->nodeType != XMLReader::END_ELEMENT) || ($xmlreader->name != 'description'))
								if (!$xmlreader->read())
									throw new Exception('Unable to read the parameter description node sub elements.');
						}
						break;
					case 'values':
						// Load values
						if ($xmlreader->isEmptyElement)
							break;
						if (!$xmlreader->read())
							throw new Exception('Unable to read the parameter values node sub elements.');
						while( $xmlreader->nodeType != XMLReader::END_ELEMENT ){
							if( $xmlreader->nodeType == XMLReader::ELEMENT ){
								$value = new tx_icsodcoreapi_value();
								$value->loadXML($xmlreader);
								$this->addValue($value);
							}
							if (!$xmlreader->read())
								throw new Exception('Unable to read the parameter values node sub elements.');
						}
						break;
					default:
						throw new Exception('description or values expected. ' . $xmlreader->name . ' found.');
				}
			}
			if (!$xmlreader->read())
				throw new Exception('Unable to read the parameter node sub elements.');
		}
	}

	/**
	 * Put parameter in a document xml
	 *
	 * @param	$xmlwriter		XMLWriter
	 * @return	void
	 */
	function saveXML(XMLWriter $xmlwriter){
		$xmlwriter->startElement('parameter');
		$xmlwriter->writeAttribute( 'name' , $this->getName() );
		$xmlwriter->writeAttribute( 'type' , $this->getType() );
		$xmlwriter->writeAttribute( 'mandatory' , $this->getMandatory()? 1: 0 );
		if( $this->getDefault() != '' )
			$xmlwriter->writeAttribute( 'default' , $this->getDefault() );
		$xmlwriter->startElement('description');
		$xmlwriter->text( $this->getDescription() );
		$xmlwriter->endElement();
		if( $this->getValuesCount() > 0 ){
			$xmlwriter->startElement('values');
			foreach( $this->values as $value ){
				$value->saveXML($xmlwriter);
			}
			$xmlwriter->endElement();
		}
		$xmlwriter->endElement();
	}

	/**
	 * Loads _POST variables
	 *
	 * @param	array		$post
	 * @return	void
	 */
	function loadPOST($post){
		// Load parameter sub element
		foreach( $post as $postvar=>$value){
			switch( $postvar ){
				case 'name':
					$this->setName($value);
					break;
				case 'type':
					$this->setType($value);
					break;
				case 'mandatory':
					$this->setMandatory(!empty($value));
					break;
				case 'default':
					$this->setDefault($value);
					break;
				case 'description':
					$this->setDescription($value);
					break;
				case 'values':
					// Load values
					foreach( $value as $post_values){
						$value = new tx_icsodcoreapi_value();
						$value->loadPOST($post_values);
						$this->addValue($value);
					}
					break;
				default:
					throw new Exception('name, type, mandatory, default, description or values element expected. ' . $postvar . ' found.');
			}
		}
	}

	/**
	 * Retrieves parameter's name
	 *
	 * @return	string
	 */
	function getName(){
		return $this->name;
	}

	/**
	 * Defines parameter's name
	 *
	 * @param	string		$name
	 * @return	void
	 */
	function setName($name){
		$this->name = $name;
	}

	/**
	 * Retrieves parameter's type
	 *
	 * @return	string
	 */
	function getType(){
		return $this->type;
	}

	/**
	 * Defines parameter's type
	 *
	 * @param	string		$type
	 * @return	void
	 */
	function setType($type){
		if (!in_array($type, array('enum', 'string', 'number')))
			throw new Exception('Invalid type. "enum", "string" or "number" expected. "' . $type . '" found.');
		$this->type = $type;
	}

	/**
	 * Retrieves parameter's description
	 *
	 * @return	string
	 */
	function getDescription(){
		return $this->description;
	}

	/**
	 * Defines parameter's description
	 *
	 * @param	string		$description
	 * @return	void
	 */
	function setDescription($description){
		$this->description = $description;
	}

	/**
	 * Retrieves mandatory
	 *
	 * @return	boolean
	 */
	function getMandatory(){
		return $this->mandatory;
	}

	/**
	 * Defines mandatory
	 *
	 * @param	boolean		$mandatory
	 * @return	void
	 */
	function setMandatory($mandatory){
		$this->mandatory = $mandatory;
	}

	/**
	 * Retrieves default value
	 *
	 * @return	string
	 */
	function getDefault(){
		return $this->default;
	}

	/**
	 * Defines default value
	 *
	 * @param	string		$default
	 * @return	void
	 */
	function setDefault($default){
		$this->default = $default;
	}

	/**
	 * Count parameter values
	 *
	 * @return	integer		The number values
	 */
	function getValuesCount(){
		return count($this->values);
	}

	/**
	 * Retrieves value
	 *
	 * @param	integer		$i: Indice of value
	 * @return	value
	 */
	function getValue($i){
		return $this->values[$i];
	}

	/**
	 * Delete value
	 *
	 * @param	object		$obj: value
	 * @return	void
	 */
	function removeValue($obj){
		$keys = array_keys($this->values, $obj, true);
		rsort($keys);
		foreach ($keys as $key)
			array_splice($this->values, $key, 1);
	}

	/**
	 * Insert value in values
	 *
	 * @param	object		$obj: value
	 * @return	void
	 */
	function addValue($obj){
		$this->values[] = $obj;
	}
}

?>