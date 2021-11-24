<?global $APPLICATION; ?>
<form action="<?echo $APPLICATION->GetCurPage()?>">
<?=bitrix_sessid_post()?>
    <input type="hidden" name="lang" value="<?echo LANG?>">
    <input type="hidden" name="id" value="neti.simplemail">
    <input type="hidden" name="uninstall" value="Y">
    <input type="hidden" name="step" value="2">
    <?echo CAdminMessage::ShowMessage(GetMessage("MOD_UNINST_WARN"))?>
    <p><?echo GetMessage("MODULE_UNSTEP_BD")?></p>
    <p><input type="checkbox" name="savedata" id="savedata" value="Y" checked><label for="savedata"><?echo GetMessage("MODULE_UNSTEP_BD_SAVE_TABLE")?></label></p>
    <input type="submit" name="inst" value="<?echo GetMessage("MODULE_UNSTEP_BD_DEL")?>">
</form>