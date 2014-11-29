jQuery(function($) {

	function getData(oembedUrl, url, callback) {
		$.ajax({
			dataType: 'json',
			url: oembedUrl + '&url=' + url,
			success: function(data) {
				if (data['mediaembed:provider_code']) {
					callback(null, data);
				}
				else {
					callback('Failed to get data');
				}
			},
			error: function() {
				callback('Failed to get data');
			}
		});
	}

	function updateStatus(oembedUrl, status, val) {
		status.addClass('loading').removeClass('noservice').removeClass('error').removeClass('success').text('loading');

		if (!val || val.match(/^\s*$/)) {
			status.removeClass('loading').addClass('noservice').removeClass('error').removeClass('success');
			status.text('');
			status.closest('.mediaembed').find('[js-data]').val('');
			return;
		}

		getData(oembedUrl, val, function(err, data) {
			if (err) {
				status.removeClass('loading').removeClass('noservice').addClass('error').removeClass('success');
				status.text('Error getting data. Are you sure this is a valid URL?');

				status.closest('.mediaembed').find('[js-data]').val('');
			}
			else {
				var html = data.html;

				status.removeClass('loading').removeClass('noservice').removeClass('error').addClass('success');
				status.empty();
				status.append($(html));

				status.closest('.mediaembed').find('[js-data]').val(JSON.stringify(data));
			}
		});
	}

	function initializeElement(mediaembed) {
		if (mediaembed.data('mediaembed-isinitialized')) {
			return;
		}

		mediaembed.data('mediaembed-isinitialized', true);

		var oembedUrl = mediaembed.attr('data-oembed-url');

		var input = mediaembed.find('input');
		var status = mediaembed.find('.status');
		if (!status.length) {
			status = $('<div>').addClass('status').appendTo(mediaembed);
		}
		input.on('change', function() {
			var val = input.val();
			updateStatus(oembedUrl, status, val);
		});
	}

	$('.mediaembed').each(function() {
		initializeElement($(this));
	});

	if (typeof(window.Grid) !== 'undefined') {
		for (var i = 0; i < MediaEmbedFieldtypes.length; i++) {
			var fieldtype = MediaEmbedFieldtypes[i];
			Grid.bind(fieldtype, 'display', onDisplay);
			Grid.bind(fieldtype, 'remove', onRemove);
		}

		function onDisplay(cell) {
			// When Grid addes a new mediaembed, initialize it.
			var mediaembed = $(cell).find('.mediaembed');
			initializeElement(mediaembed);
		}

		function onRemove(cell) {
			// Because mediaembed inputs have a type of 'url', if a person
			// removes a mediaembed from Grid that does not have a valid value,
			// the browser will refuse to submit it (because it isn't a valid
			// URL), but that won't be visisble to the user. So, when a user
			// removes a mediaembed, just clear the value, so the browser
			// doesn't do that.
			var mediaembed = $(cell).find('.mediaembed');
			mediaembed.find('input').val('');
		}
	}
});
