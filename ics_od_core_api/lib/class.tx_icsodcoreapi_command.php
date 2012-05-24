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
 * $Id: class.tx_icsodcoreapi_command.php 48432 2011-06-07 09:27:20Z emilieprudhomme $
 */

require_once( t3lib_extMgm::extPath('ics_od_core_api') . 'lib/class.tx_icsodcoreapi_parameter.php' );

/**
 * Objet 'Command' for the 'ics_od_core_api' extension.
 *
 * @author	Tsi <tsi@in-cite.net>
 * @package	TYPO3
 * @subpackage	tx_icsodcoreapi
 */
class tx_icsodcoreapi_command{

	private $name = '', $cmd  = '', $description  = '', $brief = '';
	private $parameters = array();

	/**
	 * Loads a document xml
	 *
	 * @param	XMLReader		$XMLReader
	 * @return	void
	 */
	function loadXML(XMLReader $xmlreader) {
		// Check the node name.
		if ($xmlreader->name != 'command')
			throw new Exception('command element expected. ' . $xmlreader->name . ' found.');
		// Load attributes.
		$this->setName($xmlreader->getAttribute('name'));
		$this->setCmd($xmlreader->getAttribute('cmd'));
		$this->setBrief($xmlreader->getAttribute('brief'));
		if ($xmlreader->isEmptyElement)
			return;
		if (!$xmlreader->read())
			throw new Exception('Unable to read the command node sub elements.');
		while($xmlreader->nodeType != XMLReader::END_ELEMENT){
			if( $xmlreader->nodeType == XMLReader::ELEMENT ){
				switch( $xmlreader->name ){
					case 'description':
						// Load description
						if (!$xmlreader->isEmptyElement){
							$this->setDescription($xmlreader->readString());
							while( ($xmlreader->nodeType != XMLReader::END_ELEMENT) || ($xmlreader->name != 'description') )
								if (!$xmlreader->read())
									throw new Exception('Unable to read the command description node sub elements.');
						}
						break;
					case 'parameters':
						// Load parameters
						if ($xmlreader->isEmptyElement)
							break;
						if (!$xmlreader->read())
							throw new Exception('Unable to read the command node sub elements.');
						while( $xmlreader->nodeType != XMLReader::END_ELEMENT ){
							if( $xmlreader->nodeType == XMLReader::ELEMENT ){
								$parameter = new tx_icsodcoreapi_parameter();
								$parameter->loadXML($xmlreader);
								$this->addParameter($parameter);
							}
							if (!$xmlreader->read())
								throw new Exception('Unable to read the command parameters node sub elements.');
						}
						break;
					default:
						throw new Exception('description or parameters expected. ' . $xmlreader->name . ' found.');
				}
			}
			if (!$xmlreader->read())
				throw new Exception('Unable to read the command node sub elements.');
		}
	}

	/**
	 * Put command in a document xml
	 *
	 * @param	$xmlwriter		XMLWriter
	 * @return	void
	 */
	function saveXML(XMLWriter $xmlwriter){
		$xmlwriter->startElement('command');
		$xmlwriter->writeAttribute( 'name' , $this->getName() );
		$xmlwriter->writeAttribute( 'cmd' , $this->getCmd() );
		if( $this->getBrief() != '' )
			$xmlwriter->writeAttribute( 'brief' , $this->getBrief() );
		$xmlwriter->startElement('description');
		$xmlwriter->text( $this->getDescription() );
		$xmlwriter->endElement();
		if( $this->getParametersCount() > 0 ){
			$xmlwriter->startElement('parameters');
			foreach( $this->parameters as $parameter ){
				$parameter->saveXML($xmlwriter);
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
		foreach( $post as $postvar=>$value ){
			switch( $postvar ){
				case 'name':
					$this->setName($value);
					break;
				case 'cmd':
					$this->setCmd($value);
					break;
				case 'brief':
					$this->setBrief($value);
					break;
				case 'description':
					$this->setDescription($value);
					break;
				case 'parameters':
					// Load parameters
					foreach( $value as $post_parameter){
						$parameter = new tx_icsodcoreapi_parameter();
						$parameter->loadPOST($post_parameter);
						$this->addParameter($parameter);
					}
					break;
				default:
					throw new Exception('name, cmd, brief, description or parameters element expected. ' . $postvar . ' found.');
			}
		}
	}

	/**
	 * Retrieves command's name
	 *
	 * @return	string
	 */
	function getName(){
		return $this->name;
	}

	/**
	 * Defines command's name
	 *
	 * @param	string		$name
	 * @return	void
	 */
	function setName($name){
		$this->name = $name;
	}

	/**
	 * Retrieves command's key
	 *
	 * @return	string
	 */
	function getCmd(){
		return $this->cmd;
	}

	/**
	 * Defines command's key
	 *
	 * @param	string		$cmd: The key of the command
	 * @return	void
	 */
	function setCmd($cmd){
		$this->cmd = $cmd;
	}

	/**
	 * Retrieves command's brief
	 *
	 * @return	string
	 */
	function getBrief(){
		return $this->brief;
	}

	/**
	 * Defines command's brief
	 *
	 * @param	string		$brief
	 * @return	void
	 */
	function setBrief($brief){
		$this->brief = $brief;
	}

	/**
	 * Retrieves command's description
	 *
	 * @return	string
	 */
	function getDescription(){
		return $this->description;
	}

	/**
	 * Defines command's description
	 *
	 * @param	string		$description
	 * @return	void
	 */
	function setDescription($description){
		$this->description = $description;
	}

	/**
	 * Count command parameters
	 *
	 * @return	integer		The number of elements in parameters
	 */
	function getParametersCount(){
		return count($this->parameters);
	}

	/**
	 * Retrieves parameter
	 *
	 * @param	integer		$i: The indice of parameter
	 * @return	parameter
	 */
	function getParameter($i){
		return $this->parameters[$i];
	}

	/**
	 * Delete parameter
	 *
	 * @param	object		$obj: parameter
	 * @return	void
	 */
	function removeParameter($obj){
		$keys = array_keys($this->parameters, $obj, true);
		rsort($keys);
		foreach ($keys as $key)
			array_splice($this->parameters, $key, 1);
	}

	/**
	 * Insert parameter in parameters
	 *
	 * @param	object		$obj: parameter
	 * @return	void
	 */
	function addParameter($obj){
		$this->parameters[] = $obj;
	}
}