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
 * Course format mutations for ButtonsX format.
 *
 * @module     format_buttonsx/mutations
 * @copyright  2024 Tina John
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getCurrentCourseEditor} from 'core_courseformat/courseeditor';

// Setup format mutations.
export const init = () => {
    // Add any custom mutations for buttonsx format here.
    const courseEditor = getCurrentCourseEditor();
    
    if (courseEditor) {
        // Initialize H5P resize observer when sections become visible
        observeH5PContent();
    }
};

/**
 * Observe H5P content and ensure proper resizing.
 */
const observeH5PContent = () => {
    // Watch for H5P iframes that need resizing
    const resizeH5P = () => {
        const h5pIframes = document.querySelectorAll('.h5p-iframe');
        h5pIframes.forEach((iframe) => {
            if (iframe.contentWindow && iframe.contentWindow.H5P) {
                iframe.contentWindow.H5P.externalDispatcher.on('resize', () => {
                    // Trigger H5P resize
                    if (window.H5P && window.H5P.externalDispatcher) {
                        window.H5P.externalDispatcher.trigger('resize');
                    }
                });
            }
        });
    };

    // Observe DOM changes for dynamically loaded H5P content
    const observer = new MutationObserver(() => {
        resizeH5P();
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    // Initial resize
    resizeH5P();
};
