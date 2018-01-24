/*jshint loopfunc:true */
var muncieEventsMonthWidget = {
	current_month: null,
	current_year: null,
	prepared_calendars: [],
	events: {},
	fade_duration: 200,

	prepareWidget: function() {

	},

	prepareLinks: function(calendar_selector) {
		var calendar = $(calendar_selector);

		// Prev / next links
		calendar.find('thead a.prev_month').click(function(event) {
			event.preventDefault();
			muncieEventsMonthWidget.goToPrevMonth();
		});
		calendar.find('thead a.next_month').click(function(event) {
			event.preventDefault();
			muncieEventsMonthWidget.goToNextMonth();
		});

		// Event links
		calendar.find('tbody a.event').click(function(event) {
			event.preventDefault();
			var event_id = $(this).data('eventId');
			muncieEventsMonthWidget.showEvent(event_id);
		});

		// Date and 'more events' links
		var year = calendar.data('year');
		var month = calendar.data('month');
		calendar.find('a.date, a.more').click(function(event) {
			event.preventDefault();
			var day = $(this).data('day');
			muncieEventsMonthWidget.listEventsOnDay(year, month, day);
		});

		this.prepared_calendars.push(calendar_selector);
	},

	/**
	 * Prepares the 'event actions' block (like on Facebook, export, edit, etc.)
	 * @param container_selector
	 */
	setupEventActions: function(container_selector) {
		$(container_selector).find('.export_options_toggler').click(function(event) {
			event.preventDefault();
			var link = $(this);
			link.parent('div').toggleClass('open');
			link.next('.export_options').slideToggle(300);
		});
	},

	showEvent: function(event_id) {
		var calendar_container = $('#calendar_container');
		var event_view = $('#event_'+event_id);
		if (event_view.length > 0) {
			calendar_container.fadeOut(this.fade_duration, function() {
				event_view.fadeIn(muncieEventsMonthWidget.fade_duration);
				$(window).scrollTop(0);
			});
			return;
		}
		$.ajax({
			url: '/widgets/event/'+event_id,
			beforeSend: function() {
				muncieEventsMonthWidget.loadingStart();
			},
			success: function(data) {
				calendar_container.fadeOut(muncieEventsMonthWidget.fade_duration, function() {
					var event_view = $('<div id="event_'+event_id+'" style="display: none;"></div>').html(data);
					muncieEventsMonthWidget.setupEventActions('#event_'+event_id);
					$('#events').append(event_view);
					$('#load_more_events').hide();
					var back_link = $('<a href="#" class="back">&larr; Back</a>').click(function (event) {
						event.preventDefault();
						$('#event_'+event_id).fadeOut(muncieEventsMonthWidget.fade_duration, function() {
							calendar_container.fadeIn(muncieEventsMonthWidget.fade_duration);
							$('#load_more_events').show();
						});
					});
					event_view.prepend(back_link);
					//back_link.clone(true).appendTo(event_view);
					event_view.fadeIn(muncieEventsMonthWidget.fade_duration);
					$(window).scrollTop(0);
					muncieEventsImagePopups.prepare();
				});
			},
			error: function() {
				alert('There was an error loading that event. Please try again.');
			},
			complete: function() {
				muncieEventsMonthWidget.loadingEnd();
			}
		});
	},

	listEventsOnDay: function(year, month, day) {
		var date = year+'-'+month+'-'+day;

		// If there are no events on this date
		if (! this.events[date]) {
			// Find appropriate cell
			var cell = $('#calendar_'+year+'-'+month+' a[data-day='+day+']').parents('td');
			if (cell.length == 0) {
				console.log('Error: Calendar cell not found. ($(\'#calendar_'+year+'-'+month+' a[data-day='+day+']\').parents(\'td\');)');
				return;
			}

			// Avoid creating multiple messages
			var container = cell.children('div');
			var existing_message = container.children('.no_events');
			if (existing_message.length > 0) {
				return;
			}

			// Display message that fades in and out
			var message = $('<p class="no_events">No events on this day.</p>').hide();
			container.append(message);
			message.fadeIn(500, function() {
				setTimeout(function() {
					message.fadeOut(500, function() {
						message.remove();
					});
				}, 3000);
			});
			return;
		}

		var calendar_container = $('#calendar_container');
		var event_lists_container = $('#event_lists');
		var event_list = $('#events_on_'+year+'_'+month+'_'+day);

		// If this list has already been generated
		if (event_list.length > 0) {
			calendar_container.fadeOut(this.fade_duration, function() {
				event_list.show();
				event_lists_container.fadeIn(this.fade_duration);
			});
			return;
		}

		// If a list must be generated
		event_list = $('<div id="events_on_'+year+'_'+month+'_'+day+'"></div>');
		event_list.append('<h2>'+this.events[date].heading+'</h2>');

		for (var i = 0; i < this.events[date].events.length; i++) {
			var event = this.events[date].events[i];
			var event_link = $('<a href="'+event.url+'" data-event-id="'+event.id+'" class="event"></a>');
			event_link.click(function(event) {
				event.preventDefault();
				var event_id = $(this).data('eventId');
				event_lists_container.fadeOut(muncieEventsMonthWidget.fade_duration, function() {
					event_list.hide();
					muncieEventsMonthWidget.showEvent(event_id);
				});
			});
			event_link.append('<span class="time">'+event.time+'</span>');
			event_link.append('<i class="icon '+event.category_icon_class+'" title="'+event.category_name+'"></i>');
			event_link.append(event.title);
			event_list.append(event_link);
		}
		/*
		var close_button = $('<a href="#" class="close"><span class="x">x</span>Close</a>');
		close_button.click(function(event) {
			event.preventDefault();
			event_lists_container.fadeOut(muncieEventsMonthWidget.fade_duration, function() {
				event_list.hide();
				calendar_container.fadeIn(muncieEventsMonthWidget.fade_duration);
			});
		});
		event_list.prepend(close_button);
		*/
		var back_link = $('<a href="#" class="back">&larr; Back</a>').click(function (event) {
			event.preventDefault();
			event_list.fadeOut(muncieEventsMonthWidget.fade_duration, function() {
				calendar_container.fadeIn(muncieEventsMonthWidget.fade_duration);
			});
		});
		event_list.prepend(back_link);
		event_lists_container.append(event_list);
		calendar_container.fadeOut(muncieEventsMonthWidget.fade_duration, function() {
			event_lists_container.fadeIn(this.fade_duration);
		});
	},

	getNextMonth: function() {
		var current_month = this.getCurrentMonthInt();
		var next_month = (current_month == 12) ? 1 : current_month + 1;
		return this.zeroPadMonth(next_month);
	},

	getPrevMonth: function() {
		var current_month = this.getCurrentMonthInt();
		var prev_month = (current_month == 1) ? 12 : current_month - 1;
		return this.zeroPadMonth(prev_month);
	},

	getCurrentMonthInt: function() {
		var month = this.current_month;
		if (typeof(month) == 'string' && month.substr(0, 1) == '0') {
			return parseInt(month.substr(1, 1));
		}
		return parseInt(month);
	},

	zeroPadMonth: function(month) {
		if (month < 10) {
			return '0'+month;
		}
		return month;
	},

	getNextMonthsYear: function() {
		var current_year = parseInt(this.current_year);
		var current_month = this.getCurrentMonthInt();
		return (current_month == 12) ? current_year + 1 : current_year;
	},

	getPrevMonthsYear: function() {
		var current_year = parseInt(this.current_year);
		var current_month = this.getCurrentMonthInt();
		return (current_month == 1) ? current_year - 1 : current_year;
	},

	setCurrentMonth: function(month) {
		this.current_month = month;
	},

	setCurrentYear: function(year) {
		this.current_year = year;
	},

	goToNextMonth: function() {
		this.goToMonth(this.getNextMonthsYear(), this.getNextMonth());
	},

	goToPrevMonth: function() {
		this.goToMonth(this.getPrevMonthsYear(), this.getPrevMonth());
	},

	goToMonth: function(year, month) {
		var loaded_calendar = $('#calendar_'+year+'-'+month);
		if (loaded_calendar.length > 0) {
			$('#calendar_container table.calendar:visible').fadeOut(this.fade_duration, function() {
				loaded_calendar.fadeIn(muncieEventsMonthWidget.fade_duration);
			});
			muncieEventsMonthWidget.setCurrentMonth(month);
			muncieEventsMonthWidget.setCurrentYear(year);
			return;
		}
		var qs_separator_index = window.location.href.indexOf('?');
		var query_string = '';
		if (qs_separator_index != -1) {
			query_string = window.location.href.slice(qs_separator_index + 1);
		} else {
			query_string = '';
		}
		$.ajax({
			url: '/widgets/month/'+year+'-'+month+'?'+query_string,
			beforeSend: function() {
				muncieEventsMonthWidget.loadingStart();
				//$('#calendar_container table.calendar:visible').fadeOut(muncieEventsMonthWidget.fade_duration);
			},
			success: function(data) {
				$('#calendar_container table.calendar:visible').fadeOut(muncieEventsMonthWidget.fade_duration, function() {
					$(this).parent().hide().append(data).fadeIn(muncieEventsMonthWidget.fade_duration);
                    console.log(muncieEventsMonthWidget);
				});
			},
			error: function() {
				alert('There was an error loading that month. Please try again.');
			},
			complete: function() {
				muncieEventsMonthWidget.loadingEnd();
			}
		});
	},

	setEvents: function(events) {
		if (events.length == 0) {
			return;
		}
		for (var date in events) {
			var events_on_date = events[date];
			if (events_on_date.length == 0) {
				continue;
			}
			this.events[date] = events_on_date;
			/*
			for (var i = 0; i < events_on_date.length; i++) {
				var event = events_on_date[i];
			}
			*/
		}
	},

	loadingStart: function() {
		$('#loading').fadeIn(this.fade_duration);
	},

	loadingEnd: function() {
		$('#loading').fadeOut(this.fade_duration);
	}
};
