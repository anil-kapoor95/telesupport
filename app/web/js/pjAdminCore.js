/* CSRF: attach token to admin AJAX via header + POST form submits (no URL slug). */
function pjCsrfToken(){var m=document.querySelector('meta[name="csrf-token"]');return m?m.getAttribute("content"):"";}
(function(){
	if (typeof jQuery === 'undefined') { return; }
	jQuery.ajaxPrefilter(function (options) {
		if (options.crossDomain) { return; }
		var t = pjCsrfToken(); if (!t) { return; }
		options.headers = options.headers || {};
		options.headers['X-CSRF-Token'] = t;
	});
	function pjInject(form){
		var m = (form.getAttribute('method') || 'get').toLowerCase();
		if (m !== 'post') { return; }
		var t = pjCsrfToken();
		if (t && !form.querySelector('input[name="csrf_token"]')) {
			var i = document.createElement('input');
			i.type = 'hidden'; i.name = 'csrf_token'; i.value = t;
			form.appendChild(i);
		}
	}
	jQuery(document).on('submit', 'form', function () { pjInject(this); });
	if (window.HTMLFormElement && HTMLFormElement.prototype && !HTMLFormElement.prototype.__pjCsrfWrapped) {
		HTMLFormElement.prototype.__pjCsrfWrapped = true;
		var _pjNativeSubmit = HTMLFormElement.prototype.submit;
		HTMLFormElement.prototype.submit = function () {
			try { pjInject(this); } catch (e) {}
			return _pjNativeSubmit.apply(this, arguments);
		};
	}
})();

var asApp = asApp || {};
var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		$(".pj-checkbox").hover(
				function () {
					$(this).addClass("pj-checkbox-hover");
				}, 
				function () {
					$(this).removeClass("pj-checkbox-hover");
				}
			);
		$("#content").on("click", ".notice-close", function (e) {
			if (e && e.preventDefault) {
				e.preventDefault();
			}
			$(this).closest(".notice-box").fadeOut();
			return false;
		});
	});
})(jQuery);