<?php

namespace JambageCom\Voucher\Controller;


/***************************************************************
*  Copyright notice
*
*  (c) 2017 Franz Holzinger (franz@ttproducts.de)
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
* see \TYPO3\CMS\Taskcenter\Controller\TaskModuleController;
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


use TYPO3\CMS\Backend\Module\BaseScriptClass;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BackendModuleController extends BaseScriptClass {

    /**
    * @var array
    */
    protected $pageinfo;

    /**
    * ModuleTemplate Container
    *
    * @var ModuleTemplate
    */
    protected $moduleTemplate;

    /**
    * The name of the module
    *
    * @var string
    */
    protected $moduleName = 'web_txvoucherM1';


    /**
    * @var string
    */
    public $body = '';


    /**
    * Initializes the Module
    */
    public function __construct()
    {
        $this->moduleTemplate = GeneralUtility::makeInstance(ModuleTemplate::class);
//      $this->moduleTemplate->getPageRenderer()->addCssFile(ExtensionManagementUtility::extRelPath('taskcenter') . 'Resources/Public/Css/styles.css');
        $this->getLanguageService()->includeLLFile('EXT:voucher/Resources/Private/Language/locallang.xlf');
        $this->MCONF = [
            'name' => $this->moduleName,
            'access' => 'admin',
            'script' => '_DISPATCH'
        ];
        parent::init();
    }


    /**
    * Adds items to the ->MOD_MENU array. Used for the function menu selector.
    *
    * @return void
    */
    public function menuConfig()
    {
        $this->MOD_MENU = ['function' => []];
        $this->MOD_MENU['function']['edit_voucher'] = $this->getLanguageService()->sL('LLL:EXT:voucher/Resources/Private/Language/locallang.xlf:edit_voucher');
        $this->MOD_MENU['function']['create_voucher'] = $this->getLanguageService()->sL('LLL:EXT:voucher/Resources/Private/Language/locallang.xlf:create_voucher');
        $this->MOD_MENU['function']['general_voucher'] = $this->getLanguageService()->sL('LLL:EXT:voucher/Resources/Private/Language/locallang.xlf:general_voucher');

        parent::menuConfig();
    }

    /**
    * Generates the menu based on $this->MOD_MENU
    *
    * @throws \InvalidArgumentException
    */
    protected function generateMenu()
    {
        $menu = $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $menu->setIdentifier('WebFuncJumpMenu');
        foreach ($this->MOD_MENU['function'] as $controller => $title) {
            $item = $menu
                ->makeMenuItem()
                ->setHref(
                    BackendUtility::getModuleUrl(
                        $this->moduleName,
                        [
                            'id' => $this->id,
                            'SET' => [
                                'function' => $controller
                            ]
                        ]
                    )
                )
                ->setTitle($title);
            if ($controller === $this->MOD_SETTINGS['function']) {
                $item->setActive(true);
            }
            $menu->addMenuItem($item);
        }
        $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);
    }

    /**
    * Injects the request object for the current request or subrequest
    * Simply calls main() and writes the content to the response
    *
    * @param ServerRequestInterface $request the current request
    * @param ResponseInterface $response
    * @return ResponseInterface the response with the content
    */
    public function mainAction (
        \Psr\Http\Message\ServerRequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response
    ) {
// 		$this->init();
        $GLOBALS['SOBE'] = $this;
        $this->main();

        $this->moduleTemplate->setContent($this->content);

        $response->getBody()->write($this->moduleTemplate->renderContent());
// 		$response = $response->withHeader('Content-Type', 'text/html; charset=utf-8');
        return $response;
    }

    /**
    * Creates the module's content.
    *
    * @return void
    */
    public function main()
    {
//         $this->getButtons();
        $this->generateMenu();
        $this->moduleTemplate->addJavaScriptCode(
            'VoucherManagerInlineJavascript',
            'if (top.fsMod) { top.fsMod.recentIds["web"] = 0; }'
        );

        // Render content

        $this->renderModuleContent();

        // Renders the module page
        $this->moduleTemplate->setTitle($this->getLanguageService()->getLL('title'));
    }


    /**
    * Main function of the module. Result is written to $this->content
    */
    public function old_main () {
        // Loading current page record and checking access:
        $this->pageinfo = BackendUtility::readPageAccess($this->id, $this->perms_clause);
        // Access check!
        $access = is_array($this->pageinfo) ? 1 : 0;
        $model = GeneralUtility::makeInstance('JambageCom\Voucher\Model\VoucherModel');

        if (
            (
                $this->id &&
                $access
            ) ||
            (
                $GLOBALS['BE_USER']->user['admin'] &&
                !$this->id
            )
        ) {
            /** @var FormResultCompiler formResultCompiler */
            $this->formResultCompiler = GeneralUtility::makeInstance(FormResultCompiler::class);

            // Start document template object:
// 			$this->doc = GeneralUtility::makeInstance(\TYPO3\CMS\Backend\Template\DocumentTemplate::class);
// TODO: Auf $this->doc aufbauen damit das Funktionsmenü angezeigt wird.
// $this->doc->funcMenu
// 			$this->getPageRenderer()->loadRequireJsModule('TYPO3/CMS/Recordlist/Tooltip');

                // JavaScript
//             $this->moduleTemplate->addJavaScriptCode($javascript);

            // Begin to compile the whole page, starting out with page header:
            if (!$this->id) {
                $title = $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'];
            } else {
                $title = $this->pageinfo['title'];
            }
            $this->body = $this->moduleTemplate->header($title);
            $this->moduleTemplate->setTitle($title);

            $moduleId = 'txvoucherM1';
            $this->moduleTemplate->setModuleId($moduleId);
            $moduleName = 'Voucher Manager';
            $this->moduleTemplate->setModuleName($moduleName);

            $this->moduleTemplate->registerModuleMenu($moduleMenuIdentifier);

            $moduleContent = $this->moduleContent();

            $this->moduleTemplate->setContent($moduleContent);
            $menuRegistry = $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry();
            $docHeader = $this->moduleTemplate->getDocHeaderComponent()->docHeaderContent();

            $uid = $_REQUEST['delete'];
            if ($uid) {
                $model->deleteRecords($uid);
            }
            $model->modifyRecords();

            $this->content = $this->moduleTemplate->renderContent();
        } else {
                // If no access or if ID == zero
            $this->content = '';
        }

        return $this->content;
    }

    /**
    * Generates the module content
    */
    public function renderModuleContent () {
        $table = 'tx_voucher_codes';
        $db = $this->getDatabaseConnection();

        // Check if the task is restricted to admins only
        if (!$this->checkAccess()) {
            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                $this->getLanguageService()->getLL('error-access', true),
                $this->getLanguageService()->getLL('error_header'),
                FlashMessage::ERROR
            );

            /** @var $flashMessageService \TYPO3\CMS\Core\Messaging\FlashMessageService */
            $flashMessageService = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Messaging\FlashMessageService::class);
            /** @var $defaultFlashMessageQueue \TYPO3\CMS\Core\Messaging\FlashMessageQueue */
            $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
            $defaultFlashMessageQueue->enqueue($flashMessage);
            return;
        }

        $content = '';
        $function = (string) $this->MOD_SETTINGS['function'];
        $tableTCA = $GLOBALS['TCA'][$table]['columns'];
        $amountTypeTextArray = array();

        if (
            isset($tableTCA['amount_type']) &&
            is_array($tableTCA['amount_type'])
        ) {
            foreach ($tableTCA['amount_type']['config']['items'] as $k => $valArray) {
                $v = $valArray['0'];
                $amountTypeTextArray[$k] = $GLOBALS['LANG']->sL($v);
            }
        }

        switch($function) {
            case 'edit_voucher':
                $msg = '';
                $cnt = 0;
                $error = FALSE;
                $notEmpty = FALSE;

                if(
                    isset($_REQUEST['uid']) &&
                    $_REQUEST['uid'] &&
                    isset($_REQUEST['vcsave'])
                        ||
                    isset($_REQUEST['edit'])
                ) {
                    $content = '<h4>' . strtoupper('Gutscheincode Verwaltung') . '</h4>';
                    $uid = $_REQUEST['uid'];
                    if (!$uid) {
                        $uid = $_REQUEST['edit'];
                    }
                    $where = 'uid=' . intval($uid);
                    $result1 = $db->exec_SELECTquery('*', 'fe_users', $where);

                    while ($row1 = mysql_fetch_array($result1)) {
                        $content .= '
                            <b>Name</b>: ' . $row1['name'] . '<br />
                            <b>Adresse</b>: ' . $row1['address'] . '<br />' . $row1['zip'] . ' ' . $row1['city'] . '<br />' . $row1['country']. '
                            <br /><b>E-Mail</b>: ' . $row1['email']. '<br /><br />';
                        $content .= '<table><tr><td colspan="3"><h4>nicht aktive Gutscheincodes</h4></td></tr><tr><td><b>Gutscheincode</b>:</td><td><b>Typ</b>:</td><td><b>Betrag</b>:</td><td><b>G&uuml;ltigkeitszeitraum</b>:</td></tr>';
                        $time = time();
                        $where = 'fe_users_uid="' . $row1['uid'] . '"';
                        $where .= t3lib_BEfunc::BEenableFields($table, TRUE);
                        $result3 = $db->exec_SELECTquery('*', $table, $where, '', 'code');
                        while ($row3 = mysql_fetch_array($result3)) {
                            $out = '<tr><td colspan="3"><b>' . $row3['uid'] . ':</b></td></tr>';
                            $out .= $this->getVoucherFields($table, $row3);
                            $content .= $out;
                        }
                        $db->sql_free_result($result3);
                        $content .= '<tr><td colspan="3"></td></tr><tr><td colspan="3"><h4>aktuelle Gutscheincodes</h4></td></tr>';

                        $where = 'fe_users_uid="' . $uid . '"';
                        $where .= t3lib_BEfunc::BEenableFields($table, FALSE);
                        $result2 = $db->exec_SELECTquery('*', $table, $where, '', 'code');
                        while ($row2 = mysql_fetch_array($result2)) {
                            $cnt++;
                            $out = '<tr><td colspan="3"><b>' . $row2['uid'] . ':</b></td></tr>';
                            $out .= $this->getVoucherFields($table, $row2);
                            $content .= $out;
                        }
                        $db->sql_free_result($result2);
                    }
                    $content .= '<br /><input type="hidden" name="uid" value="' . $_REQUEST['edit'] . '" /><input type="submit" name="vcsave" value="speichern" />&nbsp;<input type="submit" name="back" value="zur&uuml;ck" />';
                } else {
                    $where = 'fe_users_uid <> 0 AND not deleted';
                    $row = $db->exec_SELECTgetSingleRow('count(*)', 'tx_voucher_codes', $where);

                    if ($row) {
                        $notEmpty = $row['count(*)'] > '0';
                    }

                    if($notEmpty == TRUE) {
                        $content = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
                            <tr>
                            <td style="border-bottom:1px solid #cccccc;"><b>Name</b></td>
                            <td style="border-bottom:1px solid #cccccc;"><b>E-Mail</b></td>
                            <td style="border-bottom:1px solid #cccccc;"><b>Gutscheincodes</b></td>
                            <td style="border-bottom:1px solid #cccccc;"><b>eingel&ouml;ste Gutscheine</b></td>
                            <td style="border-bottom:1px solid #cccccc;">&nbsp;</td></tr>';

                        $result2 = mysql_query("Select * from tx_voucher_codes where fe_users_uid <> 0 and not deleted group by fe_users_uid order by fe_users_uid");
                        while ($row2 = mysql_fetch_array($result2)) {
                            $result = mysql_query("Select * from fe_users where uid='" . $row2['fe_users_uid'] . "'");
                            while ($row = mysql_fetch_array($result)) {
                                $content .= '<tr>
                                <td style="border-left:1px solid #cccccc;border-right:1px solid #cccccc;border-bottom:1px solid #cccccc;">
                                <b>'. $row['name'] .'</b>
                                </td>
                                <td style="border-right:1px solid #cccccc;border-bottom:1px solid #cccccc;">
                                '. $row['email'] .'
                                </td>';
                            }
                            $codes1 ='';
                            $codes2 = '';
                            $result1 = mysql_query('Select * from tx_voucher_codes where fe_users_uid = "' . $row2['fe_users_uid'] . '" and deleted = 0');
                            while ($row1 = mysql_fetch_array($result1)) {
                                $codes1 .= substr($row1['code'], 0, $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][VOUCHER_EXT]['codeSize']) . ', ';
                            }
                            $result1 = mysql_query('Select * from tx_voucher_codes where fe_users_uid = "' . $row2['fe_users_uid'] . '" and deleted = 1');
                            while ($row1 = mysql_fetch_array($result1)) {
                                $codes2 .= substr($row1['code'], 0, $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][VOUCHER_EXT]['codeSize']) . ', ';
                            }

                            if($codes1 == '')
                                $codes1 = '&nbsp;';
                            else
                                $codes1 = substr($codes1, 0, strlen($codes1) - 2);
                            if($codes2 == '')
                                $codes2 = '&nbsp;';
                            else
                                $codes2 = substr($codes2, 0, strlen($codes2) - 2);
                            $content .= '<td style="border-right:1px solid #cccccc;border-bottom:1px solid #cccccc;">
                            '. $codes1 .'
                            </td>
                            <td style="border-right:1px solid #cccccc;border-bottom:1px solid #cccccc;">
                            '. $codes2 .'
                            </td>
                            <td style="border-right:1px solid #cccccc;border-bottom:1px solid #cccccc;">
                                <input type="hidden" name="' . $row2['fe_users_uid' ] .'" value="1" />
                                <input style="background-image: url(edit.gif); background-repeat:no-repeat; background-color:transparent; color: #eeeeee; cursor: pointer; border-style: none; border-color:transparent; height:20px; width:20px;" type="submit" name="edit" title="" alt="Codes bearbeiten" value="' . $row2['fe_users_uid'] . '" />
                            </td></tr>';
                        }
                    } else {
                        $content .= '<i>Keine Datens&auml;tze vorhanden.</i>';
                    }
                }
            break;
            case 'create_voucher':
                $msg = '';
                $notEmpty = FALSE;
                $row = $this->getRow();

                //Gutscheincode zuordnen

                if(
                    $row['code'] != '' &&
                    $row['amount'] != ''
                ) {
                    if (isset($row['code'])) {
                        $content = '<h4>' . strtoupper('Gutscheincode Verwaltung') . '</h4>';
                        $where = 'uid IN (' . implode(',', $this->feUserArray) . ') AND deleted = 0';
                        $result1 = mysql_query('SELECT * FROM fe_users WHERE ' . $where . ' ORDER BY uid');

                        while ($row1 = mysql_fetch_array($result1)){
                            if($_REQUEST[$row1['uid']] == 1 || $_REQUEST[$row1['uid']] != '') {
                                $content .= '<br />
                                <b>Name</b>: ' . $row1['name']. '<br />
                                <b>Adresse</b>: ' . $row1['address'] . '<br />' . $row1['zip'] . ' ' .$row1['city'] . '<br />' . $row1['country'] . '
                                <br /><b>E-Mail</b>: ' . $row1['email'] . '<br /><br /><table>';
                                $result2 = mysql_query('Select * from tx_voucher_codes where deleted = 0 and fe_users_uid = "' . $row1['uid'] . '"');
                                while ($row2 = mysql_fetch_array($result2)) {
                                    $content .= '<tr><td>
                                    <b>Gutscheincode</b>: ' . substr($row2['code'],
                                    0,
                                    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][VOUCHER_EXT]['codeSize']) . '</td><td><b>Typ</b>: ' .
                                    $amountTypeTextArray[$row2['amount_type']] .
                                    '</td><td><b>Betrag</b>: ' . $row2['amount'] .#
                                    '</td><td><b>G&uuml;tigkeitszeitraum</b>: ' .
                                    $this->getOutputDate($row2['starttime']) . ' - ' .
                                    $this->getOutputDate($row2['endtime']) .
                                    '</td></tr>';
                                }
                            }
                            $content .= '</table>';
                        } $content .= '<br /><input type="submit" name="back" value="zur&uuml;ck" />';
                    }
                } else {
                    $result = mysql_query('Select * from fe_users where deleted = 0 order by uid');
                    $max = mysql_num_rows($result);
                    $notEmpty = ($max > 0);
                    $cnt = 0;


                    if($notEmpty == TRUE) {

                        $content = $this->newVoucherInput($table, $max);
                        $content .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
                            <tr>
                                <td style="border-bottom:1px solid #cccccc;"><b>Name</b></td>
                                <td style="border-bottom:1px solid #cccccc;"><b>E-Mail</b></td>
                                <td style="border-bottom:1px solid #cccccc;"><b>Stadt</b></td>
                                <td style="border-bottom:1px solid #cccccc;"><b>UID</b></td>
                                <td style="border-bottom:1px solid #cccccc;">&nbsp;</td></tr>';

                        while ($row = mysql_fetch_array($result)) {
                            $content .= '<tr>
                            <td style="border-left:1px solid #cccccc;border-right:1px solid #cccccc;border-bottom:1px solid #cccccc;">
                            <b>'. $row['name'] .'</b>
                            </td>
                            <td style="border-right:1px solid #cccccc;border-bottom:1px solid #cccccc;">
                            '. $row['email'] .'
                            </td>
                            <td style="border-right:1px solid #cccccc;border-bottom:1px solid #cccccc;">
                            '. $row['city'] .'&nbsp;
                            </td>
                            <td style="border-right:1px solid #cccccc;border-bottom:1px solid #cccccc;text-align:right;">
                            '. $row['uid'] .'
                            </td>
                            <td style="border-right:1px solid #cccccc;border-bottom:1px solid #cccccc;">
                            <input type="checkbox" name="' . $row['uid'] . '" id="gc' . $cnt. '" value="1" /></td></tr>';
                            $cnt++;
                        }
                        $jsOut2 = $this->tceforms->printNeededJSFunctions();
                        $content .= $jsOut2;
                    } else {
                        $content .= '<i>Keine Datens&auml;tze vorhanden.</i>';
                    }
                    $content .= '</table>';
                }
            break;
            case 'general_voucher':
                $content = $this->newVoucherInput($table, 0);

            //	$result1 = mysql_query("Select * from tx_voucher_codes where deleted = 0 and fe_users_uid = 0 group by code order by code");

                $rowArray = $db->exec_SELECTgetRows('*', 'tx_voucher_codes', 'deleted = 0 and fe_users_uid = 0', 'code', 'code');

                $content .= '<table>';
                foreach ($rowArray as $row1) {
        //		while ($row1 = mysql_fetch_array($result1)) {
                    $content .= '
                    <tr><td><b>Gutscheincode</b>:</td><td>' . substr($row1['code'], 0, $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][VOUCHER_EXT]['codeSize']) . '</td>
                        <td><b>Typ</b>:</td><td>' . $amountTypeTextArray[$row1['amount_type']] . '</td>
                        <td><b>Betrag</b>:</td><td>' . $row1['amount'] . '</td>
                        <td><b>mehrfach</b>:</td><td>' . ($row1['reusable'] ? 'Ja' : 'Nein') . '</td>
                        <td><b>Startdatum</b>:</td><td>' . $this->getOutputDate($row1['starttime']) . '</td>
                        <td><b>Ablaufdatum</b>:</td><td>' . $this->getOutputDate($row1['endtime']) . '</td>
                        <td>
                        <input style="background-image: url(garbage.gif); background-repeat:no-repeat; background-color:transparent; color: #eeeeee; cursor: pointer; border-style: none; border-color:transparent; height:20px; width:20px;" type="submit" name="delete" title="" alt="l&ouml;schen" value="' . $row1['uid'] . '" />
                        </td>
                    </tr>';
                }
                $content .= '</table>';
            break;
        }	// switch

//         $content = '<div id="vouchermanager-main">
// 						<div id="vouchermanager-menu">' . $this->indexAction() . '</div>
// 						<div id="vouchermanager-item" class="' . htmlspecialchars(($extKey . '-' . $taskClass)) . '">' . $actionContent . '
// 						</div>
// 					</div>';

        $this->content .= $content;
    }

    /**
    * Check the access to a task. Considered are:
    * - Admins are always allowed
    * - can be blinded by TsConfig voucher.admin = 0
    *
    * @return bool Access to the task allowed or not
    */
    protected function checkAccess()
    {
        // Admins are always allowed
        if ($this->getBackendUser()->isAdmin()) {
            return true;
        }
        // Check if voucher manager is restricted to admins
        if ((int) $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['voucher']['admin'] === 1) {
            return false;
        }
        return true;
    }
}

