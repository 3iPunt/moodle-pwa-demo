<?php // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype = 'mysqli';
$CFG->dblibrary = 'native';
$CFG->dbhost = 'localhost';
$CFG->dbname = 'beta35_2';
$CFG->dbuser = 'root';
$CFG->dbpass = 'tresipunt';
$CFG->prefix = 'mdl_';
$CFG->dboptions = array(
    'dbpersist' => 0,
    'dbport' => '',
    'dbsocket' => '',
    'dbcollation' => 'utf8mb4_unicode_ci',
);

$CFG->wwwroot = 'http://localhost';
$CFG->dataroot = '/Users/tresipunt/moodles/beta_35/moodledata';
$CFG->admin = 'admin';

@error_reporting(E_ALL | E_STRICT); // NOT FOR PRODUCTION SERVERS!
@ini_set('display_errors', '1'); // NOT FOR PRODUCTION SERVERS!
$CFG->debug = (E_ALL | E_STRICT); // === DEBUG_DEVELOPER - NOT FOR PRODUCTION SERVERS!
$CFG->debugdisplay = 1; // NOT FOR PRODUCTION SERVERS!

//$CFG->cachejs = false;
//$CFG->yuiloglevel = 'debug';
//$CFG->langstringcache = false;

$CFG->directorypermissions = 0777;

$CFG->additionalhtmlhead = <<<'HTML'
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#f98012">
<meta name="mobile-web-app-capable" content="yes">
<link rel="icon" sizes="192x192" href="/resources/android/icon/drawable-xxxhdpi-icon.png">
HTML;
$CFG->additionalhtmlfooter = '<script>' . file_get_contents(__DIR__ . '/pwa.js') . '</script>';

require_once(__DIR__ . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
