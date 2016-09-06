var Editor = (function () {
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

            $('.eight-block-decorator')
                .mouseenter(function () {
                    Editor.showFrame($(this));
                })
                .mouseleave(function () {
                    Editor.hideFrame($(this));
                })
                ;

            $('.add-item')
                .click(function (event) {
                    Editor.addItemDialog(event.currentTarget);
                });

            $('[data-toggle="tooltip"]').tooltip({
                placement: 'bottom'
            });

            $('.eight-page-textarea').each(function () {
                CKEDITOR.replace($(this).attr('id'));
            });

        },

        showFrame: function (element) {

            var blockContent = $(element).data('editor-content');

            // upper left
            $('<div/>', { "class": "eight-frame-corner eight-frame-element eight-frame-element-" + blockContent.id })
                .css({
                    left: element.offset().left - 20,
                    top: element.offset().top - 20
                })
                .appendTo($('body'));

            // upper right
            $('<div/>', { "class": "eight-frame-corner eight-frame-element eight-frame-element-" + blockContent.id })
                .css({
                    right: $(window).width() - element.offset().left - element.outerWidth() - 20,
                    top: element.offset().top - 20
                })
                .appendTo($('body'));

            // lower left
            $('<div/>', { "class": "eight-frame-corner eight-frame-element eight-frame-element-" + blockContent.id })
                .css({
                    left: element.offset().left - 20,
                    top: element.offset().top + element.outerHeight()
                })
                .appendTo($('body'));

            // lower right
            $('<div/>', { "class": "eight-frame-corner eight-frame-element eight-frame-element-" + blockContent.id })
                .css({
                    right: $(window).width() - element.offset().left - element.outerWidth() - 20,
                    top: element.offset().top + element.outerHeight()
                })
                .appendTo($('body'))
                ;

            // header
            $('<div/>', { "class": "eight-frame-title eight-frame-element eight-frame-element-" + blockContent.id})
                .css({
                    left: element.offset().left,
                    top: element.offset().top - 20,
                    width: element.width()
                })
                .html(element.data('widget-label'))
                .appendTo('body')
                ;

            // toolbar
            var template = $($('#toolbar-template').html()).clone();

            template.find('.eight-toolbar')
                // .css({
                //     right: $(window).width() - element.offset().left - element.outerWidth(),
                //     top: element.offset().top
                // })
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
                                window.location.reload();
                            },
                            error: function () {
                                alert('error');
                            }
                        });
                    }
                });


            template.find('.btn-disable-block')
                .click(function (event) {
                    if (confirm("Disable block '" + element.data('widget-label') + "' ?")) {
                        $.ajax({
                            url: globalDisableUrl,
                            data: {
                                block_id: blockContent.id
                            },
                            success: function () {
                                window.location.reload();
                            },
                            error: function () {
                                alert('error');
                            }
                        });
                    }
                });

            template.find('.btn-enable-block')
                .click(function (event) {
                    if (confirm("Enable block '" + element.data('widget-label') + "' ?")) {
                        $.ajax({
                            url: globalEnableUrl,
                            data: {
                                block_id: blockContent.id
                            },
                            success: function () {
                                window.location.reload();
                            },
                            error: function () {
                                alert('error');
                            }
                        });
                    }
                });

            if (blockContent.enabled) {
                element.addClass('block-enabled');
            } else {
                element.addClass('block-disabled');
            }


            $('.eight-list-decorator')
                .sortable({
                    handle: ".btn-sort-block",
                    stop: function (event, ui) {
                        Editor.updateBlocksOrder(ui.item.parent());
                    }
                });

            $(template).find('.eight-toolbar').appendTo(element);
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

                $('<a/>', {
                        "href": "javascript:;",
                        "class": "add-item",
                        "html": '<span class="glyphicon glyphicon-plus"></span>Add to ' + variables['title'] + ' (pos: ' + variables['subject-label'] + ')'
                    })
                    .appendTo(parent)
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

        addItemDialog: function (el) {

            var $el = $(el);
            var variables = $(el).parent().data('variables');
            var content = $(el).parent().data('editor-content');

            $('.modal-title').html("Add new block to " + variables['title'] + " at position " + variables['subject-label']);

            $('#add-block-modal .modal-footer .add-block-btn')
                .unbind('click')
                .click(function () {
                    $.ajax({
                        url: globalAppendUrl,
                        data: {
                            subject: $el.parent().data('subject-class'),
                            id: content.id,
                            name: $('.modal-body select').find('option:selected').val(),
                            template: $('.modal-body select').val(),
                            label: variables['title']
                        },
                        success: function () {
                            window.location.reload();
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
        }
    };

    return api;
}());

$(function () {
    Editor.init();
});