<?php
/**
 * Theme 11 "Your appointment" card (checkout + preview). Hidden by AppScheduler.css, shown only by theme11.css.
 * Uses the same data as elements/cart.php and the totals below it: $cart, $cart_arr, $tpl['summary'].
 * The remove button carries the same class + data attributes as the original one, so the existing JS handles it.
 */
$t11_title = __('front_t11_your_appointment', true);
if ($t11_title === NULL || $t11_title === '')
{
	$t11_title = 'Your appointment';
}
?>
<div class="pjAsT11Summary">
	<h3 class="pjAsT11SummaryTitle"><?php echo pjSanitize::html($t11_title); ?></h3>
	<?php
	$t11_i = -1;
	foreach ($cart as $t11_key => $t11_value)
	{
		$t11_i++;
		list($t11_cid, $t11_date, $t11_service_id, $t11_start_ts, $t11_end_ts, $t11_employee_id) = explode("|", $t11_key);
		$t11_from = $t11_start_ts + $cart_arr[$t11_service_id]['before'] * 60;
		$t11_to = $t11_end_ts - $cart_arr[$t11_service_id]['after'] * 60;
		?>
		<div class="pjAsT11Item">
			<div class="pjAsT11ItemText">
				<span class="pjAsT11ItemHead">
					<strong class="pjAsT11ItemName"><?php echo pjSanitize::html($cart_arr[$t11_service_id]['name']); ?></strong>
					<span class="pjAsT11ItemPrice"><?php echo pjCurrency::formatPrice($cart_arr[$t11_service_id]['price']); ?></span>
				</span>
				<span class="pjAsT11ItemEmployee"><?php echo pjSanitize::html($cart_arr[$t11_service_id]['employee_arr'][$t11_employee_id]['name']); ?></span>
				<?php $ld_i = $t11_i; $ld_class = 'pjAsT11ItemDiscount'; include PJ_VIEWS_PATH . 'pjFrontPublic/elements/line_discount.php'; ?>
				<span class="pjAsT11ItemWhen"><?php echo date($tpl['option_arr']['o_date_format'], strtotime($t11_date)); ?> &middot; <?php echo date($tpl['option_arr']['o_time_format'], $t11_from); ?> - <?php echo date($tpl['option_arr']['o_time_format'], $t11_to); ?></span>
			</div>
			<button type="button" class="pjAsT11ItemRemove asSelectorRemoveFromCart" aria-label="Remove" data-start_ts="<?php echo $t11_start_ts; ?>" data-end_ts="<?php echo $t11_end_ts; ?>" data-date="<?php echo $t11_date; ?>" data-service_id="<?php echo $t11_service_id; ?>" data-employee_id="<?php echo $t11_employee_id; ?>"><span aria-hidden="true">&times;</span></button>
		</div>
		<?php
	}
	?>
	<?php if (isset($t11_voucher_box) && $t11_voucher_box) { include PJ_VIEWS_PATH . 'pjFrontPublic/elements/voucher.php'; } ?>
	<dl class="pjAsT11Totals">
		<div class="pjAsT11TotalRow"><dt><?php __('single_price'); ?></dt><dd><?php echo pjCurrency::formatPrice($tpl['summary']['price']); ?></dd></div>
		<?php if (!empty($tpl['summary']['voucher']) && (float) $tpl['summary']['discount'] > 0) : ?>
			<div class="pjAsT11TotalRow pjAsT11TotalDiscount"><dt><?php __('front_voucher_discount'); ?> (<?php echo pjSanitize::html($tpl['summary']['voucher']['code']); ?><?php echo $tpl['summary']['voucher']['type'] === 'percent' ? ', ' . pjSanitize::html($tpl['summary']['voucher']['label']) : ''; ?>)</dt><dd>-<?php echo pjCurrency::formatPrice($tpl['summary']['discount']); ?></dd></div>
		<?php endif; ?>
		<?php if ($tpl['summary']['tax'] > 0) : ?>
			<div class="pjAsT11TotalRow"><dt><?php __('front_tax'); ?> (<?php echo $tpl['option_arr']['o_tax']; ?>%)</dt><dd><?php echo pjCurrency::formatPrice($tpl['summary']['tax']); ?></dd></div>
		<?php endif; ?>
		<div class="pjAsT11TotalRow pjAsT11TotalMain"><dt><?php __('front_total'); ?></dt><dd><?php echo pjCurrency::formatPrice($tpl['summary']['total']); ?></dd></div>
		<div class="pjAsT11TotalRow pjAsT11TotalDeposit"><dt><?php __('front_deposit'); ?><?php echo $tpl['option_arr']['o_deposit_type'] == 'percent' ? ' (' . $tpl['option_arr']['o_deposit'] . '%)' : NULL; ?></dt><dd><?php echo pjCurrency::formatPrice($tpl['summary']['deposit']); ?></dd></div>
	</dl>
</div>
