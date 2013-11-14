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
 * Useful functions for the offlinequiz evaluation cronjob admin interface.
 *
 * @package       report
 * @subpackage    offlinequizcron
 * @author        Juergen Zimmer
 * @copyright     2013 The University of Vienna
 * @since         Moodle 2.5.3
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
**/

defined('MOODLE_INTERNAL') || die();

class offlinequizcron_jobs_table extends flexible_table {

    protected $reportscript;
    protected $params;

    public function __construct($uniqueid) {
        parent::__construct($uniqueid);
    }

    public function print_nothing_to_display() {
        global $OUTPUT;
        return;
    }

    public function wrap_html_start() {
    echo '<div id="tablecontainer" class="centerbox">';
        echo '<center>';
    }

    public function wrap_html_finish() {
        echo '  </center>';
        // Close form
        echo ' </div>';
    }
} // end class

class offlinequizcron_job_files_table extends flexible_table {

    public function __construct($uniqueid) {
        parent::__construct($uniqueid);
    }

    public function print_nothing_to_display() {
        global $OUTPUT;
        echo $OUTPUT->heading(get_string('nofiles', 'report_offlinequizcron'), 3);
        return;
    }
    public function wrap_html_start() {
        echo '<div id="tablecontainer" class="centerbox">';
        echo '<center>';
    }

    public function wrap_html_finish() {
        echo '  </center>';
        echo ' </div>';
    }
} // end class


function offlinequizcron_display_job_list() {
    global $CFG, $DB, $OUTPUT;

    $pagesize = optional_param('pagesize', 20, PARAM_INT);
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
    
    $baseurl = new moodle_url($CFG->wwwroot . '/report/offlinequizcron/index.php');

    echo $OUTPUT->box_start('centerbox');
    echo $OUTPUT->heading(get_string('offlinequizjobs', 'report_offlinequizcron'));

    // Initialise the table.
    $statusvalues = array('statusnew' => $statusnew, 'statusprocessing' => $statusprocessing, 'statusfinished' => $statusfinished);

    // Print checkboxes for status filters.
    echo '<form id="reportform" method="post" action="'. $baseurl . '" >';
    echo ' <div>';
    echo get_string('showjobswithstatus', 'report_offlinequizcron') . ': &nbsp;&nbsp;&nbsp;';
    foreach ($statusvalues as $name => $value) {
        print_object($value);
        if ($value) {
            $checked = 'checked="checked"';
        } else {
            $checked = '';
        }
        echo '<input type="checkbox" name="' . $name .'" value="1" ' . $checked . '/>' . get_string($name, 'report_offlinequizcron') . '&nbsp;&nbsp;&nbsp;&nbsp;';
    }
    echo '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';
    echo '<input type="submit" value="' . get_string('apply', 'report_offlinequizcron') . '" />';
    echo '</div></form></div><br/>';

    // Print the table of offlinequiz evaluation jobs.
    $table = new offlinequizcron_jobs_table('offlinequizcronadmin');

    $tablecolumns = array('id', 'status', 'oqname', 'cshortname', 'importuser', 'jobtimecreated', 'jobtimestart', 'jobtimefinish');
    $tableheaders = array(
            get_string('jobid', 'report_offlinequizcron'),
            get_string('status', 'report_offlinequizcron'),
            get_string('pluginname', 'mod_offlinequiz'),
            get_string('course'),
            get_string('importuser', 'report_offlinequizcron'),
            get_string('timecreated', 'report_offlinequizcron'),
            get_string('timestart', 'report_offlinequizcron'),
            get_string('timefinish', 'report_offlinequizcron'));

    $table->define_columns($tablecolumns);
    $table->define_headers($tableheaders);
    $table->define_baseurl($baseurl);
    $table->sortable(true);
    $table->setup();

    $sort = $table->get_sql_sort();

    $sql = "SELECT oqq.id, oqq.status as status,
                   oqq.timecreated as jobtimecreated, oqq.timestart as jobtimestart, oqq.timefinish as jobtimefinish,
                   oq.id as oqid, oq.name as oqname,
                   c.shortname as cshortname, c.id as cid,
                   u.id as uid, u.firstname as firstname, u.lastname as lastname
              FROM {offlinequiz_queue} oqq
              JOIN {offlinequiz} oq on oqq.offlinequizid = oq.id
              JOIN {course} c on oq.course = c.id
              JOIN {user} u on oqq.importuserid = u.id
              WHERE 1=1
    ";

    $sqlparams = array();

    if ($statusnew || $statusfinished || $statusprocessing) {
        $statuses = array();
        if ($statusnew) {
            $statuses[] = 'new';
        }
        if ($statusprocessing) {
            $statuses[] = 'processing';
        }
        if ($statusfinished) {
            $statuses[] = 'finished';
        }
        list($ssql, $sparams) = $DB->get_in_or_equal($statuses);
        $sql .= " AND oqq.status $ssql ";
        $sqlparams = $sparams;
    }

    if ($sort) {
        $sql .= "ORDER BY $sort";
    } else {
        $sql .= "ORDER BY id DESC";
    }

    $total = $DB->count_records('offlinequiz_queue');
    $table->pagesize($pagesize, $total);

    $jobs = $DB->get_records_sql($sql, $sqlparams, $table->get_page_start(), $table->get_page_size());

    $strtimeformat = get_string('strftimedatetime');
    foreach ($jobs as $job) {
        $joburl = new moodle_url($CFG->wwwroot . '/report/offlinequizcron/index.php', array('jobid' => $job->id));
        $offlinequizurl = new moodle_url($CFG->wwwroot . '/mod/offlinequiz/view.php', array('q' => $job->oqid));
        $courseurl = new moodle_url($CFG->wwwroot . '/course/view.php', array('id' => $job->cid));
        $userurl = new moodle_url($CFG->wwwroot . '/user/profile.php', array('id' => $job->uid));

        //    $table->data[] = array(
        $table->add_data(array(
                html_writer::link($joburl, $job->id),
                get_string('status' . $job->status, 'report_offlinequizcron'),
                html_writer::link($offlinequizurl, $job->oqname),
                html_writer::link($courseurl, $job->cshortname),
                html_writer::link($userurl, fullname($job)),
                $job->jobtimecreated > 0 ? userdate($job->jobtimecreated, $strtimeformat) : '',
                $job->jobtimestart > 0 ? userdate($job->jobtimestart, $strtimeformat) : '',
                $job->jobtimefinish > 0 ? userdate($job->jobtimefinish , $strtimeformat) : ''
        ));
    }

    // Print it.
    $table->finish_html();
    echo $OUTPUT->box_end();

    echo '<div class="controls">';
    echo ' <form id="options" action="index.php" method="get">';
    echo '  <div class=centerbox>';
    echo '   <table id="overview-options" class="boxaligncenter">';
    echo '    <tr align="left">';
    echo '     <td><label for="pagesize">'.get_string('pagesize', 'report_offlinequizcron').'</label></td>';
    echo '     <td><input type="text" id="pagesize" name="pagesize" size="3" value="' . $pagesize . '" /></td>';
    echo '    </tr>';
    echo '   </table>';
    echo '  </div>';
    echo ' </form>';
    echo '</div>';
}



function offlinequizcron_display_job_details($jobid) {
    global $CFG, $DB, $OUTPUT;

    $sql = "SELECT oqq.id, oqq.status,
                   oqq.timecreated as jobtimecreated, oqq.timestart as jobtimestart, oqq.timefinish as jobtimefinish,
                   oq.id as oqid, oq.name as oqname,
                   c.shortname as cshortname, c.id as cid,
                   u.id as uid, u.firstname as firstname, u.lastname as lastname
              FROM {offlinequiz_queue} oqq
              JOIN {offlinequiz} oq on oqq.offlinequizid = oq.id
              JOIN {course} c on oq.course = c.id
              JOIN {user} u on oqq.importuserid = u.id
              WHERE oqq.id = :jobid
              ";

    $params = array('jobid' => $jobid);
    
    if (!$job = $DB->get_record_sql($sql, $params)) {
        redirect($CFG->wwwroot . '/report/offlinequizcron/index.php');
    }

    $pagesize = optional_param('pagesize', 20, PARAM_INT);
    if ($pagesize < 1) {
        $pagesize = 10;
    }

    echo $OUTPUT->box_start('centerbox');
    echo $OUTPUT->heading(get_string('offlinequizjobdetails', 'report_offlinequizcron', $job->id));

    $offlinequizurl = new moodle_url($CFG->wwwroot . '/mod/offlinequiz/view.php', array('q' => $job->oqid));
    $courseurl = new moodle_url($CFG->wwwroot . '/course/view.php', array('id' => $job->cid));
    $userurl = new moodle_url($CFG->wwwroot . '/user/profile.php', array('id' => $job->uid));

    $detailstable = new html_table();
    $detailstable->id = 'jobdetailstable';
    $detailstable->align = array('left', 'right');
    $detailstable->attributes = array('align' => 'center');
    
    $strtimeformat = get_string('strftimedatetime');
    $detailstable->data[] = array(get_string('status', 'report_offlinequizcron'), get_string('status' . $job->status, 'report_offlinequizcron'));
    $detailstable->data[] = array(get_string('pluginname', 'offlinequiz'), html_writer::link($offlinequizurl, $job->oqname));
    $detailstable->data[] = array(get_string('course'), html_writer::link($courseurl, $job->cshortname));
    $detailstable->data[] = array(get_string('importuser', 'report_offlinequizcron'), html_writer::link($userurl, fullname($job))); 
    $detailstable->data[] = array(get_string('timecreated', 'report_offlinequizcron'), $job->jobtimecreated > 0 ? userdate($job->jobtimecreated, $strtimeformat) : '');
    $detailstable->data[] = array(get_string('timestart', 'report_offlinequizcron'), $job->jobtimestart > 0 ? userdate($job->jobtimestart, $strtimeformat) : '');
    $detailstable->data[] = array(get_string('timefinish', 'report_offlinequizcron'), $job->jobtimefinish > 0 ? userdate($job->jobtimefinish , $strtimeformat) : '');

    echo html_writer::table($detailstable);
    
    
    // Initialise the table.
    $tableparams = array();
    $reporturl = new moodle_url($CFG->wwwroot . '/report/offlinequizcron/index.php', array('jobid' => $job->id, 'pagesize' => $pagesize));

    $table = new offlinequizcron_job_files_table('offlinequizcronjobfiles');

    $tablecolumns = array('checkbox', 'id', 'filename', 'status', 'error');
    $tableheaders = array(
            '<input type="checkbox" name="toggle" onClick="if (this.checked) {select_all_in(\'DIV\',null,\'tablecontainer\');}
                else {deselect_all_in(\'DIV\',null,\'tablecontainer\');}"/>',
            get_string('jobid', 'report_offlinequizcron'),
            get_string('filename', 'report_offlinequizcron'),
            get_string('status', 'report_offlinequizcron'),
            get_string('error', 'report_offlinequizcron'));

    $table->define_columns($tablecolumns);
    $table->define_headers($tableheaders);
    $table->define_baseurl($CFG->wwwroot . '/report/offlinequizcron/index.php?jobid=' . $jobid . '&pagesize=' . $pagesize);
    $table->sortable(true);
    $table->no_sorting('checkbox');
    $table->setup();

    $sql = "SELECT *
              FROM {offlinequiz_queue_data} oqd
             WHERE queueid = :queueid
             ";

    $sqlparams = array('queueid' => $jobid);

    $sort = $table->get_sql_sort();
    if ($sort) {
        $sql .= "ORDER BY $sort";
    } else {
        $sql .= "ORDER BY id ASC";
    }

    $total = $DB->count_records('offlinequiz_queue_data', array('queueid' => $jobid));
    $table->pagesize($pagesize, $total);

    $files = $DB->get_records_sql($sql, $sqlparams, $table->get_page_start(), $table->get_page_size());

    foreach ($files as $file) {
//        if (file_exists($file->filename)) {
            $fileurl = new moodle_url($CFG->wwwroot . '/report/offlinequizcon/download.php', array('fileid' => $file->id));
            $pathparts = pathinfo($file->filename);
            $shortname = shorten_text($pathparts['basename']);
            $error = '';
            if (!empty($file->error)) {
                $error = get_string('error' . $file->error, 'offlinequiz_rimport');
            }
            $checkbox = '<input type="checkbox" name="' . $file->id . '" value="' . $file->id . '" />';

            $table->add_data(array(
                    $checkbox,
                    $file->id,
                    html_writer::link($fileurl, $shortname, array('title' => $file->filename)),
                    get_string('status' . $file->status, 'report_offlinequizcron'),
                    $error
            ));
  //      }
    }

    // Print it.
    echo $OUTPUT->heading(get_string('files', 'report_offlinequizcron'));
    $table->finish_html();

    echo '<div class="controls">';
    echo ' <form id="options" action="index.php" method="get">';
    echo '  <div class=centerbox>';
    echo '   <input type="hidden" id="jobid" name="jobid" value="' . $jobid . '" />';
    echo '   <table id="overview-options" class="boxaligncenter">';
    echo '    <tr align="left">';
    echo '     <td><label for="pagesize">'.get_string('pagesize', 'report_offlinequizcron').'</label></td>';
    echo '     <td><input type="text" id="pagesize" name="pagesize" size="3" value="' . $pagesize . '" /></td>';
    echo '    </tr>';
    echo '   </table>';
    echo '  </div>';
    echo ' </form>';
    echo '</div>';
    $backurl = new moodle_url($CFG->wwwroot . '/report/offlinequizcron/index.php');
    echo html_writer::link($backurl, get_string('backtomainpage', 'report_offlinequizcron'));
    echo $OUTPUT->box_end();

}
