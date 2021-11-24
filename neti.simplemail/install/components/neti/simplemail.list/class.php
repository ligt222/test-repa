<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;

if (!Loader::includeModule("neti.simplemail") && Loader::includeModule("highloadblock")) {
    return;
}

use Neti\SimpleMail\SimpleMail;
use Bitrix\Main\Entity;

class simplemail_list extends CBitrixComponent
{
    function executeComponent()
    {
        $arParams = $this->arParams;
        $itemsCnt = SimpleMail::getRecordCount();
        // проверяем на наличие объявлений
        $successMessage = '';
        if (isset($_SESSION['neti.simplemail']['SUCCESS_MESSAGE'])) {
            $successMessage = $_SESSION['neti.simplemail']['SUCCESS_MESSAGE'];
            unset($_SESSION['neti.simplemail']['SUCCESS_MESSAGE']);
            // если есть объявление, не используем кэширование
            $this->arParams['CACHE_TYPE'] = 'N';
        }
        $nav = new \Bitrix\Main\UI\PageNavigation('simplemail_list');
        // устанавливаем количество записей на страницу
        $nav->allowAllRecords(false)->setPageSize($arParams['ELEMENTS_COUNT']);
        // устанавливаем количество записей для пагинации
        $nav->setRecordCount($itemsCnt)->initFromUri();

        if ($this->startResultCache(FALSE, array('NAV_PAGE' => $nav->getCurrentPage()))) {
            $arItems = array();
            $entityDataClass = SimpleMail::getEntity();
            // получаем данные
            $rsData = $entityDataClass::getList(array(
                "select" => array(
                    "ID",
                    "UF_NAME",
                    "UF_ANONS",
                    "UF_DATETIME",
                    "UF_USER_ID",
                    "UF_ELEMENT_ID",
                    "TEST_FIELD",
                    "TEST_FIELD_EL",

                ),
                'runtime' => array(
                    new Entity\ReferenceField(
                        'TEST_FIELD',
                        '\Bitrix\Main\User',
                        array('=this.UF_USER_ID' => 'ref.ID'),
                        array('join_type' => 'left')
                    ),
                    new Entity\ReferenceField(
                        'TEST_FIELD_EL',
                        '\Bitrix\Iblock\ElementTable',
                        array('=this.UF_ELEMENT_ID' => 'ref.ID'),
                        array('join_type' => 'left')
                    ),
                ),
                'offset' => $nav->getOffset(),
                "limit" => $nav->GetLimit(),
                "order" => array("ID" => "ASC"),
                "filter" => array(),
            ));

            while ($arItem = $rsData->Fetch()) {
                $arItems[$arItem['ID']]['ID'] = $arItem['ID'];
                $arItems[$arItem['ID']]['NAME'] = $arItem['UF_NAME'];
                $arItems[$arItem['ID']]['ANONS'] = $arItem['UF_ANONS'];
                $arItems[$arItem['ID']]['DATETIME'] = $arItem['UF_DATETIME'];
                $arItems[$arItem['ID']]['USER_ID'] = $arItem['UF_USER_ID'];
                $arItems[$arItem['ID']]['USER_NAME'] = $arItem['SIMPLE_MAIL_TEST_FIELD_LOGIN'];
                $arItems[$arItem['ID']]['ELEMENT_ID'] = $arItem['UF_ELEMENT_ID'];
                $arItems[$arItem['ID']]['ELEMENT_NAME'] = $arItem['SIMPLE_MAIL_TEST_FIELD_EL_NAME'];
            }
            $this->arResult = array(
                'ITEMS' => $arItems,
                'NAV' => $nav,
                'SUCCESS' => $successMessage,
            );
            $this->IncludeComponentTemplate();
        }
    }

}