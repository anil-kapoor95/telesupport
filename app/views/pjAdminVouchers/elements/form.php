<?php
// label arrays come back sorted by key; show the options in their natural order instead
$voucher_months = (array) __('months', true);
ksort($voucher_months);
$voucher_days_min = (array) __('short_days', true);
ksort($voucher_days_min);
$ordered = function ($name, $keys) {
	$arr = __($name, true);
	$out = array();
	foreach ($keys as $k) { if (isset($arr[$k])) { $out[$k] = $arr[$k]; } }
	foreach ($arr as $k => $v) { if (!isset($out[$k])) { $out[$k] = $v; } }
	return $out;
};
$voucher_valid_labels = function ($arr) {
	// short names for the validity options (older installs still have the long default texts in the database)
	$old = array('fixed' => 'One day, time window', 'period' => 'Date and time period', 'recurring' => 'Weekly, time window');
	$new = array('fixed' => 'Fixed date', 'period' => 'Period', 'recurring' => 'Recurring');
	foreach ($old as $k => $o) { if (isset($arr[$k]) && $arr[$k] === $o) { $arr[$k] = $new[$k]; } }
	return $arr;
};
/**
 * Shared voucher form (create + update).
 * Times are shown in the configured time format (24h or 12h with am/pm) and stored as 24h by the controller.
 * $tpl['arr'] (optional), $tpl['ve_arr'], $tpl['service_arr'], $tpl['errors']
 */
$arr = isset($tpl['arr']) ? $tpl['arr'] : array();
$ve_arr = isset($tpl['ve_arr']) ? $tpl['ve_arr'] : array();
$mode = pjVoucherEngine::timeMode($tpl['option_arr']['o_time_format']);
$jqDateFormat = pjUtil::momentJsDateFormat($tpl['option_arr']['o_date_format']);
$o_date = $tpl['option_arr']['o_date_format'];
$g = function ($key, $default = '') use ($arr) {
	return isset($arr[$key]) && $arr[$key] !== null ? $arr[$key] : $default;
};
$posted = !empty($arr['posted']);
$type = $g('type', 'percent');
$apply = $g('apply', 'total');
$valid = $g('valid', 'always');
$valid_on = $g('valid_on', 'booking');
$status = $g('status', 'T');

// time selects: [prefix][from|to] => hour/minute/ampm values
$times = array();
foreach (array('f', 'p', 'r') as $p)
{
	foreach (array('from', 'to') as $w)
	{
		if ($posted)
		{
			$times[$p][$w] = array('hour' => $g($p . '_hour_' . $w, '00'), 'minute' => $g($p . '_minute_' . $w, '00'), 'ampm' => $g($p . '_ampm_' . $w, ''));
		} else {
			$default = $w === 'from' ? '09:00:00' : '17:00:00';
			$stored = ($valid !== 'always' && !empty($arr['time_' . $w]) && ($p === 'f' && $valid === 'fixed' || $p === 'p' && $valid === 'period' || $p === 'r' && $valid === 'recurring')) ? $arr['time_' . $w] : $default;
			$times[$p][$w] = pjVoucherEngine::from24h($stored, $mode);
		}
	}
}
$timeSelect = function ($p, $w) use ($times, $mode) {
	$t = $times[$p][$w];
	$dtHour = pjDateTime::factory()->attr('name', $p . '_hour_' . $w)->attr('id', $p . '_hour_' . $w)->attr('class', 'form-control')->prop('ampm', $mode)->prop('selected', $t['hour']);
	$dtMin = pjDateTime::factory()->attr('name', $p . '_minute_' . $w)->attr('id', $p . '_minute_' . $w)->attr('class', 'form-control')->prop('step', 1)->prop('selected', $t['minute']);
	$html = '<div class="row"><div class="col-xs-4" style="padding-right:3px">' . $dtHour->hour() . '</div><div class="col-xs-4" style="padding:0 3px">' . $dtMin->minute() . '</div>';
	if ($mode > 0)
	{
		$dtAm = pjDateTime::factory()->attr('name', $p . '_ampm_' . $w)->attr('id', $p . '_ampm_' . $w)->attr('class', 'form-control')->prop('ampm', $mode)->prop('selected', $t['ampm']);
		$html .= '<div class="col-xs-4" style="padding-left:3px">' . $dtAm->ampm() . '</div>';
	}
	return $html . '</div>';
};
$dateField = function ($name, $value) use ($tpl, $jqDateFormat) {
	return '<div class="input-group date" data-provide="datepicker" data-date-autoclose="true" data-date-format="' . pjSanitize::html($jqDateFormat) . '" data-date-week-start="' . (int) $tpl['option_arr']['o_week_start'] . '">'
		. '<input type="text" name="' . $name . '" id="' . $name . '" class="form-control" value="' . pjSanitize::html($value) . '" autocomplete="off">'
		. '<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span></div>';
};
$dateVal = function ($key) use ($arr, $posted, $o_date) {
	if ($posted)
	{
		return isset($arr[$key]) ? $arr[$key] : '';
	}
	return '';
};
$stored_date = function ($col) use ($arr, $o_date) {
	return !empty($arr[$col]) ? pjVoucherEngine::displayDate($arr[$col], $o_date) : '';
};
$f_date = $posted ? $g('f_date') : ($valid === 'fixed' ? $stored_date('date_from') : '');
$p_from = $posted ? $g('p_date_from') : ($valid === 'period' ? $stored_date('date_from') : '');
$p_to = $posted ? $g('p_date_to') : ($valid === 'period' ? $stored_date('date_to') : '');
$r_every = $posted ? $g('r_every') : ($valid === 'recurring' ? $g('every') : 'monday');
$required_msg = __('plugin_base_this_field_is_required', true);
if (!empty($tpl['errors'])) : ?>
	<div class="alert alert-danger">
		<i class="fa fa-exclamation-triangle m-r-xs"></i>
		<?php foreach ($tpl['errors'] as $key) : ?><div><?php echo pjSanitize::html(__($key, true)); ?></div><?php endforeach; ?>
	</div>
<?php endif; ?>
<div id="voucherCfg" style="display:none" data-ampm="<?php echo (int) $mode; ?>" data-currency="<?php echo pjSanitize::html(html_entity_decode(pjCurrency::getCurrencySign($tpl['option_arr']['o_currency'], false), ENT_QUOTES, 'UTF-8')); ?>"
	data-required="<?php echo pjSanitize::html($required_msg); ?>" data-time-order="<?php echo pjSanitize::html(__('voucher_err_time_order', true)); ?>" data-period-order="<?php echo pjSanitize::html(__('voucher_err_period_order', true)); ?>"
	data-exists="<?php echo pjSanitize::html(__('voucher_err_code_exists', true)); ?>" data-code-format="<?php echo pjSanitize::html(__('voucher_err_code', true)); ?>" data-wstart="<?php echo (int) $tpl['option_arr']['o_week_start']; ?>"
	data-months="<?php echo pjSanitize::html(implode('_', $voucher_months)); ?>" data-days="<?php echo pjSanitize::html(implode('_', $voucher_days_min)); ?>"></div>

<input type="hidden" name="apply" value="<?php echo pjSanitize::html($apply); ?>">
<input type="hidden" name="max_uses" id="max_uses" value="<?php echo pjSanitize::html($g('max_uses')); ?>">
<input type="hidden" name="max_uses_per_email" id="max_uses_per_email" value="<?php echo pjSanitize::html($g('max_uses_per_email')); ?>">
<input type="hidden" name="valid_on" value="appointment">
<div class="row">
	<div class="col-lg-3 col-md-6 col-sm-6">
		<div class="form-group">
			<label class="control-label"><?php __('voucher_code'); ?></label>
			<input type="text" name="code" id="code" class="form-control required" maxlength="50" value="<?php echo pjSanitize::html($g('code')); ?>" autocomplete="off" data-msg-required="<?php echo pjSanitize::html($required_msg); ?>">
		</div>
	</div>
	<div class="col-lg-2 col-md-6 col-sm-6">
		<div class="form-group">
			<label class="control-label"><?php __('voucher_type'); ?></label>
			<select name="type" id="type" class="form-control">
				<?php foreach ($ordered('voucher_types', array('percent','amount')) as $k => $v) : ?>
					<option value="<?php echo $k; ?>"<?php echo $type === $k ? ' selected="selected"' : ''; ?>><?php echo pjSanitize::html($v); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>
	<div class="col-lg-3 col-md-6 col-sm-6">
		<div class="form-group">
			<label class="control-label"><?php __('voucher_discount'); ?></label>
			<div class="input-group group-fa-change">
				<input type="text" name="discount" id="discount" class="form-control text-right required" value="<?php echo pjSanitize::html($g('discount') !== '' ? number_format((float) $g('discount'), 2, '.', '') : ''); ?>" autocomplete="off" data-msg-required="<?php echo pjSanitize::html($required_msg); ?>">
				<span class="input-group-addon"><strong class="voucher-unit">%</strong></span>
			</div>
		</div>
	</div>
	<div class="col-lg-4 col-md-6 col-sm-6">
		<div class="form-group">
			<label class="control-label"><?php __('voucher_services'); ?></label>
			<select name="service_id[]" id="service_id" multiple="multiple" class="form-control select-item" data-placeholder="-- <?php echo pjSanitize::html(__('lblAll', true)); ?> --">
				<?php foreach ($tpl['service_arr'] as $s) : ?>
					<option value="<?php echo (int) $s['id']; ?>"<?php echo isset($ve_arr[$s['id']]) ? ' selected="selected"' : ''; ?>><?php echo pjSanitize::html($s['name'] !== null && $s['name'] !== '' ? $s['name'] : '#' . $s['id']); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-lg-3 col-md-6 col-sm-6">
		<div class="form-group">
			<label class="control-label"><?php __('voucher_valid'); ?></label>
			<select name="valid" id="valid" class="form-control">
				<?php foreach ($voucher_valid_labels($ordered('voucher_valids', array('always','fixed','period','recurring'))) as $k => $v) : ?>
					<option value="<?php echo $k; ?>"<?php echo $valid === $k ? ' selected="selected"' : ''; ?>><?php echo pjSanitize::html($v); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>
	<div class="col-lg-3 col-md-6 col-sm-6">
		<div class="form-group">
			<label class="control-label"><?php __('voucher_status'); ?></label>
			<select name="status" id="status" class="form-control">
				<option value="T"<?php echo $status === 'T' ? ' selected="selected"' : ''; ?>><?php __('voucher_status_active'); ?></option>
				<option value="F"<?php echo $status === 'F' ? ' selected="selected"' : ''; ?>><?php __('voucher_status_inactive'); ?></option>
			</select>
		</div>
	</div>
</div>

<div class="valid-box" id="valid_fixed" style="display:none">
	<div class="row">
		<div class="col-lg-3 col-md-4 col-sm-6"><div class="form-group"><label class="control-label"><?php __('voucher_date'); ?></label><?php echo $dateField('f_date', $f_date); ?></div></div>
		<div class="col-lg-3 col-md-4 col-sm-6"><div class="form-group"><label class="control-label"><?php __('voucher_time_from'); ?></label><?php echo $timeSelect('f', 'from'); ?></div></div>
		<div class="col-lg-3 col-md-4 col-sm-6"><div class="form-group"><label class="control-label"><?php __('voucher_time_to'); ?></label><?php echo $timeSelect('f', 'to'); ?></div></div>
	</div>
</div>
<div class="valid-box" id="valid_period" style="display:none">
	<div class="row">
		<div class="col-lg-3 col-md-6 col-sm-6"><div class="form-group"><label class="control-label"><?php __('voucher_date_from'); ?></label><?php echo $dateField('p_date_from', $p_from); ?></div></div>
		<div class="col-lg-3 col-md-6 col-sm-6"><div class="form-group"><label class="control-label"><?php __('voucher_time_from'); ?></label><?php echo $timeSelect('p', 'from'); ?></div></div>
		<div class="col-lg-3 col-md-6 col-sm-6"><div class="form-group"><label class="control-label"><?php __('voucher_date_to'); ?></label><?php echo $dateField('p_date_to', $p_to); ?></div></div>
		<div class="col-lg-3 col-md-6 col-sm-6"><div class="form-group"><label class="control-label"><?php __('voucher_time_to'); ?></label><?php echo $timeSelect('p', 'to'); ?></div></div>
	</div>
	<input type="text" name="validate_period" id="validate_period" style="position:absolute;left:-9999px;width:1px;height:1px" tabindex="-1" aria-hidden="true">
</div>
<div class="valid-box" id="valid_recurring" style="display:none">
	<div class="row">
		<div class="col-lg-3 col-md-4 col-sm-6">
			<div class="form-group">
				<label class="control-label"><?php __('voucher_every'); ?></label>
				<select name="r_every" id="r_every" class="form-control">
					<?php foreach ($ordered('voucher_days', array('monday','tuesday','wednesday','thursday','friday','saturday','sunday')) as $k => $v) : ?>
						<option value="<?php echo $k; ?>"<?php echo $r_every === $k ? ' selected="selected"' : ''; ?>><?php echo pjSanitize::html($v); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<div class="col-lg-3 col-md-4 col-sm-6"><div class="form-group"><label class="control-label"><?php __('voucher_time_from'); ?></label><?php echo $timeSelect('r', 'from'); ?></div></div>
		<div class="col-lg-3 col-md-4 col-sm-6"><div class="form-group"><label class="control-label"><?php __('voucher_time_to'); ?></label><?php echo $timeSelect('r', 'to'); ?></div></div>
	</div>
</div>
