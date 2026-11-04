<?php
$titles = __('error_titles', true);
$bodies = __('error_bodies', true);
$vv = __('voucher_valids', true);
foreach (array('fixed' => array('One day, time window', 'Fixed date'), 'period' => array('Date and time period', 'Period'), 'recurring' => array('Weekly, time window', 'Recurring')) as $k => $pair) { if (isset($vv[$k]) && $vv[$k] === $pair[0]) { $vv[$k] = $pair[1]; } }
$vt = __('voucher_types', true);
$filter = __('filter', true, true);
?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-sm-12">
        <div class="row">
            <div class="col-sm-10">
                <h2><?php __('infoVouchersTitle'); ?></h2>
            </div>
        </div><!-- /.row -->

        <p class="m-b-none"><i class="fa fa-info-circle"></i> <?php echo str_replace(', to a date or time window and to a maximum number of uses', ' or to a date or time window', __('infoVouchersDesc', true)); ?></p>
    </div><!-- /.col-md-12 -->
</div>

<div class="wrapper wrapper-content animated fadeInRight">
	<?php
	$error_code = $controller->_get->toString('err');
	if (!empty($error_code))
	{
		switch (true)
		{
			case in_array($error_code, array('AV01', 'AV03')):
				?>
				<div class="alert alert-success">
					<i class="fa fa-check m-r-xs"></i>
					<strong><?php echo @$titles[$error_code]; ?></strong>
					<?php echo @$bodies[$error_code]; ?>
				</div>
				<?php
				break;
			case in_array($error_code, array('AV02', 'AV04', 'AV08')):
				?>
				<div class="alert alert-danger">
					<i class="fa fa-exclamation-triangle m-r-xs"></i>
					<strong><?php echo @$titles[$error_code]; ?></strong>
					<?php echo @$bodies[$error_code]; ?>
				</div>
				<?php
				break;
		}
	}
	?>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-content">
                    <div class="row m-b-md">
                        <div class="col-md-3 col-sm-4">
                            <?php if ($tpl['has_create']) : ?>
                                <a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminVouchers&amp;action=pjActionCreate" class="btn btn-primary"><i class="fa fa-plus"></i> <?php __('btnAddVoucher'); ?></a>
                            <?php endif; ?>
                        </div><!-- /.col-md-3 -->

                        <div class="col-md-5 col-sm-8">
                            <form action="" method="get" class="form-horizontal frm-filter">
                                <div class="input-group">
                                    <input type="text" name="q" placeholder="<?php __('btnSearch', false, true); ?>" class="form-control">
                                    <div class="input-group-btn">
                                        <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div><!-- /.col-md-5 -->

                        <div class="col-md-4 col-sm-12 text-right">
                            <select name="valid_filter" class="form-control vch-valid-filter">
                                <option value=""><?php __('lblAll'); ?></option>
                                <?php foreach (array('always', 'fixed', 'period', 'recurring') as $k) : ?>
                                    <option value="<?php echo $k; ?>"><?php echo pjSanitize::html(@$vv[$k]); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div id="grid"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
var pjGrid = pjGrid || {};
var myLabel = myLabel || {};
myLabel.code = "<?php __('voucher_code', false, true); ?>";
myLabel.services = "<?php __('voucher_services', false, true); ?>";
myLabel.discount = "<?php __('voucher_discount', false, true); ?>";
myLabel.valid = "<?php __('voucher_valid', false, true); ?>";
myLabel.status = "<?php __('voucher_status', false, true); ?>";
myLabel.active = "<?php __('voucher_status_active', false, true); ?>";
myLabel.inactive = "<?php __('voucher_status_inactive', false, true); ?>";
myLabel.delete_selected = "<?php __('delete_selected', false, true); ?>";
myLabel.delete_confirmation = "<?php __('delete_confirmation', false, true); ?>";
myLabel.has_update = <?php echo (int) $tpl['has_update']; ?>;
myLabel.has_delete = <?php echo (int) $tpl['has_delete']; ?>;
myLabel.has_delete_bulk = <?php echo (int) $tpl['has_delete_bulk']; ?>;
</script>
