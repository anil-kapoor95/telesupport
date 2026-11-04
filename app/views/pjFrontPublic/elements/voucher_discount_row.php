<?php
/**
 * "Discount (CODE)" row of the totals on the details and confirmation pages (all themes).
 * $tpl['summary']['discount'] / ['voucher'] come from pjFront::getSummary()
 */
if (!empty($tpl['summary']['voucher']) && (float) $tpl['summary']['discount'] > 0)
{
	?>
	<div class="clearfix pjAsVoucherRow">
		<h3 class="pull-left text-uppercase pjAsSectionTotal"><?php __('front_voucher_discount'); ?> (<?php echo pjSanitize::html($tpl['summary']['voucher']['code']); ?><?php echo $tpl['summary']['voucher']['type'] === 'percent' ? ', ' . pjSanitize::html($tpl['summary']['voucher']['label']) : ''; ?>)</h3><!-- /.pull-left text-uppercase pjAsSectionTotal -->

		<h3 class="pull-right text-uppercase pjAsSectionPrice">
			<strong>-<?php echo pjCurrency::formatPrice($tpl['summary']['discount']); ?></strong>
		</h3><!-- /.pull-right text-uppercase pjAsSectionPrice -->
	</div><!-- /.clearfix -->
	<?php
}
?>
