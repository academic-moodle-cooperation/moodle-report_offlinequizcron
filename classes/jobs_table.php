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
 * Table for lists of offlinequiz evaluation cronjobs.
 *
 * @copyright  2013 The University of Vienna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class jobs_table extends \flexible_table {

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
}