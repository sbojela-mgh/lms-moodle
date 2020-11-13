<?php

/**
 * Output Renderers for custom HTML output.
 *
 * @package   theme_responsive
 * @copyright 2012 Rheinard Korf  {@link http://rheinardkorf.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 

class theme_responsive_core_renderer extends core_renderer {
 
	/**
     * Overrides the default login/logged in user information by adding
     * the user profile picture.
     *
     * Items are displayed using an unordered list (ul) instead of inline
     * elements so that these have more flexibility when styled.
     *
     * @return string HTML fragment.
     */
    public function login_info() {
        global $USER, $CFG, $DB, $SESSION;

        if (during_initial_install()) {
            return '';
        }

        $loginpage = ((string)$this->page->url === get_login_url());
        $course = $this->page->course;
		$userpic = '';
        if (session_is_loggedinas()) {
            $realuser = session_get_realuser();
            $fullname = fullname($realuser, true);
            $realuserinfo = " [<a href=\"$CFG->wwwroot/course/loginas.php?id=$course->id&amp;sesskey=".sesskey()."\">$fullname</a>] ";

        } else {
            $realuserinfo = '';
        }

        $loginurl = get_login_url();

        if (empty($course->id)) {
            // $course->id is not defined during installation
            return '';
        } else if (isloggedin()) {
            $context = get_context_instance(CONTEXT_COURSE, $course->id);

            $fullname = fullname($USER, true);
            $userpic = $this->user_picture($USER, array('popup'=>false, 'link'=>false, 'size'=>25));
            // Since Moodle 2.0 this link always goes to the public profile page (not the course profile page)
            $username = "<a href=\"$CFG->wwwroot/user/profile.php?id=$USER->id\">$userpic $fullname</a>";
            if (is_mnet_remote_user($USER) and $idprovider = $DB->get_record('mnet_host', array('id'=>$USER->mnethostid))) {
                $username .= " from <a href=\"{$idprovider->wwwroot}\">{$idprovider->name}</a>";
            }
            if (isguestuser()) {
	            //You're just dropping by - login!
                //$loggedinas = $realuserinfo.get_string('loggedinasguest');
				$loggedinas = '';
                if (!$loginpage) {
                    $loggedinas .= "<ul><li class=\"login-link\"><a href=\"$loginurl\">".get_string('login').'</a></li></ul>';
                }
            } else if (is_role_switched($course->id)) { // Has switched roles
	            //You're someone else
                $rolename = '';
                if ($role = $DB->get_record('role', array('id'=>$USER->access['rsw'][$context->path]))) {
                    $rolename = ': '.format_string($role->name);
                }
                $loggedinas = '<ul><li>'.$username.$rolename.'</li>'.
                          "<li class=\"switch-user-link\"><a href=\"$CFG->wwwroot/course/view.php?id=$course->id&amp;switchrole=0&amp;sesskey=".sesskey()."\">".get_string('switchrolereturn').'</a></li></ul>';

            } else {
	            //You are you!
                $loggedinas = '<ul><li class="profile-link">'.$realuserinfo.$username.'</li>'.
                          "<li class=\"logout-link\"><a href=\"$CFG->wwwroot/login/logout.php?sesskey=".sesskey()."\">".get_string('logout').'</a></li></ul>';
            }
        } else {
	        //Not sure what you are... login just in case.
            $loggedinas = '';
            if (!$loginpage) {
                $loggedinas .= "<ul><li><a href=\"$loginurl\">".get_string('login').'</a></li></ul>';
            }
        }

        $loggedinas = '<div class="logininfo">'.$loggedinas.'</div>';

        if (isset($SESSION->justloggedin)) {
            unset($SESSION->justloggedin);
            if (!empty($CFG->displayloginfailures)) {
                if (!isguestuser()) {
                    if ($count = count_login_failures($CFG->displayloginfailures, $USER->username, $USER->lastlogin)) {
                        $loggedinas .= '&nbsp;<div class="loginfailures">';
                        if (empty($count->accounts)) {
                            $loggedinas .= get_string('failedloginattempts', '', $count);
                        } else {
                            $loggedinas .= get_string('failedloginattemptsall', '', $count);
                        }
                        if (file_exists("$CFG->dirroot/report/log/index.php") and has_capability('report/log:view', get_context_instance(CONTEXT_SYSTEM))) {
                            $loggedinas .= ' (<a href="'.$CFG->wwwroot.'/report/log/index.php'.
                                                 '?chooselog=1&amp;id=1&amp;modid=site_errors">'.get_string('logs').'</a>)';
                        }
                        $loggedinas .= '</div>';
                    }
                }
            }
        }

        return $loggedinas;
    }


	/**
	 * Overriding the renderer to output for HTML5.
	 *
	 * @return string the DOCTYPE declaration that should be used.
	 */
	public function doctype() {
	    global $CFG;

	    $doctype = '<!DOCTYPE html>' . "\n";
	    $this->contenttype = 'text/html; charset=utf-8';
	    return $doctype;
	}



}