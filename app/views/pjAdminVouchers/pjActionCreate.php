<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-sm-12">
        <div class="row">
            <div class="col-sm-10">
                <h2><?php __('infoAddVoucherTitle'); ?></h2>
            </div>
        </div><!-- /.row -->

        <p class="m-b-none"><i class="fa fa-info-circle"></i> <?php __('infoAddVoucherDesc'); ?></p>
    </div><!-- /.col-md-12 -->
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-content">
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminVouchers&amp;action=pjActionCreate" method="post" id="frmCreateVoucher" autocomplete="off">
                        <input type="hidden" name="csrf_token" value="<?php echo pjAppController::getCsrfToken(); ?>">
                        <input type="hidden" name="voucher_create" value="1" />
                        <?php include dirname(__FILE__) . '/elements/form.php'; ?>
                        <div class="hr-line-dashed"></div>
                        <div class="clearfix">
                            <button type="submit" class="btn btn-primary btn-lg pull-left"><i class="fa fa-check"></i> <?php __('btnSave'); ?></button>
                            <a class="btn btn-default btn-lg pull-right" href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminVouchers&amp;action=pjActionIndex"><?php __('btnCancel'); ?></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
