<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$arComponentDescription = array(
    'NAME' => Loc::getMessage('NETI_SIMPLEMAIL_COMPONENT_LIST_NAME'),
    'DESCRIPTION' => Loc::getMessage('NETI_SIMPLEMAIL_COMPONENT_LIST_DESCRIPTION'),
    'COMPLEX' => 'N',
    'PATH' => array(
        'ID' => 'neti',
        'NAME' => Loc::getMessage('NETI_SIMPLEMAIL_COMPONENT_PARTNER_NAME'),
        'CHILD' => array(
            'ID' => 'neti.simplemail',
            'NAME' => Loc::getMessage('NETI_SIMPLEMAIL_COMPONENT_PATH_NAME'),
            'CHILD' => array(
                'ID' => 'simplemail_cmpx',
            ),
        )
    ),
);