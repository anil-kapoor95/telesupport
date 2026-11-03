<div class="pjIcCalendar">
	<div class="pj-calendar">
		<?php
		$date = ($controller->_get->check('date') && ($controller->_get->toString('date'))) ? $controller->_get->toString('date') : date('Y-m-d');
		list($year, $month,) = explode("-", $date);
		echo $tpl['calendar']->getMonthHTML((int) $month, $year);
		?>
	</div>
</div>