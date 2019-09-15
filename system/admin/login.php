<?php namespace Filmio; ?>
<?php if ( !defined( 'FILMIO_PATH' ) ) { die('No direct access'); } ?>
<!DOCTYPE HTML>
<html>
<head>
	<title><?php _e('Login to %s', array(Options::get( 'title' ))); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width">
	<script type="text/javascript">
	var Filmio = {
		url: { Filmio: '<?php Site::out_url('filmio'); ?>' }
	};
	</script>
	<style>
		.off_reset {}
		
		.on_reset, input[type=submit].on_reset {
			display: none;
		}
		.do_reset .on_reset, .do_reset input[type=submit].on_reset {
			display: block;
		}
		.do_reset .off_reset {
			display: none;
		}

	</style>

	<?php
		Plugins::act( 'admin_header', $this );
		Stack::out( 'admin_header_javascript', Method::create('\\Filmio\\Stack', 'scripts') );
		Stack::out( 'admin_stylesheet', Method::create('\\Filmio\\Stack', 'styles') );
	?>

</head>
<body class="login">
	<div id="page" class="container">
		<div class="columns six offset-by-five">
			<?php echo $form; ?>
			<p class="poweredby"><?php _e('%1$s is powered by %2$s', array(Options::out('title'), '<a href="http://filmio.com/" title="' . _t('Go to the Filmio site') . '">Filmio ' . Version::get_Filmioversion() . '</a>')); ?></p>
		</div>
	</div>
<?php
	Plugins::act( 'admin_footer', $this );
	Stack::out( 'admin_footer_javascript', ' <script src="%s" type="text/javascript"></script>'."\r\n" );
?>

<script type="text/javascript">
	$(document).ready( function() {
		<?php Session::messages_out( true, Method::create( '\\Filmio\\Format', 'humane_messages' ) ); ?>
		$('.reset_link').click(function(){$(this).closest('form').toggleClass('do_reset'); return false;});
	});
</script>
<?php
	include ('db_profiling.php');
?>
</body>
</html>
