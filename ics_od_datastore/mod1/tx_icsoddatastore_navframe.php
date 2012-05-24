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
 * $Id: tx_icsoddatastore_navframe.php 48432 2011-06-07 09:27:20Z emilieprudhomme $
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   64: class  tx_icsoddatastore_navframe
 *   76:     function init()
 *   99:     function jumpTo(params,linkObj,highLightID)
 *  115:     function refresh_nav()
 *  124:     function _refresh_nav()
 *  155:     function main()
 *  204:     function getFilegroups($pid)
 *  220:     function getFilegroupsElements($pid)
 *  243:     function printContent()
 *
 * TOTAL FUNCTIONS: 8
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
/*
 * Remarque : certaines parties du code proviennent du DAM. Notemment la fonction init(), main() et printContent() avec quelques modifications.
 * Le norme de codage n'est donc pas consistante pour indiquer les parties rajoutées.
 */

unset($MCONF);
include ('conf.php');
include ($BACK_PATH.'init.php');
include ($BACK_PATH.'template.php');
$LANG->includeLLFile('EXT:ics_od_datastore/mod1/locallang.xml');

/**
 * Module 'Documents' for the 'ics_od_datastore' extension.
 *
 * @author	DSIT Ville de Rennes <technique@in-cite.net>
 * @package	TYPO3
 * @subpackage	tx_icsoddatastore
 */
class  tx_icsoddatastore_navframe {

	var $doc;
	var $content;

		// Internal, static: _GP
	var $currentSubScript;

	var $mainModule = 'web';


	/**
	 * Construtor
	 *
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TYPO3_CONF_VARS;

		$this->include_once[] = t3lib_extMgm::extPath('ics_od_datastore') . 'lib/class.tx_icsoddatastore_sysfolder.php';

		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->backPath = $BACK_PATH;
		$this->doc->setModuleTemplate(t3lib_extMgm::extRelPath('ics_od_datastore') . 'mod1/mod_navframe.html');
		//$this->doc->styleSheetFile2 = t3lib_extMgm::extRelPath('dam') . 'res/css/stylesheet.css';
		$this->doc->docType  = 'xhtml_trans';


		$this->currentSubScript = t3lib_div::_GP('currentSubScript');

			// Setting highlight mode:
		$this->doHighlight = !$BE_USER->getTSConfigVal('options.pageTree.disableTitleHighlight');

		$this->doc->JScode='';

			// Setting JavaScript for menu.
		$this->doc->JScode=$this->doc->wrapScriptTags(
			($this->currentSubScript?'top.currentSubScript=unescape("'.rawurlencode($this->currentSubScript).'");':'').'

			function jumpTo(params,linkObj,highLightID)	{
				var theUrl = top.TS.PATH_typo3+top.currentSubScript+((top.currentSubScript.indexOf(\'?\') < 0) ? ("?") : ("&"))+params;

				if (top.condensedMode)	{
					top.content.document.location.href=theUrl;
				} else {
					parent.list_frame.document.location.href=theUrl;
				}
				'.($this->doHighlight?'hilight_row("row"+top.fsMod.recentIds["txdamM1"],highLightID);':'').'
				'.(!$GLOBALS['CLIENT']['FORMSTYLE'] ? '' : 'if (linkObj) {linkObj.blur();}').'
				return false;
			}


				// Call this function, refresh_nav(), from another script in the backend if you want to refresh the navigation frame (eg. after having changed a page title or moved pages etc.)
				// See t3lib_BEfunc::getSetUpdateSignal()
			function refresh_nav()	{
				window.setTimeout("_refresh_nav();",0);
			}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
			function _refresh_nav()	{
				document.location.href="'.htmlspecialchars(t3lib_div::linkThisScript(array('unique' => time()))).'";
			}

				// Highlighting rows in the page tree:
			function hilight_row(frameSetModule,highLightID) {	//

					// Remove old:
				theObj = document.getElementById(top.fsMod.navFrameHighlightedID[frameSetModule]);
				if (theObj)	{
					theObj.style.backgroundColor="";
				}

					// Set new:
				top.fsMod.navFrameHighlightedID[frameSetModule] = highLightID;
				theObj = document.getElementById(highLightID);
				if (theObj)	{
					theObj.style.backgroundColor="'.t3lib_div::modifyHTMLColorAll($this->doc->bgColor,-20).'";
				}
			}
		');
	}




	/**
	 * Main function, rendering the browsable page tree
	 *
	 * @return	void
	 */
	function main()	{
		global $LANG, $TYPO3_CONF_VARS;

		if (!$GLOBALS['BE_USER']->check('tables_select', 'tx_icsoddatastore_filegroups'))
		{
			$oMessage = t3lib_div::makeInstance(
				't3lib_FlashMessage',
				$LANG->getLL('error_noselect'),
				'',
				t3lib_FlashMessage::ERROR
			);
			t3lib_FlashMessageQueue::addMessage($oMessage);
			return;
		}

		$pid = tx_icsoddatastore_sysfolder::getPid();
		if (is_null($pid))
		{
			$oMessage = t3lib_div::makeInstance(
				't3lib_FlashMessage',
				$LANG->getLL('error_nosysfolder'),
				'',
				t3lib_FlashMessage::ERROR
			);
			t3lib_FlashMessageQueue::addMessage($oMessage);
			return;
		}
		$this->doc->getContextMenuCode();
		$this->content = '';
		$this->content = $this->getFilegroups($pid);

		$this->markers['REFRESH'] = '<a href="'.htmlspecialchars(t3lib_div::linkThisScript(array('unique' => uniqid('tx_web_navframe')))).'">'.
				'<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/refresh_n.gif','width="14" height="14"').' title="'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.refresh',1).'" alt="" /></a>';

		$sOnclickNewFilegroup = 'top.content.list_frame.location.href=top.TS.PATH_typo3+\'alt_doc.php?edit[tx_icsoddatastore_filegroups][' . $pid . ']=new&returnUrl=\' + encodeURIComponent(top.content.list_frame.location.href);';
		$this->markers['NEW_PAGE'] = '<a href="#" onclick="' . htmlspecialchars($sOnclickNewFilegroup) . '"><img' . t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/new_el.gif') . ' title="' . $LANG->sL('LLL:EXT:ics_od_datastore/mod1/locallang.xml:new_filegroup', 1) . '" alt="" /></a>';

			// Adding highlight - JavaScript
		if ($this->doHighlight)	$this->content .= $this->doc->wrapScriptTags('
			hilight_row("",top.fsMod.navFrameHighlightedID["web"]);
		');
	}

	/**
	 * render Filegroups
	 *
	 * @param	int		$pid: storage pid
	 * @return	string		content
	 */
	function getFilegroups($pid)
	{
		return '
			<div id="PageTreeDiv">
				<!-- TYPO3 tree structure. -->
				<ul class="tree" id="treeRoot">' . $this->getFilegroupsElements($pid) . '
				</ul>
			</div>';
	}

	/**
	 * render Filegroups Elements
	 *
	 * @param	int		$pid: storage pid
	 * @return	string		content
	 */
	function getFilegroupsElements($pid)
	{
		global $TYPO3_DB;
		$sTable = 'tx_icsoddatastore_filegroups';
		$res = $TYPO3_DB->exec_SELECTquery('*', $sTable, '1' . t3lib_BEfunc::deleteClause($sTable) . ' AND pid = ' . $TYPO3_DB->fullQuoteStr($pid, $sTable), '', 'title');
		$aList = array();
		while ($aRow = $TYPO3_DB->sql_fetch_assoc($res))
		{
			$sAltText = t3lib_BEfunc::getRecordIconAltText($aRow, $sTable);
			$sIconImg = t3lib_iconWorks::getIconImage($sTable, $aRow, $this->doc->backPath, 'title="'.htmlspecialchars($sAltText).'"');
			$sTheIcon = $this->doc->wrapClickMenuOnIcon($sIconImg,$sTable,$aRow['uid']);
			$sRecTitle = t3lib_BEfunc::getRecordTitle($sTable, $aRow, FALSE, TRUE);
			$sRecLink = '<a href="#" onclick="jumpTo(\'id=' . $aRow['uid'] . '\', this);">' . htmlspecialchars($sRecTitle) . '</a>';
			$aList[] = chr(10) . '<li id="' . $sTable . '_' . $aRow['uid'] . '_' . $aRow['pid'] . '">' . $sTheIcon . $sRecLink . '</li>';
		}
		return implode('', $aList);
	}

	/**
	 * Outputting the accumulated content to screen
	 *
	 * @return	void
	 */
	function printContent()	{
		global $LANG;
		// Null out markers:
		$docHeaderButtons = array(
			'new_page' => '',
			'csh' => '',
			'refresh' => '',
		);
		$this->markers['FILEGROUPINFO'] = '';

		$this->markers['CONTENT'] = $this->content;
		$subparts['###SECOND_ROW###'] = '';
		$docHeaderButtons['refresh'] = $this->markers['REFRESH'];
		$docHeaderButtons['new_page'] = $this->markers['NEW_PAGE'];

		$this->content = $this->doc->startPage($LANG->sL('LLL:EXT:ics_od_datastore/mod1/locallang_mod.xml:mlang_labels_tablabel',1));
		$this->content.= $this->doc->moduleBody($this->pageinfo, $docHeaderButtons, $this->markers, $subparts);
		$this->content.= $this->doc->endPage();

		$this->content = $this->doc->insertStylesAndJS($this->content);
		echo $this->content;
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_od_datastore/mod1/tx_icsoddatastore_navframe.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_od_datastore/mod1/tx_icsoddatastore_navframe.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_icsoddatastore_navframe');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>