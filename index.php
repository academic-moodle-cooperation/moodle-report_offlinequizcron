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
 * The admin interface for the offlinequiz evaluation cronjob.
 *
 * @package       report_offlinequizcron
 * @author        Juergen Zimmer
 * @copyright     2013 The University of Vienna
 * @since         Moodle 2.5.3
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 **/

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/report/offlinequizcron/locallib.php');

// Get URL parameters.
$jobid = optional_param('jobid', 0, PARAM_INT);

// Print the header & check permissions.
admin_externalpage_setup('reportofflinequizcron', '', null, '', ['pagelayout' => 'report']);
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/report/offlinequizcron/styles.css'));
report_offlinequizcron_display_job_list();

// Footer.
echo $OUTPUT->footer();
