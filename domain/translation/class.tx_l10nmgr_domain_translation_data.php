<?php
/***************************************************************
 *  Copyright notice
 *
 *  Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
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

require_once t3lib_extMgm::extPath('l10nmgr') . 'interface/interface.tx_l10nmgr_interface_stateImportable.php';
require_once t3lib_extMgm::extPath('l10nmgr') . 'domain/translation/class.tx_l10nmgr_domain_translation_pageCollection.php';

/**
 * Translation data object which holds the metadata of an XML file
 *
 * class.tx_l10nmgr_domain_translation_data.php
 *
 * @author Michael Klapper <klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id$
 * @date $Date$
 * @since 24.04.2009 - 11:44:48
 * @package TYPO3
 * @subpackage tx_l10nmgr
 * @access public
 */
class tx_l10nmgr_domain_translation_data implements tx_l10nmgr_interface_stateImportable {

	/**
	 * Indicate that the current entity was already processed for the import
	 *
	 * @var boolean
	 */
	protected $isImported = false;

	/**
	 * Exportd record uid of database table "tx_l10nmgr_exportdata"
	 *
	 * @var integer
	 */
	protected $exportDataRecordUid = 0;

	/**
	 * Translation configuration record uid of table "tx_l10nmgr_cfg"
	 *
	 * @var integer
	 */
	protected $l10ncfgUid = 0;

	/**
	 * Uid of the sys_language from the Exported language
	 *
	 * @var integer
	 */
	protected $sysLanguageUid = 0;

	/**
	 * Column "lg_iso_2" of the "static_language" database table
	 * Needed for the flexform translation handling
	 *
	 * @see $this->sysLanguage
	 * @var string
	 */
	protected $sourceLanguageISOcode = '';

	/**
	 * Uid of the sys_language where the translation should be imported into
	 *
	 * @var integer
	 */
	protected $targetLanguageUid = 0;

	/**
	 * Base url from the exported system
	 *
	 * @var string
	 */
	protected $baseUrl = '';

	/**
	 * Uid of the sys_workspace record where the export was made from
	 *
	 * @var integer
	 */
	protected $workspaceId = 0;

	/**
	 * Count of the available fields from the XML export file
	 *
	 * @var integer
	 */
	protected $fieldCount = 0;

	/**
	 * Count of the words stored in the XML file
	 *
	 * @var integer
	 */
	protected $wordCount = 0;

	/**
	 * System messages produced by the exporter
	 *
	 * Possible messages are:
	 * - Error message
	 * - Warning
	 * - Notice
	 *
	 * @var ArrayObject
	 */
	protected $MessageCollection = null;

	/**
	 * Version of the XML struct defined by constant "L10NMGR_FILEVERSION"
	 *
	 * @see EXT:l10nmgr/ext_localconf.php
	 * @var float
	 */
	protected $formatVersion = 0;

	/**
	 * All available pages from the XML export file
	 *
	 * @var tx_l10nmgr_domain_translation_pageCollection
	 */
	protected $PageCollection = null;

	/**
	 * Write import success information about the chilg elements
	 *
	 * Possible log entrys are:
	 * - Fields skipped with message
	 * - Fields which are imported
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function writeProcessingLog() {
		//!TODO implement me
	}

	/**
	 * Mark entity as processed for the import
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function markImported() {
//!TODO refactor this, the object should not allowed to set his own isImported state to true
//		$this->isImported = true;
	}

	/**
	 * Retrieve the import state
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return boolean
	 */
	public function isImported() {

		if ( ($this->PageCollection instanceof tx_l10nmgr_domain_translation_pageCollection) && $this->PageCollection->isImported() ) {
			$this->isImported = true;
		} else {
			$this->isImported = false;
		}

		return $this->isImported;
	}

	/**
	 * Find fieldCollection for current parameter
	 *
	 * Note:
	 * An tx_mvc_exception_argumentOutOfRange Exception is thrown if an index not available.
	 *
	 * @param integer $pageUid
	 * @param string $tableName
	 * @param integer $elementUid
	 * @param string $uniqueKey EXAMPLE "pages_language_overlay:NEW/1/1111:title"
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return tx_l10nmgr_domain_translation_fieldCollection
	 */
	public function findByTableUidAndKey($pageUid, $tableName, $elementUid, $uniqueKey) {
		return $this->getPageCollection()->offsetGet($pageUid)->getElementCollection()->offsetGet($tableName . ':' . $elementUid)->getFieldCollection()->offsetGet($uniqueKey);
	}

	/**
	 * @param unknown_type $exportDataRecordUid
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return
	 */
	public function setExportDataRecordUid($exportDataRecordUid) {
		$this->exportDataRecordUid = $exportDataRecordUid;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return integer
	 */
	public function getExportDataRecordUid() {
		return $this->exportDataRecordUid;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return string
	 */
	public function getBaseUrl() {
		return $this->baseUrl;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return integer
	 */
	public function getFieldCount() {
		return $this->fieldCount;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return float
	 */
	public function getFormatVersion() {
		return $this->formatVersion;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return integer
	 */
	public function getL10ncfgUid() {
		return $this->l10ncfgUid;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return ArrayObject
	 */
	public function getMessages() {
		//!TODO implement me finally
		return $this->MessageCollection;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return tx_l10nmgr_domain_translation_pageCollection
	 */
	public function getPageCollection() {
		return $this->PageCollection;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return integer
	 */
	public function getSysLanguageUid() {
		return $this->sysLanguageUid;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return string
	 */
	public function getSourceLanguageISOcode() {
		return $this->sourceLanguageISOcode;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return integer
	 */
	public function getTargetLanguageUid() {
		return $this->targetLanguageUid;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return integer
	 */
	public function getWordCount() {
		return $this->wordCount;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return integer
	 */
	public function getWorkspaceId() {
		return $this->workspaceId;
	}

	/**
	 * @param string $baseUrl
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function setBaseUrl($baseUrl) {
		$this->baseUrl = $baseUrl;
	}

	/**
	 * @param integer $fieldCount
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function setFieldCount($fieldCount) {
		$this->fieldCount = $fieldCount;
	}

	/**
	 * @param float $formatVersion
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function setFormatVersion($formatVersion) {
		$this->formatVersion = $formatVersion;
	}

	/**
	 * @param integer $l10ncfgUid
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function setL10ncfgUid($l10ncfgUid) {
		$this->l10ncfgUid = $l10ncfgUid;
	}

	/**
	 * @param string $messages
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function setMessage($messages) {
		//!TODO implement me finally
		$this->MessageCollection->append($messages);
	}

	/**
	 * @param tx_l10nmgr_domain_translation_pageCollection $PageCollection
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function setPageCollection(tx_l10nmgr_domain_translation_pageCollection $PageCollection) {
		$this->PageCollection = $PageCollection;
	}

	/**
	 * @param integer $sysLanguageUid
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function setSysLanguageUid($sysLanguageUid) {
		$this->sysLanguageUid = $sysLanguageUid;
	}

	/**
	 * @param string $sourceLanguageISOcode
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function setSourceLanguageISOcode($sourceLanguageISOcode) {
		$this->sourceLanguageISOcode = $sourceLanguageISOcode;
	}

	/**
	 * @param integer $targetLanguageUid
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function setTargetLanguageUid($targetLanguageUid) {
		$this->targetLanguageUid = $targetLanguageUid;
	}

	/**
	 * @param integer $wordCount
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function setWordCount($wordCount) {
		$this->wordCount = $wordCount;
	}

	/**
	 * @param integer $workspaceId
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function setWorkspaceId($workspaceId) {
		$this->workspaceId = $workspaceId;
	}

	/**
	 * Returns a collection of page ids which are relevant for this translation.
	 *
	 * @access public
	 * @author Timo Schmidt
	 * @return ArrayObject
	 */
	public function getPageIdCollection(){
		$pageIdCollection = new ArrayObject();

		for ( $it = $this->PageCollection->getIterator(); $it->valid(); $it->next() ) {
			$currentPage = $it->current();
			$pageIdCollection->append($currentPage->getUid());
		}

		return $pageIdCollection;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/domain/translation/class.tx_l10nmgr_domain_translation_data.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/domain/translation/class.tx_l10nmgr_domain_translation_data.php']);
}

?>