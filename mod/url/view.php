<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * URL module main user interface
 *
 * @package    mod_url
 * @copyright  2009 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/locallib.php');
require_once(__DIR__ . '/../../lib/completionlib.php');

$id = optional_param('id', 0, PARAM_INT); // Course module ID
$u = optional_param('u', 0, PARAM_INT); // URL instance id
$redirect = optional_param('redirect', 0, PARAM_BOOL);
$forceview = optional_param('forceview', 0, PARAM_BOOL);
$frame = optional_param('frameset', 'main', PARAM_ALPHA); // main, top

// Fetch the minimum data in order to decide which layout / app shell we need to load
if ($u) { // Two ways to specify the module
    /** @var stdClass $url */
    $url = $DB->get_record('url', array('id' => $u), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('url', $url->id, $url->course, false, MUST_EXIST);
} else {
    $cm = get_coursemodule_from_id('url', $id, 0, false, MUST_EXIST);
    /** @var stdClass $url */
    $url = $DB->get_record('url', array('id' => $cm->instance), '*', MUST_EXIST);
}
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

$displaytype = url_get_final_display_type($url);

// Minimal page setup
$PAGE->set_url('/mod/url/view.php', array('id' => $cm->id));
$PAGE->set_cm($cm);
$PAGE->set_activity_record($url);

// Optional page setup (should we keep it?)
//$PAGE->set_title($course->shortname.': '.$url->name);
//$PAGE->set_heading($course->fullname);

$PAGE->requires->js_call_amd('mod_url/view', 'init', [$url->id, $redirect, $forceview, $frame]);

// Print the app shell
if (RESOURCELIB_DISPLAY_FRAME === $displaytype) {
    if ($frame === 'top') {
        $PAGE->set_pagelayout('frametopshell'); // 1 column app shell
        echo $OUTPUT->header();
        echo $OUTPUT->heading('<span id="heading-placeholder"></span>', 2);
        echo $OUTPUT->box_start('mod_introbox hidden', 'urlintro');
        echo '<span id="module_intro-placeholder"></span>';
        echo $OUTPUT->box_end();
        echo $OUTPUT->footer();
    } else {
        $PAGE->set_pagelayout('xhtmlframesetshell'); // xhtml frameset layout shell
        echo $OUTPUT->header();
        echo $OUTPUT->footer();
    }
} else { // Includes the case RESOURCELIB_DISPLAY_EMBED === $displaytype
    $PAGE->set_pagelayout('incourseshell'); // 2 columns app shell
    echo $OUTPUT->header();
    echo $OUTPUT->heading('<span id="heading-placeholder"></span>', 2);
    echo '<span id="code-placeholder"></span>';
    echo $OUTPUT->box_start('mod_introbox hidden', 'urlintro');
    echo '<span id="module_intro-placeholder"></span>';
    echo $OUTPUT->box_end();
    echo '<span id="urlworkaround-placeholder"></span>';
    echo $OUTPUT->footer();
}
