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

namespace report_offlinequizcron;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');

/**
 * Table for the files of an offlinequiz evaluation cronjob.
 *
 * @copyright  2013 The University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class job_files_table extends \flexible_table {

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