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
 * Useful functions for the offlinequiz evaluation cronjob admin interface.
 *
 * @package       report_offlinequizcron
 * @author        Juergen Zimmer
 * @copyright     2013 The University of Vienna
 * @since         Moodle 2.5.3
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 **/

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/adminlib.php');

/**
 * Displays the list of offlinequiz evaluation cronjobs.
 */
function report_offlinequizcron_display_job_list() {
    global $CFG, $DB, $OUTPUT;
    $searchterm = optional_param('searchterm', '', PARAM_TEXT);
    $pagesize = optional_param('pagesize_jobs', 20, PARAM_INT);
    $statusnew = optional_param('statusnew', 0, PARAM_INT);
    $statusprocessing = optional_param('statusprocessing', 0, PARAM_INT);
    $statusfinished = optional_param('statusfinished', 0, PARAM_INT);

    // If no status filters is selected, select some!
    if (!$statusnew && !$statusprocessing && !$statusfinished) {
        $statusnew = 1;
        $statusprocessing = 1;
    }

    if ($pagesize < 1) {
        $pagesize = 10;
    }

    $baseurl = new moodle_url($CFG->wwwroot . '/report/offlinequizcron/index.php', ['pagesize_jobs' => $pagesize]);
    $tablebaseurl = new moodle_url(
        $CFG->wwwroot . '/report/offlinequizcron/index.php',
        ['statusnew' => $statusnew,
                  'statusprocessing' => $statusprocessing,
                  'statusfinished' => $statusfinished,
                  'pagesize_jobs' => $pagesize]
    );

    echo $OUTPUT->header();
    echo $OUTPUT->box_start('centerbox');
    echo $OUTPUT->heading_with_help(
        get_string('offlinequizjobs', 'report_offlinequizcron'),
        'offlinequizjobs',
        'report_offlinequizcron'
    );

    // Initialise the table.
    $statusvalues = ['statusnew' => $statusnew, 'statusprocessing' => $statusprocessing, 'statusfinished' => $statusfinished];

    // Print checkboxes for status filters.
    echo '<form id="reportform" method="get" action="' . $baseurl . '" class="form" >';
    echo get_string('showjobswithstatus', 'report_offlinequizcron') . ': &nbsp;&nbsp;&nbsp;';
    foreach ($statusvalues as $name => $value) {
        if ($value) {
            $checked = 'checked="checked"';
        } else {
            $checked = '';
        }
        echo '<input type="checkbox" name="' . $name . '" value="1" ' . $checked . '/>'
                . get_string($name, 'report_offlinequizcron') . '&nbsp;&nbsp;&nbsp;&nbsp;';
    }
    echo '<br/><div class="form-group ">';
    echo '   <label for="search">' . get_string('search', 'report_offlinequizcron') . '</label>&nbsp;&nbsp;';
    echo '   <input type="text" id="search" name="searchterm" size="20" class="" value="' . $searchterm . '" />';
    echo '   <input type="submit" value="' . get_string('apply', 'report_offlinequizcron') . '" class="btn btn-secondary" />';
    echo '</div>';
    echo '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';

    echo '</form><br/>';

    // Print the table of offlinequiz evaluation jobs.
    $table = new \report_offlinequizcron\jobs_table('offlinequizcronadmin');

    $tablecolumns = ['status', 'oqname', 'cshortname', 'lastname', 'jobtimecreated', 'jobtimestart', 'jobtimefinish', 'pageamount'];
    $tableheaders = [
            get_string('status', 'report_offlinequizcron'),
            get_string('pluginname', 'mod_offlinequiz'),
            get_string('course'),
            get_string('importuser', 'report_offlinequizcron'),
            get_string('timecreated', 'report_offlinequizcron'),
            get_string('timestart', 'report_offlinequizcron'),
            get_string('timefinish', 'report_offlinequizcron'),
            get_string('pageamount', 'report_offlinequizcron')];

    $table->define_columns($tablecolumns);
    $table->define_headers($tableheaders);
    $table->define_baseurl($tablebaseurl);
    $table->sortable(true);
    $table->setup();

    $sort = $table->get_sql_sort();

    $sql = "SELECT oqq.id as jobid, oqq.status as status,
                   oqq.timecreated as jobtimecreated, oqq.timestart as jobtimestart, oqq.timefinish as jobtimefinish,
                   oq.id as oqid, oq.name as oqname,
                   c.shortname as cshortname, c.id as cid,
                   u.id as uid, u.firstname as firstname, u.lastname as lastname,
                   u.alternatename, u.middlename, u.firstnamephonetic, u.lastnamephonetic,
                   (SELECT count(*)
                     FROM {offlinequiz_queue_data} oqd
                     WHERE oqd.queueid = oqq.id) as files,
                   oqq.id as jobid
              FROM {offlinequiz_queue} oqq
              JOIN {offlinequiz} oq on oqq.offlinequizid = oq.id
              JOIN {course} c on oq.course = c.id
              JOIN {user} u on oqq.importuserid = u.id
              WHERE 1=1
             ";

    $countsql = "SELECT COUNT(oqq.id)
                   FROM {offlinequiz_queue} oqq
                   JOIN {offlinequiz} oq on oqq.offlinequizid = oq.id
                   JOIN {course} c on oq.course = c.id
                   JOIN {user} u on oqq.importuserid = u.id
                  WHERE 1=1
                  ";

    $sqlparams = [];

    if ($statusnew || $statusfinished || $statusprocessing) {
        $statuses = [];
        if ($statusnew) {
            $statuses[] = 'new';
        }
        if ($statusprocessing) {
            $statuses[] = 'processing';
        }
        if ($statusfinished) {
            $statuses[] = 'finished';
        }
        [$ssql, $sparams] = $DB->get_in_or_equal($statuses);
        $sql .= " AND oqq.status $ssql ";
        $countsql .= " AND oqq.status $ssql ";
        $sqlparams = $sparams;
    }

    if ($searchterm) {
        $countsql .= ' AND ( oq.name LIKE ? OR c.shortname LIKE ? OR CONCAT(u.firstname, \' \', u.lastname) LIKE ? )';
        $sql .= ' AND ( oq.name LIKE ? OR c.shortname LIKE ? OR CONCAT(u.firstname,  \' \', u.lastname) LIKE ? )';
        $sqlparams[count($sqlparams)] = '%' . $searchterm . '%';
        $sqlparams[count($sqlparams)] = '%' . $searchterm . '%';
        $sqlparams[count($sqlparams)] = '%' . $searchterm . '%';
    }

    if ($sort) {
        $sql .= "ORDER BY $sort";
    }

    $total = $DB->count_records_sql($countsql, $sqlparams);
    $table->pagesize($pagesize, $total);

    $jobs = $DB->get_records_sql($sql, $sqlparams, $table->get_page_start(), $table->get_page_size());

    $strtimeformat = get_string('strftimedatetime');
    foreach ($jobs as $job) {
        $offlinequizurl = new moodle_url($CFG->wwwroot . '/mod/offlinequiz/view.php', ['q' => $job->oqid]);
        $courseurl = new moodle_url($CFG->wwwroot . '/course/view.php', ['id' => $job->cid]);
        $userurl = new moodle_url($CFG->wwwroot . '/user/profile.php', ['id' => $job->uid]);
        $joburl = new moodle_url(
            '/mod/offlinequiz/report.php',
            ['q' => $job->oqid, 'mode' => 'correct'],
            'offlinequiz-queue-' . $job->jobid
        );
        $table->add_data(
            [
                get_string('status' . $job->status, 'report_offlinequizcron'),
                html_writer::link($offlinequizurl, $job->oqname),
                html_writer::link($courseurl, $job->cshortname),
                html_writer::link($userurl, fullname($job)),
                $job->jobtimecreated > 0 ? userdate($job->jobtimecreated, $strtimeformat) : '',
                $job->jobtimestart > 0 ? userdate($job->jobtimestart, $strtimeformat) : '',
                $job->jobtimefinish > 0 ? userdate($job->jobtimefinish, $strtimeformat) : '',
                html_writer::link($joburl, $job->files),
            ]
        );
    }

    // Print it.
    $table->finish_html();

    echo '<div class="controls">';
    echo ' <form id="options" action="index.php" method="get">';
    echo '     <label for="pagesize_jobs">' . get_string('pagesize_jobs', 'report_offlinequizcron') . '</label>&nbsp;&nbsp;';
    foreach ($statusvalues as $name => $value) {
        echo '     <input type="hidden" name="' . $name . '" value="' . $value . '"/>';
    }
    echo '     <input type="text" id="pagesize_jobs" name="pagesize_jobs" size="3" value="' . $pagesize . '" />';
    echo ' </form>';
    echo '</div>';

    echo $OUTPUT->box_end();
}
