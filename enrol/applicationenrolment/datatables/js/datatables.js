<<<<<<< HEAD
var table_obj_list = [];
var id = 0;
var array_button_grade = [];
var is_responsive = false;
var form_plagiarism_alert_change = false;
var form_moderation_agreement_change = false;
var fullload = false;
var display_suspended_gbl   =   0;

/**
 *
 * @param row
 * @returns {boolean}
 */
function compare_row(row) {
    return (this == row.DT_RowId);
}

$(document).ready( function () {
    // Prepare Message
    var langmessage = JSON.parse($('#element_lang_messages').attr('data-lang'));


    if(isMobileDevice() && $(window).width() < 768) {
        $(".datatabletest thead > tr:first").remove();
    }
    var base_url = window.location.origin  + '/mod/coursework/datatables/js/';

    var button_grade_action  = add_button_grade();

    /**
     *
     * @param tableobject
     * @returns {any[]}
     */
    function get_selected_submissions(tableobject) {
        var result = [];
        var selectedrows = tableobject.rows({ selected: true }).data();
        for (var i = 0; i < selectedrows.length; i++) {
            var submissioncellposition = $('.tableheadfilename').attr('data-seq');
            var submissionId = $('<div>' + selectedrows[i][submissioncellposition] + '</div>').find('.submissionid').attr('data-submissionid');
            if (submissionId) {
                result.push(submissionId);
            }
        }
        return result;
    }

    /**
     *
     * @param e
     * @param dt
     * @param node
     * @param conf
     */
    function grade_action_redirect(e, dt, node, conf) {
        var url = array_button_grade[conf.text];
        var selectrowactions = [langmessage.download_submitted_files, langmessage.exportfinalgrades, langmessage.exportgradingsheets];
        if (selectrowactions.indexOf(conf.text) > -1) {
            var selectedsubmissionids = get_selected_submissions(dt);
            if (selectedsubmissionids.length) {
                url += '&' + $.param({selectedsubmissionids: selectedsubmissionids});
            }
        }
        window.location.href = url;
    }

    function add_button_grade() {
        var buttons = [];
        $("button.grade-action").each(function() {
            var button_grade = {
                'text': $(this).html(),
                'action':  function(e, dt, node, conf) {
                    grade_action_redirect(e, dt, node, conf);
                }
            };
            buttons.push(button_grade);
            array_button_grade[$(this).html()] = $(this).attr('data-url');
        });

        if($('div.printcoversheet').length) {
            var button_grade = {
                'text': $('div.printcoversheet form input[type=submit]').val(),
                'action':  function(e, dt, node, conf) {
                    $('div.printcoversheet form input[type=submit]').click();
                }
            };
            buttons.push(button_grade);
        }

        if($('div.setattendancedate').length) {
            var button_grade = {
                'text': $('div.setattendancedate form input[type=submit]').val(),
                'action':  function(e, dt, node, conf) {
                    $('div.setattendancedate form input[type=submit]').click();
                }
            };
            buttons.push(button_grade);
        }

        if(typeof buttons !== 'undefined' && buttons.length > 0) {
            return {
                extend: 'collection',
                text: $('#grading_action_button').val() || 'Grading Action',
                buttons: buttons
            }
        }
        return 0;
    };

    require.config({
        paths: {
            'jquery':                   base_url + 'jquery-3.3.1.min',
            'datatables.net':           base_url + 'jquery.datatables',
            'datatables.searchpanes':   base_url + 'datatables.searchpanes',
            'datatables.buttons':       base_url + 'datatables.buttons',
            'datatables.select':        base_url + 'datatables.select',
            'datatables.responsive':    base_url + 'datatables.responsive.min',
            'jquery-mousewheel': base_url +'jquery.mousewheel',
            'datetimepicker':    base_url + 'jquery.datetimepicker',

        }
    });

    require(['jquery', 'datatables.net'], function ($, DataTable) {

        $.fn.DataTable = DataTable;
        $.fn.DataTableSettings = DataTable.settings;
        $.fn.dataTableExt = DataTable.ext;
        DataTable.$ = $;
        $.fn.DataTable = function ( opts ) {
            return $(this).dataTable( opts ).api();
        };

        require(['jquery', 'datatables.searchpanes'], function($) {
            require(['jquery', 'datatables.select'], function($) {
                require(['jquery', 'datatables.buttons'], function($) {
                    require(['jquery', 'datatables.responsive'], function($) {
                        if(isMobileDevice() && $(window).width() < 768) {
                            is_responsive = true;
                            initDatatable(is_responsive);

                            $('.datatabletest').on('order.dt', function(e) {
                                $('.submissionrowmulti').removeClass("shown");
                            });
                        }
                        else {
                            initDatatable(is_responsive);
                        }
                    });
                });
            });
        });
    });

    /**
     *
     * @param tableid
     */
    function background_load_table(tableid) {
        var tableelement = $('#' + tableid);
        var wrapperelement = tableelement.parent('.dataTables_wrapper');
        var paginationelement = wrapperelement.find('.dataTables_paginate');
        var tableobject = table_obj_list[tableid];

        // hide buttons
        wrapperelement.find('.dataTables_paginate, .dataTables_info, .dataTables_length, .dataTables_filter').css('visibility', 'hidden');
        wrapperelement.find('thead, .dt-button').each(function() {
            var me = $(this);
            me.css('pointer-events', 'none');
            if (me.hasClass('dt-button')) {
                me.find('span').html(' ' + me.find('span').html());
            }
        });
        $('<div class="text-center pagination-loading"><i class="fa fa-spinner fa-spin"></i> ' + langmessage.loadingpagination + '</div>').insertAfter(paginationelement);
        $('<i class="fa fa-spinner fa-spin pagination-loading"></i>').insertBefore(wrapperelement.find('.dt-button > span'));

        // prepare params for ajax request
        var params = {
            group: tableelement.attr('group'),
            perpage: tableelement.attr('perpage'),
            sortby: tableelement.attr('sortby'),
            sorthow: tableelement.attr('sorthow'),
            firstnamealpha: tableelement.attr('firstnamealpha'),
            lastnamealpha: tableelement.attr('lastnamealpha'),
            groupnamealpha: tableelement.attr('groupnamealpha'),
            substatus: tableelement.attr('substatus'),
            unallocated: tableelement.attr('unallocated'),
            courseworkid: tableelement.attr('courseworkid'),
        };

        $.ajax({
            url: '/mod/coursework/actions/ajax/datatable/grading.php',
            type: 'POST',
            data: params
        }).done(function(response) {
            tableobject.rows.add($(response)).draw(false);
            tableobject.searchPanes.rebuildPane();
        }).fail(function() {}).always(function() {
            // show buttons
            wrapperelement.find('.pagination-loading').remove();
            wrapperelement.find('thead, .dt-button').css('pointer-events', 'auto');
            wrapperelement.find('.dataTables_paginate, .dataTables_info, .dataTables_length, .dataTables_filter').css('visibility', 'visible');
        });
    }

    function initDatatable(is_responsive) {
        $(".datatabletest").each(function () {
            var fullloaded = $(this).hasClass('full-loaded');
            var table =   $(this).DataTable( {
                'order': [],
                stateSave: true,
                language: {
                    searchPanes: {
                        collapse: {0: $('#search_pane_button').val() || 'Filter', _:($('#search_pane_button').val() || 'Filter')+' (%d)'}
                    }
                },
                buttons:[
                    'searchPanes'
                ],
                dom: 'Blfrtip',
                columnDefs:[
                    {
                        searchPanes:{
                            show: false
                        },
                        className: "select-checkbox",
                        targets: 'checkbox_cell',
                        bSortable: false
                    },
                    {
                        searchPanes:{
                            show: false
                        },
                        targets: ['studentname','addition-multiple-button'],
                        bSortable: false
                    },
                    {
                        searchPanes: {
                            show: false
                        },
                        targets: ['lastname_cell','firstname_cell','tableheadpersonaldeadline', 'tableheaddate', 'tableheadfilename', 'tableheadplagiarismalert', 'plagiarism', 'agreedgrade', 'feedbackandgrading', 'provisionalgrade', 'tableheadmoderationagreement']
                    },
                    {
                        searchPanes:{
                            show: true,
                            header: $('#search_pane_group').val() || 'Group',
                        },
                        targets: 'tableheadgroups',
                    },
                    {
                        searchPanes:{
                            show: true,
                            header: $('#search_pane_status').val() || 'Status',
                            getFullText: true,
                        },
                        targets: 'tableheadstatus',
                    },
                    {
                        searchPanes:{
                            show: true,
                            header: $('#search_pane_firstname').val() || 'First Name Initial',
                        },
                        targets: 'firstname_letter_cell',
                    },
                    {
                        searchPanes:{
                            show: true,
                            header: $('#search_pane_lastname').val() || 'Last Name Initial',
                        },
                        targets: 'lastname_letter_cell',
                    },
                    { "visible": false,  "targets": [ 'lastname_letter_cell','firstname_letter_cell', 'lastname_cell','firstname_cell'] }
                ],
                select: {
                    style:    'multi',
                    selector: '.select-checkbox'
                },
                stateSaveParams: function (settings, data) {
                    data.columns = [];
                }

            }).on("click", "th.select-checkbox", function() {
                if ($(this).hasClass("selected")) {
                    table.rows().deselect();
                } else {
                    table.rows().select();
                }
            }).on("select deselect", function() {
                if (table.rows({selected: true}).count() !== table.rows().count()) {
                    $("th.select-checkbox").removeClass("selected");
                } else {
                    $("th.select-checkbox").addClass("selected");
                }
            });
            table.column('.checkbox_cell').visible(true);

            //extends the search filters to enable hide and show of suspended students
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {

                    return ( $(table.row(dataIndex).node()).hasClass('suspendeduser') && display_suspended_gbl==0) ? false :true;

                }
            );


            var showhidesuspendedbutton  =                     //this creates the hide/show suspended students
                {
                    text: 'Show Unenrolled',
                    action: function ( e, dt, node, config ) {

                        //this checks the value of the display suspended global to see if it they show be displayed
                        // the display suspended global is set here and checked by code on line 254
                        if (display_suspended_gbl == 0) {
                            this.text( 'Hide Unenrolled' );
                            display_suspended_gbl   =   1;
                        }
                        else {
                            this.text( 'Show Unenrolled' );
                            display_suspended_gbl   =   0;
                        }

                        //after the display suspended global is set retrieve the table and tell it to redraw itself
                        var tableid = $(".datatabletest").first().attr('id');
                        var tabledatatable    =   $('#' + tableid).DataTable({"retrieve": true});
                        tabledatatable.draw();

                    }
                }


                if ($('.courseworktype').first().attr('value') == 0) {
                    table.button().add(1, showhidesuspendedbutton);
                }

            if(is_responsive) {

                new $.fn.dataTable.Responsive( table, {
                    details: {
                        display: $.fn.DataTable.Responsive.display.childRowImmediate,
                        type: 'none',
                        target: ''
                    }
                } );
            }
            if(id === 0 && button_grade_action !== 0) {
                table.button().add(1,button_grade_action);
            }
            id += 1;
            table_obj_list[$(this).attr('id')] = table;
            if (!fullloaded) {
                background_load_table($(this).attr('id'));
            }
            //redraw the table so suspended users are hidden
            table.draw();
        });

    }

    if(isMobileDevice() && $(window).width() < 768) {
        // For small screens
        $('.datatabletest tbody').on('click', 'td.details-control', function () {
            var tr = $(this).closest("tr");
            var row_id = tr.attr('id').replace('allocatable_', '');
            var table_id = 'assessorfeedbacktable_' + row_id;

            if ($(tr).next('tr.row_assessors').length > 0) {
                $(tr).next('tr.row_assessors').remove();
            }
            else {
                $('<tr class = "submissionrowmultisub row_assessors">'+
                    '<td class="assessors" colspan = "11"><table class="assessors" style="width:95%">' + $('#' + table_id).clone().html() + '</table></td>' +
                '</tr>').insertAfter($(tr));
            }

            $(tr).toggleClass('shown');
            // $("#" + table_id).toggleClass('tbl_assessor_shown');
            // $("#" + table_id).DataTable({ 'dom': '', 'responsive': true });
        });
    }
    else {
        // Add event listener for opening and closing details
        $('.datatabletest tbody').on('click', 'td.details-control', function () {
            var tr = $(this).closest("tr");
            var table_key = $(this).closest('.datatabletest').attr('id');
            var table = table_obj_list[table_key];
            if (table) {
                var row = table.row( tr );

                var row_id = tr.attr('id').replace('allocatable_', '');
                var table_id = 'assessorfeedbacktable_' + row_id;

                if ($('#' + table_id).length > 0) {
                    if ( row.child.isShown() ) {
                        // This row is already open - close it
                        row.child.hide();
                        tr.removeClass('shown');
                    }
                    else {
                        // Open this row
                        // row.child( format(row.data()) ).show();
                        row.child($(
                            '<table class="assessors" width="100%"><tr class = "submissionrowmultisub">'+
                                '<td class="assessors" colspan = "11"><table class="assessors">' + $('#' + table_id).clone().html() + '</table></td>' +
                            '</tr></table>'
                        )).show();
                        tr.addClass('shown');
                    }
                }
            }
        });
    }

    function format(value) {
        // alert(value);
    }

    $('.datatabletest').on('click', '.splitter-firstname, .splitter-lastname', function (event) {
        event.preventDefault();
        var node = $(event.target),
            isAscending = node.hasClass('sorting_asc'),
            currentsort = 'asc', sortby = 'desc';
        if (!isAscending) {
            currentsort = 'desc';
            sortby = 'asc';
        }

        //node.closest('#example').DataTable()
        //    .order( [ sortColumn, sortby ] )
        //    .draw();
        var table_id = $(this).closest('.datatabletest').attr('id');
        table = table_obj_list[table_id];
        var headerclass = $(this).hasClass('splitter-firstname') ? 'firstname_cell' : 'lastname_cell';
        var sortColumn = table.column('.' + headerclass).index();

        table.order([sortColumn, sortby]).draw();

        node.addClass('sorting_' + sortby).removeClass('sorting sorting_' + currentsort);
        node.parent().removeClass('sorting sorting_asc sorting_desc');
        node.siblings().removeClass('sorting_asc sorting_desc').addClass('sorting');
    });

    /*##################################################### PLAGIARISM ALERT EDIT ##############################################################*/

    /* Catch the event of any changes in the form input fields */
    $('#modal_form_plagiarism_alert :input').change(function () {
        form_plagiarism_alert_change = true;
        $('#modal_form_plagiarism_alert .btn_save').removeAttr('disabled');
    });

    function plagiarism_alert_confirm(plagiarism_dlg) {
        var confirm = new M.core.confirm({
            title: 'Confirm',
            question: $('#modal_form_plagiarism_alert .notification_leave_form_message').text(),
            yesLabel: 'Yes',
            noLabel: 'No',
        });

        confirm.on('complete-yes',function() {
            confirm.hide();
            confirm.destroy();
            form_plagiarism_alert_change = false;
            $(plagiarism_dlg).modal('hide');
        });

        confirm.show();
    }

    /* New plagiarism click */
    $('.wrapper_table_submissions').on('click', '.new_plagiarism_flag', function(e) {
        e.preventDefault();

        var self = $(this);
        var submissionid = $(self).data('submissionid');
        var current_user = $(self).closest('tr').find('.user_cell > a:first').text();
        var currentrowid  = $(self).closest('tr').attr('id');

        $('#modal_form_plagiarism_alert .modal-title').html('Plagiarism flagging for ' + current_user);
        $('#modal_form_plagiarism_alert .editor_information').html('');
        $('#modal_form_plagiarism_alert').find('.plagiarism_flag_status option').removeAttr('selected');
        $('#modal_form_plagiarism_alert').find('.plagiarism_flag_status option[value=0]').prop('selected','selected');
        $('#modal_form_plagiarism_alert .plagiarism_flag_comment').val('');
        $('#modal_form_plagiarism_alert .btn_save').attr('data-submissionid', submissionid).attr('data-action', 'create')
            .attr('data-currentrowid', currentrowid).removeAttr('disabled');

        form_plagiarism_alert_change = false;
        $('#modal_form_plagiarism_alert')
            .on('hide.bs.modal', function(e) {
                // No idea, why this event sometimes fires several times
                if ($('.moodle-dialogue-base.moodle-dialogue-confirm[aria-hidden=false]').length > 0) {
                    return false;
                }
                var self = this;
                if(form_plagiarism_alert_change) {
                    plagiarism_alert_confirm(self);
                    return false;
                }
                return true;
            })
            .modal('show');
    });

    /* Click on the editing an existing plagiarism */
    $('.wrapper_table_submissions').on('click', '.existing_plagiarism_flag', function(e) {
        e.preventDefault();

        var self = $(this);

        var createddby      = $(self).data('createddby');
        var lasteditedby    = $(self).data('lasteditedby');
        var timemodified    = $(self).data('timemodified');
        var statusvalue     = $(self).data('statusvalue');
        var comment         = $(self).data('comment');
        var flagid          = $(self).data('flagid');
        var submissionid    = $(self).closest('tr').find('.submissionid').data('submissionid');
        var currentrowid    = $(self).closest('tr').attr('id');
        var current_user    = $(self).closest('tr').find('.user_cell > a:first').text();
        var submission_published = $(self).data('submission_published');

        $('#modal_form_plagiarism_alert .modal-title').html('Plagiarism flagging for ' + current_user);
        $('#modal_form_plagiarism_alert').find('.plagiarism_flag_status option').removeAttr('selected');
        $('#modal_form_plagiarism_alert').find('.plagiarism_flag_status option[value='+statusvalue+']').prop('selected','selected');
        $('#modal_form_plagiarism_alert .plagiarism_flag_comment').val(comment);
        $('#modal_form_plagiarism_alert .btn_save').attr('data-flagid', flagid).attr('data-action', 'edit')
            .attr('data-submissionid', submissionid).attr('data-currentrowid', currentrowid);

        var editor_information = '<table class = "plagiarism-flag-details">';
        editor_information += '<tr><th>Created by </th><td>' + createddby + '</td></tr>';
        editor_information += '<tr><th>Last edited by </th><td>' + lasteditedby + ' on ' + timemodified + '</td></tr></table>';
        if (submission_published == '1') {
            editor_information += '<div class ="alert">' + $('#modal_form_plagiarism_alert .gradereleasedtostudent').text() + '</div>';
        }
        $('#modal_form_plagiarism_alert .editor_information').html(editor_information);

        form_plagiarism_alert_change = false;
        $('#modal_form_plagiarism_alert')
            .on('hide.bs.modal', function(e) {
                // No idea, why this event sometimes fires several times
                if ($('.moodle-dialogue-base.moodle-dialogue-confirm[aria-hidden=false]').length > 0) {
                    return false;
                }
                var self = this;
                if(form_plagiarism_alert_change) {
                    plagiarism_alert_confirm(self);
                    return false;
                }
                return true;
            })
            .modal('show');
    });

    /* Click on the button Save */
    $('#modal_form_plagiarism_alert').on('click', '.btn_save', function(e) {
        e.preventDefault();

        var self = $(this);

        var submissionid            = $(self).attr('data-submissionid');
        var flagid                  = $(self).attr('data-flagid');
        var current_rowid           = $(self).attr('data-currentrowid');
        var plagiarism_flag_status  = $('#modal_form_plagiarism_alert .plagiarism_flag_status').find(":selected").val();
        var plagiarism_flag_comment = $('#modal_form_plagiarism_alert .plagiarism_flag_comment').val();

        var data = {    'plagiarism_flag_status': plagiarism_flag_status,
                        'plagiarism_flag_comment': plagiarism_flag_comment,
                        'submissionid': submissionid,
                        'flagid': flagid,
                        'inlineedit': 'true' };

        var url_edit = '/mod/coursework/actions/plagiarism_flagging/update.php';
        var url_create = '/mod/coursework/actions/plagiarism_flagging/create.php';
        if ($(self).attr('data-action') == 'create') {
            var url = url_create;
        }
        else if ($(self).attr('data-action') == 'edit') {
            var url = url_edit;
        }

        // AJAX POST
        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            beforeSend: function () {
                $('html, body').css('cursor', 'wait');
                $(self).prev('img').css('visibility', 'visible');
                $(self).prop('disabled', true);
                form_plagiarism_alert_change = false;
            },
            success: function (response) {
                // console.log(response);
                $('html, body').css('cursor', 'auto');
                var data_response = JSON.parse(response);
                if (data_response.result == 'success') {
                    // Update the DOM
                    // $(self).closest('.plagiarism_flag_cell').html(response->success_content);

                    // Update the data of datatables too. => It seems, updating the datatables triggering the DOM getting updated too.
                    if (Object.keys(table_obj_list).length > 0) {
                        // Get the first datatable object.
                        var table = table_obj_list[Object.keys(table_obj_list)[0]];
                        var current_row_data = table.row('#' + current_rowid).data();

                        // plagiarism
                        var plagiarismindex = table.column('.tableheadplagiarismalert').index();
                        var current_plagiarism_alert_cell_data = data_response.success_content;
                        current_row_data[plagiarismindex] = current_plagiarism_alert_cell_data;
                        table.row('#' + current_rowid).data(current_row_data);
                    }
                    var notification = new M.core.alert({
                        message: $('#modal_form_plagiarism_alert .plagiarism_alert_saved_info').text(),
                        title: 'Info',
                    });
                    notification.show();
                }
                else {
                    var notification = new M.core.alert({
                        message: $('#modal_form_plagiarism_alert .moderations_inlineedit_error_info').text(),
                        title: 'Info',
                    });
                    notification.show();
                }
            },
            error: function () {
                $('html, body').css('cursor', 'auto');
            },
            complete: function () {
                $('html, body').css('cursor', 'auto');
                $(self).prev('img').css('visibility', 'hidden');
            }
        });
    });

    function plagiarism_alert_move_back_or_next(self, direction) {
        if (Object.keys(table_obj_list).length > 0) {

            var last_rowid = $(self).closest('.modal-footer').find('.btn_save').attr('data-currentrowid'); // => it seems $('.btn_save').data('current_rowid') returning cached value

            // Get the first datatable object.
            var table = table_obj_list[Object.keys(table_obj_list)[0]];

            var ordereddata = table.rows( { order: 'applied', search: 'applied' } ).data().toArray();
            var last_row_index = ordereddata.findIndex(compare_row, last_rowid);

            do {
                if (direction == 'next') {
                    var target_row_index = last_row_index + 1;
                }
                else if (direction == 'back') {
                    var target_row_index = last_row_index - 1;
                }
                last_row_index = target_row_index;
                var target_row_data = ordereddata[target_row_index];
                if (typeof target_row_data != 'undefined') {
                    var data_found = false;

                    var target_rowid = target_row_data.DT_RowId;

                    // user name
                    var firstnameindex = table.column('.firstname_cell').index();
                    var lastnameindex = table.column('.lastname_cell').index();
                    var current_user = target_row_data[firstnameindex] + ' ' + target_row_data[lastnameindex];

                    // plagiarism
                    var plagiarismindex = table.column('.tableheadplagiarismalert').index();
                    var current_cell_data = target_row_data[plagiarismindex].trim(); // Remove leading and tailing spaces if any
                    if (current_cell_data != '') {
                        $(self).closest('.modal-footer').find('.btn_save').attr('data-currentrowid', target_rowid).removeAttr('disabled');
                        data_found = true;
                        var tmp_node = $('<div/>').html(target_row_data[plagiarismindex]);

                        if ($(tmp_node).find('.new_plagiarism_flag').length > 0) {
                            // Create new moderation agreement
                            var submissionid = $(tmp_node).find('.new_plagiarism_flag').data('submissionid');
                            $(self).closest('.modal-footer').find('.btn_save').attr('data-action', 'create')
                                .attr('data-submissionid', submissionid);

                            $('#modal_form_plagiarism_alert').find('.plagiarism_flag_status option').removeAttr('selected');
                            $('#modal_form_plagiarism_alert').find('.plagiarism_flag_status option[value=0]').prop('selected','selected');
                            $('#modal_form_plagiarism_alert .plagiarism_flag_comment').val('');
                            $('#modal_form_plagiarism_alert .modal-title').html('Plagiarism flagging for ' + current_user);
                            $('#modal_form_plagiarism_alert .editor_information').html('');
                        }
                        else if ($(tmp_node).find('.existing_plagiarism_flag').length > 0) {
                            // Update an existing moderation agreement
                            var statusvalue  = $(tmp_node).find('.existing_plagiarism_flag').data('statusvalue');
                            var comment      = $(tmp_node).find('.existing_plagiarism_flag').data('comment');
                            var flagid       = $(tmp_node).find('.existing_plagiarism_flag').data('flagid');
                            var createddby   = $(tmp_node).find('.existing_plagiarism_flag').data('createddby');
                            var lasteditedby = $(tmp_node).find('.existing_plagiarism_flag').data('lasteditedby');
                            var timemodified = $(tmp_node).find('.existing_plagiarism_flag').data('timemodified');
                            var submission_published = $(tmp_node).find('.existing_plagiarism_flag').data('submission_published');

                            $('#modal_form_plagiarism_alert').find('.plagiarism_flag_status option').removeAttr('selected');
                            $('#modal_form_plagiarism_alert').find('.plagiarism_flag_status option[value='+statusvalue+']').prop('selected','selected');
                            $('#modal_form_plagiarism_alert .plagiarism_flag_comment').val(comment);
                            $('#modal_form_plagiarism_alert .modal-title').html('Plagiarism flagging for ' + current_user);
                            var editor_information = '<table class = "plagiarism-flag-details">';
                            editor_information += '<tr><th>Created by </th><td>' + createddby + '</td></tr>';
                            editor_information += '<tr><th>Last edited by </th><td>' + lasteditedby + ' on ' + timemodified + '</td></tr></table>';
                            if (submission_published == '1') {
                                editor_information += '<div class ="alert">' + $('#modal_form_plagiarism_alert .gradereleasedtostudent').text() + '</div>';
                            }
                            $('#modal_form_plagiarism_alert .editor_information').html(editor_information);

                            $(self).closest('.modal-footer').find('.btn_save').attr('data-flagid', flagid)
                                .attr('data-action', 'edit');
                        }
                        form_plagiarism_alert_change = false;
                    }
                    // console.log(JSON.stringify(target_row_data));
                    if (data_found) {
                        break; // break the while loop
                    }
                }
                else {
                    var notification = new M.core.alert({
                        message: $('#modal_form_plagiarism_alert .plagiarism_inlineedit_no_more_info').text(),
                        title: 'Info',
                    });
                    notification.show();

                    break; // break the while loop. Row index gets to the edge and no more found
                }
            }
            while (data_found == false);

        }
    }

    /* Clicking on Plagiarism Alert Back button */
    $('#modal_form_plagiarism_alert').on('click', '.btn_back', function(e) {

        var self = $(this);

        if (form_plagiarism_alert_change) {
            var confirm = new M.core.confirm({
                title: 'Confirm',
                question: $('#modal_form_plagiarism_alert .notification_leave_form_message').text(),
                yesLabel: 'Yes',
                noLabel: 'No',
            });

            confirm.on('complete-yes',function() {
                confirm.hide();
                confirm.destroy();
                plagiarism_alert_move_back_or_next(self, 'back');
            });

            confirm.show();
        }
        else {
            plagiarism_alert_move_back_or_next(self, 'back');
        }
    });

    /* Clicking on Plagiarism Alert Next button */
    $('#modal_form_plagiarism_alert').on('click', '.btn_next', function(e) {

        var self = $(this);

        if (form_plagiarism_alert_change) {
            var confirm = new M.core.confirm({
                title: 'Confirm',
                question: $('#modal_form_plagiarism_alert .notification_leave_form_message').text(),
                yesLabel: 'Yes',
                noLabel: 'No',
            });

            confirm.on('complete-yes',function() {
                confirm.hide();
                confirm.destroy();
                plagiarism_alert_move_back_or_next(self, 'next');
            });

            confirm.show();
        }
        else {
            plagiarism_alert_move_back_or_next(self, 'next');
        }
    });

    /*##################################################### END OF PLAGIARISM ALERT EDIT ##############################################################*/





    /*##################################################### MODERATION EDIT ###########################################################################*/

    $('#modal_new_moderation_agreement :input').change(function () {
        form_moderation_agreement_change = true;
        $('#modal_new_moderation_agreement .btn_save').removeAttr('disabled');
    });

    function moderation_agreement_confirm(moderation_dlg) {
        var confirm = new M.core.confirm({
            title: 'Confirm',
            question: $('#modal_new_moderation_agreement .notification_leave_form_message').text(),
            yesLabel: 'Yes',
            noLabel: 'No',
        });

        confirm.on('complete-yes',function() {
            confirm.hide();
            confirm.destroy();
            form_moderation_agreement_change = false;
            $(moderation_dlg).modal('hide');
        });

        confirm.show();
    }

    $('.wrapper_table_submissions').on('click', '.new_moderation', function(e) {
        e.preventDefault();

        var self = $(this);
        $('#modal_new_moderation_agreement .btn_save').removeAttr('disabled');
        var current_user = $(self).closest('tr').find('.user_cell > a:first').text();

        $('#modal_new_moderation_agreement .modal-title').html('Moderation for ' + current_user);
        var submissionid = $(self).data('submissionid');
        var courseworkid = $(self).data('courseworkid');
        var feedbackid   = $(self).data('feedbackid');
        var current_rowid = $(self).closest('tr').attr('id');
        var moderatorname = $(self).data('moderatorname');

        $('#modal_new_moderation_agreement .btn_save').attr('data-submissionid', submissionid).attr('data-courseworkid', courseworkid)
            .attr('data-feedbackid', feedbackid).attr('data-current_rowid', current_rowid);

        var editor_information = '<table class = "moderating-details">';
        editor_information += '<tr><th>Moderator</th><td>' + moderatorname + '</td></tr>';
        editor_information += '</table>';
        $('#modal_new_moderation_agreement .editor_information').html(editor_information);

        form_moderation_agreement_change = false;
        $('#modal_new_moderation_agreement')
            .on('hide.bs.modal', function(e) {
                // No idea, why this event sometimes fires several times
                if ($('.moodle-dialogue-base.moodle-dialogue-confirm[aria-hidden=false]').length > 0) {
                    return false;
                }
                var self = this;
                if(form_moderation_agreement_change) {
                    moderation_agreement_confirm(self);
                    return false;
                }
                return true;
            })
            .modal('show');
    });

    /* Clicking on Moderation Agreement Save button */
    $('#modal_new_moderation_agreement').on('click', '.btn_save', function() {

        var self         = $(this);
        var submissionid = $(self).attr('data-submissionid');
        var current_rowid  = $(self).attr('data-current_rowid');
        var moderation_agreement = $('#modal_new_moderation_agreement .moderation_agreement').find(":selected").val();
        moderation_agreement_comment = $('#modal_new_moderation_agreement .moderation_agreement_comment').val();
        var current_action = $(self).attr('data-action');

        if (current_action == 'edit') {
            var data = {    'moderationid': $(self).attr('data-moderationid'),
                            'moderation_agreement': moderation_agreement,
                            'moderation_agreement_comment': moderation_agreement_comment,
                            'inlineedit': 'true'
                        };
            var url = '/mod/coursework/actions/moderations/update.php';
        }
        else {
            var data = {    'submissionid': submissionid,
                            'feedbackid': $(self).attr('data-feedbackid'),
                            'stage_identifier': 'moderator',
                            'moderation_agreement': moderation_agreement,
                            'moderation_agreement_comment': moderation_agreement_comment,
                            'inlineedit': 'true'
                        };
            var url = '/mod/coursework/actions/moderations/create.php';
        }

        // AJAX POST
        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            beforeSend: function () {
                $('html, body').css('cursor', 'wait');
                $(self).prev('img').css('visibility', 'visible');
                $(self).prop('disabled', true);
                form_moderation_agreement_change = false;
            },
            success: function (result) {
                // console.log(result);
                $('html, body').css('cursor', 'auto');
                $(self).prev('img').css('visibility', 'hidden');
                if (result.startsWith('Agreed') || result.startsWith('Disagreed')) {
                    // Update the DOM
                    // if ($('.submissionid[data-submissionid="'+ submissionid +'"]').closest('tr').find('.moderation_agreement_cell').length > 0) {
                    //     $('.submissionid[data-submissionid="'+ submissionid +'"]').closest('tr').find('.moderation_agreement_cell').html(result);
                    // }

                    // Update the data of datatables too. => It seems, updating the datatables triggering the DOM getting updated too.
                    if (Object.keys(table_obj_list).length > 0) {
                        // Get the first datatable object.
                        var table = table_obj_list[Object.keys(table_obj_list)[0]];
                        var current_row_data = table.row('#' + current_rowid).data();

                        var moderationagreementindex = table.column('.tableheadmoderationagreement').index();
                        var current_moderation_cell_data = result;
                        current_row_data[moderationagreementindex] = current_moderation_cell_data;
                        table.row('#' + current_rowid).data(current_row_data);
                    }

                    var notification = new M.core.alert({
                        message: $('#modal_new_moderation_agreement .moderations_inlineedit_saved_info').text(),
                        title: 'Info',
                    });
                    notification.show();
                }
                else {

                    var notification = new M.core.alert({
                        message: $('#modal_new_moderation_agreement .moderations_inlineedit_error_info').text(),
                        title: 'Info',
                    });
                    notification.show();
                }
            },
            error: function () {
                $('html, body').css('cursor', 'auto');
                $(self).prev('img').css('visibility', 'hidden');
            },
            complete: function () {
                $('html, body').css('cursor', 'auto');
                $(self).prev('img').css('visibility', 'hidden');
            }
        });
    });

    /* Clicking on edit icon of Moderation Agreement (pencil) */
    $('.wrapper_table_submissions').on('click', '.existing_moderation', function(e) {
        e.preventDefault();

        var self = $(this);
        $('#modal_new_moderation_agreement .btn_save').removeAttr('disabled');
        var current_user = $(self).closest('tr').find('.user_cell > a:first').text();

        $('#modal_new_moderation_agreement .modal-title').html('Moderation for ' + current_user);
        var moderationid = $(self).data('moderationid');
        var submissionid = $(self).data('submissionid');
        var courseworkid = $(self).data('courseworkid');
        var feedbackid   = $(self).data('feedbackid');
        var modcomment   = $(self).data('modcomment');
        var agreementvalue = $(self).data('agreementvalue');
        var current_rowid  = $(self).closest('tr').attr('id');
        var moderatedby    = $(self).data('moderatedby');
        var lasteditedby   = $(self).data('lasteditedby');
        var timemodified   = $(self).data('timemodified');

        $('#modal_new_moderation_agreement .btn_save').attr('data-submissionid', submissionid).attr('data-courseworkid', courseworkid)
            .attr('data-feedbackid', feedbackid).attr('data-action', 'edit').attr('data-moderationid', moderationid).attr('data-current_rowid', current_rowid);
        $('#modal_new_moderation_agreement').find('.moderation_agreement option').removeAttr('selected');
        $('#modal_new_moderation_agreement').find('.moderation_agreement option[value='+agreementvalue+']').prop('selected','selected');
        $('#modal_new_moderation_agreement .moderation_agreement_comment').val(modcomment);

        var editor_information = '<table class = "moderating-details">';
        editor_information += '<tr><th>Moderated by</th><td>' + moderatedby + '</td></tr>';
        editor_information += '<tr><th>Last edited by</th><td>' + lasteditedby + ' on ' + timemodified + '</td></tr>';
        editor_information += '</table>';
        $('#modal_new_moderation_agreement .editor_information').html(editor_information);

        form_moderation_agreement_change = false;
        $('#modal_new_moderation_agreement')
            .on('hide.bs.modal', function(e) {
                // No idea, why this event sometimes fires several times
                if ($('.moodle-dialogue-base.moodle-dialogue-confirm[aria-hidden=false]').length > 0) {
                    return false;
                }
                var self = this;
                if(form_moderation_agreement_change) {
                    moderation_agreement_confirm(self);
                    return false;
                }
                return true;
            })
            .modal('show');
    });

    function moderation_agreement_move_back_or_next(self, direction) {

        if (Object.keys(table_obj_list).length > 0) {

            var last_rowid = $(self).closest('.modal-footer').find('.btn_save').attr('data-current_rowid'); // => it seems $('.btn_save').data('current_rowid') returning cached value

            // Get the first datatable object.
            var table = table_obj_list[Object.keys(table_obj_list)[0]];

            var ordereddata = table.rows( { order: 'applied', search: 'applied' } ).data().toArray();
            var last_row_index = ordereddata.findIndex(compare_row, last_rowid);

            do {
                if (direction == 'next') {
                    var target_row_index = last_row_index + 1;
                }
                else if (direction == 'back') {
                    var target_row_index = last_row_index - 1;
                }
                last_row_index = target_row_index;
                var target_row_data = ordereddata[target_row_index];
                if (typeof target_row_data != 'undefined') {
                    var data_found = false;

                    var target_rowid = target_row_data.DT_RowId;

                    // user name
                    var firstnameindex = table.column('.firstname_cell').index();
                    var lastnameindex = table.column('.lastname_cell').index();
                    var current_user = target_row_data[firstnameindex] + ' ' + target_row_data[lastnameindex];

                    // plagiarism
                    var moderationagreementindex = table.column('.tableheadmoderationagreement').index();
                    var current_cell_data = target_row_data[moderationagreementindex].trim(); // Remove leading and tailing spaces if any
                    if (current_cell_data != '') {
                        $(self).closest('.modal-footer').find('.btn_save').attr('data-current_rowid', target_rowid).removeAttr('disabled');
                        data_found = true;
                        var tmp_node = $('<div/>').html(target_row_data[moderationagreementindex]);

                        if ($(tmp_node).find('.new_moderation').length > 0) {
                            // Create new moderation agreement
                            var submissionid = $(tmp_node).find('.new_moderation').data('submissionid');
                            var feedbackid = $(tmp_node).find('.new_moderation').data('feedbackid');
                            var moderatorname = $(tmp_node).find('.new_moderation').data('moderatorname');
                            $(self).closest('.modal-footer').find('.btn_save').attr('data-feedbackid', feedbackid)
                                .attr('data-submissionid', submissionid).attr('data-action', 'add_new');

                            $('#modal_new_moderation_agreement').find('.moderation_agreement option').removeAttr('selected');
                            $('#modal_new_moderation_agreement').find('.moderation_agreement option[value=agreed]').prop('selected','selected');
                            $('#modal_new_moderation_agreement .moderation_agreement_comment').val('');
                            $('#modal_new_moderation_agreement .modal-title').html('Moderation for ' + current_user);
                            var editor_information = '<table class = "moderating-details">';
                            editor_information += '<tr><th>Moderator</th><td>' + moderatorname + '</td></tr>';
                            editor_information += '</table>';
                            $('#modal_new_moderation_agreement .editor_information').html(editor_information);
                        }
                        else if ($(tmp_node).find('.existing_moderation').length > 0) {
                            // Update an existing moderation agreement
                            var agreementvalue  = $(tmp_node).find('.existing_moderation').data('agreementvalue');
                            var modcomment      = $(tmp_node).find('.existing_moderation').data('modcomment');
                            var moderationid    = $(tmp_node).find('.existing_moderation').data('moderationid');
                            var submissionid    = $(tmp_node).find('.existing_moderation').data('submissionid');
                            var moderatedby     = $(tmp_node).find('.existing_moderation').data('moderatedby');
                            var lasteditedby    = $(tmp_node).find('.existing_moderation').data('lasteditedby');
                            var timemodified    = $(tmp_node).find('.existing_moderation').data('timemodified');

                            $('#modal_new_moderation_agreement').find('.moderation_agreement option').removeAttr('selected');
                            $('#modal_new_moderation_agreement').find('.moderation_agreement option[value='+agreementvalue+']').prop('selected','selected');
                            $('#modal_new_moderation_agreement .moderation_agreement_comment').val(modcomment);
                            $('#modal_new_moderation_agreement .modal-title').html('Moderation for ' + current_user);
                            var editor_information = '<table class = "moderating-details">';
                            editor_information += '<tr><th>Moderated by</th><td>' + moderatedby + '</td></tr>';
                            editor_information += '<tr><th>Last edited by</th><td>' + lasteditedby + ' on ' + timemodified + '</td></tr>';
                            editor_information += '</table>';
                            $('#modal_new_moderation_agreement .editor_information').html(editor_information);

                            $(self).closest('.modal-footer').find('.btn_save').attr('data-moderationid', moderationid)
                                .attr('data-submissionid', submissionid).attr('data-action', 'edit');
                        }
                        form_moderation_agreement_change = false;
                    }

                    // console.log(JSON.stringify(target_row_data));
                    if (data_found) {
                        break; // break the while loop
                    }
                }
                else {
                    var notification = new M.core.alert({
                        message: $('#modal_new_moderation_agreement .moderations_inlineedit_no_more_info').text(),
                        title: 'Info',
                    });
                    notification.show();

                    break; // break the while loop. Row index gets to the edge and no more found
                }
            }
            while (data_found == false);

        }
    }

    /* Clicking on Moderation Agreement Next button */
    $('#modal_new_moderation_agreement').on('click', '.btn_next', function(e) {

        var self = $(this);

        if (form_moderation_agreement_change) {
            var confirm = new M.core.confirm({
                title: 'Confirm',
                question: $('#modal_new_moderation_agreement .notification_leave_form_message').text(),
                yesLabel: 'Yes',
                noLabel: 'No',
            });

            confirm.on('complete-yes',function() {
                confirm.hide();
                confirm.destroy();
                moderation_agreement_move_back_or_next(self, 'next');
            });

            confirm.show();
        }
        else {
            moderation_agreement_move_back_or_next(self, 'next');
        }
    });

    /* Clicking on Moderation Agreement Back button */
    $('#modal_new_moderation_agreement').on('click', '.btn_back', function(e) {

        var self = $(this);

        if (form_moderation_agreement_change) {
            var confirm = new M.core.confirm({
                title: 'Confirm',
                question: $('#modal_new_moderation_agreement .notification_leave_form_message').text(),
                yesLabel: 'Yes',
                noLabel: 'No',
            });

            confirm.on('complete-yes',function() {
                confirm.hide();
                confirm.destroy();
                moderation_agreement_move_back_or_next(self, 'back');
            });

            confirm.show();
        }
        else {
            moderation_agreement_move_back_or_next(self, 'back');
        }
    });

    /*##################################################### END OF MODERATION ALERT EDIT ##############################################################*/

});

function isMobileDevice() {
    if ( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
        return true;
    }
    return false;
}

$("#grade-show").click(function(e) {
    e.preventDefault();
    if($("#list-button").hasClass("display-none")) {
        $("#list-button").removeClass("display-none");
    } else {
        $("#list-button").addClass("display-none");
    }
});

$(".grade-action").click(function(e) {
    e.preventDefault();
    var url = $(this).attr("data-url");
    window.location.href = url;
});
=======
var table_obj_list = [];
var id = 0;
var array_button_grade = [];
var is_responsive = false;
var form_plagiarism_alert_change = false;
var form_moderation_agreement_change = false;
var fullload = false;
var display_suspended_gbl   =   0;

/**
 *
 * @param row
 * @returns {boolean}
 */
function compare_row(row) {
    return (this == row.DT_RowId);
}

$(document).ready( function () {
    // Prepare Message
    var langmessage = JSON.parse($('#element_lang_messages').attr('data-lang'));


    if(isMobileDevice() && $(window).width() < 768) {
        $(".datatabletest thead > tr:first").remove();
    }
    var base_url = window.location.origin  + '/mod/coursework/datatables/js/';

    var button_grade_action  = add_button_grade();

    /**
     *
     * @param tableobject
     * @returns {any[]}
     */
    function get_selected_submissions(tableobject) {
        var result = [];
        var selectedrows = tableobject.rows({ selected: true }).data();
        for (var i = 0; i < selectedrows.length; i++) {
            var submissioncellposition = $('.tableheadfilename').attr('data-seq');
            var submissionId = $('<div>' + selectedrows[i][submissioncellposition] + '</div>').find('.submissionid').attr('data-submissionid');
            if (submissionId) {
                result.push(submissionId);
            }
        }
        return result;
    }

    /**
     *
     * @param e
     * @param dt
     * @param node
     * @param conf
     */
    function grade_action_redirect(e, dt, node, conf) {
        var url = array_button_grade[conf.text];
        var selectrowactions = [langmessage.download_submitted_files, langmessage.exportfinalgrades, langmessage.exportgradingsheets];
        if (selectrowactions.indexOf(conf.text) > -1) {
            var selectedsubmissionids = get_selected_submissions(dt);
            if (selectedsubmissionids.length) {
                url += '&' + $.param({selectedsubmissionids: selectedsubmissionids});
            }
        }
        window.location.href = url;
    }

    function add_button_grade() {
        var buttons = [];
        $("button.grade-action").each(function() {
            var button_grade = {
                'text': $(this).html(),
                'action':  function(e, dt, node, conf) {
                    grade_action_redirect(e, dt, node, conf);
                }
            };
            buttons.push(button_grade);
            array_button_grade[$(this).html()] = $(this).attr('data-url');
        });

        if($('div.printcoversheet').length) {
            var button_grade = {
                'text': $('div.printcoversheet form input[type=submit]').val(),
                'action':  function(e, dt, node, conf) {
                    $('div.printcoversheet form input[type=submit]').click();
                }
            };
            buttons.push(button_grade);
        }

        if($('div.setattendancedate').length) {
            var button_grade = {
                'text': $('div.setattendancedate form input[type=submit]').val(),
                'action':  function(e, dt, node, conf) {
                    $('div.setattendancedate form input[type=submit]').click();
                }
            };
            buttons.push(button_grade);
        }

        if(typeof buttons !== 'undefined' && buttons.length > 0) {
            return {
                extend: 'collection',
                text: $('#grading_action_button').val() || 'Grading Action',
                buttons: buttons
            }
        }
        return 0;
    };

    require.config({
        paths: {
            'jquery':                   base_url + 'jquery-3.3.1.min',
            'datatables.net':           base_url + 'jquery.datatables',
            'datatables.searchpanes':   base_url + 'datatables.searchpanes',
            'datatables.buttons':       base_url + 'datatables.buttons',
            'datatables.select':        base_url + 'datatables.select',
            'datatables.responsive':    base_url + 'datatables.responsive.min',
            'jquery-mousewheel': base_url +'jquery.mousewheel',
            'datetimepicker':    base_url + 'jquery.datetimepicker',

        }
    });

    require(['jquery', 'datatables.net'], function ($, DataTable) {

        $.fn.DataTable = DataTable;
        $.fn.DataTableSettings = DataTable.settings;
        $.fn.dataTableExt = DataTable.ext;
        DataTable.$ = $;
        $.fn.DataTable = function ( opts ) {
            return $(this).dataTable( opts ).api();
        };

        require(['jquery', 'datatables.searchpanes'], function($) {
            require(['jquery', 'datatables.select'], function($) {
                require(['jquery', 'datatables.buttons'], function($) {
                    require(['jquery', 'datatables.responsive'], function($) {
                        if(isMobileDevice() && $(window).width() < 768) {
                            is_responsive = true;
                            initDatatable(is_responsive);

                            $('.datatabletest').on('order.dt', function(e) {
                                $('.submissionrowmulti').removeClass("shown");
                            });
                        }
                        else {
                            initDatatable(is_responsive);
                        }
                    });
                });
            });
        });
    });

    /**
     *
     * @param tableid
     */
    function background_load_table(tableid) {
        var tableelement = $('#' + tableid);
        var wrapperelement = tableelement.parent('.dataTables_wrapper');
        var paginationelement = wrapperelement.find('.dataTables_paginate');
        var tableobject = table_obj_list[tableid];

        // hide buttons
        wrapperelement.find('.dataTables_paginate, .dataTables_info, .dataTables_length, .dataTables_filter').css('visibility', 'hidden');
        wrapperelement.find('thead, .dt-button').each(function() {
            var me = $(this);
            me.css('pointer-events', 'none');
            if (me.hasClass('dt-button')) {
                me.find('span').html(' ' + me.find('span').html());
            }
        });
        $('<div class="text-center pagination-loading"><i class="fa fa-spinner fa-spin"></i> ' + langmessage.loadingpagination + '</div>').insertAfter(paginationelement);
        $('<i class="fa fa-spinner fa-spin pagination-loading"></i>').insertBefore(wrapperelement.find('.dt-button > span'));

        // prepare params for ajax request
        var params = {
            group: tableelement.attr('group'),
            perpage: tableelement.attr('perpage'),
            sortby: tableelement.attr('sortby'),
            sorthow: tableelement.attr('sorthow'),
            firstnamealpha: tableelement.attr('firstnamealpha'),
            lastnamealpha: tableelement.attr('lastnamealpha'),
            groupnamealpha: tableelement.attr('groupnamealpha'),
            substatus: tableelement.attr('substatus'),
            unallocated: tableelement.attr('unallocated'),
            courseworkid: tableelement.attr('courseworkid'),
        };

        $.ajax({
            url: '/mod/coursework/actions/ajax/datatable/grading.php',
            type: 'POST',
            data: params
        }).done(function(response) {
            tableobject.rows.add($(response)).draw(false);
            tableobject.searchPanes.rebuildPane();
        }).fail(function() {}).always(function() {
            // show buttons
            wrapperelement.find('.pagination-loading').remove();
            wrapperelement.find('thead, .dt-button').css('pointer-events', 'auto');
            wrapperelement.find('.dataTables_paginate, .dataTables_info, .dataTables_length, .dataTables_filter').css('visibility', 'visible');
        });
    }

    function initDatatable(is_responsive) {
        $(".datatabletest").each(function () {
            var fullloaded = $(this).hasClass('full-loaded');
            var table =   $(this).DataTable( {
                'order': [],
                stateSave: true,
                language: {
                    searchPanes: {
                        collapse: {0: $('#search_pane_button').val() || 'Filter', _:($('#search_pane_button').val() || 'Filter')+' (%d)'}
                    }
                },
                buttons:[
                    'searchPanes'
                ],
                dom: 'Blfrtip',
                columnDefs:[
                    {
                        searchPanes:{
                            show: false
                        },
                        className: "select-checkbox",
                        targets: 'checkbox_cell',
                        bSortable: false
                    },
                    {
                        searchPanes:{
                            show: false
                        },
                        targets: ['studentname','addition-multiple-button'],
                        bSortable: false
                    },
                    {
                        searchPanes: {
                            show: false
                        },
                        targets: ['lastname_cell','firstname_cell','tableheadpersonaldeadline', 'tableheaddate', 'tableheadfilename', 'tableheadplagiarismalert', 'plagiarism', 'agreedgrade', 'feedbackandgrading', 'provisionalgrade', 'tableheadmoderationagreement']
                    },
                    {
                        searchPanes:{
                            show: true,
                            header: $('#search_pane_group').val() || 'Group',
                        },
                        targets: 'tableheadgroups',
                    },
                    {
                        searchPanes:{
                            show: true,
                            header: $('#search_pane_status').val() || 'Status',
                            getFullText: true,
                        },
                        targets: 'tableheadstatus',
                    },
                    {
                        searchPanes:{
                            show: true,
                            header: $('#search_pane_firstname').val() || 'First Name Initial',
                        },
                        targets: 'firstname_letter_cell',
                    },
                    {
                        searchPanes:{
                            show: true,
                            header: $('#search_pane_lastname').val() || 'Last Name Initial',
                        },
                        targets: 'lastname_letter_cell',
                    },
                    { "visible": false,  "targets": [ 'lastname_letter_cell','firstname_letter_cell', 'lastname_cell','firstname_cell'] }
                ],
                select: {
                    style:    'multi',
                    selector: '.select-checkbox'
                },
                stateSaveParams: function (settings, data) {
                    data.columns = [];
                }

            }).on("click", "th.select-checkbox", function() {
                if ($(this).hasClass("selected")) {
                    table.rows().deselect();
                } else {
                    table.rows().select();
                }
            }).on("select deselect", function() {
                if (table.rows({selected: true}).count() !== table.rows().count()) {
                    $("th.select-checkbox").removeClass("selected");
                } else {
                    $("th.select-checkbox").addClass("selected");
                }
            });
            table.column('.checkbox_cell').visible(true);

            //extends the search filters to enable hide and show of suspended students
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {

                    return ( $(table.row(dataIndex).node()).hasClass('suspendeduser') && display_suspended_gbl==0) ? false :true;

                }
            );


            var showhidesuspendedbutton  =                     //this creates the hide/show suspended students
                {
                    text: 'Show Unenrolled',
                    action: function ( e, dt, node, config ) {

                        //this checks the value of the display suspended global to see if it they show be displayed
                        // the display suspended global is set here and checked by code on line 254
                        if (display_suspended_gbl == 0) {
                            this.text( 'Hide Unenrolled' );
                            display_suspended_gbl   =   1;
                        }
                        else {
                            this.text( 'Show Unenrolled' );
                            display_suspended_gbl   =   0;
                        }

                        //after the display suspended global is set retrieve the table and tell it to redraw itself
                        var tableid = $(".datatabletest").first().attr('id');
                        var tabledatatable    =   $('#' + tableid).DataTable({"retrieve": true});
                        tabledatatable.draw();

                    }
                }


                if ($('.courseworktype').first().attr('value') == 0) {
                    table.button().add(1, showhidesuspendedbutton);
                }

            if(is_responsive) {

                new $.fn.dataTable.Responsive( table, {
                    details: {
                        display: $.fn.DataTable.Responsive.display.childRowImmediate,
                        type: 'none',
                        target: ''
                    }
                } );
            }
            if(id === 0 && button_grade_action !== 0) {
                table.button().add(1,button_grade_action);
            }
            id += 1;
            table_obj_list[$(this).attr('id')] = table;
            if (!fullloaded) {
                background_load_table($(this).attr('id'));
            }
            //redraw the table so suspended users are hidden
            table.draw();
        });

    }

    if(isMobileDevice() && $(window).width() < 768) {
        // For small screens
        $('.datatabletest tbody').on('click', 'td.details-control', function () {
            var tr = $(this).closest("tr");
            var row_id = tr.attr('id').replace('allocatable_', '');
            var table_id = 'assessorfeedbacktable_' + row_id;

            if ($(tr).next('tr.row_assessors').length > 0) {
                $(tr).next('tr.row_assessors').remove();
            }
            else {
                $('<tr class = "submissionrowmultisub row_assessors">'+
                    '<td class="assessors" colspan = "11"><table class="assessors" style="width:95%">' + $('#' + table_id).clone().html() + '</table></td>' +
                '</tr>').insertAfter($(tr));
            }

            $(tr).toggleClass('shown');
            // $("#" + table_id).toggleClass('tbl_assessor_shown');
            // $("#" + table_id).DataTable({ 'dom': '', 'responsive': true });
        });
    }
    else {
        // Add event listener for opening and closing details
        $('.datatabletest tbody').on('click', 'td.details-control', function () {
            var tr = $(this).closest("tr");
            var table_key = $(this).closest('.datatabletest').attr('id');
            var table = table_obj_list[table_key];
            if (table) {
                var row = table.row( tr );

                var row_id = tr.attr('id').replace('allocatable_', '');
                var table_id = 'assessorfeedbacktable_' + row_id;

                if ($('#' + table_id).length > 0) {
                    if ( row.child.isShown() ) {
                        // This row is already open - close it
                        row.child.hide();
                        tr.removeClass('shown');
                    }
                    else {
                        // Open this row
                        // row.child( format(row.data()) ).show();
                        row.child($(
                            '<table class="assessors" width="100%"><tr class = "submissionrowmultisub">'+
                                '<td class="assessors" colspan = "11"><table class="assessors">' + $('#' + table_id).clone().html() + '</table></td>' +
                            '</tr></table>'
                        )).show();
                        tr.addClass('shown');
                    }
                }
            }
        });
    }

    function format(value) {
        // alert(value);
    }

    $('.datatabletest').on('click', '.splitter-firstname, .splitter-lastname', function (event) {
        event.preventDefault();
        var node = $(event.target),
            isAscending = node.hasClass('sorting_asc'),
            currentsort = 'asc', sortby = 'desc';
        if (!isAscending) {
            currentsort = 'desc';
            sortby = 'asc';
        }

        //node.closest('#example').DataTable()
        //    .order( [ sortColumn, sortby ] )
        //    .draw();
        var table_id = $(this).closest('.datatabletest').attr('id');
        table = table_obj_list[table_id];
        var headerclass = $(this).hasClass('splitter-firstname') ? 'firstname_cell' : 'lastname_cell';
        var sortColumn = table.column('.' + headerclass).index();

        table.order([sortColumn, sortby]).draw();

        node.addClass('sorting_' + sortby).removeClass('sorting sorting_' + currentsort);
        node.parent().removeClass('sorting sorting_asc sorting_desc');
        node.siblings().removeClass('sorting_asc sorting_desc').addClass('sorting');
    });

    /*##################################################### PLAGIARISM ALERT EDIT ##############################################################*/

    /* Catch the event of any changes in the form input fields */
    $('#modal_form_plagiarism_alert :input').change(function () {
        form_plagiarism_alert_change = true;
        $('#modal_form_plagiarism_alert .btn_save').removeAttr('disabled');
    });

    function plagiarism_alert_confirm(plagiarism_dlg) {
        var confirm = new M.core.confirm({
            title: 'Confirm',
            question: $('#modal_form_plagiarism_alert .notification_leave_form_message').text(),
            yesLabel: 'Yes',
            noLabel: 'No',
        });

        confirm.on('complete-yes',function() {
            confirm.hide();
            confirm.destroy();
            form_plagiarism_alert_change = false;
            $(plagiarism_dlg).modal('hide');
        });

        confirm.show();
    }

    /* New plagiarism click */
    $('.wrapper_table_submissions').on('click', '.new_plagiarism_flag', function(e) {
        e.preventDefault();

        var self = $(this);
        var submissionid = $(self).data('submissionid');
        var current_user = $(self).closest('tr').find('.user_cell > a:first').text();
        var currentrowid  = $(self).closest('tr').attr('id');

        $('#modal_form_plagiarism_alert .modal-title').html('Plagiarism flagging for ' + current_user);
        $('#modal_form_plagiarism_alert .editor_information').html('');
        $('#modal_form_plagiarism_alert').find('.plagiarism_flag_status option').removeAttr('selected');
        $('#modal_form_plagiarism_alert').find('.plagiarism_flag_status option[value=0]').prop('selected','selected');
        $('#modal_form_plagiarism_alert .plagiarism_flag_comment').val('');
        $('#modal_form_plagiarism_alert .btn_save').attr('data-submissionid', submissionid).attr('data-action', 'create')
            .attr('data-currentrowid', currentrowid).removeAttr('disabled');

        form_plagiarism_alert_change = false;
        $('#modal_form_plagiarism_alert')
            .on('hide.bs.modal', function(e) {
                // No idea, why this event sometimes fires several times
                if ($('.moodle-dialogue-base.moodle-dialogue-confirm[aria-hidden=false]').length > 0) {
                    return false;
                }
                var self = this;
                if(form_plagiarism_alert_change) {
                    plagiarism_alert_confirm(self);
                    return false;
                }
                return true;
            })
            .modal('show');
    });

    /* Click on the editing an existing plagiarism */
    $('.wrapper_table_submissions').on('click', '.existing_plagiarism_flag', function(e) {
        e.preventDefault();

        var self = $(this);

        var createddby      = $(self).data('createddby');
        var lasteditedby    = $(self).data('lasteditedby');
        var timemodified    = $(self).data('timemodified');
        var statusvalue     = $(self).data('statusvalue');
        var comment         = $(self).data('comment');
        var flagid          = $(self).data('flagid');
        var submissionid    = $(self).closest('tr').find('.submissionid').data('submissionid');
        var currentrowid    = $(self).closest('tr').attr('id');
        var current_user    = $(self).closest('tr').find('.user_cell > a:first').text();
        var submission_published = $(self).data('submission_published');

        $('#modal_form_plagiarism_alert .modal-title').html('Plagiarism flagging for ' + current_user);
        $('#modal_form_plagiarism_alert').find('.plagiarism_flag_status option').removeAttr('selected');
        $('#modal_form_plagiarism_alert').find('.plagiarism_flag_status option[value='+statusvalue+']').prop('selected','selected');
        $('#modal_form_plagiarism_alert .plagiarism_flag_comment').val(comment);
        $('#modal_form_plagiarism_alert .btn_save').attr('data-flagid', flagid).attr('data-action', 'edit')
            .attr('data-submissionid', submissionid).attr('data-currentrowid', currentrowid);

        var editor_information = '<table class = "plagiarism-flag-details">';
        editor_information += '<tr><th>Created by </th><td>' + createddby + '</td></tr>';
        editor_information += '<tr><th>Last edited by </th><td>' + lasteditedby + ' on ' + timemodified + '</td></tr></table>';
        if (submission_published == '1') {
            editor_information += '<div class ="alert">' + $('#modal_form_plagiarism_alert .gradereleasedtostudent').text() + '</div>';
        }
        $('#modal_form_plagiarism_alert .editor_information').html(editor_information);

        form_plagiarism_alert_change = false;
        $('#modal_form_plagiarism_alert')
            .on('hide.bs.modal', function(e) {
                // No idea, why this event sometimes fires several times
                if ($('.moodle-dialogue-base.moodle-dialogue-confirm[aria-hidden=false]').length > 0) {
                    return false;
                }
                var self = this;
                if(form_plagiarism_alert_change) {
                    plagiarism_alert_confirm(self);
                    return false;
                }
                return true;
            })
            .modal('show');
    });

    /* Click on the button Save */
    $('#modal_form_plagiarism_alert').on('click', '.btn_save', function(e) {
        e.preventDefault();

        var self = $(this);

        var submissionid            = $(self).attr('data-submissionid');
        var flagid                  = $(self).attr('data-flagid');
        var current_rowid           = $(self).attr('data-currentrowid');
        var plagiarism_flag_status  = $('#modal_form_plagiarism_alert .plagiarism_flag_status').find(":selected").val();
        var plagiarism_flag_comment = $('#modal_form_plagiarism_alert .plagiarism_flag_comment').val();

        var data = {    'plagiarism_flag_status': plagiarism_flag_status,
                        'plagiarism_flag_comment': plagiarism_flag_comment,
                        'submissionid': submissionid,
                        'flagid': flagid,
                        'inlineedit': 'true' };

        var url_edit = '/mod/coursework/actions/plagiarism_flagging/update.php';
        var url_create = '/mod/coursework/actions/plagiarism_flagging/create.php';
        if ($(self).attr('data-action') == 'create') {
            var url = url_create;
        }
        else if ($(self).attr('data-action') == 'edit') {
            var url = url_edit;
        }

        // AJAX POST
        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            beforeSend: function () {
                $('html, body').css('cursor', 'wait');
                $(self).prev('img').css('visibility', 'visible');
                $(self).prop('disabled', true);
                form_plagiarism_alert_change = false;
            },
            success: function (response) {
                // console.log(response);
                $('html, body').css('cursor', 'auto');
                var data_response = JSON.parse(response);
                if (data_response.result == 'success') {
                    // Update the DOM
                    // $(self).closest('.plagiarism_flag_cell').html(response->success_content);

                    // Update the data of datatables too. => It seems, updating the datatables triggering the DOM getting updated too.
                    if (Object.keys(table_obj_list).length > 0) {
                        // Get the first datatable object.
                        var table = table_obj_list[Object.keys(table_obj_list)[0]];
                        var current_row_data = table.row('#' + current_rowid).data();

                        // plagiarism
                        var plagiarismindex = table.column('.tableheadplagiarismalert').index();
                        var current_plagiarism_alert_cell_data = data_response.success_content;
                        current_row_data[plagiarismindex] = current_plagiarism_alert_cell_data;
                        table.row('#' + current_rowid).data(current_row_data);
                    }
                    var notification = new M.core.alert({
                        message: $('#modal_form_plagiarism_alert .plagiarism_alert_saved_info').text(),
                        title: 'Info',
                    });
                    notification.show();
                }
                else {
                    var notification = new M.core.alert({
                        message: $('#modal_form_plagiarism_alert .moderations_inlineedit_error_info').text(),
                        title: 'Info',
                    });
                    notification.show();
                }
            },
            error: function () {
                $('html, body').css('cursor', 'auto');
            },
            complete: function () {
                $('html, body').css('cursor', 'auto');
                $(self).prev('img').css('visibility', 'hidden');
            }
        });
    });

    function plagiarism_alert_move_back_or_next(self, direction) {
        if (Object.keys(table_obj_list).length > 0) {

            var last_rowid = $(self).closest('.modal-footer').find('.btn_save').attr('data-currentrowid'); // => it seems $('.btn_save').data('current_rowid') returning cached value

            // Get the first datatable object.
            var table = table_obj_list[Object.keys(table_obj_list)[0]];

            var ordereddata = table.rows( { order: 'applied', search: 'applied' } ).data().toArray();
            var last_row_index = ordereddata.findIndex(compare_row, last_rowid);

            do {
                if (direction == 'next') {
                    var target_row_index = last_row_index + 1;
                }
                else if (direction == 'back') {
                    var target_row_index = last_row_index - 1;
                }
                last_row_index = target_row_index;
                var target_row_data = ordereddata[target_row_index];
                if (typeof target_row_data != 'undefined') {
                    var data_found = false;

                    var target_rowid = target_row_data.DT_RowId;

                    // user name
                    var firstnameindex = table.column('.firstname_cell').index();
                    var lastnameindex = table.column('.lastname_cell').index();
                    var current_user = target_row_data[firstnameindex] + ' ' + target_row_data[lastnameindex];

                    // plagiarism
                    var plagiarismindex = table.column('.tableheadplagiarismalert').index();
                    var current_cell_data = target_row_data[plagiarismindex].trim(); // Remove leading and tailing spaces if any
                    if (current_cell_data != '') {
                        $(self).closest('.modal-footer').find('.btn_save').attr('data-currentrowid', target_rowid).removeAttr('disabled');
                        data_found = true;
                        var tmp_node = $('<div/>').html(target_row_data[plagiarismindex]);

                        if ($(tmp_node).find('.new_plagiarism_flag').length > 0) {
                            // Create new moderation agreement
                            var submissionid = $(tmp_node).find('.new_plagiarism_flag').data('submissionid');
                            $(self).closest('.modal-footer').find('.btn_save').attr('data-action', 'create')
                                .attr('data-submissionid', submissionid);

                            $('#modal_form_plagiarism_alert').find('.plagiarism_flag_status option').removeAttr('selected');
                            $('#modal_form_plagiarism_alert').find('.plagiarism_flag_status option[value=0]').prop('selected','selected');
                            $('#modal_form_plagiarism_alert .plagiarism_flag_comment').val('');
                            $('#modal_form_plagiarism_alert .modal-title').html('Plagiarism flagging for ' + current_user);
                            $('#modal_form_plagiarism_alert .editor_information').html('');
                        }
                        else if ($(tmp_node).find('.existing_plagiarism_flag').length > 0) {
                            // Update an existing moderation agreement
                            var statusvalue  = $(tmp_node).find('.existing_plagiarism_flag').data('statusvalue');
                            var comment      = $(tmp_node).find('.existing_plagiarism_flag').data('comment');
                            var flagid       = $(tmp_node).find('.existing_plagiarism_flag').data('flagid');
                            var createddby   = $(tmp_node).find('.existing_plagiarism_flag').data('createddby');
                            var lasteditedby = $(tmp_node).find('.existing_plagiarism_flag').data('lasteditedby');
                            var timemodified = $(tmp_node).find('.existing_plagiarism_flag').data('timemodified');
                            var submission_published = $(tmp_node).find('.existing_plagiarism_flag').data('submission_published');

                            $('#modal_form_plagiarism_alert').find('.plagiarism_flag_status option').removeAttr('selected');
                            $('#modal_form_plagiarism_alert').find('.plagiarism_flag_status option[value='+statusvalue+']').prop('selected','selected');
                            $('#modal_form_plagiarism_alert .plagiarism_flag_comment').val(comment);
                            $('#modal_form_plagiarism_alert .modal-title').html('Plagiarism flagging for ' + current_user);
                            var editor_information = '<table class = "plagiarism-flag-details">';
                            editor_information += '<tr><th>Created by </th><td>' + createddby + '</td></tr>';
                            editor_information += '<tr><th>Last edited by </th><td>' + lasteditedby + ' on ' + timemodified + '</td></tr></table>';
                            if (submission_published == '1') {
                                editor_information += '<div class ="alert">' + $('#modal_form_plagiarism_alert .gradereleasedtostudent').text() + '</div>';
                            }
                            $('#modal_form_plagiarism_alert .editor_information').html(editor_information);

                            $(self).closest('.modal-footer').find('.btn_save').attr('data-flagid', flagid)
                                .attr('data-action', 'edit');
                        }
                        form_plagiarism_alert_change = false;
                    }
                    // console.log(JSON.stringify(target_row_data));
                    if (data_found) {
                        break; // break the while loop
                    }
                }
                else {
                    var notification = new M.core.alert({
                        message: $('#modal_form_plagiarism_alert .plagiarism_inlineedit_no_more_info').text(),
                        title: 'Info',
                    });
                    notification.show();

                    break; // break the while loop. Row index gets to the edge and no more found
                }
            }
            while (data_found == false);

        }
    }

    /* Clicking on Plagiarism Alert Back button */
    $('#modal_form_plagiarism_alert').on('click', '.btn_back', function(e) {

        var self = $(this);

        if (form_plagiarism_alert_change) {
            var confirm = new M.core.confirm({
                title: 'Confirm',
                question: $('#modal_form_plagiarism_alert .notification_leave_form_message').text(),
                yesLabel: 'Yes',
                noLabel: 'No',
            });

            confirm.on('complete-yes',function() {
                confirm.hide();
                confirm.destroy();
                plagiarism_alert_move_back_or_next(self, 'back');
            });

            confirm.show();
        }
        else {
            plagiarism_alert_move_back_or_next(self, 'back');
        }
    });

    /* Clicking on Plagiarism Alert Next button */
    $('#modal_form_plagiarism_alert').on('click', '.btn_next', function(e) {

        var self = $(this);

        if (form_plagiarism_alert_change) {
            var confirm = new M.core.confirm({
                title: 'Confirm',
                question: $('#modal_form_plagiarism_alert .notification_leave_form_message').text(),
                yesLabel: 'Yes',
                noLabel: 'No',
            });

            confirm.on('complete-yes',function() {
                confirm.hide();
                confirm.destroy();
                plagiarism_alert_move_back_or_next(self, 'next');
            });

            confirm.show();
        }
        else {
            plagiarism_alert_move_back_or_next(self, 'next');
        }
    });

    /*##################################################### END OF PLAGIARISM ALERT EDIT ##############################################################*/





    /*##################################################### MODERATION EDIT ###########################################################################*/

    $('#modal_new_moderation_agreement :input').change(function () {
        form_moderation_agreement_change = true;
        $('#modal_new_moderation_agreement .btn_save').removeAttr('disabled');
    });

    function moderation_agreement_confirm(moderation_dlg) {
        var confirm = new M.core.confirm({
            title: 'Confirm',
            question: $('#modal_new_moderation_agreement .notification_leave_form_message').text(),
            yesLabel: 'Yes',
            noLabel: 'No',
        });

        confirm.on('complete-yes',function() {
            confirm.hide();
            confirm.destroy();
            form_moderation_agreement_change = false;
            $(moderation_dlg).modal('hide');
        });

        confirm.show();
    }

    $('.wrapper_table_submissions').on('click', '.new_moderation', function(e) {
        e.preventDefault();

        var self = $(this);
        $('#modal_new_moderation_agreement .btn_save').removeAttr('disabled');
        var current_user = $(self).closest('tr').find('.user_cell > a:first').text();

        $('#modal_new_moderation_agreement .modal-title').html('Moderation for ' + current_user);
        var submissionid = $(self).data('submissionid');
        var courseworkid = $(self).data('courseworkid');
        var feedbackid   = $(self).data('feedbackid');
        var current_rowid = $(self).closest('tr').attr('id');
        var moderatorname = $(self).data('moderatorname');

        $('#modal_new_moderation_agreement .btn_save').attr('data-submissionid', submissionid).attr('data-courseworkid', courseworkid)
            .attr('data-feedbackid', feedbackid).attr('data-current_rowid', current_rowid);

        var editor_information = '<table class = "moderating-details">';
        editor_information += '<tr><th>Moderator</th><td>' + moderatorname + '</td></tr>';
        editor_information += '</table>';
        $('#modal_new_moderation_agreement .editor_information').html(editor_information);

        form_moderation_agreement_change = false;
        $('#modal_new_moderation_agreement')
            .on('hide.bs.modal', function(e) {
                // No idea, why this event sometimes fires several times
                if ($('.moodle-dialogue-base.moodle-dialogue-confirm[aria-hidden=false]').length > 0) {
                    return false;
                }
                var self = this;
                if(form_moderation_agreement_change) {
                    moderation_agreement_confirm(self);
                    return false;
                }
                return true;
            })
            .modal('show');
    });

    /* Clicking on Moderation Agreement Save button */
    $('#modal_new_moderation_agreement').on('click', '.btn_save', function() {

        var self         = $(this);
        var submissionid = $(self).attr('data-submissionid');
        var current_rowid  = $(self).attr('data-current_rowid');
        var moderation_agreement = $('#modal_new_moderation_agreement .moderation_agreement').find(":selected").val();
        moderation_agreement_comment = $('#modal_new_moderation_agreement .moderation_agreement_comment').val();
        var current_action = $(self).attr('data-action');

        if (current_action == 'edit') {
            var data = {    'moderationid': $(self).attr('data-moderationid'),
                            'moderation_agreement': moderation_agreement,
                            'moderation_agreement_comment': moderation_agreement_comment,
                            'inlineedit': 'true'
                        };
            var url = '/mod/coursework/actions/moderations/update.php';
        }
        else {
            var data = {    'submissionid': submissionid,
                            'feedbackid': $(self).attr('data-feedbackid'),
                            'stage_identifier': 'moderator',
                            'moderation_agreement': moderation_agreement,
                            'moderation_agreement_comment': moderation_agreement_comment,
                            'inlineedit': 'true'
                        };
            var url = '/mod/coursework/actions/moderations/create.php';
        }

        // AJAX POST
        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            beforeSend: function () {
                $('html, body').css('cursor', 'wait');
                $(self).prev('img').css('visibility', 'visible');
                $(self).prop('disabled', true);
                form_moderation_agreement_change = false;
            },
            success: function (result) {
                // console.log(result);
                $('html, body').css('cursor', 'auto');
                $(self).prev('img').css('visibility', 'hidden');
                if (result.startsWith('Agreed') || result.startsWith('Disagreed')) {
                    // Update the DOM
                    // if ($('.submissionid[data-submissionid="'+ submissionid +'"]').closest('tr').find('.moderation_agreement_cell').length > 0) {
                    //     $('.submissionid[data-submissionid="'+ submissionid +'"]').closest('tr').find('.moderation_agreement_cell').html(result);
                    // }

                    // Update the data of datatables too. => It seems, updating the datatables triggering the DOM getting updated too.
                    if (Object.keys(table_obj_list).length > 0) {
                        // Get the first datatable object.
                        var table = table_obj_list[Object.keys(table_obj_list)[0]];
                        var current_row_data = table.row('#' + current_rowid).data();

                        var moderationagreementindex = table.column('.tableheadmoderationagreement').index();
                        var current_moderation_cell_data = result;
                        current_row_data[moderationagreementindex] = current_moderation_cell_data;
                        table.row('#' + current_rowid).data(current_row_data);
                    }

                    var notification = new M.core.alert({
                        message: $('#modal_new_moderation_agreement .moderations_inlineedit_saved_info').text(),
                        title: 'Info',
                    });
                    notification.show();
                }
                else {

                    var notification = new M.core.alert({
                        message: $('#modal_new_moderation_agreement .moderations_inlineedit_error_info').text(),
                        title: 'Info',
                    });
                    notification.show();
                }
            },
            error: function () {
                $('html, body').css('cursor', 'auto');
                $(self).prev('img').css('visibility', 'hidden');
            },
            complete: function () {
                $('html, body').css('cursor', 'auto');
                $(self).prev('img').css('visibility', 'hidden');
            }
        });
    });

    /* Clicking on edit icon of Moderation Agreement (pencil) */
    $('.wrapper_table_submissions').on('click', '.existing_moderation', function(e) {
        e.preventDefault();

        var self = $(this);
        $('#modal_new_moderation_agreement .btn_save').removeAttr('disabled');
        var current_user = $(self).closest('tr').find('.user_cell > a:first').text();

        $('#modal_new_moderation_agreement .modal-title').html('Moderation for ' + current_user);
        var moderationid = $(self).data('moderationid');
        var submissionid = $(self).data('submissionid');
        var courseworkid = $(self).data('courseworkid');
        var feedbackid   = $(self).data('feedbackid');
        var modcomment   = $(self).data('modcomment');
        var agreementvalue = $(self).data('agreementvalue');
        var current_rowid  = $(self).closest('tr').attr('id');
        var moderatedby    = $(self).data('moderatedby');
        var lasteditedby   = $(self).data('lasteditedby');
        var timemodified   = $(self).data('timemodified');

        $('#modal_new_moderation_agreement .btn_save').attr('data-submissionid', submissionid).attr('data-courseworkid', courseworkid)
            .attr('data-feedbackid', feedbackid).attr('data-action', 'edit').attr('data-moderationid', moderationid).attr('data-current_rowid', current_rowid);
        $('#modal_new_moderation_agreement').find('.moderation_agreement option').removeAttr('selected');
        $('#modal_new_moderation_agreement').find('.moderation_agreement option[value='+agreementvalue+']').prop('selected','selected');
        $('#modal_new_moderation_agreement .moderation_agreement_comment').val(modcomment);

        var editor_information = '<table class = "moderating-details">';
        editor_information += '<tr><th>Moderated by</th><td>' + moderatedby + '</td></tr>';
        editor_information += '<tr><th>Last edited by</th><td>' + lasteditedby + ' on ' + timemodified + '</td></tr>';
        editor_information += '</table>';
        $('#modal_new_moderation_agreement .editor_information').html(editor_information);

        form_moderation_agreement_change = false;
        $('#modal_new_moderation_agreement')
            .on('hide.bs.modal', function(e) {
                // No idea, why this event sometimes fires several times
                if ($('.moodle-dialogue-base.moodle-dialogue-confirm[aria-hidden=false]').length > 0) {
                    return false;
                }
                var self = this;
                if(form_moderation_agreement_change) {
                    moderation_agreement_confirm(self);
                    return false;
                }
                return true;
            })
            .modal('show');
    });

    function moderation_agreement_move_back_or_next(self, direction) {

        if (Object.keys(table_obj_list).length > 0) {

            var last_rowid = $(self).closest('.modal-footer').find('.btn_save').attr('data-current_rowid'); // => it seems $('.btn_save').data('current_rowid') returning cached value

            // Get the first datatable object.
            var table = table_obj_list[Object.keys(table_obj_list)[0]];

            var ordereddata = table.rows( { order: 'applied', search: 'applied' } ).data().toArray();
            var last_row_index = ordereddata.findIndex(compare_row, last_rowid);

            do {
                if (direction == 'next') {
                    var target_row_index = last_row_index + 1;
                }
                else if (direction == 'back') {
                    var target_row_index = last_row_index - 1;
                }
                last_row_index = target_row_index;
                var target_row_data = ordereddata[target_row_index];
                if (typeof target_row_data != 'undefined') {
                    var data_found = false;

                    var target_rowid = target_row_data.DT_RowId;

                    // user name
                    var firstnameindex = table.column('.firstname_cell').index();
                    var lastnameindex = table.column('.lastname_cell').index();
                    var current_user = target_row_data[firstnameindex] + ' ' + target_row_data[lastnameindex];

                    // plagiarism
                    var moderationagreementindex = table.column('.tableheadmoderationagreement').index();
                    var current_cell_data = target_row_data[moderationagreementindex].trim(); // Remove leading and tailing spaces if any
                    if (current_cell_data != '') {
                        $(self).closest('.modal-footer').find('.btn_save').attr('data-current_rowid', target_rowid).removeAttr('disabled');
                        data_found = true;
                        var tmp_node = $('<div/>').html(target_row_data[moderationagreementindex]);

                        if ($(tmp_node).find('.new_moderation').length > 0) {
                            // Create new moderation agreement
                            var submissionid = $(tmp_node).find('.new_moderation').data('submissionid');
                            var feedbackid = $(tmp_node).find('.new_moderation').data('feedbackid');
                            var moderatorname = $(tmp_node).find('.new_moderation').data('moderatorname');
                            $(self).closest('.modal-footer').find('.btn_save').attr('data-feedbackid', feedbackid)
                                .attr('data-submissionid', submissionid).attr('data-action', 'add_new');

                            $('#modal_new_moderation_agreement').find('.moderation_agreement option').removeAttr('selected');
                            $('#modal_new_moderation_agreement').find('.moderation_agreement option[value=agreed]').prop('selected','selected');
                            $('#modal_new_moderation_agreement .moderation_agreement_comment').val('');
                            $('#modal_new_moderation_agreement .modal-title').html('Moderation for ' + current_user);
                            var editor_information = '<table class = "moderating-details">';
                            editor_information += '<tr><th>Moderator</th><td>' + moderatorname + '</td></tr>';
                            editor_information += '</table>';
                            $('#modal_new_moderation_agreement .editor_information').html(editor_information);
                        }
                        else if ($(tmp_node).find('.existing_moderation').length > 0) {
                            // Update an existing moderation agreement
                            var agreementvalue  = $(tmp_node).find('.existing_moderation').data('agreementvalue');
                            var modcomment      = $(tmp_node).find('.existing_moderation').data('modcomment');
                            var moderationid    = $(tmp_node).find('.existing_moderation').data('moderationid');
                            var submissionid    = $(tmp_node).find('.existing_moderation').data('submissionid');
                            var moderatedby     = $(tmp_node).find('.existing_moderation').data('moderatedby');
                            var lasteditedby    = $(tmp_node).find('.existing_moderation').data('lasteditedby');
                            var timemodified    = $(tmp_node).find('.existing_moderation').data('timemodified');

                            $('#modal_new_moderation_agreement').find('.moderation_agreement option').removeAttr('selected');
                            $('#modal_new_moderation_agreement').find('.moderation_agreement option[value='+agreementvalue+']').prop('selected','selected');
                            $('#modal_new_moderation_agreement .moderation_agreement_comment').val(modcomment);
                            $('#modal_new_moderation_agreement .modal-title').html('Moderation for ' + current_user);
                            var editor_information = '<table class = "moderating-details">';
                            editor_information += '<tr><th>Moderated by</th><td>' + moderatedby + '</td></tr>';
                            editor_information += '<tr><th>Last edited by</th><td>' + lasteditedby + ' on ' + timemodified + '</td></tr>';
                            editor_information += '</table>';
                            $('#modal_new_moderation_agreement .editor_information').html(editor_information);

                            $(self).closest('.modal-footer').find('.btn_save').attr('data-moderationid', moderationid)
                                .attr('data-submissionid', submissionid).attr('data-action', 'edit');
                        }
                        form_moderation_agreement_change = false;
                    }

                    // console.log(JSON.stringify(target_row_data));
                    if (data_found) {
                        break; // break the while loop
                    }
                }
                else {
                    var notification = new M.core.alert({
                        message: $('#modal_new_moderation_agreement .moderations_inlineedit_no_more_info').text(),
                        title: 'Info',
                    });
                    notification.show();

                    break; // break the while loop. Row index gets to the edge and no more found
                }
            }
            while (data_found == false);

        }
    }

    /* Clicking on Moderation Agreement Next button */
    $('#modal_new_moderation_agreement').on('click', '.btn_next', function(e) {

        var self = $(this);

        if (form_moderation_agreement_change) {
            var confirm = new M.core.confirm({
                title: 'Confirm',
                question: $('#modal_new_moderation_agreement .notification_leave_form_message').text(),
                yesLabel: 'Yes',
                noLabel: 'No',
            });

            confirm.on('complete-yes',function() {
                confirm.hide();
                confirm.destroy();
                moderation_agreement_move_back_or_next(self, 'next');
            });

            confirm.show();
        }
        else {
            moderation_agreement_move_back_or_next(self, 'next');
        }
    });

    /* Clicking on Moderation Agreement Back button */
    $('#modal_new_moderation_agreement').on('click', '.btn_back', function(e) {

        var self = $(this);

        if (form_moderation_agreement_change) {
            var confirm = new M.core.confirm({
                title: 'Confirm',
                question: $('#modal_new_moderation_agreement .notification_leave_form_message').text(),
                yesLabel: 'Yes',
                noLabel: 'No',
            });

            confirm.on('complete-yes',function() {
                confirm.hide();
                confirm.destroy();
                moderation_agreement_move_back_or_next(self, 'back');
            });

            confirm.show();
        }
        else {
            moderation_agreement_move_back_or_next(self, 'back');
        }
    });

    /*##################################################### END OF MODERATION ALERT EDIT ##############################################################*/

});

function isMobileDevice() {
    if ( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
        return true;
    }
    return false;
}

$("#grade-show").click(function(e) {
    e.preventDefault();
    if($("#list-button").hasClass("display-none")) {
        $("#list-button").removeClass("display-none");
    } else {
        $("#list-button").addClass("display-none");
    }
});

$(".grade-action").click(function(e) {
    e.preventDefault();
    var url = $(this).attr("data-url");
    window.location.href = url;
});
>>>>>>> 50f475fc90ed57a62c9dd5bf62b657f8b9598e76
