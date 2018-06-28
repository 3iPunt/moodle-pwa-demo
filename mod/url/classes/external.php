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
 * URL external API
 *
 * @package    mod_url
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");

/**
 * URL external functions
 *
 * @package    mod_url
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */
class mod_url_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function view_url_parameters() {
        return new external_function_parameters(
            array(
                'urlid' => new external_value(PARAM_INT, 'url instance id')
            )
        );
    }

    /**
     * Trigger the course module viewed event and update the module completion status.
     *
     * @param int $urlid the url instance id
     * @return array of warnings and status result
     * @since Moodle 3.0
     * @throws moodle_exception
     */
    public static function view_url($urlid) {
        global $DB, $CFG;
        require_once($CFG->dirroot . "/mod/url/lib.php");

        $params = self::validate_parameters(self::view_url_parameters(),
                                            array(
                                                'urlid' => $urlid
                                            ));
        $warnings = array();

        // Request and permission validation.
        $url = $DB->get_record('url', array('id' => $params['urlid']), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($url, 'url');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        require_capability('mod/url:view', $context);

        // Call the url/lib API.
        url_view($url, $course, $cm, $context);

        $result = array();
        $result['status'] = true;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function view_url_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings()
            )
        );
    }

    /**
     * Describes the parameters for get_urls_by_courses.
     *
     * @return external_function_parameters
     * @since Moodle 3.3
     */
    public static function get_urls_by_courses_parameters() {
        return new external_function_parameters (
            array(
                'courseids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'Course id'), 'Array of course ids', VALUE_DEFAULT, array()
                ),
            )
        );
    }

    /**
     * Returns a list of urls in a provided list of courses.
     * If no list is provided all urls that the user can view will be returned.
     *
     * @param array $courseids course ids
     * @return array of warnings and urls
     * @since Moodle 3.3
     */
    public static function get_urls_by_courses($courseids = array()) {

        $warnings = array();
        $returnedurls = array();

        $params = array(
            'courseids' => $courseids,
        );
        $params = self::validate_parameters(self::get_urls_by_courses_parameters(), $params);

        $mycourses = array();
        if (empty($params['courseids'])) {
            $mycourses = enrol_get_my_courses();
            $params['courseids'] = array_keys($mycourses);
        }

        // Ensure there are courseids to loop through.
        if (!empty($params['courseids'])) {

            list($courses, $warnings) = external_util::validate_courses($params['courseids'], $mycourses);

            // Get the urls in this course, this function checks users visibility permissions.
            // We can avoid then additional validate_context calls.
            $urls = get_all_instances_in_courses("url", $courses);
            foreach ($urls as $url) {
                $context = context_module::instance($url->coursemodule);
                // Entry to return.
                $url->name = external_format_string($url->name, $context->id);

                list($url->intro, $url->introformat) = external_format_text($url->intro,
                                                                $url->introformat, $context->id, 'mod_url', 'intro', null);
                $url->introfiles = external_util::get_area_files($context->id, 'mod_url', 'intro', false, false);

                $returnedurls[] = $url;
            }
        }

        $result = array(
            'urls' => $returnedurls,
            'warnings' => $warnings
        );
        return $result;
    }

    /**
     * Describes the get_urls_by_courses return value.
     *
     * @return external_single_structure
     * @since Moodle 3.3
     */
    public static function get_urls_by_courses_returns() {
        return new external_single_structure(
            array(
                'urls' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'Module id'),
                            'coursemodule' => new external_value(PARAM_INT, 'Course module id'),
                            'course' => new external_value(PARAM_INT, 'Course id'),
                            'name' => new external_value(PARAM_RAW, 'URL name'),
                            'intro' => new external_value(PARAM_RAW, 'Summary'),
                            'introformat' => new external_format_value('intro', 'Summary format'),
                            'introfiles' => new external_files('Files in the introduction text'),
                            'externalurl' => new external_value(PARAM_RAW_TRIMMED, 'External URL'),
                            'display' => new external_value(PARAM_INT, 'How to display the url'),
                            'displayoptions' => new external_value(PARAM_RAW, 'Display options (width, height)'),
                            'parameters' => new external_value(PARAM_RAW, 'Parameters to append to the URL'),
                            'timemodified' => new external_value(PARAM_INT, 'Last time the url was modified'),
                            'section' => new external_value(PARAM_INT, 'Course section id'),
                            'visible' => new external_value(PARAM_INT, 'Module visibility'),
                            'groupmode' => new external_value(PARAM_INT, 'Group mode'),
                            'groupingid' => new external_value(PARAM_INT, 'Grouping id'),
                        )
                    )
                ),
                'warnings' => new external_warnings(),
            )
        );
    }

    /*
     * Load view
     */

    public static function load_view_parameters() {
        return new external_function_parameters (array(
            'urlid' => new external_value(PARAM_INT),
            'redirect' => new external_value(PARAM_BOOL),
            'forceview' => new external_value(PARAM_BOOL),
            'frame' => new external_value(PARAM_ALPHA),
        ));
    }

    public static function load_view($urlid, $redirect, $forceview, $frame) {
        global $DB, $CFG, $PAGE;
        require_once(__DIR__ . '/../locallib.php');

        $params = self::validate_parameters(self::load_view_parameters(), array(
            'urlid' => $urlid,
            'redirect' => $redirect,
            'forceview' => $forceview,
            'frame' => $frame,
        ));

        $url = $DB->get_record('url', array('id' => $params['urlid']), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($url, 'url');
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/url:view', $context);

        url_view($url, $course, $cm, $context);

        $warnings = array();
        $notices = array();
        $data = array();
        $redirectaction = array(
            'url' => false,
            'message' => '',
            'delay' => 0,
            'messagetype' => 'info',
        );

        $displaytype = url_get_final_display_type($url);

        /*
         * Return notice with button link
         */

        // Make sure URL exists before generating output - some older sites may contain empty urls
        // Do not use PARAM_URL here, it is too strict and does not support general URIs!
        $exturl = trim($url->externalurl);
        if (empty($exturl) || $exturl === 'http://') {
            $notices[] = [
                'message' => get_string('invalidstoredurl', 'url'),
                'link' => new moodle_url('/course/view.php', array('id' => $cm->course))
            ];
            return [
                'notices' => $notices,
                'warnings' => $warnings,
                'data' => $data,
                'redirectaction' => $redirectaction,
            ];
        }
        unset($exturl);

        /*
         * Redirect user
         */

        // Override redirect param
        if (RESOURCELIB_DISPLAY_OPEN === $displaytype) {
            $redirect = true;
            // We can't redirect using php if we are not sure that is safe to cache the response!
            // So we will redirect using JS instead.
        }

        if ($redirect && !$forceview) {
            // coming from course page or url index page,
            // the redirection is needed for completion tracking and logging
            $fullurl = str_replace('&amp;', '&', url_get_full_url($url, $cm, $course));

            if (!course_get_format($course)->has_view_page()) {
                // If course format does not have a view page, add redirection delay with a link to the edit page.
                // Otherwise teacher is redirected to the external URL without any possibility to edit activity or course settings.
                $editurl = null;
                if (has_capability('moodle/course:manageactivities', $context)) {
                    $editurl = new moodle_url('/course/modedit.php', array('update' => $cm->id));
                    $edittext = get_string('editthisactivity');
                } else if (has_capability('moodle/course:update', $context->get_course_context())) {
                    $editurl = new moodle_url('/course/edit.php', array('id' => $course->id));
                    $edittext = get_string('editcoursesettings');
                }
                if ($editurl) {
                    $redirectaction['url'] = $fullurl;
                    $redirectaction['message'] = html_writer::link($editurl, $edittext) . '<br/>' . get_string('pageshouldredirect');
                    $redirectaction['delay'] = 10;
                    return [
                        'notices' => $notices,
                        'warnings' => $warnings,
                        'data' => $data,
                        'redirectaction' => $redirectaction,
                    ];
                }
            }
            $redirectaction['url'] = $fullurl;
            return [
                'notices' => $notices,
                'warnings' => $warnings,
                'data' => $data,
                'redirectaction' => $redirectaction,
            ];
        }

        /*
         * Return view data
         */

        $data['displaytype'] = $displaytype;

        if (RESOURCELIB_DISPLAY_EMBED === $displaytype) {
            $mimetype = resourcelib_guess_url_mimetype($url->externalurl);
            $fullurl = url_get_full_url($url, $cm, $course);
            $title = $url->name;
            $link = html_writer::tag('a', $fullurl, array('href' => str_replace('&amp;', '&', $fullurl)));
            $clicktoopen = get_string('clicktoopen', 'url', $link);
            $moodleurl = new moodle_url($fullurl);
            //$extension = resourcelib_get_extension($url->externalurl);
            $mediamanager = core_media_manager::instance($PAGE);
            $embedoptions = array(
                core_media_manager::OPTION_TRUSTED => true,
                core_media_manager::OPTION_BLOCK => true
            );
            if (in_array($mimetype, array('image/gif', 'image/jpeg', 'image/png'))) {  // It's an image
                $code = resourcelib_embed_image($fullurl, $title);
            } else if ($mediamanager->can_embed_url($moodleurl, $embedoptions)) {
                // Media (audio/video) file.
                $code = $mediamanager->embed_url($moodleurl, $title, 0, 0, $embedoptions);
            } else {
                // anything else - just try object tag enlarged as much as possible
                $code = resourcelib_embed_general($fullurl, $title, $clicktoopen, $mimetype);
            }

            $options = empty($url->displayoptions) ? array() : unserialize($url->displayoptions, ['allowed_classes' => false]);

            $data['codehtml'] = $code;
            $data['heading'] = format_string($url->name);
            $data['printintro'] = !empty($options['printintro']) && trim(strip_tags($url->intro));
            $data['intro'] = format_module_intro('url', $url, $cm->id);

        } else if (RESOURCELIB_DISPLAY_FRAME === $displaytype) {
            if ($frame === 'top') {
                $options = empty($url->displayoptions) ? array() : unserialize($url->displayoptions, ['allowed_classes' => false]);

                $data['heading'] = format_string($url->name);
                $data['printintro'] = !empty($options['printintro']) && trim(strip_tags($url->intro));
                $data['intro'] = format_module_intro('url', $url, $cm->id);
            } else {
                $config = get_config('url');
                $exteurl = url_get_full_url($url, $cm, $course, $config);
                $navurl = "$CFG->wwwroot/mod/url/view.php?id=$cm->id&amp;frameset=top";
                $coursecontext = context_course::instance($course->id);
                $courseshortname = format_string($course->shortname, true, array('context' => $coursecontext));
                $title = strip_tags($courseshortname . ': ' . format_string($url->name));
                $framesize = $config->framesize;
                $modulename = s(get_string('modulename', 'url'));
                $contentframetitle = s(format_string($url->name));
                $dir = get_string('thisdirection', 'langconfig');

                $data['dir'] = $dir;
                $data['title'] = $title;
                $data['framesize'] = $framesize;
                $data['navurl'] = $navurl;
                $data['modulename'] = $modulename;
                $data['exteurl'] = $exteurl;
                $data['contentframetitle'] = $contentframetitle;
            }
        } else {
            $options = empty($url->displayoptions) ? array() : unserialize($url->displayoptions, ['allowed_classes' => false]);
            $fullurl = url_get_full_url($url, $cm, $course);
            $display = url_get_final_display_type($url);

            $data['heading'] = format_string($url->name);
            $data['printintro'] = !empty($options['printintro']) && trim(strip_tags($url->intro));
            $data['intro'] = format_module_intro('url', $url, $cm->id);

            if (RESOURCELIB_DISPLAY_POPUP === $display) {
                $jsfullurl = addslashes_js($fullurl);
                $width = empty($options['popupwidth']) ? 620 : $options['popupwidth'];
                $height = empty($options['popupheight']) ? 450 : $options['popupheight'];
                $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
                $extra = "onclick=\"window.open('$jsfullurl', '', '$wh'); return false;\"";
            } else if ($display === RESOURCELIB_DISPLAY_NEW) {
                $extra = "onclick=\"this.target='_blank';\"";
            } else {
                $extra = '';
            }

            $data['clicktoopenhtml'] = get_string('clicktoopen', 'url', "<a href=\"$fullurl\" $extra>$fullurl</a>");
        }

        return [
            'notices' => $notices,
            'warnings' => $warnings,
            'data' => $data,
            'redirectaction' => $redirectaction,
        ];
    }

    public static function load_view_returns() {
        return new external_single_structure(array(
            'notices' => new external_multiple_structure(new external_single_structure(array(
                'message' => new external_value(PARAM_RAW),
                'link' => new external_value(PARAM_RAW),
            ), '', VALUE_OPTIONAL)),
            'redirectaction' => new external_single_structure(array(
                'url' => new external_value(PARAM_RAW),
                'message' => new external_value(PARAM_RAW),
                'delay' => new external_value(PARAM_RAW),
                'messagetype' => new external_value(PARAM_RAW),
            )),
            'data' => new external_single_structure(array(
                'displaytype' => new external_value(PARAM_RAW, ''),
                'codehtml' => new external_value(PARAM_RAW, '', VALUE_OPTIONAL),
                'heading' => new external_value(PARAM_RAW, '', VALUE_OPTIONAL),
                'printintro' => new external_value(PARAM_RAW, '', VALUE_OPTIONAL),
                'intro' => new external_value(PARAM_RAW, '', VALUE_OPTIONAL),
                'dir' => new external_value(PARAM_RAW, '', VALUE_OPTIONAL),
                'title' => new external_value(PARAM_RAW, '', VALUE_OPTIONAL),
                'framesize' => new external_value(PARAM_RAW, '', VALUE_OPTIONAL),
                'navurl' => new external_value(PARAM_RAW, '', VALUE_OPTIONAL),
                'modulename' => new external_value(PARAM_RAW, '', VALUE_OPTIONAL),
                'exteurl' => new external_value(PARAM_RAW, '', VALUE_OPTIONAL),
                'contentframetitle' => new external_value(PARAM_RAW, '', VALUE_OPTIONAL),
                'clicktoopenhtml' => new external_value(PARAM_RAW, '', VALUE_OPTIONAL),
            )),
            'warnings' => new external_warnings(),
        ));
    }
}
