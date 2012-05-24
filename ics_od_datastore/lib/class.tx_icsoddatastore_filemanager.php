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
 * $Id: class.tx_icsoddatastore_filemanager.php 48432 2011-06-07 09:27:20Z emilieprudhomme $
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   49: class tx_icsoddatastore_filemanager
 *   67:     function processDatamap_preProcessFieldArray(&$pi_aIncommingFieldArray, $pi_sTable, $pi_sId, $pi_oTce)
 *  246:     function processDatamap_afterDatabaseOperations($pi_sStatus, $pi_sTable, $pi_sId, $pi_aFieldArray, $pi_oTce)
 *
 * TOTAL FUNCTIONS: 2
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

/**
 * Cette classe est enregistrée comme crochet (hook) de TCEmain.
 *          Actuellement, seul le champ file de tx_icsoddatastore_files est traité.
 *
 * @desc Gère le comportement du contrôle backend personnalisé d'envoie de fichier.
 * @author Pierrick Caillon <pierrick@in-cite.net>
 * @remarks Une amélioration serait de traiter tous les champs de type user utilisant le contrôle.
 */
class tx_icsoddatastore_filemanager {
	/**
 * Pré-traitement des valeurs de l'enregistrement soumis à TCEmain pour persistance.
 * Fait respecter l'emplacement courant du fichier lié et son préfix (l'id de l'enregistrement).
 * Prend en compte le fichier envoyé s'il y a et le pose à la place de tout fichier existant.
 * Le fichier existant s'il y a se voie inséré le timestamp de l'heure de la modification.
 *
 * Pour un nouvel enregistrement, le nom du fichier ne peut être changé car l'id n'existe pas.
 * Le fichier en vigueur est au format "<id>-<nom originel>".
 * Le fichier d'un ancien fichier est au format "<id>-<timestamp>-<nom originel>".
 *
 * @param	array		$pi_aIncommingFieldArray: Les données de l'enregistrement.
 * @param	string		$pi_sTable:	Le nom de la table affectée.
 * @param	string		$pi_sId: L'identifiant de l'enregistrement. Peut être une chaîne de caractère commençant par 'NEW'.
 * @param	object		$pi_oTce: L'instance de TCEmain effectuant la mise à jour de la base.
 * @return	void
 * @author Pierrick Caillon <pierrick@in-cite.net>
 */
	function processDatamap_preProcessFieldArray(&$pi_aIncommingFieldArray, $pi_sTable, $pi_sId, $pi_oTce) {
		if ($pi_oTce->bypassFileHandling)
			return;
		if ($pi_sTable == 'tx_icsoddatastore_files') {
			$_EXTCONF = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ics_od_datastore']);
			$sDocFolder = $_EXTCONF['docfolder'];
			if (isset($pi_aIncommingFieldArray['filegroup'])) {
				$sFilegroup = $pi_aIncommingFieldArray['filegroup'];
			}
			else {
				// $aRec = t3lib_BEfunc::getRecord($pi_sTable, $pi_sId);
				// $sFilegroup = $aRec['filegroup'];
				$aRes = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
					'uid_foreign',
					'tx_icsoddatastore_files_filegroups_mm',
					'uid_local = ' . $pi_sId
				);
				$sFilegroup = $aRes['0'];
			}
				// Clean the existing data: Rename and move the file if needed.
			if (isset($pi_aIncommingFieldArray['file'])) {
				if (!empty($pi_aIncommingFieldArray['file'])) {
					$sNewFilename = false;
					$sNewFolder = false;
					if (strpos($pi_aIncommingFieldArray['file'], $sDocFolder) === 0) {
						$sWid = dirname(substr($pi_aIncommingFieldArray['file'], strlen($sDocFolder)));
						$sFolder = dirname($pi_aIncommingFieldArray['file']);
						$sFilename = basename($pi_aIncommingFieldArray['file']);
						if (!preg_match('/^(\\d+)-/', $sFilename, $aMatches) && is_int($pi_sId)) {
							$sNewFilename = $pi_sId . '-' . $sFilename;
						}
						else if (is_int($pi_sId) && ($aMatches[1] != $pi_sId)) {
							$sNewFilename = $pi_sId . '-' . substr($sFilename, strlen($aMatches[0]));
						}
						if ($sWid != $sFilegroup) {
							$sNewFolder = $sDocFolder . $sFilegroup;
						}
					}
					else {
						t3lib_div::loadTCA($pi_sTable);
						if (is_file(t3lib_div::getFileAbsFileName($pi_aIncommingFieldArray['file'])))
							$sFolder = dirname($pi_aIncommingFieldArray['file']);
						else
							$sFolder = $GLOBALS['TCA'][$pi_sTable]['columns']['file']['config']['uploadfolder'];
						$sFilename = basename($pi_aIncommingFieldArray['file']);
						$sNewFilename = (is_int($pi_sId) ? ($pi_sId . '-') : ('')) . $sFilename;
						$sNewFolder = $sDocFolder . $sFilegroup;
					}
					if (!is_file(t3lib_div::getFileAbsFileName($sFolder . '/' . $sFilename))) {
						$pi_aIncommingFieldArray['file'] = '';
					}
					else if (($sNewFilename !== false) || ($sNewFolder !== false)) {
						if (rename(
								t3lib_div::getFileAbsFileName($sFolder . '/' . $sFilename),
								t3lib_div::getFileAbsFileName(
									($sNewFolder ? $sNewFolder : $sFolder) . '/' .
									($sNewFilename ? $sNewFilename : $sFilename)
								)
							)) {
							$pi_aIncommingFieldArray['file'] = ($sNewFolder ? $sNewFolder : $sFolder) . '/' .
								($sNewFilename ? $sNewFilename : $sFilename);
						}
					}
				}
			}
			$aUploadedFileArray = $pi_oTce->uploadedFileArray[$pi_sTable][$pi_sId]['file'];
				// Parse the uploaded file and move the file is exists.
			if (is_array($aUploadedFileArray) &&
				$aUploadedFileArray['name'] &&
				strcmp($aUploadedFileArray['name'], 'none')) {
				$pi_oTce->alternativeFileName[$aUploadedFileArray['tmp_name']] = $aUploadedFileArray['name'];
				if (!$pi_oTce->fileFunc)	{
					$pi_oTce->fileFunc = t3lib_div::makeInstance('t3lib_basicFileFunctions');
					$pi_oTce->include_filefunctions = 1;
				}
				t3lib_div::loadTCA($pi_sTable);
				$aTcaFieldConf = $GLOBALS['TCA'][$pi_sTable]['columns']['file']['config'];
					// Setting permitted extensions.
				$aAllFiles = Array();
				$aAllFiles['webspace']['allow'] = $aTcaFieldConf['allowed'];
				$aAllFiles['webspace']['deny'] = $aTcaFieldConf['disallowed'] ? $aTcaFieldConf['disallowed'] : '*';
				$aAllFiles['ftpspace'] = $aAllFiles['webspace'];
				$pi_oTce->fileFunc->init('', $aAllFiles);

					// For logging..
				$aPropArr = $pi_oTce->getRecordProperties($pi_sTable,$pi_sId);
				$sRecFID = $pi_sTable . ':' . $pi_sId . ':file';

				$sDest = $sDocFolder . $sFilegroup . '/';

				if (@is_dir(t3lib_div::getFileAbsFileName($sDest)) && (@is_file($aUploadedFileArray['tmp_name']) || @is_uploaded_file($aUploadedFileArray['tmp_name']))) {
					$sTargetFileName = (is_int($pi_sId) ? ($pi_sId . '-') : ('')) . basename(t3lib_div::fixWindowsFilePath($aUploadedFileArray['name']));
					$aFI = t3lib_div::split_fileref($sTargetFileName);
						// Check for allowed extension:
					if ($pi_oTce->fileFunc->checkIfAllowed($aFI['fileext'], $sDest, $sTargetFileName)) {
						$sTmpName = uniqid('upload');
						$sDestFile = t3lib_div::getFileAbsFileName($sDest . $sTmpName);
						t3lib_div::upload_copy_move($aUploadedFileArray['tmp_name'],$sDestFile);
						//$this->copiedFileMap[$theFile] = $theDestFile;
						clearstatcache();
						if (!@is_file($sDestFile)) {
							$pi_oTce->log($pi_sTable,$pi_sId,5,0,1,"Copying file '%s' failed!: The destination path (%s) may be write protected. Please make it write enabled!. (%s)",16,array($aUploadedFileArray['tmp_name'], $sDest, $sRecFID),$aPropArr['event_pid']);
							$sTmpName = '';
						}
					}
					else $pi_oTce->log($pi_sTable,$pi_sId,5,0,1,"Fileextension '%s' not allowed. (%s)",12,array($aFI['fileext'], $sRecFID),$aPropArr['event_pid']);
				}
				else $pi_oTce->log($pi_sTable,$pi_sId,5,0,1,'The destination (%s) or the source file (%s) does not exist. (%s)',14,array($sDest, $aUploadedFileArray['tmp_name'], $sRecFID),$aPropArr['event_pid']);
			}
				// Rename the old file with timestamp if needed. Rename the uploaded file with its final name and register it.
			if (!empty($sTmpName)) {
				if (isset($pi_aIncommingFieldArray['file'])) {
					if (!empty($pi_aIncommingFieldArray['file'])) {
						$sNewFilename = false;
						if (strpos($pi_aIncommingFieldArray['file'], $sDocFolder) === 0) {
							$sFolder = dirname($pi_aIncommingFieldArray['file']);
							$sFilename = basename($pi_aIncommingFieldArray['file']);
							// if (!preg_match('/^(\\d+)-/', $sFilename, $aMatches)) {
								// $sNewFilename = $pi_sId . '-' . time() . '-' . $sFilename;
							// }
							// else if (is_int($pi_sId)) {
								// $sNewFilename = $pi_sId . '-' . time() . '-' . substr($sFilename, strlen($aMatches[0]));
							// }
						}
						if (!is_file(t3lib_div::getFileAbsFileName($sFolder . '/' . $sFilename))) {
							$pi_aIncommingFieldArray['file'] = '';
						}
						else if ($sNewFilename !== false) {
							if (rename(
									t3lib_div::getFileAbsFileName($sFolder . '/' . $sFilename),
									t3lib_div::getFileAbsFileName($sFolder . '/' . $sNewFilename)
								)) {
								$pi_aIncommingFieldArray['file'] = $sFolder . '/' . $sNewFilename;
							}
						}
					}
				}
				if (rename(
						t3lib_div::getFileAbsFileName($sDestFile),
						t3lib_div::getFileAbsFileName($sDest . $sTargetFileName)
					)) {
					$pi_aIncommingFieldArray['file'] = $sDest . $sTargetFileName;
				}
			}
		}
	}

	/**
	 * Post-traitement des valeurs de l'enregistrement soumis à TCEmain pour persistance.
	 * Rempli le champ indiquant l'utilisateur aillant édité.
	 *
	 * @author Pierrick Caillon <pierrick@in-cite.net>
	 * @param string	$pi_sStatus: Indication de la création ou de la mise à jour de l'enregistrement.
	 * @param array		$pi_aFieldArray: Les données de l'enregistrement.
	 * @param string	$pi_sTable: Le nom de la table affectée.
	 * @param string	$pi_sId: L'identifiant de l'enregistrement. Peut être une chaîne de caractère commençant par 'NEW'.
	 * @param object	$pi_oTce: L'instance de TCEmain effectuant la mise à jour de la base.
	 */
	// function processDatamap_postProcessFieldArray($pi_sStatus, $pi_sTable, $pi_sId, &$pi_aFieldArray, $pi_oTce) {
		// if ($pi_sTable == 'tx_icsoddatastore_files') {
			// if ((count($pi_aFieldArray) > ((isset($pi_aFieldArray[$GLOBALS['TCA']['tx_icsoddatastore_files']['ctrl']['enablecolumns']['disabled']])) ? (1) : (0))) && is_numeric($pi_sId)) {
				// error_log($GLOBALS['BE_USER']->user[$GLOBALS['BE_USER']->userid_column]);
				// $pi_aFieldArray['changedby'] = $GLOBALS['BE_USER']->user[$GLOBALS['BE_USER']->userid_column];
			// }
		// }
	// }

	/**
	 * Post-traitement des enregistrements traités par TCEmain après la modification de la base.
	 * Crée le dossier de sauvegarde des fichiers pour la fiche créé ou mise à jour.
	 *
	 * @param	string		$pi_sStatus: Indication de la création ou de la mise à jour de l'enregistrement.
	 * @param	string		$pi_sTable: Nom de la table affectée.
	 * @param	string		$pi_sId: Identifiant de l'enregistrement concerné.
	 * @param	array		$pi_aFieldArray: Données de l'enregistrement.
	 * @param	object		$pi_oTce: L'instance de TCEmain ayant effectué la mise à jour de la base.
	 * @return	void
	 * @author Pierrick Caillon <pierrick@in-cite.net>
	 */
	function processDatamap_afterDatabaseOperations($pi_sStatus, $pi_sTable, $pi_sId, $pi_aFieldArray, $pi_oTce) {
		if ($pi_sTable == 'tx_icsoddatastore_filegroups') {
			if (isset($pi_oTce->substNEWwithIDs[$pi_sId]))
				$pi_sId = $pi_oTce->substNEWwithIDs[$pi_sId];
			$_EXTCONF = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ics_od_datastore']);
			$sFolder = $_EXTCONF['docfolder'] . $pi_sId . '/';
			t3lib_div::mkdir_deep(PATH_site, $sFolder);
		}
	}
}
?>