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

define([
    'jquery',
    'core/templates',
    'core/ajax',
    'core/notification'
], function($, templates, ajax, notification) {
    return {
        init: function(urlid, redirect, forceview, frame) {
            ajax.call([{
                methodname: 'mod_url_load_view',
                args: {
                    urlid: urlid,
                    redirect: redirect,
                    forceview: forceview,
                    frame: frame
                }
            }])[0].done(function(response) {
                console.log(response);

                if (typeof response.notices !== 'undefined' && response.notices.length) {
                    // TODO inject notices html
                    return;
                }

                if (response.redirectaction.url) {
                    // TODO handle redirect action
                    return;
                }

                if (2 === response.data.displaytype) {
                    if ('top' === frame) {
                        // TODO load:
                        // heading
                        // printintro
                        // intro
                    } else {
                        // TODO load:
                        // dir
                        // title
                        // framesize
                        // navurl
                        // modulename
                        // exteurl
                        // contentframetitle
                    }
                } else {
                    $('#heading-placeholder').replaceWith(response.data.heading);
                    if (response.data.printintro) {
                        $('#module_intro-placeholder').replaceWith(response.data.intro);
                        $('.mod_introbox').removeClass('hidden');
                    }

                    if (typeof response.data.codehtml !== 'undefined') {
                        $('#code-placeholder').replaceWith(response.data.codehtml);
                    } else if (typeof response.data.clicktoopenhtml !== 'undefined') {
                        $('#urlworkaround-placeholder').replaceWith('<div class="urlworkaround">' + response.data.clicktoopenhtml + '</div>');
                    }

                }
            }).fail(notification.exception);
        }
    };
});
