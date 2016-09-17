var Editor = (function () {

    var plugins = [];

    var api = {
        init: function () {

            // $('.block-wrapper')
            //     .each(function () {

            //         // if the block has no height
            //         // use a child to get positioning
            //         if ($(this).height() == 0) {

            //             var children = $(this).children();

            //             var found = false;
            //             children.each(function () {
            //                 if (!found && $(this).height() > 0) {
            //                     child = $(this);
            //                     found = true;
            //                 }
            //             });

            //             $(this)
            //                 .clone()
            //                 .find('> :not(.eight-ui-element)').remove().end()
            //                 .css({
            //                     position: child.css('position'),
            //                     top: child.css('top'),
            //                     left: child.css('left'),
            //                     width: Math.min(child.outerWidth(), $(window).width()),
            //                     height: child.outerHeight()
            //                 })
            //                 .appendTo($(this));
            //         }
            //     });

            $('body').addClass('toolkit');

            Editor.setup();
        },

        reload: function () {
            Editor.setupMarkers();
            Editor.setup();
        },

        setup: function () {

            $('.eight-frame-element').remove();

            $('.eight-block-decorator:not(.eight-ui-bound)')
                .mouseenter(function () {
                    if ($('body').hasClass('eight-is-sorting')) {
                        return;
                    }
                    Editor.showFrame($(this));
                })
                .mouseleave(function () {
                    Editor.hideFrame($(this));
                })
                .addClass('eight-ui-bound')
                ;

            $('.add-item:not(.eight-ui-bound)')
                .click(function (event) {
                    Editor.addItemDialog(event.currentTarget);
                })
                .addClass('eight-ui-bound')
                ;

            $('.eight-page-textarea:not(.eight-rich-editor-bound)')
                .each(function () {
                    CKEDITOR.replace($(this).attr('id'));
                })
                .addClass('eight-rich-editor-bound')
            ;

            $('[data-toggle="tooltip"]').tooltip({
                placement: 'bottom'
            });

            Editor.reloadPlugins();
        },

        addPlugin: function (callback) {
            plugins.push(callback);
        },

        reloadPlugins: function () {
            $.each(plugins, function (context) {
                this(context);
            });
        },

        setupToolbar: function (element) {

            var blockContent = $(element).data('editor-content');

            // toolbar
            var template = $($('#toolbar-template').html()).clone();

            template.find('.eight-toolbar')
                .addClass("eight-frame-element-" + blockContent.id);

            // bind toolbar functioning

            if (element.data('load-variables')) {
                template.find('.btn-edit-variable')
                    .click(function () {
                        Editor.edit(element);
                    });
            } else {
                template.find('.btn-edit-variable').remove();
            }

            template.find('.btn-remove-block')
                .click(function (event) {
                    if (confirm("Delete block '" + element.data('widget-label') + "' ?")) {
                        $.ajax({
                            url: globalRemoveUrl,
                            data: {
                                block_id: blockContent.id
                            },
                            success: function () {
                                Editor.hideFrame(element);
                                element.remove();
                            },
                            error: function () {
                                alert('error');
                            }
                        });
                    }
                });


            if (blockContent.enabled) {
                element.addClass('block-enabled');

                template.find('.btn-disable-block')
                    .click(function (event) {
                        if (confirm("Disable block '" + element.data('widget-label') + "' ?")) {
                            $.ajax({
                                url: globalDisableUrl,
                                data: {
                                    block_id: blockContent.id,
                                    page_id: element.data('page-id')
                                },
                                success: function (response) {
                                    $(response.html).insertAfter(element);
                                    Editor.hideFrame(element);
                                    element.remove();

                                    Editor.reload();
                                },
                                error: function () {
                                    alert('error');
                                }
                            });
                        }
                    });

                template.find('.btn-enable-block').remove();
            } else {
                element.addClass('block-disabled');

                template.find('.btn-enable-block')
                    .click(function (event) {
                        if (confirm("Enable block '" + element.data('widget-label') + "' ?")) {
                            $.ajax({
                                url: globalEnableUrl,
                                data: {
                                    block_id: blockContent.id,
                                    page_id: element.data('page-id')
                                },
                                success: function (response) {
                                    $(response.html).insertAfter(element);
                                    Editor.hideFrame(element);
                                    element.remove();

                                    Editor.reload();
                                },
                                error: function () {
                                    alert('error');
                                }
                            });
                        }
                    });

                template.find('.btn-disable-block').remove();
            }

            $(template).find('.eight-toolbar').appendTo(element);
        },

        showFrame: function (element) {

            var blockContent = $(element).data('editor-content');

            // header
            $('<div/>', { "class": "eight-frame-title eight-frame-element eight-frame-element-" + blockContent.id})
                .css({
                    left: element.offset().left,
                    top: element.offset().top - 30,
                    width: element.outerWidth()
                })
                .html('<span class="' + element.data('widget-icon') + '"></span> ' + element.data('widget-label'))
                .appendTo('body')
                ;

            // left
            $('<div/>', { "class": "eight-frame-side eight-frame-element eight-frame-element-" + blockContent.id })
                .css({
                    left: element.offset().left,
                    top: element.offset().top,
                    height: element.outerHeight()
                })
                .appendTo($('body'));

            // right
            $('<div/>', { "class": "eight-frame-side eight-frame-element eight-frame-element-" + blockContent.id })
                .css({
                    right: $(window).width() - element.offset().left - element.outerWidth(),
                    top: element.offset().top,
                    height: element.outerHeight()
                })
                .appendTo($('body'));

            // bottom
            $('<div/>', { "class": "eight-frame-bottom eight-frame-element eight-frame-element-" + blockContent.id })
                .css({
                    left: element.offset().left,
                    top: element.offset().top + element.outerHeight(),
                    width: element.outerWidth()
                })
                .appendTo($('body'));

            this.setupToolbar(element);

            $('.eight-list-decorator')
                .sortable({
                    items: '.eight-block-decorator',
                    handle: ".btn-sort-block",
                    start: function (event, ui) {
                        $('body').addClass('eight-is-sorting');
                    },
                    stop: function (event, ui) {
                        $('body').removeClass('eight-is-sorting');

                        $('.eight-frame-element').remove();

                        Editor.updateBlocksOrder(ui.item.parent());

                        Editor.reloadPlugins();
                    }
                });

            // setup tooltip
            $('[data-toggle="tooltip"]').tooltip({
                placement: 'bottom'
            });
        },

        hideFrame: function (element) {
            var blockContent = $(element).data('editor-content');

            $('.eight-frame-element-' + blockContent.id).remove();
        },

        setupMarkers: function () {

            $('.eight-marker-start').each(function () {
                var $el = $(this);
                var next = $el.next();

                // cycle sibling till the end marker is found
                while (!next.hasClass('eight-marker-end')) {

                    if (next.prop("tagName") !== 'SCRIPT') {

                        var variables = $el.data('add-variables');

                        for (var i in variables) {

                            next.attr('data-' + i, variables[i]);
                        }

                        next.attr('data-editor-content', JSON.stringify($el.data('add-content')));

                        if ($el.data('add-classes') !== '') {
                            var classes = $el.data('add-classes').split(" ");
                            for (var i in classes) {
                                next.addClass(classes[i]);
                            }
                        }
                    }

                    next = next.next();
                }

                $el.remove();
            });

            $('.eight-marker-end').remove();

            $('.eight-list-marker').each(function () {
                var $el = $(this);

                var parent = $el.parent();
                var variables = $el.data('add-variables');

                parent.attr('data-variables', JSON.stringify(variables));
                parent.attr('data-editor-content', JSON.stringify($el.data('add-content')));
                parent.attr('data-subject-class', $el.data('subject-class'));

                if ($el.data('add-classes') !== '') {
                    var classes = $el.data('add-classes').split(" ");
                    for (var i in classes) {
                        parent.addClass(classes[i]);
                    }
                }

                var label;
                if ($el.data('subject-class').indexOf('Block') !== -1) {
                    label = variables['subject-label'];
                } else {
                    label = 'Main Page';
                }

                $('<a/>', {
                        "href": "javascript:;",
                        "class": "add-item",
                        "html": '<span class="glyphicon glyphicon-plus"></span>Add to ' + label + ' (pos: ' + variables['slot-label'] + ')'
                    })
                    .insertAfter(parent)
                    ;

                $el.remove();
            });
        },

        updateBlocksOrder: function (container) {

            var ids = [];
            container.find('> .eight-block-decorator').each(function () {
                ids.push($(this).data('editor-content').id);
            });

            $.ajax({
                url: globalReorderUrl,
                data: {
                    ids: ids
                },
                success: function () {

                },
                error: function () {

                }
            });
        },

        selectWidget: function (el) {
            $('.single-block-button').removeClass('selected');
            $(el).addClass('selected');
        },

        addItemDialog: function (el) {

            var $el = $(el);
            var parent = $(el).prev();
            var variables = parent.data('variables');
            var content = parent.data('editor-content');

            $('.modal-title').html("Add new block to " + variables['subject-label'] + " at position " + variables['slot-label']);

            $('#add-block-modal .modal-footer .add-block-btn')
                .unbind('click')
                .click(function () {
                    $.ajax({
                        url: globalAppendUrl,
                        data: {
                            subject: parent.data('subject-class'),
                            id: content.id,
                            name: $('.single-block-button.selected').data('name'),
                            slot_label: variables['slot-label'],
                            page_id: variables['page-id']
                        },
                        success: function (response) {
                            parent.append(response.html);
                            $('body').append(response.form);
                            Editor.reload();
                            $('#add-block-modal').modal('hide');
                        },
                        error: function () {
                            $('#add-block-modal').modal('hide');
                            alert('Error');
                        }
                    })
                });

            $('#add-block-modal').modal('show');
        },

        edit: function (element) {
            var blockContent = $(element).data('editor-content');
            $('#edit-block-modal-form_' + blockContent.id).modal('show');
        },

        selectIconWindow: function (element) {
            var selectionWindow = $(element).parent().find('.icon-selection-list');

            if (!selectionWindow.hasClass('hide')) {
                selectionWindow.addClass('hide');
                return;
            }

            selectionWindow.css({
                top: $(element).position().top,
                left: $(element).position().left + 160,
            });

            selectionWindow.removeClass('hide');
        },

        selectIcon: function (element) {
            $($(element).data('target')).val($(element).data('value'));

            $(element).parents('.select-icon-container').find('.icon-preview')
                .removeClass (function (index, css) {
                    return (css.match (/(^|\s)fa[\-]?\S+/g) || []).join(' ');
                });
            $(element).parents('.select-icon-container').find('.icon-preview').addClass($(element).data('value'));

            var selectionWindow = $(element).parents('.select-icon-container').find('.icon-selection-list');

            selectionWindow.addClass('hide');
        },

        closeIconWindow: function (element) {
            var selectionWindow = $(element).parents('.select-icon-container').find('.icon-selection-list');

            selectionWindow.addClass('hide');
        }
    };

    return api;
}());

$(function () {
    Editor.init();
});