<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\EventManager;
use Bitrix\Main\ModuleManager;
use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
use Neti\SimpleMail\Company;
use Neti\SimpleMail\SimpleMail;

Loc::loadMessages(__FILE__);

class neti_simplemail extends CModule
{
    public $MODULE_ID = 'neti.simplemail';
    public $MODULE_VERSION = '1.0';
    public $MODULE_VERSION_DATE = '2017-08-29 00:00:00';
    public $PARTNER_NAME = 'Neti';
    public $PARTNER_URI = 'http://i-neti.ru';

    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $MODULE_GROUP_RIGHTS;

    public $errors, $modulePath;

    public function __construct()
    {
        $this->MODULE_NAME = Loc::getMessage('MODULE_NETI_SIMPLEMAIL_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('MODULE_NETI_SIMPLEMAIL_DESCRIPTION');
        $this->MODULE_GROUP_RIGHTS = 'N';
        $this->errors = array();

        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $this->MODULE_ID . '/')) {
            $this->modulePath = $_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $this->MODULE_ID;
        } else {
            $this->modulePath = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID;
        }
    }

    public function doInstallDB()
    {
        global $APPLICATION;

        CModule::IncludeModule('highloadblock');

        $hlID = SimpleMail::getHLBlockID();
        if (empty($hlID)) {
            $resHLBT = HLBT::add(array(
                'NAME' => SimpleMail::HL_ENTITY_NAME,
                'TABLE_NAME' => SimpleMail::HL_TABLE_NAME,
            ));

            if ($resHLBT->isSuccess()) {
                $hlID = $resHLBT->getId();
                // получим необходимые поля ХБ для добавления
                $arHLBlockFields = $this->getHLBlockFields($hlID);
                // Добаввляем поля
                if (count($arHLBlockFields) > 0) {
                    $userTypeEntity = new CUserTypeEntity();
                    foreach ($arHLBlockFields as $arField) {
                        if (!$userTypeEntity->Add($arField)) {
                            $this->errors[] = Loc::getMessage('ERROR_FIELD_CREATE_INS', array('NAME' => $arField['FIELD_NAME']));
                        }
                    }
                    if (!empty($this->errors)){
                        $APPLICATION->ThrowException(implode('<br>', $this->errors));
                        return false;
                    }
                }
            } else {
                $APPLICATION->ThrowException(Loc::getMessage('ERROR_FIELD_CREATE'));
                return false;
            }
        }
    }

    function doUnInstallDB()
    {
        CModule::IncludeModule('highloadblock');

        // получим идентификатор ХБ блока
        $hlID = SimpleMail::getHLBlockID();
        if (!empty($hlID)) {
            HLBT::delete($hlID);
        }
    }

    public function doInstall()
    {
        global $APPLICATION;

        if (!ModuleManager::isModuleInstalled('highloadblock')) {

            $APPLICATION->ThrowException(Loc::getMessage('ERROR_MODULE_HIGHLOADBLOCK'));
            return false;
        }

        if (!ModuleManager::isModuleInstalled($this->MODULE_ID)) {
            if ($this->doInstallTypeEvent()) {
                if ($this->doInstallTemplateEvent()) {
                    $this->doInstallEvents();
                }
            }
            $this->doInstallFiles();

            RegisterModule($this->MODULE_ID);

            if (CModule::IncludeModule($this->MODULE_ID)) {
                $this->doInstallDB();
            }
        }
    }

    public function doUninstall()
    {
        global $APPLICATION, $step;

        if (CModule::IncludeModule($this->MODULE_ID)) {
            $step = IntVal($step);
            if ($step < 2) {
                $APPLICATION->IncludeAdminFile(Loc::GetMessage('REPORT_UNINSTALL_TITLE'), $this->modulePath . '/install/unstep1.php');
            } elseif ($step == 2) {
                if ($_REQUEST['savedata'] != 'Y') {
                    $this->doUnInstallDB();
                }
                if ($this->doUnInstallEvents()) {
                    if ($this->doUnInstallTemplateEvent()) {
                        $this->doUnInstallTypeEvent();
                    }
                }
                $this->doUnInstallFiles();
                UnRegisterModule($this->MODULE_ID);
            }
        }
    }

    public function doInstallFiles()
    {
        CopyDirFiles($this->modulePath.'/install/components', $_SERVER['DOCUMENT_ROOT'].'/local/components', TRUE, TRUE);
    }

    public function doUnInstallFiles()
    {
        DeleteDirFilesEx('/local/components/neti/simplemail.list');
    }

    public function doInstallTypeEvent()
    {
        global $APPLICATION;

        $arTypeEvent = array(
            "EVENT_NAME" => "ADD_NEW_ELEMENT_IN_IBLOCK",
            "NAME" => Loc::GetMessage('MODULE_NETI_SIMPLEMAIL_MAIL_TYPE_EVENT_NAME'),
            "LID" => "ru",
            "DESCRIPTION" => Loc::GetMessage('MODULE_NETI_SIMPLEMAIL_MAIL_TYPE_EVENT_DESC')
        );
        $obEventType = new CEventType;
        if ($obEventType->Add($arTypeEvent)) {
            return true;
        } else {
            $APPLICATION->ThrowException($obEventType->LAST_ERROR);
            return false;
        }
    }

    public function doUnInstallTypeEvent()
    {
        global $APPLICATION;
        $et = new CEventType;

        if ($et->Delete("ADD_NEW_ELEMENT_IN_IBLOCK")) {
            return true;
        } else {
            $APPLICATION->ThrowException($et->LAST_ERROR);
            return false;
        }
    }

    public function doInstallTemplateEvent()
    {
        global $APPLICATION;
        $arSiteId = array();
        $rsSites = CSite::GetList($by = 'id', $order = 'asc', array("ACTIVE" => "Y"));
        while ($arSite = $rsSites->Fetch()) {
            $arSiteId[] = $arSite['LID'];
        }

        $arr["ACTIVE"] = "Y";
        $arr["EVENT_NAME"] = "ADD_NEW_ELEMENT_IN_IBLOCK";
        $arr["LID"] = $arSiteId;
        $arr["EMAIL_FROM"] = "#DEFAULT_EMAIL_FROM#";
        $arr["EMAIL_TO"] = "#EMAIL_TO#";
        $arr["BCC"] = "";
        $arr["SUBJECT"] = Loc::GetMEssage('MODULE_NETI_SIMPLEMAIL_MAIL_TEMPLATE_SUBJECT');
        $arr["BODY_TYPE"] = "text";
        $arr["MESSAGE"] = Loc::GetMessage('MODULE_NETI_SIMPLEMAIL_MAIL_TEMPLATE_MESSAGE');
        $arType = CEventMessage::GetList($by = "id", $order = "desc", array("TYPE_ID" => $arr["EVENT_NAME"]));

        if (!$arType->GetNext()) {
            $obTemplate = new CEventMessage;
            if ($obTemplate->Add($arr)) {
                return true;
            } else {
                $APPLICATION->ThrowException($obTemplate->LAST_ERROR);
                return false;
            }
        }
    }

    public function doUnInstallTemplateEvent()
    {
        global $APPLICATION;
        $rsMess = CEventMessage::GetList($by = "active", $order = "desc", array("TYPE_ID" => "ADD_NEW_ELEMENT_IN_IBLOCK"));
        if ($arRes = $rsMess->GetNext()) {
            $delId = $arRes['ID'];
            $eMessage = new CEventMessage;
            if ($eMessage->Delete(intval($delId))) {
                return true;
            } else {
                $APPLICATION->ThrowException($eMessage->LAST_ERROR);
                return false;
            }
        }
    }

    public function doInstallEvents()
    {
        $arEvents = $this->getModuleEvents();
        $eventManager = EventManager::getInstance();
        foreach ($arEvents as $event) {
            $eventManager->registerEventHandler($event[0], $event[1], $this->MODULE_ID, $event[2], $event[3]);
        }
    }

    public function doUnInstallEvents()
    {
        $arEvents = $this->getModuleEvents();
        $eventManager = EventManager::getInstance();
        foreach ($arEvents as $event) {
            $eventManager->unRegisterEventHandler($event[0], $event[1], $this->MODULE_ID, $event[2], $event[3]);
        }
        return true;
    }

    private function getModuleEvents()
    {
        $eventListenerMap = array(
            ['iblock', 'OnAfterIBlockElementAdd', '\\Neti\\SimpleMail\\Company', 'OnAfterIBlockElementAdd'],
            ['iblock', 'OnAfterIBlockElementUpdate', '\\Neti\\SimpleMail\\Company', 'OnAfterIBlockElementUpdate'],
        );

        return $eventListenerMap;
    }

    /**
     * Возвращает массив со структурой и настройками полей ХБ
     * @param int $hlID Идентификатор хайблока
     * @return array
     */
    private function getHLBlockFields($hlID)
    {
        return array(
            array(
                'ENTITY_ID' => 'HLBLOCK_' . $hlID,
                'FIELD_NAME' => 'UF_NAME',
                'USER_TYPE_ID' => 'string'
            ),
            array(
                'ENTITY_ID' => 'HLBLOCK_' . $hlID,
                'FIELD_NAME' => 'UF_ANONS',
                'USER_TYPE_ID' => 'string'
            ),
            array(
                'ENTITY_ID' => 'HLBLOCK_' . $hlID,
                'FIELD_NAME' => 'UF_DATETIME',
                'USER_TYPE_ID' => 'datetime'
            ),
            array(
                'ENTITY_ID' => 'HLBLOCK_' . $hlID,
                'FIELD_NAME' => 'UF_USER_ID',
                'USER_TYPE_ID' => 'integer'
            ),
            array(
                'ENTITY_ID' => 'HLBLOCK_' . $hlID,
                'FIELD_NAME' => 'UF_ELEMENT_ID',
                'USER_TYPE_ID' => 'integer'
            ),
        );
    }

}