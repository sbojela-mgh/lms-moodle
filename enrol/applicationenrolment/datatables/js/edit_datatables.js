// Add the init function:
function notification_init() {
    // Init stuff ...
}
var now = new Date();
var mitigation_form_change = false;
$(document).ready(function () {
    require(['jquery', 'datatables/js/jquery.datetimepicker.js'], function ($, datetimepicker) {
        $.datetimepicker.setDateFormatter({
            parseDate: function (date, format) {
                var fmt = new DateFormatter();
                return fmt.parseDate(date, format)
            },

            formatDate: function (date, format) {
                var fmt = new DateFormatter();
                return fmt.formatDate(date, format);
            },
        });

        // Prepare Message
        var mitigation_lang_message = JSON.parse($('#mitigation_lang_messages').attr('data-lang'));

        /******
     * Personal Dealine
     */

    $('.datatabletest').on('click', '.edit_personal_deadline',function (e) {
            e.preventDefault();
            var parent = $(this).closest('.personal_deadline_cell');
            parent.children('.show_personal_dealine').addClass('display-none');
            var change_form = parent.children('.show_edit_personal_dealine');
            var data_get = $(this).attr('data-get');
            var data_time = $(this).attr('data-time');
            if (change_form.html().length == 1) {
                var form = '<input type="text" class="form-control input-personal-deadline" value="' + data_time;
                form += '">';
                form += '<div class="personal_deadline_button"><a href="/" class="approve-personal-deadline" data-get=';
                form += data_get
                form += '><i class="fa fa-check" aria-hidden="true"></i></a>';
                form += '<a href="/" class="cancel-personal-deadline"><i class="fa fa-times" aria-hidden="true"></i></a></div>';
                $(change_form).html(form);




            }
            var time = $(change_form).children('.input-personal-deadline');
            $(time).datetimepicker({
                format: 'd-m-Y H:i',
                step: 5
            });
            $(change_form).removeClass('display-none');
        });

        $('.datatabletest').on('click', '.cancel-personal-deadline', function (e) {
            e.preventDefault();
            $(this).closest('.show_edit_personal_dealine').addClass('display-none');
            $(this).closest('.personal_deadline_cell').children('.show_personal_dealine').removeClass('display-none');
        });

        $('.datatabletest').on('click', '.approve-personal-deadline', function (e) {
            e.preventDefault();
            var deadline = $(this);
            var data_get = $(deadline).attr('data-get');

            var value = $(deadline).closest('.show_edit_personal_dealine').children('.input-personal-deadline').val();

            var fmt = new DateFormatter();
            var input_date = Date.parse(fmt.parseDate(value, "d-m-Y H:i"));

            if (input_date <= Date.parse(now)) {
                var notification = new M.core.alert({
                    message: mitigation_lang_message.alert_validate_deadline.replace(/\_/g, ' '),
                    title: mitigation_lang_message.notification_info,
                });
                notification.show();
                return 0;
            }

            var url = mitigation_lang_message.url_root + "/mod/coursework/actions/personal_deadline.php";
            var param = JSON.parse(data_get);
            param.personal_deadline_time = value;

            $.ajax({
                type: "POST",
                url: url,
                data: param,
                beforeSend: function () {
                    $('html, body').css("cursor", "wait");
                    $(self).prev('img').css('visibility', 'visible');
                },
                success: function (response) {
                    $('html, body').css("cursor", "auto");
                    data_response = JSON.parse(response);
                    if (data_response.error == 1) {
                        var notification = new M.core.alert({
                            message: data_response.message,
                            title: mitigation_lang_message.notification_info,
                        });
                        notification.show();
                    } else {
                        var parent = $(deadline).closest('.personal_deadline_cell');
                        $(parent).attr('data-order', data_response.timestamp);
                        var table = table_obj_list[Object.keys(table_obj_list)[0]];
                        table.row('#' + $(parent).closest('tr').attr('id')).invalidate();

                        $(parent).children('.show_personal_dealine').children('.content_personal_deadline').html(data_response.time);
                        $(parent).children('.show_edit_personal_dealine').addClass('display-none');
                        $(parent).children('.show_personal_dealine').removeClass('display-none');

                        var personaldeadline    =   $(parent).closest('.feedback_release_cell');

                        $(parent).siblings('.feedback_release_cell').find('.content_personal_feedback_deadline').html(data_response.personaldate);


                        var notification = new M.core.alert({
                            message: mitigation_lang_message.alert_personaldeadline_save_successful.replace(/\_/g, ' '),
                            title: mitigation_lang_message.notification_info,
                        });

                        notification.show();
                    }
                },
                error: function () {
                    $('html, body').css("cursor", "auto");
                },
                complete: function () {
                    $('html, body').css("cursor", "auto");
                }
            });

        });

        /***************************
         * Feedback Release
         */

        $('.datatabletest').on('click', '.edit_personal_feedback_deadline',function (e) {
            e.preventDefault();
            var parent = $(this).closest('.feedback_release_cell');
            parent.children('.show_personal_feedback_deadline').addClass('display-none');
            var change_form = parent.children('.show_edit_personal_feedback_deadline');
            var data_get = $(this).attr('data-get');
            var data_time = $(this).attr('data-time');
            if (change_form.html().length == 1) {
                var form = '<input type="text" class="form-control input-personal-feedback-deadline" value="' + data_time;
                form += '">';
                form += '<div class="personal_feedback_deadline_button"><a href="/" class="approve-personal-feedback-deadline" data-get=';
                form += data_get
                form += '><i class="fa fa-check" aria-hidden="true"></i></a>';
                form += '<a href="/" class="cancel-personal-feedback-deadline"><i class="fa fa-times" aria-hidden="true"></i></a></div>';
                $(change_form).html(form);
            }
            var time = $(change_form).children('.input-personal-feedback-deadline');
            $(time).datetimepicker({
                format: 'd-m-Y H:i',
                step: 5
            });
            $(change_form).removeClass('display-none');
        });

        $('.datatabletest').on('click', '.cancel-personal-feedback-deadline', function (e) {
            e.preventDefault();
            $(this).closest('.show_edit_personal_feedback_deadline').addClass('display-none');
            $(this).closest('.feedback_release_cell').children('.show_personal_feedback_deadline').removeClass('display-none');
        });

        $('.datatabletest').on('click', '.approve-personal-feedback-deadline', function (e) {
            e.preventDefault();
            var feedback_deadline = $(this);
            var data_get = $(feedback_deadline).attr('data-get');

            var value = $(feedback_deadline).closest('.show_edit_personal_feedback_deadline').children('.input-personal-feedback-deadline').val();

            var fmt = new DateFormatter();
            var input_date = Date.parse(fmt.parseDate(value, "d-m-Y H:i"));

            if (input_date <= Date.parse(now)) {
                var notification = new M.core.alert({
                    message: mitigation_lang_message.alert_validate_personal_feedback_deadline.replace(/\_/g, ' '),
                    title: mitigation_lang_message.notification_info,
                });
                notification.show();
                return 0;
            }

            var url = '/mod/coursework/actions/personal_feedback_deadline.php';
            var param = JSON.parse(data_get);
            param.personal_feedback_deadline_time = value;
            param.inlineedit = 'true';

            $.ajax({
                type: "POST",
                url: url,
                data: param,
                beforeSend: function () {
                    $('html, body').css("cursor", "wait");
                    $(self).prev('img').css('visibility', 'visible');
                },
                success: function (response) {
                    $('html, body').css("cursor", "auto");
                    data_response = JSON.parse(response);
                    if (data_response.error == 1) {
                        var notification = new M.core.alert({
                            message: data_response.message,
                            title: mitigation_lang_message.notification_info,
                        });
                        notification.show();
                    } else {
                        var parent = $(feedback_deadline).closest('.feedback_release_cell');
                        $(parent).attr('data-order', data_response.timestamp);
                        var table = table_obj_list[Object.keys(table_obj_list)[0]];
                        table.row('#' + $(parent).closest('tr').attr('id')).invalidate();

                        $(parent).children('.show_personal_feedback_deadline').children('.content_personal_feedback_deadline').html(data_response.time);
                        $(parent).children('.show_edit_personal_feedback_deadline').addClass('display-none');
                        $(parent).children('.show_personal_feedback_deadline').removeClass('display-none');
                        var notification = new M.core.alert({
                            message: mitigation_lang_message.alert_personalfeedbackdeadline_save_successful.replace(/\_/g, ' '),
                            title: mitigation_lang_message.notification_info,
                        });
                        notification.show();
                    }
                },
                error: function () {
                    $('html, body').css("cursor", "auto");
                },
                complete: function () {
                    $('html, body').css("cursor", "auto");
                }
            });

        });

        /***************************
         * Mitigation
         */

        /**
         * Add new mitigation
         */
        $('.datatabletest').on('click', '.new_mitigation', function (e) {
            e.preventDefault();
            var data_name = $(this).attr('data-name');
            var data_params = JSON.parse($(this).attr('data-params'));
            var data_time = JSON.parse($(this).attr('data-time'));
            var current_rowid = $(this).closest('tr').attr('id');
            mitigatiion_new_change_data_form(data_name, data_params, data_time, current_rowid);
            $('#modal-ajax').modal('show');
        });

        /**
         * Edit mitigation
         */
        $('.datatabletest').on('click', '.edit_mitigation', function (e) {
            e.preventDefault();
            var data_name = $(this).attr('data-name');
            var data_params = JSON.parse($(this).attr('data-params'));
            var data_time = JSON.parse($(this).attr('data-time'));
            var current_rowid = $(this).closest('tr').attr('id');
            mitigatiion_edit_change_data_form(data_name, data_params, data_time, current_rowid);
            $('#modal-ajax').modal('show');
        });

        /**
         * Submit save mitigation
         */
        $('.modal-footer').on('click', '#mitigation-submit', function (e) {
            e.preventDefault();
            var params = {};
            params.allocatabletype = $('#mitigation-allocatabletype').val();
            params.allocatableid = $('#mitigation-allocatableid').val();
            params.courseworkid = $('#mitigation-courseworkid').val();
            params.id = $('#mitigation-id').val();
            params.extended_deadline = $('#mitigration-extend-deadline').val();
            params.type = $('#mitigation-select').val();
            params.editor = $('#mitigration-time-content').html();
            params.text = $('#id_extra_information').val();
            params.submissionid = $('#mitigation-submissionid').val();
            params.name = $('#mitigation-name').val();
            params.pre_defined_reason = $('#mitigation-extension-reason-select').val();
            params.requesttype = 'submit';
            current_rowid = $('#button-id').val();
            var url = mitigation_lang_message.url_root;
            $.ajax({
                type: "POST",
                url: url + "/mod/coursework/actions/ajax/mitigation/submit.php",
                data: params,
                beforeSend: function () {
                    $('html, body').css("cursor", "wait");
                    $('.modal-footer').children('img').css('visibility', 'visible');
                },
                success: function (response) {
                    var data_response = JSON.parse(response);
                    $('html, body').css("cursor", "auto");
                    $('.modal-footer').children('img').css('visibility', 'hidden');
                    if (data_response.error == 1) {
                        var notification = new M.core.alert({
                            message: data_response.messages,
                            title: mitigation_lang_message.notification_info,
                        });
                        notification.show();
                    } else {
                        if (Object.keys(table_obj_list).length > 0) {
                            // Get the first datatable object.
                            var table = table_obj_list[Object.keys(table_obj_list)[0]];
                            var current_row_index = table.row('#' + current_rowid).index();
                            var submissiondateindex = table.column('.tableheaddate').index();
                            var current_moderation_cell_data = data_response.content;
                            $('#' + current_rowid + ' .time_submitted_cell').attr('data-order', current_moderation_cell_data['@data-order']);

                            table.cell({row:current_row_index, column:submissiondateindex}).data(current_moderation_cell_data.display);
                            table.cell({row:current_row_index, column:submissiondateindex}).invalidate();

                           if(params.type == 'extension') {
                                if(!$('#'+current_rowid).find('.edit_personal_deadline').hasClass('display-none')) {
                                    $('#'+current_rowid).find('.edit_personal_deadline').addClass('display-none');
                                }
                                if(!$('#'+current_rowid).find('.show_edit_personal_dealine').hasClass('display-none')) {
                                    $('#'+current_rowid).find('.show_edit_personal_dealine').addClass('display-none');
                                }
                                if($('#'+current_rowid).find('.show_personal_dealine').hasClass('display-none')) {
                                    $('#'+current_rowid).find('.show_personal_dealine').removeClass('display-none');
                                }

                                $('#' + current_rowid).find('.feedback_release_cell').find('.content_personal_feedback_deadline').html(data_response.personaldate);


                            }
                            if(params.type != 'extension' && $('#'+current_rowid).find('.edit_personal_deadline').hasClass('display-none')) {
                                $('#'+current_rowid).find('.edit_personal_deadline').removeClass('display-none');
                            }
                            if(params.type == 'permanent') {
                                $('#'+current_rowid).find('.submission_cell').html('');
                                $('#'+current_rowid).find('.edit_personal_feedback_deadline').addClass('display-none');
                            }
                            $('#mitigation-id').val(data_response.data.id);
                        }

                        change__status_mitigation_submit_button(true);
                        save_mitigation_form_data();
                        var notification = new M.core.alert({
                            message: mitigation_lang_message.alert_mitigation_save_successful.replace(/\_/g, ' '),
                            title: mitigation_lang_message.notification_info,
                        });
                        notification.show();
                        initialise_personal_feedback_deadline_locks();


                    }
                },
                error: function () {
                    $('html, body').css("cursor", "auto");
                },
                complete: function () {
                    $('html, body').css("cursor", "auto");
                }
            });
        });

        function disable_button_form_mitigation(type) {
            if (type != 'extension') {
                $('#id_extra_information').prop('disabled', true);
                $('#mitigration-extend-deadline').prop('disabled', true);
                $('#mitigation-extension-reason-select').prop('disabled', true);
            } else {
                $('#id_extra_information').prop('disabled', false);
                $('#mitigration-extend-deadline').prop('disabled', false);
                $('#mitigation-extension-reason-select').prop('disabled', false);
            }
            change__status_mitigation_submit_button(false);
            $('#mitigation-back').prop('disabled',false);
            $('#mitigation-next').prop('disabled',false);
        }

        /**
         * Delete mitigration
         */
        $('.datatabletest').on('click', '.delete_mitigation', function (e) {
            e.preventDefault();
            var confirm = new M.core.confirm({
                title: mitigation_lang_message.notification_confirm_label,
                question: mitigation_lang_message.alert_mitigation_confirm_delete.replace(/\_/g, ' '),
                yesLabel: mitigation_lang_message.notification_yes_label,
                noLabel: mitigation_lang_message.notification_no_label,
            });
            var self = this;
            confirm.on('complete-yes', function() {
                confirm.hide();
                confirm.destroy();
                var name = $(self).attr('data-name');
                var data_params = JSON.parse($(self).attr('data-params'));
                data_params.requesttype = 'delete';
                data_params.name = name;
                var current_rowid = $(self).closest('tr').attr('id');
                var url = mitigation_lang_message.url_root;
                $.ajax({
                    type: "POST",
                    url: url + "/mod/coursework/actions/ajax/mitigation/delete.php",
                    data: data_params,
                    success: function (response) {
                        response = $.parseJSON(response);
                        if (Object.keys(table_obj_list).length > 0) {
                            // Get the first datatable object.
                            var table = table_obj_list[Object.keys(table_obj_list)[0]];
                            var current_row_data = table.row('#' + current_rowid).data();
                            var submissiondateindex = table.column('.tableheaddate').index();
                            var current_moderation_cell_data = response;
                            current_row_data[submissiondateindex] = current_moderation_cell_data;
                            table.row('#' + current_rowid).data(current_row_data);
                            $('#' + current_rowid + ' .time_submitted_cell').attr('data-order', current_moderation_cell_data['@data-order']);
                            table.row('#' + current_rowid).invalidate();
                        }
                        if($('#'+current_rowid).find('.edit_personal_deadline').hasClass('display-none')) {
                            $('#'+current_rowid).find('.edit_personal_deadline').removeClass('display-none');
                        }
                        var notification = new M.core.alert({
                            message: mitigation_lang_message.alert_mitigation_delete_successful.replace(/\_/g, ' '),
                            title: mitigation_lang_message.notification_info,
                        });
                        notification.show();

                        initialise_personal_feedback_deadline_locks();

                    }
                });
            });
            confirm.on('complete-no',function() {
                confirm.hide();
                confirm.destroy();
            });
            confirm.show();
        });

         /**
         * Function close button
         */
        $('#modal-ajax').on('hide.bs.modal', function (e) {
            var self = this;
            if(is_data_mitigation_form_change()) {
                var confirm = new M.core.confirm({
                    title: mitigation_lang_message.notification_leave_form_title.replace(/\_/g, ' '),
                    question: mitigation_lang_message.notification_leave_form_message.replace(/\_/g, ' '),
                    yesLabel: mitigation_lang_message.notification_yes_label,
                    noLabel: mitigation_lang_message.notification_no_label,
                });

                confirm.on('complete-yes',function() {
                    save_mitigation_form_data();
                    confirm.hide();
                    confirm.destroy();
                    $(self).modal('hide');
                });

                confirm.on('complete-no',function() {
                    confirm.hide();
                    confirm.destroy();
                    return false;
                });

                confirm.show();
                return false;
            }
            return true;
        });

        /**
         * Function next button
         */
        $('.modal-footer').on('click', '#mitigation-next', function (e) {
            e.preventDefault();

            if (is_data_mitigation_form_change()) {
                var confirm = new M.core.confirm({
                    title: mitigation_lang_message.notification_leave_form_title.replace(/\_/g, ' '),
                    question: mitigation_lang_message.notification_leave_form_message.replace(/\_/g, ' '),
                    yesLabel: mitigation_lang_message.notification_yes_label,
                    noLabel: mitigation_lang_message.notification_no_label,
                });

                confirm.on('complete-yes', function () {
                    confirm.hide();
                    confirm.destroy();
                    if (Object.keys(table_obj_list).length > 0) {

                        var self = $(this);
                        var prev_rowid = $('#button-id').val();

                        // Get the first datatable object.
                        var table = table_obj_list[Object.keys(table_obj_list)[0]];

                        var prev_row_index = table.row('#' + prev_rowid).index();


                        var current_row_index = prev_row_index + 1;

                        if (table.row(current_row_index)) {
                            var current_row_data = table.row(current_row_index).data();
                            if (current_row_data) {
                                var current_rowid = table.row(current_row_index).id();

                                var submissiondateindex = table.column('.tableheaddate').index();
                                var current_cell_data = current_row_data[submissiondateindex];
                                if (current_cell_data) {
                                    var tmp_node = $('<div/>').html(current_cell_data.display);
                                    var submisiondate = $(tmp_node).find('.new_mitigation');
                                    if (submisiondate.length > 0) {
                                        var data_params = JSON.parse(submisiondate.attr('data-params'));
                                        var data_name = submisiondate.attr('data-name');
                                        var data_time = JSON.parse(submisiondate.attr('data-time'));
                                        mitigatiion_new_change_data_form(data_name, data_params, data_time, current_rowid);
                                    } else {
                                        submisiondate = $(tmp_node).find('.edit_mitigation');
                                        var data_params = JSON.parse(submisiondate.attr('data-params'));
                                        var data_name = submisiondate.attr('data-name');
                                        var data_time = JSON.parse(submisiondate.attr('data-time'));
                                        mitigatiion_edit_change_data_form(data_name, data_params, data_time, current_rowid);
                                    }
                                }
                            }
                            else {
                                $('#mitigation-next').prop('disabled', true);
                                var notification = new M.core.alert({
                                    message: mitigation_lang_message.alert_no_mitigation.replace(/\_/g, ' '),
                                    title: mitigation_lang_message.notification_info,
                                });
                                notification.show();
                            }
                        }
                    }
                });

                confirm.on('complete-no', function () {
                    confirm.hide();
                    confirm.destroy();

                });

                confirm.show();
            } else {
                if (Object.keys(table_obj_list).length > 0) {

                    var self = $(this);
                    var prev_rowid = $('#button-id').val();

                    // Get the first datatable object.
                    var table = table_obj_list[Object.keys(table_obj_list)[0]];

                    var ordereddata = table.rows( { order: 'applied', search: 'applied' } ).data().toArray();
                    var prev_row_index = ordereddata.findIndex(compare_row, prev_rowid);


                    var current_row_index = prev_row_index + 1;

                    if (table.row(current_row_index)) {
                        var current_row_data = ordereddata[current_row_index];
                        if (typeof current_row_data != 'undefined') {
                            var current_rowid = current_row_data.DT_RowId;

                            var submissiondateindex = table.column('.tableheaddate').index();
                            var current_cell_data = current_row_data[submissiondateindex];
                            if (current_cell_data) {
                                var tmp_node = $('<div/>').html(current_cell_data.display);
                                var submisiondate = $(tmp_node).find('.new_mitigation');
                                if (submisiondate.length > 0) {
                                    var data_params = JSON.parse(submisiondate.attr('data-params'));
                                    var data_name = submisiondate.attr('data-name');
                                    var data_time = JSON.parse(submisiondate.attr('data-time'));
                                    mitigatiion_new_change_data_form(data_name, data_params, data_time, current_rowid);
                                } else {
                                    submisiondate = $(tmp_node).find('.edit_mitigation');
                                    var data_params = JSON.parse(submisiondate.attr('data-params'));
                                    var data_name = submisiondate.attr('data-name');
                                    var data_time = JSON.parse(submisiondate.attr('data-time'));
                                    mitigatiion_edit_change_data_form(data_name, data_params, data_time, current_rowid);
                                }
                            }
                        }
                        else {
                            $('#mitigation-next').prop('disabled', true);
                            var notification = new M.core.alert({
                                message: mitigation_lang_message.alert_no_mitigation.replace(/\_/g, ' '),
                                title: mitigation_lang_message.notification_info,
                            });
                            notification.show();
                        }
                    }
                }
            }


        });

        /**
         * Function back button
         */
        $('.modal-footer').on('click', '#mitigation-back', function (e) {
            e.preventDefault();
            if (is_data_mitigation_form_change()) {
                var confirm = new M.core.confirm({
                    title: mitigation_lang_message.notification_leave_form_title.replace(/\_/g, ' '),
                    question: mitigation_lang_message.notification_leave_form_message.replace(/\_/g, ' '),
                    yesLabel: mitigation_lang_message.notification_yes_label,
                    noLabel: mitigation_lang_message.notification_no_label,
                });

                confirm.on('complete-yes', function () {
                    confirm.hide();
                    confirm.destroy();
                    if (Object.keys(table_obj_list).length > 0) {

                        var self = $(this);
                        var prev_rowid = $('#button-id').val();

                        // Get the first datatable object.
                        var table = table_obj_list[Object.keys(table_obj_list)[0]];

                        var prev_row_index = table.row('#' + prev_rowid).index();


                        var current_row_index = prev_row_index - 1;

                        if (table.row(current_row_index)) {
                            var current_row_data = table.row(current_row_index).data();
                            if (current_row_data) {
                                var current_rowid = table.row(current_row_index).id();

                                var submissiondateindex = table.column('.tableheaddate').index();
                                var current_cell_data = current_row_data[submissiondateindex];
                                if (current_cell_data) {
                                    var tmp_node = $('<div/>').html(current_cell_data.display);
                                    var submisiondate = $(tmp_node).find('.new_mitigation');
                                    if (submisiondate.length > 0) {
                                        var data_params = JSON.parse(submisiondate.attr('data-params'));
                                        var data_name = submisiondate.attr('data-name');
                                        var data_time = JSON.parse(submisiondate.attr('data-time'));
                                        mitigatiion_new_change_data_form(data_name, data_params, data_time, current_rowid);
                                    } else {
                                        submisiondate = $(tmp_node).find('.edit_mitigation');
                                        var data_params = JSON.parse(submisiondate.attr('data-params'));
                                        var data_name = submisiondate.attr('data-name');
                                        var data_time = JSON.parse(submisiondate.attr('data-time'));
                                        mitigatiion_edit_change_data_form(data_name, data_params, data_time, current_rowid);
                                    }
                                }
                            }
                            else {
                                $('#mitigation-back').prop('disabled', true);
                                var notification = new M.core.alert({
                                    message: mitigation_lang_message.alert_no_mitigation.replace(/\_/g, ' '),
                                    title: mitigation_lang_message.notification_info,
                                });
                                notification.show();
                            }
                        }
                    }
                });

                confirm.on('complete-no', function () {
                    confirm.hide();
                    confirm.destroy();
                });

                confirm.show();
            } else {
                if (Object.keys(table_obj_list).length > 0) {

                    var self = $(this);
                    var prev_rowid = $('#button-id').val();

                    // Get the first datatable object.
                    var table = table_obj_list[Object.keys(table_obj_list)[0]];

                    var ordereddata = table.rows( { order: 'applied', search: 'applied' } ).data().toArray();
                    var prev_row_index = ordereddata.findIndex(compare_row, prev_rowid);


                    var current_row_index = prev_row_index - 1;

                    if (table.row(current_row_index)) {
                        var current_row_data = ordereddata[current_row_index];
                        if (typeof current_row_data != 'undefined') {
                            var current_rowid = current_row_data.DT_RowId;

                            var submissiondateindex = table.column('.tableheaddate').index();
                            var current_cell_data = current_row_data[submissiondateindex];
                            if (current_cell_data) {
                                var tmp_node = $('<div/>').html(current_cell_data.display);
                                var submisiondate = $(tmp_node).find('.new_mitigation');
                                if (submisiondate.length > 0) {
                                    var data_params = JSON.parse(submisiondate.attr('data-params'));
                                    var data_name = submisiondate.attr('data-name');
                                    var data_time = JSON.parse(submisiondate.attr('data-time'));
                                    mitigatiion_new_change_data_form(data_name, data_params, data_time, current_rowid);
                                } else {
                                    submisiondate = $(tmp_node).find('.edit_mitigation');
                                    var data_params = JSON.parse(submisiondate.attr('data-params'));
                                    var data_name = submisiondate.attr('data-name');
                                    var data_time = JSON.parse(submisiondate.attr('data-time'));
                                    mitigatiion_edit_change_data_form(data_name, data_params, data_time, current_rowid);
                                }
                            }
                        }
                        else {
                            $('#mitigation-back').prop('disabled', true);
                            var notification = new M.core.alert({
                                message: mitigation_lang_message.alert_no_mitigation.replace(/\_/g, ' '),
                                title: mitigation_lang_message.notification_info,
                            });
                            notification.show();
                        }
                    }
                }
            }


        });

        function mitigatiion_edit_change_data_form(data_name, data_params, data_time, current_rowid) {
            var title = 'Editing the mitigation for ' + data_name;
            var time_content = 'Default deadline: ' + data_time.time_content;
            $('#mitigration-modal-title').html(title);
            $('#form-mitigation').find('input[type=hidden]').val("");
            $('#form-mitigation').find('textarea').val("");
            $('#button-id').val(current_rowid);
            $('#mitigation-submissionid').val(data_params.submissionid);
            $('#mitigation-name').val(data_name);
            data_params.requesttype = 'edit';
            var url = mitigation_lang_message.url_root;
            $.ajax({
                type: "GET",
                url: url + "/mod/coursework/actions/ajax/mitigation/edit.php",
                data: data_params,
                beforeSend: function () {
                    change__status_mitigation_submit_button(true);
                    $('html, body').css("cursor", "wait");
                    $('.modal-footer').children('img').css('visibility', 'visible');
                },
                success: function (response) {
                    var data_response = JSON.parse(response);
                    $('html, body').css("cursor", "auto");
                    $('.modal-footer').children('img').css('visibility', 'hidden');
                    if (data_response.error == 1) {
                        var notification = new M.core.alert({
                            message: data_response.message + ' .Please reload the page!',
                            title: mitigation_lang_message.notification_info,
                        });
                        notification.show();
                    } else {
                        var mitigation = data_response.data;
                        if (mitigation.time_content) {
                            $('#mitigration-time-content').html(mitigation.time_content);
                        } else {
                            $('#mitigration-time-content').html(time_content);
                        }

                        $('#mitigration-extend-deadline').val(mitigation.time);
                        $('#mitigration-extend-deadline').datetimepicker({
                            format: 'd-m-Y H:i',
                            step: 5
                        });
                        $('#mitigation-extension-reason-select').val(mitigation.pre_defined_reason);
                        $('#mitigation-allocatabletype').val(mitigation.allocatabletype);
                        $('#mitigation-allocatableid').val(mitigation.allocatableid);
                        $('#mitigation-courseworkid').val(mitigation.courseworkid);
                        $('#mitigation-id').val(mitigation.id);
                        $('#mitigation-select').val(mitigation.type);
                        $('#id_extra_information').val(mitigation.text);
                        if (mitigation.id) {
                            $('#mitigation-id').val(mitigation.id);
                        }
                        disable_button_form_mitigation(mitigation.type);
                        save_mitigation_form_data();
                    }
                },
                error: function () {
                    $('html, body').css("cursor", "auto");
                    change__status_mitigation_submit_button(false);
                },
                complete: function () {
                    $('html, body').css("cursor", "auto");
                    change__status_mitigation_submit_button(false);
                }
            });
        }

        function mitigatiion_new_change_data_form(data_name, data_params, data_time, current_rowid) {
            var title = 'New mitigation for ' + data_name;
            $('#mitigration-modal-title').html(title);
            $('#form-mitigation').find('input[type=hidden]').val('');
            $('#form-mitigation').find('textarea').val('');
            if ($('#mitigation-select option[value="extension"]').length > 0) {
                $('#mitigation-select').val('extension');
            } else {
                $('#mitigation-select').val('permanent');
            }
            if(data_time.is_have_deadline == '1') {
                var url = mitigation_lang_message.url_root;
                $.ajax({
                    type: "GET",
                    url: url + "/mod/coursework/actions/ajax/mitigation/new.php",
                    data: data_params,
                    beforeSend: function () {
                        change__status_mitigation_submit_button(true);
                        $('html, body').css("cursor", "wait");
                        $('.modal-footer').children('img').css('visibility', 'visible');
                    },
                    success: function (response) {
                        $('html, body').css("cursor", "auto");
                        $('.modal-footer').children('img').css('visibility', 'hidden');
                        var data_response = JSON.parse(response);
                        $('#mitigration-time-content').html(data_response.data.time_content);
                        $('#mitigration-extend-deadline').val(data_response.data.time);
                        $('#mitigration-extend-deadline').datetimepicker({
                            format: 'd-m-Y H:i',
                            step: 5
                        });
                        save_mitigation_form_data();
                    },
                    error: function () {
                        $('html, body').css("cursor", "auto");
                    },
                    complete: function () {
                        $('html, body').css("cursor", "auto");
                    }
                });
            } else {
                save_mitigation_form_data();
            }
            $('#mitigation-extension-reason-select').val('');
            $('#mitigation-allocatabletype').val(data_params.allocatabletype);
            $('#mitigation-allocatableid').val(data_params.allocatableid);
            $('#mitigation-courseworkid').val(data_params.courseworkid);
            $('#mitigation-submissionid').val(data_params.submissionid);
            $('#mitigation-name').val(data_name);
            $('#button-id').val(current_rowid);
            var type = $('#mitigation-select').val();
            disable_button_form_mitigation(type);
        }

        $('#mitigation-select').change(function (e) {
            var type = $('#mitigation-select').val();
            disable_button_form_mitigation(type);
        });

        $("#form-mitigation :input").change(function () {
            mitigation_form_change = true;
            change__status_mitigation_submit_button(false);
        });

        function change__status_mitigation_submit_button(status) {
            $('#mitigation-submit').prop('disabled', status);
        }

        function save_mitigation_form_data() {
            mitigation_form_change = false;
        }

        function is_data_mitigation_form_change() {
            return mitigation_form_change;
        }



        function initialise_personal_feedback_deadline_locks() {


            $('.set_lock').unbind('click', null, function (e) {});

            $('.set_lock').on('click', null, function (e) {

                e.preventDefault();


                var data_params = [];
                var hiddenid = $(this).attr('data-hiddenid');
                var lockflag = $('#' + hiddenid).val();
                data_params.lockflag = $(this).attr('data-lockflag');
                data_params.ajax = 1;
                data_params.allocatableid = $(this).attr('data-allocatableid');
                data_params.allocatabletype = $(this).attr('data-allocatabletype');
                data_params.courseworkid = $(this).attr('data-courseworkid');
                var url = $(this).attr('data-url');

                var link = $(this);


                $.ajax({
                    type: "POST",
                    data: {
                        'lockflag': lockflag,
                        'ajax': 1,
                        'allocatableid': $(this).attr('data-allocatableid'),
                        'allocatabletype': $(this).attr('data-allocatabletype'),
                        'courseworkid': $(this).attr('data-courseworkid')
                    },
                    url: url,
                    success: function (response) {
                        response = $.parseJSON(response);


                        if (lockflag == 1) {

                            $(link).html("<i class ='icon fa fa-lock fa-fw'></i>");
                        } else {

                            $(link).html("<i class ='icon fa fa-unlock-alt fa-fw'></i>");
                        }

                        newlockflag = (lockflag == 1) ? 0 : 1;
                        $('#' + hiddenid).val(newlockflag);


                    }
                });


            });
        }

        initialise_personal_feedback_deadline_locks();

    });


})

