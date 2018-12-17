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
 * Handles displaying the calendar block.
 *
 * @package    block_calendar_month
 * @copyright  2004 Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_calendar_month extends block_base {

    /**
     * Initialise the block.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_calendar_month');
    }

    /**
     * Return the content of this block.
     *
     * @return stdClass the content
     */
    public function get_content() {
        global $CFG;

        require_once($CFG->dirroot.'/calendar/lib.php');

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        $courseid = $this->page->course->id;
        $categoryid = ($this->page->context->contextlevel === CONTEXT_COURSECAT && !empty($this->page->category)) ?
            $this->page->category->id : null;
        $calendar = \calendar_information::create(time(), $courseid, $categoryid);

        //Preparando a data para colocar 3 meses na pagina
        $calendartype = \core_calendar\type_factory::get_calendar_instance();

        $date = $calendartype->timestamp_to_date_array(time());

        $prevmonth = calendar_sub_month($date['mon'], $date['year']);
        $prevmonthtime = $calendartype->convert_to_gregorian($prevmonth[1], $prevmonth[0], 1);
        $time_a = make_timestamp($prevmonthtime['year'], $prevmonthtime['month'], $prevmonthtime['day'],
          $prevmonthtime['hour'], $prevmonthtime['minute']);

        $nextmonth = calendar_add_month($date['mon'], $date['year']);
        $nextmonthtime = $calendartype->convert_to_gregorian($nextmonth[1], $nextmonth[0], 1);
        $time_p = make_timestamp($nextmonthtime['year'], $nextmonthtime['month'], $nextmonthtime['day'],
          $nextmonthtime['hour'], $nextmonthtime['minute']);

        $calendar_a = \calendar_information::create($time_a, $courseid, $categoryid);
        $calendar_p = \calendar_information::create($time_p, $courseid, $categoryid);
        //FInal da preparacao da data

        list($data_a, $template_a) = calendar_get_view($calendar_a, 'minithree', false, isloggedin());
        list($data, $template) = calendar_get_view($calendar, 'minithree', false, isloggedin());
        list($data_p, $template_p) = calendar_get_view($calendar_p, 'minithree', false, isloggedin());

        $renderer = $this->page->get_renderer('core_calendar');
        $this->content->text .= $renderer->render_from_template($template_a, $data_a);
        $this->content->text .= $renderer->render_from_template($template, $data);
        $this->content->text .= $renderer->render_from_template($template_p, $data_p);

        if ($this->page->course->id != SITEID) {
            $this->content->text .= $renderer->event_filter();
        }

        return $this->content;
    }
}
