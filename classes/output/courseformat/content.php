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
 * Contains the default content output class for ButtonsX format.
 *
 * @package   format_buttonsx
 * @copyright 2024 Tina John
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_buttonsx\output\courseformat;

use core_courseformat\output\local\content as content_base;
use renderer_base;
use stdClass;

/**
 * Base class to render ButtonsX course content.
 *
 * @package   format_buttonsx
 * @copyright 2024 Tina John
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content extends content_base {

    /**
     * @var bool ButtonsX format has add section after each topic.
     */
    protected $hasaddsection = true;

    /**
     * Get the name of the template to use for this templatable.
     *
     * @param renderer_base $renderer The renderer requesting the template name
     * @return string The template name
     */
    public function get_template_name(\renderer_base $renderer): string {
        return 'format_buttonsx/local/content';
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */
    public function export_for_template(renderer_base $output) {
        global $PAGE;
        
        // Load ButtonsX JavaScript modules
        $PAGE->requires->js_call_amd('format_buttonsx/mutations', 'init');
        $PAGE->requires->js_call_amd('format_buttonsx/section', 'init');
        
        $data = parent::export_for_template($output);
        
        // Add ButtonsX specific data
        $format = $this->format;
        $course = $format->get_course();
        $modinfo = get_fast_modinfo($course);
        
        // Get course format options
        $data->showdefaultsectionname = $course->showdefaultsectionname ?? 1;
        $data->sectionposition = $course->sectionposition ?? 0;
        $data->inlinesections = $course->inlinesections ?? 0;
        $data->usebottommenu = $course->usebottommenu ?? 0;
        $data->displayh5picons = $course->displayh5picons ?? 0;
        $data->act_complinfo_position = $course->act_complinfo_position ?? 0;
        $data->hilight = $course->hilight ?? 0;
        $data->hililast = $course->hililast ?? 0;
        $data->sequential = $course->sequential ?? 0;
        $data->sectiontype = $course->sectiontype ?? 'numeric';
        $data->buttonstyle = $course->buttonstyle ?? 'circle';
        
        // Add color configuration
        $data->colorcurrent = $this->get_color_config($course, 'colorcurrent');
        $data->colorvisible = $this->get_color_config($course, 'colorvisible');
        
        // Generate button navigation data
        $data->buttonnavdata = $this->get_button_navigation_data($course, $modinfo);
        
        return $data;
    }

    /**
     * Generate button navigation data for template.
     *
     * @param stdClass $course Course object
     * @param course_modinfo $modinfo Course module info
     * @return stdClass Button navigation data
     */
    protected function get_button_navigation_data($course, $modinfo) {
        $navdata = new stdClass();
        $navdata->course = $course;
        $navdata->sections = [];
        $navdata->divisors = [];
        $navdata->iscircle = ($course->buttonstyle === 'circle');
        $navdata->showbottommenu = (!empty($course->usebottommenu) && $course->usebottommenu == 1);
        
        $sections = $modinfo->get_section_info_all();
        $currentdivisor = 1;
        $count = 1;
        $currentbuttons = [];
        
        foreach ($sections as $section) {
            if ($section->section == 0) {
                continue;
            }
            
            if ($section->section > $course->numsections) {
                continue;
            }
            
            if ($course->hiddensections && !$section->visible) {
                continue;
            }
            
            // Check if we need to start a new divisor
            if (isset($course->{'divisor' . $currentdivisor}) &&
                $count > $course->{'divisor' . $currentdivisor} &&
                $course->{'divisor' . $currentdivisor} > 0) {
                // Save current divisor
                if (!empty($currentbuttons)) {
                    $navdata->divisors[] = [
                        'text' => format_string($course->{'divisortext' . $currentdivisor} ?? ''),
                        'buttons' => $currentbuttons,
                    ];
                }
                $currentdivisor++;
                $count = 1;
                $currentbuttons = [];
            }
            
            // Add button
            // Check if this divisor has only one section
            $divisorsize = isset($course->{'divisor' . $currentdivisor}) ? $course->{'divisor' . $currentdivisor} : 0;
            if ($divisorsize == 1) {
                // Single button in divisor - use special text
                $buttontext = isset($course->{'divisorsinglebuttext' . $currentdivisor}) 
                    ? format_string($course->{'divisorsinglebuttext' . $currentdivisor})
                    : '&bull;&bull;&bull;';
            } else {
                $buttontext = $this->get_button_text($course, $section->section);
            }
            
            $currentbuttons[] = [
                'num' => $section->section,
                'text' => $buttontext,
                'iscurrent' => ($course->marker == $section->section),
                'isvisible' => $section->visible,
            ];
            
            // Add to sections array for bottom menu
            $navdata->sections[] = [
                'num' => $section->section,
                'text' => $buttontext,
            ];
            
            $count++;
        }
        
        // Add last divisor
        if (!empty($currentbuttons)) {
            $divisortext = format_string($course->{'divisortext' . $currentdivisor} ?? '');
            $divisortext = str_replace('[br]', '<br>', $divisortext);
            $navdata->divisors[] = [
                'text' => $divisortext,
                'buttons' => $currentbuttons,
            ];
        }
        
        // If no divisors, create one default divisor with all buttons
        if (empty($navdata->divisors) && !empty($navdata->sections)) {
            $allbuttons = [];
            foreach ($navdata->sections as $sectiondata) {
                $allbuttons[] = [
                    'num' => $sectiondata['num'],
                    'text' => $sectiondata['text'],
                    'iscurrent' => ($course->marker == $sectiondata['num']),
                    'isvisible' => true,
                ];
            }
            $navdata->divisors[] = [
                'text' => '',
                'buttons' => $allbuttons,
            ];
        }
        
        return $navdata;
    }

    /**
     * Get button text based on section type.
     *
     * @param stdClass $course Course object
     * @param int $sectionnum Section number
     * @return string Button text
     */
    protected function get_button_text($course, $sectionnum) {
        $sectiontype = $course->sectiontype ?? 'numeric';
        
        switch ($sectiontype) {
            case 'roman':
                return $this->number_to_roman($sectionnum);
            case 'alphabet':
                return $this->number_to_alphabet($sectionnum);
            default:
                return $sectionnum;
        }
    }

    /**
     * Convert number to roman numeral.
     *
     * @param int $number
     * @return string
     */
    protected function number_to_roman($number) {
        $number = intval($number);
        $return = '';
        $romanarray = [
            'M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400,
            'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40,
            'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1
        ];
        foreach ($romanarray as $roman => $value) {
            $matches = intval($number / $value);
            $return .= str_repeat($roman, $matches);
            $number = $number % $value;
        }
        return $return;
    }

    /**
     * Convert number to alphabet.
     *
     * @param int $number
     * @return string
     */
    protected function number_to_alphabet($number) {
        $number = $number - 1;
        $alphabet = range("A", "Z");
        if ($number <= 25) {
            return $alphabet[$number];
        } else if ($number > 25) {
            $dividend = ($number + 1);
            $alpha = '';
            while ($dividend > 0) {
                $modulo = ($dividend - 1) % 26;
                $alpha = $alphabet[$modulo] . $alpha;
                $dividend = floor((($dividend - $modulo) / 26));
            }
            return $alpha;
        }
        return '';
    }

    /**
     * Get color configuration.
     *
     * @param stdClass $course Course object
     * @param string $name Color config name
     * @return string|false Color hex value or false
     */
    protected function get_color_config($course, $name) {
        $return = false;
        if (isset($course->{$name})) {
            $color = str_replace('#', '', $course->{$name});
            $color = substr($color, 0, 6);
            if (preg_match('/^#?[a-f0-9]{6}$/i', $color)) {
                $return = '#' . $color;
            }
        }
        return $return;
    }
}
