/**
 *
 * @package   	local_course_id_generator
 * @Author		Hieu Han(hieu.van.han@gmail.com)
 * @license    	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

jQuery(document).ready(function($) {
	
	$('input[name=toggle_all_courses]').change(function() {
	    if(this.checked) {
			$('input[name=single_course]').prop('checked', true);
		}
		else {
			$('input[name=single_course]').prop('checked', false);
		}
	});

	$('input[id=bt_filter]').click(function (evt) {
		if ($('#filter_course_name').val() == '' && $('#filter_shortname').val() == '' && $('#filter_courseid').val() == ''
			&& $('#filter_category').val() == '') {
			require(['core/notification'], function(notification) {
			    notification.alert('Error', 'Please fill in the course name or short name or course id or category to filter.', 'Ok');
			});

			return false;
		}

		return true;
	});

	var ok_to_submit = 0;
	$('input[id=bt_rename]').click(function (evt) {

		if(ok_to_submit) {
			return true;
		}

		if ($('#new_coursefullname').val() == '' && $('#new_shortname').val() == '') {

            require(['core/notification'], function(notification) {
			    notification.alert('Error', 'Please fill in the new full name or new short name.', 'Ok');
			});

			return false;
		}

		var arr_selected_courseids = new Array();
		$('input[name=single_course]').each(function(){

			if ($(this).prop('checked')) {
				arr_selected_courseids.push($(this).attr('value'));
			}
		});

		if (arr_selected_courseids.length == 0) {

            require(['core/notification'], function(notification) {
			    notification.alert('Error', 'No any selected courses to rename.', 'Ok');
			});
			return false;
		}

		var str_courses = arr_selected_courseids.length == 1 ? ' course' : ' courses';

		var confirm = new M.core.confirm({
			title: 'Confirm',
			question: 'Are you sure you want to rename ' + arr_selected_courseids.length + str_courses + "?<br><br>",
			yesLabel: 'Yes',
			noLabel: 'No'
		});

		confirm.on('complete-yes', function () {
			$('#hd_selected_courseids').val(JSON.stringify(arr_selected_courseids));
			confirm.hide();
			confirm.destroy();
			ok_to_submit = 1;
			$('input[id=bt_rename]').click();
		}, self);

		confirm.on('complete-no',function() {
		    confirm.hide();
		    confirm.destroy();
		});

		confirm.show();

		return false;
	});

});


function notification_init() {
	// Init stuff ...
}