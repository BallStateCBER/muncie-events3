var muncieEvents = {
    // Prevent header dropdown menus from closing
    keepOpenMenus: {categories: false, datepicker: false},

    // Store event filters for the current request (e.g. only a specific category)
    requestEventFilters: {
        category: null,
        tag: null
    },

    paginationPrepared: false,

    imagePopups: {
        groups_processed: []
    },

    // Used by the datePicker in the header
    populatedDates: {}
};

/* function closeSubmenu(name) {
    setTimeout(function() {
        if (muncieEvents.keepOpenMenus[name]) {
            return;
        }
        var submenu = $('#header_nav_'+name);
        submenu.slideUp(200);
    }, 1000);
} */

function setupHeaderNav() {
    // Set up datepicker
    $('#header_datepicker').datepicker({
        onSelect: function(date) {
            window.location.href = '/events/day/'+date;
        },
        beforeShowDay: function(date) {
            // Get zero-padded date components
            var day = date.getDate().toString();
            if (day < 10) {
                day = '0'+day.toString();
            }
            // Because they're zero-indexed for some reason
            var month = (date.getMonth() + 1).toString();
            if (month < 10) {
                month = '0'+month;
            }
            var year = date.getFullYear().toString();
            var month_year = month+'-'+year;

            if (muncieEvents.populatedDates.hasOwnProperty(month_year)) {
                var selectable = muncieEvents.populatedDates[month_year].indexOf(day) != -1;
                var class_name = selectable ? 'has_events' : 'no_events';
                var tooltip = selectable ? null : 'No events';
            } else {
                var selectable = true;
                var class_name = 'has_events';
                var tooltip = 'tooltip text?';
            }

            return [selectable, class_name, tooltip];
        },
        onChangeMonthYear: function(year, month) {

        }
    }).change(function(event) {
        var date = $(this).val();
        window.location.href = '/events/day/'+date;
    });
}

function setupSidebar() {
    function sidebarSelectLocation(location) {
        if (location == '') {
            return false;
        }
        if (location == '[past events]') {
            var url = '/past_locations';
        } else {
            var url = '/location/'+encodeURIComponent(location);
        }
        window.location.href = url;
    };
    $('#sidebar > div.locations select').change(function() {
        var location = $(this).val();
        sidebarSelectLocation(location);
    });
    $('#sidebar_select_location').submit(function(event) {
        event.preventDefault();
        var location = $(this).children('select').val();
        sidebarSelectLocation(location);
    });
}

function setupPagination() {
    if (muncieEvents.paginationPrepared) {
        return;
    }
    $('.paginator_select').each(function () {
        var menu = $(this);
        menu.change(function () {
            var url = menu.data('url');
            window.location.href = url.replace('{page}', menu.val());
        });
    });
    muncieEvents.paginationPrepared = true;
}

function setupSearch() {
    $('#EventSearchForm').submit(function() {
        var input = $('#EventFilter');
        input.val($.trim(input.val()));
        if (input.val() == '') {
            alert('Please enter a word or phrase in the search box to search for events.');
            return false;
        }
        return true;
    });

    var input_field = $('#EventFilter');
    input_field.focus(function() {
        var options = $('#search_options');
        if (options.is(':visible')) {
            options.slideUp(200);
        }
    });
    input_field.bind('keydown', function (event) {
        // don't navigate away from the field on tab when selecting an item
        if (event.keyCode === $.ui.keyCode.TAB && $(this).data('autocomplete').menu.active) {
            event.preventDefault();
        }
    }).autocomplete({
        source: function(request, response) {
            if ($('#EventDirectionFuture').is(':checked')) {
                var direction = 'future';
            } else if ($('#EventDirectionPast').is(':checked')) {
                var direction = 'past';
            } else {
                var direction = 'all';
            }
            $.getJSON('/events/search_autocomplete/'+direction, {
                term: extractLast(request.term)
            }, response);
        },
        search: function() {
            // custom minLength
            var term = extractLast(this.value);
            if (term.length < 2) {
                //return false;
            }
            $('#search_autocomplete_loading').css('visibility', 'visible');
        },
        response: function() {
            $('#search_autocomplete_loading').css('visibility', 'hidden');
        },
        focus: function() {
            // prevent value inserted on focus
            return false;
        },
        select: function(event, ui) {
            this.value = ui.item.value;
            return false;
        }
    });
}

function showFlashMessages() {
    var messages = $('#flash_messages');
    if (!messages.is(':visible')) {
        messages.fadeIn(500);
    }
}

function hideFlashMessages() {
    var messages = $('#flash_messages');
    if (messages.is(':visible')) {
        messages.fadeOut(500, function() {
            $('#flash_messages ul').empty();
        });
    }
}

function insertFlashMessage(message, classname) {
    var msgLi = $(document.createElement('li'))
        .addClass(classname)
        .append('<p>'+message+'</p>')
        .hide()
        .fadeIn(500);
    $('#flash_messages ul').append(msgLi);
    if (!$('#flash_messages').is(':visible')) {
        showFlashMessages();
    }
}

function setupCategoriesApplyButton() {
    $('#apply_changed_event_categories').click(function(event) {
        event.preventDefault();
        var selected_categories = new Array();
        $('#event_category_options input').each(function() {
            if ($(this).is(':checked')) {
                var category_id = this.id.replace('event_category_', '');
                selected_categories.push(category_id);
            }
        });
        if (selected_categories.length) {
            $.ajax({
                url: '/events/categories/categories:'+selected_categories.join(','),
                beforeSend: function () {
                    $('#apply_changed_event_categories_indicator').show();
                },
                success: function (data) {
                    $('#event_categories_inner').html(data);
                    $('#apply_changed_event_categories_indicator').hide();
                    $.ajax({
                        url: '/events/accordion',
                        beforeSend: function () {
                            $('#event_accordion_loading_indicator').show();
                        },
                        success: function (data) {
                            $('#event_accordion_inner').html(data);
                        },
                        error: function(transport) {
                            $('#event_accordion_loading_indicator').hide();
                            alert('There was an error displaying events according to your selection. Please try again.');
                        }
                    });
                    $.ajax({
                        url: '/events/upcoming_tags',
                        beforeSend: function () {
                            $('#tags_loading_indicator').show();
                        },
                        success: function (data) {
                            $('#event_tags_inner').html(data);
                        },
                        complete: function() {
                            $('#tags_loading_indicator').hide();
                        }
                    });
                    $('#change_event_categories').show();
                },
                error: function () {
                    $('#apply_changed_event_categories_indicator').hide();
                    alert('Your preferences could not be saved. Please try again.');
                }
            });
        } else {
            alert('Please select at least one category.');
        }
    });
}

function setupCategoriesChangeButton() {
    $('#change_event_categories').click(function(event) {
        event.preventDefault();
        $('#event_category_options').slideDown(300, function() {
            $('#change_event_categories').hide();
        });
    });
}


/**
 * Creates listeners for clicking on tags and filtering the event list
 */
function setupTagCloud() {
    $('#tag_cloud > a').each(function () {
        $(this).click(function(event) {
            event.preventDefault();
            var tagSlug = $(this).data('tagSlug');
            $.ajax({
                url: '/events/accordion/tag:'+tagSlug,
                beforeSend: function () {
                    $('#event_accordion_loading_indicator').show();
                },
                success: function (data) {
                    $('#event_accordion_inner').html(data);
                }
            });
        });
    });
}

var muncieEventsFeed = {
    nextStartDate: null,
    accordions_prepared: new Array(),
    xfbml_parsed: new Array(),
    no_more_events: false
};
/**Sets the date that the next "page" of events will start at
 * @param date A string in 'YYYY-MM-DD' format
 */
function setNextStartDate(date) {
    muncieEventsFeed.nextStartDate = date;
}

/**
 * Creates listeners for expanding events and revealing addresses.
 * Keeps track of what <ul> elements have already been prepared
 */
function setupEventAccordion() {
    $('ul.event_accordion').each(function() {
        var accordion_id = this.id;
        // Prepared <ul>s are given IDs.
        // <ul>s without IDs or with IDs not in muncieEventsFeed.accordions_prepared need to be prepared.
        if (!accordion_id || muncieEventsFeed.accordions_prepared.indexOf(accordion_id) == -1) {
            if (!accordion_id) {
                this.id = 'event_accordion_'+(muncieEventsFeed.accordions_prepared.length + 1);
            }
            $('#'+this.id+' > li > a.more_info_handle').click(function(event) {
                event.preventDefault();
                var toggler = $(this);
                var event_id = toggler.data('eventId');
                var collapse = toggler.next('.collapse');
                var thumbnail = toggler.siblings('.tiny_thumbnails').children('a.thumbnail:first-child');
                if (thumbnail.length > 0) {
                    if (collapse.is(':visible') && collapse.css('height') != '0px') {
                        thumbnail.fadeIn(150);
                    } else {
                        thumbnail.fadeOut(500);
                    }
                }

                var more_info_id = 'more_info_'+event_id;
                if (muncieEventsFeed.xfbml_parsed.indexOf(more_info_id) == -1) {
                    FB.XFBML.parse(document.getElementById(more_info_id));
                    muncieEventsFeed.xfbml_parsed.push(more_info_id);
                }
            });
            setupEventActions('#'+this.id);
            muncieEventsFeed.accordions_prepared.push(this.id);
        }
    });
}

/**
 * Prepares the 'event actions' block (like on Facebook, export, edit, etc.)
 * @param container_selector
 */
function setupEventActions(container_selector) {
    $(container_selector).find('.export_options_toggler').click(function(event) {
        event.preventDefault();
        var link = $(this);
        link.parent('div').toggleClass('open');
        link.next('.export_options').slideToggle(300);
    });
}

/**
 * Loads another chunk of events at the bottom of the event accordion
 */
function loadMoreEvents() {
    var more_events_url = '/events/accordion/'+muncieEventsFeed.nextStartDate+'/';
    if (muncieEvents.requestEventFilters.category) {
        more_events_url += 'category:'+muncieEvents.requestEventFilters.category;
    } else if (muncieEvents.requestEventFilters.tag) {
        more_events_url += 'tag:'+muncieEvents.requestEventFilters.tag;
    }

    $.ajax({
        url: more_events_url,
        beforeSend: function() {
            $('#event_accordion_loading_indicator').show();
        },
        success: function(data) {
            $('#event_accordion').append(data);
            muncieEventsImagePopups.prepare();
        },
        error: function() {
            alert('There was an error loading more events. Please try again.');
        },
        complete: function() {
            $('#event_accordion_loading_indicator').hide();
            if (muncieEventsFeed.no_more_events) {
                $('#event_accordion').append('<p class=\"no_events\">No more events found.</p>');
                $('#load_more_events').hide();
            }
        }
    });
}

function setNoMoreEvents() {
    muncieEventsFeed.no_more_events = true;
}

function split(val) {
    return val.split(/,\s*/);
}

function extractLast(term) {
    return split(term).pop();
}

function mailingListFormValidate() {
    var unsubscribe_field = $('#MailingListUnsubscribe');
    if (unsubscribe_field.length != 0 && unsubscribe_field.is(':checked')) {
        return confirm('Are you sure you want to unsubscribe?');
    }
    if ($('#MailingListFrequencyCustom').is(':checked')) {
        var selected_days = $('#custom_frequency_options input[type=checkbox]:checked');
        if (selected_days.length == 0) {
            alert('Please select at least one day on which to receive emails.');
            return false;
        }
    }
    if ($('#MailingListEventCategoriesCustom').is(':checked')) {
        var selected_categories = $('#custom_event_type_options input[type=checkbox]:checked');
        if (selected_categories.length == 0) {
            alert('Please select at least one event category.');
            return false;
        }
    }
    return true;
}

function MailingListToggleFreqOptions() {
    if ($('#MailingListFrequencyCustom').is(':checked')) {
        $('#custom_frequency_options').slideDown(300);
    } else {
        $('#custom_frequency_options').slideUp(300);
    }
}

function MailingListToggleEventTypeOptions() {
    if ($('#MailingListEventCategoriesCustom').is(':checked')) {
        $('#custom_event_type_options').slideDown(300);
    } else {
        $('#custom_event_type_options').slideUp(300);
    }
}

function MailingListToggleBasicOptions() {
    if ($('#MailingListSettingsCustom').is(':checked')) {
        $('#custom_options').slideDown(300);
    } else {
        $('#custom_options').slideUp(300);
    }
}

function setupMailingListForm() {
    MailingListToggleFreqOptions();
    MailingListToggleEventTypeOptions();
    MailingListToggleBasicOptions();
    $('.frequency_options').change(function(event) {
        MailingListToggleFreqOptions();
    });
    $('.category_options').change(function(event) {
        MailingListToggleEventTypeOptions();
    });
    $('.settings_options').change(function(event) {
        MailingListToggleBasicOptions();
    });
    $('#MailingListForm').submit(function(event) {
        return mailingListFormValidate();
    });
}

function prepareImagePopups() {
    $('a[rel*=\"popup\"]').each(function () {
        var link = $(this);
        var options = {
            closeBtnInside: true,
            type: 'image',
            fixedContentPos: false,
            fixedBgPos: true,
            midClick: true,
            removalDelay: 300,
            mainClass: 'my-mfp-zoom-in'
        };
        var caption = link.attr('title');
        var rel = link.attr('rel');

        // Single image
        if (rel == 'popup') {
            options.key = 'single_image';
            if (caption) {
                options.key += '_with_caption';
            } else {
                options.key += '_no_caption';
            }
            link.magnificPopup(options);

        // Group together all images with this value for rel
        } else if (rel.indexOf('popup[') == 0) {

            // Skip a group that's already been processed
            if (muncieEvents.imagePopups.groups_processed.indexOf(rel) != -1) {
                return;
            }

            options.key = 'multiple_images';
            options.delegate = 'a[rel=\"'+rel+'\"]';
            options.gallery = {
                enabled: true,
                navigateByImgClick: true,
                preload: [0,2]
            };
            link.parent().magnificPopup(options);
            muncieEvents.imagePopups.groups_processed.push(rel);
        }
    });
}
