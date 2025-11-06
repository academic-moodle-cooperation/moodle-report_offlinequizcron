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
 * Download script for the offlinequiz evaluation cronjob.
 *
 * @package       report_offlinequizcron
 * @author        Juergen Zimmer
 * @copyright     2013 The University of Vienna
 * @since         Moodle 2.5.3
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 **/

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/filelib.php');

$fileid = optional_param('fileid', 0, PARAM_INT);
$jobid = optional_param('jobid', 0, PARAM_INT);
$downloadall = optional_param('downloadall', 0, PARAM_INT);

require_login();

if (!has_capability('moodle/site:config', context_system::instance())) {
    // The requested section could depend on a different capability
    // but most likely the user has inadequate capabilities.
    print_error('accessdenied', 'admin');
    die;
}

if ($fileid && $file = $DB->get_record('offlinequiz_queue_data', ['id' => $fileid])) {
    // Download for single image files.
    if (!file_exists($file->filename)) {
        send_file_not_found();
    } else {
        $pathparts = pathinfo($file->filename);
        $shortname = $pathparts['basename'];
        send_file($file->filename, $shortname, 'default', 0, false, true);
    }
} else if ($jobid && $downloadall && $job = $DB->get_record('offlinequiz_queue', ['id' => $jobid])) {
    // Download all files of a job as a ZIP archive.
    $files = $DB->get_records('offlinequiz_queue_data', ['queueid' => $job->id]);
    $offlinequizid = $DB->get_field('offlinequiz_queue', 'offlinequizid', ['id' => $job->id]);
    $offlinequiz = $DB->get_record('offlinequiz', ['id' => $offlinequizid]);
    $shortname = $DB->get_field('course', 'shortname', ['id' => $offlinequiz->course]);
    $zipfilename = $shortname . '_' . $offlinequiz->name;

    $filelist = [];

    foreach ($files as $file) {
        if (file_exists($file->filename)) {
            $pathparts = pathinfo($file->filename);
            $filelist[$pathparts['basename']] = $file->filename;
        }
    }

    $zipper = new zip_packer();
    $tempzip = tempnam($CFG->tempdir . '/', 'offlinequizcronfiles');

    if ($zipper->archive_to_pathname($filelist, $tempzip)) {
        send_temp_file($tempzip, clean_filename($zipfilename) . '.zip');
    }
} else {
    // Download selected files as a ZIP archive.
    $rawdata = (array) data_submitted();
    $fileids = [];

    foreach ($rawdata as $key => $value) {
        if (preg_match('!^fileids([0-9]+)$!', $key)) {
            $fileids[] = clean_param($value, PARAM_INT);
        }
    }

    if (!empty($fileids)) {
        require_once($CFG->libdir . '/filestorage/zip_packer.php');

        $filelist = [];
        foreach ($fileids as $fileid) {
            $file = $DB->get_record('offlinequiz_queue_data', ['id' => $fileid]);
            if (file_exists($file->filename)) {
                $pathparts = pathinfo($file->filename);
                $filelist[$pathparts['basename']] = $file->filename;
            }
        }

        $jobid = $DB->get_field('offlinequiz_queue_data', 'queueid', ['id' => $fileids[0]]);
        $offlinequizid = $DB->get_field('offlinequiz_queue', 'offlinequizid', ['id' => $jobid]);
        $offlinequiz = $DB->get_record('offlinequiz', ['id' => $offlinequizid]);
        $shortname = $DB->get_field('course', 'shortname', ['id' => $offlinequiz->course]);
        $zipfilename = $shortname . '_' . $offlinequiz->name;

        $zipper = new zip_packer();
        $tempzip = tempnam($CFG->tempdir . '/', 'offlinequizcronfiles');

        if ($zipper->archive_to_pathname($filelist, $tempzip)) {
            send_temp_file($tempzip, clean_filename($zipfilename) . '.zip');
        }
    }
}
