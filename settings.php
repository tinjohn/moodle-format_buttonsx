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
 * Settings for ButtonsX course format.
 *
 * @package   format_buttonsx
 * @copyright 2024 Tina John
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    // Default section name display setting.
    $name = 'format_buttonsx/showdefaultsectionname';
    $title = get_string('showdefaultsectionname', 'format_buttonsx');
    $description = get_string('showdefaultsectionname_help', 'format_buttonsx');
    $default = 1;
    $choices = [
        0 => get_string('no', 'format_buttonsx'),
        1 => get_string('yes', 'format_buttonsx')
    ];
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    // Default section position setting.
    $name = 'format_buttonsx/sectionposition';
    $title = get_string('sectionposition', 'format_buttonsx');
    $description = get_string('sectionposition_help', 'format_buttonsx');
    $default = 0;
    $choices = [
        0 => get_string('above', 'format_buttonsx'),
        1 => get_string('below', 'format_buttonsx')
    ];
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    // Default inline sections setting.
    $name = 'format_buttonsx/inlinesections';
    $title = get_string('inlinesections', 'format_buttonsx');
    $description = get_string('inlinesections_help', 'format_buttonsx');
    $default = 0;
    $choices = [
        0 => get_string('no', 'format_buttonsx'),
        1 => get_string('yes', 'format_buttonsx')
    ];
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    // Default bottom menu setting.
    $name = 'format_buttonsx/usebottommenu';
    $title = get_string('usebottommenu', 'format_buttonsx');
    $description = get_string('usebottommenu_help', 'format_buttonsx');
    $default = 0;
    $choices = [
        0 => get_string('no', 'format_buttonsx'),
        1 => get_string('yes', 'format_buttonsx')
    ];
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    // Default H5P icons display setting.
    $name = 'format_buttonsx/displayh5picons';
    $title = get_string('displayh5picons', 'format_buttonsx');
    $description = get_string('displayh5picons_help', 'format_buttonsx');
    $default = 0;
    $choices = [
        0 => get_string('no', 'format_buttonsx'),
        1 => get_string('yes', 'format_buttonsx')
    ];
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    // Default activity completion info position.
    $name = 'format_buttonsx/act_complinfo_position';
    $title = get_string('act_complinfo_position', 'format_buttonsx');
    $description = get_string('act_complinfo_position_help', 'format_buttonsx');
    $default = 0;
    $choices = [
        0 => get_string('below_act', 'format_buttonsx'),
        1 => get_string('above_act', 'format_buttonsx')
    ];
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    // Default sequential access setting.
    $name = 'format_buttonsx/sequential';
    $title = get_string('sequential', 'format_buttonsx');
    $description = get_string('sequential_help', 'format_buttonsx');
    $default = 0;
    $choices = [
        0 => get_string('notsequentialdesc', 'format_buttonsx'),
        1 => get_string('sequentialdesc', 'format_buttonsx')
    ];
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    // Default section type.
    $name = 'format_buttonsx/sectiontype';
    $title = get_string('sectiontype', 'format_buttonsx');
    $description = get_string('sectiontype_help', 'format_buttonsx');
    $default = 'numeric';
    $choices = [
        'numeric' => get_string('numeric', 'format_buttonsx'),
        'roman' => get_string('roman', 'format_buttonsx'),
        'alphabet' => get_string('alphabet', 'format_buttonsx')
    ];
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    // Default button style.
    $name = 'format_buttonsx/buttonstyle';
    $title = get_string('buttonstyle', 'format_buttonsx');
    $description = get_string('buttonstyle_help', 'format_buttonsx');
    $default = 'circle';
    $choices = [
        'circle' => get_string('circle', 'format_buttonsx'),
        'square' => get_string('square', 'format_buttonsx')
    ];
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    // Default color for current section.
    $name = 'format_buttonsx/colorcurrent';
    $title = get_string('colorcurrent', 'format_buttonsx');
    $description = get_string('colorcurrent_help', 'format_buttonsx');
    $default = '';
    $settings->add(new admin_setting_configtext($name, $title, $description, $default));

    // Default color for visible section.
    $name = 'format_buttonsx/colorvisible';
    $title = get_string('colorvisible', 'format_buttonsx');
    $description = get_string('colorvisible_help', 'format_buttonsx');
    $default = '';
    $settings->add(new admin_setting_configtext($name, $title, $description, $default));

    // Default divisor settings.
    for ($i = 1; $i <= 12; $i++) {
        $name = 'format_buttonsx/divisor' . $i;
        $title = get_string('divisor', 'format_buttonsx', $i);
        $description = get_string('divisortext_help', 'format_buttonsx');
        $default = 0;
        $settings->add(new admin_setting_configtext($name, $title, $description, $default, PARAM_INT));

        $name = 'format_buttonsx/divisortext' . $i;
        $title = get_string('divisortext', 'format_buttonsx', $i);
        $description = get_string('divisortext_help', 'format_buttonsx');
        $default = '';
        $settings->add(new admin_setting_configtext($name, $title, $description, $default));
    }

    // Indentation setting (like topics format).
    $name = 'format_buttonsx/indentation';
    $title = get_string('indentation', 'format_buttonsx');
    $description = get_string('indentation_help', 'format_buttonsx');
    $default = 1;
    $choices = [
        0 => get_string('no'),
        1 => get_string('yes')
    ];
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    // Hilight setting.
    $name = 'format_buttonsx/hilight';
    $title = get_string('highlight', 'format_buttonsx');
    $description = get_string('highlight_help', 'format_buttonsx');
    $default = 0;
    $settings->add(new admin_setting_configtext($name, $title, $description, $default, PARAM_INT));

    // Hililast setting.
    $name = 'format_buttonsx/hililast';
    $title = get_string('hililast', 'format_buttonsx');
    $description = get_string('hililast_help', 'format_buttonsx');
    $default = 0;
    $choices = [
        0 => get_string('yes', 'format_buttonsx'),
        1 => get_string('no', 'format_buttonsx')
    ];
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));
}
