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

require_once($CFG->dirroot.'/course/format/topics/renderer.php');

/**
 * format_buttonsx_renderer
 *
 * @package    format_buttonsx
 * @author     Rodrigo Brandão <https://www.linkedin.com/in/brandaorodrigo>
 * @copyright  2020 Rodrigo Brandão <rodrigo.brandao.contato@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_buttonsx_renderer extends format_topics_renderer {

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
        $htmlsection = false;
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
                $htmlsection[$section] .= $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                $htmlsection[$section] .= $this->courserenderer->course_section_add_cm_control($course, $section, 0);
            }
            $htmlsection[$section] .= $this->section_footer();
        }
        if ($section0->summary || !empty($modinfo->sections[0]) || $this->page->user_is_editing()) {
            $htmlsection0 = $this->section_header($section0, $course, false, 0);
            $htmlsection0 .= $this->courserenderer->course_section_cm_list($course, $section0, 0);
            $htmlsection0 .= $this->courserenderer->course_section_add_cm_control($course, 0, 0);
            $htmlsection0 .= $this->section_footer();
        }
        echo $completioninfo->display_help_icon();
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
                echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
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
}
