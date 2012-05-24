<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Popy (popy.dev@gmail.com)
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
 * $Id: class.tx_icsoddatastore_pi2.php 48432 2011-06-07 09:27:20Z emilieprudhomme $
 */
/**
 * 'pp_rsslatestcontent' extension.
 *
 * @author	Popy <popy.dev@gmail.com>
 *
 * Adapted by In Cité Solution <technique@in-cite.net> for plugin 'Datastore RSS' for the 'ics_od_datastore' extension.
 *
 */



require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_icsoddatastore_pi2 extends tslib_pibase{
	var $prefixId      = 'tx_icsoddatastore_pi2';		// Same as class name
	var $scriptRelPath = 'pi2/class.tx_icsoddatastore_pi2.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'ics_od_datastore';	// The extension key.

	/**
	 * Render datastore rss feed
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The		rss feed content that is displayed on the website
	 */
	function renderSingleRssPage($content,$conf) 	{
		$this->conf = $conf;

		/* Get the template */
		$this->templateFile = $this->conf['templateFile'];
		if (!$this->templateFile)
			$this->templateFile = 'typo3conf/ext/ics_od_datastore/res/rss2_tmplFile.tmpl';

		/* Declarations */
		$rssId = intval(t3lib_div::_GP('rssFeed'));
		$this->config = $GLOBALS['TSFE']->config['config']['datastore_rss.'];
		$this->feed = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			'tx_icsoddatastore_filegroups',
			'1' . t3lib_BEfunc::deleteClause('tx_icsoddatastore_filegroups') . t3lib_BEfunc::BEenableFields('tx_icsoddatastore_filegroups'),
			'',
			'tstamp DESC',
			'10'
		);

		$pubDate = $this->feed[0]['tstamp'];

		$this->siteUrl = t3lib_div::getIndpEnv('TYPO3_SITE_URL');
		$this->charset = trim($GLOBALS['TSFE']->config['config']['renderCharset'])? $GLOBALS['TSFE']->config['config']['renderCharset'] : $GLOBALS['TSFE']->config['config']['metaCharset'];

		$tmpl = $this->cObj->fileResource($this->templateFile);
		$template['TEMPLATE_RSS2'] = $this->cObj->getSubpart($tmpl, '###TEMPLATE_RSS2###');
		$markers = array();

		/* RSS feed header */
		$template['HEADER'] = $this->cObj->getSubpart($template['TEMPLATE_RSS2'], '###HEADER###');

		$markers = array(
			'###RSS_DECLARATION###' => ($this->conf['displayRSS.']['rssDeclaration'])? $this->conf['displayRSS.']['rssDeclaration'] : '<rss version="2.0">',
			'###SITE_TITLE###' => htmlspecialchars($GLOBALS['TSFE']->tmpl->setup['sitetitle']),
			'###SITE_LINK###' => htmlspecialchars($this->siteUrl),
			'###SITE_DESCRIPTION###' => htmlspecialchars($this->conf['displayRSS.']['description']),
			'###LANGUAGE###' => htmlspecialchars($this->conf['displayRSS.']['language']),
			'###COPYRIGHT###' => htmlspecialchars($this->conf['displayRSS.']['copyright']),
			'###MANAGINGEDITOR###' => htmlspecialchars($this->conf['displayRSS.']['managingeditor']),
			'###WEBMASTER###' => htmlspecialchars($this->conf['displayRSS.']['webMaster']),
			'###PUBDATE###' => htmlspecialchars(date('r', $pubDate)),
			'###GENERATOR###' => htmlspecialchars($this->conf['displayRSS.']['generator']),
			'###DOCS###' => htmlspecialchars($this->conf['displayRSS.']['docs']),
			'###CLOUD###' => $this->conf['displayRSS.']['cloud'], // like <cloud domain="rpc.sys.com" port="80" path="/RPC2" registerProcedure="myCloud.rssPleaseNotify" protocol="xml-rpc" />
			'###TTL###' => htmlspecialchars($this->conf['displayRSS.']['ttl']),
			'###RATING###' => htmlspecialchars($this->conf['displayRSS.']['rating']),
			'###SKIPHOURS###' => '',
			'###SKIPDAYS###' => '',
		);

		/* Remove not used balise */
		$subpartArray = array();

		$fieldsNotEmpty = array('site_description', 'language', 'description', 'copyright', 'managingeditor', 'webMaster', 'generator', 'docs', 'ttl','rating', 'cloud');
		foreach ($fieldsNotEmpty as $field) {
			$upper = strtoupper($field);
			if (!$this->conf['displayRSS.'][$field]) {
				$subpartArray['###SUBPART_' . $upper . '###'] = '';
			} else {
				$subpart_field = $this->cObj->getSubpart($template['HEADER'], '###SUBPART_' . $upper . '###');
				$subpartArray['###SUBPART_' . $upper . '###'] = $this->cObj->substituteMarkerArray($subpart_field, array('###'. $upper . '###' => $markers['###' . $upper . '###']));
			}
		}

		/* RSS feed header's image */
		$template['IMAGE'] = '';
		if ($this->config['siteLogo'] || $this->config['siteLogo.']) {
			$imgPath='';
			if ($this->config['siteLogo.']['relativeUrl']) {
				$imgPath=$this->siteUrl;
			}
			$imgPath.=$this->cObj->stdWrap(
				$this->config['siteLogo'],
				$this->config['siteLogo.']
				);

			$template['IMAGE'] = $this->cObj->getSubpart($template['TEMPLATE_RSS2'], '###IMAGE###');
			$markersImage = array(
				'###SITE_TITLE###' => htmlspecialchars($GLOBALS['TSFE']->tmpl->setup['sitetitle']),
				'###IMGPATH###' => htmlspecialchars($imgPath),
				'###SITE_LINK###' => htmlspecialchars($this->siteUrl),
				'###IMG_WIDTH###' => '',
				'###IMG_HEIGHT###' => '',
				'###SITE_DESCRIPTION###' => ($this->conf['displayRSS.']['description'])? htmlspecialchars($this->conf['displayRSS.']['description']) : '',
			);
			$template['IMAGE'] = $this->cObj->substituteMarkerArray($template['IMAGE'], $markersImage);
		}
		$template['HEADER'] = $this->cObj->substituteSubpart($template['HEADER'], '###IMAGE###', $template['IMAGE']);


		/* RSS feed header's textInput */
		$template['TEXTINPUT'] = '';
		if ($this->conf['displayRSS.']['textInput.'])	{
			$template['TEXTINPUT'] = $this->cObj->getSubpart($template['TEMPLATE_RSS2'], '###TEXTINPUT###');
			$markersTextInput = array(
				'###TEXTINPUT_TITLE###' => htmlspecialchars($this->conf['displayRSS.']['textInput.']['title']),
				'###TEXTINPUT_DESCRIPTION###' => htmlspecialchars($this->conf['displayRSS.']['textInput.']['description']),
				'###TEXTINPUT_NAME###' => htmlspecialchars($this->conf['displayRSS.']['textInput.']['name']),
				'###TEXTINPUT_LINK###' => htmlspecialchars($this->conf['displayRSS.']['textInput.']['link']),
			);
			$template['TEXTINPUT'] =  $this->cObj->substituteMarkerArray($template['TEXTINPUT'], $markersTextInput);
		}
		$template['HEADER'] = $this->cObj->substituteSubpart($template['HEADER'], '###TEXTINPUT###', $template['TEXTINPUT']);

		/* RSS feed header skipHours & skipDays */
		if ($this->conf['displayRSS.']['skipHours.'])	{
			$skipHours = t3lib_div::trimExplode(',', $this->conf['displayRSS.']['skipHours.'], true);
			foreach ($skipHours as $hour)	{
				$skipHours_content .= '<hour>' . htmlspecialchars($hour) . '</hour>';
			}
			$markers['###SKIPHOURS###'] = $skipHours_content;
		}
		if ($this->conf['displayRSS.']['skipDays.'])	{
			$skipDays = t3lib_div::trimExplode(',', $this->conf['displayRSS.']['skipDays.'], true);
			foreach ($skipDays as $day)	{
				$skipDays_content .= '<day>' . htmlspecialchars($day) . '</day>';
			}
			$markers['###SKIPDAYS###'] = $skipDays_content;
		}
		$template['HEADER'] = $this->cObj->substituteMarkerArrayCached($template['HEADER'], $markers, $subpartArray);

		/* RSS feed content */
		$template['CONTENT'] = $this->cObj->getSubpart($template['TEMPLATE_RSS2'], '###CONTENT###');
		$template['DATASET'] = $this->cObj->getSubpart($template['CONTENT'], '###DATASET###');

		$subpart_author = $this->cObj->getSubpart($template['DATASET'], '###SUBPART_AUTHOR###');
		$datasets = '';
		foreach ($this->feed as $item) {
			$linkArray = array();
			$subpartArray = array();

			if ($this->conf['displayRSS.']['items.']['link.'])	{
				foreach ($this->conf['displayRSS.']['items.']['link.'] as $key=>$param)	{
					if ($key == 'item.')
						$linkArray[$param['key']] = $item['uid'];
					else
						$linkArray[$param['key']] = $param['value'];
				}
			}
			$author = $this->getDatastoreAuthor($item['creator']);
			if (!$author) {
				$subpartArray['###SUBPART_AUTHOR###'] = '';
				$author = '';
			} else {
				$author = htmlspecialchars($this->utf8_csConv($author));
				$subpartArray['###SUBPART_AUTHOR###'] = $this->cObj->substituteMarkerArray($subpart_author, array('###DATASET_AUTHOR###' => $author));
			}

			$markersDataset = array(
				'###DATASET_TITLE###' => htmlspecialchars($item['title']),
				'###DATASET_LINK###' => htmlspecialchars(t3lib_div::linkThisUrl($this->siteUrl, $linkArray)),
				'###DATASET_DESCRIPTION###' => htmlspecialchars($this->utf8_csConv($item['description'])),
				'###DATASET_AUTHOR###' => $author,
				'###DATASET_PUBDATE###' => htmlspecialchars(date('r', $item['tstamp'])),
			);

			// Hook for add fields markers
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['additionalFieldsRSSMarkers'])) {
				foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['additionalFieldsRSSMarkers'] as $_classRef) {
					$_procObj = & t3lib_div::getUserObj($_classRef);
					$_procObj->additionalFieldsRSSMarkers($markersDataset, $subpartArray, $template['DATASET'], $item, $this->conf, $this);
				}
			}
			$datasets .= $this->cObj->substituteMarkerArrayCached($template['DATASET'], $markersDataset, $subpartArray);

		}
		$template['CONTENT'] = $this->cObj->substituteSubpart($template['CONTENT'], '###DATASET###', $datasets);

		//--
		return $this->cObj->substituteMarkerArrayCached(
			$template['TEMPLATE_RSS2'],
			$markers,
			array(
				'###HEADER###' => $template['HEADER'],
				'###CONTENT###' => $template['CONTENT'],
			)
		);
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


	/**
	 * Convert from utf-8 to charset
	 *
	 * @param	string		$content: The content to convert
	 * @return	The		content converted
	 */
	function utf8_csConv($content)	{
		if (strtoupper($this->charset) != 'UTF-8')
			return $GLOBALS['TSFE']->csConvObj->conv($content, 'utf-8', $this->charset);
		return $content;
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_od_datastore/class.tx_icsoddatastore_pi2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_od_datastore/class.tx_icsoddatastore_pi2.php']);
}

?>