<?php

namespace Neti\SimpleMail;

use Bitrix\Main;
use Bitrix\Highloadblock\HighloadBlockTable AS HLBT;
use Neti\SimpleMail\SimpleMail;

class Company
{

    public function OnAfterIBlockElementAdd(&$arParams)
    {
        if ($arParams["IBLOCK_ID"] && $arParams["RESULT"] != false) {

            if (intval(\COption::GetOptionString("neti.simplemail", "iblock")) === intval($arParams["IBLOCK_ID"])) {

                \CModule::IncludeModule('highloadblock');

                $hlID = SimpleMail::getHLBlockID();
                if ($hlID){
                    $entity = HLBT::compileEntity($hlID);
                    $entityDataClass = $entity->getDataClass();

                    $arMass = Array(
                        "UF_NAME" => $arParams["NAME"],
                        "UF_ANONS" => $arParams["PREVIEW_TEXT"],
                        "UF_DATETIME" => date("m.d.Y G:i:s"),
                        "UF_USER_ID" => $arParams["CREATED_BY"],
                        "UF_ELEMENT_ID" => $arParams["ID"],
                    );

                    $resAdd = $entityDataClass::add($arMass);

                    if ($resAdd->isSuccess()) {
                        $arSiteId = array();
                        $rsSites = \CSite::GetList($by="id", $order="asc", array("ACTIVE" => "Y"));
                        while ($arSite = $rsSites->Fetch()) {
                            $arSiteId[] = $arSite['LID'];
                        }

                        if (\CModule::IncludeModule("iblock")) {
                            $res = \CIBlock::GetByID(intval($arParams["IBLOCK_ID"]));
                            if ($arRes = $res->GetNext()) {

                                $iblockName = $arRes["NAME"];

                                $arFields = array(
                                    "IBLOCK_NAME" => $iblockName,
                                    "ID" => $arParams["ID"],
                                    "EMAIL_TO" => \COption::GetOptionString("neti.simplemail", "emails"),
                                    "ELEMENT_NAME" => $arParams["NAME"],
                                    "ELEMENT_DESC" => $arParams["PREVIEW_TEXT"]
                                );

                                \CEvent::Send("ADD_NEW_ELEMENT_IN_IBLOCK", $arSiteId, $arFields);

                            }
                        }
                    }
                }
            }
        }

    }

}