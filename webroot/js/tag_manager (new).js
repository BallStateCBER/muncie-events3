var TagManager = {
    tags: [],
    selectedTags: [],

    /**
     * @param data An array of tag objects
     * @param container $('#container_id')
     * @returns
     */
    createTagList: function(data, container) {
        var list = $('<ul></ul>');
        var availableTagsContainer = $('#available_tags');
        for (var i = 0; i < data.length; i++) {
            var tagId = data[i].id;
            var tagName = data[i].name;
            var children = data[i].children;
            var hasChildren = (children.length > 0);
            var isSelectable = data[i].selectable;
            var listItem = $('<li id="available_tag_li_'+tagId+'"></li>');
            var row = $('<div class="single_row"></div>');
            listItem.append(row);
            list.append(listItem);

            if (isSelectable) {
                var tagLink = $('<a href="#" class="available_tag" title="Click to select" id="available_tag_'+tagId+'"></a>');
                tagLink.append(tagName);
                (function(tagId) {
                    tagLink.click(function (event) {
                        event.preventDefault();
                        var link = $(this);
                        var tagName = link.html();
                        var listItem = link.parents('li').first();
                        TagManager.selectTag(tagId, tagName, listItem);
                    });
                })(tagId);
                tagName = tagLink;
            }

            // Bullet point
            if (hasChildren) {
                var collapsed_icon = $('<a href="#" title="Click to expand/collapse"></a>');
                collapsed_icon.append('<img src="/img/icons/menu-collapsed.png" class="expand_collapse" />');
                (function(children) {
                    collapsed_icon.click(function(event) {
                        event.preventDefault();
                        var icon = $(this);
                        var icon_container = icon.parent('div');
                        var children = data[i].children;
                        var children_container = icon_container.next('.children');
                        var row = icon_container.parent('li');

                        // Populate list if it is empty
                        if (children_container.is(':empty')) {
                            TagManager.createTagList(children, children_container);
                        }

                        // Open/close
                        children_container.slideToggle(200, function() {
                            var icon_image = icon.children('img.expand_collapse');
                            if (children_container.is(':visible')) {
                                icon_image.prop('src', '/img/icons/menu-expanded.png');
                            } else {
                                icon_image.prop('src', '/img/icons/menu-collapsed.png');
                            }
                        });
                    });
                })(children);

                row.append(collapsed_icon);
            } else {
                row.append('<img src="/img/icons/menu-leaf.png" class="leaf" />');
            }

            row.append(tagName);

            // Tag and submenu
            if (hasChildren) {
                var children_container = $('<div style="display: none;" class="children"></div>');
                row.after(children_container);
            }

            // If tag has been selected
            if (isSelectable && this.tagIsSelected(tagId)) {
                tagName.addClass('selected');
                if (! hasChildren) {
                    listItem.hide();
                }
            }
        }
        container.append(list);
    },

    tagIsSelected: function(tagId) {
        var selectedTags = $('#selected_tags a');
        for (var i = 0; i < selectedTags.length; i++) {
            var tag = $(selectedTags[i]);
            if (tag.data('tagId') == tagId) {
                return true;
            }
        }
        return false;
    },

    preselectTags: function(selectedTags) {
        if (selectedTags.length == 0) {
            return;
        }
        $('#selected_tags_container').show();
        for (var i = 0; i < selectedTags.length; i++) {
            TagManager.selectTag(selectedTags[i].id, selectedTags[i].name);
        }
    },

    unselectTag: function(tagId, unselect_link) {
        var availableTagListItem = $('#available_tag_li_'+tagId);

        // If available tag has not yet been loaded, then simply remove the selected tag
        if (availableTagListItem.length == 0) {
            unselect_link.remove();
            if ($('#selected_tags').children().length == 0) {
                $('#selected_tags_container').slideUp(200);
            }
            return;
        }

        // Remove 'selected' class from available tag
        var available_link = $('#available_tag_'+tagId);
        if (available_link.hasClass('selected')) {
            available_link.removeClass('selected');
        }

        var remove_link = function() {
            unselect_link.fadeOut(200, function() {
                unselect_link.remove();
                if ($('#selected_tags').children().length == 0) {
                    $('#selected_tags_container').slideUp(200);
                }
            });
        };

        availableTagListItem.slideDown(200);

        // If available tag is not visible, then no transfer effect
        if (available_link.is(':visible')) {
            var options = {
                to: '#available_tag_'+tagId,
                className: 'ui-effects-transfer'
            };
            unselect_link.effect('transfer', options, 200, remove_link);
        } else {
            remove_link();
        }
    },

    selectTag: function(tagId, tagName, availableTagListItem) {
        var selected_container = $('#selected_tags_container');
        if (! selected_container.is(':visible')) {
            selected_container.slideDown(200);
        }

        // Do not add tag if it is already selected
        if (this.tagIsSelected(tagId)) {
            return;
        }

        // Add tag
        var listItem = $('<a href="#" title="Click to remove" data-tag-id="'+tagId+'" id="selected_tag_'+tagId+'"></a>');
        listItem.append(tagName);
        listItem.append('<input type="hidden" name="data[Tag][]" value="'+tagId+'" />');
        listItem.click(function (event) {
            event.preventDefault();
            var unselect_link = $(this);
            var tagId = unselect_link.data('tagId');
            TagManager.unselectTag(tagId, unselect_link);
        });
        listItem.hide();
        $('#selected_tags').append(listItem);
        listItem.fadeIn(200);

        // If available tag has not yet been loaded, then return
        var availableTagListItem = $('#available_tag_li_'+tagId);
        if (availableTagListItem.length == 0) {
            return;
        }

        // Hide/update link to add tag
        var link = $('#available_tag_'+tagId);
        var options = {
            to: '#selected_tag_'+tagId,
            className: 'ui-effects-transfer'
        };
        var callback = function() {
            link.addClass('selected');
            var hasChildren = (availableTagListItem.children('div.children').length != 0);
            if (! hasChildren) {
                availableTagListItem.slideUp(200);
            }
        };
        link.effect('transfer', options, 200, callback);
    },

    setupAutosuggest: function(selector) {
        $(selector).bind('keydown', function (event) {
            if (event.keyCode === $.ui.keyCode.TAB && $(this).data('autocomplete').menu.active) {
                event.preventDefault();
            }
        }).autocomplete({
            source: function(request, response) {
                $.getJSON('/tags/auto_complete', {
                    term: extractLast(request.term)
                }, response);
            },
            delay: 0,
            search: function() {
                var term = extractLast(this.value);
                if (term.length < 2) {
                    return false;
                }
                $(selector).siblings('img.loading').show();
            },
            response: function() {
                $(selector).siblings('img.loading').hide();
            },
            focus: function() {
                return false;
            },
            select: function(event, ui) {
                var tagName = ui.item.label;
                var terms = split(this.value);
                terms.pop();
                terms.push(tagName);
                // Add placeholder to get the comma-and-space at the end
                terms.push('');
                this.value = terms.join(', ');
                return false;
            }
        });
    },

    setupCustomTagInput: function(selector) {
        if (! selector) {
            selector = '#custom_tag_input';
        }
        $(selector).bind('keydown', function (event) {
            // don't navigate away from the field on tab when selecting an item
            if (event.keyCode === $.ui.keyCode.TAB && $(this).data('autocomplete').menu.active) {
                event.preventDefault();
            }
        }).autocomplete({
            source: function(request, response) {
                $.getJSON('/tags/auto_complete', {
                    term: extractLast(request.term)
                }, response);
            },
            delay: 0,
            search: function() {
                // custom minLength
                var term = extractLast(this.value);
                if (term.length < 2) {
                //    return false;
                }
                $('#tag_autosuggest_loading').show();
            },
            response: function() {
                $('#tag_autosuggest_loading').hide();
            },
            focus: function() {
                // prevent value inserted on focus
                return false;
            },
            select: function(event, ui) {
                // Add the selected term to 'selected tags'
                var tagName = ui.item.label;
                var tagId = ui.item.value;
                TagManager.selectTag(tagId, tagName);

                var terms = split(this.value);
                // Remove the term being typed from the input field
                terms.pop();
                if (terms.length > 0) {
                    // Add placeholder to get the comma-and-space at the end
                    terms.push('');
                }
                this.value = terms.join(', ');

                return false;
            }
        });
    }
};
