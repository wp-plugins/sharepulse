(function ($) {
    'use strict';
	var count = 0,
		ids,
		template;
	
	$("#progressbar").progressbar();

	$('#rebuild').bind('build_done', function (event) {
		$.post(sp_Ajax.ajaxurl,	{
			// wp ajax action
			action : 'sharepulse-build-done',
			// send the nonce along with the request
			spNonce : sp_Ajax.spNonce
		}, function (response) {
			$("#progress").append("<li>Done!</li>");
			$("#statsTitle").html('Build Complete');
			$('#buildAlert').css({'color': 'black'}).html("Done! You&apos;re good to go!");
		});
	});
	
	$('#rebuild').bind('set_stat', function (event, id) {
		template = _.template("<li><%= data.title %> - Twitter: <%= data.twitter %>, Facebook: <%= data.facebook %>, LinkedIn: <%= data.linkedin %>, Total: <%= data.total %></li>");
		$.post(sp_Ajax.ajaxurl,	{
			// wp ajax action
			action : 'sharepulse-build-stats-admin',
			// vars
			id : id,
			// send the nonce along with the request
			spNonce : sp_Ajax.spNonce
		}, function (response) {
			if (response !== 'error') {
				count++;
				$("#statsTitle").html(response.title);
				$("#progressbar").progressbar({ value : (count / ids.length) * 100 });
				$("#progress").append(template({ data: response }));
				if (1 === (count / ids.length)) {
					$('#rebuild').trigger('build_done');
				}
			}
		});
	});
	
	$(document).ready(function () {
		$('#rebuild').click(function (e) {
			e.preventDefault();
			if ('undefined' !== typeof sp_Ajax.rebuild_list && !$(this).hasClass('disabled')) {
				ids = $.parseJSON(sp_Ajax.rebuild_list);
				$.each(ids, function (i, id) {
					$('#rebuild').trigger('set_stat', id);
				});
			}
			$(this).addClass('disabled');
		});
	});
}(jQuery));