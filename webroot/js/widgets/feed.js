var muncieEventsFeedWidget = {
     next_start_date: null,
     no_more_events: false,
     fade_duration: 200,

     prepareWidget: function() {
          $('#load_more_events').click(function(event) {
               event.preventDefault();
               muncieEventsFeedWidget.loadMoreEvents();
          });
     },

     prepareLinks: function(event_ids) {
          if (event_ids.length == 0) {
               return;
          }
          var event_list = $('#event_list');
          for (i = 0; i < event_ids.length; i++) {
               var event_id = event_ids[i];
               (function(eid, elist) {
                    $('#event_link_'+eid).click(function (event) {
                        $('#load_more_events').hide();
                        $('#load_more_events_wrapper').hide();
                         event.preventDefault();
                         muncieEventsFeedWidget.showEvent(eid, elist);
                    });
               })(event_id, event_list);
          }
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

     showEvent: function(eid, elist) {
 		var event_view = $('#event_'+eid);
 		if (event_view.length > 0) {
 			elist.fadeOut(muncieEventsFeedWidget.fade_duration, function() {
 				event_view.fadeIn(muncieEventsFeedWidget.fade_duration);
 				$(window).scrollTop(0);
 			});
 			return;
 		}
 		var event_link = $('#event_link_'+eid);
 		$.ajax({
 			url: '/widgets/event/'+eid,
 			beforeSend: function() {
 				muncieEventsFeedWidget.loadingStart();
 			},
 			success: function(data) {
                muncieEventsFeedWidget.loadingEnd();
 				elist.after($('<div id="event_'+eid+'" style="display: none;"></div>').html(data));
 				muncieEventsFeedWidget.setupEventActions('#event_'+eid);
 				elist.fadeOut(muncieEventsFeedWidget.fade_duration, function() {
 					var event_view = $('#event_'+eid);
 					var back_link = $('<a href="#" class="back">&larr; Back</a>').click(function (event) {
                //        $('#load_more_events').show();
                //         $('#load_more_events_wrapper').show();
 						event.preventDefault();
 						$('#event_'+eid).fadeOut(muncieEventsFeedWidget.fade_duration, function() {
 							$('#event_list').fadeIn(muncieEventsFeedWidget.fade_duration);
 							$(window).scrollTop(event_link.offset().top);
 						});
 					});
 					event_view.prepend(back_link);
 					event_view.fadeIn(muncieEventsFeedWidget.fade_duration);
 					$(window).scrollTop(0);
 					muncieEventsImagePopups.prepare();
 				});
 			},
 			error: function() {
                muncieEventsFeedWidget.loadingEnd();
 				alert('There was an error loading that event. Please try again.');
 			},
 			complete: function() {
                muncieEventsFeedWidget.loadingEnd();
 			}
 		});
 	},

     /**Sets the date that the next "page" of events will start at
      * @param date A string in 'YYYY-MM-DD' format
      */
     setNextStartDate: function(date) {
          muncieEventsFeedWidget.next_start_date = date;
     },

     setNoMoreEvents: function() {
          muncieEventsFeedWidget.no_more_events = true;
          $('#load_more_events').hide();
     },

     loadMoreEvents: function() {
          var wrapper = $('#load_more_events').parent();
          if (wrapper.hasClass('loading')) {
               return;
          }
          var qs_separator_index = window.location.href.indexOf('?');
          if (qs_separator_index != -1) {
               var queryString = window.location.href.slice(qs_separator_index + 1);
          } else {
               var queryString = '';
          }
          $.ajax({
               url: '/widgets/feed/'+muncieEventsFeedWidget.next_start_date+'?'+queryString,
               beforeSend: function() {
                    wrapper.addClass('loading');
               },
               success: function(data) {
                    var height = $('#event_list').height();
                    $('#event_list').append(data);
                    $('html, body').animate({
                        scrollTop: height
                    }, 500);
                    muncieEventsImagePopups.prepare();
               },
               error: function() {
                    alert('There was an error loading more events. Please try again.');
               },
               complete: function() {
                    wrapper.removeClass('loading');
                    if (muncieEventsFeedWidget.no_more_events) {
                         // $('#event_list').append('<p class=\"no_events\">No more events found.</p>');
                    }
               }
          });
     },

     loadingStart: function() {
          $('#loading').fadeIn(this.fade_duration);
     },

     loadingEnd: function() {
          $('#loading').fadeOut(this.fade_duration);
     }
};
