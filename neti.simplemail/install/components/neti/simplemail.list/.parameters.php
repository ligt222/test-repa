<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;

$arComponentParameters = array(
    "PARAMETERS" => array(
        "ELEMENTS_COUNT" => Array(
            "PARENT" => "BASE",
            "NAME" => Loc::getMessage('ELEMENT_COUNT'),
            "TYPE" => "STRING",
            "DEFAULT" => "20",
        ),
        'DISPLAY_TOP_PAGER' => array(
            'PARENT' => 'BASE',
            'NAME' => Loc::getMessage('DISPLAY_TOP_PAGER'),
            'TYPE' => 'CHECKBOX',
            'MULTIPLE' => 'N',
            'DEFAULT' => 'N',
        ),
        'DISPLAY_BOTTOM_PAGER' => array(
            'PARENT' => 'BASE',
            'NAME' => Loc::getMessage('DISPLAY_BOTTOM_PAGER'),
            'TYPE' => 'CHECKBOX',
            'MULTIPLE' => 'N',
            'DEFAULT' => 'Y',
        ),
    )
);