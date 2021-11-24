<?php

namespace Neti\SimpleMail;

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Highloadblock\HighloadBlockTable AS HLBT;

class SimpleMail
{

    const HL_TABLE_NAME = 'neti_simplemail';
    const HL_ENTITY_NAME = 'SimpleMail';

    /**
     * Возвращает идентификатор хайлоад блока, используемого для хранения изображений
     * @return int
     */
    static function getHLBlockID()
    {
        $dbRes = HLBT::getList(array(
            'filter' => array(
                'TABLE_NAME' => self::HL_TABLE_NAME,
            )
        ));
        if ($arRes = $dbRes->fetch()) {
            return $arRes['ID'];
        } else {
            return false;
        }
    }

    /**
     * Возвращает название сущности для работы с ХБ
     * @return mixed
     */
    static function getEntity()
    {
        static $entityDataClass = null;
        if (!empty($entityDataClass)) {
            return $entityDataClass;
        }

        $hlID = self::getHLBlockID();
        if (empty($hlID)) { return null; }

        $arHLB = HLBT::getById($hlID)->fetch();
        if($arHLB) {
            $obEntity = HLBT::compileEntity($arHLB);
            $entityDataClass = $obEntity->getDataClass();
            return $entityDataClass;
        } else {
            return NULL;
        }
    }

    /**
     * Возвращает текущее количество записей таблицы ХБ
     * @return int
     */
    static function getRecordCount()
    {
        $entity = self::getEntity();
        if (empty($entity)) { return 0; }

        $arParams = array(
            'select' => array('CNT'),
            'runtime' => array(
                new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)')
            ),
            'limit' => 1,
        );

        $dbResult = $entity::getList($arParams);
        if ($arRes = $dbResult->fetch()) {
            return $arRes['CNT'];
        } else {
            return 0;
        }
    }
}


