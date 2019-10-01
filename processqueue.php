<?php
// This file is for Moodle - http://moodle.org/
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
define('NO_OUTPUT_BUFFERING', true);
require(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot . '/report/offlinequizcron/locallib.php');
require_once($CFG->dirroot . '/mod/offlinequiz/cron.php');

// Print the header & check permissions.
admin_externalpage_setup('reportofflinequizcron', '', null, '', array('pagelayout' => 'report'));
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/report/offlinequizcron/styles.css'));

echo $OUTPUT->header();
echo $OUTPUT->box_start('centerbox');
echo $OUTPUT->heading(get_string('processingqueue', 'report_offlinequizcron'));
echo $OUTPUT->action_link($CFG->wwwroot . '/report/offlinequizcron/index.php', get_string('backtomainpage', 'report_offlinequizcron'),
    null, array('class' => 'backtomainlink'));
echo '<br/><br/>';

offlinequiz_evaluation_cron(0, true);

echo '<br/><br/>';

echo $OUTPUT->action_link($CFG->wwwroot . '/report/offlinequizcron/index.php', get_string('backtomainpage', 'report_offlinequizcron'),
    null, array('class' => 'backtomainlink'));
echo $OUTPUT->box_end();
// Footer.
echo $OUTPUT->footer();
