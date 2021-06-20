<?php
require('../config.php');
require_once("$CFG->libdir/formslib.php");
echo '
<!DOCTYPE html>
<html style = "background-color: rgb(247, 247, 247);">
<head>
<title>Confirmation</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
.body {
  width: 700px;
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
  height: 500px;
  background-color: white;
  margin-right: 1px; 
}

.column_2 {
  float: left;
  width: 65%;
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


<div style = "width: 600px;height: 500px;margin-left: auto; margin-right:auto; margin-top: auto; margin-bottom: auto; height:max-content; ">
<body >
<div class = "body">

<div style = "background-color: white;">
<h1 style = " width: 100%; height: 59px; margin: 0px 0px 10px 0px; padding: 0px 0px 0px 8px; color: #13949C;">Enrollment Success !</h1>
</div>
<div class="row" style=" width: 100%; height: 500px; overflow: hidden; background-color: rgb(247, 247, 247);" >

  <div class="column"/>
    <a style = " font-size: 21px; font-weight: bold;line-height: 13px; color: #139497;">Save the Date!</a> </br> </br>
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
  $online_course_category_id = 0;
  $sql = "SELECT * from {course_categories} where name = 'On Demand'";
  $categories = $DB->get_records_sql($sql);
  foreach ($categories as $category){
  
    $online_course_category_id = $category->id;
    
  }
  if ($course->category == $online_course_category_id){
    echo '<tr>'.'<span style = "font-weight: bold;
    font-size: 18px; color: black;">'.'Date/Time: On Demand'.'</span>'.'</tr>';
} else {
    echo '<tr>'.'<span style = "font-weight: bold;
    font-size: 18px; color: black;">'.'Date/Time:'. date(' M-d-Y hA', $course->startdate).'</span>'.'</tr>';
}
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
<a href="'.$CFG->wwwroot.'/enrol/ics.php?id='.$course->id.'"><button id="ical" class = "download" onmouseover="hey(this.id)" onmouseout="bye(this.id)">Add to Calendar</button></a>
  </div>

  <div class="column_2" style="overflow: scroll; height: 500px; text-align: start; background-color: white;">
    <a><span style = "font-weight: bold;">You are now enrolled in:</span> </br> </br>'.'<span style = "color: black; font-size: 18px">'.$course->fullname.'</span>'.'</a>
    <p style = "font-size: 16px;"><span style = "font-size: 21px; font-weight: bold;">Course Description:</span> </br> </br> <span style = "color: black;">'.$course->summary.'</span></p>

<p style = "font-size: 18px; color: black;">You will receive an email confirming your enrollment with OpenCourses!</p>

<p style = "font-size: 18px; color: black;">If you have any questions, contact 
<span style ="font-size: 18px; color: black;">DCRCCRE@partners.edu.</span></p>

<p style = "font-size: 18px; color: red;">Disclaimer: This message does not confirm enrollment if this course requires an application. </p>

</div>
</div>
</body>
<a href="'.$CFG->wwwroot.'/course/view.php?id='.$course->id.'"><button id = "nextPage" onmouseover="hey(this.id)" onmouseout="bye(this.id)" class = "download"style = "float: right; margin-top: 5px;">Course Page</button></a>
</div>
</html>

';

?>

