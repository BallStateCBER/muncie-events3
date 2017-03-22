var eventForm = {
	previousLocations: []
};

function setup_eventseries_edit_form() {
	// Validation upon submit
	$('#EventSeriesEditForm').submit(function() {
		if ($('#event_series_delete_confirm').is(':checked')) {
			return confirm('Are you sure you want to delete this entire series?');
		}
		if ($('#EventSeriesTitle').val() == '') {
			alert('Please give this series a name.');
			return false;
		}
		return true;
	});
	// 'Edit' buttons
	$('#events_in_series tbody tr.display a.toggler').each(function() {
		var link = $(this);
		var event_id = link.data('eventId');
		link.click(function(event) {
			event.preventDefault();
			$('#eventinseries_display_'+event_id).hide();
			$('#eventinseries_edit_'+event_id).show();
			$('#eventinseries_edited_'+event_id).val(1);
		});
	});
	// 'Done' buttons
	$('#events_in_series tbody tr.edit a.toggler').each(function() {
		$(this).click(function(event) {
			var event_id = $(this).data('eventId');
			event.preventDefault();
			if ($('#Event'+event_id+'Title').val() == '') {
				alert('A title is required for this event.');
				return;
			}
			editEventSeries_updateRow(event_id);
			$('#eventinseries_display_'+event_id).show();
			$('#eventinseries_edit_'+event_id).hide();
		});
	});
	// Pre-fiddle with all 'display' rows
	if (event_ids.length > 0) {
		for (var i = 0; i < event_ids.length; i++) {
			var event_id = event_ids[i];
			editEventSeries_updateRow(event_id);
		}
	}

}

function editEventSeries_updateRow(event_id) {
	var months = ['', 'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
	var month = months[parseInt($('#Event'+event_id+'DateMonth').val())];
	var day = parseInt($('#Event'+event_id+'DateDay').val(), 10);
	var year = $('#Event'+event_id+'DateYear').val();
	var date = month + ' ' + day + ', ' + year;
	$('#eventinseries_display_'+event_id+'_date').html(date);
	var title = $('#Event'+event_id+'Title').val();
	$('#eventinseries_display_'+event_id+'_title').html(title);
	var time = $('#Event'+event_id+'TimeStartHour').val()+':'+$('#Event'+event_id+'TimeStartMin').val()+$('#Event'+event_id+'TimeStartMeridian').val();
	$('#eventinseries_display_'+event_id+'_time').html(time);
	if ($('#eventinseries_delete_' + event_id).is(':checked')) {
		$('#eventinseries_display_' + event_id).addClass('deleted');
	} else {
		$('#eventinseries_display_' + event_id).removeClass('deleted');
	}
}

function setupEventForm() {
	// Since TinyMCE doesn't work with HTML 5's required attribute
	$('#EventDescription').removeAttr('required');

	// This is only applicable if a new event takes place on multiple dates
	var series_title_input = $('#EventSeriesTitle');
	if (! series_title_input.is(':visible')) {
		series_title_input.removeAttr('required');
	}
	$('#add_end_time').click(function(event) {
		event.preventDefault();
		$('#eventform_hasendtime').show();
		$('#eventform_noendtime').hide();
		$('#eventform_hasendtime_boolinput').val('1');
		$('#EventTimeEndHour').focus();
	});
	$('#remove_end_time').click(function(event) {
		event.preventDefault();
		$('#eventform_noendtime').show();
		$('#eventform_hasendtime').hide();
		$('#eventform_hasendtime_boolinput').val('0');
	});
	$('#eventform_noaddress').click(function(event) {
		event.preventDefault();
		$('#eventform_noaddress').hide();
		$('#eventform_address').show();
		$('#EventAddress').focus();
	});
	$('#event_add_cost').click(function(event) {
		event.preventDefault();
		$('#eventform_nocost').hide();
		$('#eventform_hascost').show();
		$('#EventCost').focus();
	});
	$('#event_remove_cost').click(function(event) {
		event.preventDefault();
		$('#eventform_nocost').show();
		$('#eventform_hascost').hide();
		$('#EventCost').val('');
	});
	$('#event_add_age_restriction').click(function(event) {
		event.preventDefault();
		$('#eventform_noages').hide();
		$('#eventform_hasages').show();
		$('#EventAgeRestriction').val('21+').focus().select();
	});
	$('#event_remove_age_restriction').click(function(event) {
		event.preventDefault();
		$('#eventform_noages').show();
		$('#eventform_hasages').hide();
		$('#EventAgeRestriction').val('');
	});
	$('#event_add_source').click(function(event) {
		event.preventDefault();
		$('#eventform_nosource').hide();
		$('#eventform_hassource').show();
		$('#EventSource').focus();
	});
	$('#event_remove_source').click(function(event) {
		event.preventDefault();
		$('#eventform_nosource').show();
		$('#eventform_hassource').hide();
		$('#EventSource').val('');
	});
	var form = $('#EventAddForm');
	if (form.length == 0) {
		form = $('#EventEditForm');
	}
	form.submit(function(event) {
		if ($('#EventTitle').val() == '') {
			alert('Please give this event a title.');
			$('#EventTitle').focus();
			return false;
		}
		if ($('#datepicker_hidden').val() == '') {
			alert('Please select a date.');
			return false;
		}
		if ($('#EventLocation').val() == '') {
			alert('Please enter a location.');
			$('#EventLocation').focus();
			return false;
		}
		var description = tinyMCE.get('EventDescription').getContent();
		if (description == "" || description == null) {
			alert('Please enter a description of this event.');
			return false;
		}
		return true;
	});
	if ($('#EventCost').val() != '') {
		$('#eventform_nocost').hide();
		$('#eventform_hascost').show();
	}
	if ($('#EventAgeRestriction').val() != '') {
		$('#eventform_noages').hide();
		$('#eventform_hasages').show();
	}
	if ($('#EventSource').val() != '') {
		$('#eventform_nosource').hide();
		$('#eventform_hassource').show();
	}
	if ($('#EventAddress').val() != '') {
		$('#eventform_noaddress').hide();
		$('#eventform_address').show();
	}
	if ($('#eventform_hasendtime_boolinput').val() == '1') {
		$('#eventform_hasendtime').show();
		$('#eventform_noendtime').hide();
	}
	setupLocationAutocomplete();
	setupAddressLookup();

	$('#series_editing_options input[type=radio]').click(function () {
		if ($(this).val() != '0') {
			$('#series_editing_warning').slideDown(300);
		} else {
			$('#series_editing_warning').slideUp(300);
		}
	});
	if ($('#EventUpdateSeries0').is(':checked')) {
		$('#series_editing_warning').hide();
	}
}

function setupLocationAutocomplete() {
	if (eventForm.previousLocations.length == 0) {
		return;
	}
	$('#EventLocation').bind('keydown', function (event) {
		// don't navigate away from the field on tab when selecting an item
		if (event.keyCode === $.ui.keyCode.TAB && $(this).data('is_open')) {
			event.preventDefault();
		}
	}).bind('autocompleteopen', function(event, ui) {
	    $(this).data('is_open', true);
	}).bind('autocompleteclose', function(event, ui) {
	    $(this).data('is_open', false);
	}).autocomplete({
		source: function(request, response) {
			var term = request.term;
			if (term == '') {
				return eventForm.previousLocations;
			}
			var pattern = new RegExp($.ui.autocomplete.escapeRegex(term), 'i');
			var matches = jQuery.grep(eventForm.previousLocations, function(location) {
				var loc_name = location.label;
				return pattern.test(loc_name);
			});
			response(matches.slice(0, 10));
		},
		delay: 0,
		minLength: 1,
		response: function() {
			$('#tag_autosuggest_loading').hide();
		},
		focus: function() {
			// prevent value inserted on focus
            return false;
		},
		select: function(event, ui) {
			// Add the selected term to 'selected tags'
			var location = ui.item.label;
			this.value = location;

			// Update address (might be changed to blank)
			var address = ui.item.value;
			$('#EventAddress').val(address);
			if (address != '' && ! $('#eventform_address').is(':visible')) {
				$('#eventform_noaddress').hide();
				$('#eventform_address').show();
			}
			return false;
		}
	}).focus(function() {
		// Trigger autocomplete on field focus
		$(this).autocomplete('search', $(this).val());
	});
}

function setupAddressLookup() {
	$('#EventLocation').change(function() {
		var location_field = $(this);
		var location_name = location_field.val();
		var address_field = $('#EventAddress');

		// Take no action if the address has already been entered
		if (address_field.val() != '') {
			return;
		}

		// Take no action if location name is blank
		if (location_name == '') {
			return;
		}
		var address_field = $('#EventAddress');
		var address_row = $('#eventform_address');
		var address_handle = $('#eventform_noaddress');
		// Attempt to look up address from this user's previous locations
		var matches = jQuery.grep(eventForm.previousLocations, function(location_obj) {
			return location_obj.label == location_name;
		});
		if (matches.length > 0) {
			address_field.val(matches[0].value);
			if (! address_row.is(':visible')) {
				address_handle.hide();
				address_row.show();
			}

		// Ask the database for the address
		} else {
			var address_th = $('#eventform_address th');
			$.ajax({
				url: '/events/getAddress/'+location_name,
				beforeSend: function() {
					address_th.addClass('loading');
					address_handle.addClass('loading');
				},
				complete: function() {
					address_th.removeClass('loading');
					address_handle.removeClass('loading');
				},
				success: function (data) {
					// Make sure address field hasn't received input since the AJAX request
					if (data == '' || address_field.val() != '') {
						return;
					}
					address_field.val(data);
					if (! address_row.is(':visible')) {
						address_handle.hide();
						address_row.show();
					}
				},
				error: function () {
					console.log('Error trying to pull location address from /events/getAddress/'+location_name);
				}
			});
		}
	});

	// Stop in-progress address lookup on any keydown in address field
}

function setupDatepickerMultiple(default_date, preselected_dates) {
	var options = {
		defaultDate: default_date,
		altField: '#datepicker_hidden',
		onSelect: function(date) {
			var dates = $('#datepicker').multiDatesPicker('getDates');
			if (dates.length > 1) {
				showSeriesRow();
				$('#EventSeriesTitle').attr('required', 'required');
				var series_title_field = $('#EventSeriesTitle');
				if (series_title_field.val() == '') {
					series_title_field.val($('#EventTitle').val());
				}
			} else {
				hideSeriesRow();
				$('#EventSeriesTitle').removeAttr('required');
			}
		}
	};
	if (preselected_dates.length > 0) {
		options.addDates = preselected_dates;
	}
	$('#datepicker').multiDatesPicker(options);
}

function showSeriesRow() {
	var row = $('#series_row');
	if (row.is(':visible')) {
		return;
	}
	if (row.children().children('div.slide_helper').length == 0) {
		row.children().wrapInner('<div class="slide_helper" />');
	}
	var slide_helpers = row.find('div.slide_helper');
	slide_helpers.hide();
	row.show();
	slide_helpers.slideDown(300);
}

function hideSeriesRow() {
	var row = $('#series_row');
	if (row.children().children('div.slide_helper').length == 0) {
		row.children().wrapInner('<div class="slide_helper" />');
	}
	row.find('div.slide_helper').slideUp(300, function() {
		row.hide();
	});
}

function setupDatepickerSingle(default_date) {
	$('#datepicker').datepicker({
		defaultDate: default_date,
		onSelect: function(date) {
			$('#datepicker_hidden').val(date);
		}
	});
}
