<?php  // Moodle configuration file

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'MariaDB';
$CFG->dblibrary = 'native';
<<<<<<< HEAD
$CFG->dbhost    = 'localhost';
$CFG->dbname    = 'moodle_2';
$CFG->dbuser    = 'root';
$CFG->dbpass    = '';
=======
$CFG->dbhost    = 'mysql4.research.partners.org:3306';
$CFG->dbname    = 'lmsqa';
$CFG->dbuser    = 'headmaster';
$CFG->dbpass    = 'g@Me_P*cK+!bC=617';
>>>>>>> origin/lms-test
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbport' => '',
  'dbsocket' => '',
  'dbcollation' => 'utf8_general_ci',
);

<<<<<<< HEAD
$CFG->wwwroot   = 'http://localhost/lms-moodle';
$CFG->dataroot  = 'C:\xampp\htdocs\moodledata';
=======
$CFG->wwwroot   = 'https://rc-lmsqa.partners.org';
$CFG->dataroot  = '/var/www/moodledata/';
>>>>>>> origin/lms-test
$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;

require_once(__DIR__ . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
