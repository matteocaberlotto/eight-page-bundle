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
            Editor.repositionAddButtons();
            Editor.reloadPlugins();
        },

        confirmation: function (message, callback) {
            $('#confirmation-modal').find('.modal-body .confirmation').html(message);

            $('#confirmation-modal').find('.btn-confirm').unbind().click(function () {

                $('#confirmation-modal').modal('hide');

                if (typeof callback === 'function') {
                    callback();
                }

                if (window.hasOwnProperty('vueApp') && vueApp.hasOwnProperty(callback)) {
                    vueApp[callback]();
                }
            });

            $('#confirmation-modal').modal('show');
        },

        loader: function (dismiss) {
            if (typeof dismiss !== 'undefined') {
                $('#loading').addClass('d-none');
            } else {
                $('#loading').removeClass('d-none');
            }
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
                    var _curr_id = $(this).attr('id');

                    tinyMCE.init({
                        height: 300,
                        selector: '#' + _curr_id,
                        auto_focus: _curr_id,
                        theme: 'modern',
                        plugins: 'print preview searchreplace autolink directionality visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists textcolor wordcount imagetools contextmenu colorpicker textpattern help',
                        toolbar: 'formatselect | bold italic strikethrough forecolor backcolor | link | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat'
                    });
                })
                .addClass('eight-rich-editor-bound')
            ;

            $('[data-toggle="tooltip"]').tooltip({
                placement: 'bottom'
            });

            $('.eight-block-modal form button').click(function () {
                $(this).parents('form').find('button').removeClass('clicked');
                $(this).addClass('clicked');
            });

            $('.eight-block-modal form:not(.eight-ui-bound)')
                .submit(function (e) {
                    e.preventDefault();

                    var url = $(this).attr('action');
                    if ($(this).find('.submit-enable-block-edit').hasClass('clicked')) {
                        url += '&enable=true';
                    }

                    var form = $(this);
                    var parent = form.parents('.eight-block-modal').eq(0);
                    var block_selector = '.eight-block-' + parent.data('block-id');
                    var ref = $(block_selector);

                    Editor.loader();

                    $.ajax({
                        type: 'POST',
                        url: url,
                        data: new FormData(form[0]),
                        processData: false,
                        contentType: false,
                        success: function (rData) {
                            Editor.loader(true);

                            form.parents('.eight-block-modal').modal('hide');
                            $(rData.html).insertAfter(ref);
                            $('body').append(rData.form);
                            form.find('.eight-rich-editor-bound').each(function () {
                                var _curr_id = $(this).attr('id');
                                tinymce.get(_curr_id).remove();
                            });

                            ref.remove();
                            parent.remove();

                            setTimeout(function () {
                                Editor.reload();
                            }, 100);
                        },
                        error: function () {
                            Editor.loader(true);
                            alert("error");
                        }
                    });

                    return false;
                })
                .addClass('eight-ui-bound')
                ;

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
                    Editor.confirmation("Delete block '" + element.data('widget-label') + "' ?", function () {
                        Editor.loader();
                        $.ajax({
                            url: globalRemoveUrl,
                            data: {
                                block_id: blockContent.id,
                                page_id: element.data('page-id')
                            },
                            success: function () {
                                Editor.loader(true);
                                Editor.hideFrame(element);
                                element.remove();
                                Editor.reload();
                            },
                            error: function () {
                                Editor.loader(true);
                                alert('error');
                            }
                        });
                    });
                });


            if (blockContent.enabled) {
                element.addClass('block-enabled');

                template.find('.btn-disable-block')
                    .click(function (event) {
                        Editor.confirmation("Disable block '" + element.data('widget-label') + "' ?", function () {
                            Editor.loader();
                            $.ajax({
                                url: globalDisableUrl,
                                data: {
                                    block_id: blockContent.id,
                                    page_id: element.data('page-id')
                                },
                                success: function (response) {
                                    Editor.loader(true);

                                    $(response.html).insertAfter(element);
                                    Editor.hideFrame(element);
                                    element.remove();

                                    Editor.reload();
                                },
                                error: function () {
                                    Editor.loader(true);
                                    alert('error');
                                }
                            });
                        });
                    });

                template.find('.btn-enable-block').remove();
            } else {
                element.addClass('block-disabled');

                template.find('.btn-enable-block')
                    .click(function (event) {
                        Editor.confirmation("Enable block '" + element.data('widget-label') + "' ?", function (){
                            Editor.loader();
                            $.ajax({
                                url: globalEnableUrl,
                                data: {
                                    block_id: blockContent.id,
                                    page_id: element.data('page-id')
                                },
                                success: function (response) {
                                    Editor.loader(true);
                                    $(response.html).insertAfter(element);
                                    Editor.hideFrame(element);
                                    element.remove();

                                    Editor.reload();
                                },
                                error: function () {
                                    Editor.loader(true);
                                    alert('error');
                                }
                            });
                        });
                    });

                template.find('.btn-disable-block').remove();
            }

            $(template).find('.eight-toolbar').appendTo(element);
        },

        showFrame: function (element) {

            var blockContent = $(element).data('editor-content');

            if ($('.eight-frame-element-' + blockContent.id).length) {
                return;
            }

            var minWidth = element.outerWidth();

            if (minWidth < 120) {
                minWidth = 120;
            }

            // header
            $('<div/>', { "class": "eight-frame-title " + (blockContent.static ? 'eight-static-frame-title' : '') + " eight-frame-element eight-frame-element-" + blockContent.id})
                .css({
                    left: element.offset().left,
                    top: element.offset().top - 26,
                    maxWidth: minWidth
                })
                .html('<span class="' + element.data('widget-icon') + '"></span> ' + element.data('widget-label') + ' (id:' + blockContent.id + ')')
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
                    right: $(window).width() - element.offset().left - minWidth,
                    top: element.offset().top,
                    height: element.outerHeight()
                })
                .appendTo($('body'));

            // top
            $('<div/>', { "class": "eight-frame-bottom eight-frame-element eight-frame-element-" + blockContent.id })
                .css({
                    left: element.offset().left,
                    top: element.offset().top,
                    width: minWidth
                })
                .appendTo($('body'));

            // bottom
            $('<div/>', { "class": "eight-frame-bottom eight-frame-element eight-frame-element-" + blockContent.id })
                .css({
                    left: element.offset().left,
                    top: element.offset().top + element.outerHeight(),
                    width: minWidth
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
                        var parent = ui.item.parents('.eight-list-decorator').first();
                        Editor.updateBlocksOrder(parent);

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

                    // do not alter <script> tags
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

                        if ($el.data('add-content').block_id) {
                            next.addClass('eight-block-children-' + $el.data('add-content').block_id + '-' + $el.data('add-content')['type']);
                        } else {
                            next.addClass('eight-block-children-page-' + ($el.data('add-content').page_id ? $el.data('add-content').page_id : 'static') + '-' + $el.data('add-content')['type']);
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

                if (variables['is_static']) {
                    var class_name = "add-item add-item-static";
                    var html_content = ' ' + variables['slot-label'];
                } else {
                    var class_name = "add-item";
                    var html_content = ' ' + variables['slot-label'];
                }

                var addNewItem = $('<a/>', {
                        "href": "javascript:;",
                        "class": class_name,
                        "html": '<span class="fa fa-cubes"></span>' + html_content
                    })
                    ;

                addNewItem.insertAfter(parent);

                if (globalUpdateEightAddButtonsPosition) {
                    Editor.repositionAddButtons();
                }

                $el.remove();
            });

            if (globalUpdateEightAddButtonsPosition) {
                $('.add-item').css('position', 'absolute');
            }
        },

        repositionAddButtons: function () {
            if (globalUpdateEightAddButtonsPosition) {
                setTimeout(function () {

                    $('.add-item').each(function () {

                        if ($(this).prev().hasClass('dropdown-menu')) {
                            var top = $(this).prev().prev().position().top + $(this).prev().prev().outerHeight();
                            var left = $(this).prev().prev().position().left;
                        } else {
                            var top = $(this).prev().position().top + $(this).prev().outerHeight();
                            var left = $(this).prev().position().left;
                            $(this).parent().addClass('toolkit-relative');
                        }

                        $(this).css({
                            position: 'absolute',
                            top: top,
                            left: left
                        });
                    });
                }, 1000);
            }
        },

        updateBlocksOrder: function (container) {

            var editorContent = container.data('editor-content');
            var variables = container.data('variables');

            var ids = [];

            var childrenSelector;

            if (container.data('subject-class') === 'Eight\\PageBundle\\Entity\\Block') {
                // the container is a block
                childrenSelector = '.eight-block-children-' + editorContent.id + '-' + variables['slot-label'];
            } else {
                // the container is the page
                if (variables.is_static) {
                    // this is a static slot
                    childrenSelector = '.eight-block-children-page-static-' + variables['slot-label'];
                } else {
                    childrenSelector = '.eight-block-children-page-' + variables['page-id'] + '-' + variables['slot-label'];
                }
            }

            container.find(childrenSelector).each(function () {
                ids.push($(this).data('editor-content').id);
            });


            $.ajax({
                url: globalReorderUrl,
                data: {
                    ids: ids,
                    page_id: editorContent.page_id
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
                    Editor.loader();
                    $.ajax({
                        url: globalAppendUrl,
                        data: {
                            subject: parent.data('subject-class'),
                            id: content.id,
                            name: $('.single-block-button.selected').data('name'),
                            slot_label: variables['slot-label'],
                            page_id: variables['page-id'],
                            is_static: variables['is_static']
                        },
                        success: function (response) {
                            Editor.loader(true);
                            $('#add-block-modal').modal('hide');
                            parent.append(response.html);
                            $('body').append(response.form);
                            Editor.reload();
                        },
                        error: function () {
                            Editor.loader(true);
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

            if (!selectionWindow.hasClass('d-none')) {
                selectionWindow.addClass('d-none');
                return;
            }

            selectionWindow.css({
                top: $(element).position().top,
                left: $(element).position().left + 160,
            });

            selectionWindow.removeClass('d-none');
        },

        selectIcon: function (element) {
            $($(element).data('target')).val($(element).data('value'));

            $(element).parents('.select-icon-container').find('.icon-preview')
                .removeClass (function (index, css) {
                    return (css.match (/(^|\s)fa[\-]?\S+/g) || []).join(' ');
                });
            $(element).parents('.select-icon-container').find('.icon-preview').addClass($(element).data('value'));

            var selectionWindow = $(element).parents('.select-icon-container').find('.icon-selection-list');

            selectionWindow.addClass('d-none');
        },

        closeIconWindow: function (element) {
            var selectionWindow = $(element).parents('.select-icon-container').find('.icon-selection-list');

            selectionWindow.addClass('d-none');
        }
    };

    return api;
}());

$(function () {
    Editor.init();
});

// https://stackoverflow.com/questions/18111582/tinymce-4-links-plugin-modal-in-not-editable
$(document).on('focusin', function(e) {
    if ($(e.target).closest(".mce-window").length) {
        e.stopImmediatePropagation();
    }
});