<?php
require('../config.php');
require_once("$CFG->libdir/formslib.php");
echo '
<!DOCTYPE html>
<html style = "background-color: rgb(247, 247, 247);">
<head>
<title>Confirmation</title>

<style>
.body {
  width: 550px;
  height: 500px;
  padding: 25%;
  color: white;
  font-size: 21px;
}

.download {
  font-size:14px;
  background-color: #989898;
  color: white;
}



.column {
  float: left;
  width: 29%;
  padding: 10px;
  height: 400px;
  background-color: white;
  margin-right: 1px; 
}

.column_2 {
  float: left;
  width: 63%;
  padding: 10px;
  height: 400px;
  margin-left: 1px;
  color: #139497; 
}

.row:after {
  content: "";
  display: table;
  clear: both;
}
</style>
<script>
function highlight(id)
  {document.getElementById().style.backgroundcolor = "red";
</script>
</head>


<div style = "width: 600px;height: 500px;margin-left: 25%;margin-top: 10%; height:max-content; ">
<body >
<div class = "body">

<div style = "background-color: white;">
<h1 style = " width: 100%; height: 59px; margin: 0px 0px 10px 0px; padding: 0px 0px 0px 8px; color: #13949C;">Enrollment Success !</h1>
</div>
<div class="row" style=" width: 100%; max-height: 400px; overflow: hidden; background-color: rgb(247, 247, 247);" >

  <div class="column"/>
    <a style = " font-size: 21px; font-weight: bold;line-height: 13px; color: #139497;">Save the Date!</a>
    ';
    
    
    $id = required_param('id', PARAM_INT);
    $returnurl = optional_param('returnurl', 0, PARAM_LOCALURL);
    
    $course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
    $context = context_course::instance($course->id, MUST_EXIST);

	if (isset($_SESSION['username'])) {
		$url = "page1.php";
		header('Location: ' . $url);
		exit();
	} else if (isset($_POST['username'])) {
		$username = $_POST['username'];
		$_SESSION['username'] = $username;
		$url = "page1.php";
		header('Location: ' . $url);
		exit();
	}
    echo '<a style = "font-size: 18px; color: black;">'.date(' D, M d, Y h:i A', $course->startdate).' - '.date(' h:i A', $course->enddate).'</a>';
?>
    <script>
function hey(id)
  {document.getElementById(id).style.color = "#13949C";
   document.getElementById(id).style.backgroundColor = "white";
   window.parent.frames["frame2"].document.getElementById(id).style.color = "#13949C";
   window.parent.frames["frame2"].document.getElementById(id).style.backgroundColor = "white";} 
function bye(id)
  {document.getElementById(id).style.color = "white";
   document.getElementById(id).style.backgroundColor = "#989898";
   window.parent.frames["frame2"].document.getElementById(id).style.color = "white";
   window.parent.frames["frame2"].document.getElementById(id).style.backgroundColor = "#989898";}
</script>

<?php
echo'
<a href="/lms-moodle/enrol/ics.php?id='.$course->id.'"><button id="ical" class = "download" onmouseover="hey(this.id)" onmouseout="bye(this.id)">Add to Calendar</button></a>
  </div>

  <div class="column_2" style="overflow: scroll; height: 400px; text-align: start; background-color: white;">
    <a><span style = "font-weight: bold;">You are now enrolled in:</span> </br> </br>'.'<span style = "color: black; font-size: 18px">'.$course->fullname.'</span>'.'</a>
    <p style = "font-size: 16px;"><span style = "font-size: 21px; font-weight: bold;">Course Description:</span> </br> </br> <span style = "color: black;">'.$course->summary.'</span></p>

<p style = "font-size: 18px; color: black;">You will receive an email confirming your enrollment with OpenCourses!</p>

<p style = "font-size: 18px; color: black;">If you have any questions, contact 
<span style ="font-size: 18px; color: black;">DCRCCRE@partners.edu.</span></p>

<p style = "font-size: 18px; color: red;">Disclaimer: This message does not confirm enrollment if this course requires an application. </p>

</div>
</div>
</body>
<a href="./lms-moodle/course/view.php?id='.$course->id.'"><button id = "nextPage" onmouseover="hey(this.id)" onmouseout="bye(this.id)" class = "download"style = "float: right; margin-top: 5px;">Course Page</button></a>
</div>
</html>

';

?>

