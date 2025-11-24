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
 * @author     Tina John
 * @author     based on the work of Rodrigo Brandão <https://www.linkedin.com/in/brandaorodrigo>
 * @copyright  2022 Tina John <johnt.22.tijo@gmail.com>
 * @copyright  based on the work GNU GPL2020 Rodrigo Brandão <rodrigo.brandao.contato@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/renderer.php');

/**
 * format_buttonsx_renderer
 *
 * @package    format_buttonsx
 * @author     Rodrigo Brandão <https://www.linkedin.com/in/brandaorodrigo>
 * @copyright  2020 Rodrigo Brandão <rodrigo.brandao.contato@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_buttonsx_renderer extends core_course_renderer {

    /**
     * Get_button_section
     *
     * @param stdclass $course
     * @param string $name
     * @return string
     */
    protected function get_color_config($course, $name) {
        $return = false;
        if (isset($course->{$name})) {
            $color = str_replace('#', '', $course->{$name});
            $color = substr($color, 0, 6);
            if (preg_match('/^#?[a-f0-9]{6}$/i', $color)) {
                $return = '#'.$color;
            }
        }
        return $return;
    }

    /**
     * Number_to_roman
     *
     * @param integer $number
     * @return string
     */
    protected function number_to_roman($number) {
        $number = intval($number);
        $return = '';
        $romanarray = [
            'M' => 1000,
            'CM' => 900,
            'D' => 500,
            'CD' => 400,
            'C' => 100,
            'XC' => 90,
            'L' => 50,
            'XL' => 40,
            'X' => 10,
            'IX' => 9,
            'V' => 5,
            'IV' => 4,
            'I' => 1
        ];
        foreach ($romanarray as $roman => $value) {
            $matches = intval($number / $value);
            $return .= str_repeat($roman, $matches);
            $number = $number % $value;
        }
        return $return;
    }

    /**
     * Number_to_alphabet
     *
     * @param integer $number
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
    }

    /**
     * Get_button_section
     *
     * @param stdclass $course
     * @param string $sectionvisible
     * @return string
     */
    protected function get_button_section($course, $sectionvisible) {
        $html = '';
        $css = '';
        if ($colorcurrent = $this->get_color_config($course, 'colorcurrent')) {
            $css .=
            '#buttonsectioncontainer .buttonsection.current {
                background: ' . $colorcurrent . ';
            }
            ';
        }
        if ($colorvisible = $this->get_color_config($course, 'colorvisible')) {
            $css .=
            '#buttonsectioncontainer .buttonsection.sectionvisible {
                background: ' . $colorvisible . ';
            }
            ';
        }
        // ADDED tina john 20220825.
        if (!$this->page->user_is_editing() && !$course->displayh5picons) {
            $css .=
            '.h5pactivity .activity-instance {
              display: none !important;
            }';
        }
        // 20220926.
        if (!$this->page->user_is_editing() && !$course->act_complinfo_position) {
            $css .=
            '.activity-item {
              display: flex;
              flex-direction: column-reverse;
            }';
        }

        // END ADDED.
        if ($css) {
            $html .= html_writer::tag('style', $css);
        }
        $withoutdivisor = true;
        for ($k = 1; $k <= 12; $k++) {
            if ($course->{'divisor' . $k} != 0) {
                $withoutdivisor = false;
            }
        }

        $modinfo = get_fast_modinfo($course);
        if ($withoutdivisor) {
            $course->divisor1 = array_key_last($modinfo->get_section_info_all());
        }
        $divisorshow = false;
        $count = 1;
        $currentdivisor = 1;
        $inline = '';
        foreach ($modinfo->get_section_info_all() as $section => $thissection) {
            if ($section == 0) {
                continue;
            }
            if ($section > $course->numsections) {
                continue;
            }
            if ($course->hiddensections && !(int)$thissection->visible) {
                continue;
            }
            if (isset($course->{'divisor' . $currentdivisor}) &&
                $count > $course->{'divisor' . $currentdivisor}) {
                $currentdivisor++;
                $count = 1;
            }
            if (isset($course->{'divisor' . $currentdivisor}) &&
                $course->{'divisor' . $currentdivisor} != 0 &&
                !isset($divisorshow[$currentdivisor])) {
                $currentdivisorhtml = format_string($course->{'divisortext' . $currentdivisor});
                $currentdivisorhtml = str_replace('[br]', '<br>', $currentdivisorhtml);
                $currentdivisorstring = $currentdivisorhtml;
                $currentdivisorhtml = html_writer::tag('div', $currentdivisorhtml, ['class' => 'divisortext']);
                if ($course->inlinesections) {
                    $inline = 'inlinebuttonsections';
                }
                $html .= html_writer::tag('div', $currentdivisorhtml, ['class' => "divisorsection $inline"]);
                $divisorshow[$currentdivisor] = true;
            }
            $id = 'buttonsection-' . $section;
            if ($course->sequential) {
                $name = $section;
            } else {
                if (isset($course->{'divisor' . $currentdivisor}) &&
                $course->{'divisor' . $currentdivisor} == 1) {
                  // ADDED tinjohn 20221208.
                  // make it an option
                  // $courseformatoptions['divisorsinglebuttext'.$i]
                  if (isset($course->{'divisorsinglebuttext' . $currentdivisor})) {
                    $name = format_string($course->{'divisorsinglebuttext' . $currentdivisor});
                  } else {
                    $name = '&bull;&bull;&bull;';
                  }

                } else {
                    $name = $count;
                }
            }
            if ($course->sectiontype == 'alphabet' && is_numeric($name)) {
                $name = $this->number_to_alphabet($name);
            }
            if ($course->sectiontype == 'roman' && is_numeric($name)) {
                $name = $this->number_to_roman($name);
            }
            $class = 'buttonsection';
            $onclick = 'M.format_buttonsx.show(' . $section . ',' . $course->id . ')';
            if (!$thissection->available &&
                !empty($thissection->availableinfo)) {
                $class .= ' sectionhidden';
            } else if (!$thissection->uservisible || !$thissection->visible) {
                $class .= ' sectionhidden';
                $onclick = false;
            }
            // ADDED tinjohn 06102022 deprecated option to have since moodle 4.0.
            if ($course->marker == $section) {
                $class .= ' current';
            }
            // ADDED tinjohn 06102022 instead use last for OCP
            if ($section == $course->hilight) {
                $class .= ' hilight';
            }
            if ($course->hililast && $course->numsections == $section) {
              $class .= ' hilight';
            }
            if ($sectionvisible == $section) {
                $class .= ' sectionvisible';
            }
            if ($this->page->user_is_editing()) {
                $onclick = false;
            }
            $html .= html_writer::tag('div', $name, ['id' => $id, 'class' => $class, 'onclick' => $onclick]);
            $count++;
        }
        $html = html_writer::tag('div', $html, ['id' => 'buttonsectioncontainer', 'class' => $course->buttonstyle]);
        if ($this->page->user_is_editing()) {
            $html .= html_writer::tag('div', get_string('editing', 'format_buttonsx'), ['class' => 'alert alert-warning alert-block fade in']);
        }
        return $html;
    }


    // ADDED.
    /**
     * is_previous_vis_section
     *
     * @return boolean
     */
    protected function is_previous_vis_section ($course, $sectionnrvisible, $sectionnr) {
        $modinfo = get_fast_modinfo($course);

        /*
        # sectionsnrvisible - preselected section
        # sectionnr - current section in processing

        # it's a previous section when
        # $sectionvisible - 1 == $section && (int)$thissection->visible
        */
        $sectionnrbef = $sectionnrvisible - 1;
        $thissection = $modinfo->get_section_info($sectionnrbef);

        if ($sectionnrbef == $sectionnr && $thissection->visible) {
            return true;
        } else {
            // Find previous visible section and check against section.
            while (!$thissection->visible) {
                $sectionnrbef = $sectionnrbef - 1;
                if ($sectionnrbef == 1) {
                    return false;
                }
                $thissection = $modinfo->get_section_info($sectionnrbef);
            }
            if ($sectionnrbef == $sectionnr) {
                return true;
            }
        }
        return false;
    }

     /**
      * is_next_vis_section
      *
      * @return boolean
      */
    protected function is_next_vis_section ($course, $sectionvisible, $section) {
        if ($sectionvisible + 1 == $section) {
            return true;
        }
        return false;
    }

    /**
     * is_divi_previous_vis_section
     *
     * @return boolean
     */
    protected function is_divi_previous_vis_section ($course, $sectionnr, $count, $currentdivisor) {
        if (!isset($course->{'divisor' . $currentdivisor})) {
            return false;
        }
        $modinfo = get_fast_modinfo($course);
        $thissection = $modinfo->get_section_info($sectionnr);
        if (!$thissection->visible) {
            return false;
        }
        // Section navigator wants to know if there was any section left till the divisor.
        $cntsectionnr = $sectionnr;
        $ki = 1;
        $allhidden = true;
        for ($k = $count; $k < ($course->{'divisor' . $currentdivisor}); $k++) {
            $cntsectionnr = $sectionnr + $ki;
            $cntinvis = 0;
            $thissection = $modinfo->get_section_info($cntsectionnr);
            if ($thissection->visible) {
                return false;
            } else {
                $cntinvis++;
            }
            $ki++;
        }
        if ($cntinvis > 0) {
            return true;
        }
        return false;
    }

    // END ADDED.

    // INCLUDED /course/format/renderer.php function get_button_section_bottom.
    // Based on get_button_section.
    /**
     * Get_button_section_bottom
     *
     * @param stdclass $course
     * @param string $sectionvisible
     * @return string
     */
    protected function get_button_section_bottom ($course, $sectionvisible) {
        $html = '';
        $css = '';
        if ($course->usebottommenu) {
            $html = html_writer::tag('div', $html, ['id' => 'bottombuttonsectioncontainer', 'class' => $course->buttonstyle]);
            if ($this->page->user_is_editing()) {
                $html .= html_writer::tag('div', get_string('editing', 'format_buttonsx'), ['class' => 'alert alert-warning alert-block fade in']);
            }
            return $html;
        }

        if ($colorcurrent = $this->get_color_config($course, 'colorcurrent')) {
            $css .=
            '#bottombuttonsectioncontainer {
                --button-currcol: ' . $colorcurrent . ';
            }';
        }
        if ($colorvisible = $this->get_color_config($course, 'colorvisible')) {
            $css .=
            '#bottombuttonsectioncontainer {
                --button-viscol: ' . $colorvisible . ';
            }';
            // A semicolon alone in the code - commented ;.
        }
        if ($css) {
            $html .= html_writer::tag('style', $css);
        }
        $withoutdivisor = true;

        // The $course->divisor1  already set in the main menu.
        // But kept for further development maybe without main menu.
        for ($k = 1; $k <= 12; $k++) {
            if ($course->{'divisor' . $k} != 0) {
                $withoutdivisor = false;
            }
        }

        $modinfo = get_fast_modinfo($course);
        if ($withoutdivisor) {
            $course->divisor1 = array_key_last($modinfo->get_section_info_all());
            $withoutdivisor = true;
        }

        $divisorshow = false;
        $count = 1;
        $currentdivisor = 1;
        $modinfo = get_fast_modinfo($course);
        $inline = '';
        $lasthidden = false;
        $hidden = false;
        $lasthiddencnt = 0;
        foreach ($modinfo->get_section_info_all() as $section => $thissection) {
            if ($hidden) {
                $lasthiddencnt = $lasthiddencnt + 1;
            }
            $hidden = false;
            if ($section == 0) {
                continue;
            }
            if ($section > $course->numsections) {
                continue;
            }
            if ($course->hiddensections && !(int)$thissection->visible) {
                continue;
            }
            if (isset($course->{'divisor' . $currentdivisor}) &&
                $count > $course->{'divisor' . $currentdivisor}) {
                $currentdivisor++;
                $count = 1;
                $lasthiddencnt = 0;
            }

            $id = 'bottombuttonsection-' . $section;
            if ($course->sequential) {
                $name = $section;
            } else {
                if (isset($course->{'divisor' . $currentdivisor}) &&
                $course->{'divisor' . $currentdivisor} == 1) {
                    $name = '&bull;&bull;&bull;';
                } else {
                    $name = $count;
                }
            }
            if ($course->sectiontype == 'alphabet' && is_numeric($name)) {
                $name = $this->number_to_alphabet($name);
            }
            if ($course->sectiontype == 'roman' && is_numeric($name)) {
                $name = $this->number_to_roman($name);
            }
            $class = 'buttonsection';
            $onclick = 'M.format_buttonsx.show(' . $section . ',' . $course->id . ')';
            if (!$thissection->available &&
                !empty($thissection->availableinfo)) {
                $class .= ' sectionhidden';
                $hidden = true;
            } else if (!$thissection->uservisible || !$thissection->visible) {
                $class .= ' sectionhidden';
                $onclick = false;
                $hidden = true;
            }

            if ($sectionvisible == $section) {
                $class .= ' sectionvisible';
            } else {
                // Sectionbeforevisible arrows, sectionaftervisible arrows for divisor jump set by js init.
                $class .= ' sectionnotvisible';
            }

            if ($this->page->user_is_editing()) {
                $onclick = false;
            }
            /*
            if ($count == 1) {
              $arspan = html_writer::tag('div', "", ['id' => '', 'class' => 'divisorline']);
               $name = $arspan.''.$name;
            }
            */
            /* ADDED.
                $withoutdivisor is always false because first divisor is set for main
                to length of sections.
            */
            if (!$withoutdivisor) {
                if ($count == (1 + $lasthiddencnt)) {
                    $class .= ' specialbgafter';
                } elseif ($count == ($course->{'divisor' . $currentdivisor})) {
                    $class .= ' specialbgbefore';
                    // Der davor müsste schon wissen, dass er hidden ist.
                } else {
                    if ($this->is_divi_previous_vis_section($course, $section, $count, $currentdivisor)) {
                        $class .= ' specialbgbefore';
                    }
                }
            }
            if ($course->marker == $section) {
                $class .= ' current';
            }
            // ADDED tinjohn 20221006 instead use last for OCP
            if ($section == $course->hilight) {
                $class .= ' hilight';
            }
            if ($course->hililast && $course->numsections == $section) {
              $class .= ' hilight';
            }

            if (!$hidden) {
                $html .= html_writer::tag('div', $name, ['id' => $id, 'class' => $class, 'onclick' => $onclick]);
            }
            $count++;
        }
        $html = html_writer::tag('div', $html, ['id' => 'bottombuttonsectioncontainer', 'class' => $course->buttonstyle]);
        if ($this->page->user_is_editing()) {
            $html .= html_writer::tag('div', get_string('editing', 'format_buttonsx'), ['class' => 'alert alert-warning alert-block fade in']);
        }
        return $html;
    }
    // END INCLUDED.

    /**
     * Start_section_list
     *
     * @return string
     */
    protected function start_section_list() {
        return html_writer::start_tag('ul', ['class' => 'buttons']);
    }

    /**
     * Section_header
     *
     * @param stdclass $section
     * @param stdclass $course
     * @param bool $onsectionpage
     * @param int $sectionreturn
     * @return string
     */
    protected function section_header($section, $course, $onsectionpage, $sectionreturn = null) {
        $o = '';
        $currenttext = '';
        $sectionstyle = '';

        if ($section->section != 0) {
            // Only in the non-general sections.
            if (!$section->visible) {
                $sectionstyle = ' hidden';
            }
            if (course_get_format($course)->is_section_current($section)) {
                $sectionstyle = ' current';
            }
        }

        $o .= html_writer::start_tag('li', array('id' => 'section-'.$section->section,
             'class' => 'section main clearfix'.$sectionstyle, 'role' => 'region',
             'aria-label' => get_section_name($course, $section)));

        // Create a span that contains the section title to be used to create the keyboard section move menu.
        $o .= html_writer::tag('span', get_section_name($course, $section), array('class' => 'hidden sectionname'));

        $leftcontent = $this->section_left_content($section, $course, $onsectionpage);
        $o .= html_writer::tag('div', $leftcontent, array('class' => 'left side'));

        $rightcontent = $this->section_right_content($section, $course, $onsectionpage);
        $o .= html_writer::tag('div', $rightcontent, array('class' => 'right side'));
        $o .= html_writer::start_tag('div', array('class' => 'content'));

        // When not on a section page, we display the section titles except the general section if null.
        $hasnamenotsecpg = (!$onsectionpage && ($section->section != 0 || !is_null($section->name)));

        // When on a section page, we only display the general section title, if title is not the default one.
        $hasnamesecpg = ($onsectionpage && ($section->section == 0 && !is_null($section->name)));

        $classes = ' accesshide';
        if ($hasnamenotsecpg || $hasnamesecpg) {
            $classes = '';
        }
        $sectionname = html_writer::tag('span', $this->section_title($section, $course));

        // Button format - ini.
        if ($course->showdefaultsectionname) {
            $o .= $this->output->heading($sectionname, 3, 'sectionname' . $classes);
        }
        // Button format - end.

        $o .= $this->section_availability($section);

        $o .= html_writer::start_tag('div', array('class' => 'summary'));
        if ($section->uservisible || $section->visible) {
            // Show summary if section is available or has availability restriction information.
            // Do not show summary if section is hidden but we still display it because of course setting
            // "Hidden sections are shown in collapsed form".
            $o .= $this->format_summary_text($section);
        }
        $o .= html_writer::end_tag('div');

        return $o;
    }

    /**
     * Generate the display of a section footer
     *
     * @return string HTML to output.
     */
    protected function section_footer() {
        $o = html_writer::end_tag('div');
        $o .= html_writer::end_tag('li');
        return $o;
    }

    /**
     * Generate the closing container html for a list of sections
     * @return string HTML to output.
     */
    protected function end_section_list() {
        return html_writer::end_tag('ul');
    }

    /**
     * Print_multiple_section_page
     *
     * @param stdclass $course
     * @param array $sections (argument not used)
     * @param array $mods (argument not used)
     * @param array $modnames (argument not used)
     * @param array $modnamesused (argument not used)
     */
    public function print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused) {
        $modinfo = get_fast_modinfo($course);
        $course = course_get_format($course)->get_course();

        $context = context_course::instance($course->id);
        // Title with completion help icon.
        $completioninfo = new completion_info($course);

        // Buttons format - ini.
        if (isset($_COOKIE['sectionvisible_' . $course->id])) {
            $sectionvisible = $_COOKIE['sectionvisible_' . $course->id];
        } else if ($course->marker > 0) {
            $sectionvisible = $course->marker;
        } else {
            $sectionvisible = 1;
        }
        $htmlsection = [];
        foreach ($modinfo->get_section_info_all() as $section => $thissection) {
            $htmlsection[$section] = '';
            if ($section == 0) {
                $section0 = $thissection;
                continue;
            }
            if ($section > $course->numsections) {
                continue;
            }
            /* If is not editing verify the rules to display the sections */
            if (!$this->page->user_is_editing()) {
                if ($course->hiddensections && !(int)$thissection->visible) {
                    continue;
                }
                if (!$thissection->available && !empty($thissection->availableinfo)) {
                    $htmlsection[$section] .= $this->section_header($thissection, $course, false, 0);
                    continue;
                }
                if (!$thissection->uservisible || !$thissection->visible) {
                    $htmlsection[$section] .= $this->section_hidden($section, $course->id);
                    continue;
                }
            }
            $htmlsection[$section] .= $this->section_header($thissection, $course, false, 0);
            if ($thissection->uservisible) {
                $htmlsection[$section] .= $this->course_section_cm_list($course, $thissection, 0);
                $htmlsection[$section] .= $this->course_section_add_cm_control($course, $section, 0);
            }
            $htmlsection[$section] .= $this->section_footer();
        }
        if ($section0->summary || !empty($modinfo->sections[0]) || $this->page->user_is_editing()) {
            $htmlsection0 = $this->section_header($section0, $course, false, 0);
            $htmlsection0 .= $this->course_section_cm_list($course, $section0, 0);
            $htmlsection0 .= $this->course_section_add_cm_control($course, 0, 0);
            $htmlsection0 .= $this->section_footer();
        }
        echo $this->output->heading($this->page_title(), 2, 'accesshide');
        echo $this->course_activity_clipboard($course, 0);
        echo $this->start_section_list();
        if ($course->sectionposition == 0 && isset($htmlsection0)) {
            echo html_writer::tag('span', $htmlsection0, ['class' => 'above']);
        }
        echo $this->get_button_section($course, $sectionvisible);
        foreach ($htmlsection as $current) {
            echo $current;
        }
        if ($course->sectionposition == 1 && isset($htmlsection0)) {
            echo html_writer::tag('span', $htmlsection0, ['class' => 'below']);
        }
        if ($this->page->user_is_editing() && has_capability('moodle/course:update', $context)) {
            foreach ($modinfo->get_section_info_all() as $section => $thissection) {
                if ($section <= $course->numsections || empty($modinfo->sections[$section])) {
                    continue;
                }
                echo $this->stealth_section_header($section);
                echo $this->course_section_cm_list($course, $thissection, 0);
                echo $this->stealth_section_footer();
            }
            echo $this->end_section_list();
            echo html_writer::start_tag('div', ['id' => 'changenumsections', 'class' => 'mdl-right']);
            $straddsection = get_string('increasesections', 'moodle');
            $url = new moodle_url('/course/changenumsections.php', ['courseid' => $course->id,
                'increase' => true, 'sesskey' => sesskey()]);
            $icon = $this->output->pix_icon('t/switch_plus', $straddsection);
            echo html_writer::link($url, $icon.get_accesshide($straddsection), ['class' => 'increase-sections']);
            if ($course->numsections > 0) {
                $strremovesection = get_string('reducesections', 'moodle');
                $url = new moodle_url('/course/changenumsections.php', ['courseid' => $course->id,
                    'increase' => false, 'sesskey' => sesskey()]);
                $icon = $this->output->pix_icon('t/switch_minus', $strremovesection);
                echo html_writer::link(
                    $url,
                    $icon.get_accesshide($strremovesection),
                    ['class' => 'reduce-sections']
                );
            }
            echo html_writer::end_tag('div');
        } else {
            echo $this->end_section_list();
        }

        // ADDED.
        // By tinjohn 2022-07-29.
        // Configuration option was added and is used here.
        /* if ($course->usebottommenu) { */
           echo $this->get_button_section_bottom($course, $sectionvisible);
        /* } */
        // END ADDED.

        if (!$this->page->user_is_editing()) {
            $this->page->requires->js_init_call('M.format_buttonsx.init', [$course->numsections, $sectionvisible, $course->id]);
        }
        // Button format - end.
    }

    /**
     * Render the enable bulk editing button.
     * @param course_format $format the course format
     * @return string|null the enable bulk button HTML (or null if no bulk available).
     */
    public function bulk_editing_button($format): ?string {
        if (!$format->show_editor() || !$format->supports_components()) {
            return null;
        }
        $widgetclass = $format->get_output_classname('content\\bulkedittoggler');
        if (!class_exists($widgetclass)) {
            return null;
        }
        $widget = new $widgetclass($format);
        return $this->render($widget);
    }

    /**
     * Generate the content to displayed on the left part of a section
     * before course modules are included
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return string HTML to output.
     */
    protected function section_left_content($section, $course, $onsectionpage) {
        $o = $this->output->spacer();

        if ($section->section != 0) {
            // Only in the non-general sections.
            if (course_get_format($course)->is_section_current($section)) {
                $o .= get_accesshide(get_string('currentsection', 'format_' . $course->format));
            }
        }
        return $o;
    }

    /**
     * Generate the content to displayed on the right part of a section
     * before course modules are included
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return string HTML to output.
     */
    protected function section_right_content($section, $course, $onsectionpage) {
        $o = '';
        return $o;
    }

    /**
     * Generate the section title, wraps it in a link to the section page if page is to be displayed on a separate page
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title($section, $course) {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section));
    }

    /**
     * Generate the section title to be displayed on the section page, without a link
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title_without_link($section, $course) {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section, false));
    }

    /**
     * Output the html for a single section page.
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections The course_sections entries from the DB
     * @param array $mods used for print_section()
     * @param array $modnames used for print_section()
     * @param array $modnamesused used for print_section()
     * @param int $displaysection The section number in the course which is being displayed
     */
    public function print_single_section_page($course, $sections, $mods, $modnames, $modnamesused, $displaysection) {
        // This method is deprecated but may still be called
        // Simply return empty to avoid errors
        return;
    }

    /**
     * Output the html for the section availability information.
     *
     * @param stdClass $section The course_section entry from DB
     * @return string HTML to output.
     */
    protected function section_availability($section) {
        $o = '';
        if (!$section->uservisible && !empty($section->availableinfo)) {
            // Note: We only get to this function if availableinfo is non-empty,
            // so there is definitely something to print.
            $o .= html_writer::start_tag('div', ['class' => 'availabilityinfo']);
            $o .= $section->availableinfo;
            $o .= html_writer::end_tag('div');
        }
        return $o;
    }

    /**
     * Show if something is on the course clipboard (moving around)
     *
     * @param stdClass $course The course entry from DB
     * @param int $sectionno The section number in the course which is being displayed
     * @return string HTML to output.
     */
    protected function course_activity_clipboard($course, $sectionno = null) {
        global $USER;

        $o = '';
        // If currently moving a file then show the current clipboard.
        if (ismoving($course->id)) {
            $url = new moodle_url('/course/mod.php',
                ['sesskey' => sesskey(),
                 'cancelcopy' => 'true',
                 'sr' => $sectionno,
                ]
            );

            $o .= html_writer::start_tag('div', ['class' => 'clipboard']);
            $o .= strip_tags(get_string('activityclipboard', '', $USER->activitycopyname));
            $o .= ' (' . html_writer::link($url, get_string('cancel')) . ')';
            $o .= html_writer::end_tag('div');
        }

        return $o;
    }

    /**
     * Format summary text for the section.
     *
     * @param stdClass $section The section object.
     * @return string the formatted summary text.
     */
    protected function format_summary_text($section) {
        $context = context_course::instance($section->course);
        $summarytext = file_rewrite_pluginfile_urls($section->summary, 'pluginfile.php',
            $context->id, 'course', 'section', $section->id);

        $options = new stdClass();
        $options->noclean = true;
        $options->overflowdiv = true;
        return format_text($summarytext, $section->summaryformat, $options);
    }

    /**
     * Generate the content to display the list of course modules in a section.
     *
     * @param stdClass $course The course entry from DB
     * @param int|stdClass|section_info $section The course_section entry from DB
     * @param int $sectionreturn The section to return to after an action
     * @param array $displayoptions extra display options
     * @return string HTML to output.
     */
    public function course_section_cm_list($course, $section, $sectionreturn = null, $displayoptions = []) {
        global $USER;

        $output = '';
        $modinfo = get_fast_modinfo($course);
        if (is_object($section)) {
            $section = $modinfo->get_section_info($section->section);
        } else {
            $section = $modinfo->get_section_info($section);
        }
        $completioninfo = new completion_info($course);

        // Check if we are currently in the process of moving a module with JavaScript disabled.
        $ismoving = $this->page->user_is_editing() && ismoving($course->id);
        if ($ismoving) {
            $movingpix = new pix_icon('movehere', get_string('movehere'), 'moodle', ['class' => 'movetarget']);
            $strmovefull = strip_tags(get_string("movefull", "", "'$USER->activitycopyname'"));
        }

        // Get the list of modules visible to user (excluding the module being moved if there is one).
        $moduleshtml = [];
        if (!empty($modinfo->sections[$section->section])) {
            foreach ($modinfo->sections[$section->section] as $modnumber) {
                $mod = $modinfo->cms[$modnumber];

                if ($ismoving && $mod->id == $USER->activitycopy) {
                    // Do not display moving mod.
                    continue;
                }

                if ($modulehtml = $this->course_section_cm_list_item($course,
                        $completioninfo, $mod, $sectionreturn, $displayoptions)) {
                    $moduleshtml[$modnumber] = $modulehtml;
                }
            }
        }

        $sectionoutput = '';
        if (!empty($moduleshtml) || $ismoving) {
            foreach ($moduleshtml as $modnumber => $modulehtml) {
                if ($ismoving) {
                    $movingurl = new moodle_url('/course/mod.php', ['moveto' => $modnumber, 'sesskey' => sesskey()]);
                    $sectionoutput .= html_writer::tag('li',
                        html_writer::link($movingurl, $this->output->render($movingpix), ['title' => $strmovefull]),
                        ['class' => 'movehere']);
                }

                $sectionoutput .= $modulehtml;
            }

            if ($ismoving) {
                $movingurl = new moodle_url('/course/mod.php', ['movetosection' => $section->id, 'sesskey' => sesskey()]);
                $sectionoutput .= html_writer::tag('li',
                    html_writer::link($movingurl, $this->output->render($movingpix), ['title' => $strmovefull]),
                    ['class' => 'movehere']);
            }
        }

        // Always output the section module list.
        $output .= html_writer::tag('ul', $sectionoutput, ['class' => 'section img-text']);

        return $output;
    }

    /**
     * Renders HTML to display one course module for display within a section.
     *
     * @param stdClass $course
     * @param completion_info $completioninfo
     * @param cm_info $mod
     * @param int|null $sectionreturn
     * @param array $displayoptions
     * @return String
     */
    public function course_section_cm_list_item($course, &$completioninfo, cm_info $mod, $sectionreturn, $displayoptions = []) {

        $output = '';
        if ($modulehtml = $this->course_section_cm($course, $completioninfo, $mod, $sectionreturn, $displayoptions)) {
            $modclasses = 'activity ' . $mod->modname . ' modtype_' . $mod->modname . ' ' . $mod->extraclasses;
            $output .= html_writer::tag('li', $modulehtml, ['class' => $modclasses, 'id' => 'module-' . $mod->id]);
        }
        return $output;
    }

    /**
     * Renders HTML to display one course module in a course section
     *
     * @param stdClass $course
     * @param completion_info $completioninfo
     * @param cm_info $mod
     * @param int|null $sectionreturn
     * @param array $displayoptions
     * @return String
     */
    public function course_section_cm($course, &$completioninfo, cm_info $mod, $sectionreturn, $displayoptions = []) {
        $output = '';
        // We return empty string (because course module will not be displayed at all)
        // if:
        // 1) The activity is not visible to users
        // and
        // 2) The 'availableinfo' is empty, i.e. the activity was
        //     hidden in a way that leaves no info, such as using the
        //     eye icon.
        if (!$mod->is_visible_on_course_page()) {
            return $output;
        }

        $indentclasses = 'mod-indent';
        if (!empty($mod->indent)) {
            $indentclasses .= ' mod-indent-'.$mod->indent;
            if ($mod->indent > 15) {
                $indentclasses .= ' mod-indent-huge';
            }
        }

        $output .= html_writer::start_tag('div');

        if ($this->page->user_is_editing()) {
            $output .= course_get_cm_move($mod, $sectionreturn);
        }

        $output .= html_writer::start_tag('div', ['class' => 'mod-indent-outer']);

        // This div is used to indent the content.
        $output .= html_writer::div('', $indentclasses);

        // Start the div for the activity content.
        $output .= html_writer::start_tag('div', ['class' => 'activityinstance']);
        $output .= $this->course_section_cm_name($mod, $displayoptions);

        if ($mod->uservisible) {
            $output .= $this->course_section_cm_completion_legacy($course, $completioninfo, $mod, $displayoptions);
        }

        $output .= html_writer::end_tag('div'); // .activityinstance

        // If there is content AND a link, then display the content here
        // (AFTER any icons). Otherwise it was displayed before.
        $output .= $this->course_section_cm_text($mod, $displayoptions);

        $output .= html_writer::end_tag('div'); // .mod-indent-outer
        $output .= html_writer::end_tag('div');

        return $output;
    }

    /**
     * Renders html to display the module content on the course page (i.e. text of the labels)
     *
     * @param cm_info $mod
     * @param array $displayoptions
     * @return string
     */
    public function course_section_cm_text(cm_info $mod, $displayoptions = []) {
        $output = '';
        if (!$mod->is_visible_on_course_page()) {
            // Nothing to display to the user.
            return $output;
        }
        $content = $mod->get_formatted_content(['overflowdiv' => true, 'noclean' => true]);
        $accesstext = '';
        $textclasses = '';
        if ($mod->uservisible) {
            $conditionalhidden = $this->is_cm_conditionally_hidden_legacy($mod);
            $accessiblebutdim = (!$mod->visible || $conditionalhidden) &&
                has_capability('moodle/course:viewhiddenactivities', $mod->context);
            if ($accessiblebutdim) {
                $textclasses .= ' dimmed_text';
                if ($conditionalhidden) {
                    $textclasses .= ' conditionalhidden';
                }
                // Show accessibility note only if user can access the module himself.
                $accesstext = get_accesshide(get_string('hiddenfromstudents').':'. $mod->modfullname);
            }
        }
        if ($mod->url) {
            if ($content) {
                // If specified, display extra content after link.
                $output = html_writer::tag('div', $content, ['class' => trim('contentafterlink ' . $textclasses)]);
            }
        } else {
            $groupinglabel = $mod->get_grouping_label($textclasses);

            // No link, so display only content.
            $output = html_writer::tag('div', $accesstext . $content . $groupinglabel,
                    ['class' => 'contentwithoutlink ' . $textclasses]);
        }
        return $output;
    }

    /**
     * Renders HTML to show course module availability information (for someone who isn't allowed
     * to see the activity itself, or for staff)
     *
     * @param cm_info $mod
     * @param array $displayoptions
     * @return string
     */
    public function course_section_cm_availability(cm_info $mod, $displayoptions = []) {
        global $CFG;
        $conditionalhidden = $this->is_cm_conditionally_hidden_legacy($mod);
        $accessiblebutdim = (!$mod->visible || $conditionalhidden) &&
            has_capability('moodle/course:viewhiddenactivities', $mod->context);

        $output = '';
        if ($accessiblebutdim) {
            $output .= html_writer::start_span('dimmed_text');
            if ($conditionalhidden) {
                $output .= html_writer::start_span('conditionalhidden');
            }
        }
        if (!$mod->uservisible) {
            // This module is not visible. Show availability info.
            $output .= html_writer::tag('div', $mod->availableinfo, ['class' => 'availabilityinfo']);
        }
        if ($accessiblebutdim) {
            if ($conditionalhidden) {
                $output .= html_writer::end_span(); // conditionalhidden
            }
            $output .= html_writer::end_span(); // dimmed_text
        }
        return $output;
    }

    /**
     * Checks whether the module is conditionally hidden (i.e. hidden if condition not met)
     *
     * @param cm_info $mod
     * @return bool
     */
    protected function is_cm_conditionally_hidden_legacy(cm_info $mod) {
        global $CFG;
        $conditionalhidden = false;
        if (!empty($CFG->enableavailability)) {
            $info = new \core_availability\info_module($mod);
            $conditionalhidden = !$info->is_available_for_all();
        }
        return $conditionalhidden;
    }

    /**
     * Renders HTML to display the module name on the course page (with a link if appropriate)
     *
     * @param cm_info $mod
     * @param array $displayoptions
     * @return string
     */
    public function course_section_cm_name(cm_info $mod, $displayoptions = []) {
        if (!$mod->is_visible_on_course_page() || !$mod->url) {
            // Nothing to display to the user or no link to display.
            return $this->course_section_cm_name_title($mod, $displayoptions);
        }

        $classattributes = 'aalink';
        if (!$mod->uservisible) {
            $classattributes .= ' dimmed';
        }
        // Accessibility: for files get description via icon, this is very ugly hack!
        $instancename = $mod->get_formatted_name();
        $groupinglabel = $mod->get_grouping_label($classattributes);

        // Avoid unnecessary duplication: if e.g. a forum name already
        // includes the word forum (or Forum, etc) then it is unhelpful
        // to include that in the accessible description that is added.
        $altname = get_accesshide(' '.$mod->modfullname);

        // Get on-click attribute value if specified and decode the onclick - it
        // has already been encoded for display (puke).
        $onclick = htmlspecialchars_decode($mod->onclick, ENT_QUOTES);

        // Display link itself.
        $activitylink = html_writer::empty_tag('img', ['src' => $mod->get_icon_url(),
                'class' => 'iconlarge activityicon', 'alt' => '', 'role' => 'presentation', 'aria-hidden' => 'true']) .
                $altname . html_writer::tag('span', $instancename . $groupinglabel, ['class' => 'instancename']);
        if ($mod->uservisible) {
            $output = html_writer::link($mod->url, $activitylink, ['class' => $classattributes, 'onclick' => $onclick]);
        } else {
            // We may be displaying this just in order to show information
            // about visibility, without the actual link ($mod->uservisible)
            $output = html_writer::tag('div', $activitylink, ['class' => $classattributes]);
        }
        return $output;
    }

    /**
     * Renders HTML to display the module name without a link
     *
     * @param cm_info $mod
     * @param array $displayoptions
     * @return string
     */
    public function course_section_cm_name_title(cm_info $mod, $displayoptions = []) {
        $output = '';
        $url = $mod->url;
        if (!$mod->is_visible_on_course_page() || !$url) {
            // Nothing to display to the user.
            $output .= html_writer::tag('span', get_accesshide(' '.$mod->modfullname));
        }
        $output .= html_writer::tag('span', $mod->get_formatted_name(), ['class' => 'instancename']);
        return $output;
    }

    /**
     * Renders HTML to display completion info for a course module
     *
     * @param stdClass $course
     * @param completion_info $completioninfo
     * @param cm_info $mod
     * @param array $displayoptions
     * @return string
     */
    protected function course_section_cm_completion_legacy($course, &$completioninfo, cm_info $mod, $displayoptions = []) {
        $output = '';
        if (!empty($displayoptions['hidecompletion']) || !isloggedin() || isguestuser() || !$mod->uservisible) {
            return $output;
        }
        if ($completioninfo === null) {
            $completioninfo = new completion_info($course);
        }
        $completion = $completioninfo->is_enabled($mod);
        if ($completion == COMPLETION_TRACKING_NONE) {
            return $output;
        }

        $completiondata = $completioninfo->get_data($mod, true);
        $completionicon = '';

        if ($this->page->user_is_editing()) {
            switch ($completion) {
                case COMPLETION_TRACKING_MANUAL :
                    $completionicon = 'manual-enabled'; break;
                case COMPLETION_TRACKING_AUTOMATIC :
                    $completionicon = 'auto-enabled'; break;
            }
        } else if ($completion == COMPLETION_TRACKING_MANUAL) {
            switch($completiondata->completionstate) {
                case COMPLETION_INCOMPLETE:
                    $completionicon = 'manual-n' . ($completiondata->overrideby ? '-override' : '');
                    break;
                case COMPLETION_COMPLETE:
                    $completionicon = 'manual-y' . ($completiondata->overrideby ? '-override' : '');
                    break;
            }
        } else { // Automatic
            switch($completiondata->completionstate) {
                case COMPLETION_INCOMPLETE:
                    $completionicon = 'auto-n' . ($completiondata->overrideby ? '-override' : '');
                    break;
                case COMPLETION_COMPLETE:
                    $completionicon = 'auto-y' . ($completiondata->overrideby ? '-override' : '');
                    break;
                case COMPLETION_COMPLETE_PASS:
                    $completionicon = 'auto-pass'; break;
                case COMPLETION_COMPLETE_FAIL:
                    $completionicon = 'auto-fail'; break;
            }
        }
        if ($completionicon) {
            $formattedname = html_entity_decode($mod->get_formatted_name(), ENT_QUOTES, 'UTF-8');
            if (!$this->page->user_is_editing() && $completion == COMPLETION_TRACKING_MANUAL) {
                $newstate = $completiondata->completionstate == COMPLETION_COMPLETE ? COMPLETION_INCOMPLETE : COMPLETION_COMPLETE;
                $accessibility = get_string('modcompletionaccessibility', 'completion', [
                    'modname' => $formattedname,
                    'status' => get_string('completion-alt-' . $completionicon, 'completion')
                ]);
                // Use the icon with the accessible 'Complete' or 'Incomplete' label.
                $completionpixicon = new pix_icon('i/completion-' . $completionicon, $accessibility, 'moodle', ['title' => '']);
                $output .= html_writer::link(
                    new moodle_url($mod->url, ['sesskey' => sesskey()]),
                    $this->output->render($completionpixicon),
                    ['class' => 'autocompletion']
                );
            } else {
                // The completion info is not interactive. Just display the icon.
                $completionpixicon = new pix_icon('i/completion-' . $completionicon,
                    get_string('completion-alt-' . $completionicon, 'completion'), 'moodle');
                $output .= html_writer::tag('span', $this->output->render($completionpixicon),
                    ['class' => 'autocompletion']);
            }
        }
        return $output;
    }

    /**
     * Renders HTML to display the 'Add an activity or resource' control.
     *
     * @param stdClass $course
     * @param int $section
     * @param int $sectionreturn
     * @param array $displayoptions
     * @return string
     */
    public function course_section_add_cm_control($course, $section, $sectionreturn = null, $displayoptions = []) {
        global $CFG;

        $output = '';
        if (!$this->page->user_is_editing()) {
            return $output;
        }

        if (has_capability('moodle/course:manageactivities', context_course::instance($course->id))) {
            $url = new moodle_url('/course/mod.php', ['id' => $course->id, 'section' => $section, 'sesskey' => sesskey()]);

            $options = ['class' => 'add-activity btn btn-link'];
            $actions = course_get_cm_types_for_section($course, $section, $sectionreturn);

            // If there is only one action, render it as a button.
            if (count($actions) == 1) {
                $action = reset($actions);
                $url = new moodle_url($action['link']);
                $output .= html_writer::link($url, $action['icon'] . ' ' . $action['name'], $options);
            } else {
                // Otherwise render it as a menu.
                $selectoutput = html_writer::start_tag('div', ['class' => 'section-modchooser']);
                $selectoutput .= html_writer::link($url, get_string('addresourceoractivity'), $options);
                $selectoutput .= html_writer::end_tag('div');
                $output .= $selectoutput;
            }
        }

        return $output;
    }
}
