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

class offlinequiz_jobs_table extends flexible_table {
    public function __construct($uniqueid) {
        parent::__construct($uniqueid);
    }

    public function print_nothing_to_display() {
        global $OUTPUT;
        return;
    }

    public function wrap_html_start() {
        $strreallydel  = addslashes(get_string('deleteresultcheck', 'offlinequiz'));
        echo '<div id="tablecontainer" class="centerbox">';
        echo '  <center>';
    }

    public function wrap_html_finish() {
        echo '  </center>';
        // Close form
        echo ' </div>';
    }

//     protected function print_one_initials_bar($alpha, $current, $class, $title, $urlvar) {
//         echo html_writer::start_tag('div', array('class' => 'initialbar linkbox ' . $class)) .
//         $title . ' : ';
//         if ($current) {
//             echo html_writer::link($this->baseurl->out(false, array($urlvar => '')), get_string('all'));
//         } else {
//             echo html_writer::tag('strong', get_string('all'));
//         }
//         echo '&nbsp;';

//         foreach ($alpha as $letter) {
//             if ($letter === $current) {
//                 echo html_writer::tag('strong', $letter);
//             } else {
//                 echo html_writer::link($this->baseurl->out(false, array($urlvar => $letter)), $letter);
//             }
//             echo '&nbsp;';
//         }

//         echo html_writer::end_tag('div');
//     }

} // end class
