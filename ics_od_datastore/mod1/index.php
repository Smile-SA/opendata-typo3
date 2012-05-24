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
 * $Id: index.php 48432 2011-06-07 09:27:20Z emilieprudhomme $
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   65: class tx_icsoddatastore_module1 extends t3lib_SCbase
 *   73:     function init()
 *   95:     function menuConfig()
 *  117:     function main()
 *  178:     function printContent()
 *  192:     function moduleContent()
 *  388:     function loadFilegroups()
 *  425:     function renderFilegroup()
 *  590:     function renderMenuButtons()
 *  610:     protected function getButtons()
 *  647:     public function getNewRecordParams()
 *  658:     public function getEditRecordParams($sId)
 *
 * TOTAL FUNCTIONS: 11
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(t3lib_extMgm::extPath('ics_od_datastore') . 'lib/class.tx_icsoddatastore_recordlist.php');
$LANG->includeLLFile('EXT:ics_od_datastore/mod1/locallang.xml');
require_once(PATH_t3lib . 'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]



/**
 * Module 'Documents' for the 'ics_od_datastore' extension.
 *
 * @author	DSIT Ville de Rennes <technique@in-cite.net>
 * @package	TYPO3
 * @subpackage	tx_icsoddatastore
 */
class tx_icsoddatastore_module1 extends t3lib_SCbase {
	var $pageinfo;

	/**
	 * Initializes the Module
	 *
	 * @return	void
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		parent::init();

		/*
		if (t3lib_div::_GP('clear_all_cache'))	{
			$this->include_once[] = PATH_t3lib.'class.t3lib_tcemain.php';
		}
		*/
		$this->include_once[] = t3lib_extMgm::extPath('ics_od_datastore') . 'lib/class.tx_icsoddatastore_sysfolder.php';
		$this->pointer = t3lib_div::_GP('pointer');
		$this->showLimit = t3lib_div::_GP('showLimit');
		$this->returnUrl = t3lib_div::_GP('returnUrl');
		$this->cmd = t3lib_div::_GP('cmd');
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	void
	 */
	function menuConfig()	{
		global $LANG;
			// MENU-ITEMS:
		$this->MOD_MENU = array(
			'bigControlPanel' => '',
			'clipBoard' => '',
			'localization' => '',
		);

			// Loading module configuration:
		$this->modTSconfig = t3lib_BEfunc::getModTSconfig($this->id,'mod.web_list');

			// Clean up settings:
		$this->MOD_SETTINGS = t3lib_BEfunc::getModuleData($this->MOD_MENU, t3lib_div::_GP('SET'), 'web_list');
	}

	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 * @return	[type]		...
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pid = tx_icsoddatastore_sysfolder::getPid();
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->pid,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

			// initialize doc
		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->setModuleTemplate(t3lib_extMgm::extPath('ics_od_datastore') . 'mod1/mod_template.html');
		$this->doc->backPath = $BACK_PATH;
		$docHeaderButtons = $this->getButtons();

		if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{

				// Draw the form
			$this->doc->form = '<form action="" method="post" enctype="multipart/form-data">';

			$this->doc->JScode = $this->doc->wrapScriptTags('
				function jumpToUrl(URL)	{	//
					window.location.href = URL;
					return false;
				}
			');

			$this->doc->postCode='
				<script language="javascript" type="text/javascript">
					script_ended = 1;
					//if (top.fsMod) top.fsMod.recentIds["web"] = 0;
				</script>
			';
				// Render content:
			$this->moduleContent();
		} else {
				// If no access or if ID == zero
			$docHeaderButtons['save'] = '';
			$this->content.=$this->doc->spacer(10);
		}

			// compile document
		$markers['FUNC_MENU'] = '';//t3lib_BEfunc::getFuncMenu(0, 'SET[function]', $this->MOD_SETTINGS['function'], $this->MOD_MENU['function']);
		$markers['CONTENT'] = $this->content;

		if ($this->aListHeaderButtons)
			$docHeaderButtons = array_merge($docHeaderButtons, $this->aListHeaderButtons);
		$markers['CSH'] = $docHeaderButtons['csh'];

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

		echo $this->content;
	}

// Note alt_doc:
// - overrideVals[table][field]: Forcer la valeur de field dans table à la valeur du paramètre.
// - defVals[table][field]: Pour les nouveaux enregistrement, initialiser le valeur de field dans table à la valeur du paramètre.
// - columnsOnly[table]: Liste des champs à afficher pour table. Les champs de overrideVals sont masqués.
	/**
	 * Generates the module content
	 *
	 * @return	void
	 */
	function moduleContent()	{
		global $TYPO3_DB, $LANG, $BACK_PATH, $BE_USER, $CLIENT;
		if (!$this->loadFilegroups())
			return;

		// Render filegroup metadata ====
		$sContent .= $this->renderFilegroup();

		// Render files buttons ======
		$sContent .= $this->renderMenuButtons();

			// Initialize the dblist object:
		$dblist = t3lib_div::makeInstance('tx_icsoddatastore_recordList');
		$dblist->backPath = $BACK_PATH;
		$dblist->calcPerms = $BE_USER->calcPerms($this->pageinfo);
		$dblist->thumbs = $BE_USER->uc['thumbnailsByDefault'];
		$dblist->returnUrl = '';
		$dblist->allFields = 1;
		$dblist->localizationView = 0;
		$dblist->showClipboard = 1;
		$dblist->disableSingleTableView = false;
		$dblist->listOnlyInSingleTableMode = true;
		$dblist->hideTables = '';
		$dblist->tableTSconfigOverTCA = $this->modTSconfig['properties']['table.'];
		$dblist->clickTitleMode = $this->modTSconfig['properties']['clickTitleMode'];
		$dblist->alternateBgColors=$this->modTSconfig['properties']['alternateBgColors']?1:0;
		$dblist->allowedNewTables = t3lib_div::trimExplode(',', $this->modTSconfig['properties']['allowedNewTables'], 1);
		$dblist->deniedNewTables = t3lib_div::trimExplode(',', $this->modTSconfig['properties']['deniedNewTables'], 1);
		$dblist->newWizards = 0;
		$dblist->pageRow = $this->pageinfo;
		$dblist->counter++;
		$dblist->MOD_MENU = array();
		$dblist->modTSconfig = $this->modTSconfig;
		$dblist->script = 'mod.php?M=' . $this->MCONF['name'];
		$this->cmd_table = 'tx_icsoddatastore_files';

			// Clipboard is initialized:
		$dblist->clipObj = t3lib_div::makeInstance('t3lib_clipboard');		// Start clipboard
		$dblist->clipObj->initializeClipboard();	// Initialize - reads the clipboard content from the user session

			// Clipboard actions are handled:
		$CB = t3lib_div::_GET('CB');	// CB is the clipboard command array
		if ($this->cmd=='setCB') {
				// CBH is all the fields selected for the clipboard, CBC is the checkbox fields which were checked. By merging we get a full array of checked/unchecked elements
				// This is set to the 'el' array of the CB after being parsed so only the table in question is registered.
			$CB['el'] = $dblist->clipObj->cleanUpCBC(array_merge((array)t3lib_div::_POST('CBH'),(array)t3lib_div::_POST('CBC')),$this->cmd_table);
		}
		if (!$this->MOD_SETTINGS['clipBoard'])	$CB['setP']='normal';	// If the clipboard is NOT shown, set the pad to 'normal'.
		$dblist->clipObj->setCmd($CB);		// Execute commands.
		$dblist->clipObj->cleanCurrent();	// Clean up pad
		$dblist->clipObj->endClipboard();	// Save the clipboard content

			// This flag will prevent the clipboard panel in being shown.
			// It is set, if the clickmenu-layer is active AND the extended view is not enabled.
		$dblist->dontShowClipControlPanels = $CLIENT['FORMSTYLE'] && !$this->MOD_SETTINGS['bigControlPanel'] && $dblist->clipObj->current=='normal' && !$BE_USER->uc['disableCMlayers'] && !$this->modTSconfig['properties']['showClipControlPanelsDespiteOfCMlayers'];

			// Deleting records...:
			// Has not to do with the clipboard but is simply the delete action. The clipboard object is used to clean up the submitted entries to only the selected table.
		if ($this->cmd=='delete')	{
			$items = $dblist->clipObj->cleanUpCBC(t3lib_div::_POST('CBC'),$this->cmd_table,1);
			if (count($items))	{
				$cmd=array();
				reset($items);
				while(list($iK)=each($items))	{
					$iKParts = explode('|',$iK);
					$cmd[$iKParts[0]][$iKParts[1]]['delete']=1;
				}
				$tce = t3lib_div::makeInstance('t3lib_TCEmain');
				$tce->stripslashes_values=0;
				$tce->start(array(),$cmd);
				$tce->process_cmdmap();

				if (isset($cmd['pages']))	{
					t3lib_BEfunc::setUpdateSignal('updatePageTree');
				}

				$tce->printLogErrorMessages(t3lib_div::getIndpEnv('REQUEST_URI'));
			}
		}

		$this->pointer = t3lib_div::intInRange($this->pointer,0,100000);
		$dblist->start($this->pid,'tx_icsoddatastore_files',$this->pointer,'',0,$this->showLimit);
		$dblist->setDispFields();

			// Render the list of tables:
		$dblist->generateList();

			// Write the bottom of the page:
		$dblist->writeBottom();

			// JavaScript
		$this->doc->JScode = $this->doc->wrapScriptTags('
			function jumpToUrl(URL)	{	//
				window.location.href = URL;
				return false;
			}
			function jumpExt(URL,anchor)	{	//
				var anc = anchor?anchor:"";
				window.location.href = URL+(T3_THIS_LOCATION?"&returnUrl="+T3_THIS_LOCATION:"")+anc;
				return false;
			}
			function jumpSelf(URL)	{	//
				window.location.href = URL+(T3_RETURN_URL?"&returnUrl="+T3_RETURN_URL:"");
				return false;
			}

			function setHighlight(id)	{	//
				top.fsMod.recentIds["web"]=id;
				top.fsMod.navFrameHighlightedID["web"]="pages"+id+"_"+top.fsMod.currentBank;	// For highlighting

				if (top.content && top.content.nav_frame && top.content.nav_frame.refresh_nav)	{
					top.content.nav_frame.refresh_nav();
				}
			}
			'.$this->doc->redirectUrls($dblist->listURL()).'
			function editRecords(table,idList,addParams,CBflag)	{	//
				window.location.href="'.$BACK_PATH.'alt_doc.php?returnUrl='.rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI')).
					'&edit["+table+"]["+idList+"]=edit"+addParams;
			}
			function editList(table,idList)	{	//
				var list="";

					// Checking how many is checked, how many is not
				var pointer=0;
				var pos = idList.indexOf(",");
				while (pos!=-1)	{
					if (cbValue(table+"|"+idList.substr(pointer,pos-pointer))) {
						list+=idList.substr(pointer,pos-pointer)+",";
					}
					pointer=pos+1;
					pos = idList.indexOf(",",pointer);
				}
				if (cbValue(table+"|"+idList.substr(pointer))) {
					list+=idList.substr(pointer)+",";
				}

				return list ? list : idList;
			}

			if (top.fsMod) top.fsMod.recentIds["web"] = '.intval($this->id).';
		');

			// Setting up the context sensitive menu:
		$this->doc->getContextMenuCode();

				// Draw the form
		//$this->doc->form = '<form action="' . htmlspecialchars($dblist->listURL()) . '" method="post" enctype="multipart/form-data">';
		$this->doc->form = '';
		//$sContent = $dblist->HTMLcode;
		//$sContent .= $dblist->HTMLcode;
		$sContent .= '
		<form action="' . htmlspecialchars($dblist->listURL()) . '" method="post" enctype="multipart/form-data" name="dblistForm">' . $dblist->HTMLcode . '
			<input type="hidden" name="cmd_table" />
			<input type="hidden" name="cmd" />
		</form>';

		if ($sContent) {
			// MODIFICATION Loïc le 25/11/2010. Suppression du choix des champs à afficher
			//$sContent .= $dblist->fieldSelectBox($dblist->table);

				// Adding checkbox options for extended listing and clipboard display:
			// $sContent .= '

					// <!--
						// Listing options for clipboard and thumbnails
					// -->
					// <div id="typo3-listOptions">
						// <form action="" method="post">';

			// $sContent .= t3lib_BEfunc::getFuncCheck($this->id,'SET[bigControlPanel]',$this->MOD_SETTINGS['bigControlPanel'],'db_list.php',($this->table?'&table='.$this->table:''),'id="checkLargeControl"').' <label for="checkLargeControl">'.$LANG->getLL('largeControl',1).'</label><br />';
			// if ($dblist->showClipboard)	{
				// $sContent .= t3lib_BEfunc::getFuncCheck($this->id,'SET[clipBoard]',$this->MOD_SETTINGS['clipBoard'],'db_list.php',($this->table?'&table='.$this->table:''),'id="checkShowClipBoard"').' <label for="checkShowClipBoard">'.$LANG->getLL('showClipBoard',1).'</label><br />';
			// }
			// $sContent .= '
						// </form>
					// </div>';
			// $sContent .= t3lib_BEfunc::cshItem('xMOD_csh_corebe', 'list_options', $GLOBALS['BACK_PATH']);

				// // Printing clipboard if enabled:
			// if ($this->MOD_SETTINGS['clipBoard'] && $dblist->showClipboard)	{
				// $sContent .= $dblist->clipObj->printClipboard();
				// $sContent .= t3lib_BEfunc::cshItem('xMOD_csh_corebe', 'list_clipboard', $GLOBALS['BACK_PATH']);
			// }
		}
		else {
		}
		$this->aListHeaderButtons = $dblist->getButtons();

		$this->content .= $this->doc->section($this->filegroupTitle, $sContent, 0, 1);
	}

	/**
	 * @return	boolean		Le succès du chargement de l'espace.
	 * @author Pierrick Caillon <pierrick@in-cite.net>
	 * @desc Charge l'espace courant. Le titre et les groupes sont enregistrés en variables d'instance.
	 */
	function loadFilegroups() {
		global $LANG, $TYPO3_DB;
		if (empty($this->id))
		{
			$oMessage = t3lib_div::makeInstance(
				't3lib_FlashMessage',
				$LANG->getLL('info_selectfilegroup'),
				'',
				t3lib_FlashMessage::INFO
			);
			t3lib_FlashMessageQueue::addMessage($oMessage);
			return false;
		}
		$sTable = 'tx_icsoddatastore_filegroups';
		$aResults = $TYPO3_DB->exec_SELECTgetRows('*', $sTable, 'uid = ' . $this->id . ' ' . t3lib_BEfunc::deleteClause($sTable));
		if (empty($aResults))
		{
			$oMessage = t3lib_div::makeInstance(
				't3lib_FlashMessage',
				$LANG->getLL('error_nofilegroup'),
				'',
				t3lib_FlashMessage::ERROR
			);
			t3lib_FlashMessageQueue::addMessage($oMessage);
			return false;
		}
		$this->filegroup = $aResults[0];
		$this->filegroupTitle = t3lib_BEfunc::getRecordTitle($sTable, $aResults[0], FALSE, TRUE);
		return true;
	}


	/**
	 * Render filegroup
	 *
	 * @return	The		filegroup content
	 */
	function renderFilegroup()	{
		global $LANG;

		if(!empty($this->filegroup['licence'])) {
			$licence = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				'`name`',
				'`tx_icsoddatastore_licences`',
				'`uid` IN (' . $this->filegroup['licence'] . ')'
			);
			if(is_array($licence) && count($licence)) {
				foreach($licence as $licenc) {
					$aLicence[] = $licenc['name'];
				}
				$licenceValue = implode(', ', $aLicence);
			}
			else {
				$licenceValue = '';
			}
		}

		if(!empty($this->filegroup['categories'])) {
			$categories = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				'`name`',
				'`tx_icsoddatastore_categories`',
				'`uid` IN (' . $this->filegroup['categories'] . ')'
			);
			if(is_array($categories) && count($categories)) {
				foreach($categories as $category) {
					$aCat[] = $category['name'];
				}
				$categoriesValue = implode(', ', $aCat);
			}
			else {
				$categoriesValue = '';
			}
		}

		if(!empty($this->filegroup['agency'])) {
			$agency = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				'`name`',
				'`tx_icsoddatastore_tiers`',
				'`uid` IN (' . $this->filegroup['agency'] . ')'
			);
			if(is_array($agency) && count($agency)) {
				foreach($agency as $agenc) {
					$aAgency[] = $agenc['name'];
				}
				$agencyValue = implode(', ', $aAgency);
			}
			else {
				$agencyValue = '';
			}
		}

		if(!empty($this->filegroup['contact'])) {
			$contact = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				'`name`',
				'`tx_icsoddatastore_tiers`',
				'`uid` IN (' . $this->filegroup['contact'] . ')'
			);
			if(is_array($contact) && count($contact)) {
				foreach($contact as $contac) {
					$aContact[] = $contac['name'];
				}
				$contactValue = implode(', ', $aContact);
			}
			else {
				$contactValue = '';
			}
		}

		if(!empty($this->filegroup['publisher'])) {
			$publisher = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				'`name`',
				'`tx_icsoddatastore_tiers`',
				'`uid` IN (' . $this->filegroup['publisher'] . ')'
			);
			if(is_array($publisher) && count($publisher)) {
				foreach($publisher as $publishe) {
					$aPublisher[] = $publishe['name'];
				}
				$publisherValue = implode(', ', $aPublisher);
			}
			else {
				$publisherValue = '';
			}
		}

		if(!empty($this->filegroup['creator'])) {
			$creator = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				'`name`',
				'`tx_icsoddatastore_tiers`',
				'`uid` IN (' . $this->filegroup['creator'] . ')'
			);
			if(is_array($creator) && count($creator)) {
				foreach($creator as $creato) {
					$aCreator[] = $creato['name'];
				}
				$creatorValue = implode(', ', $aCreator);
			}
			else {
				$creatorValue = '';
			}
		}

		if(!empty($this->filegroup['manager'])) {
			$manager = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				'`name`',
				'`tx_icsoddatastore_tiers`',
				'`uid` IN (' . $this->filegroup['manager'] . ')'
			);
			if(is_array($manager) && count($manager)) {
				foreach($manager as $manage) {
					$aManager[] = $manage['name'];
				}
				$managerValue = implode(', ', $aManager);
			}
			else {
				$managerValue = '';
			}
		}

		if(!empty($this->filegroup['owner'])) {
			$owner = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				'`name`',
				'`tx_icsoddatastore_tiers`',
				'`uid` IN (' . $this->filegroup['owner'] . ')'
			);
			if(is_array($owner) && count($owner)) {
				foreach($owner as $owne) {
					$aOwner[] = $owne['name'];
				}
				$ownerValue = implode(', ', $aOwner);
			}
			else {
				$ownerValue = '';
			}
		}

		$sContent .= '<div style="clear:both;float:left;width:100%;"><div style="float:left;width:15em;">' . $LANG->getLL('description') . '</div><div style="float:left;width:60%;">' . $this->filegroup['description'] . '</div></div>';
		$sContent .= '<div style="clear:both;float:left;width:100%;"><div style="float:left;width:15em;">' . $LANG->getLL('technical_data') . '</div><div style="float:left;width:60%;">' . $this->filegroup['technical_data'] . '</div></div>';

		$sContent .= '<div style="clear:both;float:left;width:100%;"><div style="float:left;width:15em;">' . $LANG->getLL('agency') . '</div><div style="float:left;width:60%;">' . $agencyValue . '</div></div>';
		$sContent .= '<div style="clear:both;float:left;width:100%;"><div style="float:left;width:15em;">' . $LANG->getLL('category') . '</div><div style="float:left;width:60%;">' . $categoriesValue . '</div></div>';
		$sContent .= '<div style="clear:both;float:left;width:100%;"><div style="float:left;width:15em;">' . $LANG->getLL('contact') . '</div><div style="float:left;width:60%;">' . $contactValue . '</div></div>';
		$sContent .= '<div style="clear:both;float:left;width:100%;"><div style="float:left;width:15em;">' . $LANG->getLL('licence') . '</div><div style="float:left;width:60%;">' . $licenceValue . '</div></div>';

		$sContent .= '<div style="clear:both;float:left;width:100%;"><div style="float:left;width:15em;">' . $LANG->getLL('release_date') . '</div><div style="float:left;width:60%;">' . (($this->filegroup['release_date'])? date('d/m/Y',$this->filegroup['release_date']) : '') . '</div></div>';
		$sContent .= '<div style="clear:both;float:left;width:100%;"><div style="float:left;width:15em;">' . $LANG->getLL('update_date') . '</div><div style="float:left;width:60%;">' . (($this->filegroup['update_date'])? date('d/m/Y',$this->filegroup['update_date']) : '') . '</div></div>';
		$sContent .= '<div style="clear:both;float:left;width:100%;"><div style="float:left;width:15em;">' . $LANG->getLL('time_period') . '</div><div style="float:left;width:60%;">' . $this->filegroup['time_period'] . '</div></div>';
		$sContent .= '<div style="clear:both;float:left;width:100%;"><div style="float:left;width:15em;">' . $LANG->getLL('update_frequency') . '</div><div style="float:left;width:60%;">' . $this->filegroup['update_frequency'] . '</div></div>';

		$sContent .= '<div style="clear:both;float:left;width:100%;"><div style="float:left;width:15em;">' . $LANG->getLL('publisher') . '</div><div style="float:left;width:60%;">' . $publisherValue . '</div></div>';
		$sContent .= '<div style="clear:both;float:left;width:100%;"><div style="float:left;width:15em;">' . $LANG->getLL('creator') . '</div><div style="float:left;width:60%;">' . $creatorValue . '</div></div>';
		$sContent .= '<div style="clear:both;float:left;width:100%;"><div style="float:left;width:15em;">' . $LANG->getLL('manager') . '</div><div style="float:left;width:60%;">' . $managerValue . '</div></div>';
		$sContent .= '<div style="clear:both;float:left;width:100%;"><div style="float:left;width:15em;">' . $LANG->getLL('owner') . '</div><div style="float:left;width:60%;">' . $ownerValue . '</div></div><div style="clear:both;"></div>';

		return $sContent;
	}

	/**
	 * Render menu buttons
	 *
	 * @return	string	content
	 */
	function renderMenuButtons()	{
		global $LANG;

		$sContent .= '<div style="padding-top: 2em;">';
		$sContent .= '<p style="display: inline; margin-left: 1em"><a href="#" onclick="' . htmlspecialchars(t3lib_BEfunc::editOnClick($GLOBALS['SOBE']->getNewRecordParams(),$this->backPath,-1)) . '">' .
			'<img ' .  t3lib_iconWorks::skinImg(t3lib_extMgm::extRelPath('ics_od_datastore'), 'res/uploader.png') . ' title="' . $LANG->getLL('newFile') . '" alt="' . $LANG->getLL('newFile') . '" />' .
			'</a></p>';
		$sContent .= '<p style="display: inline; margin-left: 1em"><a href="#" onclick="' . htmlspecialchars(t3lib_BEfunc::editOnClick('&edit[tx_icsoddatastore_filegroups][' . $this->filegroup['uid']. ']=edit',$this->backPath,-1)) . '">' .
			'<img ' .  t3lib_iconWorks::skinImg(t3lib_extMgm::extRelPath('ics_od_datastore'), 'res/editer.png') . ' title="' . $LANG->getLL('editFile') . '" alt="' . $LANG->getLL('editFile') . '" />' .
			'</a></p>';
		$sContent .='</div>';

		return $sContent;
	}

	/**
	 * Create the panel of buttons for submitting the form or otherwise perform operations.
	 *
	 * @return	array		all available buttons as an assoc. array
	 */
	protected function getButtons()	{
		global $LANG;

		$buttons = array(
			'shortcut' => '',
			'csh' => '',
			'new_record' => '',
			'reload' => '',
			'back' => '',
		);

			// Shortcut
		if ($GLOBALS['BE_USER']->mayMakeShortcut())	{
			$buttons['shortcut'] = $this->doc->makeShortcutIcon('id, pointer, showLimit, sortField, sortRev', 'function', $this->MCONF['name']);
		}

			// Edit record
		// $buttons['edit'] = '<a href="#" onclick="' . htmlspecialchars(t3lib_BEfunc::editOnClick('&edit[tx_icsoddatastore_filegroups][' . $this->id . ']=edit',$this->doc->backPath,-1)) . '">' .
						// '<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/edit2.gif') . ' title="' . $LANG->getLL('edit', 1) . '" alt="" />' .
						// '</a>';
		$buttons['edit'] = '<a href="#" onclick="' . htmlspecialchars(t3lib_BEfunc::editOnClick('&edit[tx_icsoddatastore_filegroups][' . $this->id . ']=edit',$this->doc->backPath,-1)) . '">' .
						'<img' . t3lib_iconWorks::skinImg(t3lib_extMgm::extRelPath('ics_od_datastore'), 'res/editer_icone.png') . ' title="' . $LANG->getLL('editFile') . '" alt="' . $LANG->getLL('editFile') . '" />' .
						'</a>';

			// New record file
		// $buttons['new_record'] = '<a href="#" onclick="' . htmlspecialchars(t3lib_BEfunc::editOnClick($this->getNewRecordParams(),$this->backPath,-1)) . '">' .
						// '<img' . t3lib_iconWorks::skinImg(t3lib_extMgm::extRelPath('ics_od_datastore'), 'res/uploader_icone.png') . ' title="' . $LANG->getLL('newFile') . '" alt="' . $LANG->getLL('newFile') . '" />' .
						// '</a>';

		return $buttons;
	}

	/**
	 * Get URL params for new tx_icsoddatastore_files record
	 *
	 * @return	string	params url
	 */
	public function getNewRecordParams()
	{
		return '&edit[tx_icsoddatastore_files][' . $this->pid . ']=new&overrideVals[tx_icsoddatastore_files][filegroup]=1&defVals[tx_icsoddatastore_files][filegroup]=' . $this->id;
	}

	/**
	 * get URL params for edit tx_icsoddatastore_files record
	 *
	 * @param	int		$sId: record uid
	 * @return	string	params url
	 */
	public function getEditRecordParams($sId)
	{
		return '&edit[tx_icsoddatastore_files][' . $sId . ']=edit&overrideVals[tx_icsoddatastore_files][filegroup]=1&defVals[tx_icsoddatastore_files][filegroup]=' . $this->id;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_od_datastore/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_od_datastore/mod1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_icsoddatastore_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>