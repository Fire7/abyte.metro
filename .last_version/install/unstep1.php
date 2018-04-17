<form action="<?echo $APPLICATION->GetCurPage()?>">
<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
	<input type="hidden" name="id" value="abyte.metro">
	<input type="hidden" name="uninstall" value="Y">
	<input type="hidden" name="STEP" value="2">
	<?echo CAdminMessage::ShowMessage(GetMessage("MOD_UNINST_WARN"))?>
	<p><?echo GetMessage("MOD_UNINST_SAVE")?></p>
	<p><input type="checkbox" name="savebase" id="savebase" value="Y" checked><label for="savedata"><?echo GetMessage("MOD_UNINST_SAVE_TABLES")?></label></p>
	
	<input type="submit" name="inst" value="<?echo GetMessage("MOD_UNINST_DEL")?>">
</form>