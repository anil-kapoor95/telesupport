<?php
/**
 * Theme 11 step tracker (Service > Time > Details > Confirm).
 * The markup is always printed, but AppScheduler.css hides it; only theme11.css shows it, so themes 1-10 look exactly as before.
 * $t11_step: 0 = service, 1 = time, 2 = details, 3 = confirm, 4 = finished (all steps done)
 */
$t11_step = isset($t11_step) ? (int) $t11_step : 0;
$t11_steps = array(
	array('front_t11_step_service', 'Service', '<path d="M8 6h13M8 12h13M8 18h13"/><circle cx="3.5" cy="6" r="1"/><circle cx="3.5" cy="12" r="1"/><circle cx="3.5" cy="18" r="1"/>'),
	array('front_t11_step_time', 'Time', '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>'),
	array('front_t11_step_details', 'Details', '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/>'),
	array('front_t11_step_confirm', 'Confirm', '<path d="M5 13l4 4L19 7"/>')
);
?>
<ol class="pjAsT11Steps">
	<?php
	foreach ($t11_steps as $i => $step)
	{
		$label = __($step[0], true);
		if ($label === NULL || $label === '')
		{
			$label = $step[1];
		}
		$state = $i < $t11_step ? ' pjAsT11StepDone' : ($i == $t11_step ? ' pjAsT11StepCurrent' : '');
		?>
		<li class="pjAsT11Step<?php echo $state; ?>">
			<span class="pjAsT11StepBar"></span>
			<span class="pjAsT11StepIcon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?php echo $step[2]; ?></svg></span>
			<span class="pjAsT11StepLabel"><?php echo pjSanitize::html($label); ?></span>
		</li>
		<?php
	}
	?>
</ol>
