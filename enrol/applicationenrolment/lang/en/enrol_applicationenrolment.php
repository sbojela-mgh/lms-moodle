<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     enrol_applicationenrolment
 * @author		hieu.van.han@gmail.com
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] 					= 'Application Enrollment';
$string['pluginname_desc'] 				= 'This plugin will allow students to apply for a course and the course director will review to accept or deny';

$string['applicationenrolment:manage']  = 'Manage Application Enrollment instances';
$string['applicationenrolment:config'] 	= 'Manage Application Enrollment instances';
$string['applicationenrolment:enrol'] 	= 'Enrol a user';
$string['applicationenrolment:unenrol']	= 'Unenrol a user';
$string['enrolstartdate']				= 'Start date';
$string['enrolduedate']					= 'Due date';
$string['max_submissions']				= 'Max submissions';
$string['emailtemplate_approved']		= 'Email template (approved)';
$string['emailtemplate_denied']			= 'Email template (denied)';
$string['emailtemplate_reminder']		= 'Email template (reminder)';
$string['pageheading_multiplechoices']	= 'Adding a Multiple choice question';
$string['pageheading_textquestion']		= 'Adding a Text question';
$string['nopermission']                 = "<p>You don't have the permission to access this page</p>";
$string['application_limit_reached']    = '<p>This course has reached the submission limit and is no longer accepting applications.</p>';
$string['comment_review_instruction']   = 'Comments made within this text field will be made available to students if their application is rejected. Otherwise, you can use this text field to keep track of your notes prior to making a decision.';
$string['emailtemplate_approvedcontent']	= 'Dear [Student First Name],<br><br>We are excited to enroll and welcome you to [Course Fullname].<br><br>Please visit the course page [HYPERLINK COURSE URL] to access course content and materials.<br><br>If you have any questions, please reply to this email (DCRCCRE@partners.org).<br><br>Thank you,<br>Division of Clinical Research, Center for Clinical Research Education';
$string['emailtemplate_deniedcontent']	= 'Dear [Student First Name],<br><br>Thank you for your interest in [Course Fullname]. On this occasion, we have decided not to take your<br><br>application [HYPERLINK_APPLICATION_FORM] further. We encourage you to reapply next time and check out our othercourse offerings <a href="https://opencourses.partners.org">https://opencourses.partners.org</a>.<br><br>Thank you,<br>Division of Clinical Research, Center for Clinical Research Education';
$string['emailtemplate_remindercontent'] = 'Dear [Student First Name],<br><br>Thank you for starting your application. To be considered for [Course Fullname], please finish your application here: [Course URL].<br><br>The deadline to complete your application is [Due Date].<br><br>Questions with an asterisk next to them must be filled in. Make sure you complete all required questions.<br><br>If you have any questions, please reply to this email (from: DCRCCRE@partners.org).<br><br>Thank you,<br>Division of Clinical Research, Center for Clinical Research Education';
$string['emailtemplate_submitconfirm'] = 'Dear [Student First Name],<br><br>Thank you for submitting your application for [Course Fullname] this year. We are looking forward to reviewing your application.<br><br>Please visit the course page [Hyperlink Course URL] for application status updates. You will receive an email once a decision has been made.<br><br>If you have any questions, please reply to this email (from: DCRCCRE@partners.org)<br><br>Thank you,<br>Division of Clinical Research, Center for Clinical Research Education';
