<?php
/**
 * Theme 11 time slot legend (Available / Selected / Booked). Hidden by AppScheduler.css, shown only by theme11.css.
 */
$t11_legend = array(
	array('front_t11_legend_available', 'Available', 'pjAsT11LegendFree'),
	array('front_t11_legend_selected', 'Selected', 'pjAsT11LegendSelected'),
	array('front_t11_legend_booked', 'Booked', 'pjAsT11LegendBooked')
);
?>
<ul class="list-unstyled pjAsT11Legend">
	<?php
	foreach ($t11_legend as $item)
	{
		$label = __($item[0], true);
		if ($label === NULL || $label === '')
		{
			$label = $item[1];
		}
		?>
		<li><i class="<?php echo $item[2]; ?>"></i> <?php echo pjSanitize::html($label); ?></li>
		<?php
	}
	?>
</ul>
