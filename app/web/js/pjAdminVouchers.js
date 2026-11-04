var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		"use strict";
		var $frmCreate = $("#frmCreateVoucher"),
			$frmUpdate = $("#frmUpdateVoucher"),
			$form = $frmCreate.length ? $frmCreate : $frmUpdate,
			cfg = $("#voucherCfg"),
			ampm = parseInt(cfg.data("ampm"), 10) || 0,
			datepicker = ($.fn.datepicker !== undefined),
			datagrid = ($.fn.datagrid !== undefined),
			validate = ($.fn.validate !== undefined);

		if (datepicker && cfg.length) {
			$.fn.datepicker.dates['en'] = {
				days: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
				daysMin: String(cfg.data("days")).split("_"),
				daysShort: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
				months: String(cfg.data("months")).split("_"),
				monthsShort: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"]
			};
		}

		if ($(".select-item").length && $.fn.select2) {
			$(".select-item").select2({width: '100%', allowClear: true, placeholder: $(".select-item").data("placeholder")});
		}

		/* ---- hh:mm (+ am/pm) of a picker -> minutes since midnight. 12 AM = 0, 12 PM = 720 ---- */
		function minutes(prefix, which) {
			var h = parseInt($("#" + prefix + "_hour_" + which).val(), 10),
				m = parseInt($("#" + prefix + "_minute_" + which).val(), 10);
			if (ampm > 0) {
				h = h % 12;
				if (String($("#" + prefix + "_ampm_" + which).val()).toLowerCase() === "pm") {
					h += 12;
				}
			}
			return h * 60 + m;
		}

		function currentMode() {
			return $("#valid").val();
		}

		function syncVisibility() {
			var mode = currentMode();
			$(".valid-box").hide();
			if (mode !== "always") {
				$("#valid_" + mode).show();
				$("#valid_hint").show();
			}
			$(".box-valid-on").toggle(mode !== "always");
			var isAmount = $("#type").val() === "amount";
			$(".voucher-unit").text(isAmount ? String(cfg.data("currency")) : "%");
			$(".box-apply").toggle(isAmount);
		}
		$(document).on("change", "#valid, #type", syncVisibility);
		syncVisibility();

		if (validate && $form.length) {
			$.validator.addMethod("voucherTimeOrder", function () {
				var mode = currentMode(), p = mode === "fixed" ? "f" : (mode === "recurring" ? "r" : null);
				if (!p) {
					return true;
				}
				return minutes(p, "to") > minutes(p, "from");
			}, String(cfg.data("time-order")));

			$.validator.addMethod("voucherPeriodOrder", function () {
				if (currentMode() !== "period") {
					return true;
				}
				return true; // the real check (dates in the configured format) is done by the server
			}, String(cfg.data("period-order")));

			if ($.validator.methods.pattern === undefined) {
				$.validator.addMethod("pattern", function (value, element, param) {
					return this.optional(element) || param.test(value);
				});
			}
			$form.validate({
				rules: {
					"code": {
						required: true,
						remote: "index.php?controller=pjAdminVouchers&action=pjActionCheckCode" + ($frmUpdate.length ? "&id=" + $frmUpdate.find("input[name='id']").val() : ""),
						pattern: /^[A-Za-z0-9][A-Za-z0-9_\-\.]{1,49}$/
					},
					"discount": {required: true, number: true, min: 0.01},
					"max_uses": {digits: true},
					"max_uses_per_email": {digits: true},
					"f_date": {required: function () { return currentMode() === "fixed"; }},
					"p_date_from": {required: function () { return currentMode() === "period"; }},
					"p_date_to": {required: function () { return currentMode() === "period"; }},
					"f_hour_to": {voucherTimeOrder: true},
					"r_hour_to": {voucherTimeOrder: true}
				},
				messages: {
					"code": {remote: String(cfg.data("exists")), pattern: String(cfg.data("code-format"))}
				},
				errorPlacement: function (error, element) {
					error.insertAfter(element.closest(".input-group").length ? element.closest(".input-group") : element);
				},
				ignore: ":hidden:not(select, input)",
				onkeyup: false
			});
			$("#type").on("change", function () {
				var max = $(this).val() === "percent" ? 100 : 99999999;
				$("#discount").rules("add", {max: max});
			}).trigger("change");
		}

		/* ---- list ---- */
		if ($("#grid").length && datagrid) {
			var buttons = [], actions = [], editable = false, select = false;
			if (myLabel.has_update) {
				editable = true;
				buttons.push({type: "edit", url: "index.php?controller=pjAdminVouchers&action=pjActionUpdate&id={:id}"});
			}
			if (myLabel.has_delete) {
				buttons.push({type: "delete", url: "index.php?controller=pjAdminVouchers&action=pjActionDeleteVoucher&id={:id}"});
			}
			if (myLabel.has_delete_bulk) {
				actions.push({text: myLabel.delete_selected, url: "index.php?controller=pjAdminVouchers&action=pjActionDeleteVoucherBulk", render: true, confirmation: myLabel.delete_confirmation});
				select = {field: "id", name: "record[]"};
			}
			var $grid = $("#grid").datagrid({
				buttons: buttons,
				columns: [
					{text: myLabel.code, type: "text", sortable: true, editable: false},
					{text: myLabel.discount, type: "text", sortable: true, editable: false, renderer: function (str, obj) { return obj.discount_f; }},
					{text: myLabel.services, type: "text", sortable: false, editable: false},
					{text: myLabel.valid, type: "text", sortable: false, editable: false, renderer: function (str, obj) { return obj.valid_f; }},
					{text: myLabel.status, type: "toggle", sortable: true, editable: editable, positiveLabel: myLabel.active, positiveValue: "T", negativeLabel: myLabel.inactive, negativeValue: "F"}
				],
				dataUrl: "index.php?controller=pjAdminVouchers&action=pjActionGetVoucher",
				dataType: "json",
				fields: ['code', 'discount', 'services', 'valid', 'status'],
				paginator: {actions: actions, gotoPage: true, paginate: true, total: true, rowCount: true},
				saveUrl: "index.php?controller=pjAdminVouchers&action=pjActionSaveVoucher&id={:id}",
				select: select
			});

			$(document).on("change", ".vch-valid-filter", function (e) {
				var content = $grid.datagrid("option", "content"), cache = $grid.datagrid("option", "cache");
				$.extend(cache, {valid: $(this).val()});
				$grid.datagrid("option", "cache", cache);
				$grid.datagrid("load", "index.php?controller=pjAdminVouchers&action=pjActionGetVoucher", "id", "DESC", 1, content.rowCount);
			}).on("submit", ".frm-filter", function (e) {
				e.preventDefault();
				var content = $grid.datagrid("option", "content"), cache = $grid.datagrid("option", "cache");
				$.extend(cache, {q: $(this).find("input[name='q']").val()});
				$grid.datagrid("option", "cache", cache);
				$grid.datagrid("load", "index.php?controller=pjAdminVouchers&action=pjActionGetVoucher", "id", "DESC", content.page, content.rowCount);
			});
		}
	});
})(jQuery_1_8_2);
