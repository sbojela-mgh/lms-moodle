<?php
echo '
<!DOCTYPE html>
<html>
<head>
<title>Confirmation</title>

<style>
.body {
  width: 550px;
  height: 500px;
  padding: 25%;
  background-color: darkslategrey;
  color: white;
  font-size: 21px;
}

.download{
font-size:14px;
}

.download a:hover {
  background-color: red;
}


.column {
  float: left;
  width: 29%;
  padding: 10px;
  height: 300px; 
}

.column_2 {
  float: left;
  width: 63%;
  padding: 10px;
  height: fit-content;
  border-left: solid 1px;
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


<div style = "width: 600px;height: 500px;margin-left: 30%;margin-right: 30%; margin-top: 10%; margin-bottom: auto; height:max-content; ">
<body >
<div class = "body">


<h1 style = "border-bottom: solid 2px white; width: fit-content;">Enrollment Success !</h1>

<div class="row" style="border: solid 1px; border: solid 1px; width: 550px; max-height: 400px; overflow: hidden;" >


  <div class="column" /*style="border: solid 1px;"*/>
    <h4 style = "font-weight: bold;line-height: 13px;">Save the Date!</h4>
    ';
    require('../config.php');
    require_once("$CFG->libdir/formslib.php");
    
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
    echo '<a style = "font-size: 18px;">'.date(' D, M d, Y h:i A', $course->startdate).' - '.date(' h:i A', $course->enddate).'</a>';
?>
    <script>
function hey(id)
  {document.getElementById(id).style.color = "red";
   document.getElementById(id).style.backgroundColor = "yellow";
   window.parent.frames["frame2"].document.getElementById(id).style.color = "red";
   window.parent.frames["frame2"].document.getElementById(id).style.backgroundColor = "yellow";} 
function bye(id)
  {document.getElementById(id).style.color = "black";
   document.getElementById(id).style.backgroundColor = "white";
   window.parent.frames["frame2"].document.getElementById(id).style.color = "black";
   window.parent.frames["frame2"].document.getElementById(id).style.backgroundColor = "white";}
</script>
<?php
echo'
<a href="/lms-moodle/enrol/ics.php?id='.$course->id.'"><button id="ical" class = "download" onmouseover="hey(this.id)" onmouseout="bye(this.id)">Add to Calendar</button></a>
  </div>

  <div class="column_2" style="overflow: scroll; max-height: 400px; text-align: start;"*/>
    <a>You are now enrolled in: </br> </br>'.'<span style = "color: bisque; font-size: 18px">'.$course->fullname.'</span>'.'</a>
    <p style = "font-size: 16px;"><span style = "font-size: 21px;">Course Description:</span> </br> </br> <span style = "color: bisque;">'.$course->summary.'</span></p>

<p style = "font-size: 18px; color;">You will receive an email confirming your enrollment with OpenCourses!</p>

<p style = "font-size: 18px; color;">If you have any questions, contact 
<span style ="font-size: 18px; color: bisque;">DCRCCRE@partners.edu.</span></p>

<p style = "font-size: 18px; color;">Disclaimer: This message does not confirm enrollment if this course requires an application. </p>

</div>
</div>
</body>
<a href="/lms-moodle/course/view.php?id='.$course->id.'"><button id = "nextPage" onmouseover="hey(this.id)" onmouseout="bye(this.id)" style = "float: right; margin-top: 5px;">Course Page</button></a>
</div>
</html>

';

?>

