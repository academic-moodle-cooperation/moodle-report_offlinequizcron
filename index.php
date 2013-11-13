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
 * @package       report
 * @subpackage    offlinequizcron
 * @author        Juergen Zimmer
 * @copyright     2013 The University of Vienna
 * @since         Moodle 2.5.3
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 **/

require(dirname(__FILE__).'/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/report/offlinequizcron/locallib.php');

// Get URL parameters.
$jobid = optional_param('jobid', 0, PARAM_INT);
$pagesize = optional_param('pagesize', 20, PARAM_INT);

if ($pagesize < 1) {
    $pagesize = 10;
}

// Print the header & check permissions.
admin_externalpage_setup('reportofflinequizcron', '', null, '', array('pagelayout' => 'report'));
$PAGE->requires->css(new moodle_url($CFG->wwwroot . '/report/offlinequizcron/styles.css'));
echo $OUTPUT->header();

// Log.
add_to_log(SITEID, "admin", "report offlinequizcron", "report/offlinequizcron/index.php?jobid=$jobid&pagesize=$pagesize", $jobid);

// Prepare the list of capabilities to choose from
echo $OUTPUT->box_start('centerbox');
echo $OUTPUT->heading(get_string('offlinequizjobs', 'report_offlinequizcron'));

// Initialise the table.
$table = new offlinequiz_jobs_table('offlinequizcronadmin');
// $table->head = array(
//         get_string('id', 'report_offlinequizcron'),
//         get_string('status', 'report_offlinequizcron'),
//         get_string('pluginname', 'mod_offlinequiz'),
//         get_string('timestart', 'report_offlinequizcron'),
//         get_string('timefinish', 'report_offlinequizcron'));

$tablecolumns = array('id', 'status', 'importuser', 'offlinequiz', 'course', 'timestart', 'timefinish');
$tableheaders = array(
        get_string('jobid', 'report_offlinequizcron'),
        get_string('status', 'report_offlinequizcron'),
        get_string('importuser', 'report_offlinequizcron'),
        get_string('pluginname', 'mod_offlinequiz'),
        get_string('course'),
        get_string('timestart', 'report_offlinequizcron'),
        get_string('timefinish', 'report_offlinequizcron'));

$table->define_columns($tablecolumns);
$table->define_headers($tableheaders);
$table->define_baseurl($CFG->wwwroot . '/report/offlinequizcron/index.php?pagesize=' . $pagesize);
$table->sortable(true);
$table->no_sorting('offlinequiz');
$table->setup();

$sort = $table->get_sql_sort();

$sql = "SELECT * 
          FROM {offlinequiz_queue}
        ";

if ($sort) {
    $sql .= "ORDER BY $sort";
} else {
    $sql .= "ORDER BY id DESC";
}

$total = $DB->count_records('offlinequiz_queue');
$table->pagesize($pagesize, $total);

$jobs = $DB->get_records_sql($sql, array(), $table->get_page_start(), $table->get_page_size());

$strtimeformat = get_string('strftimedatetime');
foreach ($jobs as $job) {
    $offlinequiz = $DB->get_record('offlinequiz', array('id' => $job->offlinequizid));
    $importuser = $DB->get_record('user', array('id' => $job->importuserid));
    $course = $DB->get_record('course', array('id' => $offlinequiz->course));
    
    $joburl = new moodle_url($CFG->wwwroot . '/report/offlinequizcron/index.php', array('jobid' => $job->id));
    $offlinequizurl = new moodle_url($CFG->wwwroot . '/mod/offlinequiz/view.php', array('q' => $offlinequiz->id));
    $courseurl = new moodle_url($CFG->wwwroot . '/course/view.php', array('id' => $course->id));
    
//    $table->data[] = array(
    $table->add_data(array(
            html_writer::link($joburl, $job->id),
            get_string('status' . $job->status, 'report_offlinequizcron'),
            fullname($importuser),
            html_writer::link($offlinequizurl, $offlinequiz->name),
            html_writer::link($courseurl, $course->shortname),
            $job->timestart > 0 ? userdate($job->timestart, $strtimeformat) : '',
            $job->timefinish > 0 ? userdate($job->timefinish , $strtimeformat) : ''
    ));
}

// Print it.
$table->finish_html();
echo $OUTPUT->box_end('centerbox');

echo '<div class="controls">';
echo ' <form id="options" action="index.php" method="get">';
echo '  <div class=centerbox>';
echo '   <table id="overview-options" class="boxaligncenter">';
echo '    <tr align="left">';
echo '     <td><label for="pagesize">'.get_string('pagesizeparts', 'offlinequiz').'</label></td>';
echo '     <td><input type="text" id="pagesize" name="pagesize" size="3" value="'.$pagesize.'" /></td>';
echo '    </tr>';
echo '   </table>';
echo '  </div>';
echo ' </form>';
echo '</div>';
// Footer.
echo $OUTPUT->footer();
