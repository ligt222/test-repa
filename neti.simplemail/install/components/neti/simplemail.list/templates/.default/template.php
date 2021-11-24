<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<? if ($arParams['DISPLAY_TOP_PAGER'] == 'Y') { ?>
    <?$APPLICATION->IncludeComponent(
        'bitrix:main.pagenavigation',
        '',
        array(
            'NAV_OBJECT' => $arResult['NAV'],
            'CACHE_TYPE' => $arParams['CACHE_TYPE'],
            'CACHE_TIME' => $arParams['CACHE_TIME'],
            'SEF_MODE'  => $arParams['SEF_MODE'],
        ),
        $component);
    ?>
<? } ?>
<div style="width: 100%">
    <table style="width: 100%">
        <thead>
        <tr>
            <td align="center" style="color: darkgrey">ID</td>
            <td align="center" style="color: darkgrey">Название элемента</td>
            <td align="center" style="color: darkgrey">Анонс</td>
            <td align="center" style="color: darkgrey">Дата/Время</td>
            <td align="center" style="color: darkgrey">ID Пользователя</td>
            <td align="center" style="color: darkgrey">ID Элемента</td>
        </tr>
        </thead>
        <tbody>
        <?foreach ($arResult["ITEMS"] as $item) { ?>
            <tr>
                <td align="center"><?=$item["ID"]; ?></td>
                <td align="center"><?=$item["NAME"]; ?></td>
                <td align="center"><?=$item["ANONS"]; ?></td>
                <td align="center"><?=$item["DATETIME"]; ?></td>
                <td align="center"><?=$item["USER_ID"]; ?> - <?=$item['USER_NAME']?></td>
                <td align="center"><?=$item["ELEMENT_ID"];?> - <?=$item['ELEMENT_NAME']; ?></td>
            </tr>
        <? } ?>
        </tbody>
    </table>
</div>
<? if ($arParams['DISPLAY_BOTTOM_PAGER'] == 'Y') { ?>
    <?$APPLICATION->IncludeComponent(
        'bitrix:main.pagenavigation',
        '',
        array(
            'NAV_OBJECT' => $arResult['NAV'],
            'CACHE_TYPE' => $arParams['CACHE_TYPE'],
            'CACHE_TIME' => $arParams['CACHE_TIME'],
            'SEF_MODE'  => $arParams['SEF_MODE'],
        ),
        $component);
    ?>
<? } ?>

