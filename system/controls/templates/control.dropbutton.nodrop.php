<?php
/**
 * @var array $_template_attributes
 * @var array $_attributes
 * @var array $actions
 * @var \Filmio\FormControl $first
 * @var \Filmio\FormControl $action
 * @var \Filmio\Theme $theme
 */
?>
<div <?= $_template_attributes['div'] ?>>
	<ul <?= $_template_attributes['ul'] ?> >
		<li><?= $first->get($theme); ?></li>
		<?php foreach($actions as $action): ?>
			<li><?= $action->get($theme); ?></li>
		<?php endforeach; ?>
	</ul>
</div>
