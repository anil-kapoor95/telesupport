<?php
/**
 * Per-service voucher discount note under a cart line ("Discount: -3.50 $ (10%)").
 * Expects $ld_i = position of the line in the cart; the numbers come from pjFront::getSummary() ($tpl['summary']['services'][i]['discount']).
 * Lines the voucher does not apply to (other service / outside its date or time) print nothing.
 * $ld_class = CSS class of the printed element.
 */
$ld_amount = isset($tpl['summary']['services'][$ld_i]['discount']) ? (float) $tpl['summary']['services'][$ld_i]['discount'] : 0;
if ($ld_amount > 0 && !empty($tpl['summary']['voucher']))
{
	$ld_rate = $tpl['summary']['voucher']['type'] === 'percent' ? ' (' . pjSanitize::html($tpl['summary']['voucher']['label']) . ')' : '';
	?><span class="<?php echo $ld_class; ?>"><?php __('front_voucher_discount'); ?>: -<?php echo pjCurrency::formatPrice($ld_amount); ?><?php echo $ld_rate; ?></span><?php
}
