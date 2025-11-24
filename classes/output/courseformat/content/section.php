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
 * Contains the default section output class for ButtonsX format.
 *
 * @package   format_buttonsx
 * @copyright 2024 Tina John
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_buttonsx\output\courseformat\content;

use core_courseformat\output\local\content\section as section_base;
use renderer_base;
use stdClass;
use context_course;

/**
 * Base class to render a course section for ButtonsX format.
 *
 * @package   format_buttonsx
 * @copyright 2024 Tina John
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class section extends section_base {

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */
    public function export_for_template(renderer_base $output): stdClass {
        $data = parent::export_for_template($output);
        
        $format = $this->format;
        $course = $format->get_course();
        $section = $this->section;
        
        // Add ButtonsX specific section data
        $data->buttonsx = new stdClass();
        $data->buttonsx->sectionnum = $section->section;
        $data->buttonsx->ishidden = !$section->visible;
        
        // Determine section button text based on sectiontype
        $sectiontype = $course->sectiontype ?? 'numeric';
        switch ($sectiontype) {
            case 'roman':
                $data->buttonsx->buttontext = $format->number_to_roman($section->section);
                break;
            case 'alphabet':
                $data->buttonsx->buttontext = $format->number_to_alphabet($section->section);
                break;
            default:
                $data->buttonsx->buttontext = $section->section;
        }
        
        // Check if this section is current/highlighted
        $data->buttonsx->iscurrent = ($course->marker == $section->section);
        
        // Sequential access check
        if (!empty($course->sequential)) {
            $data->buttonsx->sequential = true;
            // Check if previous section is completed
            if ($section->section > 1) {
                $data->buttonsx->canaccesssection = $this->can_access_section($section->section - 1);
            } else {
                $data->buttonsx->canaccesssection = true;
            }
        } else {
            $data->buttonsx->sequential = false;
            $data->buttonsx->canaccesssection = true;
        }
        
        return $data;
    }

    /**
     * Check if user can access a section based on sequential completion.
     *
     * @param int $previoussectionnum Previous section number
     * @return bool
     */
    protected function can_access_section($previoussectionnum) {
        global $USER;
        
        $format = $this->format;
        $course = $format->get_course();
        $modinfo = get_fast_modinfo($course);
        
        $prevsection = $modinfo->get_section_info($previoussectionnum);
        if (!$prevsection) {
            return true;
        }
        
        // Check completion of all activities in previous section
        $completion = new \completion_info($course);
        if (!$completion->is_enabled()) {
            return true;
        }
        
        $cms = $modinfo->get_cms();
        foreach ($cms as $cm) {
            if ($cm->sectionnum == $previoussectionnum) {
                $cmcompletion = $completion->get_data($cm, false, $USER->id);
                if ($cmcompletion->completionstate == COMPLETION_INCOMPLETE) {
                    return false;
                }
            }
        }
        
        return true;
    }
}
