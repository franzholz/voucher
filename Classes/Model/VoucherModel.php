<?php

namespace JambageCom\Voucher\Model;


/***************************************************************
*  Copyright notice
*
*  (c) 2016 Franz Holzinger (franz@ttproducts.de)
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
 * backend module controller
 *
 * @author	Franz Holzinger <franz@ttproducts.de>
 * @maintainer Franz Holzinger <franz@ttproducts.de>
 * @package TYPO3
 * @subpackage voucher
 */

use TYPO3\CMS\Backend\Form\Exception\AccessDeniedException;
use TYPO3\CMS\Backend\Form\FormDataCompiler;
use TYPO3\CMS\Backend\Form\FormDataGroup\TcaDatabaseRecord;
use TYPO3\CMS\Backend\Form\FormResultCompiler;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Backend\Form\Utility\FormEngineUtility;

use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\DocumentTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Utility\BackendUtility;

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

use TYPO3\CMS\Lang\LanguageService;


class VoucherModel implements \TYPO3\CMS\Core\SingletonInterface {

    public $timeFieldRow =
        array(
            'starttime' => '0',
            'endtime' => '0',
            'title' => '',
            'code' => '',
            'reusable' => '0',
            'usecounter' => '0',
            'combinable' => '0',
            'amount_type' => '0',
            'amount' => '0',
            'tax' => '0',
            'note' => '',
            'acquired_days' => '0',
            'acquired_groups' => ''
        );
    public $rowArray = array();
    public $feUserArray = array();

    /**
    * The name of the database table
    *
    * @var string
    */
    protected $table = 'tx_voucher_codes';

    /**
    * @var \TYPO3\CMS\Backend\Form\FormResultCompiler
    */
    protected $formResultCompiler;

    /**
    * Initializing the module
    *
    * @return void
    */
    public function init ($get, $post)
    {
        $backendUser = $this->getBackendUser();
        $this->perms_clause = $backendUser->getPagePermsClause(1);
        // Get session data
        $sessionData = $backendUser->getSessionData(__CLASS__);

        // GPvars:
        $this->id = (int)GeneralUtility::_GP('id');
        $this->returnUrl = GeneralUtility::sanitizeLocalUrl(GeneralUtility::_GP('returnUrl'));
        // Initialize menu
        $this->menuConfig();
        // Store session data
        $backendUser->setAndSaveSessionData(RecordList::class, $sessionData);
        $this->getPageRenderer()->addInlineLanguageLabelFile('EXT:voucher/Resources/Private/Language/locallang.xlf');

        $this->formResultCompiler =
        GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Form\\FormResultCompiler');

    }

    /**
    * @return PageRenderer
    */
    protected function getPageRenderer ()
    {
        if ($this->pageRenderer === null) {
            $this->pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        }

        return $this->pageRenderer;
    }

    public function getTable () {
        return $this->table;
    }

    public function getTimeFieldRow () {
        return $this->timeFieldRow;
    }

    public function getFields () {
        $result = array_keys($this->getTimeFieldRow());
    }

    public function setRow ($row) {
        $this->rowArray[] = $row;
        $this->rowArray = array_unique($this->rowArray);
    }

    public function getRow ($key = '') {
        $rc = '';
        if ($key) {
            $rc = $this->rowArray[$key];
        } else {
            $rc = current($this->rowArray);
        }
        return $rc;
    }



    public function modifyRecords () {

        $feUser = $_REQUEST['edit'];
        $feUserArray = array();
        $dbConnection = $this->getDatabaseConnection();

        // if (!$feUser && isset($_REQUEST['vcsave'])) {
        foreach ($_REQUEST as $k => $v) {
            if (
                MathUtility::canBeInterpretedAsInteger($k) &&
                $v != ''
            ) {
                $feUserArray[] = $k;
            }
        }

        if (!count($feUserArray)) {
            $feUserArray = array('0');
        }

        $this->feUserArray = $feUserArray;

        $table = 'tx_voucher_codes';
        $dataArray = $_REQUEST['data'];

        if (is_array($dataArray)) {
            foreach ($dataArray as $table => $rowArray) {
                foreach ($rowArray as $id => $row) {
                    if ($row['title'] != '' && $row['amount'] != '0') {
                        if (substr($id, 0, 3) == 'NEW') {
                            $this->setRow($row);
                            foreach ($feUserArray as $k => $uid) {
                                $row['fe_users_uid'] = $uid;
                                $dbConnection->exec_INSERTquery($table, $row);
                            }
                        } else {
                                        // Saving the order record
                            $dbConnection->exec_UPDATEquery(
                                $table,
                                'uid=' . intval($id),
                                $row
                            );
                        }
                    }
                }
            }
        }
    }

    public function deleteRecords ($uid) {
        if($uid) {
            $dbConnection = $this->getDatabaseConnection();
            $fieldsArray = array();
            $fieldsArray['deleted'] = 1;
            $where = 'uid=' . intval($uid);
            $result2 =
                $dbConnection->exec_UPDATEquery(
                    'tx_voucher_codes',
                    $where,
                    $fieldsArray
                );
        }
    }


    public function getOutputDate ($date) {
        $rc = '';
        if ($date) {
            $rc = date('d-M-Y', $date);
        } else {
            $rc = '-';
        }
        return $rc;
    }

    /**
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * Returns LanguageService
     *
     * @return \TYPO3\CMS\Lang\LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }

    /**
     * Returns the database connection
     *
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}


