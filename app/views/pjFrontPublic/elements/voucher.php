<?php
/**
 * Voucher (discount code) box of the details page. Printed twice (plain themes + Theme 11 summary card); the copy that
 * does not belong to the active theme is hidden by CSS. The JS looks the elements up by class inside the closest
 * .pjAsVoucher, so both copies work.
 * $tpl['summary']['voucher'] / ['voucher_error'] come from pjFront::getSummary()
 */
$v_applied = !empty($tpl['summary']['voucher']) ? $tpl['summary']['voucher'] : NULL;
?>
<div class="pjAsVoucher<?php echo $v_applied ? ' pjAsVoucherApplied' : ''; ?>">
	<?php if (!empty($tpl['summary']['voucher_error']['text'])) : ?>
		<div class="alert alert-warning pjAsVoucherNotice" role="alert"><?php echo pjSanitize::html($tpl['summary']['voucher_error']['text']); ?></div>
	<?php endif; ?>
	<?php if ($v_applied) : ?>
		<div class="pjAsVoucherOn">
			<span class="pjAsVoucherTag"><i class="fa fa-tag"></i> <strong><?php echo pjSanitize::html($v_applied['code']); ?></strong> <span class="pjAsVoucherRate">(<?php echo pjSanitize::html($v_applied['label']); ?>)</span></span>
			<a href="#" class="asSelectorVoucherRemove pjAsVoucherRemove"><?php __('front_voucher_remove'); ?></a>
		</div>
	<?php else : ?>
		<label class="pjAsVoucherLabel"><?php __('front_voucher_label'); ?></label>
		<div class="pjAsVoucherForm">
			<input type="text" class="form-control asSelectorVoucherCode" maxlength="50" autocomplete="off" autocapitalize="characters" spellcheck="false" placeholder="<?php __('front_voucher_placeholder', false, true); ?>" aria-label="<?php __('front_voucher_label', false, true); ?>">
			<button type="button" class="btn btn-default pjAsBtn asSelectorVoucherApply"><?php __('front_voucher_apply'); ?></button>
		</div>
	<?php endif; ?>
	<div class="pjAsVoucherMsg asSelectorVoucherMsg" role="status" aria-live="polite" style="display:none"></div>
</div>
