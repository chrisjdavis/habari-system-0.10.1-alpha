<?php namespace Filmio; ?>
<?php if ( !defined( 'FILMIO_PATH' ) ) { die('No direct access'); } ?>
<?php include('header.php'); ?>

<div class="container transparent item controls">

	<?php
	echo $theme->form->get();
	?>

</div>

<div id="comments" class="container main manage comments">

<?php $theme->display('comments_items'); ?>

</div>

<script type="text/javascript">

itemManage.updateURL = Filmio.url.ajaxUpdateComment;
itemManage.fetchURL = "<?php echo URL::get('admin_ajax', array('context' => 'comments')) ?>";
itemManage.fetchReplace = $('#comments');

</script>


<?php include('footer.php'); ?>
