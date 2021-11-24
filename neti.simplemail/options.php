<?
if (!$USER->IsAdmin())
    return;

$module_id = 'neti.simplemail';

/* ВКЛАДКИ */
$arTabs = [
    [
        'DIV' => 'NetiOptions',
        'TAB' => 'Настройка данных',
        'ICON' => 'ib_settings',
        'TITLE' => 'Настройка общей информации'
    ]
];
$tabControl = new CAdminTabControl("tabControl", $arTabs);

if (CModule::IncludeModule("iblock")) {
    $arIBlockSelect = [];
    $res = CIBlock::GetList(
        array(),
        array(

            'ACTIVE' => 'Y',
            "CNT_ACTIVE" => "Y",
        ), true
    );
    while ($arRes = $res->Fetch()) {
        $arIBlockSelect[$arRes['ID']] = $arRes['NAME'];

    }
}

$arOptions = [
    'NetiOptions' => [
        ['', 'Настройка номера телефона'],
        ['name_organization', 'Наиминование организации', '', ['text', 40]],
        ['type_choice', 'Тип организации', '', ['selectbox'], ['U' => 'ОАО', 'A' => 'ЗАО', 'D' => 'ООО', 'P' => 'ИП']],
        ['type_usn', 'Использует УСН', 'N', ['checkbox']],
        ['iblock', 'Выбор инфоблока', '', ['selectbox'], $arIBlockSelect],
        ['emails', 'Email адреса', 'test@test.test', ['text', 50]],
    ]
];

// Запись информации
//-------------------------------------------------------------------------------
$arAllOptions = [];
foreach ($arTabs as $arTab) {
    $optName = $arTab['DIV'];
    $arAllOptions = array_merge($arAllOptions, $arOptions[$optName]);
}

if ($REQUEST_METHOD == "POST" && strlen($Update . $Apply . $RestoreDefaults) > 0 && check_bitrix_sessid()) {

    if (strlen($RestoreDefaults) > 0) {
        COption::RemoveOption($module_id);
    } else {
        foreach ($arAllOptions as $arOption) {
            $name = $arOption[0];
            if (empty($name)) {
                continue;
            }
            $val = $_REQUEST[$name];
            if ($arOption[3][0] == 'checkbox' && $val != 'Y')
                $val = 'N';
            if ($arOption[3][0] == 'selectboxMulti') {
                $val = json_encode($val);
            }
            if ($arOption[3][0] == 'bxMuliElementLink') {
                $val = array_values(array_filter($val));
                $val = json_encode($val);
            }
            COption::SetOptionString($module_id, $name, $val, $arOption[1]);
        }
    }
    if (strlen($Update) > 0 && strlen($_REQUEST["back_url_settings"]) > 0)
        LocalRedirect($_REQUEST["back_url_settings"]);
    else
        LocalRedirect($APPLICATION->GetCurPage() . "?mid=" . urlencode($mid) . "&lang=" . urlencode(LANGUAGE_ID) . "&back_url_settings=" . urlencode($_REQUEST["back_url_settings"]) . "&" . $tabControl->ActiveTabParam());
}
//-------------------------------------------------------------------------------

$tabControl->Begin();
?>
<form method="post"
      action="<? echo $APPLICATION->GetCurPage() ?>?mid=<?= urlencode($mid) ?>&amp;lang=<? echo LANGUAGE_ID ?>">

    <? foreach ($arTabs as $arTab) {
        $optName = $arTab['DIV'];
        $tabControl->BeginNextTab(); ?>

        <? foreach ($arOptions[$optName] as $arOption) { ?>
            <? if (empty($arOption[0])) { ?>
                <tr class="heading">
                    <td colspan="2"><b><?= $arOption[1] ?></b></td>
                </tr>
            <? } else { ?>
                <?
                $val = COption::GetOptionString($module_id, $arOption[0]);
                $type = $arOption[3];
                ?>
                <tr>
                    <td width="40%" nowrap <?= ($type[0] == "textarea") ? 'class="adm-detail-valign-top"' : '' ?>>
                        <label for="<?= htmlspecialcharsbx($arOption[0]) ?>"><?= $arOption[1] ?>:</label>
                    </td>
                    <td width="60%">
                        <? if ($type[0] == "checkbox") { ?>
                            <input type="checkbox" id="<?= htmlspecialcharsbx($arOption[0]) ?>"
                                   name="<?= htmlspecialcharsbx($arOption[0]) ?>"
                                   value="Y"<?= ($val == "Y") ? ' checked' : '' ?>>
                        <? } elseif ($type[0] == "text") { ?>
                            <input type="text" size="<?= $type[1] ?>" maxlength="255"
                                   value="<?= htmlspecialcharsbx($val) ?>"
                                   name="<?= htmlspecialcharsbx($arOption[0]) ?>">
                        <? } elseif ($type[0] == "textarea") { ?>
                            <textarea rows="<?= $type[1] ?>" cols="<?= $type[2] ?>"
                                      name="<?= htmlspecialcharsbx($arOption[0]) ?>"><?= htmlspecialcharsbx($val) ?></textarea>
                        <? } elseif ($type[0] == "selectbox") { ?>
                            <select name="<? echo htmlspecialcharsbx($arOption[0]) ?>"
                                    id="<? echo htmlspecialcharsbx($arOption[0]) ?>">
                                <? foreach ($arOption[4] as $v => $k) {
                                    ?>
                                    <option value="<?= $v ?>"<? if ($val == $v) echo " selected"; ?>><?= $k ?></option><?
                                }
                                ?>
                            </select>
                        <? } ?>

                    </td>
                </tr>
            <? } ?>
        <? } ?>
    <? } ?>

    <? $tabControl->Buttons(); ?>
    <input type="submit" name="Update" value="Сохранить" title="Сохранить" class="adm-btn-save">
    <input type="submit" name="Apply" value="Применить" title="Применить">
    <input type="reset" name="Cancel" value="Отмена" title="Отмена">
    <input type="submit" name="RestoreDefaults" title="Настройки по-умолчанию"
           OnClick="return confirm('Восстановить значения по-умолчанию?')"
           value="Настройки по-умолчанию">
    <?= bitrix_sessid_post(); ?>
    <? $tabControl->End(); ?>
</form>