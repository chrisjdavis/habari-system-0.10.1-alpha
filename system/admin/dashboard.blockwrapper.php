<?php namespace Filmio; ?>
<?php if ( !defined( 'FILMIO_PATH' ) ) { die('No direct access'); } ?>
<li class="module columns eight <?php echo $block->css_classes; ?>" id="dashboard_block_<?php echo $block->id; ?>" data-module-area-id="<?php echo $block->id; ?>" data-module-id="<?php echo $block->id; ?>">
	<div class="close"><i class="icon-remove"></i></div>
	<?php if ( isset($block->has_options) && $block->has_options ) : ?>
	<div class="options"><i class="icon-cog"></i></div>
	<?php endif; ?>
	<div class="clear"></div>
	<div class="modulecore">
		<h2>
			<?php if(isset($block->link)): ?>
			<a href="<?php echo $block->link; ?>"><?php echo $block->_title; ?></a>
			<?php else: ?>
			<?php echo $block->title; ?>
			<?php endif; ?>
		</h2>
		<div class="handle">&nbsp;</div>

		<?php echo $content; ?>
	</div>
	<div class="optionswindow">
		<h2><?php echo $block->title; ?></h2>
		<div class="optionsform"></div>
	</div>
</li>