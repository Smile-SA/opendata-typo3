<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011  <>
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
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   53: class tx_smileicsoddatastorerss_pi1 extends tslib_pibase
 *   70:     function main($content, $conf)
 *   97:     function init()
 *  122:     function getFileGroups()
 *  141:     function getFileGroupsHighlighted()
 *  159:     function renderXml(&$datas)
 *  228:     function getDatastoreFormat($filegroupUid)
 *  253:     function getDatastoreAuthor($uid)
 *
 * TOTAL FUNCTIONS: 7
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'RSS export' for the 'smile_icsoddatastore' extension.
 *
 * @author	 <>
 * @package	TYPO3
 * @subpackage	tx_smileicsoddatastore
 */
class tx_smileicsoddatastorerss_pi1 extends tslib_pibase {
	var $prefixId	  = 'tx_smileicsoddatastorerss_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_smileicsoddatastorerss_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey		= 'smile_icsoddatastorerss';	// The extension key.

	var $view;
	var $templateFile;
	var $limit;
	var $datastorePid;

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The		content that is displayed on the website
	 */
	function main($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj = 1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!

		$this->init();

		$data = array();
		switch ($this->view) {
			case 'HIGHLIGHTED' :
				$data = $this->getFileGroupsHighlighted();
				break;
			case 'ALL' :
				$data = $this->getFileGroups();
				break;
		}
		$content = $this->renderXml($data);

		return $content;
	}

	/**
	 * Initialize
	 *
	 * @return	[type]		...
	 */
	function init() {
		// Flexform
		$this->pi_initPIflexForm();
		$this->view = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'view', 'configuration');
		$this->templateFile = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'templateFile', 'configuration');
		$this->limit = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'limit', 'configuration');
		$this->datastorePid = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'datastorePid', 'configuration');
		// TypoScript
		$this->view = $this->view ? $this->view : $this->conf['view'];
		$this->templateFile = $this->templateFile ? $this->templateFile : $this->conf['templateFile'];
		$this->limit = $this->limit ? $this->limit : $this->conf['limit'];
		$this->datastorePid = $this->datastorePid ? $this->datastorePid : $this->conf['datastorePid'];
		// Default
		$this->templateFile = $this->templateFile ? $this->templateFile : 'EXT:smile_icsoddatastore_rss/res/template.html';
		$this->limit = $this->limit ? $this->limit : '10';
		$this->datastorePid = $this->datastorePid ? intval($this->datastorePid) : 0;
	}

	/**
	 * Retrieves last update file groups
	 *
	 * return	array
	 *
	 * @return	[type]		...
	 */
	function getFileGroups() {
		$data = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			'tx_icsoddatastore_filegroups',
			'1'.$this->cObj->enableFields('tx_icsoddatastore_filegroups'),
			'',
			'update_date DESC',
			$this->limit
		);
		return $data;
	}

	/**
	 * Retrieves highlighted file groups
	 *
	 * return	array
	 *
	 * @return	[type]		...
	 */
	function getFileGroupsHighlighted() {
		$data = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			'tx_icsoddatastore_filegroups',
			'tx_icsoddatastore_filegroups.tx_smileicsoddatastorerss_highlight=1'.$this->cObj->enableFields('tx_icsoddatastore_filegroups'),
			'',
			'update_date DESC',
			$this->limit
		);
		return $data;
	}

	/**
	 * Render RSS stream
	 *
	 * @param	array		$data
	 * @return	[type]		...
	 */
	function renderXml(&$datas) {
		$tpl = $this->cObj->fileResource($this->templateFile);
		$tpl = $this->cObj->getSubpart($tpl, '###TEMPLATE_RSS2###');

		// Header
		$tplHeader = $this->cObj->getSubpart($tpl, '###HEADER###');
		$markerArrayHeader = array(
			'###SITE_TITLE###' => htmlspecialchars($GLOBALS['TSFE']->tmpl->setup['sitetitle']),
			'###SITE_LINK###' => htmlspecialchars(t3lib_div::getIndpEnv('TYPO3_SITE_URL')),
			'###SITE_DESCRIPTION###' => $this->cObj->stdWrap($this->conf['site_description'], $this->conf['site_description_stdWrap.']),
			'###SITE_LANG###' => $this->cObj->stdWrap($this->conf['site_lang'], $this->conf['site_lang_stdWrap.']),
			'###GENERATOR###' => $this->cObj->stdWrap($this->conf['generator'], $this->conf['generator_stdWrap.']),
			'###DOCS###' => $this->cObj->stdWrap($this->conf['docs'], $this->conf['docs_stdWrap.']),
			'###COPYRIGHT###' => $this->cObj->stdWrap($this->conf['copyright'], $this->conf['copyright_stdWrap.']),
			'###WEBMASTER###' => $this->cObj->stdWrap($this->conf['webmaster'], $this->conf['webmaster_stdWrap.']),
			'###MANAGINGEDITOR###' => $this->cObj->stdWrap($this->conf['managingeditor'], $this->conf['managingeditor_stdWrap.']),
			'###LASTBUILD###' => $this->cObj->stdWrap(htmlspecialchars(date('r')), $this->conf['lastbuild_stdWrap.']),
		);
		$contentHeader = $this->cObj->substituteMarkerArray($tplHeader, $markerArrayHeader);

		// Items
		$tplContent = $this->cObj->getSubpart($tpl, '###CONTENT###');
		$tplContentItem = $this->cObj->getSubpart($tplContent, '###ITEM###');
		$contentContentItems = '';
		$markerArrayItem = array();
		foreach ($datas as $data) {
			if (is_array($data)) {
				$markerArrayItem['###ITEM_TITLE###'] = htmlspecialchars($data['title']);
				$markerArrayItem['###ITEM_LINK###'] = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$this->pi_getPageLink($this->datastorePid, '', array('tx_icsoddatastore_pi1' => array('uid' => $data['uid'])));
				$markerArrayItem['###ITEM_DESCRIPTION###'] = htmlspecialchars($data['description']);
				$markerArrayItem['###ITEM_DATE###'] = htmlspecialchars(date('r', $data['tstamp']));
				$markerArrayItem['###ITEM_AUTHOR###'] = $this->cObj->stdWrap(htmlspecialchars($this->getDatastoreAuthor($data['creator'])), $this->conf['author_stdWrap.']);
				$markerArrayItem['###ITEM_FORMAT###'] = $this->cObj->stdWrap(htmlspecialchars($this->getDatastoreFormat($data['uid'])), $this->conf['format_stdWrap.']);
				$markerArrayItem['###ITEM_CATEGORY###'] = '';

				// Hook for add fields markers
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['additionalFieldsRSSMarkers'])) {
					foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['additionalFieldsRSSMarkers'] as $_classRef) {
						$_procObj = & t3lib_div::getUserObj($_classRef);
						$_procObj->additionalFieldsRSSMarkers($markerArrayItem, $subpartItem, $tplContentItem, $data, $this->conf, $this);
					}
				}
			}

			if (isset($markerArrayItem['###CATEGORIES_VALUE###']) && $markerArrayItem['###CATEGORIES_VALUE###']!='') {
				$markerArrayItem['###ITEM_CATEGORY###'] = $this->cObj->stdWrap(htmlspecialchars(trim($markerArrayItem['###CATEGORIES_VALUE###'])), $this->conf['category_stdWrap.']);
			}

			$contentContentItems .= $this->cObj->substituteMarkerArray($tplContentItem, $markerArrayItem);
		}
		$contentContent = $this->cObj->substituteSubpart($tplContent, '###ITEM###', $contentContentItems);

		// global
		$markerArray = array();
		$subpartArray = array(
			'###HEADER###' => $contentHeader,
			'###CONTENT###' => $contentContent,
		);
		$content = $this->cObj->substituteMarkerArrayCached($tpl, $markerArray, $subpartArray);

		return $content;
	}

	/**
	 * Retrieves datastore dataset's file fomats
	 *
	 * @param	unknown_type		$filegroupUid
	 * @return	[type]		...
	 */
	function getDatastoreFormat($filegroupUid) {

		$mmWhere = 'tx_icsoddatastore_files.uid=tx_icsoddatastore_files_filegroup_mm.uid_local AND tx_icsoddatastore_filegroups.uid=tx_icsoddatastore_files_filegroup_mm.uid_foreign AND tx_icsoddatastore_fileformats.uid=tx_icsoddatastore_files.format';
		$whereClause = 'AND tx_icsoddatastore_filegroups.uid='.$filegroupUid.$this->cObj->enableFields('tx_icsoddatastore_filegroups').$this->cObj->enableFields('tx_icsoddatastore_fileformats');
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'DISTINCT tx_icsoddatastore_fileformats.name',
			'tx_icsoddatastore_files,tx_icsoddatastore_filegroups,tx_icsoddatastore_files_filegroup_mm,tx_icsoddatastore_fileformats',
			$mmWhere . ' ' . $whereClause,
			'',
			'tx_icsoddatastore_fileformats.name'
		);

		$formats = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$formats[] = $row['name'];
		}
		return implode(', ', $formats);
	}

	/**
	 * Retrieves datastore dataset's author
	 *
	 * @param	int		$uid: The author uid
	 * @return	string		The author's name
	 */
	function getDatastoreAuthor($uid)	{
		$author = t3lib_BEfunc::getrecord(
			'tx_icsoddatastore_tiers',
			$uid,
			'name',
			t3lib_BEfunc::BEenableFields('tx_icsoddatastore_tiers')
		);
		return $author['name'];
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/smile_icsoddatastore_rss/pi1/class.tx_smileicsoddatastorerss_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/smile_icsoddatastore_rss/pi1/class.tx_smileicsoddatastorerss_pi1.php']);
}

?>