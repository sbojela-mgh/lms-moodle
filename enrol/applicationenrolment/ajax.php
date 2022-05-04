<<<<<<< HEAD
<?php
/**
 *
 * @package     enrol_applicationenrolment
 * @Author      Hieu Han(hieu.van.han@gmail.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

global $CFG, $DB, $USER;

require_login();

$action = required_param('action', PARAM_ALPHAEXT);

if (confirm_sesskey()) {

    switch ($action) {

        case 'loadapplylayout':

            $courseid = required_param('courseid', PARAM_INT);

            if (!$DB->record_exists('enrol', ['courseid' => $courseid, 'enrol' => 'applicationenrolment', 'status' => 0])) {
                echo "application_enrollment_not_apply";
                exit;
            }

            // Check if the application limit number or enrol-enddate has been reached
            $sql = "SELECT COUNT(*) AS application_number, e.customint1 AS application_limit
                    FROM {student_apply_course} sac
                    JOIN {enrol} e ON sac.courseid = e.courseid
                    WHERE application_state = 'Pending'
                        AND e.enrol = 'applicationenrolment'
                        AND sac.courseid = $courseid";
            $result = $DB->get_record_sql($sql, []);
            if (!empty($result->application_number) && !empty($result->application_limit)) {
                if (intval($result->application_number) >= intval($result->application_limit)) {
                    $application_limit_reached = true;
                }
            }

            $context = context_course::instance($courseid);
            $is_enrolled = is_enrolled($context, $USER->id, '', true);

            if ($is_enrolled == false) {
                $course = $DB->get_record('course', ['id' => $courseid]);
                if(!empty($course)) {

                    $tags = \core_tag_tag::get_item_tags_array('core', 'course', $courseid);


                    $application = $DB->get_record('student_apply_course', ['courseid' => $courseid, 'studentid' => $USER->id]);
                    if (empty($application)) {
                        // Student didn't apply for this course yet
                        $button_text = 'Apply';
                        $application_url = new moodle_url('/enrol/applicationenrolment/loadapplicationform.php',
                                        ['courseid' => $courseid, 'applicationid' => 0]);
                    }
                    else {
                        // Student already applied for this course, check the state...
                        $button_text = $application->application_button_state;
                        $application_url = new moodle_url('/enrol/applicationenrolment/loadapplicationform.php',
                                        ['courseid' => $courseid, 'applicationid' => $application->id]);
                    }

                    echo "
                            <h2>Enrollment options</h2>
                            <div class='wrapper_apply_content'>
                                <p class='title'>$course->fullname</p>
                                <p>Start: ". ($course->startdate != 0 ? userdate($course->startdate, '%B %d, %Y %I:%M %p') : '') ."</p>
                                <p>End: ". ($course->enddate != 0 ? userdate($course->enddate, '%B %d, %Y %I:%M %p') : '') ."</p>
                                <p>Description: ". strip_tags($course->summary) ."</p>
                                <p>Tags: ". implode(',', $tags) ."</p>
                            </div>
                            <div class='wrapper_legend'>
                                <legend class='legend_app'>
                                    <a class='legend_title' href='#'><i class='icon fa fa-caret-down'></i>Application Enrollment</a></legend>
                            </div>
                            <div class='wrapper_apply_state'>";
                                $enrolenddate = $DB->get_field('enrol', 'enrolenddate', ['enrol'=>'applicationenrolment', 'courseid'=>$courseid]);
                                if (!empty($enrolenddate)) {
                                    echo '<p>Due: '. userdate($enrolenddate, '%B %d, %Y at %I:%M %p') .'</p>';
                                }
                                // if the enrollment duedate reached or passed, no more new applications, also not to allow saved applications to submit
                                if ( empty($application) && !empty($enrolenddate) && time() > $enrolenddate) {
                                    // No more new applications
                                    echo '<p>Application is closed.</p>';
                                }
                                else if ( !empty($application) && !empty($enrolenddate) && time() > $enrolenddate  && $application->application_button_state != 'Submitted') {
                                    // Not to allow saved applications to submit
                                    echo "<a href='javascript:;' class='apply_button missedduedate'>". $button_text ."</a><br/>";
                                    echo "<span class='application_status'><b>Application Status:</b> ". $application->application_state ."</span>";
                                }
                                else if (empty($application_limit_reached) ||
                                   (!empty($application_limit_reached) && $button_text != 'Apply') ) {
                                    echo "<a href='". $application_url->out(false) ."' class='apply_button'>". $button_text ."</a><br/>";
                                    if (!empty($application)) {
                                        if ($application->assessment_state == 'Denied') {
                                            echo "<div><span class='application_status'><b>Application Status:</b> Denied</span></div>";
                                            echo "<div><textarea name='textarea_denied'>$application->comment</textarea></div>";
                                        }
                                        else {
                                            echo "<span class='application_status'><b>Application Status:</b> ". $application->application_state ."</span>";
                                        }
                                    }
                                }
                                else {
                                    // Student didn't apply for this course yet and the max number of applications reached
                                    echo get_string('application_limit_reached', 'enrol_applicationenrolment');
                                }
                    echo   "</div>
                            <style type='text/css'>
                                .coursebox { padding-left: 0; }
                                .wrapper_apply_content p { margin: 0; padding: 1px 0; }
                                .wrapper_apply_content a { text-decoration: none; }
                                .wrapper_apply_content .title { color: #0f6cbf; font-size: 24px; }
                                .wrapper_legend { margin-top: 40px; border-bottom: 1px solid; padding-bottom: 20px; }
                                .wrapper_legend .icon { color: #a99; margin-left: 0; }
                                .wrapper_legend .legend_app a:hover { text-decoration: none; }
                                .wrapper_apply_state { padding: 25px 0; text-align: center; }
                                .wrapper_apply_state a.apply_button { display: inline-block; margin: 0 auto; color: #fff; text-decoration: none; padding: 6px 20px; background-color: #0f6cbf; }
                                .wrapper_apply_state a.apply_button:hover { text-decoration: none; }
                                .wrapper_apply_state .application_status { display: inline-block; margin-top: 20px; background: #bfe1dd; padding: 4px 15px; }
                                div[role=main] { position: relative; }
                                .wrapper_ratings { display: inline-block; position: absolute; top: 0; right: 0; }
                                [name=textarea_denied] { width: 600px; height: 120px; margin: 20px auto; }
                            </style>
                        ";
                }
            }
            else {
                echo '<p>You are already enrolled in this course.</p>';
            }
            $block = block_instance('rate_course');
            echo '<div class="wrapper_ratings">';
            $block->display_rating($courseid);
            echo '</div>';
            break;

        case 'loadquestionbank':

            $sql = "SELECT c.id as courseid, c.shortname, aq.id as questionid, aq.question_content, aq.required
                    FROM {application_question} aq JOIN {course} c ON aq.categoryid = c.id
                    UNION
                    SELECT 0 as courseid, 'System' as shortname, id as questionid, question_content, required
                    FROM {application_question} WHERE categoryid = 0";

            $records = $DB->get_recordset_sql($sql, []);

            $course_shortnames = [];
            $questions = [];
            foreach ($records as $key => $value) {
                $course_shortnames[$key] = $value->shortname;

                $question_content = json_decode($value->question_content);
                $question_type  = $question_content->question_type;
                $question_name  = $question_content->question_name;
                $question_text  = strip_tags($question_content->question_text->text);
                $questionid     = $value->questionid;
                $required       = $value->required;

                if(empty($questions[$key])) {
                    $questions[$key] = [];
                }
                $questions[$key][] = [
                                        'questionid' => $questionid,
                                        'question_type' => $question_type,
                                        'question_name' => $question_name,
                                        'question_text' => $question_text,
                                        'required'      => $required,
                                    ];
            }

            if (count($course_shortnames) > 0) {
                echo json_encode(['course_shortnames' => $course_shortnames, 'questions' => $questions]);
            }
            else {
                echo '';
            }
            break;

        case 'loadquestionpreview':

            $questionid = required_param('questionid', PARAM_INT);

            $record = $DB->get_record('application_question', ['id' => $questionid]);

            if (!empty($record)) {

                $question_content = json_decode($record->question_content);

                if ($question_content->question_type == 'multiple_choices') {

                    echo '<p class="preview_questionname">' . $question_content->question_name . '</p>';
                    echo '<div class="preview_questiontext">' . $question_content->question_text->text . '</div>';

                    foreach($question_content->choices as $choice) {
                        echo '<p><input type="radio" name="preview_choice" /> &nbsp;'. $choice .'</p>';

                    }
                }
                else if ($question_content->question_type == 'text') {
                    echo '<p class="preview_questionname">' . $question_content->question_name . '</p>';
                    echo '<div class="preview_questiontext">' . $question_content->question_text->text . '</div>';
                }
            }

            echo '';
            break;
    }
    exit;
}
else {
    echo '<h2>Naughty!</h2>';
}


=======
<?php
/**
 *
 * @package     enrol_applicationenrolment
 * @Author      Hieu Han(hieu.van.han@gmail.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

global $CFG, $DB, $USER;

require_login();

$action = required_param('action', PARAM_ALPHAEXT);

if (confirm_sesskey()) {

    switch ($action) {

        case 'loadapplylayout':

            $courseid = required_param('courseid', PARAM_INT);

            if (!$DB->record_exists('enrol', ['courseid' => $courseid, 'enrol' => 'applicationenrolment', 'status' => 0])) {
                echo "application_enrollment_not_apply";
                exit;
            }

            // Check if the application limit number or enrol-enddate has been reached
            $sql = "SELECT COUNT(*) AS application_number, e.customint1 AS application_limit
                    FROM {student_apply_course} sac
                    JOIN {enrol} e ON sac.courseid = e.courseid
                    WHERE application_state = 'Pending'
                        AND e.enrol = 'applicationenrolment'
                        AND sac.courseid = $courseid";
            $result = $DB->get_record_sql($sql, []);
            if (!empty($result->application_number) && !empty($result->application_limit)) {
                if (intval($result->application_number) >= intval($result->application_limit)) {
                    $application_limit_reached = true;
                }
            }

            $context = context_course::instance($courseid);
            $is_enrolled = is_enrolled($context, $USER->id, '', true);

            if ($is_enrolled == false) {
                $course = $DB->get_record('course', ['id' => $courseid]);
                if(!empty($course)) {

                    $tags = \core_tag_tag::get_item_tags_array('core', 'course', $courseid);


                    $application = $DB->get_record('student_apply_course', ['courseid' => $courseid, 'studentid' => $USER->id]);
                    if (empty($application)) {
                        // Student didn't apply for this course yet
                        $button_text = 'Apply';
                        $application_url = new moodle_url('/enrol/applicationenrolment/loadapplicationform.php',
                                        ['courseid' => $courseid, 'applicationid' => 0]);
                    }
                    else {
                        // Student already applied for this course, check the state...
                        $button_text = $application->application_button_state;
                        $application_url = new moodle_url('/enrol/applicationenrolment/loadapplicationform.php',
                                        ['courseid' => $courseid, 'applicationid' => $application->id]);
                    }

                    echo "
                            <h2>Enrollment options</h2>
                            <div class='wrapper_apply_content'>
                                <p class='title'>$course->fullname</p>
                                <p>Start: ". ($course->startdate != 0 ? userdate($course->startdate, '%B %d, %Y %I:%M %p') : '') ."</p>
                                <p>End: ". ($course->enddate != 0 ? userdate($course->enddate, '%B %d, %Y %I:%M %p') : '') ."</p>
                                <p>Description: ". strip_tags($course->summary) ."</p>
                                <p>Tags: ". implode(',', $tags) ."</p>
                            </div>
                            <div class='wrapper_legend'>
                                <legend class='legend_app'>
                                    <a class='legend_title' href='#'><i class='icon fa fa-caret-down'></i>Application Enrollment</a></legend>
                            </div>
                            <div class='wrapper_apply_state'>";
                                $enrolenddate = $DB->get_field('enrol', 'enrolenddate', ['enrol'=>'applicationenrolment', 'courseid'=>$courseid]);
                                if (!empty($enrolenddate)) {
                                    echo '<p>Due: '. userdate($enrolenddate, '%B %d, %Y at %I:%M %p') .'</p>';
                                }
                                // if the enrollment duedate reached or passed, no more new applications, also not to allow saved applications to submit
                                if ( empty($application) && !empty($enrolenddate) && time() > $enrolenddate) {
                                    // No more new applications
                                    echo '<p>Application is closed.</p>';
                                }
                                else if ( !empty($application) && !empty($enrolenddate) && time() > $enrolenddate  && $application->application_button_state != 'Submitted') {
                                    // Not to allow saved applications to submit
                                    echo "<a href='javascript:;' class='apply_button missedduedate'>". $button_text ."</a><br/>";
                                    echo "<span class='application_status'><b>Application Status:</b> ". $application->application_state ."</span>";
                                }
                                else if (empty($application_limit_reached) ||
                                   (!empty($application_limit_reached) && $button_text != 'Apply') ) {
                                    echo "<a href='". $application_url->out(false) ."' class='apply_button'>". $button_text ."</a><br/>";
                                    if (!empty($application)) {
                                        if ($application->assessment_state == 'Denied') {
                                            echo "<div><span class='application_status'><b>Application Status:</b> Denied</span></div>";
                                            echo "<div><textarea name='textarea_denied'>$application->comment</textarea></div>";
                                        }
                                        else {
                                            echo "<span class='application_status'><b>Application Status:</b> ". $application->application_state ."</span>";
                                        }
                                    }
                                }
                                else {
                                    // Student didn't apply for this course yet and the max number of applications reached
                                    echo get_string('application_limit_reached', 'enrol_applicationenrolment');
                                }
                    echo   "</div>
                            <style type='text/css'>
                                .coursebox { padding-left: 0; }
                                .wrapper_apply_content p { margin: 0; padding: 1px 0; }
                                .wrapper_apply_content a { text-decoration: none; }
                                .wrapper_apply_content .title { color: #0f6cbf; font-size: 24px; }
                                .wrapper_legend { margin-top: 40px; border-bottom: 1px solid; padding-bottom: 20px; }
                                .wrapper_legend .icon { color: #a99; margin-left: 0; }
                                .wrapper_legend .legend_app a:hover { text-decoration: none; }
                                .wrapper_apply_state { padding: 25px 0; text-align: center; }
                                .wrapper_apply_state a.apply_button { display: inline-block; margin: 0 auto; color: #fff; text-decoration: none; padding: 6px 20px; background-color: #0f6cbf; }
                                .wrapper_apply_state a.apply_button:hover { text-decoration: none; }
                                .wrapper_apply_state .application_status { display: inline-block; margin-top: 20px; background: #bfe1dd; padding: 4px 15px; }
                                div[role=main] { position: relative; }
                                .wrapper_ratings { display: inline-block; position: absolute; top: 0; right: 0; }
                                [name=textarea_denied] { width: 600px; height: 120px; margin: 20px auto; }
                            </style>
                        ";
                }
            }
            else {
                echo '<p>You are already enrolled in this course.</p>';
            }
            $block = block_instance('rate_course');
            echo '<div class="wrapper_ratings">';
            $block->display_rating($courseid);
            echo '</div>';
            break;

        case 'loadquestionbank':

            $sql = "SELECT c.id as courseid, c.shortname, aq.id as questionid, aq.question_content, aq.required
                    FROM {application_question} aq JOIN {course} c ON aq.categoryid = c.id
                    UNION
                    SELECT 0 as courseid, 'System' as shortname, id as questionid, question_content, required
                    FROM {application_question} WHERE categoryid = 0";

            $records = $DB->get_recordset_sql($sql, []);

            $course_shortnames = [];
            $questions = [];
            foreach ($records as $key => $value) {
                $course_shortnames[$key] = $value->shortname;

                $question_content = json_decode($value->question_content);
                $question_type  = $question_content->question_type;
                $question_name  = $question_content->question_name;
                $question_text  = strip_tags($question_content->question_text->text);
                $questionid     = $value->questionid;
                $required       = $value->required;

                if(empty($questions[$key])) {
                    $questions[$key] = [];
                }
                $questions[$key][] = [
                                        'questionid' => $questionid,
                                        'question_type' => $question_type,
                                        'question_name' => $question_name,
                                        'question_text' => $question_text,
                                        'required'      => $required,
                                    ];
            }

            if (count($course_shortnames) > 0) {
                echo json_encode(['course_shortnames' => $course_shortnames, 'questions' => $questions]);
            }
            else {
                echo '';
            }
            break;

        case 'loadquestionpreview':

            $questionid = required_param('questionid', PARAM_INT);

            $record = $DB->get_record('application_question', ['id' => $questionid]);

            if (!empty($record)) {

                $question_content = json_decode($record->question_content);

                if ($question_content->question_type == 'multiple_choices') {

                    echo '<p class="preview_questionname">' . $question_content->question_name . '</p>';
                    echo '<div class="preview_questiontext">' . $question_content->question_text->text . '</div>';

                    foreach($question_content->choices as $choice) {
                        echo '<p><input type="radio" name="preview_choice" /> &nbsp;'. $choice .'</p>';

                    }
                }
                else if ($question_content->question_type == 'text') {
                    echo '<p class="preview_questionname">' . $question_content->question_name . '</p>';
                    echo '<div class="preview_questiontext">' . $question_content->question_text->text . '</div>';
                }
            }

            echo '';
            break;
    }
    exit;
}
else {
    echo '<h2>Naughty!</h2>';
}


>>>>>>> 50f475fc90ed57a62c9dd5bf62b657f8b9598e76
