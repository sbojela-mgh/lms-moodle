<?php
/**
 * The primary layout for this theme.
 *
 * @package   theme_responsive
 * @copyright 2012 Rheinard Korf  {@link http://rheinardkorf.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


$hasheading = ($PAGE->heading);
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());
$hasfooter = (empty($PAGE->layout_options['nofooter']));
$hassidepre = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-pre', $OUTPUT));
$hassidepost = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-post', $OUTPUT));
$haslogininfo = (empty($PAGE->layout_options['nologininfo']));

$showsidepre = ($hassidepre && !$PAGE->blocks->region_completely_docked('side-pre', $OUTPUT));
$showsidepost = ($hassidepost && !$PAGE->blocks->region_completely_docked('side-post', $OUTPUT));

$custommenu = $OUTPUT->custom_menu();
$hascustommenu = (empty($PAGE->layout_options['nocustommenu']) && !empty($custommenu));

$bodyclasses = array();
$maincols = 'seven';
$precols = 'three';
$postcols = 'two';
$maincolsonesidebar = 'nine';
$pushcols = 'push-'.$precols;
$pullcols = 'pull-'.$maincols;
if ($showsidepre && !$showsidepost) {
    if (!right_to_left()) {
        $bodyclasses[] = 'side-pre-only';
    }else{
        $bodyclasses[] = 'side-post-only';
    }
   $maincols = $maincolsonesidebar;
   $pushcols = 'push-'.$precols;
   $pullcols = 'pull-'.$maincols;
} else if ($showsidepost && !$showsidepre) {
    if (!right_to_left()) {
        $bodyclasses[] = 'side-post-only';
    }else{
        $bodyclasses[] = 'side-pre-only';
    }
   $maincols = $maincolsonesidebar;
   $pushcols = '';
   $pullcols = '';
} else if (!$showsidepost && !$showsidepre) {
   $bodyclasses[] = 'content-only';
   $maincols = 'twelve';
   $pushcols = '';
   $pullcols = '';
}

if ($hascustommenu) {
    $bodyclasses[] = 'has_custom_menu';
}

echo $OUTPUT->doctype(); ?>

<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
  <meta charset="utf-8" />

  <!-- Set the viewport width to device width for mobile -->
  <meta name="viewport" content="width=device-width" />

  <title><?php echo $PAGE->title ?></title>
  
  <!-- IE Fix for HTML5 Tags -->
  <!--[if lt IE 9]>
    <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
  <![endif]-->
  <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />
  <?php echo $OUTPUT->standard_head_html() ?>
  
</head>
<body id="<?php p($PAGE->bodyid) ?>" class="<?php p($PAGE->bodyclasses.' '.join(' ', $bodyclasses)) ?>">

	<?php echo $OUTPUT->standard_top_of_body_html() ?>
	
	<div id="page">

		<?php if ($hasheading || $hasnavbar) { ?>
		<!-- Header -->
		<div id="page-header" class="row">
		  <div class="twelve columns">
			<div class="eight columns">
				<h2 class="headermain"><?php echo $PAGE->heading ?></h2>
			</div>
			<div class="four columns">
		    <h3 class="logininfo"><?php
	            if ($haslogininfo) {
	                echo $OUTPUT->login_info();
	            }
	            if (!empty($PAGE->layout_options['langmenu'])) {
	                echo $OUTPUT->lang_menu();
	            }
	            echo $PAGE->headingmenu
	        ?></h3>
	 		</div>
		  </div>	
		</div>
	
		<!-- Custom Menu -->
	    <?php if ($hascustommenu) { ?>
	    <div id="custommenu" class="row">
		  <div class="twelve columns">
			<?php echo $custommenu; ?>
		  </div>
		</div>
	    <?php } ?>
	
		<!-- Nav and Breadcrumbs -->
	    <?php if ($hasnavbar) { ?>
	    <div class="navbar clearfix row">
	       <div class="breadcrumb ten columns"><?php echo $OUTPUT->navbar(); ?></div>
	       <div class="navbutton two columns"> <?php echo $PAGE->button; ?></div>
	    </div>
	    <?php } ?>
		<?php } ?>

		<!-- Main, Sidebar Pre, Sidebar Post -->
		<div id="page-content" class="row">
			
			<!-- Main -->
			<div id="region-main" class="<?php echo $maincols; ?> columns <?php echo $pushcols; ?>">
				<div class="region-content">
					<?php echo $OUTPUT->main_content() ?>
				</div>
			</div>
			
			<!-- Sidebar Pre -->
	        <?php if ($hassidepre OR (right_to_left() AND $hassidepost)) { ?>
		            <div id="region-pre" class="block-region <?php echo $precols; ?> columns <?php echo $pullcols; ?>">
			        <div class="region-content">
		                    <?php
		                if (!right_to_left()) {
		                    echo $OUTPUT->blocks_for_region('side-pre');
		                } elseif ($hassidepost) {
		                    echo $OUTPUT->blocks_for_region('side-post');
		            } ?>
		            </div>
		            </div>
	        <?php } ?>
			
			<!-- Sidebar Post -->
	        <?php if ($hassidepost OR (right_to_left() AND $hassidepre)) { ?>

		            <div id="region-post" class="block-region <?php echo $postcols; ?> columns">
			        <div class="region-content">
		                   <?php
		               if (!right_to_left()) {
		                   echo $OUTPUT->blocks_for_region('side-post');
		               } elseif ($hassidepre) {
		                   echo $OUTPUT->blocks_for_region('side-pre');
		            } ?>
		            </div>
		            </div>

	        <?php } ?>
			
		</div>
		
		<!-- Footer -->
	    <?php if ($hasfooter) { ?>
	    <div id="page-footer-wrap" class="clearfix row">
			<div id="page-footer" class="twelve columns">
		        <p class="helplink"><?php echo page_doc_link(get_string('moodledocslink')) ?></p>
		        <?php
		        echo $OUTPUT->login_info();
		        echo $OUTPUT->home_link();
		        echo $OUTPUT->standard_footer_html();
		        ?>
			</div>
	    </div>
	    <?php } ?>
		<!-- End Footer -->
		
	</div>
	
	<?php echo $OUTPUT->standard_end_of_body_html() ?>		
	
    <!-- Google Analytics: change UA-XXXXX-X to be your site's ID. -->
    <script>
        var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview']];
        (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
        g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
        s.parentNode.insertBefore(g,s)}(document,'script'));
    </script>

</body>
</html>
	