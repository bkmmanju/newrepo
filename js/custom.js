$( document ).ready(function() {
	// Tooltip

	$('.clipboard').tooltip({
		trigger: 'click',
		placement: 'bottom'
	});

	$('.clipboard_video').tooltip({
		trigger: 'click',
		placement: 'bottom'
	});

	function setTooltip(btn, message) {
		$(btn).tooltip('hide')
		.attr('data-original-title', message)
		.tooltip('show');
	}

	function hideTooltip(btn) {
		setTimeout(function() {
			$(btn).tooltip('hide');
		}, 1000);
	}

	var clipboard = new ClipboardJS('.clipboard');

	clipboard.on('success', function(e) {
		setTooltip(e.trigger, 'Copied!');
		hideTooltip(e.trigger);
	});

	clipboard.on('error', function(e) {
		setTooltip(e.trigger, 'Failed!');
		hideTooltip(e.trigger);
	});

	var clipboard1 = new ClipboardJS('.clipboard_video');

	clipboard1.on('success', function(e) {
		setTooltip(e.trigger, 'Copied!');
		hideTooltip(e.trigger);
	});

	clipboard1.on('error', function(e) {
		setTooltip(e.trigger, 'Failed!');
		hideTooltip(e.trigger);
	});
});