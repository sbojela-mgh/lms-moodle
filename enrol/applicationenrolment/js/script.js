/**
 *
 * @package   	enrol_applicationenrolment
 * @Author		Hieu Han(hieu.van.han@gmail.com)
 * @license    	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

jQuery(document).ready(function($) {

    if (window.location.href.indexOf(M.cfg.wwwroot + "/enrol/index.php?") == 0) {
    	var courseid = GetURLParameter('id');
		$.ajax({
			type: "POST",
			url: M.cfg.wwwroot + "/enrol/applicationenrolment/ajax.php",
			dataType: "html",
			data: { action: "loadapplylayout", courseid: courseid, sesskey: M.cfg.sesskey },
		    beforeSend: function () {
		    },
			success: function (response) {
				if(response.length != 0) {
					if (response.trim() == 'application_enrollment_not_apply') {

					}
					else {
						$("div[role=main]").html(response);
					}
				}
			},
			error: function (data, response) {
				console.log(response);
			},
		    complete: function () {
		    }
		});

    	//
    }

	var question_bank;
	
    $(".legend_questions a").click(function (e) { e.preventDefault(); });

    $(".legend_app a.app_title, .legend_app a.legend_title").click(function (e) {
        e.preventDefault();
        if ($(this).find(".icon").hasClass("fa-caret-down")) {
            $(this).find(".icon").removeClass("fa-caret-down");
            $(this).find(".icon").addClass("fa-caret-right");
        }
        else if ($(this).find(".icon").hasClass("fa-caret-right")) {
            $(this).find(".icon").removeClass("fa-caret-right");
            $(this).find(".icon").addClass("fa-caret-down");
        }
        $(this).parent().next("div").slideToggle(300);
    });

	require(['jquery', 'core/modal_factory'], function($, ModalFactory) {
		var trigger = $('#add_a_new_question');

		var html_body = "<p>QUESTIONS</p>";
		html_body += '<input type="radio" id="chk_multiple_choice" name="chk_questiontype" value="multiple_choice"/> <label for="chk_multiple_choice">Multiple choice</label><br>';
		html_body += '<input type="radio" id="chk_text" name="chk_questiontype" value="text"/> <label for="chk_text">Text</label>';

		var html_footer = '<input type="button" value="Add" onclick="onadd_click()" />';
		html_footer += '<input type="button" value="Cancel" onclick="jQuery(\'.close\').click();" />';

		ModalFactory.create({
		    title: 'Choose a question type to add',
		    body: html_body,
		    footer: html_footer,
		}, trigger)
		.done(function(modal) {
			//modal.getRoot().find(".modal-footer").removeClass('hidden');
	  	});
	});

	require(['jquery', 'core/modal_factory'], function($, ModalFactory) {
		if ($('.table_answers').length > 0) {
			var trigger = $('.add_question_from_bank');
			var html_body = '<div class="dynamic_question_body">';
			html_body += '<img src="'+M.cfg.wwwroot+'/enrol/applicationenrolment/pix/loadding.gif" width="32" height="32" />';
			html_body += '</div>';
			var html_footer = '<input type="button" id="addfrombank" value="Add selected questions to the application" onclick="onaddfrombank_click()"/>';

			ModalFactory.create({
				large: true,
			    title: 'Add from the question bank to application',
			    body: html_body,
			    footer: html_footer,
			}, trigger)
			.done(function(modal) {
				//modal.getRoot().find(".modal-footer").removeClass('hidden');
				$.ajax({
					type: "POST",
					url: M.cfg.wwwroot + "/enrol/applicationenrolment/ajax.php",
					dataType: "html",
					data: { action: "loadquestionbank", sesskey: M.cfg.sesskey },
				    beforeSend: function () {
				    },
					success: function (response) {
						console.log(response);
						if(response.length != 0) {
							question_bank = JSON.parse(response);

							var select = $('<select name="dynamic_courseshortnames"><option value=""></option></select>')
							$.each(question_bank['course_shortnames'], function (index, value) {
							  	$(select).append($('<option/>', {
							      	value: index,
							      	text : value
							  	}));
							});

							var wrapper_select = '<div><label>Select a category</label>'+ $("<div />").append($(select)).html()+'</div>';
							var wrapper_questions = '<div class="wrapper_questions"></div>';

							var interval = setInterval(function() {
								if($('.dynamic_question_body').length >= 1) {
									$('.dynamic_question_body').html(wrapper_select + wrapper_questions);
									clearInterval(interval);
								}
							}, 1000);
						}
					},
					error: function (data, response) {
						console.log(response);
					},
				    complete: function () {
				    }
				});
		  	});
	  	}
	});

	$("input[name=bt_add]").click(function (e) {
		var html_newchoice = '<input type="text" class="form-control " name="answer[ORDERNR]" id="id_answer[ORDERNR]" value="" size="48" data-order="[ORDERNR]">'
		var previous = $(this).parent().prev();
		var new_choice = $(previous).clone();

		var order_number = parseInt($(new_choice).find('input').attr('data-order'));
		order_number += 1;
		$(new_choice).find('label').text("Choice " + order_number).end().find("input").attr("data-order", order_number).val('');

		$(new_choice).insertAfter($(previous));
	});

	if($('#id_category').length > 0 && $.isFunction( $.fn.chosen )) {
		$('#id_category').chosen({
	        placeholder: 'Select',
	        allowClear: true
	    });
	}

    require(['jquery', 'core/sortable_list'], function($, SortableList) {
        new SortableList($('.table_answers tbody')[0]);
        var position = 1;
        $(".wrapper_table").on(SortableList.EVENTS.DROP, ".table_answers tr", function (evt, info) {
        	$('.table_answers tr').delay(500).each(function() {
        		if (position > $(".table_answers tr").length) {
        			position = 1;
        		}
	            $(this).find('.quest_order').text(position);
	            position++;
	        });
        });
    });

    $("body").on("click", "#id_submitbutton, #id_saveandstay", function (e) {
    	if ($('.table_answers tr').length > 0) {
	    	var questionids = new Array();
	    	$('.table_answers tr').each(function() {
	    		questionids.push($(this).find("td:first").attr('data-questionid'));
	        });
	        $("input[name=customchar1]").val(questionids.join(","));
	    }
	    if ($('.table_answers').length > 0) {
	    	is_submit_clicked = true;
	    }
    });

    $('body').on('change', 'select[name=dynamic_courseshortnames]', function (evt) {
    	var selected_course = $('select[name=dynamic_courseshortnames]').find(":selected").attr('value');
    	var questions = question_bank["questions"][selected_course];

    	$(".wrapper_questions").html('');

    	var table_questions = '<p style="margin-bottom: 6px;"><b>Questions</b><br/><span style="position: relative; top: 5px; left: 8px;"><input type="checkbox" id="dynamic_questionselectall"/><label for="dynamic_questionselectall">&nbsp; Select all</label></span></p><table border="0" cellpadding="0" cellspacing="0" width="100%">';

    	$.each(questions, function (index, value) {
		  	var html_question = '<tr><td width="3%"><input type="checkbox" name="chk_questtick" value="'+value["questionid"]+'" data-questiontype="'+value["question_type"]+'"/></td>';
		  	var star = (value['required'] == 1 ? '<span class="star">*</span> ' : "");
		  	html_question += '<td width="94%"><b>'+ star + value["question_name"] +'</b> - '+ value["question_text"] +'</td>';
		  	html_question += '<td width="3%"><i class="icon fa fa-search-plus"></i></td>'
		  	html_question += '</tr>';
		  	table_questions += html_question;
		});
		table_questions += "</table>";
		$(".wrapper_questions").html(table_questions);
		$('.modal-body:has(> .dynamic_question_body)').css('background', '#fff');
    });

    $('body').on('change', '#dynamic_questionselectall', function (evt) {
    	$('input[name=chk_questtick]:checkbox').not(this).prop('checked', this.checked);
	});

    $('.table_answers').on('click', '.fa-cog', function() {

    	var questionid = $(this).closest('tr').find('td:first').attr('data-questionid');
    	var questiontype = $(this).closest('tr').find('td:first').attr('data-questiontype');
    	if (questionid == undefined) {
    		questionid = $(this).closest('tr').find('td:first').find('input').attr('value');
    		questiontype = $(this).closest('tr').find('td:first').find('input').attr('data-questiontype');
    	}
    	if (questiontype == 'text') {
    		window.location = url_textquestion + '&questionid=' + questionid;
    	}
    	else if (questiontype == 'multiple_choices') {
    		window.location = url_multiplechoices + '&questionid=' + questionid;
    	}
    });

    $('.table_answers').on('click', '.fa-trash', function() {
    	var cur_node = $(this);
    	var confirm = new M.core.confirm({
			title: 'Confirm',
			question: 'Are you sure you want to delete this question?<br><br>',
			yesLabel: 'Yes',
			noLabel: 'No'
		});

		confirm.on('complete-yes', function () {
			$(cur_node).closest('tr').remove();
			// Recalculate the order
			var order = 1;
			$('.table_answers tr').each(function() {
	            $(this).find('.quest_order').text(order);
	            order++;
	        });
		}, self);

		confirm.on('complete-no',function() {
		    confirm.hide();
		    confirm.destroy();
		});

		confirm.show();
    });

    $('body').on('click', '.table_answers .fa-search-plus, .wrapper_questions .fa-search-plus', function() {

    	var questionid = $(this).closest('tr').find('td:first').attr('data-questionid');
    	if (questionid == undefined) {
    		questionid = $(this).closest('tr').find('td:first').find('input').attr('value');
    	}

    	var loading = '<img src="'+M.cfg.wwwroot+'/enrol/applicationenrolment/pix/loadding.gif" width="32" height="32" class="loadquestionpreview" style="display:block;margin:0 auto;"/>';
    	require(['core/notification'], function(notification) {
		    notification.alert('Question preview', loading, 'Ok');
		});

		$.ajax({
			type: "POST",
			url: M.cfg.wwwroot + "/enrol/applicationenrolment/ajax.php",
			dataType: "html",
			data: { action: "loadquestionpreview", questionid: questionid, sesskey: M.cfg.sesskey },
		    beforeSend: function () {
		    },
			success: function (response) {
				console.log(response);
				if(response.length != 0) {

					var interval = setInterval(function() {
						if($('.modal-dialog:has(.loadquestionpreview)').length >= 1) {
							$('.modal-dialog:has(.loadquestionpreview)').attr('style', "max-width: 700px !important; transition: max-width 0.3s ease-out, height 0.3s ease 0.5s");

							$('.modal-dialog:has(.loadquestionpreview)').find('.modal-body').html(response);
							clearInterval(interval);
						}
					}, 200);
				}
			},
			error: function (data, response) {
				console.log(response);
			},
		    complete: function () {
		    }
		});

    });

    $('body').on('click', '.missedduedate', function() {
    	var tmpclassname = "missedduedateinfo_" + new Date().getTime();
		require(['core/notification'], function(notification) {
			notification.alert('', '<p class="'+tmpclassname+'">You have missed the deadline to submit your application.</p>');
		});
		var interval = setInterval(function() {
			if($('.modal-dialog:has(.'+tmpclassname+')').length >= 1) {
				$('.modal-dialog:has(.'+tmpclassname+')').find('.modal-footer').attr('style', "display: none;");
				var top_y = ($(window).height() - 250)/2;
				$('.modal-dialog:has(.'+tmpclassname+')').each(function() {
					$(this).attr('style', "margin-top: " + top_y + "px;");
				});
				clearInterval(interval);
			}
		}, 10);
    });

});

function onadd_click() {
	var questiontype = jQuery('input[name=chk_questiontype]:checked').val();
	if (questiontype == 'multiple_choice') {
		window.location = url_multiplechoices;
	}
	else if (questiontype == 'text') {
		window.location = url_textquestion;
	}
	else {
		require(['core/notification'], function(notification) {
		    notification.alert('Info', 'Please select a question type.', 'Ok');
		});
	}
}

function onaddfrombank_click() {

	var selected_bank_questions = jQuery('input[name=chk_questtick]:checked');

    if(selected_bank_questions.length > 0) {

		var existing_questionids = new Array();
		$('.table_answers td').each(function() {
			existing_questionids.push($(this).attr('data-questionid'));
	    });

	    var any_new = false;

		jQuery.each(selected_bank_questions, function () {
			var selected_questid = jQuery(this).val();
		  	if ( existing_questionids.includes( selected_questid ) == false) {

		  		any_new = true;

		  		var quest_content = jQuery('input[value='+ selected_questid +']').closest("td").next().html();
		  		var questiontype  = jQuery('input[value='+ selected_questid +']').attr('data-questiontype');

		  		var nr_question = jQuery(".table_answers tbody tr").length;
		  		// var new_row = jQuery(".table_answers tbody tr:last").clone(); // This doesn't work if no row to clone
		  		var new_row = jQuery('<tr><td width="5%" data-questiontype="multiple_choices" data-questionid="1"><span tabindex="0" role="button" aria-haspopup="true" data-drag-type="move" title="Move"><i class="icon fa fa-arrows fa-fw " aria-hidden="true"></i></span><span class="quest_order">3</span></td><td width="90%" class="quest_content"><b><span class="star">*</span> Question name</b> - Question text</td><td width="5%"><span class="action_icons"><i class="icon fa fa-cog"></i><i class="icon fa fa-search-plus"></i><i class="icon fa fa-trash"></i></span></td></tr>');

		  		jQuery(new_row).find("td:first").attr("data-questionid", selected_questid).attr("data-questiontype", questiontype).end().find(".quest_order").text(parseInt(nr_question)+1).end().find(".quest_content").html(quest_content).end().appendTo(jQuery(".table_answers tbody"));

		  		$('.dynamic_question_body').closest('.modal-content').find('button.close').click();
		  	}
		});

		if (any_new == false) {
			require(['core/notification'], function(notification) {
			    notification.alert('Info', 'Your selected questions were already added.', 'Ok');
			});
		}

    }
    else {
    	require(['core/notification'], function(notification) {
		    notification.alert('Info', 'Please select questions to add.', 'Ok');
		});
    }
}

function notification_init() {
	// Init stuff ...
}

var is_submit_clicked = false;

jQuery(window).on("beforeunload", function (event) {

	if ($('.table_answers').length > 0) {
		var new_questionids = new Array();
		if ($('.table_answers tr').length > 0) {
	    	$('.table_answers tr').each(function() {
	    		new_questionids.push(parseInt($(this).find("td:first").attr('data-questionid')));
	        });
	    }
	    var filteredArray = new_questionids.filter(x => !current_questlist.includes(x)).concat(current_questlist.filter(x => !new_questionids.includes(x)));
	    if (filteredArray.length > 0) {
	    	var interval = setInterval(function(){
	    		var attr = $('input[type=submit]').attr('disabled');
				if (typeof attr !== 'undefined' && attr !== false) {
					$('input[type=submit]').removeAttr('disabled');
					clearInterval(interval);
				}
			}, 1000);
			if (is_submit_clicked == false) {
		    	return "Please save your changes first, if you leave the page, changes will be losed.";
		    }
	    }
	}
});

function GetURLParameter(sParam)
{
    var sPageURL = window.location.search.substring(1);
    var sURLVariables = sPageURL.split('&');
    for (var i = 0; i < sURLVariables.length; i++)
    {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] == sParam)
        {
            return sParameterName[1];
        }
    }
}