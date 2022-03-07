/**
 *
 * @package   	local_course_id_generator
 * @Author		Hieu Han(hieu.van.han@gmail.com)
 * @license    	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

jQuery(document).ready(function($) {
	
	if ($('body#page-course-edit').length > 0) {
		var raw_html_dropdowns = '<div class="fcontainer clearfix" id="yui_3_17_2_1_1645079029434_1087"><div id="fitem_id_customfield_coursetype" class="form-group row  fitem"><div class="col-md-3 col-form-label d-flex pb-0 pr-md-0"><label class="d-inline word-break " for="id_customfield_coursetype">Course Type</label><div class="form-label-addon d-flex align-items-center align-self-start"></div></div><div class="col-md-9 form-inline align-items-start felement" data-fieldtype="select" id="yui_3_17_2_1_1645079029434_1086"><select class="custom-select" name="customfield_coursetype" id="id_customfield_coursetype"><option value="" selected="">Choose</option><option value="L">Live (L)</option><option value="O">On Demand (O)</option></select><div class="form-control-feedback invalid-feedback" id="id_error_customfield_coursetype"></div></div></div><div id="fitem_id_customfield_obligatory" class="form-group row  fitem"><div class="col-md-3 col-form-label d-flex pb-0 pr-md-0"><label class="d-inline word-break " for="id_customfield_obligatory">Obligatory</label><div class="form-label-addon d-flex align-items-center align-self-start"></div></div><div class="col-md-9 form-inline align-items-start felement" data-fieldtype="select" id="yui_3_17_2_1_1645079029434_1093"><select class="custom-select" name="customfield_obligatory" id="id_customfield_obligatory"><option value="" selected="">Choose</option><option value="E">Elective (E)</option><option value="M">Mandatory (M)</option></select><div class="form-control-feedback invalid-feedback" id="id_error_customfield_obligatory"></div></div></div></div>';
		$('#fitem_id_idnumber').parent().append($(raw_html_dropdowns));

		$('select#id_customfield_coursetype').change(function() {
			if ( this.value != '') {
				var obligatory = $('#id_customfield_obligatory').find(":selected").attr('value');
				if (obligatory != '') {
					// Format LE0001-001
					get_course_id_number_by_prefix(this.value + obligatory);
				}
			}
			else {
				$('input[id=id_idnumber]').val('');
			}
		});
		
		$('select#id_customfield_obligatory').change(function() {
			if ( this.value != '') {
				var coursetype = $('#id_customfield_coursetype').find(":selected").attr('value');
				if (coursetype != '') {
					// Format LE0001-001
					get_course_id_number_by_prefix(coursetype + this.value);
				}
			}
			else {
				$('input[id=id_idnumber]').val('');
			}
		});
	}
	
	$('#coursecat-management').on('click', 'a[class=action-copy]', function (evt) {
		
		var course_id = $(this).closest("li").attr("data-id");
		
		$.ajax({
			type: "POST",
			url: M.cfg.wwwroot + "/local/course_id_generator/index.php",
			dataType: "html",
			data: { course_id: course_id },
			success: function (response) {
				console.log(response);
				if(response.length != 0) {
					var obj = JSON.parse(response);
					var interval = setInterval(function(){
						if($('input[id=id_idnumber]').length >= 1) {
							if (obj.course_id_number != "") {
								$('input[id=id_idnumber]').val(obj.course_id_number);
							}
							$('input[id=id_shortname]').val(obj.course_shortname);
							clearInterval(interval);
						}
					}, 1000);
				}
			},
			error: function (data, response) {
				console.log(response);
			}
		});
	});

	function get_course_id_number_by_prefix(prefix) {
		$.ajax({
			type: "POST",
			url: M.cfg.wwwroot + "/local/course_id_generator/id_by_prefix.php",
			dataType: "html",
			data: { course_id_prefix: prefix },
		    beforeSend: function () {
		        $('body#page-course-edit').css("cursor", "wait");
		        $('#loading_img').css("opacity", "1");
		    },
			success: function (response) {
				console.log(response);
				if(response.length != 0) {
					var obj = JSON.parse(response);
					if (obj.course_id_number == '') {
						$('input[id=id_idnumber]').val(prefix + "0001-001");
					}
					else {
						$('input[id=id_idnumber]').val(obj.course_id_number);
					}
				}
			},
			error: function (data, response) {
				console.log(response);
			},
		    complete: function () {
		        $('body#page-course-edit').css("cursor", "auto");
		        $('#loading_img').css("opacity", "0");
		    }
		});
	}

	if ($('body#page-course-edit').length > 0) {
		$("input[id=id_shortname]").focusout(function() {
	  		var current_val = $(this).val().trim();
	  		// check if (001) is already appended
	  		if (current_val != '' && !current_val.endsWith("(001)")) {
	  			$(this).val(current_val + " (001)");
	  		}
		});
	}

	$('<img src="' + M.cfg.wwwroot + '/local/course_id_generator/pix/loadding.gif" id="loading_img" width="24" height="24" style="opacity: 0; position: relative; top: 5px; left: 5px;" />').insertAfter($('input[id=id_idnumber]'));
	
});