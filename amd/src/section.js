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
 * ButtonsX format section visibility and navigation component.
 *
 * @module     format_buttonsx/section
 * @copyright  2024 Tina John
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

let currentSection = null;

/**
 * Initialize ButtonsX section navigation.
 */
export const init = () => {
    // Wait for DOM to be ready with a delay to ensure all elements are rendered
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(initNavigation, 100);
        });
    } else {
        setTimeout(initNavigation, 100);
    }
};

/**
 * Initialize navigation after DOM is ready.
 */
const initNavigation = () => {
    // Find all buttons
    const buttons = document.querySelectorAll('.buttonsection');

    if (buttons.length === 0) {
        // No buttons found, retry after delay
        setTimeout(initNavigation, 100);
        return;
    }

    // Determine which section to show
    let sectionToShow = 1; // Default to section 1

    // Check URL hash first
    const hash = window.location.hash;
    if (hash.startsWith('#section-')) {
        const hashNum = parseInt(hash.replace('#section-', ''));
        if (!isNaN(hashNum) && hashNum > 0) {
            sectionToShow = hashNum;
        }
    }

    // Show the initial section
    showSection(sectionToShow);

    // Add click handlers to all buttons
    buttons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const sectionNum = parseInt(button.getAttribute('data-section'));
            if (!isNaN(sectionNum) && sectionNum > 0) {
                showSection(sectionNum);
                window.history.pushState(null, null, '#section-' + sectionNum);
            }
        });
    });

    // Listen for hash changes
    window.addEventListener('hashchange', () => {
        const hash = window.location.hash;
        if (hash.startsWith('#section-')) {
            const sectionNum = parseInt(hash.replace('#section-', ''));
            if (!isNaN(sectionNum) && sectionNum > 0) {
                showSection(sectionNum);
            }
        }
    });
};

/**
 * Hide all sections except section 0.
 */
const hideAllSections = () => {
    // Find all sections in the course content list
    const sectionList = document.querySelector('ul[data-for="course_sectionlist"]');
    if (!sectionList) {
        return;
    }

    sectionList.querySelectorAll('li.section').forEach(section => {
        const sectionId = section.getAttribute('id');
        if (sectionId && sectionId !== 'section-0') {
            section.style.display = 'none';
        }
    });
};

/**
 * Show a specific section.
 *
 * @param {number} sectionNum The section number to show
 */
const showSection = (sectionNum) => {
    // First hide all sections (except section-0)
    hideAllSections();

    currentSection = sectionNum;

    // Show the selected section
    const section = document.querySelector('#section-' + sectionNum);

    if (section) {
        section.style.display = 'block';

        // Scroll to button container
        const buttonContainer = document.querySelector('#buttonsectioncontainer');
        if (buttonContainer) {
            setTimeout(() => {
                buttonContainer.scrollIntoView({behavior: 'smooth', block: 'start'});
            }, 100);
        }

        // Trigger H5P resize if available
        if (window.H5P && window.H5P.externalDispatcher) {
            setTimeout(() => {
                window.H5P.externalDispatcher.trigger('resize');
            }, 200);
        }
    }

    // Update button states
    updateButtonStates(sectionNum);
};

/**
 * Update button states (current/visible).
 *
 * @param {number} sectionNum The current section number
 */
const updateButtonStates = (sectionNum) => {
    // Remove current class from all buttons (including bottom menu)
    document.querySelectorAll('#buttonsectioncontainer .buttonsection').forEach(button => {
        button.classList.remove('buttoncurrent', 'sectionvisible', 'current');
    });

    document.querySelectorAll('#bottombuttonsectioncontainer .buttonsection').forEach(button => {
        button.classList.remove('buttoncurrent', 'sectionvisible', 'current');
        button.classList.add('sectionnotvisible');
        button.classList.remove('sectionbeforevisible', 'sectionaftervisible');
    });

    // Add current class to active top button
    const currentButton = document.querySelector('#buttonsectioncontainer .buttonsection[data-section="' + sectionNum + '"]');
    if (currentButton) {
        currentButton.classList.add('buttoncurrent', 'sectionvisible', 'current');
    }

    // Update bottom menu if it exists
    const bottomContainer = document.querySelector('#bottombuttonsectioncontainer');
    if (bottomContainer) {
        // Current button in bottom menu
        const currentBottomButton = bottomContainer.querySelector('.buttonsection[data-section="' + sectionNum + '"]');
        if (currentBottomButton) {
            currentBottomButton.classList.add('buttoncurrent', 'sectionvisible', 'current');
            currentBottomButton.classList.remove('sectionnotvisible');
        }

        // Previous button (arrow left)
        const prevBottomButton = bottomContainer.querySelector('.buttonsection[data-section="' + (sectionNum - 1) + '"]');
        if (prevBottomButton) {
            prevBottomButton.classList.add('sectionbeforevisible');
            prevBottomButton.classList.remove('sectionnotvisible');
        }

        // Next button (arrow right)
        const nextBottomButton = bottomContainer.querySelector('.buttonsection[data-section="' + (sectionNum + 1) + '"]');
        if (nextBottomButton) {
            nextBottomButton.classList.add('sectionaftervisible');
            nextBottomButton.classList.remove('sectionnotvisible');
        }
    }
};
