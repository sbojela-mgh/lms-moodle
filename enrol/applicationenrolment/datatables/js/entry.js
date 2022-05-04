/**
 *
 * @package     enrol_applicationenrolment
 * @Author      Hieu Han(hieu.van.han@gmail.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

var table;
var is_responsive = false;

jQuery(document).ready( function ($) {

    var base_url = M.cfg.wwwroot + '/enrol/applicationenrolment/datatables/js/';

    require.config({
        paths: {
            'jquery':                   base_url + 'jquery-3.3.1.min',
            'datatables.net':           base_url + 'jquery.datatables',
            'datatables.searchpanes':   base_url + 'datatables.searchpanes',
            'datatables.buttons':       base_url + 'datatables.buttons',
            'datatables.select':        base_url + 'datatables.select',
            'datatables.responsive':    base_url + 'datatables.responsive.min',
            'jquery-mousewheel':        base_url + 'jquery.mousewheel',
            'datetimepicker':           base_url + 'jquery.datetimepicker',
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
                        }

                        $(".table_applicationlist").each(function () {
                            table = $(this).DataTable( {
                                'order': [],
                                // pageLength: 50,
                                paging: false,
                                stateSave: true,
                                buttons:[
                                    'searchPanes',
                                    'csv',
                                    {
                                        text: 'Download table as .CSV file',
                                        action: function ( e, dt, node, config ) {
                                            $('input[name=selected_application_ids]').val('all');
                                            $('input[name=download_all]').click();
                                        }
                                    }
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
                                            show: true,
                                        },
                                        targets: ['submission', 'status', 'coursedirector', 'applicants'],
                                    },
                                    { "visible": false,  "targets": ['applicants'] }
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


                            if(is_responsive) {

                                new $.fn.dataTable.Responsive( table, {
                                    details: {
                                        display: $.fn.DataTable.Responsive.display.childRowImmediate,
                                        type: 'none',
                                        target: ''
                                    }
                                } );
                            }

                        });
                    });
                });
            });
        });
    });

    var ok_to_submit = false;
    $('body').on('click', 'input[name=delete_selected_attempts]', function() {

        if(ok_to_submit) {
            return true;
        }

        if (table.rows({selected: true}).count() > 0 ) {

            var confirm = new M.core.confirm({
                title: 'Confirm',
                question: 'Are you sure you want to delete selected applications?<br><br>',
                yesLabel: 'Yes',
                noLabel: 'No'
            });

            confirm.on('complete-yes', function () {

                var selected_application_ids = new Array();
                var selected_rows = table.rows({ selected: true }).nodes().to$();

                $.each(selected_rows, function() {
                    selected_application_ids.push( $(this).attr('data-applicationid') );
                })

                $('input[name=selected_application_ids]').val(JSON.stringify(selected_application_ids));
                ok_to_submit = true;
                $('input[name=delete_selected_attempts]').click();

            }, self);

            confirm.on('complete-no',function() {
                confirm.hide();
                confirm.destroy();
            });

            confirm.show();
        }
        else {
            require(['core/notification'], function(notification) {
                notification.alert('Info', 'Please select entries to delete.', 'Ok');
            });
        }
        return false;
    });

    $('body').on('click', 'input[name=download_selected_responses]', function() {

        if (table.rows({selected: true}).count() > 0 ) {

            if (table.rows({selected: true}).count() == table.rows().count()) {
                // download all
                $('input[name=selected_application_ids]').val('all');
            }
            else {
                var selected_application_ids = new Array();
                var selected_rows = table.row({ selected: true }).nodes().to$();

                $.each(selected_rows, function() {
                    selected_application_ids.push( $(this).attr('data-applicationid') );
                })

                $('input[name=selected_application_ids]').val(JSON.stringify(selected_application_ids));
            }
        }
        else {
            require(['core/notification'], function(notification) {
                notification.alert('Info', 'Please select entries to download.', 'Ok');
            });
            return false;
        }
        true;
    });

});

function notification_init() {
    // Init stuff ...
}

function isMobileDevice() {
    if ( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
        return true;
    }
    return false;
}