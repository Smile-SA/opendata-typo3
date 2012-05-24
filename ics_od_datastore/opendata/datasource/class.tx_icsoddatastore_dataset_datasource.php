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
 * $Id: class.tx_icsoddatastore_dataset_datasource.php 48432 2011-06-07 09:27:20Z emilieprudhomme $
 */

require_once(t3lib_extMgm::extPath('ics_od_datastore') . 'opendata/datasource/tx_icsoddatastore_sourceconnexion.php');


/**
 * Short description of the command
 *
 * @file class.tx_icsoddatastore_dataset_datasource.php
 * @author    In Cité Solution <technique@in-cite.net>
 * @package    TYPO3.ics_od_datastore
 */
class tx_icsoddatastore_dataset_datasource
{
	// *************************
	// * User inclusions 0
	// * DO NOT DELETE OR CHANGE THOSE COMMENTS
	// *************************

	// ... (Add additional operations here) ...
	static $filetypes = array(
		'doc' => 'DOC, DOCUMENTATION',
		'data' => 'DATA, DONNEES, DONNÉES',
		'metadata' => 'METADATA, METADONNEES, MÉTADONNÉES',
	);
	// * End user inclusions 0


	private $_datasourceDB = null;

	public function __construct()
	{
		$this->_datasourceDB = typo3db_opendatapkg_connect();
		// *************************
		// * User inclusions constructor
		// * DO NOT DELETE OR CHANGE THOSE COMMENTS
		// *************************

		// ... (Add additional operations here) ...

		// * End user inclusions constructor
	}

	/**
	 * Retrieves datasource's records
	 *
	 * @param	array		$queryarray	The query array to query on database
	 * @return	array		Array of records
	 */
	public function get($queryarray)
	{
		$datasets = $this->_datasourceDB->exec_SELECTgetRows(
			$queryarray['fields'],
			$queryarray['fromtable'],
			$queryarray['where'],
			$queryarray['groupby'],
			$queryarray['order'],
			$queryarray['limit'],
			'id'
		);

		return $datasets;
	} // End get

	/**
	 * Process datasets files
	 *
	 * @param	array		$datasets Datasets with files to process
	 * @param	string		$queryfiles
	 * @return	array		$datasets Datasets with files processed
	 */
	protected function process_datasetFile($datasets, $queryfiles)
	{
		global $TCA;
		t3lib_div::loadTCA('tx_icsoddatastore_files');
		$uploadPath = $TCA['tx_icsoddatastore_files']['columns']['file']['config']['uploadfolder'] . '/';

		$ids = array_keys($datasets);
		foreach ($ids as $id)
		{
			// Get datasets files
			$files_filegroup_mm = $this->_datasourceDB->exec_SELECTgetRows(
				'`tx_icsoddatastore_files_filegroup_mm`.`uid_local`',
				'`tx_icsoddatastore_files_filegroup_mm`',
				'`uid_foreign`=' . $id
			);
			$datasets[$id]['files'] = array();
			foreach ($files_filegroup_mm as $file_mm)
			{
				$where = '1' . t3lib_BEfunc::deleteClause('tx_icsoddatastore_files') . t3lib_BEfunc::BEenableFields('tx_icsoddatastore_files');
				$where .= ' AND `tx_icsoddatastore_files`.`uid` = ' . $file_mm['uid_local'];
				if ($queryfiles)
					$where .= ' AND ' . $queryfiles;

				$files = $this->_datasourceDB->exec_SELECTgetRows(
					'`tx_icsoddatastore_files`.`uid` as `id`,
					`tx_icsoddatastore_files`.`record_type`,
					`tx_icsoddatastore_files`.`file`,
					`tx_icsoddatastore_files`.`url`,
					`tx_icsoddatastore_files`.`format`,
					`tx_icsoddatastore_files`.`type`,
					`tx_icsoddatastore_files`.`md5`',
					'`tx_icsoddatastore_files`',
					$where
				);
				if (is_array($files) && !empty($files))
				{
					$file = $files[0];

					//Get file's type
					$types = $this->_datasourceDB->exec_SELECTgetRows(
						'`tx_icsoddatastore_filetypes`.`name`',
						'`tx_icsoddatastore_filetypes`',
						'1' . t3lib_BEfunc::deleteClause('tx_icsoddatastore_filetypes') . t3lib_BEfunc::BEenableFields('tx_icsoddatastore_filetypes') . ' AND `tx_icsoddatastore_filetypes`.`uid` = ' . $file['type']
					);
					$file['type'] = null;
					foreach (tx_icsoddatastore_dataset_datasource::$filetypes as $type=>$typeValue)
					{
						$typeValue = t3lib_div::trimExplode(',', $typeValue, true);
						if (in_array(mb_strtoupper($types[0]['name'], 'UTF-8'), $typeValue))
						{
							$file['type'] = $type;
						}
					}

					// Get file's format
					$formats = $this->_datasourceDB->exec_SELECTgetRows(
						'`tx_icsoddatastore_fileformats`.`name`',
						'`tx_icsoddatastore_fileformats`',
						'1' . t3lib_BEfunc::deleteClause('tx_icsoddatastore_fileformats') . t3lib_BEfunc::BEenableFields('tx_icsoddatastore_fileformats') . ' AND `tx_icsoddatastore_fileformats`.`uid` = ' . $file['format']
					);
					$file['format'] = $formats[0]['name'];

					// Get file's path and file size
					if ($file['record_type'] == 0)
					{
						$file['url'] = t3lib_div::getIndpEnv('TYPO3_SITE_URL') . $file['file'];
						$file['size'] = filesize($uploadPath . '/' . $file['file']);
					}
					else
					{
						$file['size'] = 0;
					}
					$datasets[$id]['files'][] = $file;
				}
			}
		}
		return $datasets;
	}

	/**
	 * Retrieves datasource's records
	 *
	 * @param	array		$params	The parameters to query on database
	 * @return	array		Array of records
	 */
	public function getDatasetsAll($params)
	{
		$queryarray = array();
		$queryarray['fields'] =
			'`tx_icsoddatastore_filegroups`.`uid` AS `id`, ' .
			'`tx_icsoddatastore_filegroups`.`title` AS `title`, ' .
			'`tx_icsoddatastore_filegroups`.`description` AS `description`, ' .
			'`tx_icsoddatastore_filegroups`.`files` AS `files`, ' .
			'`tx_icsoddatastore_filegroups`.`agency` AS `agency`, ' .
			'`tx_icsoddatastore_filegroups`.`contact` AS `contact`, ' .
			'`tx_icsoddatastore_filegroups`.`licence` AS `licence`, ' .
			'`tx_icsoddatastore_filegroups`.`release_date` AS `released`, ' .
			'`tx_icsoddatastore_filegroups`.`update_date` AS `updated`, ' .
			'`tx_icsoddatastore_filegroups`.`time_period` AS `time_period`, ' .
			'`tx_icsoddatastore_filegroups`.`update_frequency` AS `frequency`, ' .
			'`tx_icsoddatastore_filegroups`.`publisher` AS `publisher`, ' .
			'`tx_icsoddatastore_filegroups`.`creator` AS `author`, ' .
			'`tx_icsoddatastore_filegroups`.`manager` AS `manager`, ' .
			'`tx_icsoddatastore_filegroups`.`owner` AS `owner`, ' .
			'`tx_icsoddatastore_filegroups`.`technical_data` AS `technical_data`';
		$queryarray['fromtable'] =
			'`tx_icsoddatastore_filegroups`';
		$queryarray['where'] =
			'1' . t3lib_BEfunc::deleteClause('tx_icsoddatastore_filegroups') . t3lib_BEfunc::BEenableFields('tx_icsoddatastore_filegroups');
		$queryarray['groupby'] =
			'';
		$queryarray['order'] =
			'';
		$queryarray['limit'] =
			'';
		// *************************
		// * User inclusions All
		// * DO NOT DELETE OR CHANGE THOSE COMMENTS
		// *************************

		// ... (Add additional operations here) ...

		// * End user inclusions All

		return $this->get($queryarray);
	} // End getDatasetsAll

	/**
	 * Retrieves datasets
	 *
	 * @param	array		$params The command parameters
	 * @param	boolean		$process_file Set to get files data
	 * @return	array		The datasets and the number of datasets whithout sql query limit
	 */
	public function getDadasetsFilter($params, $process_file = true)
	{
		$queryarray = array();
		$queryarray['fields'] =
			'`tx_icsoddatastore_filegroups`.`uid` AS `id`, ' .
			'`tx_icsoddatastore_filegroups`.`title` AS `title`, ' .
			'`tx_icsoddatastore_filegroups`.`description` AS `description`, ' .
			'`tx_icsoddatastore_filegroups`.`files` AS `files`, ' .
			'`tx_icsoddatastore_filegroups`.`agency` AS `agency`, ' .
			'`tx_icsoddatastore_filegroups`.`contact` AS `contact`, ' .
			'`tx_icsoddatastore_filegroups`.`licence` AS `licence`, ' .
			'`tx_icsoddatastore_filegroups`.`release_date` AS `released`, ' .
			'`tx_icsoddatastore_filegroups`.`update_date` AS `updated`, ' .
			'`tx_icsoddatastore_filegroups`.`time_period` AS `time_period`, ' .
			'`tx_icsoddatastore_filegroups`.`update_frequency` AS `frequency`, ' .
			'`tx_icsoddatastore_filegroups`.`publisher` AS `publisher`, ' .
			'`tx_icsoddatastore_filegroups`.`creator` AS `author`, ' .
			'`tx_icsoddatastore_filegroups`.`manager` AS `manager`, ' .
			'`tx_icsoddatastore_filegroups`.`owner` AS `owner`, ' .
			'`tx_icsoddatastore_filegroups`.`technical_data` AS `technical_data`';
		$queryarray['fromtable'] =
			'`tx_icsoddatastore_filegroups`';
		$queryarray['where'] =
			'1' . t3lib_BEfunc::deleteClause('tx_icsoddatastore_filegroups') . t3lib_BEfunc::BEenableFields('tx_icsoddatastore_filegroups');
		$queryarray['groupby'] =
			'';
		$queryarray['order'] =
			'';
		$queryarray['limit'] =
			'';
		// *************************
		// * User inclusions filter0
		// * DO NOT DELETE OR CHANGE THOSE COMMENTS
		// *************************

		// ... (Add additional operations here) ...

		// Builds query fromtable
		if ($params['fileformats'])
		{
			$queryarray['fromtable'] .= ' JOIN `tx_icsoddatastore_files_filegroup_mm` ON `tx_icsoddatastore_filegroups`.`uid` = `tx_icsoddatastore_files_filegroup_mm`.`uid_foreign`
				JOIN `tx_icsoddatastore_files` ON `tx_icsoddatastore_files_filegroup_mm`.`uid_local` = `tx_icsoddatastore_files`.`uid`';
		}

		// Builds query where
		$where = array();
		if ($params['ids'])
		{
			$ids = t3lib_div::trimExplode(',', $params['ids']);
			foreach ($ids as $idx=>$id)
			{
				$ids[$idx] = $this->_datasourceDB->fullQuoteStr($id, 'tx_icsoddatastore_filegroups');
			}
			$where[] = '`tx_icsoddatastore_filegroups`.`uid` IN (' . implode(',', $ids) . ')';
		}
		if ($params['agencies'])
		{
			$agencies = t3lib_div::trimExplode(',', $params['agencies']);
			foreach ($agencies as $idx=>$agency)
			{
				$agencies[$idx] = $this->_datasourceDB->fullQuoteStr($agency, 'tx_icsoddatastore_filegroups');
			}
			$where[] = '`tx_icsoddatastore_filegroups`.`agency` IN (' . implode(',', $agencies) . ')';
		}
		if ($params['fileformats'])
		{
			$fileformats = t3lib_div::trimExplode(',', $params['fileformats']);
			foreach ($fileformats as $idx=>$fileformat)
			{
				if (!is_numeric($fileformat))
					$fileformat = $this->getFileformat($fileformat);
				$fileformats[$idx] = $this->_datasourceDB->fullQuoteStr($fileformat, 'tx_icsoddatastore_file');
			}
			$where[] =  '`tx_icsoddatastore_files`.`format` IN (' . implode(',', $fileformats) . ')' . t3lib_BEfunc::deleteClause('tx_icsoddatastore_files') . t3lib_BEfunc::BEenableFields('tx_icsoddatastore_files');
		}
		if ($params['licences'])
		{
			$licences = t3lib_div::trimExplode(',', $params['licences']);
			foreach ($licences as $idx=>$licence)
			{
				$licences[$idx] = $this->_datasourceDB->fullQuoteStr($licence, 'tx_icsoddatastore_filegroups');
			}
			$where[] = '`tx_icsoddatastore_filegroups`.`licence` IN (' . implode(',', $licences) . ')';
		}
		if ($params['released'])
		{
			$released = strtotime($params['released']);
			if ($released)
				$where[] = '`tx_icsoddatastore_filegroups`.`release_date` >= ' . $released;
		}
		if ($params['updated'])
		{
			$updated = strtotime($params['updated']);
			if ($updated)
				$where[] = '`tx_icsoddatastore_filegroups`.`update_date` >= ' . $updated;
		}

		if (!empty($where))
			$queryarray['where'] .= ' AND ' . implode(' AND ', $where);

		// Builds query limit
		if ($params['page'] && $params['limit'])
		{
			$count_datasets = count($this->get($queryarray));
			if ((($params['page'] -1) * $params['limit']) > $count_datasets)
				$queryarray['limit'] = intval($count_datasets / $params['limit']) . ', ' . $params['limit'];
			else
				$queryarray['limit'] = ($params['page'] -1) * $params['limit'] . ', ' . $params['limit'];
		}

		// Get datasets
		$datasets = $this->get($queryarray);

		if ($process_file)
		{
			$queryfiles = array();
			switch ($params['type'])
			{
				case 'url':
					$queryfiles[] = '`tx_icsoddatastore_files`.`record_type` = 1';
					break;
				case 'file':
					$queryfiles[] = '`tx_icsoddatastore_files`.`record_type` = 0';
					break;
				default: // full
			}
			if ($params['filetype'] && (strcmp($params['filetype'],'any') != 0))
				$queryfiles[] = '`tx_icsoddatastore_files`.`type` = ' . $this->getFiletype($params['filetype']);

			$datasets = $this->process_datasetFile($datasets, implode(' AND ', $queryfiles));
		}

		// * End user inclusions filter0
		return array('count' => ($count_datasets? $count_datasets : count($datasets)), 'datasets' => $datasets);
	} // End getDadasetsFilter

	/**
	 * Retrieves file's type
	 *
	 * @param	string		$filetype The filetype's name
	 * @return	The		filetype's uid
	 */
	private function getFiletype($filetype)
	{
		$filetypes = t3lib_div::trimExplode(',', tx_icsoddatastore_dataset_datasource::$filetypes[$filetype]);
		foreach ($filetypes as $idx=>$type)
		{
			$filetypes[$idx] = $this->_datasourceDB->fullQuoteStr($type, 'tx_icsoddatastore_filetypes');
		}

		$types = $this->_datasourceDB->exec_SELECTgetRows(
			'*',
			'tx_icsoddatastore_filetypes',
			'UPPER( `tx_icsoddatastore_filetypes`.`name` ) IN (' . implode(',', $filetypes) . ')'
		);
		return $types[0]['uid'];
	}

	/**
	 * Retrieves file's format
	 *
	 * @param	string		$fileformat The name of the file's format
	 * @return	The		file format uid
	 */
	private function getFileformat($fileformat)
	{
		$formats = $this->_datasourceDB->exec_SELECTgetRows(
			'*',
			'`tx_icsoddatastore_fileformats`',
			'`tx_icsoddatastore_fileformats`.`extension` LIKE ' . $this->_datasourceDB->fullQuoteStr($fileformat, 'tx_icsoddatastore_fileformats')
		);
		return $formats[0]['uid'];
	}
} // End of class tx_icsoddatastore_dataset_datasource
