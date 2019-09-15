<?php namespace Filmio; ?>
<?php if ( !defined( 'FILMIO_PATH' ) ) { die('No direct access'); } ?>

</div>

	<div id="footer" class="columns sixteen">
		<p>
			<span><a href="http://Filmioproject.org/" title="<?php _e('Go to the Filmio site'); ?>">Filmio
		<?php
		echo Version::get_Filmioversion();
		?> </a></span>
		 <span class="middot">&middot;</span>
		 <span><?php _e('Logged in as'); ?></span>
		 <?php if ( User::identify()->can( 'manage_users' ) || User::identify()->can( 'manage_self' ) ) { ?>
				 <a href="<?php Site::out_url( 'admin' ); ?>/user" title="<?php _e('Go to your user page'); ?>"><?php echo User::identify()->displayname ?></a>
		<?php } else { ?>
				 <span><?php echo User::identify()->displayname ?></span>
		<?php } ?>
		 <span class="middot">&middot;</span>
		 <span><a href="<?php Site::out_url( 'filmio' ); ?>/doc/manual/index.html" onclick="popUp(this.href); return false;" title="<?php _e('Open the Filmio manual in a new window'); ?>"><?php _e('Manual'); ?></a></span>
		<?php
			if ( User::identify()->can('super_user') ) {
				?>
					<span class="middot">&middot;</span>
					<span><a href="<?php Site::out_url( 'admin' ); ?>/sysinfo" title="<?php _e('Display information about the server and Filmio'); ?>"> <?php _e( 'System Information'); ?></a></span>
				<?php
			}
		?>
	
		</p>
	</div>
<?php
	Plugins::act( 'admin_footer', $this );
	Stack::out( 'admin_footer_javascript', Method::create('\\Filmio\\Stack', 'scripts') );
	include ('db_profiling.php');
?>

<?php if ( Session::has_messages() ): ?>
	<script type="text/javascript">
	jQuery(document).ready(function() {
		<?php Session::messages_out( true, Method::create( '\\Filmio\\Format', 'humane_messages' ) ); ?>
	})
  </script>
<?php endif; ?>

</body>
</html>
