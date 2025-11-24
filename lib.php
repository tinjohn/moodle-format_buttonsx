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
 * format_buttonsx_renderer
 *
 * @package    format_buttonsx
 * @author     Rodrigo Brandão <https://www.linkedin.com/in/brandaorodrigo>
 * @copyright  2020 Rodrigo Brandão <rodrigo.brandao.contato@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot. '/course/format/lib.php');

use core\output\inplace_editable;

/**
 * format_buttonsx
 *
 * @package    format_buttonsx
 * @author     Tina John
 * @author     based on work by Rodrigo Brandão
 * @copyright  2024 Tina John
 * @copyright  based on work 2017 Rodrigo Brandão
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_buttonsx extends core_courseformat\base {

    /**
     * Returns true if this course format uses sections.
     *
     * @return bool
     */
    public function uses_sections() {
        return true;
    }

    /**
     * Returns true if this course format uses course index.
     *
     * @return bool
     */
    public function uses_course_index() {
        return true;
    }

    /**
     * Returns true if this course format uses activity indentation.
     *
     * @return bool
     */
    public function uses_indentation(): bool {
        return (get_config('format_buttonsx', 'indentation')) ? true : false;
    }

    /**
     * Returns the information about the ajax support in the given source format.
     *
     * @return stdClass
     */
    public function supports_ajax() {
        $ajaxsupport = new stdClass();
        $ajaxsupport->capable = true;
        return $ajaxsupport;
    }

    /**
     * Whether this format supports components.
     *
     * @return bool
     */
    public function supports_components() {
        return true;
    }

    /**
     * Returns the display name of the given section.
     *
     * @param int|stdClass $section Section object from database or just field section.section
     * @return string Display name
     */
    public function get_section_name($section) {
        $section = $this->get_section($section);
        if ((string)$section->name !== '') {
            return format_string($section->name, true,
                ['context' => context_course::instance($this->courseid)]);
        } else {
            return $this->get_default_section_name($section);
        }
    }

    /**
     * Returns the default section name.
     *
     * @param int|stdClass $section Section object from database or just field course_sections section
     * @return string The default value for the section name.
     */
    public function get_default_section_name($section) {
        $section = $this->get_section($section);
        if ($section->section == 0) {
            return get_string('section0name', 'format_buttonsx');
        }
        
        $course = $this->get_course();
        $sectiontype = $course->sectiontype ?? 'numeric';
        
        switch ($sectiontype) {
            case 'roman':
                return $this->number_to_roman($section->section);
            case 'alphabet':
                return $this->number_to_alphabet($section->section);
            default:
                return $section->section;
        }
    }

    /**
     * Generate the title for this section page.
     *
     * @return string the page title
     */
    public function page_title(): string {
        return get_string('sectionoutline');
    }

    /**
     * Number to roman numeral conversion.
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
     * Number to alphabet conversion.
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
     * course_format_options
     *
     * @param bool $foreditform
     * @return array
     */
    public function course_format_options($foreditform = false) {
        global $PAGE;

        static $courseformatoptions = null;

        if ($courseformatoptions === null) {
            $courseconfig = get_config('moodlecourse');
            $courseformatoptions = [];

            $courseformatoptions['numsections'] = array(
                'default' => $courseconfig->numsections,
                'type' => PARAM_INT,
            );

            $courseformatoptions['hiddensections'] = array(
                'default' => $courseconfig->hiddensections,
                'type' => PARAM_INT,
            );

            $courseformatoptions['showdefaultsectionname'] = array(
                'default' => get_config('format_buttonsx', 'showdefaultsectionname'),
                'type' => PARAM_INT,
            );

            $courseformatoptions['sectionposition'] = array(
                'default' => get_config('format_buttonsx', 'sectionposition'),
                'type' => PARAM_INT,
            );
            $courseformatoptions['inlinesections'] = array(
                'default' => get_config('format_buttonsx', 'inlinesections'),
                'type' => PARAM_INT,
            );
            // ADDED tina john.
            $courseformatoptions['usebottommenu'] = array(
                'default' => get_config('format_buttonsx', 'usebottommenu'),
                'type' => PARAM_INT,
            );
            $courseformatoptions['displayh5picons'] = array(
                'default' => get_config('format_buttonsx', 'displayh5picons'),
                'type' => PARAM_INT,
            );
            $courseformatoptions['act_complinfo_position'] = array(
                'default' => get_config('format_buttonsx', 'act_complinfo_position'),
                'type' => PARAM_INT,
            );
            // ADDED tinjohn 20221006.
            $courseformatoptions['hilight'] = array(
                'default' => get_config('format_buttonsx', 'hilight'),
                'type' => PARAM_INT,
            );
            $courseformatoptions['hililast'] = array(
                'default' => get_config('format_buttonsx', 'hililast'),
                'type' => PARAM_INT,
            );
            // END ADDED.

            $courseformatoptions['sequential'] = array(
                'default' => get_config('format_buttonsx', 'sequential'),
                'type' => PARAM_INT,
            );

            $courseformatoptions['sectiontype'] = array(
                'default' => get_config('format_buttonsx', 'sectiontype'),
                'type' => PARAM_TEXT,
            );

            $courseformatoptions['buttonstyle'] = array(
                'default' => get_config('format_buttonsx', 'buttonstyle'),
                'type' => PARAM_TEXT,
            );

            for ($i = 1; $i <= 12; $i++) {
                $divisortext = get_config('format_buttonsx', 'divisortext'.$i);
                if (!$divisortext) {
                    $divisortext = '';
                }
                $courseformatoptions['divisortext'.$i] = array(
                    'default' => $divisortext,
                    'type' => PARAM_TEXT,
                );
                $courseformatoptions['divisor'.$i] = array(
                    'default' => get_config('format_buttonsx', 'divisor'.$i),
                    'type' => PARAM_INT,
                );
                // ADDED tinjohn 20221208.
                // A text for the button if single button only.
                // Default is &bull;&bull;&bull;
                $courseformatoptions['divisorsinglebuttext'.$i] = array(
                    'default' => "&bull;&bull;&bull;",
                    'type' => PARAM_TEXT,
                );
            }

            $colorcurrent = get_config('format_buttonsx', 'colorcurrent');
            if (!$colorcurrent) {
                $colorcurrent = '';
            }

            $courseformatoptions['colorcurrent'] = array(
                'default' => $colorcurrent,
                'type' => PARAM_TEXT,
            );

            $colorvisible = get_config('format_buttonsx', 'colorvisible');
            if (!$colorvisible) {
                $colorvisible = '';
            }

            $courseformatoptions['colorvisible'] = array(
                'default' => $colorvisible,
                'type' => PARAM_TEXT,
            );
        }

        if ($foreditform && !isset($courseformatoptions['coursedisplay']['label'])) {
            $courseconfig = get_config('moodlecourse');

            $max = $courseconfig->maxsections;
            if (!isset($max) || !is_numeric($max)) {
                $max = 52;
            }

            $sectionmenu = array();
            for ($i = 0; $i <= $max; $i++) {
                $sectionmenu[$i] = "$i";
            }

            $courseformatoptionsedit = [];
            
            $courseformatoptionsedit['numsections'] = array(
                'label' => new lang_string('numberweeks'),
                'element_type' => 'select',
                'element_attributes' => array($sectionmenu),
            );

            $courseformatoptionsedit['hiddensections'] = array(
                'label' => new lang_string('hiddensections'),
                'help' => 'hiddensections',
                'help_component' => 'moodle',
                'element_type' => 'select',
                'element_attributes' => array(
                    array(
                        0 => new lang_string('hiddensectionscollapsed'),
                        1 => new lang_string('hiddensectionsinvisible')
                    )
                ),
            );

            $courseformatoptionsedit['showdefaultsectionname'] = array(
                'label' => get_string('showdefaultsectionname', 'format_buttonsx'),
                'help' => 'showdefaultsectionname',
                'help_component' => 'format_buttonsx',
                'element_type' => 'select',
                'element_attributes' => array(
                    array(
                        1 => get_string('yes', 'format_buttonsx'),
                        0 => get_string('no', 'format_buttonsx'),
                    ),
                ),
            );

            $courseformatoptionsedit['sectionposition'] = array(
                'label' => get_string('sectionposition', 'format_buttonsx'),
                'help' => 'sectionposition',
                'help_component' => 'format_buttonsx',
                'element_type' => 'select',
                'element_attributes' => array(
                    array(
                        0 => get_string('above', 'format_buttonsx'),
                        1 => get_string('below', 'format_buttonsx'),
                    ),
                ),
            );
            // ADDED tinjohn 2022-07-29
            // Option for bottom menu - defaults no.
            $courseformatoptionsedit['usebottommenu'] = array(
                'label' => get_string('usebottommenu', 'format_buttonsx'),
                'help' => 'usebottommenu',
                'help_component' => 'format_buttonsx',
                'element_type' => 'select',
                'element_attributes' => array(
                    array(
                        1 => get_string('yes', 'format_buttonsx'),
                        0 => get_string('no', 'format_buttonsx'),
                    ),
                ),
            );

            // For h5picon displayed for users.
            $courseformatoptionsedit['displayh5picons'] = array(
                'label' => get_string('displayh5picons', 'format_buttonsx'),
                'help' => 'displayh5picons',
                'help_component' => 'format_buttonsx',
                'element_type' => 'select',
                'element_attributes' => array(
                    array(
                        0 => get_string('no', 'format_buttonsx'),
                        1 => get_string('yes', 'format_buttonsx'),
                    ),
                ),
            );

            // 20222609 act_complinfo_below.
            $courseformatoptionsedit['act_complinfo_position'] = array(
                'label' => get_string('act_complinfo_position', 'format_buttonsx'),
                'help' => 'act_complinfo_position',
                'help_component' => 'format_buttonsx',
                'element_type' => 'select',
                'element_attributes' => array(
                    array(
                      0 => get_string('below_act', 'format_buttonsx'),
                      1 => get_string('above_act', 'format_buttonsx'),
                    ),
                ),
            );

            $courseformatoptionsedit['hililast'] = array(
                'label' => get_string('hililast', 'format_buttonsx'),
                'help' => 'hililast',
                'help_component' => 'format_buttonsx',
                'element_type' => 'select',
                'element_attributes' => array(
                    array(
                      0 => get_string('yes', 'format_buttonsx'),
                      1 => get_string('no', 'format_buttonsx'),
                    ),
                ),
            );
            $courseformatoptionsedit['hilight'] = array(
                'label' => get_string('highlight', 'format_buttonsx'),
                'element_type' => 'select',
                'element_attributes' => array($sectionmenu),
            );



            // END ADDED.

            $courseformatoptionsedit['inlinesections'] = array(
                'label' => get_string('inlinesections', 'format_buttonsx'),
                'help' => 'inlinesections',
                'help_component' => 'format_buttonsx',
                'element_type' => 'select',
                'element_attributes' => array(
                    array(
                        1 => get_string('yes', 'format_buttonsx'),
                        0 => get_string('no', 'format_buttonsx'),
                    ),
                ),
            );

            $courseformatoptionsedit['sequential'] = array(
                'label' => get_string('sequential', 'format_buttonsx'),
                'help_component' => 'format_buttonsx',
                'element_type' => 'select',
                'element_attributes' => array(
                    array(
                        0 => get_string('notsequentialdesc', 'format_buttonsx'),
                        1 => get_string('sequentialdesc', 'format_buttonsx'),
                    ),
                ),
            );

            $courseformatoptionsedit['sectiontype'] = array(
                'label' => get_string('sectiontype', 'format_buttonsx'),
                'help_component' => 'format_buttonsx',
                'element_type' => 'select',
                'element_attributes' => array(
                    array(
                        'numeric' => get_string('numeric', 'format_buttonsx'),
                        'roman' => get_string('roman', 'format_buttonsx'),
                        'alphabet' => get_string('alphabet', 'format_buttonsx'),
                    ),
                ),
            );

            $courseformatoptionsedit['buttonstyle'] = array(
                'label' => get_string('buttonstyle', 'format_buttonsx'),
                'help_component' => 'format_buttonsx',
                'element_type' => 'select',
                'element_attributes' => array(
                    array(
                        'circle' => get_string('circle', 'format_buttonsx'),
                        'square' => get_string('square', 'format_buttonsx'),
                    ),
                ),
            );

            for ($i = 1; $i <= 12; $i++) {
                $courseformatoptionsedit['divisortext'.$i] = array(
                    'label' => get_string('divisortext', 'format_buttonsx', $i),
                    'help' => 'divisortext',
                    'help_component' => 'format_buttonsx',
                    'element_type' => 'text',
                );
                $courseformatoptionsedit['divisor'.$i] = array(
                    'label' => get_string('divisor', 'format_buttonsx', $i),
                    'help' => 'divisortext',
                    'help_component' => 'format_buttonsx',
                    'element_type' => 'select',
                    'element_attributes' => array($sectionmenu),
                );
                $courseformatoptionsedit['divisorsinglebuttext'.$i] = array(
                    'label' => get_string('divisorsinglebuttext', 'format_buttonsx', $i),
                    'help' => 'divisorsinglebuttext',
                    'help_component' => 'format_buttonsx',
                    'element_type' => 'text',
                );
            }

            $courseformatoptionsedit['colorcurrent'] = array(
                'label' => get_string('colorcurrent', 'format_buttonsx'),
                'help' => 'colorcurrent',
                'help_component' => 'format_buttonsx',
                'element_type' => 'text',
            );

            $courseformatoptionsedit['colorvisible'] = array(
                'label' => get_string('colorvisible', 'format_buttonsx'),
                'help' => 'colorvisible',
                'help_component' => 'format_buttonsx',
                'element_type' => 'text',
            );

            $courseformatoptions = array_merge_recursive($courseformatoptions, $courseformatoptionsedit);
        }
        return $courseformatoptions;
    }

    /**
     * update_course_format_options
     *
     * @param stdclass|array $data
     * @param stdClass $oldcourse
     * @return bool
     */
    public function update_course_format_options($data, $oldcourse = null) {
        global $DB;

        $data = (array)$data;

        if ($oldcourse !== null) {
            $oldcourse = (array)$oldcourse;

            $options = $this->course_format_options();

            foreach ($options as $key => $unused) {
                if (!array_key_exists($key, $data)) {
                    if (array_key_exists($key, $oldcourse)) {
                        $data[$key] = $oldcourse[$key];
                    } else if ($key === 'numsections') {
                        $maxsection = $DB->get_field_sql('SELECT max(section) from
                        {course_sections} WHERE course = ?', array($this->courseid));
                        if ($maxsection) {
                            $data['numsections'] = $maxsection;
                        }
                    }
                }
            }
        }

        $changed = $this->update_format_options($data);

        if ($changed && array_key_exists('numsections', $data)) {
            $numsections = (int)$data['numsections'];
            $sql = 'SELECT max(section) from {course_sections} WHERE course = ?';
            $maxsection = $DB->get_field_sql($sql, array($this->courseid));
            for ($sectionnum = $maxsection; $sectionnum > $numsections; $sectionnum--) {
                if (!$this->delete_section($sectionnum, false)) {
                    break;
                }
            }
        }
        return $changed;
    }

    /**
     * get_view_url
     *
     * @param int|stdclass $section
     * @param array $options
     * @return null|moodle_url
     */
    public function get_view_url($section, $options = array()) {
        global $CFG;

        $course = $this->get_course();

        $url = new moodle_url('/course/view.php', array('id' => $course->id));

        $sr = null;

        if (array_key_exists('sr', $options)) {
            $sr = $options['sr'];
        }

        if (is_object($section)) {
            $sectionno = $section->section;
        } else {
            $sectionno = $section;
        }

        if ($sectionno !== null) {
            if ($sr !== null) {
                if ($sr) {
                    $usercoursedisplay = COURSE_DISPLAY_MULTIPAGE;
                    $sectionno = $sr;
                } else {
                    $usercoursedisplay = COURSE_DISPLAY_SINGLEPAGE;
                }
            } else {
                $usercoursedisplay = 0;
            }
            if ($sectionno != 0 && $usercoursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                $url->param('section', $sectionno);
            } else {
                if (empty($CFG->linkcoursesections) && !empty($options['navigation'])) {
                    return null;
                }
                $url->set_anchor('section-'.$sectionno);
            }
        }

        return $url;
    }
}

/**
 * Implements callback inplace_editable() allowing to edit values in-place
 *
 * @param string $itemtype
 * @param int $itemid
 * @param mixed $newvalue
 * @return \core\output\inplace_editable
 */
function format_buttonsx_inplace_editable($itemtype, $itemid, $newvalue) {
    global $DB, $CFG;

    require_once($CFG->dirroot . '/course/lib.php');

    if ($itemtype === 'sectionname' || $itemtype === 'sectionnamenl') {
        $section = $DB->get_record_sql(
            'SELECT s.* FROM {course_sections} s JOIN {course} c ON s.course = c.id WHERE s.id = ? AND c.format = ?',
            array($itemid, 'buttonsx'),
            MUST_EXIST
        );
        return course_get_format($section->course)->inplace_editable_update_section_name($section, $itemtype, $newvalue);
    }
    return null;
}
