/**
 * @package   theme_responsive
 * @copyright 2012 Rheinard Korf  {@link http://rheinardkorf.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

The 'responsive' theme is a base theme that is to be used as a started theme for other responsive themes. It is meant to be used from a 'mobile first' design approach, but adheres closely to the 3 column standard Moodle layout.

Like the 'base' theme, it will not show up in the theme selector. In fact, it aims to support the a large majority of the same styles defined in the 'base' theme, but there are obvious differences.

Because the theme is designed to be a responsive layout, it was necessary to include modern browser features, as such, the following has been done to support as many browsers as possible:

1. Designed using a responsive framework: ZURB's Foundation Framework

2. An output renderer is used to alter the the standard DOCTYPE. It now produces a DOCTYPE accepted by HTML5 capable browsers:  <!DOCTYPE html>

3. An output renderer is used to change the way the login information is displayed. Rather than displaying inline elements, all items are now part of an unordered list (ideal for collapsable dropdown boxes using Javascript).

4. A custom Modernizr script gets loaded to add feature detection. This makes sure that 'Media Queries' (essential for responsive design) works. Where this feature is not available, it runs an HTML5 Shim script to add HTML5 capabilities to older browsers.

When creating your theme it is advisable that you start designing for a narrow mobile size first; then design for larger displays (980px is a good mark) and then for the designs in between.

Pay close attention to the comments in pagelayout.css to start styling your new theme.

I hope you find this useful and can help the movement towards more responsive layouts for Moodle courses.

Be sure to follow me on:
Twitter » @rheinardkorf
LinkedIn » http://linkedin.com/in/rheinardkorf
Google+ » http://j.mp/rheinardplus

But if you really want to support me, help others build responsive themes and let me know of any bugs or recommendations (that said, this will never be a complete theme, that is your job :) )