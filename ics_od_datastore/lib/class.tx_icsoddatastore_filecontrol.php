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
 * $Id: class.tx_icsoddatastore_filecontrol.php 48432 2011-06-07 09:27:20Z emilieprudhomme $
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   46: class tx_icsoddatastore_filecontrol extends t3lib_TCEforms
 *   54:     function makeControl($pi_aParameterArray, $pi_oFormObj)
 *   69:     function getSingleField_typeGroup($table,$field,$row,&$PA,$tce)
 *
 * TOTAL FUNCTIONS: 2
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

/**
 * @desc Builds de file control used to manage the filegroups.
 * @author Pierrick Caillon <pierrick@in-cite.net>
 * @remarks Some code comes from TYPO3 core, t3lib_tceforms.
 */
class tx_icsoddatastore_filecontrol extends t3lib_TCEforms {
	/**
 * @param	array		$pi_aParameterArray: Informations à propos du champ à générer.
 * @param	object		$oFormObj: L'instanced e TCEforms demandant le rendu du contrôle.
 * @return	string		Le code HTML du contrôle.
 * @desc Génère le code du contrôle backend personnalisé d'envoie de fichier pour les filegroups.
 * @author Pierrick Caillon <pierrick@in-cite.net>
 */
	function makeControl($pi_aParameterArray, $pi_oFormObj) {
		return $this->getSingleField_typeGroup($pi_aParameterArray['table'], $pi_aParameterArray['field'], $pi_aParameterArray['row'], $pi_aParameterArray, $pi_oFormObj);
	}

	/**
	 * Generation of TCEform elements of the type "group"
	 * This will render a selectorbox into which elements from either the file system or database can be inserted. Relations.
	 *
	 * @param	string		$table: The table name of the record
	 * @param	string		$field: The field name which this element is supposed to edit
	 * @param	array		$row: The record data array where the value(s) for the field can be found
	 * @param	array		$PA: An array with additional configuration options.
	 * @param	t3lib_tceforms		$tce: The calling TCEforms instance.
	 * @return	string		The HTML code for the TCEform field
	 */
	function getSingleField_typeGroup($table,$field,$row,&$PA,$tce)	{
			// Init:
		$config = $PA['fieldConf']['config'];
		//$internal_type = $config['internal_type'];
		$show_thumbs = $config['show_thumbs'];
		$size = intval($config['size']);
		$maxitems = t3lib_div::intInRange($config['maxitems'],0);
		$minitems = t3lib_div::intInRange($config['minitems'],0);
		$allowed = trim($config['allowed']);
		$disallowed = trim($config['disallowed']);

		$disabled = '';
		if($tce->renderReadonly || $config['readOnly'])  {
			$disabled = ' disabled="disabled"';
		}

		$item.= '<input type="hidden" name="'.$PA['itemFormElName'].'_mul" value="'.($config['multiple']?1:0).'"'.$disabled.' />';
		$tce->registerRequiredProperty('range', $PA['itemFormElName'], array($minitems,$maxitems,'imgName'=>$table.'_'.$row['uid'].'_'.$field));
		$info='';

			// "Extra" configuration; Returns configuration for the field based on settings found in the "types" fieldlist. See http://typo3.org/documentation/document-library/doc_core_api/Wizards_Configuratio/.
		$specConf = $tce->getSpecConfFromString($PA['extra'], $PA['fieldConf']['defaultExtras']);

		//$config['uploadfolder'] = '';
					// Creating string showing allowed types:
				$tempFT = t3lib_div::trimExplode(',',$allowed,1);
				if (!count($tempFT))	{$info.='*';}
				foreach($tempFT as $ext)	{
					if ($ext)	{
						$info.=strtoupper($ext).' ';
					}
				}
					// Creating string, showing disallowed types:
				$tempFT_dis = t3lib_div::trimExplode(',',$disallowed,1);
				if (count($tempFT_dis))	{$info.='<br />';}
				foreach($tempFT_dis as $ext)	{
					if ($ext)	{
						$info.='-'.strtoupper($ext).' ';
					}
				}

					// Making the array of file items:
				$itemArray = t3lib_div::trimExplode(',',$PA['itemFormElValue'],1);

					// Showing thumbnails:
				$thumbsnail = '';
				if ($show_thumbs)	{
					$imgs = array();
					foreach($itemArray as $imgRead)	{
						$imgP = explode('|',$imgRead);
						$imgPath = rawurldecode($imgP[0]);

						$rowCopy = array();
						$rowCopy[$field] = $imgPath;

							// Icon + clickmenu:
						$absFilePath = t3lib_div::getFileAbsFileName(/*$config['uploadfolder'] ? $config['uploadfolder'] . '/' . $imgPath :*/ $imgPath);

						$fI = pathinfo($imgPath);
						$fileIcon = t3lib_BEfunc::getFileIcon(strtolower($fI['extension']));
						$fileIcon = '<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/fileicons/'.$fileIcon,'width="18" height="16"').' class="absmiddle" title="'.htmlspecialchars($fI['basename'].($absFilePath && @is_file($absFilePath) ? ' ('.t3lib_div::formatSize(filesize($absFilePath)).'bytes)' : ' - FILE NOT FOUND!')).'" alt="" />';

						$imgs[] = '<span class="nobr">'.t3lib_BEfunc::thumbCode($rowCopy,$table,$field,$tce->backPath,'thumbs.php',''/*$config['uploadfolder']*/,0,' align="middle"').
									($absFilePath ? $tce->getClickMenu($fileIcon, $absFilePath) : $fileIcon).
									$imgPath.
									'</span>';
					}
					$thumbsnail = implode('<br />',$imgs);
				}

					// Creating the element:
				$noList = isset($config['disable_controls']) && t3lib_div::inList($config['disable_controls'], 'list');
				$params = array(
					'size' => $size,
					'dontShowMoveIcons' => ($maxitems<=1),
					'autoSizeMax' => t3lib_div::intInRange($config['autoSizeMax'],0),
					'maxitems' => $maxitems,
					'style' => isset($config['selectedListStyle']) ? ' style="'.htmlspecialchars($config['selectedListStyle']).'"' : ' style="'.$this->defaultMultipleSelectorStyle.'"',
					'info' => $info,
					'thumbnails' => $thumbsnail,
					'readOnly' => $disabled,
					'noBrowser' => $noList || isset($config['disable_controls']) && t3lib_div::inList($config['disable_controls'], 'browser'),
					'noList' => $noList,
				);
				$item.= $tce->dbFileIcons($PA['itemFormElName'],'file',implode(',',$tempFT),$itemArray,'',$params,$PA['onFocus']);

				if(!$disabled && !(isset($config['disable_controls']) && t3lib_div::inList($config['disable_controls'], 'upload'))) {
						// Adding the upload field:
					if ($tce->edit_docModuleUpload && $config['uploadfolder']) {
						$item .= '<input type="file" name="' . $PA['itemFormElName_file'] . '"' . $tce->formWidth() . ' size="60" onchange="' . implode('', $PA['fieldChangeFunc']) . '" />';
					}
				}

			// Wizards:
		$altItem = '<input type="hidden" name="'.$PA['itemFormElName'].'" value="'.htmlspecialchars($PA['itemFormElValue']).'" />';
		if (!$disabled) {
			$item = $tce->renderWizards(array($item,$altItem),$config['wizards'],$table,$row,$field,$PA,$PA['itemFormElName'],$specConf);
		}

		return $item;
	}
}
?>