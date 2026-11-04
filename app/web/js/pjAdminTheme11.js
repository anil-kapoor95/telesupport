/**
 * Theme 11 design panel (Admin > Options > Preview).
 * Live preview: one <style> block with the --t11-* variables is written into the preview iframe (same origin),
 * so nothing is stored until "Save" is pressed.
 */
var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		"use strict";
		var $panel = $("#t11Panel");
		if (!$panel.length) {
			return;
		}
		var cfg = {};
		try {
			cfg = JSON.parse($panel.attr("data-config"));
		} catch (e) {
			return;
		}
		var $iframe = $("#iframeEditor"),
			$toggle = $("#t11Toggle"),
			$msgs = $("#t11Msgs"),
			$status = $panel.find(".t11-status"),
			STYLE_ID = "t11-live-style",
			FONT_ID = "t11-live-fonts",
			state = $.extend({}, cfg.values),
			dirty = false,
			saving = false;

		function isTheme11() {
			return !$toggle.hasClass("hidden");
		}

		function varName(token) {
			return "--t11-" + token.replace(/_/g, "-");
		}

		function buildCss() {
			var parts = [], i, token, radius = cfg.radii[state.radius] || cfg.radii.soft;
			for (i = 0; i < cfg.colors.length; i++) {
				token = cfg.colors[i];
				parts.push(varName(token) + ": " + state[token] + " !important");
			}
			parts.push("--t11-font-head: " + cfg.fonts[state.font_head].stack + " !important");
			parts.push("--t11-font-body: " + cfg.fonts[state.font_body].stack + " !important");
			parts.push("--t11-fs: " + state.font_size + "px !important");
			parts.push("--t11-r: " + radius.r + " !important");
			parts.push("--t11-rb: " + radius.rb + " !important");
			parts.push("--t11-ri: " + radius.ri + " !important");
			parts.push("--t11-shadow: " + (cfg.shadows[state.shadow] || "none") + " !important");
			parts.push("--t11-steps: " + (state.steps === "1" ? "flex" : "none") + " !important");
			return '[id^="pjWrapperAppScheduler_"] { ' + parts.join("; ") + "; }";
		}

		function frameDoc() {
			try {
				return $iframe.length ? $iframe[0].contentWindow.document : null;
			} catch (e) {
				return null;
			}
		}

		function clearLive() {
			var doc = frameDoc(), el;
			if (!doc) {
				return;
			}
			if ((el = doc.getElementById(STYLE_ID))) {
				el.parentNode.removeChild(el);
			}
			if ((el = doc.getElementById(FONT_ID))) {
				el.parentNode.removeChild(el);
			}
		}

		function applyLive() {
			var doc = frameDoc(), head, style, link, families = [], i, f, key;
			if (!doc || !doc.head) {
				return;
			}
			head = doc.head;
			style = doc.getElementById(STYLE_ID);
			if (!style) {
				style = doc.createElement("style");
				style.id = STYLE_ID;
				head.appendChild(style);
			}
			style.textContent = buildCss();

			// web fonts that the preview does not have yet
			for (i = 0, key = ["font_head", "font_body"]; i < key.length; i++) {
				f = cfg.fonts[state[key[i]]];
				if (f && f.google && families.indexOf(f.google) === -1) {
					families.push(f.google);
				}
			}
			link = doc.getElementById(FONT_ID);
			if (families.length) {
				if (!link) {
					link = doc.createElement("link");
					link.id = FONT_ID;
					link.rel = "stylesheet";
					head.appendChild(link);
				}
				var href = "https://fonts.googleapis.com/css2?family=" + families.join("&family=") + "&display=swap";
				if (link.getAttribute("href") !== href) {
					link.setAttribute("href", href);
				}
			} else if (link) {
				link.parentNode.removeChild(link);
			}
		}

		function changed() {
			dirty = true;
			$status.removeClass("t11-ok t11-err").text("");
			applyLive();
		}

		function syncControls() {
			var i, token;
			for (i = 0; i < cfg.colors.length; i++) {
				token = cfg.colors[i];
				$panel.find('.t11-picker[data-token="' + token + '"]').val(state[token]);
				$panel.find('.t11-hex[data-token="' + token + '"]').val(state[token]).removeClass("t11-invalid");
			}
			$panel.find("select.t11-field").each(function () {
				$(this).val(state[$(this).data("token")]);
			});
			$panel.find(".t11-seg").each(function () {
				var v = state[$(this).data("token")];
				$(this).find("button").removeClass("active").filter('[data-value="' + v + '"]').addClass("active");
			});
			$panel.find(".t11-check").each(function () {
				this.checked = state[$(this).data("token")] === "1";
			});
			$panel.find(".t11-palette").removeClass("active");
			$.each(cfg.palettes, function (key, p) {
				var same = true;
				$.each(p.colors, function (t, c) {
					if (state[t] !== c) {
						same = false;
						return false;
					}
				});
				if (same) {
					$panel.find('.t11-palette[data-palette="' + key + '"]').addClass("active");
				}
			});
		}

		function reloadPreview() {
			if ($iframe.length) {
				$iframe.attr("src", $iframe.attr("src"));
			}
		}

		function post(data, done) {
			if (saving) {
				return;
			}
			saving = true;
			$panel.addClass("t11-busy");
			$.post($panel.attr("data-url").replace(/&amp;/g, "&"), data, null, "json").done(function (res) {
				if (res && res.status === "OK") {
					done(res);
				} else {
					$status.removeClass("t11-ok").addClass("t11-err").text($msgs.attr("data-error"));
				}
			}).fail(function () {
				$status.removeClass("t11-ok").addClass("t11-err").text($msgs.attr("data-error"));
			}).always(function () {
				saving = false;
				$panel.removeClass("t11-busy");
			});
		}

		/* ---- controls ---- */
		$panel.on("input change", ".t11-picker", function () {
			var token = $(this).data("token");
			state[token] = this.value.toLowerCase();
			$panel.find('.t11-hex[data-token="' + token + '"]').val(state[token]).removeClass("t11-invalid");
			$panel.find(".t11-palette").removeClass("active");
			changed();
		}).on("input", ".t11-hex", function () {
			var v = $.trim(this.value), token = $(this).data("token");
			if (v.charAt(0) !== "#") {
				v = "#" + v;
			}
			if (/^#[0-9a-fA-F]{6}$/.test(v)) {
				state[token] = v.toLowerCase();
				$(this).removeClass("t11-invalid");
				$panel.find('.t11-picker[data-token="' + token + '"]').val(state[token]);
				$panel.find(".t11-palette").removeClass("active");
				changed();
			} else {
				$(this).addClass("t11-invalid");
			}
		}).on("blur", ".t11-hex", function () {
			this.value = state[$(this).data("token")];
			$(this).removeClass("t11-invalid");
		}).on("change", "select.t11-field", function () {
			state[$(this).data("token")] = this.value;
			changed();
		}).on("click", ".t11-seg button", function () {
			var $seg = $(this).closest(".t11-seg");
			state[$seg.data("token")] = $(this).data("value");
			$seg.find("button").removeClass("active");
			$(this).addClass("active");
			changed();
		}).on("change", ".t11-check", function () {
			state[$(this).data("token")] = this.checked ? "1" : "0";
			changed();
		}).on("click", ".t11-palette", function () {
			var p = cfg.palettes[$(this).data("palette")];
			if (!p) {
				return;
			}
			$.each(p.colors, function (t, c) {
				state[t] = c;
			});
			syncControls();
			$(this).addClass("active");
			changed();
		});

		$(document).on("click", '[data-t11="toggle"]', function (e) {
			e.preventDefault();
			var open = !$panel.hasClass("t11-open");
			$panel.toggleClass("t11-open", open).attr("aria-hidden", open ? "false" : "true");
		}).on("click", '[data-t11="close"]', function (e) {
			e.preventDefault();
			$panel.removeClass("t11-open").attr("aria-hidden", "true");
		}).on("click", '[data-t11="save"]', function (e) {
			e.preventDefault();
			post($.extend({}, state), function (res) {
				$.extend(state, res.values);
				dirty = false;
				syncControls();
				clearLive();
				reloadPreview();
				$status.removeClass("t11-err").addClass("t11-ok").text($msgs.attr("data-saved"));
			});
		}).on("click", '[data-t11="reset"]', function (e) {
			e.preventDefault();
			if (!window.confirm($msgs.attr("data-confirm"))) {
				return;
			}
			post({mode: "reset"}, function (res) {
				$.extend(state, res.values);
				dirty = false;
				syncControls();
				clearLive();
				reloadPreview();
				$status.removeClass("t11-err").addClass("t11-ok").text($msgs.attr("data-reset"));
			});
		}).on("keydown", function (e) {
			if (e.which === 27 && $panel.hasClass("t11-open")) {
				$panel.removeClass("t11-open").attr("aria-hidden", "true");
			}
		});

		/* ---- theme switcher (pjAdminOptions.js loads the new preview, we only show / hide the panel) ---- */
		$(document).on("click", "[data-theme]", function () {
			var isT11 = $(this).data("index") === "theme11";
			$toggle.toggleClass("hidden", !isT11);
			if (!isT11) {
				$panel.removeClass("t11-open").attr("aria-hidden", "true");
			}
			// the preview is reloaded: unsaved changes are re-applied once it has loaded (see below)
		});

		/* ---- after the preview reloads, keep showing the unsaved design ---- */
		$iframe.on("load", function () {
			if (dirty && isTheme11()) {
				applyLive();
			}
		});

		syncControls();
	});
})(jQuery_1_8_2);
