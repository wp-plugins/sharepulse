(function ($) {
    $(document).ready(function () {
		$.post(sp_Ajax.ajaxurl,	{
			// wp ajax action
			action : 'sharepulse-build-stats',
			// send the nonce along with the request
			spNonce : sp_Ajax.spNonce,
            id: sp_Ajax.id
		}, function (response) {
			console.log(response);
		});
	});
}(jQuery));