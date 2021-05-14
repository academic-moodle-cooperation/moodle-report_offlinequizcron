<?php


namespace report_offlinequinzcron;

defined('MOODLE_INTERNAL') || die();

class job_files_table extends flexible_table {

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