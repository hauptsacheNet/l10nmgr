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

/**
 * Value object that holds the content of an translated record field
 *
 * class.tx_l10nmgr_domain_translation_field.php
 *
 * @author Michael Klapper <klapper@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id$
 * @date $Date$
 * @since 24.04.2009 - 14:18:09
 * @package TYPO3
 * @subpackage tx_l10nmgr
 * @access public
 */
class tx_l10nmgr_domain_translation_field implements tx_l10nmgr_interface_stateImportable {

	/**
	 * Indicate that the current entity was already processed for the import
	 *
	 * @var boolean
	 */
	protected $isImported = false;

	/**
	 * Key that stores several informations
	 * - Table name
	 * - Indicator that a new items should be created
	 * - sys_language_uid
	 * - Parent element uid
	 * - Field name
	 *
	 * @example pages_language_overlay:NEW/1/1111:title
	 * @var string
	 */
	protected $fieldPath = '';

	/**
	 * Content of the translation
	 *
	 * @var string
	 */
	protected $content = '';

	/**
	 * Indicate that the current field content are
	 * modified by the rte transformation
	 *
	 * @var boolean
	 */
	protected $transformation = false;

	/**
	 * Reason for skipping the entity
	 *
	 * @var string
	 */
	protected $skippedMessage = '';

	/**
	 * Indicator that the entity was skipped
	 *
	 * @var boolean
	 */
	protected $isSkipped = false;

	/**
	 * Mark the current entity as skipped for the current translation import process
	 *
	 * @param string $message Reason for skipping
	 * @access public
	 * @throws tx_mvc_exception_skipped
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function markSkipped($message) {
		$this->skippedMessage = $message;
		$this->isSkipped      = true;

		throw new tx_mvc_exception_skipped('Entity: "' . get_class($this) . '" with the uid: "' . $this->fieldPath . '" was skipped.');
	}

	/**
	 * Set this field as imported
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function markImported() {
		$this->isImported = true;
	}

	/**
	 * Indicate that this field is already processed for import
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return boolean
	 */
	public function isImported() {
		return $this->isImported;
	}

	/**
	 * Indicate that this field is skipped for import processing
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return boolean
	 */
	public function isSkipped() {
		return $this->isSkipped;
	}

	/**
	 * Return the skipped reason.
	 *
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return string
	 */
	public function getSkippedMessage() {
		return $this->skippedMessage;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return string
	 */
	public function getFieldPath() {
		return $this->fieldPath;
	}

	/**
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return boolean
	 */
	public function getTransformation() {
		return $this->transformation;
	}

	/**
	 * @param boolean $transformation
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function setTransformation($transformation) {
		$this->transformation = $transformation;
	}

	/**
	 * @param string $content
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function setContent($content) {
		$this->content = $content;
	}

	/**
	 * @param string $fieldPath
	 * @access public
	 * @author Michael Klapper <michael.klapper@aoemedia.de>
	 * @return void
	 */
	public function setFieldPath($fieldPath) {
		$this->fieldPath = $fieldPath;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/domain/translation/class.tx_l10nmgr_domain_translation_field.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/l10nmgr/domain/translation/class.tx_l10nmgr_domain_translation_field.php']);
}

?>