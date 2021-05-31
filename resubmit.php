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
 * Script for re-submitting an offlinequiz evaluation cronjob.
 *
 * @package       report_offlinequizcron
 * @author        Juergen Zimmer
 * @copyright     2013 The University of Vienna
 * @since         Moodle 2.5.3
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 **/

require(dirname(__FILE__).'/../../config.php');
require_once($CFG->libdir . '/filelib.php');

$jobid = optional_param('jobid', 0, PARAM_INT);
$pagesize = optional_param('pagesize', 20, PARAM_INT);
$statusnew = optional_param('statusnew', 0, PARAM_INT);
$statusprocessing = optional_param('statusprocessing', 0, PARAM_INT);
$statusfinished = optional_param('statusfinished', 0, PARAM_INT);

require_login();
require_sesskey();

if (!has_capability('moodle/site:config', context_system::instance())) {
    // The requested section could depend on a different capability
    // but most likely the user has inadequate capabilities.
    print_error('accessdenied', 'admin');
    die;
}

if ($jobid && $job = $DB->get_record('offlinequiz_queue', array('id' => $jobid))) {
    $files = $DB->get_records('offlinequiz_queue_data', array('queueid' => $job->id));

    $job->status = 'new';
    $job->timecreated = time();
    $job->timestart = 0;
    $job->timefinish = 0;
    $DB->update_record('offlinequiz_queue', $job);

    foreach ($files as $file) {
        $file->status = 'new';
        $file->error = null;
        $DB->update_record('offlinequiz_queue_data', $file);
    }
    redirect(new moodle_url($CFG->wwwroot . '/report/offlinequizcron/index.php', array('pagesize' => $pagesize,
                      'statusnew' => $statusnew,
                      'statusprocessing' => $statusprocessing,
                      'statusfinished' => $statusfinished)));
}

