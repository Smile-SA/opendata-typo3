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
 * $Id: class.tx_icsoddatastore_recordlist.php 48432 2011-06-07 09:27:20Z emilieprudhomme $
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   75: class tx_icsoddatastore_recordList extends recordList
 *
 *   94:     public function __construct()
 *  103:     public function getButtons()
 *  148:     function getTable($table,$id,$rowlist)
 *  504:     function renderListRow($table,$row,$cc,$titleCol,$thumbsCol,$indent=0)
 *  624:     function setReferences($table, $uid)
 *  644:     function renderListHeader($table, $currentIdList)
 *  823:     protected function renderListNavigation()
 *  893:     function calculatePointer()
 *
 *              SECTION: Rendering of various elements
 *  960:     function makeControl($table,$row)
 * 1199:     function makeClip($table,$row)
 * 1288:     function makeRef($table,$uid)
 * 1308:     function makeLocalizationPanel($table,$row)
 * 1356:     function fieldSelectBox($table,$formFields=1)
 *
 *              SECTION: Helper functions
 * 1427:     function linkClipboardHeaderIcon($string,$table,$cmd,$warning='')
 * 1438:     function clipNumPane()
 * 1452:     function addSortLink($code,$field,$table)
 * 1477:     function recPath($pid)
 * 1491:     function showNewRecLink($table)
 * 1510:     function makeReturnUrl()
 * 1524:     function listURL($altId='',$table=-1,$exclList='')
 *
 * TOTAL FUNCTIONS: 20
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
$LANG->includeLLFile('EXT:lang/locallang_mod_web_list.xml');
require_once ($BACK_PATH.'class.db_list.inc');

/**
 * Rendu de la liste d'enregistrement comme dans le module Web>List.
 *
 * @origauthor	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @origpackage TYPO3
 * @origsubpackage core
 * @author Pierrick Caillon <pierrick@in-cite.net>
 */
class tx_icsoddatastore_recordList extends recordList {
		// External:
	var $alternateBgColors=FALSE; /**< If true, table rows in the list will alternate in background colors (and have background colors at all!) */
	var $totalRowCount;	/**< count of record rows in view */

	var $spaceIcon;	/**< space icon used for alignment */

		// Internal:
	var $pageRow=array(); /**< Set to the page record (see writeTop()) */

	var $references; /**< References of the current record */
	var $translations; /**< Translations of the current record */
	var $selFieldList; /**< select fields for the query which fetches the translations of the current record */

	/**
	 * _construct
	 *
	 * @return	void
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Create the panel of buttons for submitting the form or otherwise perform operations.
	 *
	 * @return	array		all available buttons as an assoc. array
	 */
	public function getButtons()	{
		global $LANG;

		$buttons = array(
			'csh' => '',
			'new_record' => '',
			'reload' => '',
		);

			// Get users permissions for this page record:
		$localCalcPerms = $GLOBALS['BE_USER']->calcPerms($this->pageRow);

		/*	// CSH
		if (!strlen($this->id))	{
			$buttons['csh'] = t3lib_BEfunc::cshItem('xMOD_csh_corebe', 'list_module_noId', $GLOBALS['BACK_PATH'], '', TRUE);
		} elseif(!$this->id) {
			$buttons['csh'] = t3lib_BEfunc::cshItem('xMOD_csh_corebe', 'list_module_root', $GLOBALS['BACK_PATH'], '', TRUE);
		} else {
			$buttons['csh'] = t3lib_BEfunc::cshItem('xMOD_csh_corebe', 'list_module', $GLOBALS['BACK_PATH'], '', TRUE);
		}*/

			// New record
		// $buttons['new_record'] = '<a href="#" onclick="' . htmlspecialchars(t3lib_BEfunc::editOnClick($GLOBALS['SOBE']->getNewRecordParams(),$this->backPath,-1)) . '">' .
						// '<img' . t3lib_iconWorks::skinImg($this->backPath, 'gfx/new_el.gif') . ' title="' . $LANG->getLL('newRecordGeneral', 1) . '" alt="" />' .
						// '</a>';
		$buttons['new_record'] = '<a href="#" onclick="' . htmlspecialchars(t3lib_BEfunc::editOnClick($GLOBALS['SOBE']->getNewRecordParams(),$this->backPath,-1)) . '">' .
						'<img' . t3lib_iconWorks::skinImg(t3lib_extMgm::extRelPath('ics_od_datastore'), 'res/uploader_icone.png') . ' title="' . $LANG->getLL('newFile') . '" alt="' . $LANG->getLL('newFile') . '" />' .
						'</a>';

			// Reload
		$buttons['reload'] = '<a href="' . htmlspecialchars($this->listURL()) . '">' .
						'<img' . t3lib_iconWorks::skinImg($this->backPath, 'gfx/refresh_n.gif') . ' title="' . $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.reload', 1) . '" alt="" />' .
						'</a>';

		return $buttons;
	}

	/**
	 * Creates the listing of records from a single table
	 *
	 * @param	string		$table: Table name
	 * @param	integer		$id: Page id
	 * @param	string		$rowList: List of fields to show in the listing. Pseudo fields will be added including the record header.
	 * @return	string		HTML table with the listing for the record.
	 */
	function getTable($table,$id,$rowlist)	{
		global $TCA, $TYPO3_CONF_VARS, $LANG;

			// Loading all TCA details for this table:
		t3lib_div::loadTCA($table);

			// Init
		//$addWhere = ' AND ' . $table . '.filegroup = ' . $GLOBALS['SOBE']->id;
		$addWhere = ' AND ' . $table . '.uid IN (SELECT uid_local FROM tx_icsoddatastore_files_filegroup_mm WHERE uid_foreign = ' . $GLOBALS['SOBE']->id . ')';
		$titleCol = $TCA[$table]['ctrl']['label'];
		$thumbsCol = $TCA[$table]['ctrl']['thumbnail'];
		$l10nEnabled = $TCA[$table]['ctrl']['languageField'] && $TCA[$table]['ctrl']['transOrigPointerField'] && !$TCA[$table]['ctrl']['transOrigPointerTable'];
		$tableCollapsed = (!$this->tablesCollapsed[$table]) ? false : true;

			// prepare space icon
		$iconWidth  = $GLOBALS['TBE_STYLES']['skinImgAutoCfg']['iconSizeWidth']  ? $GLOBALS['TBE_STYLES']['skinImgAutoCfg']['iconSizeWidth']  : 12;
		$iconHeight = $GLOBALS['TBE_STYLES']['skinImgAutoCfg']['iconSizeHeight'] ? $GLOBALS['TBE_STYLES']['skinImgAutoCfg']['iconSizeHeight'] : 12;
		$this->spaceIcon = '<img src="' . $this->backPath . 'clear.gif" width="' . $iconWidth . '" height="' . $iconHeight . '" title="" alt="" />';

			// Cleaning rowlist for duplicates and place the $titleCol as the first column always!
		$this->fieldArray=array();

			// title Column
		$this->fieldArray[] = $titleCol;	// Add title column
			// type & format columns
		$this->fieldArray[] = 'type';
		$this->fieldArray[] = 'format';
			// Control-Panel
		$this->fieldArray[] = '_CONTROL_';
		$this->fieldArray[] = '_AFTERCONTROL_';

		/*
			// Control-Panel
		if (!t3lib_div::inList($rowlist,'_CONTROL_'))	{
			$this->fieldArray[] = '_CONTROL_';
			$this->fieldArray[] = '_AFTERCONTROL_';
		}
			// Clipboard
		if ($this->showClipboard)	{
			$this->fieldArray[] = '_CLIPBOARD_';
		}
			// Ref
		if (!$this->dontShowClipControlPanels)	{
			$this->fieldArray[]='_REF_';
			$this->fieldArray[]='_AFTERREF_';
		}
			// Path
		if ($this->searchLevels)	{
			$this->fieldArray[]='_PATH_';
		}
			// Localization
		if ($this->localizationView && $l10nEnabled)	{
			$this->fieldArray[] = '_LOCALIZATION_';
			$this->fieldArray[] = '_LOCALIZATION_b';
			$addWhere.=' AND (
				'.$TCA[$table]['ctrl']['languageField'].'<=0
				OR
				'.$TCA[$table]['ctrl']['transOrigPointerField'].' = 0
			)';
		}
		*/

			// Cleaning up:
		//$this->fieldArray=array_unique(array_merge($this->fieldArray,t3lib_div::trimExplode(',',$rowlist,1)));
		// if ($this->noControlPanels)	{
			// $tempArray = array_flip($this->fieldArray);
			// unset($tempArray['_CONTROL_']);
			// unset($tempArray['_CLIPBOARD_']);
			// $this->fieldArray = array_keys($tempArray);
		// }

			// Creating the list of fields to include in the SQL query:
		$selectFields = $this->fieldArray;
		$selectFields[] = 'uid';
		$selectFields[] = 'pid';
		if ($thumbsCol)	$selectFields[] = $thumbsCol;	// adding column for thumbnails
		if ($table=='pages')	{
			if (t3lib_extMgm::isLoaded('cms'))	{
				$selectFields[] = 'module';
				$selectFields[] = 'extendToSubpages';
				$selectFields[] = 'nav_hide';
			}
			$selectFields[] = 'doktype';
		}
		if (is_array($TCA[$table]['ctrl']['enablecolumns']))	{
			$selectFields = array_merge($selectFields,$TCA[$table]['ctrl']['enablecolumns']);
		}
		if ($TCA[$table]['ctrl']['type'])	{
			$selectFields[] = $TCA[$table]['ctrl']['type'];
		}
		if ($TCA[$table]['ctrl']['typeicon_column'])	{
			$selectFields[] = $TCA[$table]['ctrl']['typeicon_column'];
		}
		if ($TCA[$table]['ctrl']['versioningWS'])	{
			$selectFields[] = 't3ver_id';
			$selectFields[] = 't3ver_state';
			$selectFields[] = 't3ver_wsid';
			$selectFields[] = 't3ver_swapmode';		// Filtered out when pages in makeFieldList()
		}
		if ($l10nEnabled)	{
			$selectFields[] = $TCA[$table]['ctrl']['languageField'];
			$selectFields[] = $TCA[$table]['ctrl']['transOrigPointerField'];
		}
		if ($TCA[$table]['ctrl']['label_alt'])	{
			$selectFields = array_merge($selectFields,t3lib_div::trimExplode(',',$TCA[$table]['ctrl']['label_alt'],1));
		}
		$selectFields = array_unique($selectFields);		// Unique list!
		$selectFields = array_intersect($selectFields,$this->makeFieldList($table,1));		// Making sure that the fields in the field-list ARE in the field-list from TCA!
		$selFieldList = implode(',',$selectFields);		// implode it into a list of fields for the SQL-statement.
		$this->selFieldList = $selFieldList;

		/**
		 * @hook			DB-List getTable
		 * @date			2007-11-16
		 * @request		Malte Jansen  <mail@maltejansen.de>
		 */
		if(is_array($TYPO3_CONF_VARS['SC_OPTIONS']['typo3/class.db_list_extra.inc']['getTable'])) {
			foreach($TYPO3_CONF_VARS['SC_OPTIONS']['typo3/class.db_list_extra.inc']['getTable'] as $classData) {
				$hookObject = t3lib_div::getUserObj($classData);

				if(!($hookObject instanceof t3lib_localRecordListGetTableHook)) {
					throw new UnexpectedValueException('$hookObject must implement interface t3lib_localRecordListGetTableHook', 1195114460);
				}

				$hookObject->getDBlistQuery($table, $id, $addWhere, $selFieldList, $this);
			}
		}

		if ($this->firstElementNumber > 2 && $this->iLimit > 0) {
				// Get the two previous rows for sorting if displaying page > 1
			$this->firstElementNumber = $this->firstElementNumber - 2;
			$this->iLimit = $this->iLimit + 2;
			$queryParts = $this->makeQueryArray($table, $id,$addWhere,$selFieldList);	// (API function from class.db_list.inc)
			$this->firstElementNumber = $this->firstElementNumber + 2;
			$this->iLimit = $this->iLimit - 2;
		} else {
			$queryParts = $this->makeQueryArray($table, $id,$addWhere,$selFieldList);	// (API function from class.db_list.inc)
		}

		$this->setTotalItems($queryParts);		// Finding the total amount of records on the page (API function from class.db_list.inc)

			// Init:
		$dbCount = 0;
		$out = '';
		$listOnlyInSingleTableMode = $this->listOnlyInSingleTableMode && !$this->table;

			// If the count query returned any number of records, we perform the real query, selecting records.
		if ($this->totalItems)	{
			// Fetch records only if not in single table mode or if in multi table mode and not collapsed
			if ($listOnlyInSingleTableMode || (!$this->table && $tableCollapsed)) {
				$dbCount = $this->totalItems;
			} else {
					// set the showLimit to the number of records when outputting as CSV
				if ($this->csvOutput) {
					$this->showLimit = $this->totalItems;
					$this->iLimit = $this->totalItems;
				}
				$result = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($queryParts);
				$dbCount = $GLOBALS['TYPO3_DB']->sql_num_rows($result);
			}
		}

			// If any records was selected, render the list:
		if ($dbCount)	{

				// Half line is drawn between tables:
			if (!$listOnlyInSingleTableMode)	{
				$theData = Array();
				if (!$this->table && !$rowlist)	{
					$theData[$titleCol] = '<img src="clear.gif" width="230" height="1" alt="" />';
					if (in_array('_CONTROL_',$this->fieldArray))	$theData['_CONTROL_']='';
					if (in_array('_CLIPBOARD_',$this->fieldArray))	$theData['_CLIPBOARD_']='';
				}
				$out.=$this->addelement(0,'',$theData,'class="c-table-row-spacer"',$this->leftMargin);
			}

				// Header line is drawn
			$theData = Array();
			//if ($this->disableSingleTableView)	{
				$theData[$titleCol] = '<span class="c-table">'.$GLOBALS['LANG']->sL($TCA[$table]['ctrl']['title'],1).'</span> ('.$this->totalItems.')';
			//} else {
			//	$theData[$titleCol] = $this->linkWrapTable($table,'<span class="c-table">'.$GLOBALS['LANG']->sL($TCA[$table]['ctrl']['title'],1).'</span> ('.$this->totalItems.') <img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/'.($this->table?'minus':'plus').'bullet_list.gif','width="18" height="12"').' hspace="10" class="absmiddle" title="'.$GLOBALS['LANG']->getLL(!$this->table?'expandView':'contractView',1).'" alt="" />');
			//}

				// CSH:
			$theData[$titleCol].= t3lib_BEfunc::cshItem($table,'',$this->backPath,'',FALSE,'margin-bottom:0px; white-space: normal;');

			if ($listOnlyInSingleTableMode)	{
				/*$out.='
					<tr>
						<td class="c-headLineTable" style="width:95%;">'.$theData[$titleCol].'</td>
					</tr>';

				if ($GLOBALS['BE_USER']->uc["edit_showFieldHelp"])	{
					$GLOBALS['LANG']->loadSingleTableDescription($table);
					if (isset($GLOBALS['TCA_DESCR'][$table]['columns']['']))	{
						$onClick = 'vHWin=window.open(\'view_help.php?tfID='.$table.'.\',\'viewFieldHelp\',\'height=400,width=600,status=0,menubar=0,scrollbars=1\');vHWin.focus();return false;';
						$out.='
					<tr>
						<td class="c-tableDescription">'.t3lib_BEfunc::helpTextIcon($table,'',$this->backPath,TRUE).$GLOBALS['TCA_DESCR'][$table]['columns']['']['description'].'</td>
					</tr>';
					}
				}*/
			} else {
				// Render collapse button if in multi table mode
				/*$collapseIcon = '';
				if (!$this->table) {
					$collapseIcon = '<a href="' . htmlspecialchars($this->listURL() . '&collapse[' . $table . ']=' . ($tableCollapsed ? '0' : '1')) . '"><img' . t3lib_iconWorks::skinImg($this->backPath, 'gfx/arrow' . ($tableCollapsed ? 'right' : 'down') . '.png') . ' class="collapseIcon" alt="" title="' . ($tableCollapsed ? $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.expandTable',1) : $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.collapseTable',1)) . '" /></a>';
				}*/
				$out .= $this->addelement(1, $collapseIcon, $theData, ' class="c-headLineTable"', '');
			}

			// Render table rows only if in multi table view and not collapsed or if in single table view
			if (!$listOnlyInSingleTableMode && (!$tableCollapsed || $this->table)) {
					// Fixing a order table for sortby tables
				$this->currentTable = array();
				$currentIdList = array();
				$doSort = ($TCA[$table]['ctrl']['sortby'] && !$this->sortField);

				$prevUid = 0;
				$prevPrevUid = 0;

					// Get first two rows and initialize prevPrevUid and prevUid if on page > 1
				if ($this->firstElementNumber > 2 && $this->iLimit > 0) {
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
					$prevPrevUid = -(int) $row['uid'];
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
					$prevUid = $row['uid'];
				}

				$accRows = array();	// Accumulate rows here
				while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))	{

						// In offline workspace, look for alternative record:
					t3lib_BEfunc::workspaceOL($table, $row, $GLOBALS['BE_USER']->workspace, TRUE);

					if (is_array($row))	{
						$accRows[] = $row;
						$currentIdList[] = $row['uid'];
						if ($doSort)	{
							if ($prevUid)	{
								$this->currentTable['prev'][$row['uid']] = $prevPrevUid;
								$this->currentTable['next'][$prevUid] = '-'.$row['uid'];
								$this->currentTable['prevUid'][$row['uid']] = $prevUid;
							}
							$prevPrevUid = isset($this->currentTable['prev'][$row['uid']]) ? -$prevUid : $row['pid'];
							$prevUid=$row['uid'];
						}
					}
				}
				$GLOBALS['TYPO3_DB']->sql_free_result($result);

				$this->totalRowCount = count($accRows);

					// CSV initiated
				if ($this->csvOutput) $this->initCSV();

					// Render items:
				$this->CBnames=array();
				$this->duplicateStack=array();
				$this->eCounter=$this->firstElementNumber;

				$iOut = '';
				$cc = 0;

				foreach($accRows as $row)	{
					// Render item row if counter < limit
					if ($cc < $this->iLimit) {
						$cc++;
						$this->translations = FALSE;
						$iOut.= $this->renderListRow($table,$row,$cc,$titleCol,$thumbsCol);

							// If localization view is enabled it means that the selected records are either default or All language and here we will not select translations which point to the main record:
						if ($this->localizationView && $l10nEnabled)	{
								// For each available translation, render the record:
							if (is_array($this->translations)) {
								foreach ($this->translations as $lRow) {
										// $lRow isn't always what we want - if record was moved we've to work with the placeholder records otherwise the list is messed up a bit
									if ($row['_MOVE_PLH_uid'] && $row['_MOVE_PLH_pid']) {
										$tmpRow = t3lib_BEfunc::getRecordRaw($table, 't3ver_move_id="'.intval($lRow['uid']) . '" AND pid="' . $row['_MOVE_PLH_pid'] . '" AND t3ver_wsid=' . $row['t3ver_wsid'] . t3lib_beFunc::deleteClause($table), $selFieldList);
										$lRow = is_array($tmpRow)?$tmpRow:$lRow;
									}
										// In offline workspace, look for alternative record:
									t3lib_BEfunc::workspaceOL($table, $lRow, $GLOBALS['BE_USER']->workspace, true);
									if (is_array($lRow) && $GLOBALS['BE_USER']->checkLanguageAccess($lRow[$TCA[$table]['ctrl']['languageField']]))	{
										$currentIdList[] = $lRow['uid'];
										$iOut.=$this->renderListRow($table,$lRow,$cc,$titleCol,$thumbsCol,18);
									}
								}
							}
						}
					}

						// Counter of total rows incremented:
					$this->eCounter++;
				}

					// Record navigation is added to the beginning and end of the table if in single table mode
				if ($this->table) {
					$pageNavigation = $this->renderListNavigation();
					$iOut = $pageNavigation . $iOut . $pageNavigation;
				} else {
						// show that there are more records than shown
					if ($this->totalItems > $this->itemsLimitPerTable) {
						$countOnFirstPage = $this->totalItems > $this->itemsLimitSingleTable ? $this->itemsLimitSingleTable : $this->totalItems;
						$hasMore = ($this->totalItems > $this->itemsLimitSingleTable);
						$iOut .= '<tr><td colspan="' . count($this->fieldArray) . '" style="padding:5px;">
								<a href="'.htmlspecialchars($this->listURL() . '&table=' . rawurlencode($table)) . '">' .
								'<img' . t3lib_iconWorks::skinImg($this->backPath,'gfx/pildown.gif', 'width="14" height="14"') .' alt="" />'.
								' <i>[1 - ' . $countOnFirstPage . ($hasMore ? '+' : '') . ']</i></a>
								</td></tr>';
						}

				}

					// The header row for the table is now created:
				$out .= $this->renderListHeader($table,$currentIdList);
			}

				// The list of records is added after the header:
			$out .= $iOut;
			unset($iOut);

				// ... and it is all wrapped in a table:
			$out='



			<!--
				DB listing of elements:	"'.htmlspecialchars($table).'"
			-->
				<table border="0" cellpadding="0" cellspacing="0" class="typo3-dblist'.($listOnlyInSingleTableMode?' typo3-dblist-overview':'').'">
					'.$out.'
				</table>';

				// Output csv if...
			if ($this->csvOutput)	$this->outputCSV($table);	// This ends the page with exit.
		}

			// Return content:
		return $out;
	}

	/**
	 * Rendering a single row for the list
	 *
	 * @param	string		$table: Table name
	 * @param	array		$row: Current record
	 * @param	integer		$cc: Counter, counting for each time an element is rendered (used for alternating colors)
	 * @param	string		$titleCol: Table field (column) where header value is found
	 * @param	string		$thumbsCol: Table field (column) where (possible) thumbnails can be found
	 * @param	integer		$indent: Indent from left.
	 * @return	string		Table row for the element
	 * @access private
	 * @see getTable()
	 */
	function renderListRow($table,$row,$cc,$titleCol,$thumbsCol,$indent=0)	{
		$iOut = '';

		if (strlen($this->searchString))	{	// If in search mode, make sure the preview will show the correct page
			$id_orig = $this->id;
			$this->id = $row['pid'];
		}

		if (is_array($row))	{

			$this->setReferences($table, $row['uid']);
				// add special classes for first and last row
			$rowSpecial = '';
			if ($cc == 1 && $indent == 0) {
				$rowSpecial .= ' firstcol';
			}
			if ($cc == $this->totalRowCount || $cc == $this->iLimit) {
				$rowSpecial .= ' lastcol';
			}

				// Background color, if any:
			if ($this->alternateBgColors) {
				$row_bgColor = ($cc%2) ? ' class="db_list_normal'.$rowSpecial.'"' : ' class="db_list_alt'.$rowSpecial.'"';
			} else {
				$row_bgColor = ' class="db_list_normal'.$rowSpecial.'"';
			}
				// Overriding with versions background color if any:
			$row_bgColor = $row['_CSSCLASS'] ? ' class="'.$row['_CSSCLASS'].'"' : $row_bgColor;

				// Incr. counter.
			$this->counter++;

				// The icon with link
			$alttext = t3lib_BEfunc::getRecordIconAltText($row,$table);
			$iconImg = t3lib_iconWorks::getIconImage($table,$row,$this->backPath,'title="'.htmlspecialchars($alttext).'"'.($indent ? ' style="margin-left: '.$indent.'px;"' : ''));
			$theIcon = $this->clickMenuEnabled ? $GLOBALS['SOBE']->doc->wrapClickMenuOnIcon($iconImg,$table,$row['uid']) : $iconImg;

				// Preparing and getting the data-array
			$theData = Array();
			foreach($this->fieldArray as $fCol)	{
				if ($fCol==$titleCol)	{
					$recTitle = t3lib_BEfunc::getRecordTitle($table,$row,FALSE,TRUE);
						// If the record is edit-locked	by another user, we will show a little warning sign:
					if (($lockInfo = t3lib_BEfunc::isRecordLocked($table, $row['uid']))) {
						$warning = '<a href="#" onclick="' . htmlspecialchars('alert(' . $GLOBALS['LANG']->JScharCode($lockInfo['msg']) . '); return false;') . '">' .
							'<img' . t3lib_iconWorks::skinImg($this->backPath, 'gfx/recordlock_warning3.gif', 'width="17" height="12"') . ' title="' . htmlspecialchars($lockInfo['msg']) . '" alt="" />' .
							'</a>';
					}
					$theData[$fCol] = $warning . $this->linkWrapItems($table, $row['uid'], $recTitle, $row);

						// Render thumbsnails if a thumbnail column exists and there is content in it:
					if ($this->thumbs && trim($row[$thumbsCol])) {
						$theData[$fCol] .= '<br />' . $this->thumbCode($row,$table,$thumbsCol);
					}

					$localizationMarkerClass = '';
					if (isset($GLOBALS['TCA'][$table]['ctrl']['languageField'])
					&& $row[$GLOBALS['TCA'][$table]['ctrl']['languageField']] != 0) {
							// it's a translated record
						$localizationMarkerClass = ' localization';
					}
				} elseif ($fCol == 'pid') {
					$theData[$fCol]=$row[$fCol];
				} elseif ($fCol == '_PATH_') {
					$theData[$fCol]=$this->recPath($row['pid']);
				} elseif ($fCol == '_REF_') {
					$theData[$fCol]=$this->makeRef($table,$row['uid']);
				} elseif ($fCol == '_CONTROL_') {
					$theData[$fCol]=$this->makeControl($table,$row);
				} elseif ($fCol == '_AFTERCONTROL_' || $fCol == '_AFTERREF_') {
					$theData[$fCol] = '&nbsp;';
				} elseif ($fCol == '_CLIPBOARD_') {
					$theData[$fCol]=$this->makeClip($table,$row);
				} elseif ($fCol == '_LOCALIZATION_') {
					list($lC1, $lC2) = $this->makeLocalizationPanel($table,$row);
					$theData[$fCol] = $lC1;
					$theData[$fCol.'b'] = $lC2;
				} elseif ($fCol == '_LOCALIZATION_b') {
					// Do nothing, has been done above.
				} else {
					$tmpProc = t3lib_BEfunc::getProcessedValueExtra($table, $fCol, $row[$fCol], 100, $row['uid']);
					$theData[$fCol] = $this->linkUrlMail(htmlspecialchars($tmpProc), $row[$fCol]);
					$row[$fCol] = $tmpProc;
				}
			}

			if (strlen($this->searchString))	{	// Reset the ID if it was overwritten
				$this->id = $id_orig;
			}

			// Add classes to table cells
			$this->addElement_tdCssClass[$titleCol]         = 'col-title' . $localizationMarkerClass;
			// if (!$this->dontShowClipControlPanels) {
				// $this->addElement_tdCssClass['_CONTROL_']       = 'col-control';
				// $this->addElement_tdCssClass['_AFTERCONTROL_']  = 'col-control-space';
				// $this->addElement_tdCssClass['_CLIPBOARD_']     = 'col-clipboard';
			// }
			// Always add Control-Panel
			$this->addElement_tdCssClass['_CONTROL_']       = 'col-control';
			$this->addElement_tdCssClass['_AFTERCONTROL_']  = 'col-control-space';

			$this->addElement_tdCssClass['_PATH_']          = 'col-path';
			$this->addElement_tdCssClass['_LOCALIZATION_']  = 'col-localizationa';
			$this->addElement_tdCssClass['_LOCALIZATION_b'] = 'col-localizationb';

				// Create element in table cells:
			$iOut.=$this->addelement(1,$theIcon,$theData,$row_bgColor);

				// Finally, return table row element:
			return $iOut;
		}
	}

	/**
	 * Write sys_refindex entries for current record to $this->references
	 *
	 * @param	string		$table: Table name
	 * @param	integer		$uid: Uid of current record
	 * @return	void
	 */
	function setReferences($table, $uid) {
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			'sys_refindex',
			'ref_table='.$GLOBALS['TYPO3_DB']->fullQuoteStr($table,'sys_refindex').
				' AND ref_uid='.intval($uid).
				' AND deleted=0'
		);
		$this->references = $rows;
	}

	/**
	 * Rendering the header row for a table
	 *
	 * @param	string		$table: Table name
	 * @param	array		$currentIdList: Array of the currently displayed uids of the table
	 * @return	string		Header table row
	 * @access private
	 * @see getTable()
	 */
	function renderListHeader($table, $currentIdList)	{
		global $TCA, $LANG, $TYPO3_CONF_VARS;

			// Init:
		$theData = Array();

			// Traverse the fields:
		foreach($this->fieldArray as $fCol)	{

				// Calculate users permissions to edit records in the table:
			$permsEdit = $this->calcPerms & ($table=='pages'?2:16);

			switch((string)$fCol)	{
				case '_PATH_':			// Path
					$theData[$fCol] = '<i>['.$LANG->sL('LLL:EXT:lang/locallang_core.php:labels._PATH_',1).']</i>';
				break;
				case '_REF_':			// References
					$theData[$fCol] = '<i>['.$LANG->sL('LLL:EXT:lang/locallang_mod_file_list.xml:c__REF_',1).']</i>';
				break;
				case '_LOCALIZATION_':			// Path
					$theData[$fCol] = '<i>['.$LANG->sL('LLL:EXT:lang/locallang_core.php:labels._LOCALIZATION_',1).']</i>';
				break;
				case '_LOCALIZATION_b':			// Path
					$theData[$fCol] = $LANG->getLL('Localize',1);
				break;
				case '_CLIPBOARD_':		// Clipboard:
					$cells=array();

						// If there are elements on the clipboard for this table, then display the "paste into" icon:
					$elFromTable = $this->clipObj->elFromTable($table);
					if (count($elFromTable))	{
						$cells['pasteAfter']='<a href="'.htmlspecialchars($this->clipObj->pasteUrl($table,$this->id)).'" onclick="'.htmlspecialchars('return '.$this->clipObj->confirmMsg('pages',$this->pageRow,'into',$elFromTable)).'">'.
								'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/clip_pasteafter.gif','width="12" height="12"').' title="'.$LANG->getLL('clip_paste',1).'" alt="" />'.
								'</a>';
					}

						// If the numeric clipboard pads are enabled, display the control icons for that:
					if ($this->clipObj->current!='normal')	{

							// The "select" link:
						$cells['copyMarked']=$this->linkClipboardHeaderIcon('<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/clip_copy.gif','width="12" height="12"').' title="'.$LANG->getLL('clip_selectMarked',1).'" alt="" />',$table,'setCB');

							// The "edit marked" link:
						$editIdList = implode(',',$currentIdList);
						$editIdList = "'+editList('".$table."','".$editIdList."')+'";
						$params='&edit['.$table.']['.$editIdList.']=edit&disHelp=1';
						$cells['edit']='<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$this->backPath,-1)).'">'.
								'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/edit2.gif','width="11" height="12"').' title="'.$LANG->getLL('clip_editMarked',1).'" alt="" />'.
								'</a>';

							// The "Delete marked" link:
						$cells['delete']=$this->linkClipboardHeaderIcon('<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/garbage.gif','width="11" height="12"').' title="'.$LANG->getLL('clip_deleteMarked',1).'" alt="" />',$table,'delete',sprintf($LANG->getLL('clip_deleteMarkedWarning'),$LANG->sL($TCA[$table]['ctrl']['title'])));

							// The "Select all" link:
						$cells['markAll']='<a href="#" onclick="'.htmlspecialchars('checkOffCB(\''.implode(',',$this->CBnames).'\'); return false;').'">'.
								'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/clip_select.gif','width="12" height="12"').' title="'.$LANG->getLL('clip_markRecords',1).'" alt="" />'.
								'</a>';
					} else {
						$cells['empty']='';
					}
					/**
					 * @hook			renderListHeaderActions: Allows to change the clipboard icons of the Web>List table headers
					 * @date			2007-11-20
					 * @request		Bernhard Kraft  <krafbt@kraftb.at>
					 * @usage		Above each listed table in Web>List a header row is shown. This hook allows to modify the icons responsible for the clipboard functions (shown above the clipboard checkboxes when a clipboard other than "Normal" is selected), or other "Action" functions which perform operations on the listed records.
					 */
					if(is_array($TYPO3_CONF_VARS['SC_OPTIONS']['typo3/class.db_list_extra.inc']['actions']))	{
						foreach($TYPO3_CONF_VARS['SC_OPTIONS']['typo3/class.db_list_extra.inc']['actions'] as $classData)	{
							$hookObject = t3lib_div::getUserObj($classData);
							if(!($hookObject instanceof localRecordList_actionsHook))	{
								throw new UnexpectedValueException('$hookObject must implement interface localRecordList_actionsHook', 1195567850);
							}
							$cells = $hookObject->renderListHeaderActions($table, $currentIdList, $cells, $this);
						}
					}
					$theData[$fCol]=implode('',$cells);
				break;
				case '_CONTROL_':		// Control panel:
					if (!$TCA[$table]['ctrl']['readOnly'])	{

							// If new records can be created on this page, add links:
						if ($this->calcPerms&($table=='pages'?8:16) && $this->showNewRecLink($table))	{
							if ($table=="tt_content" && $this->newWizards)	{
									//  If mod.web_list.newContentWiz.overrideWithExtension is set, use that extension's create new content wizard instead:
								$tmpTSc = t3lib_BEfunc::getModTSconfig($this->pageinfo['uid'],'mod.web_list');
								$tmpTSc = $tmpTSc ['properties']['newContentWiz.']['overrideWithExtension'];
								$newContentWizScriptPath = $this->backPath.t3lib_extMgm::isLoaded($tmpTSc) ? (t3lib_extMgm::extRelPath($tmpTSc).'mod1/db_new_content_el.php') : 'sysext/cms/layout/db_new_content_el.php';

								$icon = '<a href="#" onclick="'.htmlspecialchars('return jumpExt(\''.$newContentWizScriptPath.'?id='.$this->id.'\');').'">'.
												'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/new_'.($table=='pages'?'page':'el').'.gif','width="'.($table=='pages'?13:11).'" height="12"').' title="'.$LANG->getLL('new',1).'" alt="" />'.
												'</a>';
							} elseif ($table=='pages' && $this->newWizards)	{
								$icon = '<a href="'.htmlspecialchars($this->backPath.'db_new.php?id='.$this->id.'&pagesOnly=1&returnUrl='.rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI'))).'">'.
												'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/new_'.($table=='pages'?'page':'el').'.gif','width="'.($table=='pages'?13:11).'" height="12"').' title="'.$LANG->getLL('new',1).'" alt="" />'.
												'</a>';

							} else {
								$params = $GLOBALS['SOBE']->getNewRecordParams();
								if ($table == 'pages_language_overlay') {
									$params .= '&overrideVals[pages_language_overlay][doktype]=' . (int) $this->pageRow['doktype'];
								}
								$icon   = '<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$this->backPath,-1)).'">'.
												'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/new_'.($table=='pages'?'page':'el').'.gif','width="'.($table=='pages'?13:11).'" height="12"').' title="'.$LANG->getLL('new',1).'" alt="" />'.
												'</a>';
							}
						}

							// If the table can be edited, add link for editing ALL SHOWN fields for all listed records:
						if ($permsEdit && $this->table && is_array($currentIdList))	{
							$editIdList = implode(',',$currentIdList);
							if ($this->clipNumPane()) $editIdList = "'+editList('".$table."','".$editIdList."')+'";
							$params = $GLOBALS['SOBE']->getEditRecordParams($editIdList) . '&columnsOnly='.implode(',',$this->fieldArray).'&disHelp=1';
							$icon  .= '<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$this->backPath,-1)).'">'.
											'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/edit2.gif','width="11" height="12"').' title="'.$LANG->getLL('editShownColumns',1).'" alt="" />'.
											'</a>';
						}
							// add an empty entry, so column count fits again after moving this into $icon
						$theData[$fCol] = '&nbsp;';
					}
				break;
				case '_AFTERCONTROL_':  // space column
				case '_AFTERREF_':	// space column
					$theData[$fCol] = '&nbsp;';
				break;
				default:			// Regular fields header:
					$theData[$fCol]='';
					if ($this->table && is_array($currentIdList))	{

							// If the numeric clipboard pads are selected, show duplicate sorting link:
						if ($this->clipNumPane()) {
							$theData[$fCol].='<a href="'.htmlspecialchars($this->listURL('',-1).'&duplicateField='.$fCol).'">'.
											'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/select_duplicates.gif','width="11" height="11"').' title="'.$LANG->getLL('clip_duplicates',1).'" alt="" />'.
											'</a>';
						}

							// If the table can be edited, add link for editing THIS field for all listed records:
						if (!$TCA[$table]['ctrl']['readOnly'] && $permsEdit && $TCA[$table]['columns'][$fCol])	{
							$editIdList = implode(',',$currentIdList);
							if ($this->clipNumPane()) $editIdList = "'+editList('".$table."','".$editIdList."')+'";
							$params = $GLOBALS['SOBE']->getEditRecordParams($editIdList) . '&columnsOnly='.$fCol.'&disHelp=1';
							$iTitle = sprintf($LANG->getLL('editThisColumn'),rtrim(trim($LANG->sL(t3lib_BEfunc::getItemLabel($table,$fCol))),':'));
							$theData[$fCol].='<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$this->backPath,-1)).'">'.
											'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/edit2.gif','width="11" height="12"').' title="'.htmlspecialchars($iTitle).'" alt="" />'.
											'</a>';
						}
					}
					$theData[$fCol].=$this->addSortLink($LANG->sL(t3lib_BEfunc::getItemLabel($table,$fCol,'<i>[|]</i>')),$fCol,$table);
				break;
			}

		}

		/**
		 * @hook			renderListHeader: Allows to change the contents of columns/cells of the Web>List table headers
		 * @date			2007-11-20
		 * @request		Bernhard Kraft  <krafbt@kraftb.at>
		 * @usage		Above each listed table in Web>List a header row is shown. Containing the labels of all shown fields and additional icons to create new records for this table or perform special clipboard tasks like mark and copy all listed records to clipboard, etc.
		 */
		if(is_array($TYPO3_CONF_VARS['SC_OPTIONS']['typo3/class.db_list_extra.inc']['actions']))	{
			foreach($TYPO3_CONF_VARS['SC_OPTIONS']['typo3/class.db_list_extra.inc']['actions'] as $classData)	{
				$hookObject = t3lib_div::getUserObj($classData);
				if(!($hookObject instanceof localRecordList_actionsHook))	{
					throw new UnexpectedValueException('$hookObject must implement interface localRecordList_actionsHook', 1195567855);
				}
				$theData = $hookObject->renderListHeader($table, $currentIdList, $theData, $this);
			}
		}

			// Create and return header table row:
		return $this->addelement(1, $icon, $theData, ' class="c-headLine"', '');
	}

	/**
	 * Creates a page browser for tables with many records
	 *
	 * @return	string		Navigation HTML
	 * @author	Dmitry Pikhno <dpi@goldenplanet.com>
	 * @author	Christian Kuhn <lolli@schwarzbu.ch>
	 */
	protected function renderListNavigation() {
		$totalPages = ceil($this->totalItems / $this->iLimit);

		$content = '';

			// Show page selector if not all records fit into one page
		if ($totalPages > 1) {
			$first = $previous = $next = $last = $reload = '';
			$listURL = $this->listURL('', $this->table);

				// 1 = first page
			$currentPage = floor(($this->firstElementNumber + 1) / $this->iLimit) + 1;

				// Compile first, previous, next, last and refresh buttons
			if ($currentPage > 1) {
				$labelFirst = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xml:first');

				$first = '<a href="' . $listURL . '&pointer=0">
					<img' . t3lib_iconWorks::skinImg($this->backPath, 'gfx/control_first.gif')
					. 'alt="' . $labelFirst . '" title="' . $labelFirst . '" />
				</a>';
			} else {
				$first = '<img' . t3lib_iconWorks::skinImg($this->backPath, 'gfx/control_first_disabled.gif') . 'alt="" title="" />';
			}

			if (($currentPage - 1) > 0) {
				$labelPrevious = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xml:previous');

				$previous = '<a href="' . $listURL . '&pointer=' . (($currentPage - 2) * $this->iLimit) . '">
					<img' . t3lib_iconWorks::skinImg($this->backPath, 'gfx/control_previous.gif')
					. 'alt="' . $labelPrevious . '" title="' . $labelPrevious . '" />
					</a>';
			} else {
				$previous = '<img' . t3lib_iconWorks::skinImg($this->backPath, 'gfx/control_previous_disabled.gif') . 'alt="" title="" />';
			}

			if (($currentPage + 1) <= $totalPages) {
				$labelNext = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xml:next');

				$next = '<a href="' . $listURL . '&pointer=' . (($currentPage) * $this->iLimit) . '">
					<img' . t3lib_iconWorks::skinImg($this->backPath, 'gfx/control_next.gif')
					. 'alt="' . $labelNext . '" title="' . $labelNext . '" />
					</a>';
			} else {
				$next = '<img' . t3lib_iconWorks::skinImg($this->backPath, 'gfx/control_next_disabled.gif') . 'alt="" title="" />';
			}

			if ($currentPage != $totalPages) {
				$labelLast = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xml:last');

				$last = '<a href="' . $listURL . '&pointer=' . (($totalPages - 1) * $this->iLimit) . '">
					<img' . t3lib_iconWorks::skinImg($this->backPath, 'gfx/control_last.gif')
					. 'alt="' . $labelLast . '" title="' . $labelLast . '" />
					</a>';
			} else {
				$last = '<img' . t3lib_iconWorks::skinImg($this->backPath, 'gfx/control_last_disabled.gif') . 'alt="" title="" />';
			}

			$reload = '<a href="#" onclick="document.dblistForm.action=\''
				. $listURL . '&pointer=\'+calculatePointer(); document.dblistForm.submit(); return true;">
				<img' . t3lib_iconWorks::skinImg($this->backPath, 'gfx/refresh_n.gif')
				. 'alt="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xml:reload')
				. '" title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xml:reload')
				. '" /></a>';

			// Add js to traverse a page select input to a pointer value
			$content = '
<script type="text/JavaScript">
/*<![CDATA[*/

	function calculatePointer(){
		page = document.getElementById(\'jumpPage\').value;

		if (page > ' . $totalPages . ') {
			page = ' . $totalPages . ';
		}

		if (page < 1) {
			page = 1;
		}

		pointer = (page - 1) * ' . $this->iLimit . ';

		return pointer;
	}

/*]]>*/
</script>
';

			$pageNumberInput = '<span>
				<input type="text" value="' . $currentPage
				. '" size="3" id="jumpPage" name="jumpPage" onkeyup="if (event.keyCode == Event.KEY_RETURN) { document.dblistForm.action=\'' . $listURL . '&pointer=\'+calculatePointer(); document.dblistForm.submit(); } return true;" />
				</span>';
			$pageIndicator = '<span class="pageIndicator">'
				. sprintf($GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:pageIndicator'), $pageNumberInput, $totalPages)
				. '</span>';

			if ($this->totalItems > ($this->firstElementNumber + $this->iLimit)) {
				$lastElementNumber = $this->firstElementNumber + $this->iLimit;
			} else {
				$lastElementNumber = $this->totalItems;
			}
			$rangeIndicator = '<span class="pageIndicator">'
				. sprintf($GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:rangeIndicator'), $this->firstElementNumber + 1, $lastElementNumber)
				. '</span>';

			$content .= '<div id="typo3-dblist-pagination">'
				. $first . $previous
				. '<span class="bar">&nbsp;</span>'
				. $rangeIndicator . '<span class="bar">&nbsp;</span>'
				. $pageIndicator . '<span class="bar">&nbsp;</span>'
				. $next . $last . '<span class="bar">&nbsp;</span>'
				. $reload
				. '</div>';
		} // end of if pages > 1

		$data = Array();
		$titleColumn = $this->fieldArray[0];
		$data[$titleColumn] = $content;

		return ($this->addElement(1, '', $data));
	}

	/*********************************
	 *
	 * Rendering of various elements
	 *
	 *********************************/

	/**
	 * Creates the control panel for a single record in the listing.
	 *
	 * @param	string		$table: The table
	 * @param	array		$row: The record for which to make the control panel.
	 * @return	string		HTML table with the control panel (unless disabled)
	 */
	function makeControl($table,$row)	{
		global $TCA, $LANG, $SOBE, $TYPO3_CONF_VARS;

		// Always return Control-Panel
		//if ($this->dontShowClipControlPanels)	return '';

			// Initialize:
		t3lib_div::loadTCA($table);
		$cells=array();

			// If the listed table is 'pages' we have to request the permission settings for each page:
		if ($table=='pages')	{
			$localCalcPerms = $GLOBALS['BE_USER']->calcPerms(t3lib_BEfunc::getRecord('pages',$row['uid']));
		}

			// This expresses the edit permissions for this particular element:
		$permsEdit = ($table=='pages' && ($localCalcPerms&2)) || ($table!='pages' && ($this->calcPerms&16));

			// "Show" link (only pages and tt_content elements)
		if ($table=='pages' || $table=='tt_content')	{
			$params='&edit['.$table.']['.$row['uid'].']=edit';
			$cells['view']='<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::viewOnClick($table=='tt_content'?$this->id.'#'.$row['uid']:$row['uid'], $this->backPath)).'">'.
					'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/zoom.gif','width="12" height="12"').' title="'.$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.showPage',1).'" alt="" />'.
					'</a>';
		} elseif(!$this->table) {
			$cells['view'] = $this->spaceIcon;
		}

			// "Edit" link: ( Only if permissions to edit the page-record of the content of the parent page ($this->id)
		if ($permsEdit)	{
			$params = $GLOBALS['SOBE']->getEditRecordParams($row['uid']);
			$cells['edit']='<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$this->backPath,-1)).'">'.
					'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/edit2'.(!$TCA[$table]['ctrl']['readOnly']?'':'_d').'.gif','width="11" height="12"').' title="'.$LANG->getLL('edit',1).'" alt="" />'.
					'</a>';
		} elseif(!$this->table) {
			$cells['edit'] = $this->spaceIcon;
		}

			// "Move" wizard link for pages/tt_content elements:
		/*if (($table=="tt_content" && $permsEdit) || ($table=='pages'))	{
			$cells['move']='<a href="#" onclick="'.htmlspecialchars('return jumpExt(\''.$this->backPath.'move_el.php?table='.$table.'&uid='.$row['uid'].'\');').'">'.
					'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/move_'.($table=='tt_content'?'record':'page').'.gif','width="11" height="12"').' title="'.$LANG->getLL('move_'.($table=='tt_content'?'record':'page'),1).'" alt="" />'.
					'</a>';
		} elseif(!$this->table) {
			$cells['move'] = $this->spaceIcon;
		}*/

			// If the extended control panel is enabled OR if we are seeing a single table:
		if ($SOBE->MOD_SETTINGS['bigControlPanel'] || $this->table)	{

				// "Info": (All records)
			$cells['viewBig']='<a href="#" onclick="'.htmlspecialchars('top.launchView(\''.$table.'\', \''.$row['uid'].'\'); return false;').'">'.
					'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/zoom2.gif','width="12" height="12"').' title="'.$LANG->getLL('showInfo',1).'" alt="" />'.
					'</a>';

				// If the table is NOT a read-only table, then show these links:
			if (!$TCA[$table]['ctrl']['readOnly'])	{

					// "Revert" link (history/undo)
				$cells['history']='<a href="#" onclick="'.htmlspecialchars('return jumpExt(\''.$this->backPath.'show_rechis.php?element='.rawurlencode($table.':'.$row['uid']).'\',\'#latest\');').'">'.
						'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/history2.gif','width="13" height="12"').' title="'.$LANG->getLL('history',1).'" alt="" />'.
						'</a>';

					// Versioning:
				if (t3lib_extMgm::isLoaded('version'))	{
					$vers = t3lib_BEfunc::selectVersionsOfRecord($table, $row['uid'], 'uid', $GLOBALS['BE_USER']->workspace, FALSE, $row);
					if (is_array($vers))	{	// If table can be versionized.
						if (count($vers)>1)	{
							$class = 'typo3-ctrl-versioning-multipleVersions';
							$lab = count($vers)-1;
						} else {
							$class = 'typo3-ctrl-versioning-oneVersion';
							$lab = 'V';
						}

						$cells['version']='<a href="'.htmlspecialchars($this->backPath.t3lib_extMgm::extRelPath('version').'cm1/index.php?table='.rawurlencode($table).'&uid='.rawurlencode($row['uid'])).'" title="'.$LANG->getLL('displayVersions',1).'" class="typo3-ctrl-versioning ' . $class . '">'.
								$lab.
								'</a>';
					} elseif(!$this->table) {
						$cells['version'] = '<span class="typo3-ctrl-versioning typo3-ctrl-versioning-hidden">V</span>';
					}
				}

					// "Edit Perms" link:
				if ($table=='pages' && $GLOBALS['BE_USER']->check('modules','web_perm'))	{
					$cells['perms']='<a href="'.htmlspecialchars('mod/web/perm/index.php?id='.$row['uid'].'&return_id='.$row['uid'].'&edit=1').'">'.
							'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/perm.gif','width="7" height="12"').' title="'.$LANG->getLL('permissions',1).'" alt="" />'.
							'</a>';
				} elseif(!$this->table && $GLOBALS['BE_USER']->check('modules','web_perm')) {
					$cells['perms'] = $this->spaceIcon;
				}

/*					// "New record after" link (ONLY if the records in the table are sorted by a "sortby"-row or if default values can depend on previous record):
				if ($TCA[$table]['ctrl']['sortby'] || $TCA[$table]['ctrl']['useColumnsForDefaultValues'])	{
					if (
						($table!='pages' && ($this->calcPerms&16)) || 	// For NON-pages, must have permission to edit content on this parent page
						($table=='pages' && ($this->calcPerms&8))		// For pages, must have permission to create new pages here.
						)	{
						if ($this->showNewRecLink($table))	{
							$params='&edit['.$table.']['.(-($row['_MOVE_PLH']?$row['_MOVE_PLH_uid']:$row['uid'])).']=new';
							$cells['new']='<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$this->backPath,-1)).'">'.
									'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/new_'.($table=='pages'?'page':'el').'.gif','width="'.($table=='pages'?13:11).'" height="12"').' title="'.$LANG->getLL('new'.($table=='pages'?'Page':'Record'),1).'" alt="" />'.
									'</a>';
						}
					}
				} elseif(!$this->table) {
					$cells['new'] = $this->spaceIcon;
				}
*//*
					// "Up/Down" links
				if ($permsEdit && $TCA[$table]['ctrl']['sortby']  && !$this->sortField && !$this->searchLevels)	{
					if (isset($this->currentTable['prev'][$row['uid']]))	{	// Up
						$params='&cmd['.$table.']['.$row['uid'].'][move]='.$this->currentTable['prev'][$row['uid']];
						$cells['moveUp']='<a href="#" onclick="'.htmlspecialchars('return jumpToUrl(\''.$SOBE->doc->issueCommand($params,-1).'\');').'">'.
								'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_up.gif','width="11" height="10"').' title="'.$LANG->getLL('moveUp',1).'" alt="" />'.
								'</a>';
					} else {
						$cells['moveUp'] = $this->spaceIcon;
					}
					if ($this->currentTable['next'][$row['uid']])	{	// Down
						$params='&cmd['.$table.']['.$row['uid'].'][move]='.$this->currentTable['next'][$row['uid']];
						$cells['moveDown']='<a href="#" onclick="'.htmlspecialchars('return jumpToUrl(\''.$SOBE->doc->issueCommand($params,-1).'\');').'">'.
								'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_down.gif','width="11" height="10"').' title="'.$LANG->getLL('moveDown',1).'" alt="" />'.
								'</a>';
					} else {
						$cells['moveDown'] = $this->spaceIcon;
					}
				} elseif(!$this->table) {
					$cells['moveUp']  = $this->spaceIcon;
					$cells['moveDown'] = $this->spaceIcon;
				}
*/
					// "Hide/Unhide" links:
				$hiddenField = $TCA[$table]['ctrl']['enablecolumns']['disabled'];
				if ($permsEdit && $hiddenField && $TCA[$table]['columns'][$hiddenField] && (!$TCA[$table]['columns'][$hiddenField]['exclude'] || $GLOBALS['BE_USER']->check('non_exclude_fields',$table.':'.$hiddenField)))	{
					if ($row[$hiddenField])	{
						$params='&data['.$table.']['.$row['uid'].']['.$hiddenField.']=0';
						$cells['hide']='<a href="#" onclick="'.htmlspecialchars('return jumpToUrl(\''.$SOBE->doc->issueCommand($params,-1).'\');').'">'.
								'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_unhide.gif','width="11" height="10"').' title="'.$LANG->getLL('unHide'.($table=='pages'?'Page':''),1).'" alt="" />'.
								'</a>';
					} else {
						$params='&data['.$table.']['.$row['uid'].']['.$hiddenField.']=1';
						$cells['hide']='<a href="#" onclick="'.htmlspecialchars('return jumpToUrl(\''.$SOBE->doc->issueCommand($params,-1).'\');').'">'.
								'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_hide.gif','width="11" height="10"').' title="'.$LANG->getLL('hide'.($table=='pages'?'Page':''),1).'" alt="" />'.
								'</a>';
					}
				} elseif(!$this->table) {
					$cells['hide'] = $this->spaceIcon;
				}

					// "Delete" link:
				if (($table=='pages' && ($localCalcPerms&4)) || ($table!='pages' && ($this->calcPerms&16))) {
					$titleOrig = t3lib_BEfunc::getRecordTitle($table,$row,FALSE,TRUE);
					$title = t3lib_div::slashJS(t3lib_div::fixed_lgd_cs($titleOrig, $this->fixedL), 1);
					$params = '&cmd['.$table.']['.$row['uid'].'][delete]=1';

					$refCountMsg = t3lib_BEfunc::referenceCount($table, $row['uid'], ' ' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xml:labels.referencesToRecord'), count($this->references)) .
						t3lib_BEfunc::translationCount($table, $row['uid'], ' ' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xml:labels.translationsOfRecord'));
					$cells['delete']='<a href="#" onclick="'.htmlspecialchars('if (confirm('.$LANG->JScharCode($LANG->getLL('deleteWarning').' "'. $title.'" '.$refCountMsg).')) {jumpToUrl(\''.$SOBE->doc->issueCommand($params,-1).'\');} return false;').'">'.
							'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/garbage.gif','width="11" height="12"').' title="'.$LANG->getLL('delete',1).'" alt="" />'.
							'</a>';
				} elseif(!$this->table) {
					$cells['delete'] = $this->spaceIcon;
				}

					// "Levels" links: Moving pages into new levels...
				if ($permsEdit && $table=='pages' && !$this->searchLevels)	{

						// Up (Paste as the page right after the current parent page)
					if ($this->calcPerms&8)	{
						$params='&cmd['.$table.']['.$row['uid'].'][move]='.-$this->id;
						$cells['moveLeft']='<a href="#" onclick="'.htmlspecialchars('return jumpToUrl(\''.$SOBE->doc->issueCommand($params,-1).'\');').'">'.
								'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_left.gif','width="11" height="10"').' title="'.$LANG->getLL('prevLevel',1).'" alt="" />'.
								'</a>';
					}
						// Down (Paste as subpage to the page right above)
					if ($this->currentTable['prevUid'][$row['uid']])	{
						$localCalcPerms = $GLOBALS['BE_USER']->calcPerms(t3lib_BEfunc::getRecord('pages',$this->currentTable['prevUid'][$row['uid']]));
						if ($localCalcPerms&8)	{
							$params='&cmd['.$table.']['.$row['uid'].'][move]='.$this->currentTable['prevUid'][$row['uid']];
							$cells['moveRight']='<a href="#" onclick="'.htmlspecialchars('return jumpToUrl(\''.$SOBE->doc->issueCommand($params,-1).'\');').'">'.
									'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_right.gif','width="11" height="10"').' title="'.$LANG->getLL('nextLevel',1).'" alt="" />'.
									'</a>';
						} else {
							$cells['moveRight'] = $this->spaceIcon;
						}
					} else {
						$cells['moveRight'] = $this->spaceIcon;
					}
				} elseif(!$this->table) {
					$cells['moveLeft'] = $this->spaceIcon;
					$cells['moveRight'] = $this->spaceIcon;
				}
			}
		}


		/**
		 * @hook			recStatInfoHooks: Allows to insert HTML before record icons on various places
		 * @date			2007-09-22
		 * @request		Kasper Skaarhoj  <kasper2007@typo3.com>
		 */
		if (is_array($TYPO3_CONF_VARS['SC_OPTIONS']['GLOBAL']['recStatInfoHooks']))	{
			$stat='';
			$_params = array($table,$row['uid']);
			foreach ($TYPO3_CONF_VARS['SC_OPTIONS']['GLOBAL']['recStatInfoHooks'] as $_funcRef)	{
				$stat.=t3lib_div::callUserFunction($_funcRef,$_params,$this);
			}
			$cells['stat'] = $stat;
		}
		/**
		 * @hook			makeControl: Allows to change control icons of records in list-module
		 * @date			2007-11-20
		 * @request		Bernhard Kraft  <krafbt@kraftb.at>
		 * @usage		This hook method gets passed the current $cells array as third parameter. This array contains values for the icons/actions generated for each record in Web>List. Each array entry is accessible by an index-key. The order of the icons is dependend on the order of those array entries.
		 */
		if(is_array($TYPO3_CONF_VARS['SC_OPTIONS']['typo3/class.db_list_extra.inc']['actions'])) {
			foreach($TYPO3_CONF_VARS['SC_OPTIONS']['typo3/class.db_list_extra.inc']['actions'] as $classData) {
				$hookObject = t3lib_div::getUserObj($classData);
				if(!($hookObject instanceof localRecordList_actionsHook))	{
					throw new UnexpectedValueException('$hookObject must implement interface localRecordList_actionsHook', 1195567840);
				}
				$cells = $hookObject->makeControl($table, $row, $cells, $this);
			}
		}

			// Compile items into a DIV-element:
		return '
											<!-- CONTROL PANEL: '.$table.':'.$row['uid'].' -->
											<div class="typo3-DBctrl">'.implode('',$cells).'</div>';
	}

	/**
	 * Creates the clipboard panel for a single record in the listing.
	 *
	 * @param	string		$table: The table
	 * @param	array		$row: The record for which to make the clipboard panel.
	 * @return	string		HTML table with the clipboard panel (unless disabled)
	 */
	function makeClip($table,$row)	{
		global $TCA, $LANG, $TYPO3_CONF_VARS;

			// Return blank, if disabled:
		if ($this->dontShowClipControlPanels)	return '';
		$cells=array();

		$cells['pasteAfter'] = $cells['pasteInto'] = $this->spaceIcon;
			//enables to hide the copy, cut and paste icons for localized records - doesn't make much sense to perform these options for them
		$isL10nOverlay = $this->localizationView && $table != 'pages_language_overlay' && $row[$TCA[$table]['ctrl']['transOrigPointerField']] != 0;
			// Return blank, if disabled:
			// Whether a numeric clipboard pad is active or the normal pad we will see different content of the panel:
		if ($this->clipObj->current=='normal')	{	// For the "Normal" pad:

				// Show copy/cut icons:
			$isSel = (string)$this->clipObj->isSelected($table,$row['uid']);
			$cells['copy'] = $isL10nOverlay ? $this->spaceIcon : '<a href="#" onclick="'.htmlspecialchars('return jumpSelf(\''.$this->clipObj->selUrlDB($table,$row['uid'],1,($isSel=='copy'),array('returnUrl'=>'')).'\');').'">'.
					'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/clip_copy'.($isSel=='copy'?'_h':'').'.gif','width="12" height="12"').' title="'.$LANG->sL('LLL:EXT:lang/locallang_core.php:cm.copy',1).'" alt="" />'.
					'</a>';
			$cells['cut'] = $isL10nOverlay ? $this->spaceIcon : '<a href="#" onclick="'.htmlspecialchars('return jumpSelf(\''.$this->clipObj->selUrlDB($table,$row['uid'],0,($isSel=='cut'),array('returnUrl'=>'')).'\');').'">'.
					'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/clip_cut'.($isSel=='cut'?'_h':'').'.gif','width="12" height="12"').' title="'.$LANG->sL('LLL:EXT:lang/locallang_core.php:cm.cut',1).'" alt="" />'.
					'</a>';

		} else {	// For the numeric clipboard pads (showing checkboxes where one can select elements on/off)

				// Setting name of the element in ->CBnames array:
			$n=$table.'|'.$row['uid'];
			$this->CBnames[]=$n;

				// Check if the current element is selected and if so, prepare to set the checkbox as selected:
			$checked = ($this->clipObj->isSelected($table,$row['uid'])?' checked="checked"':'');

				// If the "duplicateField" value is set then select all elements which are duplicates...
			if ($this->duplicateField && isset($row[$this->duplicateField]))	{
				$checked='';
				if (in_array($row[$this->duplicateField], $this->duplicateStack))	{
					$checked=' checked="checked"';
				}
				$this->duplicateStack[] = $row[$this->duplicateField];
			}

				// Adding the checkbox to the panel:
			$cells['select'] = $isL10nOverlay ? $this->spaceIcon : '<input type="hidden" name="CBH['.$n.']" value="0" /><input type="checkbox" name="CBC['.$n.']" value="1" class="smallCheckboxes"'.$checked.' />';
		}

			// Now, looking for selected elements from the current table:
		$elFromTable = $this->clipObj->elFromTable($table);
		if (count($elFromTable) && $TCA[$table]['ctrl']['sortby'])	{	// IF elements are found and they can be individually ordered, then add a "paste after" icon:
			$cells['pasteAfter'] = $isL10nOverlay ? $this->spaceIcon : '<a href="'.htmlspecialchars($this->clipObj->pasteUrl($table,-$row['uid'])).'" onclick="'.htmlspecialchars('return '.$this->clipObj->confirmMsg($table,$row,'after',$elFromTable)).'">'.
					'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/clip_pasteafter.gif','width="12" height="12"').' title="'.$LANG->getLL('clip_pasteAfter',1).'" alt="" />'.
					'</a>';
		}

			// Now, looking for elements in general:
		$elFromTable = $this->clipObj->elFromTable('');
		if ($table=='pages' && count($elFromTable))	{
			$cells['pasteInto']='<a href="'.htmlspecialchars($this->clipObj->pasteUrl('',$row['uid'])).'" onclick="'.htmlspecialchars('return '.$this->clipObj->confirmMsg($table,$row,'into',$elFromTable)).'">'.
					'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/clip_pasteinto.gif','width="12" height="12"').' title="'.$LANG->getLL('clip_pasteInto',1).'" alt="" />'.
					'</a>';
		}

		/*
		 * @hook			makeClip: Allows to change clip-icons of records in list-module
		 * @date			2007-11-20
		 * @request		Bernhard Kraft  <krafbt@kraftb.at>
		 * @usage		This hook method gets passed the current $cells array as third parameter. This array contains values for the clipboard icons generated for each record in Web>List. Each array entry is accessible by an index-key. The order of the icons is dependend on the order of those array entries.
		 */
		if(is_array($TYPO3_CONF_VARS['SC_OPTIONS']['typo3/class.db_list_extra.inc']['actions'])) {
			foreach($TYPO3_CONF_VARS['SC_OPTIONS']['typo3/class.db_list_extra.inc']['actions'] as $classData) {
				$hookObject = t3lib_div::getUserObj($classData);
				if(!($hookObject instanceof localRecordList_actionsHook))	{
					throw new UnexpectedValueException('$hookObject must implement interface localRecordList_actionsHook', 1195567845);
				}
				$cells = $hookObject->makeClip($table, $row, $cells, $this);
			}
		}

			// Compile items into a DIV-element:
		return '							<!-- CLIPBOARD PANEL: '.$table.':'.$row['uid'].' -->
											<div class="typo3-clipCtrl">'.implode('',$cells).'</div>';
	}

	/**
	 * Make reference count
	 *
	 * @param	string		$table: Table name
	 * @param	integer		$uid: UID of record
	 * @return	string		HTML-table
	 */
	function makeRef($table,$uid)	{

			// Compile information for title tag:
		$infoData=array();
		if (is_array($this->references)) {
			foreach ($this->references as $row) {
				$infoData[]=$row['tablename'].':'.$row['recuid'].':'.$row['field'];
			}
		}

		return count($infoData) ? '<a href="#" onclick="'.htmlspecialchars('top.launchView(\''.$table.'\', \''.$uid.'\'); return false;').'" title="'.htmlspecialchars(t3lib_div::fixed_lgd_cs(implode(' / ',$infoData),100)).'">'.count($infoData).'</a>' : '';
	}

	/**
	 * Creates the localization panel
	 *
	 * @param	string		$table: The table
	 * @param	array		$row: The record for which to make the localization panel.
	 * @return	array		Array with key 0/1 with content for column 1 and 2
	 */
	function makeLocalizationPanel($table,$row)	{
		global $TCA,$LANG;

		$out = array(
			0 => '',
			1 => '',
		);

		$translations = $this->translateTools->translationInfo($table, $row['uid'], 0, $row, $this->selFieldList);
		$this->translations = $translations['translations'];

			// Language title and icon:
		$out[0] = $this->languageFlag($row[$TCA[$table]['ctrl']['languageField']]);

		if (is_array($translations))	{

				// Traverse page translations and add icon for each language that does NOT yet exist:
			$lNew = '';
			foreach($this->pageOverlays as $lUid_OnPage => $lsysRec)	{
				if (!isset($translations['translations'][$lUid_OnPage]) && $GLOBALS['BE_USER']->checkLanguageAccess($lUid_OnPage))	{
					$href = $this->backPath . $GLOBALS['TBE_TEMPLATE']->issueCommand(
						'&cmd['.$table.']['.$row['uid'].'][localize]='.$lUid_OnPage,
						$this->listURL().'&justLocalized='.rawurlencode($table.':'.$row['uid'].':'.$lUid_OnPage)
					);

					$lC = ($this->languageIconTitles[$lUid_OnPage]['flagIcon'] ? '<img src="'.$this->languageIconTitles[$lUid_OnPage]['flagIcon'].'" class="absmiddle" alt="" />' : $this->languageIconTitles[$lUid_OnPage]['title']);
					$lC = '<a href="'.htmlspecialchars($href).'">'.$lC.'</a> ';

					$lNew.=$lC;
				}
			}

			if ($lNew)	$out[1].= $lNew;
		} else {
			$out[0] = '&nbsp;&nbsp;&nbsp;&nbsp;'.$out[0];
		}


		return $out;
	}

	/**
	 * Create the selector box for selecting fields to display from a table:
	 *
	 * @param	string		$table: Table name
	 * @param	boolean		$formFields: If true, form-fields will be wrapped around the table.
	 * @return	string		HTML table with the selector box (name: displayFields['.$table.'][])
	 */
	function fieldSelectBox($table,$formFields=1)	{
		global $TCA, $LANG;

			// Init:
		t3lib_div::loadTCA($table);
		$formElements=array('','');
		if ($formFields)	{
			$formElements=array('<form action="'.htmlspecialchars($this->listURL()).'" method="post">','</form>');
		}

			// Load already selected fields, if any:
		$setFields=is_array($this->setFields[$table]) ? $this->setFields[$table] : array();

			// Request fields from table:
		$fields = $this->makeFieldList($table, false, true);

			// Add pseudo "control" fields
		$fields[]='_PATH_';
		$fields[]='_REF_';
		$fields[]='_LOCALIZATION_';
		$fields[]='_CONTROL_';
		$fields[]='_CLIPBOARD_';

			// Create an option for each field:
		$opt=array();
		$opt[] = '<option value=""></option>';
		foreach($fields as $fN)	{
			$fL = is_array($TCA[$table]['columns'][$fN]) ? rtrim($LANG->sL($TCA[$table]['columns'][$fN]['label']),':') : '['.$fN.']';	// Field label
			$opt[] = '
											<option value="'.$fN.'"'.(in_array($fN,$setFields)?' selected="selected"':'').'>'.htmlspecialchars($fL).'</option>';
		}

			// Compile the options into a multiple selector box:
		$lMenu = '
										<select size="'.t3lib_div::intInRange(count($fields)+1,3,20).'" multiple="multiple" name="displayFields['.$table.'][]">'.implode('',$opt).'
										</select>
				';

			// Table with the field selector::
		$content.= '
			'.$formElements[0].'

				<!--
					Field selector for extended table view:
				-->
				<table border="0" cellpadding="0" cellspacing="0" class="bgColor4" id="typo3-dblist-fieldSelect">
					<tr>
						<td>'.$lMenu.'</td>
						<td><input type="submit" name="search" value="'.$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.setFields',1).'" /></td>
					</tr>
				</table>
			'.$formElements[1];
		return $content;
	}

	/*********************************
	 *
	 * Helper functions
	 *
	 *********************************/

	/**
	 * Creates a link around $string. The link contains an onclick action which submits the script with some clipboard action.
	 * Currently, this is used for setting elements / delete elements.
	 *
	 * @param	string		$string: The HTML content to link (image/text)
	 * @param	string		$table: Table name
	 * @param	string		$cmd: Clipboard command (eg. "setCB" or "delete")
	 * @param	string		$warning: Warning text, if any ("delete" uses this for confirmation)
	 * @return	string		<a> tag wrapped link.
	 */
	function linkClipboardHeaderIcon($string,$table,$cmd,$warning='')	{
		$onClickEvent = 'document.dblistForm.cmd.value=\''.$cmd.'\';document.dblistForm.cmd_table.value=\''.$table.'\';document.dblistForm.submit();';
		if ($warning)	$onClickEvent = 'if (confirm('.$GLOBALS['LANG']->JScharCode($warning).')){'.$onClickEvent.'}';
		return '<a href="#" onclick="'.htmlspecialchars($onClickEvent.'return false;').'">'.$string.'</a>';
	}

	/**
	 * Returns true if a numeric clipboard pad is selected/active
	 *
	 * @return	boolean
	 */
	function clipNumPane()	{
		return in_Array('_CLIPBOARD_',$this->fieldArray) && $this->clipObj->current!='normal';
	}

	/**
	 * Creates a sort-by link on the input string ($code).
	 * It will automatically detect if sorting should be ascending or descending depending on $this->sortRev.
	 * Also some fields will not be possible to sort (including if single-table-view is disabled).
	 *
	 * @param	string		$code: The string to link (text)
	 * @param	string		$field: The fieldname represented by the title ($code)
	 * @param	string		$table: Table name
	 * @return	string		Linked $code variable
	 */
	function addSortLink($code,$field,$table)	{

			// Certain circumstances just return string right away (no links):
		if ($field=='_CONTROL_' || $field=='_LOCALIZATION_' || $field=='_CLIPBOARD_' || $field=='_REF_' || $this->disableSingleTableView)	return $code;

			// If "_PATH_" (showing record path) is selected, force sorting by pid field (will at least group the records!)
		if ($field=='_PATH_')	$field=pid;

			//	 Create the sort link:
		$sortUrl = $this->listURL('',-1,'sortField,sortRev,table').'&table='.$table.'&sortField='.$field.'&sortRev='.($this->sortRev || ($this->sortField!=$field)?0:1);
		$sortArrow = ($this->sortField==$field?'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/red'.($this->sortRev?'up':'down').'.gif','width="7" height="4"').' alt="" />':'');

			// Return linked field:
		return '<a href="'.htmlspecialchars($sortUrl).'">'.$code.
				$sortArrow.
				'</a>';
	}

	/**
	 * Returns the path for a certain pid
	 * The result is cached internally for the session, thus you can call this function as much as you like without performance problems.
	 *
	 * @param	integer		$pid: The page id for which to get the path
	 * @return	string		The path.
	 */
	function recPath($pid)	{
		if (!isset($this->recPath_cache[$pid]))	{
			$this->recPath_cache[$pid] = t3lib_BEfunc::getRecordPath($pid,$this->perms_clause,20);
		}
		return $this->recPath_cache[$pid];
	}

	/**
	 * Returns true if a link for creating new records should be displayed for $table
	 *
	 * @param	string		$table: Table name
	 * @return	boolean		Returns true if a link for creating new records should be displayed for $table
	 * @see		SC_db_new::showNewRecLink
	 */
	function showNewRecLink($table)	{
			// No deny/allow tables are set:
		if (!count($this->allowedNewTables) && !count($this->deniedNewTables)) {
			return true;
			// If table is not denied (which takes precedence over allowed tables):
		} elseif (!in_array($table, $this->deniedNewTables) && (!count($this->allowedNewTables) || in_array($table, $this->allowedNewTables))) {
			return true;
			// If table is denied or allowed tables are set, but table is not part of:
		} else {
			return false;
		}
	}

	/**
	 * Creates the "&returnUrl" parameter for links - this is used when the script links to other scripts and passes its own URL with the link so other scripts can return to the listing again.
	 * Uses REQUEST_URI as value.
	 *
	 * @return	string
	 */
	function makeReturnUrl()	{
		return '&returnUrl='.rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI'));
	}

	/**
	 * Creates the URL to this script, including all relevant GPvars
	 * Fixed GPvars are id, table, imagemode, returlUrl, search_field, search_levels and showLimit
	 * The GPvars "sortField" and "sortRev" are also included UNLESS they are found in the $exclList variable.
	 *
	 * @param	string		$altId: Alternative id value. Enter blank string for the current id ($this->id)
	 * @param	string		$table: Tablename to display. Enter "-1" for the current table.
	 * @param	string		$exclList: Commalist of fields NOT to include ("sortField" or "sortRev")
	 * @return	string		URL
	 */
	function listURL($altId='',$table=-1,$exclList='')	{
		return $GLOBALS['BACK_PATH'] . $this->script.
			'&id='.(strcmp($altId,'')?$altId:$GLOBALS['SOBE']->id).
			($this->returnUrl?'&returnUrl='.rawurlencode($this->returnUrl):'').
			($this->showLimit?'&showLimit='.rawurlencode($this->showLimit):'').
			($this->firstElementNumber?'&pointer='.rawurlencode($this->firstElementNumber):'').
			((!$exclList || !t3lib_div::inList($exclList,'sortField')) && $this->sortField?'&sortField='.rawurlencode($this->sortField):'').
			((!$exclList || !t3lib_div::inList($exclList,'sortRev')) && $this->sortRev?'&sortRev='.rawurlencode($this->sortRev):'')
			;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_od_datastore/lib/class.tx_icsoddatastore_recordlist.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_od_datastore/lib/class.tx_icsoddatastore_recordlist.php']);
}

?>