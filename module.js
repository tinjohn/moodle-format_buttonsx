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
 * @copyright  based on the work 2020 Rodrigo Brandão <rodrigo.brandao.contato@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


 //document.addEventListener(CourseEvents.manualCompletionToggled, (e) => {
  // alert("manual completed");
     // const withAvailability = parseInt(e.detail.withAvailability);
     // if (withAvailability) {
     //     // Reload the page when the toggled manual completion button has availability conditions linked to it.
     //     window.location.reload();
    //  }
 //});

 // END added

M.format_buttonsx = M.format_buttonsx || {
    ourYUI: null,
    numsections: 0
};

M.format_buttonsx.init = function(Y, numsections, currentsection, courseid) {
    this.ourYUI = Y;
    this.numsections = parseInt(numsections);
    document.getElementById('buttonsectioncontainer').style.display = 'table';
    document.getElementById('bottombuttonsectioncontainer').style.display = 'table';

    var findHash = function (href) {
        var id = null;
        if (href.indexOf('#section-') !== 0) {
            var split = href.split('#section-');
            id = split[1];
        }
        return id;
    };

    var hash = findHash(window.location.href);
    if (hash) {
        currentsection = hash;
    }

    if (currentsection) {
        M.format_buttonsx.show(currentsection, courseid);
    }
    // for (var i = 1; i <= this.numsections; i++) {
    //   M.format_buttonsx.show(i, courseid);
    // }


    Y.delegate('click', function (e) {
        var href = e.currentTarget.get('href');
        currentsection = findHash(href);
        M.format_buttonsx.show(currentsection, courseid)
    }, '[data-region="drawer"]', '[data-type="30"]');

};

M.format_buttonsx.hide = function() {
    for (var i = 1; i <= this.numsections; i++) {
        if (document.getElementById('buttonsection-' + i) != undefined) {
            var buttonsection = document.getElementById('buttonsection-' + i);
            buttonsection.setAttribute('class', buttonsection.getAttribute('class').replace('sectionvisible', ''));
            document.getElementById('section-' + i).style.display = 'none';
        }
        // ADDED
        if (document.getElementById('bottombuttonsection-' + i) != undefined) {
            var bottombuttonsection = document.getElementById('bottombuttonsection-' + i);
            bottombuttonsection.setAttribute('class', bottombuttonsection.getAttribute('class').replace('sectionvisible', 'sectionnotvisible'));
            bottombuttonsection.setAttribute('class', bottombuttonsection.getAttribute('class').replace('sectionaftervisible', 'sectionnotvisible'));
            bottombuttonsection.setAttribute('class', bottombuttonsection.getAttribute('class').replace('sectionbeforevisible', 'sectionnotvisible'));
            document.getElementById('section-' + i).style.display = 'none';
        }
        // END ADDED

    }
};

M.format_buttonsx.show = function(id, courseid) {
    this.hide();
    id = parseInt(id);
    if (id > 0) {
        var buttonsection = document.getElementById('buttonsection-' + id);
        var currentsection = document.getElementById('section-' + id);
        if (buttonsection && currentsection) {
            buttonsection.setAttribute('class', buttonsection.getAttribute('class') + ' sectionvisible');
            currentsection.style.display = 'block';
            document.cookie = 'sectionvisible_' + courseid + '=' + id + '; path=/';
            M.format_buttonsx.h5p();
            // ADDED tinjohn 23092022
            document.querySelector('#buttonsectioncontainer').scrollIntoView();
          // was just a test  window.location.reload();
            // END ADDED
        }

        // ADDED
        // set classes for adjacent (previous / next) visible sections
        var bottombuttonsection = document.getElementById('bottombuttonsection-' + id);
        if(bottombuttonsection) {
          bottombuttonsection.setAttribute('class', bottombuttonsection.getAttribute('class').replace('sectionnotvisible', 'sectionvisible')); // ADDED
          if (id > 1) {
            var beforeid = id - 1;
            var bottombuttonsectionbefore = document.getElementById('bottombuttonsection-' + beforeid );
            if (!bottombuttonsectionbefore ) {
              while(!bottombuttonsectionbefore && beforeid > 0) {
                beforeid = beforeid - 1;
                bottombuttonsectionbefore = document.getElementById('bottombuttonsection-' + beforeid );
              }
            }
            bottombuttonsectionbefore.setAttribute('class', bottombuttonsectionbefore.getAttribute('class').replace('sectionnotvisible', 'sectionbeforevisible'));
          }
          if (id < this.numsections) {
            var afterid = id + 1;
            var bottombuttonsectionnext = document.getElementById('bottombuttonsection-' + afterid );
            if(!bottombuttonsectionnext) {
              while(!bottombuttonsectionnext && afterid <= this.numsections) {
                afterid = afterid + 1;
                bottombuttonsectionnext = document.getElementById('bottombuttonsection-' + afterid );
              }
            }
            bottombuttonsectionnext.setAttribute('class', bottombuttonsectionnext.getAttribute('class').replace('sectionnotvisible', 'sectionaftervisible'));
          }
        }
        // ADDED END
    }

};

M.format_buttonsx.h5p = function() {
    window.h5pResizerInitialized = false;
    var iframes = document.getElementsByTagName('iframe');
    var ready = {
        context: 'h5p',
        action: 'ready'
    };
    for (var i = 0; i < iframes.length; i++) {
        if (iframes[i].src.indexOf('h5p') !== -1) {
            M.format_buttonsx.h5prefreshSafari(iframes[i]);
            iframes[i].contentWindow.postMessage(ready, '*');
        }
    }
};


// ADDED tinjohn 20221223 - Safari bug does not give right scrollHeight due to wrong offsetHeight before refresh.
// Results are wrong iframe dimensions.

M.format_buttonsx.h5prefreshSafari = function(iframe) {
    console.log('M.format_buttonsx.h55refreshSafari');
    if (iframe.offsetParent === null) {
      //console.log("not visible");
      return;
    }
    if (iframe.getAttribute('visresized')) {
      console.log("already resized");
      return;
    }
    var safariBrowser = navigator.userAgent.match(/version\/([.\d]+)/i);
    safariBrowser = (safariBrowser === null ? 0 : parseInt(safariBrowser[1]));
    if (safariBrowser === 0) {
      //console.log("kein Safari");
      return;
    }

    iframe.src = iframe.src + '';
    iframe.setAttribute('visresized', 'visresized');

};

// END ADDED.
