
if($('#hide-voted').length > 0) {
	var hidevoted = localStorage.getItem('hide-voted');
	let toggle = $('#hide-voted');

	toggle.on('change', function(e) {
		if(toggle.is(':checked')) {
			localStorage.setItem('hide-voted', true);
			$('#votings-active [data-voted]').fadeOut('fast');
		} else {
			localStorage.removeItem('hide-voted');
			$('#votings-active [data-voted]').fadeIn('fast');
		}
	});

	if(hidevoted) {
		toggle.prop('checked', true);
	} else {
		toggle.prop('checked', false);
	}
	toggle.trigger('change');
}
