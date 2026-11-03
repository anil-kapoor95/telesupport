<?php 
$titles = __('error_titles', true);
$bodies = __('error_bodies', true);
?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-sm-12">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <h2><?php echo @$titles['AO23']; ?></h2>
            </div>
        </div>

        <p class="m-b-none"><i class="fa fa-info-circle"></i><?php echo @$bodies['AO23']; ?></p>
    </div>
</div>

<div class="row wrapper wrapper-content animated fadeInRight">
    <?php
	$error_code = $controller->_get->toString('err');
	if (!empty($error_code))
    {
    	switch (true)
    	{
    		case in_array($error_code, array('AO03')):
    			?>
    			<div class="alert alert-success">
    				<i class="fa fa-check m-r-xs"></i>
    				<strong><?php echo @$titles[$error_code]; ?></strong>
    				<?php echo @$bodies[$error_code]?>
    			</div>
    			<?php
    			break;
    		case in_array($error_code, array('')):
    			?>
    			<div class="alert alert-danger">
    				<i class="fa fa-exclamation-triangle m-r-xs"></i>
    				<strong><?php echo @$titles[$error_code]; ?></strong>
    				<?php echo @$bodies[$error_code]?>
    			</div>
    			<?php
    			break;
    	}
    }
    ?>
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-content">
                <?php
    				if (isset($tpl['arr']) && is_array($tpl['arr']) && !empty($tpl['arr']))
    				{
                    ?>
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminOptions&amp;action=pjActionUpdate" method="post" class="form-horizontal" id="frmUpdateOptions">
                    	<input type="hidden" name="csrf_token" value="<?php echo pjAppController::getCsrfToken(); ?>" />
                        <input type="hidden" name="options_update" value="1" />
                        <input type="hidden" name="tab" value="1" />
                        <input type="hidden" name="next_action" value="pjActionBooking" />
                        <?php
                        foreach ($tpl['arr'] as $option)
                        {
                        	if ((int) $option['is_visible'] === 0 || $option['key'] == 'o_layout' || $option['key'] == 'o_theme') continue;
                            if(in_array($option['key'], array('o_allow_bank', 'o_allow_cash', 'o_allow_creditcard', 'o_bank_account'))) continue; // These will be managed from Payments menu
                            ?>
                            <div class="form-group">
                                
                                <?php 
                                 if (preg_match('/^o_thankyou_page.*/', $option['key'])) { 
                                     $str = $option['key']; 
                                     if (strpos($str, 'o_thankyou_page_') === 0) {
                                         $prefix = "o_thankyou_page_";
                                         $locale_value = substr($str, strlen($prefix));
                                     }  else {
                                         $locale_value = null;
                                     }
                                     // Assuming $str is already defined and you have $locale_value from previous logic
                                     
                                     foreach ($tpl['locale_arr'] as $locale) {
                                         // Check if $str is 'o_thankyou_page', display the label once
                                         if ($str == 'o_thankyou_page') {
                                             // Display only once
                                             ?>
                                             <label class="col-sm-3 control-label"><?php __('opt_'. $option['key']); ?></label>
                                             <?php
                                             // Skip further processing as the label is already printed for 'o_thankyou_page'
                                             break;
                                         }
                                     
                                         // Now process for matching locales, if the locale ID matches, display the label
                                         if ($locale['id'] == $locale_value) {
                                             ?>
                                             <label class="col-sm-3 control-label">
                                                 <?php __('opt_'. $option['key']); ?> (<?php echo $locale['title']; ?>)
                                             </label>
                                             <?php
                                         }
                                     }
                                     
                                }elseif(preg_match('/^o_cancel_payment_url.*/', $option['key'])){
                                    $str = $option['key']; 
                                    if (strpos($str, 'o_cancel_payment_url_') === 0) {
                                        $prefix = "o_cancel_payment_url_";
                                        $locale_value = substr($str, strlen($prefix));
                                    }  else {
                                        $locale_value = null;
                                    }
                                    // Assuming $str is already defined and you have $locale_value from previous logic
                                    
                                    foreach ($tpl['locale_arr'] as $locale) {
                                        // Check if $str is 'o_cancel_payment_url', display the label once
                                        if ($str == 'o_cancel_payment_url') {
                                            // Display only once
                                            ?>
                                            <label class="col-sm-3 control-label"><?php __('opt_o_cancel_payment_url' ); ?></label>
                                            <?php
                                            // Skip further processing as the label is already printed for 'o_cancel_payment_url'
                                            break;
                                        }
                                    
                                        // Now process for matching locales, if the locale ID matches, display the label
                                        if ($locale['id'] == $locale_value) {
                                            ?>
                                            <label class="col-sm-3 control-label">
                                                <?php __('opt_o_cancel_payment_url' ); ?> (<?php echo $locale['title']; ?>)
                                            </label>
                                            <?php
                                        }
                                    }

                                }else{
                                ?>
                                    <label class="col-sm-3 control-label"><?php __('opt_'. $option['key']);?> </label>
                                    <?php
                                } ?>
                                       
                                <div class="col-lg-5 col-sm-7">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <?php
                                            switch ($option['type'])
                                            {
                                                case 'string':
                                                if (preg_match('/^o_thankyou_page.*/', $option['key'])) {
                                                    $str = $option['key']; 
                                                    if (strpos($str, 'o_thankyou_page_') === 0) {
                                                        $prefix = "o_thankyou_page_";
                                                        $locale_value = substr($str, strlen($prefix));
                                                    }   else {
                                                        $locale_value = null;
                                                    }
                                                    ?>
                                                    
                                                    <div class="locale-selector">
                                                        <div class="locale-wrapper">
                                                            <!-- Editable Input Field -->
                                                            <input type="text" name="value-<?php echo $option['type']; ?>-<?php echo $option['key']; ?>" 
                                                                class="form-control locale-input" 
                                                                value="<?php echo pjSanitize::html($option['value']); ?>">
                                                    
                                                            <!-- Dropdown Button -->
                                                            <div class="dropdown">
                                                                    <!-- Default flag image will be shown here based on the selected locale -->
                                                                    <?php
                                                                    // Get the flag for the selected locale value
                                                                    $selected_src = null;
                                                                    foreach ($tpl['locale_arr'] as $locale  ) {
                                                                        if ($locale['id'] == $locale_value  ) {
                                                                            // Check for the flag file
                                                                            if (!empty($locale['flag']) && is_file(PJ_INSTALL_PATH . $locale['flag'])) {
                                                                                $selected_src = PJ_INSTALL_URL . $locale['flag'];
                                                                            } elseif (!empty($locale['file']) && is_file(PJ_FRAMEWORK_LIBS_PATH . 'pj/img/flags/' . $locale['file'])) {
                                                                                $selected_src = PJ_INSTALL_URL . PJ_FRAMEWORK_LIBS_PATH . 'pj/img/flags/' . $locale['file'];
                                                                            }
                                                                            break; // Exit loop once matching locale is found
                                                                        }
                                                                    }
                                                                    ?>
                                                                    <img class="selected-flag" src="<?php echo $selected_src; ?>" alt="" style="width: 20px; height: auto; margin-right: 10px;">
                                                                
                                                            </div>
                                                        </div>
                                                    </div>
                                                    

                                                <style>
                                                    .locale-wrapper {
                                                        display: flex;
                                                        align-items: center;
                                                        gap: 5px;
                                                        position: relative;
                                                    }
                                                    /* Dropdown button */
                                                    .dropdown-toggle {
                                                        display: flex;
                                                        align-items: center;
                                                        color: white;
                                                        border: none;
                                                        padding: 10px;
                                                        cursor: pointer;
                                                        border-radius: 5px;
                                                        font-size: 14px;
                                                    }
                                                </style>

                                                    <?php
                                                     }elseif(preg_match('/^o_cancel_payment_url.*/', $option['key'])){

                                                    $str = $option['key']; 
                                                    if (strpos($str, 'o_cancel_payment_url_') === 0) {
                                                        $prefix = "o_cancel_payment_url_";
                                                        $locale_value = substr($str, strlen($prefix));
                                                    }   else {
                                                        $locale_value = null;
                                                    }
                                                    ?>
                                                    
                                                    <div class="locale-selector">
                                                        <div class="locale-wrapper">
                                                            <!-- Editable Input Field -->
                                                            <input type="text" name="value-<?php echo $option['type']; ?>-<?php echo $option['key']; ?>" 
                                                                class="form-control locale-input" 
                                                                value="<?php echo pjSanitize::html($option['value']); ?>">
                                                    
                                                            <!-- Dropdown Button -->
                                                            <div class="dropdown">
                                                                    <!-- Default flag image will be shown here based on the selected locale -->
                                                                    <?php
                                                                    // Get the flag for the selected locale value
                                                                    $selected_src = null;
                                                                    foreach ($tpl['locale_arr'] as $locale  ) {
                                                                        if ($locale['id'] == $locale_value  ) {
                                                                            // Check for the flag file
                                                                            if (!empty($locale['flag']) && is_file(PJ_INSTALL_PATH . $locale['flag'])) {
                                                                                $selected_src = PJ_INSTALL_URL . $locale['flag'];
                                                                            } elseif (!empty($locale['file']) && is_file(PJ_FRAMEWORK_LIBS_PATH . 'pj/img/flags/' . $locale['file'])) {
                                                                                $selected_src = PJ_INSTALL_URL . PJ_FRAMEWORK_LIBS_PATH . 'pj/img/flags/' . $locale['file'];
                                                                            }
                                                                            break; // Exit loop once matching locale is found
                                                                        }
                                                                    }
                                                                    ?>
                                                                    <img class="selected-flag" src="<?php echo $selected_src; ?>" alt="" style="width: 20px; height: auto; margin-right: 10px;">
                                                                
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                <?php
                                                     }else{
                                                        ?>
                                                        <input type="text" name="value-<?php echo $option['type']; ?>-<?php echo $option['key']; ?>" class="form-control" value="<?php echo pjSanitize::html($option['value']); ?>">
                                                        <?php
                                                     }
                                                    break;
                                                case 'text':
                                                    ?><textarea name="value-<?php echo $option['type']; ?>-<?php echo $option['key']; ?>" class="form-control"><?php echo pjSanitize::html($option['value']); ?></textarea><?php
                                                    break;
                                                case 'int':
                                                    ?>
                                                    <input type="text" name="value-<?php echo $option['type']; ?>-<?php echo $option['key']; ?>" class="form-control field-int" value="<?php echo pjSanitize::html($option['value']); ?>">
                                                    <?php
                                                    if($option['key'] == 'o_pending_time')
                                                    {
                                                        ?>
                                                        <small><?php __('opt_o_pending_time_text');?></small>
                                                        <?php
                                                    }
                                                    break;
                                                case 'float':
                                                    if(in_array($option['key'], array('o_booking_price'))) {
                                                        ?>
                                                        <div class="input-group">
                                                            <input type="text" name="value-<?php echo $option['type']; ?>-<?php echo $option['key']; ?>" class="form-control decimal number text-right" value="<?php echo number_format($option['value'], 2) ?>" data-msg-number="<?php __('pj_please_enter_valid_number', false, true);?>">

                                                            <span class="input-group-addon"><?php echo pjCurrency::getCurrencySign($tpl['option_arr']['o_currency'], false) ?></span>
                                                        </div>
                                                        <?php
                                                    } else if(in_array($option['key'], array('o_deposit'))) {
                                                    	?>
                                                    	<div class="row">
                                                    		<div class="col-md-6">
                                                    			<input type="text" name="value-<?php echo $option['type']; ?>-<?php echo $option['key']; ?>" class="form-control field-float number" value="<?php echo (int) $option['value']; ?>" />
                                                    		</div>
                                                    		<div class="col-md-6">
																<select name="value-enum-o_deposit_type" class="form-control">
																	<?php
																	$default = explode("::", $tpl['o_arr']['o_deposit_type']['value']);
																	$enum = explode("|", $default[0]);
		                                                    											
																	$enumLabels = array();
																	if (!empty($tpl['o_arr']['o_deposit_type']['label']) && strpos($tpl['o_arr']['o_deposit_type']['label'], "|") !== false)
																	{
																		$enumLabels = explode("|", $tpl['o_arr']['o_deposit_type']['label']);
																	}
																	$enum_arr = __('enum_arr', true);
																	foreach ($enum as $k => $el)
																	{
																		?><option value="<?php echo $default[0].'::'.$el; ?>"<?php echo $default[1] == $el ? ' selected="selected"' : NULL; ?>><?php echo isset($enum_arr[$el]) ? $enum_arr[$el] : (array_key_exists($k, $enumLabels) ? stripslashes($enumLabels[$k]) : stripslashes($el)); ?></option><?php
																	}
																	?>
																</select>
															</div>
														</div>
														<?php
													} else if(in_array($option['key'], array('o_tax'))) {
														?>
														<div class="input-group">
															<input type="text" name="value-<?php echo $option['type']; ?>-<?php echo $option['key']; ?>" class="form-control number text-right" value="<?php echo number_format($option['value'], 2) ?>" />
				
															<span class="input-group-addon">%</span>
														</div>
														<?php 
													} else {
                                                        ?><input type="text" name="value-<?php echo $option['type']; ?>-<?php echo $option['key']; ?>" class="form-control field-float number" value="<?php echo number_format($option['value'], 2) ?>"><?php
                                                    }
                                                    break;
                                                case 'enum':
													include dirname(__FILE__) . '/elements/enum.php';
                                                    break;
												case 'bool':
													include dirname(__FILE__) . '/elements/switch.php';
													break;
                                            }
                                            ?>
                                        </div>

                                        <?php if (in_array($option['key'], array('o_booking_status', 'o_payment_status', 'o_thank_you_page', 'o_payment_disable', 'o_min_hour'))): ?>
                                            <div class="col-sm-12">
                                                <span class="form-control-static"><?php __("opt_{$option['key']}_text") ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (in_array($option['key'], array('o_booking_length', 'o_booking_earlier'))): ?>
                                            <p class="m-t-xs"><?php __("opt_{$option['key']}_text") ?></p>
                                        <?php endif; ?>
                                        <?php if (in_array($option['key'], array('o_pending_time'))): ?>
                                            <p class="m-t-xs"><?php __("lblMinutes") ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                        <div class="hr-line-dashed"></div>

                        <div class="clearfix">
                            <button class="ladda-button btn btn-primary btn-lg pull-left btn-phpjabbers-loader" data-style="zoom-in">
                                <span class="ladda-label"><?php __('plugin_base_btn_save'); ?></span>
                                <?php include $controller->getConstant('pjBase', 'PLUGIN_VIEWS_PATH') . 'pjLayouts/elements/button-animation.php'; ?>
                            </button>
                        </div>
                    </form>
                    <?php
                }
                ?>
            </div>
        </div>
    </div><!-- /.col-lg-12 -->
</div>