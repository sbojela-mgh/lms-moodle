<?php


/**
 * The Responsive theme is a base theme for responsive design. It is built using the
 * ZURB Foundations Framework.
 *
 * It uses two Javascript files (a Foundation customised version of Modernizr) to detect
 * features and a conditional link to HTML5 shim to add additional functionality
 * to old browsers. This is needed to support the responsive media queries. There are no
 * other additional Javascripts, these need to be added by child themes.
 *
 * KNOWN FIREFOX BUG: 
 * When resizing the window in Firefox you might find that the responsive design appears
 * to break.
 * Firefox determines its viewport size dependent on the 'Navigation Tool' at the top.
 * When the navigation bar can't shrink any further, the viewport stops shrinking as well,
 * even though the window is resized.
 * SOLUTION (s):
 * 1. Go to View -> Toolbars -> Navigation Toolbar to enable or disable it.
 *    You might want to assign a custom keyboard shortcut to do this quickly.
 * 2. Use another browser to test the responsive design: Google Chrome recommended.
 * NOTE: This does NOT effect the responsive design on mobile devices
 *
 * Like the 'base' theme, this theme is not meant to be copied, but instead inherited by
 * other themes.
 *
 * @package   theme_responsive
 * @copyright 2012 Rheinard Korf
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$THEME->name = 'responsive';

$THEME->parents = array();

$THEME->sheets = array(
    'core',         /** Must come first: Moodle reset, fixus YUI issues **/
	'foundation',   /** Must come second: Foundation Framework setup. Overrides some of core.css **/
	'pagelayout',   /** In 'base' this comes first, but now its not as critical. **/
   	'admin',
    'blocks',
    'calendar',
    'course',
//    'dock',	    /** Deliberate decision not to use a dock for responsive layouts. **/
    'grade',		/** There are still many styles that can be overridden. **/
    'message',
    'question',
    'user',
    'filemanager'

);

$THEME->editor_sheets = array('editor');

$THEME->layouts = array(
    // Most backwards compatible layout without the blocks - this is the layout used by default
    'base' => array(
        'file' => 'general.php',
        'regions' => array(),
    ),
    // Standard layout with blocks, this is recommended for most pages with general information
    'standard' => array(
        'file' => 'general.php',
        'regions' => array('side-pre', 'side-post'),
        'defaultregion' => 'side-pre',
    ),
    // Main course page
    'course' => array(
        'file' => 'general.php',
        'regions' => array('side-pre', 'side-post'),
        'defaultregion' => 'side-pre',
        'options' => array('langmenu'=>true),
    ),
    'coursecategory' => array(
        'file' => 'general.php',
        'regions' => array('side-pre', 'side-post'),
        'defaultregion' => 'side-pre',
    ),
    // part of course, typical for modules - default page layout if $cm specified in require_login()
    'incourse' => array(
        'file' => 'general.php',
        'regions' => array('side-pre', 'side-post'),
        'defaultregion' => 'side-pre',
    ),
    // The site home page.
    'frontpage' => array(
        //'file' => 'frontpage.php',
		'file' => 'general.php',
        'regions' => array('side-pre', 'side-post'),
        'defaultregion' => 'side-pre',
    ),
    // Server administration scripts.
    'admin' => array(
        'file' => 'general.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
    ),
    // My dashboard page
    'mydashboard' => array(
        'file' => 'general.php',
        'regions' => array('side-pre', 'side-post'),
        'defaultregion' => 'side-pre',
        'options' => array('langmenu'=>true),
    ),
    // My public page
    'mypublic' => array(
        'file' => 'general.php',
        'regions' => array('side-pre', 'side-post'),
        'defaultregion' => 'side-pre',
    ),
    'login' => array(
        'file' => 'general.php',
        'regions' => array(),
        'options' => array('langmenu'=>true),
    ),

    // Pages that appear in pop-up windows - no navigation, no blocks, no header.
    'popup' => array(
        'file' => 'general.php',
        'regions' => array(),
        'options' => array('nofooter'=>true, 'nonavbar'=>true, 'nocustommenu'=>true, 'nologininfo'=>true),
    ),
    // No blocks and minimal footer - used for legacy frame layouts only!
    'frametop' => array(
        'file' => 'general.php',
        'regions' => array(),
        'options' => array('nofooter'=>true),
    ),
    // Embeded pages, like iframe/object embeded in moodleform - it needs as much space as possible
    'embedded' => array(
        'file' => 'embedded.php',
        'regions' => array(),
        'options' => array('nofooter'=>true, 'nonavbar'=>true, 'nocustommenu'=>true),
    ),
    // Used during upgrade and install, and for the 'This site is undergoing maintenance' message.
    // This must not have any blocks, and it is good idea if it does not have links to
    // other places - for example there should not be a home link in the footer...
    'maintenance' => array(
        'file' => 'general.php',
        'regions' => array(),
        'options' => array('noblocks'=>true, 'nofooter'=>true, 'nonavbar'=>true, 'nocustommenu'=>true),
    ),
    // Should display the content and basic headers only.
    'print' => array(
        'file' => 'general.php',
        'regions' => array(),
        'options' => array('noblocks'=>true, 'nofooter'=>true, 'nonavbar'=>false, 'nocustommenu'=>true),
    ),
    // The pagelayout used when a redirection is occuring.
    'redirect' => array(
        'file' => 'embedded.php',
        'regions' => array(),
        'options' => array('nofooter'=>true, 'nonavbar'=>true, 'nocustommenu'=>true),
    ),
    // The pagelayout used for reports
    'report' => array(
        'file' => 'general.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
    ),
);

// The Responsive theme should not be selectable.
$THEME->hidefromselector = true;

/** List of javascript files that need to included on each page */
$THEME->javascripts = array('modernizr.foundation');
$THEME->javascripts_footer = array();

$THEME->rendererfactory = 'theme_overridden_renderer_factory';
