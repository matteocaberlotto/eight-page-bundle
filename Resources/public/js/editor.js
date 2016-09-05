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

            $('.add-item')
                .click(function (event) {
                    Editor.addItemDialog(event.currentTarget);
                });

            $('.btn-remove-block')
                .click(function (event) {
                    $el = $(event.currentTarget);
                    if (confirm("Rimuovere blocco '" + $el.parent().data('name') + "' ?")) {
                        $.ajax({
                            url: globalRemoveUrl,
                            data: {
                                block_id: $el.parent().data('subject-id')
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

            $('.btn-enable-block')
                .click(function (event) {
                    $el = $(event.currentTarget);
                    if (confirm("Abilitare blocco '" + $el.parent().data('block-label') + "' ?")) {
                        $.ajax({
                            url: globalEnableUrl,
                            data: {
                                block_id: $el.parent().data('subject-id')
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

            $('.btn-disable-block')
                .click(function (event) {
                    $el = $(event.currentTarget);
                    if (confirm("Disabilitare blocco '" + $el.parent().data('block-label') + "' ?")) {
                        $.ajax({
                            url: globalDisableUrl,
                            data: {
                                block_id: $el.parent().data('subject-id')
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

            $('.block-container')
                .sortable({
                    handle: ".btn-sort-block",
                    stop: function (event, ui) {
                        Editor.updateBlocksOrder(ui.item.parent());
                    }
                });

            $('[data-toggle="tooltip"]').tooltip({
                placement: 'bottom'
            });

            $('.eight-page-textarea').each(function () {
                CKEDITOR.replace($(this).attr('id'));
            });

        },

        updateBlocksOrder: function (container) {

            var ids = [];
            container.find('> .block-wrapper').each(function () {
                ids.push($(this).data('subject-id'));
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

            $('.modal-title').html("Add new block to " + $el.parent().data('title') + " at position " + $el.parent().data('label'));

            $('#add-block-modal .modal-footer .add-block-btn')
                .unbind('click')
                .click(function () {
                    $.ajax({
                        url: globalAppendUrl,
                        data: {
                            subject: $el.parent().data('subject-class'),
                            id: $el.parent().data('subject-id'),
                            name: $('.modal-body select').find('option:selected').val(),
                            template: $('.modal-body select').val(),
                            label: $el.parent().data('label')
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

        edit: function (el) {
            $(el).parent().toggleClass('edit-content');
        }
    };

    return api;
}());

$(function () {
    Editor.init();
});