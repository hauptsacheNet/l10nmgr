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

require_once t3lib_extMgm::extPath('l10nmgr') . 'domain/translation/class.tx_l10nmgr_domain_translation_data.php';
require_once t3lib_extMgm::extPath('l10nmgr') . 'service/class.tx_l10nmgr_service_textConverter.php';

/**
 * Factory to build the translation object
 *
 * class.tx_l10nmgr_domain_translationFactory.php
 *
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id$
 * @date $Date$
 * @since 24.04.2009 - 11:39:25
 * @package TYPO3
 * @subpackage tx_l10nmgr
 * @access public
 */
class tx_l10nmgr_domain_translationFactory {

	/**
	 * @var tx_l10nmgr_domain_translation_data
	 */
	protected $TranslationData = null;

	/**
	 * Build a translation data object from given XML data structure
	 *
	 * @param string $fullQualifiedFileName
	 * @param integer $forceTargetLanguageUid OPTIONAL If is set the targetLanguageUid will be overwritten with the forced languageUid
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return tx_l10nmgr_domain_translation_data
	 */
	public function create($fullQualifiedFileName, $forceTargetLanguageUid = 0) {

		if (! tx_mvc_validator_factory::getFileValidator()->isValid($fullQualifiedFileName)) {
			throw new tx_mvc_exception_fileNotFound('The given filename: "' . var_export($fullQualifiedFileName, true) . '" not found!');
		}

		$TranslationXML = simplexml_load_file($fullQualifiedFileName, 'SimpleXMLElement', LIBXML_NOCDATA ^ LIBXML_NOERROR ^ LIBXML_NONET ^ LIBXML_XINCLUDE ^ LIBXML_NOEMPTYTAG);
		if (! $TranslationXML instanceof SimpleXMLElement ) {
			throw new tx_mvc_exception_invalidContent('The file : "' . (string)$fullQualifiedFileName . '" contains no valid XML structure!');
		}

		$this->TranslationData = new tx_l10nmgr_domain_translation_data();

			// force the target sys_language_uid
		$this->TranslationData->setForceTargetLanguageUid($forceTargetLanguageUid);

		$this->extractMetaData($TranslationXML->head);
		$this->exractTranslation($TranslationXML->pageGrp);

		return $this->TranslationData;
	}

	/**
	 * Extract the page data from the XML import file into the tx_l10nmgr_domain_translation_pageCollection object
	 *
	 * @param SimpleXMLElement $Page
	 * @access private
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	private function exractTranslation(SimpleXMLElement $Pagerows) {
		$PageCollection = new tx_l10nmgr_domain_translation_pageCollection();
		$TextConverter = new tx_l10nmgr_service_textConverter(); /* @var $test tx_l10nmgr_xmltools */

		foreach ($Pagerows as $pagerow) {
			$Page = new tx_l10nmgr_domain_translation_page();
			$Page->setUid((int)$pagerow['id']);

				// Each page has one element collection
			$ElementCollection = new tx_l10nmgr_domain_translation_elementCollection();

			foreach ($pagerow->children() as $field) {
				$table = (string)$field['table'];
				$uid   = (int)$field['elementUid'];
				$Field = new tx_l10nmgr_domain_translation_field();
				$Field->setFieldPath((string)$field['key']);
				$needsAutoDetection = !$Field->detectTransformationType($field,$this->TranslationData->getFormatVersion());

				switch($Field->getTransformationType($uid,$needsAutoDetection)) {
					case 'html':
							$Field->setContent($TextConverter->getXMLContent($field));
						break;
					case 'text':
							$Field->setTransformation(true);
							$Field->setContent($TextConverter->toText($TextConverter->getXMLContent($field,true)));
						break;
					default:
							$Field->setContent($TextConverter->toText($TextConverter->getXMLContent($field), false, false));
				}

				$Element = $this->createOrGetElementFromElementCollection($ElementCollection, $table, $uid);
				$Element->getFieldCollection()->offsetSet((string)$field['key'], $Field);
			}

			$Page->setElementCollection($ElementCollection);
			$PageCollection->offsetSet((int)$pagerow['id'], $Page);
		}

		$this->TranslationData->setPageCollection($PageCollection);
	}

	/**
	 * If the Element for the current table and uid combination not exists a new instance
	 * of the tx_l10nmgr_domain_translation_element will be create.
	 *
	 * @param string $table
	 * @param int $uid
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return tx_l10nmgr_domain_translation_element
	 */
	protected function createOrGetElementFromElementCollection($ElementCollection,$table,$uid){

		if ( $ElementCollection->offsetExists($table . ':' . $uid) ) {

			$Element = $ElementCollection->offsetGet($table . ':' . $uid);
		} else {

			$Element = new tx_l10nmgr_domain_translation_element();
			$Element->setTableName($table);
			$Element->setUid($uid);

			$ElementCollection->offsetSet($table . ':' . $uid, $Element);

			$FieldCollection = new tx_l10nmgr_domain_translation_fieldCollection();
			$Element->setFieldCollection($FieldCollection);
		}

		return $Element;
	}

	/**
	 * Extract the meta information of the import XML file into the tx_l10nmgr_domain_translation_data object
	 *
	 * @param SimpleXMLElement $Head
	 * @access private
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	private function extractMetaData(SimpleXMLElement $Head) {

		foreach ($Head as $metaData) {
			$this->TranslationData->setL10ncfgUid((int)$metaData->t3_l10ncfg);
			$this->TranslationData->setTargetSysLanguageUid((int)$metaData->t3_sysLang);
			$this->TranslationData->setTargetLanguageIsoCode((string)$metaData->t3_targetLang);
			$this->TranslationData->setSourceLanguageISOcode((string)$metaData->t3_sourceLang);
			$this->TranslationData->setBaseUrl((string)$metaData->baseURL);
			$this->TranslationData->setWorkspaceId((int)$metaData->t3_workspaceId);
			$this->TranslationData->setFieldCount((int)$metaData->t3_count);
			$this->TranslationData->setWordCount((int)$metaData->t3_wordCount);
			$this->TranslationData->setFormatVersion((float)$metaData->t3_formatVersion);
			$this->TranslationData->setExportDataRecordUid((int)$metaData->t3_exportDataId);

			foreach ($metaData->t3_internal as $messageIndes => $message) {
				//!TODO redefine the message point (alias "t3_internal")
//				$this->TranslationData->setMessages();
			}
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/domain/translation/class.tx_l10nmgr_domain_translationFactory.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/domain/translation/class.tx_l10nmgr_domain_translationFactory.php']);
}

?>