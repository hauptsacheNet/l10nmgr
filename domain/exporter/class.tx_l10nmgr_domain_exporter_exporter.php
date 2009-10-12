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
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/


/**
 * The exporter is responsible to export a set of pages as xml files
 *
 * class.tx_l10nmgr_domain_exporter_exporter.php
 *
 * @author	 Timo Schmidt <schmidt@aoemedia.de>
 * @copyright Copyright (c) 2009, AOE media GmbH <dev@aoemedia.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @version $Id: class.tx_l10nmgr_domain_exporter_exporter.php $
 * @date 01.04.2009 - 15:11:03
 * @package	TYPO3
 * @subpackage	extensionkey
 * @access public
 */
class tx_l10nmgr_domain_exporter_exporter {

	/**
	 * @var tx_l10nmgr_domain_exporter_exportData
	 */
	protected $exportData;

	/**
	 * @var int
	 */
	protected $numberOfPagesPerChunck;

	/**
	 * @var boolean
	 */
	protected $isChunkProcessed = false;


	/**
	 * @var string
	 */
	protected $resultForChunk = '';

	/**
	 * @var boolean indicates if the exporter was running or not.
	 */
	protected $wasRunning = false;

	/**
	 * @var int number of fields in exportfile
	 */
	protected $currentNumberOfFields;

	/**
	 * Constructor to create an instance of the exporter object
	 *
	 * @param tx_l10nmgr_domain_exporter_exportData $exportData
	 * @param int number of pages per chunk
	 * @param tx_l10nmgr_view_export_abstractExportView export view
	 */
	public function __construct(tx_l10nmgr_domain_exporter_exportData $exportData, $numberOfPagesPerChunk, tx_l10nmgr_view_export_abstractExportView $exportView) {
		$this->exportData 				= $exportData;
		$this->numberOfPagesPerChunk  	= $numberOfPagesPerChunk;
		$this->exportView				= $exportView;
	}

	/**
	 * Run
	 *
	 * @param void
	 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
	 * @return bool true if not completely processed
	 */
	public function run() {
		if ($this->exportData->getExportIsCompletelyProcessed()) {
			return false;
		} else {

			if ($this->exportData->getIsCompletelyUnprocessed()) {
				$this->exportData->addWorkflowState(tx_l10nmgr_domain_exporter_workflowState::WORKFLOWSTATE_EXPORTING);
			}

			$pagesForChunk 					= $this->getNextPagesChunk();
			$factory 						= new tx_l10nmgr_domain_translateable_translateableInformationFactory();
			$tranlateableInformation 		= $factory->createFromExportDataAndPageIdCollection($this->exportData,$pagesForChunk);

			$this->currentNumberOfFields 	= $tranlateableInformation->countFields();

			$this->exportView->setTranslateableInformation($tranlateableInformation);

			$this->resultForChunk 		= $this->exportView->render();
			$this->removeProcessedChunkPages($pagesForChunk);
			$this->setIsChunkProcessed(true);

			if ($this->exportData->countRemainingPages() <= 0) {
				$this->exportData->setExportIsCompletelyProcessed(true);
				$this->exportData->addWorkflowState(tx_l10nmgr_domain_exporter_workflowState::WORKFLOWSTATE_EXPORTED);
			}

			$this->exportData->increaseNumberOfExportRuns();

			$this->wasRunning = true;
			return true;
		}
	}



	/**
	 * In each run the exporter processes one chunk. This method
	 * returns true if the current chunk is processed
	 *
	 * @author Timo Schmidt
	 * @return boolean
	 */
	protected function getIsChunkProcessed() {
		return $this->isChunkProcessed;
	}

	/**
	 * Returns the result for the current chunk.
	 *
	 * @author Timo Schmidt
	 * @return string
	 */
	public function getResultForChunk() {
		return $this->resultForChunk;
	}


	/**
	 * Counts the items which haven been exported in the current chunk.
	 *
	 * @param void
	 * @return int
	 */
	protected function countItemsForChunk(){
		if(!$this->wasRunning){
			throw new Exception('export has did not run export->run() has to be called first');
		}else{
			return $this->currentNumberOfFields;
		}
	}

	/**
	 * This method can be used internally to mark the chunk as processed.
	 *
	 * @author Timo Schmidt
	 * @param boolean $isChunkProcessed
	 */
	protected function setIsChunkProcessed($isChunkProcessed) {
		$this->isChunkProcessed = $isChunkProcessed;
	}

	/**
	 * Retuns the internal exportDataObject
	 *
	 * @author Timo Schmidt
	 * @return tx_l10nmgr_domain_exporter_exportData
	 */
	public function getExportData() {
		if (!$this->getIsChunkProcessed()) {
			throw new LogicException('it makes no sence to read the export data from an unprocessed run');
		} else {
			return $this->exportData;
		}
	}

	/**
	 * Builds a chunck of pageIds from the set of remaining pages of an export
	 *
	 * @author Timo Schmidt
	 * @return ArrayObject
	 */
	protected function getNextPagesChunk() {
		$allPages 			= $this->exportData->getExportRemainingPages();
		$chunk				= new ArrayObject();

		$allPagesIterator 	= $allPages->getIterator();
		for($pagesInChunk = 0; $pagesInChunk < $this->getNumberOfPagesPerChunk(); $pagesInChunk++) {
			if ($allPagesIterator->valid()) {
				$chunk->append($allPagesIterator->current());
				$allPagesIterator->next();
			}
		}

		return $chunk;
	}

	/**
	 * Returns the configuration option for the number of pages per chunck.
	 *
	 * @author Timo Schmidt
	 * @return int
	 */
	protected function getNumberOfPagesPerChunk() {
		return $this->numberOfPagesPerChunk;
	}

	/**
	 * Method removes a set of pages from the remaining pages in the exportData
	 *
	 * @param ArrayObject $pageIdCollection
	 */
	protected function removeProcessedChunkPages($pageIdCollection) {
		$this->exportData->removePagesIdsFromRemainingPages($pageIdCollection);
	}

	/**
	 * This method performs on run of an export. It is polled via ajax to perform a complete export.
	 * It saves the export to a file an returns a boolean value if a nother run is needed.
	 *
	 * @return boolean
	 */
	public static function performFileExportRun(tx_l10nmgr_domain_exporter_exportData $exportData, $numberOfPagesPerChunk = 5) {
		$exportView				= $exportData->getInitializedExportView();
		$exporter 				= new tx_l10nmgr_domain_exporter_exporter($exportData, $numberOfPagesPerChunk, $exportView);

		$prefix 				= $exportData->getL10nConfigurationObject()->getFilenameprefix();

		$exporterWasRunning 	= $exporter->run();
		$numberOfItemsInChunk	= $exporter->countItemsForChunk();
		$exportFileRepository = new tx_l10nmgr_domain_exporter_exportFileRepository();
		
		if ($exporterWasRunning && $numberOfItemsInChunk > 0) {
			//add the number of items in the current chunk to the whole number of items
			$exportData->addNumberOfItems($numberOfItemsInChunk);
			
			//now we write the exporter result to a file
			$exportFile	= new tx_l10nmgr_domain_exporter_exportFile();
			$exportFile->setFilename($exportView->getFilename($prefix,$exportData->getNumberOfExportRuns()));
			$exportFile->setExportDataObject($exportData);
			$exportFile->setContent($exporter->getResultForChunk());
			$exportFile->setPid($exportData->getPid()); // store the export file record on the same page as the export data record (and its configuration record)
			$exportFile->write();

			$exportFileRepository->add($exportFile);
		}

		if ($exportData->getExportIsCompletelyProcessed()) {
			if($exportData->getNumberOfItems() > 0){
				$exportData->createZip($exportView->getFilename($prefix) . '.zip');
	
				// postProcessingHook
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['l10nmgr']['exportPostProcessing'])) {
					foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['l10nmgr']['exportPostProcessing'] as $userFunc) {
						$params = array();
						t3lib_div::callUserFunction($userFunc, $params, $exporter);
					}
				}
			}else{
				throw new tx_mvc_exception_skipped('The export has no items therefore no xml files have been created an no zip. Please check your export configuration');
			}
		}

		$exportDataRepository = new tx_l10nmgr_domain_exporter_exportDataRepository();
		$exportDataRepository->save($exportData);

		return $exportData->getExportIsCompletelyProcessed();
	}
}

?>