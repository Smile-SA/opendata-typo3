<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 In Cité Solution <technique@in-cite.net>
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
 * $Id: index.php 48432 2011-06-07 09:27:20Z emilieprudhomme $
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   65: class  tx_icsodcoreapi_module1 extends t3lib_SCbase
 *   73:     function init()
 *   84:     function menuConfig()
 *   94:     function main()
 *  115:     function jumpToUrl(URL)
 *  160:     function printContent()
 *  170:     function moduleContent()
 *  293:     function deleteElement( &$element, $delete)
 *  332:     protected function getButtons()
 *
 * TOTAL FUNCTIONS: 8
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
$LANG->includeLLFile('EXT:ics_od_core_api/mod1/locallang.xml');
require_once(PATH_t3lib . 'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]

require_once( t3lib_extMgm::extPath('ics_od_core_api') . 'lib/class.tx_icsodcoreapi_command.php' );
require_once( t3lib_extMgm::extPath('ics_od_core_api') . 'lib/class.tx_icsodcoreapi_parameter.php' );
require_once( t3lib_extMgm::extPath('ics_od_core_api') . 'lib/class.tx_icsodcoreapi_value.php' );
require_once( t3lib_extMgm::extPath('ics_od_core_api') . 'mod1/class.tx_icsodcoreapi_module1_menu.php' );
require_once( t3lib_extMgm::extPath('ics_od_core_api') . 'mod1/class.tx_icsodcoreapi_module1_command.php' );


/**
 * Module 'Commands documentation' for the 'ics_od_core_api' extension.
 *
 * @author	Tsi <tsi@in-cite.net>
 * @package	TYPO3
 * @subpackage	tx_icsodcoreapi
 */
class  tx_icsodcoreapi_module1 extends t3lib_SCbase {
	var $pageinfo;

	/**
	 * Initializes the Module
	 *
	 * @return	void
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		parent::init();
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	void
	 */
	function menuConfig()	{
		parent::menuConfig();
	}

	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 * @return	void
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		// Initialize doc
		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->setModuleTemplate(t3lib_extMgm::extPath('ics_od_core_api') . 'mod1/mod_template.html');
		$this->doc->backPath = $BACK_PATH;

		if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{
				// Draw the header.
			$this->doc->form='<form name="tx_icsodcoreapi_module1" id="tx_icsodcoreapi_module1" action="" method="post" enctype="multipart/form-data">';

				// JavaScript
			$this->doc->JScode = '
				<script language="javascript" type="text/javascript">
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}
				</script>
			';
			$this->doc->postCode='
				<script language="javascript" type="text/javascript">
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = 0;
				</script>
			';

			// $this->doc->JScode .= '<script language="javascript" type="text/javascript" src="script.js"></script>' . chr(10);
			// $this->doc->inDocStyles = '@import url(styles.css)' . chr(10);

			$this->doc->JScode .= '
			<script language="javascript" type="text/javascript">'
			 . file_get_contents( t3lib_extMgm::extPath('ics_od_core_api') . '/mod1/script.js')
			. '</script>';
			$this->doc->inDocStyles = file_get_contents( t3lib_extMgm::extPath('ics_od_core_api') . '/mod1/styles.css');

			// Render content:
			$this->moduleContent();
		} else {
				// If no access or if ID == zero
			$this->content.=$this->doc->spacer(10);
		}
		$docHeaderButtons = $this->getButtons();

			// compile document
		$markers['CONTENT'] = $this->content;

				// Build the <body> for the module
		$this->content = $this->doc->startPage($LANG->getLL('title'));
		$this->content.= $this->doc->moduleBody($this->pageinfo, $docHeaderButtons, $markers);
		$this->content.= $this->doc->endPage();
		$this->content = $this->doc->insertStylesAndJS($this->content);

	}

	/**
	 * Prints out the module HTML
	 *
	 * @return	void
	 */
	function printContent()	{
		$this->content.=$this->doc->endPage();
		echo $this->content;
	}

	/**
	 * Generates the module content
	 *
	 * @return	void
	 */
	function moduleContent(){
		global $LANG;

		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ics_od_core_api']);
		if ($extConf && $extConf['xmldoc.'])	{
			$xmldoc_path = $extConf['xmldoc.']['path'];
			if (strrpos($xmldoc_path, '/') == (strlen($xmldoc_path)-1))	{
				$xmldoc_path .= $extConf['xmldoc.']['version'];
			}	else	{
				$xmldoc_path .= '/' . $extConf['xmldoc.']['version'];
			}
			$xmldoc_path = (strrpos($xmldoc_path, '/') == (strlen($xmldoc_path)-1))? $xmldoc_path : $xmldoc_path . '/';
			$xmldoc_path = PATH_site . $xmldoc_path;

			$POST = t3lib_div::_POST();
			$command = new tx_icsodcoreapi_command();

			if( isset($POST['loadCommand']) ){
				$xml = file_get_contents($POST['url']);
				$output = $xml;
				if(is_bool($xml)){
					$this->content .= '<p style="color: #ff0000">' . htmlspecialchars($LANG->getLL('erreur_url')) . '</p>';
				}else{
					$reader = new XMLReader();
					$reader->XML($xml);
					do{
						$reader->read();
					}while ($reader->nodeType != XMLReader::ELEMENT);
					try{
						$command->loadXML($reader);
					}catch (Exception $e){
						var_dump($e);
					}
				}
			}
			if( isset($POST['hidden'])
				|| isset($POST['newCommand'])
				|| ( isset($POST['loadCommand']) && !is_bool($xml) ) ){

				if( isset($POST['command']) ){
					try{
						$command->loadPOST($POST['command']);
					}catch(Exception $e){
						var_dump($e);
					}
				}
				$current = 'home';
				$delete = '';
				if( isset($POST['hidden']) ){
					$current = $POST['hidden']['current'];
					$delete = $POST['hidden']['delete'];
				}
				if( $current == $delete )
					$current = '';
				if( $delete != '' ){
					$this->deleteElement( $command, explode( '|', $delete ) );
				}
				if( isset($POST['save']) ){
					$xmlwriter = new XMLWriter();
					$xmlwriter->openMemory();
					$xmlwriter->startDocument('1.0', 'utf-8', 'yes');
					$command->saveXML($xmlwriter);
					$xmlwriter->endDocument();
					$output = $xmlwriter->outputMemory();
					file_put_contents( $xmldoc_path . $command->getCmd() . '.xml', $output);
				}

				// Generate the menu
				$this->content .= '<div class="menu">';
				$this->content .= '<fieldset title="' . htmlspecialchars($LANG->getLL('menu_title')) . '">';
				$this->content .= '<legend>' . htmlspecialchars($LANG->getLL('menu_title')) . '</legend>';
				$menu = new tx_icsodcoreapi_module1_menu();
				$menu->load($command);
				$this->content .= $menu->getContent();
				$this->content .= '<div class="button">';
				$this->content .= '<input type="submit" name="update" value="' . htmlspecialchars($LANG->getLL('btn_update')) . '" />';
				$this->content .= '<input type="submit" name="save" value="' . htmlspecialchars($LANG->getLL('btn_save')) . '" />';
				$this->content .= '</div>';
				$this->content .= '<p><input type="hidden" name="hidden[current]" value="' . $current . '" id="current"/></p>';
				$this->content .= '<p><input type="hidden" name="hidden[delete]" value="" id="delete" /></p>';
				$this->content .= '</fieldset>';
				$this->content .= '</div>';
				// Generate the form
				$this->content .= '<div class="content" id="content">';
				if( $current == 'home'){
					$this->content .= 'Choose in the menu the action to do.';
				}
				$cmd = new tx_icsodcoreapi_module1_command();
				$cmd->load($command, explode('|', $current), $current);
				$this->content .= $cmd->getContent();
				$this->content .= '</div>';
				$this->content .= '<div class="cmdxml"><p>' . $command->getCmd() . '.xml</p><p>'. htmlspecialchars($output) . '</p></div>';
			}else{
				$this->content .= '
					<div>
						<p>
							<label>' . htmlspecialchars($LANG->getLL('url')) . '</label>
							<input type="text" name="url" value="' . htmlspecialchars($LANG->getLL('enter_url')) . '" id="url" onclick="select();" size="100"/>
							<input type="submit" name="loadCommand" value="' . htmlspecialchars($LANG->getLL('load_command')) . '" />
						</p>
						<p>
							<input type="submit" name="newCommand" value="' . htmlspecialchars($LANG->getLL('new_command')) . '" />
						</p>
					</div>
				';
			}
		}	else	{
			$this->content .= '<p style="color: F00">' . htmlspecialchars($LANG->getLL('xmldoc_error')) . '</p>';
		}


		//$this->content .= '<div style="clear: both;">POST:'  . t3lib_div::view_array($_POST) . '</div>';

	}


	/**
	 * Deletes command elements
	 *
	 * @param	object		&$element: element command, parameter or value
	 * @param	array		$delete
	 * @return	void
	 */
	function deleteElement( &$element, $delete){
		if( $delete[0] == 'command' ){
			array_shift($delete);
			$this->deleteElement( $element, $delete);
		}elseif( $delete[0] == 'parameters' ){
			array_shift($delete);
			$this->deleteElement( $element, $delete);
		}elseif( $delete[0] == 'values' ){
			array_shift($delete);
			$this->deleteElement( $element, $delete);
		}elseif( is_numeric($delete[0]) ){
			if( (count($delete) == 1) ){
				$index = intval($delete[0]);
				if( get_class($element) == 'tx_icsodcoreapi_parameter' ){
					$value = $element->getValue($index);
					$element->removeValue($value);
				}else{
					$parameter = $element->getParameter($index);
					$element->removeParameter($parameter);
				}
			}else{
				if( get_class( $element) == 'tx_icsodcoreapi_parameter' ){
					$underElement = $element->getValue( $delete[0] );
				}else{
					$underElement = $element->getParameter( $delete[0] );
				}
				array_shift($delete);
				$this->deleteElement( $underElement, $delete);
			}
		}else{
		}
	}


	/**
	 * Create the panel of buttons for submitting the form or otherwise perform operations.
	 *
	 * @return	array		all available buttons as an assoc. array
	 */
	protected function getButtons()	{
		global $TCA, $LANG, $BACK_PATH, $BE_USER;

		$buttons = array(
			'back' => '',
			'close' => '',
			'save' => '',
			'view' => '',
			'record_list' => '',
			'shortcut' => '',
		);

		if ($this->id && $this->access)	{
				// View page
			/*$buttons['view'] = '<a href="#" onclick="' . htmlspecialchars(t3lib_BEfunc::viewOnClick($this->pageinfo['uid'], $BACK_PATH, t3lib_BEfunc::BEgetRootLine($this->pageinfo['uid']))) . '">' .
					'<img' . t3lib_iconWorks::skinImg($BACK_PATH, 'gfx/zoom.gif') . ' title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.showPage', 1) . '" hspace="3" alt="" />' .
					'</a>';*/

				// If access to Web>List for user, then link to that module.
			/*if ($BE_USER->check('modules','web_list'))	{
				$href = $BACK_PATH . 'db_list.php?id=' . $this->pageinfo['uid'] . '&returnUrl=' . rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI'));
				$buttons['record_list'] = '<a href="' . htmlspecialchars($href) . '">' .
						'<img' . t3lib_iconWorks::skinImg($BACK_PATH, 'gfx/list.gif') . ' title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.showList', 1) . '" alt="" />' .
						'</a>';
			}*/

			/*if($this->extClassConf['name'] == 'tx_tstemplateinfo') {
				if(!empty($this->e) && !t3lib_div::_POST('abort')) {
						// SAVE button
					$buttons['save'] = '<input type="image" class="c-inputButton" name="submit" value="Update"' . t3lib_iconWorks::skinImg($BACK_PATH, 'gfx/savedok.gif','') . ' title="' . $LANG->sL('LLL:EXT:lang/locallang_core.php:rm.saveDoc', 1) . '" />';
						// CLOSE button
					$buttons['close'] = '<input type="image" class="c-inputButton" name="abort" value="Abort"' . t3lib_iconWorks::skinImg($BACK_PATH, 'gfx/closedok.gif','') . ' title="' . $LANG->sL('LLL:EXT:lang/locallang_core.php:rm.closeDoc', 1) . '" />';
				}
			} elseif($this->extClassConf['name'] == 'tx_tstemplateceditor' && count($this->MOD_MENU["constant_editor_cat"])) {
					// SAVE button
				$buttons['save'] = '<input type="image" class="c-inputButton" name="submit" value="Update"' . t3lib_iconWorks::skinImg($BACK_PATH, 'gfx/savedok.gif','') . ' title="' . $LANG->sL('LLL:EXT:lang/locallang_core.php:rm.saveDoc', 1) . '" />';
			} elseif($this->extClassConf['name'] == 'tx_tstemplateobjbrowser') {
				if(!empty($this->sObj)) {
						// BACK
					$buttons['back'] = '<a href="index.php?id=' . $this->id . '" class="typo3-goBack">' .
									'<img' . t3lib_iconWorks::skinImg($BACK_PATH, 'gfx/goback.gif') . ' title="' . $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.goBack', 1) . '" alt="" />' .
									'</a>';
				}
			}*/
			/*if ($this->MOD_SETTINGS['groupsOnPage'])
				$buttons['save'] = '<input type="image" class="c-inputButton" name="submit" value="Update"' . t3lib_iconWorks::skinImg($BACK_PATH, 'gfx/savedok.gif','') . ' title="' . $LANG->sL('LLL:EXT:lang/locallang_core.php:rm.saveDoc', 1) . '" />';
			*/
				// Shortcut
			if ($BE_USER->mayMakeShortcut())	{
				$buttons['shortcut'] = $this->doc->makeShortcutIcon('id, edit_record, pointer, new_unique_uid, search_field, search_levels, showLimit', implode(',', array_keys($this->MOD_MENU)), $this->MCONF['name']);
			}
		} else {
				// Shortcut
			if ($BE_USER->mayMakeShortcut())	{
				$buttons['shortcut'] = $this->doc->makeShortcutIcon('id', '', $this->MCONF['name']);
			}
		}

		return $buttons;
		/*
		$buttons = array(
			'csh' => '',
			'shortcut' => '',
			'save' => ''
		);
			// CSH
		$buttons['csh'] = t3lib_BEfunc::cshItem('_MOD_web_func', '', $GLOBALS['BACK_PATH']);

			// SAVE button
		$buttons['save'] = '<input type="image" class="c-inputButton" name="submit" value="Update"' . t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/savedok.gif', '') . ' title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:rm.saveDoc', 1) . '" />';


			// Shortcut
		if ($GLOBALS['BE_USER']->mayMakeShortcut())	{
			$buttons['shortcut'] = $this->doc->makeShortcutIcon('', 'function', $this->MCONF['name']);
		}

		return $buttons;
		*/
	}

}




if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_od_core_api/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_od_core_api/mod1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_icsodcoreapi_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();
?>