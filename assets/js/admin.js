jQuery(document).ready(function ($) {
	$(document).on('click', '#payex_capture', function (e) {
		e.preventDefault();

		var nonce = $(this).data('nonce');
		var order_id = $(this).data('order-id');
		var self = $(this);
		$.ajax({
			url       : Payex_Admin.ajax_url,
			type      : 'POST',
			data      : {
				action        : 'payex_capture',
				nonce         : nonce,
				order_id      : order_id
			},
			beforeSend: function () {
				self.data('text', self.html());
				self.html(Payex_Admin.text_wait);
				self.prop('disabled', true);
			},
			success   : function (response) {
				self.html(self.data('text'));
				self.prop('disabled', false);
				if (!response.success) {
					alert(response.data);
					return false;
				}

				window.location.href = location.href;
			}
		});
	});

	$(document).on('click', '#payex_cancel', function (e) {
		e.preventDefault();

		var nonce = $(this).data('nonce');
		var order_id = $(this).data('order-id');
		var self = $(this);
		$.ajax({
			url       : Payex_Admin.ajax_url,
			type      : 'POST',
			data      : {
				action        : 'payex_cancel',
				nonce         : nonce,
				order_id      : order_id
			},
			beforeSend: function () {
				self.data('text', self.html());
				self.html(Payex_Admin.text_wait);
				self.prop('disabled', true);
			},
			success   : function (response) {
				self.html(self.data('text'));
				self.prop('disabled', false);
				if (!response.success) {
					alert(response.data);
					return false;
				}

				window.location.href = location.href;
			}
		});
	});
});
