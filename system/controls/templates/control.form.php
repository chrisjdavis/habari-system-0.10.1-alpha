<form <?= $_attributes ?>>
	<?= \Filmio\Utils::setup_wsse() ?>
	<input type="hidden" name="_form_id" value="<?= $_control_id ?>">
	<?= $content ?>
</form>