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
require_once($CFG->libdir . '/tablelib.php');

/**
 * Table for lists of offlinequiz evaluation cronjobs.
 *
 * @copyright  2013 The University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class offlinequizcron_jobs_table extends flexible_table {

    /**
     * @var $reportscript
     */
    protected $reportscript;
    /**
     * @var $params
     */
    protected $params;


    /**
     * A function that always returns null
     *
     */
    public function print_nothing_to_display() {
        global $OUTPUT;
        return;
    }

    /**
     * Generates start tags
     */
    public function wrap_html_start() {
        echo '<div id="tablecontainer" class="centerbox">';
        echo '<center>';
    }

    /**
     * Generates end tags
     */
    public function wrap_html_finish() {
        echo '  </center>';
        echo ' </div>';
    }
} // end class

/**
 * Table for the files of an offlinequiz evaluation cronjob.
 *
 * @copyright  2013 The University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class offlinequizcron_job_files_table extends flexible_table {

    /**
     * @var $reportscript
     */
    protected $reportscript;
    /**
     * @var $params
     */
    protected $params;

    /**
     * offlinequizcron_job_files_table constructor.
     *
     * @param int $uniqueid
     * @param string $reportscript
     * @param string|array $params
     */
    public function __construct($uniqueid, $reportscript, $params) {
        parent::__construct($uniqueid);
        $this->reportscript = $reportscript;
        $this->params = $params;
    }

    /**
     * A function that always returns null
     */
    public function print_nothing_to_display() {
        global $OUTPUT;
        echo $OUTPUT->heading(get_string('nofiles', 'report_offlinequizcron'), 3);
        return;
    }

    /**
     * Generates start tags
     */
    public function wrap_html_start() {
        echo '<br/><center>';
        echo '<div id="tablecontainer" class="filestable">';
        echo ' <form id="filesform" method="post" action="'. $this->reportscript . '" >';

        foreach ($this->params as $name => $value) {
            echo '  <input type="hidden" name="' . $name .'" value="' . $value . '" />';
        }
        echo '  <input type="hidden" name="sesskey" value="' . sesskey() . '" />';
    }

    /**
     * Generates end tags
     *
     * @throws coding_exception
     */
    public function wrap_html_finish() {
        $strselectall = get_string('selectall', 'offlinequiz');
        $strselectnone = get_string('selectnone', 'offlinequiz');

        echo '<div class="commandsdiv">';
        echo '<table id="commands" algin="left">';
        echo ' <tr><td>';
        echo '  <a href="#" id="filesform-select">'. $strselectall . '</a> / ';
        echo '  <a href="#" id="filesform-deselect">' . $strselectnone . '</a> ';
        echo '  &nbsp;&nbsp;';
        echo '  <input type="submit" class="btn btn-secondary" value="'.
                get_string('downloadselected', 'report_offlinequizcron') . '"/>';
        echo '  </td></tr></table>';
        echo ' </form>';
        echo '</div>'; // Tablecontainer!
        // Close form!
        echo '</center>';
        echo '<script> Y.one(\'#filesform-deselect\').on(\'click\',
            function(evt) {evt.preventDefault();Y.all(\'.filesformcheckbox\').set(\'checked\', \'\');});';
        echo 'Y.one(\'#filesform-select\').on(\'click\',
        function(evt) {evt.preventDefault();Y.all(\'.filesformcheckbox\').set(\'checked\', \'true\');});';
        echo '</script>';
    }
}


/**
 * Displays the list of offlinequiz evaluation cronjobs.
 */
function offlinequizcron_display_job_list() {
    global $CFG, $DB, $OUTPUT;
    $searchterm = optional_param('searchterm', '', PARAM_TEXT);
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

    $baseurl = new moodle_url($CFG->wwwroot . '/report/offlinequizcron/index.php', array('pagesize' => $pagesize));
    $tablebaseurl = new moodle_url($CFG->wwwroot . '/report/offlinequizcron/index.php',
            array('statusnew' => $statusnew,
                  'statusprocessing' => $statusprocessing,
                  'statusfinished' => $statusfinished,
                  'pagesize' => $pagesize));

    echo $OUTPUT->header();
    echo $OUTPUT->box_start('centerbox');
    echo $OUTPUT->heading_with_help(get_string('offlinequizjobs',
            'report_offlinequizcron'), 'offlinequizjobs', 'report_offlinequizcron');

    // Initialise the table.
    $statusvalues = array('statusnew' => $statusnew, 'statusprocessing' => $statusprocessing, 'statusfinished' => $statusfinished);

    // Print checkboxes for status filters.
    echo '<form id="reportform" method="get" action="'. $baseurl . '" class="form" >';
    echo get_string('showjobswithstatus', 'report_offlinequizcron') . ': &nbsp;&nbsp;&nbsp;';
    foreach ($statusvalues as $name => $value) {
        if ($value) {
            $checked = 'checked="checked"';
        } else {
            $checked = '';
        }
        echo '<input type="checkbox" name="' . $name .'" value="1" ' . $checked . '/>'
                . get_string($name, 'report_offlinequizcron') . '&nbsp;&nbsp;&nbsp;&nbsp;';
    }
    echo '<br/><div class="form-group ">';
    echo '   <label for="search">' . get_string('search', 'report_offlinequizcron') . '</label>&nbsp;&nbsp;';
    echo '   <input type="text" id="search" name="searchterm" size="20" class="" value="' . $searchterm . '" />';
    echo '   <input type="submit" value="' . get_string('apply', 'report_offlinequizcron') . '" class="btn btn-secondary" />';
    echo '</div>';
    echo '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';

    echo '</form><br/>';

    echo '<label class="processqueue"><a href="' . $CFG->wwwroot . '/report/offlinequizcron/processqueue.php">'
             . get_string('processqueue', 'report_offlinequizcron') . '</a></label><br/>';

    // Print the table of offlinequiz evaluation jobs.
    $table = new offlinequizcron_jobs_table('offlinequizcronadmin');

    $tablecolumns = array('id', 'status', 'oqname', 'cshortname', 'lastname', 'jobtimecreated', 'jobtimestart', 'jobtimefinish');
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
    $table->define_baseurl($tablebaseurl);
    $table->sortable(true);
    $table->setup();

    $sort = $table->get_sql_sort();

    $sql = "SELECT oqq.id, oqq.status as status,
                   oqq.timecreated as jobtimecreated, oqq.timestart as jobtimestart, oqq.timefinish as jobtimefinish,
                   oq.id as oqid, oq.name as oqname,
                   c.shortname as cshortname, c.id as cid,
                   u.id as uid, u.firstname as firstname, u.lastname as lastname, u.alternatename, u.middlename, u.firstnamephonetic, u.lastnamephonetic
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
    } else {
        $sql .= "ORDER BY id DESC";
    }

    $total = $DB->count_records_sql($countsql, $sqlparams);
    $table->pagesize($pagesize, $total);

    $jobs = $DB->get_records_sql($sql, $sqlparams, $table->get_page_start(), $table->get_page_size());

    $strtimeformat = get_string('strftimedatetime');
    foreach ($jobs as $job) {
        $joburl = new moodle_url($CFG->wwwroot . '/report/offlinequizcron/index.php', array('jobid' => $job->id,
                      'statusnew' => $statusnew,
                      'statusprocessing' => $statusprocessing,
                      'statusfinished' => $statusfinished,
                      'pagesize' => $pagesize));
        $offlinequizurl = new moodle_url($CFG->wwwroot . '/mod/offlinequiz/view.php', array('q' => $job->oqid));
        $courseurl = new moodle_url($CFG->wwwroot . '/course/view.php', array('id' => $job->cid));
        $userurl = new moodle_url($CFG->wwwroot . '/user/profile.php', array('id' => $job->uid));

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

    echo '<div class="controls">';
    echo ' <form id="options" action="index.php" method="get">';
    echo '     <label for="pagesize">' . get_string('pagesize', 'report_offlinequizcron') . '</label>&nbsp;&nbsp;';
    foreach ($statusvalues as $name => $value) {
        echo '     <input type="hidden" name="' . $name .'" value="' . $value . '"/>';
    }
    echo '     <input type="text" id="pagesize" name="pagesize" size="3" value="' . $pagesize . '" />';
    echo ' </form>';
    echo '</div>';

    echo $OUTPUT->box_end();
}

/**
 * Resolves a given sql status number with a string definition
 *
 * @param int $statusnumber
 * @return string
 */
function get_status_sql($statusnumber) {
    switch($statusnumber) {
        case 1:
         return " AND oqd.status = 'error' ";
        case 2:
         return " AND oqd.status = 'processed' ";
        default:
         return "";
    }
}

/**
 * Returns the tag of the selected option
 *
 * @param string $option
 * @param string $optionselected
 * @return string
 */
function get_option($option, $optionselected) {
    if ($option == $optionselected) {
        return '<option value="' .$option . '" selected="true">';
    } else {
        return '<option value="' .$option . '">';
    }
}

/**
 * Displays the list of files of an evaluation cronjob.
 *
 * @param int $jobid
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 */
function offlinequizcron_display_job_details($jobid) {
    global $CFG, $DB, $OUTPUT;

    $deleteid = optional_param('deleteid', 0, PARAM_INT);
    $statusnew = optional_param('statusnew', 0, PARAM_INT);
    $statusprocessing = optional_param('statusprocessing', 0, PARAM_INT);
    $statusfinished = optional_param('statusfinished', 0, PARAM_INT);
    $statusselected = optional_param('statusselected', 0, PARAM_INT);

    $pagesize = optional_param('pagesize', 20, PARAM_INT);
    if ($pagesize < 10) {
        $pagesize = 10;
    }

    // Delete a job from the DB.
    if ($deleteid && $deletejob = $DB->get_record('offlinequiz_queue', array('id' => $deleteid))) {
        if ($files = $DB->get_records('offlinequiz_queue_data', array('queueid' => $deletejob->id))) {
            $file = array_pop($files);
            $pathparts = pathinfo($file->filename);
            $dirname = $pathparts['dirname'];
            remove_dir($dirname);
            $DB->delete_records('offlinequiz_queue_data', array('queueid' => $deletejob->id));
        }
        $DB->delete_records('offlinequiz_queue', array('id' => $deletejob->id));
        redirect(new moodle_url($CFG->wwwroot . '/report/offlinequizcron/index.php', array('statusnew' => $statusnew,
                  'statusprocessing' => $statusprocessing,
                  'statusfinished' => $statusfinished,
                  'pagesize' => $pagesize)));
    }

    $sql = "SELECT oqq.id, oqq.status,
                   oqq.timecreated as jobtimecreated, oqq.timestart as jobtimestart, oqq.timefinish as jobtimefinish,
                   oq.id as oqid, oq.name as oqname,
                   c.shortname as cshortname, c.id as cid,
                   u.id as uid, u.firstname as firstname, u.lastname as lastname, u.alternatename, u.middlename,
                      u.firstnamephonetic, u.lastnamephonetic
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

    $total = $DB->count_records('offlinequiz_queue_data', array('queueid' => $jobid));

    echo $OUTPUT->header();
    echo $OUTPUT->box_start('centerbox');
    echo $OUTPUT->heading(get_string('offlinequizjobdetails', 'report_offlinequizcron', $job->id));
    echo html_writer::empty_tag('br');

    $reporturl = new moodle_url($CFG->wwwroot . '/report/offlinequizcron/index.php', array('jobid' => $job->id));
    $downloadurl = new moodle_url($CFG->wwwroot . '/report/offlinequizcron/download.php');
    $resubmiturl = new moodle_url($CFG->wwwroot . '/report/offlinequizcron/resubmit.php');
    $offlinequizurl = new moodle_url($CFG->wwwroot . '/mod/offlinequiz/view.php', array('q' => $job->oqid));
    $courseurl = new moodle_url($CFG->wwwroot . '/course/view.php', array('id' => $job->cid));
    $userurl = new moodle_url($CFG->wwwroot . '/user/profile.php', array('id' => $job->uid));

    $detailstable = new html_table();
    $detailstable->id = 'jobdetailstable';
    $detailstable->align = array('left', 'right');
    $detailstable->attributes = array('align' => 'center');

    $strtimeformat = get_string('strftimedatetime');
    $detailstable->data[] = array(get_string('status', 'report_offlinequizcron'), get_string('status'
            . $job->status, 'report_offlinequizcron'));
    $detailstable->data[] = array(get_string('pluginname', 'offlinequiz'), html_writer::link($offlinequizurl, $job->oqname));
    $detailstable->data[] = array(get_string('course'), html_writer::link($courseurl, $job->cshortname));
    $detailstable->data[] = array(get_string('importuser', 'report_offlinequizcron'), html_writer::link($userurl, fullname($job)));
    $detailstable->data[] = array(get_string('timecreated', 'report_offlinequizcron'),
            $job->jobtimecreated > 0 ? userdate($job->jobtimecreated, $strtimeformat) : '');
    $detailstable->data[] = array(get_string('timestart', 'report_offlinequizcron'),
            $job->jobtimestart > 0 ? userdate($job->jobtimestart, $strtimeformat) : '');
    $detailstable->data[] = array(get_string('timefinish', 'report_offlinequizcron'),
            $job->jobtimefinish > 0 ? userdate($job->jobtimefinish , $strtimeformat) : '');
    $detailstable->data[] = array(get_string('evaluatedfiles', 'report_offlinequizcron'), $total);
    echo html_writer::table($detailstable);

    $disabled = '';
    if (!$total) {
        $disabled = 'disabled="disabled"';
    }

    // Print button to re-submit job.
    echo '<center><div class="buttons">';
    echo '<div class="resubmitbutton">';
    echo '<form id="reportform" method="post" action="'. $resubmiturl . '" >';
    echo ' <input type="hidden" name="jobid" value="' . $job->id . '" />';
    echo ' <input type="hidden" name="statusnew" value="' .$statusnew . '" />';
    echo ' <input type="hidden" name="statusprocessing" value="' . $statusprocessing . '" />';
    echo ' <input type="hidden" name="statusfinished" value="' . $statusfinished . '" />';
    echo ' <input type="hidden" name="pagesize" value="' . $pagesize . '" />';
    echo ' <input type="hidden" name="statusselected" value="' . $statusselected . '" />';
    echo ' <input class="btn btn-secondary" type="submit" value="' . get_string('resubmitjob', 'report_offlinequizcron') .
             '" ' . $disabled . '"/>';
    echo '</form>';
    echo '</div>';

    // Print button for deleting the job.
    $strreallydel  = addslashes(get_string('deletejobcheck', 'report_offlinequizcron'));
    echo '<div class="deletebutton">';
    echo '<form id="reportform" method="post" action="'. $reporturl . '" onsubmit="return confirm(\'' . $strreallydel . '\');">';
    echo ' <input type="hidden" name="jobid" value="' . $job->id . '" />';
    echo ' <input type="hidden" name="deleteid" value="' . $job->id . '" />';
    echo ' <input type="hidden" name="statusnew" value="' .$statusnew . '" />';
    echo ' <input type="hidden" name="statusprocessing" value="' . $statusprocessing . '" />';
    echo ' <input type="hidden" name="statusfinished" value="' . $statusfinished . '" />';
    echo ' <input type="hidden" name="pagesize" value="' . $pagesize . '" />';
    echo ' <input type="hidden" name="statusselected" value="' . $statusselected . '" />';
    echo ' <input class="btn btn-secondary" type="submit" value="' . get_string('deletejob', 'report_offlinequizcron') . '" />';
    echo '</form>';
    echo '</div>';

    // Print button for downloading all files of this job.
    echo '<div class="downloadbutton">';
    echo '<form id="reportform" method="post" action="'. $downloadurl . '" >';
    echo ' <input type="hidden" name="jobid" value="' . $job->id . '" />';
    echo ' <input type="hidden" name="downloadall" value="1" />';
    echo ' <input type="submit" class="btn btn-secondary" value="' .
            get_string('downloadallfiles', 'report_offlinequizcron') . '" ' .
        $disabled . '/>';
    echo '</form>';
    echo '</div>';
    echo '</div></center><br/>';

    echo $OUTPUT->heading_with_help(get_string('files', 'report_offlinequizcron'), 'files', 'report_offlinequizcron');

    // Initialise the table.
    $table = new offlinequizcron_job_files_table('offlinequizcronjobfiles', $downloadurl,
            array('jobid' => $job->id, 'pagesize' => $pagesize));

    $tablecolumns = array('checkbox', 'id', 'filename', 'status', 'error');
    $tableheaders = array(
            html_writer::empty_tag('input', ['type' => 'checkbox', 'name' => 'toggle',
                    'onClick' => 'if (this.checked) {$(\'.filesformcheckbox\').prop(\'checked\', true);}
                else {$(\'.filesformcheckbox\').prop(\'checked\', false);}']),
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

    $sql .= get_status_sql($statusselected);

    $sort = $table->get_sql_sort();
    if ($sort) {
        $sql .= "ORDER BY $sort";
    } else {
        $sql .= "ORDER BY id ASC";
    }

    $table->pagesize($pagesize, $total);

    $files = $DB->get_records_sql($sql, $sqlparams, $table->get_page_start(), $table->get_page_size());

    foreach ($files as $file) {
        $fileurl = new moodle_url($CFG->wwwroot . '/report/offlinequizcron/download.php', array('fileid' => $file->id));
        $pathparts = pathinfo($file->filename);
        $shortname = shorten_text($pathparts['basename']);
        $error = '';
        if (!empty($file->error)) {
            $error = get_string('error' . $file->error, 'offlinequiz_rimport');
        }

        if (file_exists($file->filename)) {
            $checkbox = '<input type="checkbox" name="fileids' . $file->id . '" value="' . $file->id .
                    '" class="filesformcheckbox"/>';
            $table->add_data(array(
                    $checkbox,
                    $file->id,
                    html_writer::link($fileurl, $shortname, array('title' => $file->filename)),
                    get_string('status' . $file->status, 'report_offlinequizcron'),
                    $error
            ));
        } else {
            $checkbox = '<input type="checkbox" name="fileids' . $file->id . '" value="' . $file->id .
                    '" disabled="disabled" class="filesformcheckbox" />';
            $table->add_data(array(
                    $checkbox,
                    $file->id,
                    html_writer::span($shortname, '', array('title' => $file->filename)),
                    get_string('status' . $file->status, 'report_offlinequizcron'),
                    $error
            ));
        }
    }

    // Print it.
    $table->finish_html();
    echo '<center>';
    echo '<div class="controls">';
    echo ' <form id="options" action="index.php" method="get">';
    echo '   <input type="hidden" id="jobid" name="jobid" value="' . $job->id . '" />';
    echo '   <label for="pagesize">' . get_string('pagesize', 'report_offlinequizcron') . '</label>&nbsp;&nbsp;';
    echo '   <input type="text" id="pagesize" name="pagesize" size="3" value="' . $pagesize . '" />';
    echo '   <label for="statusselected" > ' . get_string('status', 'report_offlinequizcron') . "</label>";
    echo '   <select id="statusselected" name="statusselected"/>';
    echo '      ' . get_option(0, $statusselected) . get_string('statusall', 'report_offlinequizcron') . '</option>';
    echo '      ' . get_option(1, $statusselected) . get_string('statuserror', 'report_offlinequizcron') . '</option>';
    echo '      ' . get_option(2, $statusselected) . get_string('statusprocessed', 'report_offlinequizcron') . '</option>';
    echo '   </select>';
    echo '   <input type="submit" id="submit" />';
    echo ' </form>';
    echo '</div><br/>';

    $backurl = new moodle_url($CFG->wwwroot . '/report/offlinequizcron/index.php', array(
                      'statusnew' => $statusnew,
                      'statusprocessing' => $statusprocessing,
                      'statusfinished' => $statusfinished));
    echo html_writer::link($backurl, get_string('backtomainpage', 'report_offlinequizcron'));
    echo '</center>';
    echo $OUTPUT->box_end();
}
