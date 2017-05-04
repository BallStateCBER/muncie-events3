var widgetCustomizer = {
	updateWidgetDemo: function(version, options) {
		if (typeof options == 'undefined') {
			options = '';
		}
		$.ajax({
			url: '/widgets/demo_'+version+'/?'+options,
			beforeSend: function() {},
			success: function(data) {
				$('#widget_demo').html(data);
			},
			error: function() {},
			complete: function() {}
		});
	},

	getOptionsQueryString: function() {
		// Begin constructing query string
		var options = [];

		// Style (colors and dimensions) input
		$('.widget_controls input[type=text].style').each(function () {
			var field = $(this);
			options.push(this.name+'='+encodeURIComponent(field.val()));
		});

		// Checkboxes with 'option' class
		$('.widget_controls input[type=checkbox].option').each(function () {
			var field = $(this);
			if (field.is(':checked')) {
				value = 1;
			} else {
				value = 0;
			}
			options.push(this.name+'='+value);
		});

		// Categories
		if ($('#WidgetFilterToggler_categories').is(':checked')) {
			var categories = [];
			$('.widget_controls input[type=checkbox].category').each(function () {
				if ($(this).is(':checked')) {
					categories.push($(this).val());
				}
			});
			options.push('category='+encodeURIComponent(categories.join(',')));
		}

		// Location
		if ($('#WidgetFilterToggler_location').is(':checked')) {
			var location_name = $('#WidgetFilter_location_input').val();
			if (location_name != '') {
				options.push('location='+encodeURIComponent(location_name));
			}
		}

		// Included tags
		if ($('#WidgetFilterToggler_tag_include').is(':checked')) {
			var tags_inc = $('#WidgetFilter_tag_include_input').val();
			if (tags_inc != '') {
				options.push('tags_included='+encodeURIComponent(tags_inc));
			}
		}

		// Excluded tags
		if ($('#WidgetFilterToggler_tag_exclude').is(':checked')) {
			var tags_exc = $('#WidgetFilter_tag_exclude_input').val();
			if (tags_exc != '') {
				options.push('tags_excluded='+encodeURIComponent(tags_exc));
			}
		}

		// Max visible events (month widget)
		var events_displayed_per_day = $('#WidgetEventsDisplayedPerDay');
		if (events_displayed_per_day) {
			options.push('events_displayed_per_day='+encodeURIComponent(events_displayed_per_day.val()));
		}

		return options.join('&amp;');
	},

	updateColorValue: function(field, color) {
		var all = color.val('all');
		if (! all) {
			return;
		}
		if (all.a == 255) {
			$(field).val('#' + all.hex);
		} else if (all.a == 0) {
			$(field).val('transparent');
		} else {
			var alpha = Math.round((all.a * 100) / 255) / 100;
			$(field).val('rgba('+all.r+', '+all.g+', '+all.b+', '+alpha+')');
		}
	},

	setupWidgetDemo: function(version) {
		// Have the demo (on the right) automatically update to reflect form values
		// (so if the user enters stuff and refreshes, the demo looks like it should)
		this.updateWidgetDemo(version);

		// Have color picker pop up
		/*
		$('.color_input').colorpicker({
			alpha: true,
			colorFormat: ['RGB', 'RGBA'],
			parts: ['map', 'bar', 'hex', 'rgb', 'alpha', 'preview', 'swatches', 'footer']
		});
		*/

		$('.color_input').each(function() {
			var color_field = this;
			var color_picker = $('<span id="'+this.id+'_color_picker"></span>');
			color_picker.insertAfter($(color_field));
			color_picker.jPicker(
				{
					window: {
						expandable: true,
						position: {
							x: 'screenCenter',
							y: 200
						},
						effects: {
							type: 'fade',
							speed: {
								show: '200',
								hide: '200'
							}
						},
						alphaSupport: true,
						alphaPrecision: 0
					},
					color: {
						active: $(color_field).val()
							//new $.jPicker.Color({ hex: '123456' })
					}
				},
				// Commit
				function(color, context) {
					widgetCustomizer.updateColorValue(color_field, color);
				},
				// Live
				function(color, context) {
					widgetCustomizer.updateColorValue(color_field, color);
				},
				// Cancel
				function(color, context) {
					//widgetCustomizer.updateColorValue(color_field, color);
				}
			);
		});

		// Expand control sections
		$('.widget_controls h3').each(function() {
			var header = $(this);
			var link = header.children('a');
			var section = header.next('div');
			link.click(function (event) {
				event.preventDefault();
				section.slideToggle(300);
			});
		});

		// 'All categories' checkbox
		$('#WidgetCatAll').click(function() {
			var checked = $(this).is(':checked');
			var checkboxes = $('.widget_controls input[type=checkbox].category');
			checkboxes.prop('checked', checked);
		});

		// When form is submitted...
		$('.widget_controls form').submit(function(event) {
			event.preventDefault();
			var options = widgetCustomizer.getOptionsQueryString();
			widgetCustomizer.updateWidgetDemo(version, options);
		});

		// Categories filter
		var categories_toggler = $('#WidgetFilterToggler_categories');
		var categories_wrapper = $('#WidgetFilter_categories');
		if (categories_toggler.is(':checked')) {
			categories_wrapper.show();
		} else {
			categories_wrapper.hide();
		}
		categories_toggler.click(function(event) {
			if ($(this).is(':checked')) {
				categories_wrapper.slideDown(300);
			} else {
				categories_wrapper.slideUp(300);
			}
		});

		// Location filter
		var location_toggler = $('#WidgetFilterToggler_location');
		var location_wrapper = $('#WidgetFilter_location');
		var location_input = $('#WidgetFilter_location_input');
		if (location_toggler.is(':checked')) {
			if (location_input.val() == '') {
				location_toggler.prop('checked', false);
				location_wrapper.hide();
			} else {
				location_wrapper.show();
			}
		} else {
			location_wrapper.hide();
		}
		location_toggler.click(function(event) {
			if ($(this).is(':checked')) {
				location_wrapper.slideDown(300);
			} else {
				location_wrapper.slideUp(300);
			}
		});

		// Tag include filters
		var tag_include_toggler = $('#WidgetFilterToggler_tag_include');
		var tag_include_wrapper = $('#WidgetFilter_tag_include');
		var tag_include_input = $('#WidgetFilter_tag_include_input');
		if (tag_include_toggler.is(':checked')) {
			if (tag_include_input.val() == '') {
				tag_include_toggler.prop('checked', false);
				tag_include_wrapper.hide();
			} else {
				tag_include_wrapper.show();
			}
		} else {
			tag_include_wrapper.hide();
		}
		tag_include_toggler.click(function(event) {
			if ($(this).is(':checked')) {
				tag_include_wrapper.slideDown(300);
			} else {
				tag_include_wrapper.slideUp(300);
			}
		});

		// Tag exclude filters
		var tag_exclude_toggler = $('#WidgetFilterToggler_tag_exclude');
		var tag_exclude_wrapper = $('#WidgetFilter_tag_exclude');
		var tag_exclude_input = $('#WidgetFilter_tag_exclude_input');
		if (tag_exclude_toggler.is(':checked')) {
			if (tag_exclude_input.val() == '') {
				tag_exclude_toggler.prop('checked', false);
				tag_exclude_wrapper.hide();
			} else {
				tag_exclude_wrapper.show();
			}
		} else {
			tag_exclude_wrapper.hide();
		}
		tag_exclude_toggler.click(function(event) {
			if ($(this).is(':checked')) {
				tag_exclude_wrapper.slideDown(300);
			} else {
				tag_exclude_wrapper.slideUp(300);
			}
		});

		// Icon options
		$('#WidgetShowIcons').change(function () {
			var hide_ge_icon_wrapper = $('#WidgetHideGEIcon_wrapper');
			if ($(this).is(':checked')) {
				if (! hide_ge_icon_wrapper.is(':visible')) {
					hide_ge_icon_wrapper.slideDown(300);
				}
			} else {
				if (hide_ge_icon_wrapper.is(':visible')) {
					hide_ge_icon_wrapper.slideUp(300);
				}
			}
		});
	}
}
