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
 * Contains the ButtonsX section state class.
 *
 * @package   format_buttonsx
 * @copyright 2024 Tina John
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_buttonsx\output\courseformat\state;

use core_courseformat\output\local\state\section as section_base;
use renderable;
use renderer_base;
use stdClass;

/**
 * Section state class for ButtonsX format with null-safe URL generation.
 *
 * @package   format_buttonsx
 * @copyright 2024 Tina John
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class section extends section_base {

    /**
     * Export this data so it can be used as state object in the course editor.
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */
    public function export_for_template(renderer_base $output): stdClass {
        $format = $this->format;
        $section = $this->section;
        $course = $format->get_course();

        $indexcollapsed = false;
        $contentcollapsed = false;
        $preferences = $format->get_sections_preferences();
        if (isset($preferences[$section->id])) {
            $sectionpreferences = $preferences[$section->id];
            if (!empty($sectionpreferences->contentcollapsed)) {
                $contentcollapsed = true;
            }
            if (!empty($sectionpreferences->indexcollapsed)) {
                $indexcollapsed = true;
            }
        }

        // Get section URL with null safety check - THIS IS THE FIX!
        $sectionurl = course_get_url($course, $section->section, ['navigation' => true]);
        $sectionurlstring = $sectionurl ? $sectionurl->out(false) : '';

        $data = (object)[
            'id' => $section->id,
            'section' => $section->section,
            'number' => $section->section,
            'title' => $format->get_section_name($section),
            'hassummary' => !empty($section->summary),
            'rawtitle' => $section->name,
            'cmlist' => [],
            'visible' => !empty($section->visible),
            'sectionurl' => $sectionurlstring,
            'current' => $format->is_section_current($section),
            'indexcollapsed' => $indexcollapsed,
            'contentcollapsed' => $contentcollapsed,
            'hasrestrictions' => $this->get_has_restrictions(),
            'bulkeditable' => $this->is_bulk_editable(),
            'component' => $section->component,
            'itemid' => $section->itemid,
        ];

        $modinfo = $format->get_modinfo();
        
        if (empty($modinfo->sections[$section->section])) {
            return $data;
        }

        foreach ($modinfo->sections[$section->section] as $modnumber) {
            $mod = $modinfo->cms[$modnumber];
            if ($section->uservisible && $mod->is_visible_on_course_page()) {
                $data->cmlist[] = $mod->id;
            }
        }

        return $data;
    }
}
