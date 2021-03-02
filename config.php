<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'mysqli';
$CFG->dblibrary = 'native';
<<<<<<< HEAD
$CFG->dbhost    = 'mysql4.research.partners.org:3306';
$CFG->dbname    = 'lmsqa';
$CFG->dbuser    = 'headmaster';
$CFG->dbpass    = 'g@Me_P*cK+!bC=617';
=======
$CFG->dbhost    = 'localhost';
$CFG->dbname    = 'moodle';
$CFG->dbuser    = 'root';
$CFG->dbpass    = 'root';
>>>>>>> lms-dev
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbport' => '',
  'dbsocket' => '',
  'dbcollation' => 'utf8_general_ci',
);

<<<<<<< HEAD
$CFG->wwwroot   = 'https://rc-lmsqa.partners.org';
$CFG->dataroot  = '/var/www/moodledata/';
=======
$CFG->wwwroot   = 'http://localhost:8888/lms-moodle';
$CFG->dataroot  = '/Applications/MAMP/htdocs/moodledata';
>>>>>>> lms-dev
$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;

require_once(__DIR__ . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!

