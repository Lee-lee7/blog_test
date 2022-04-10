/**
 * @file
 */

(function($) {

    'use strict';

    Drupal.behaviors.mtc = {
        attach: function(context, settings) {
            if (!Drupal.behaviors.mtc.click_set) {
                if (drupalSettings.timezone_clock.country_name) {
                    var strArray = drupalSettings.timezone_clock.country_name.split("|");
                    // Display array values on page.
                    for (var i = 0; i < strArray.length; i++) {
                        var comma_array = strArray[i].split(",");
                        // Date status.
                        var date_status = true;
                        if (comma_array[4] == 'D') {
                            var data_format = 'DD/MM/YYYY';
                        } else if (comma_array[4] == 'M') {
                            var data_format = 'MM/DD/YYYY';
                        } else {
                            var date_status = false;
                        }
                        // Time format.
                        if (comma_array[5] == 1) {
                            var time_format = 'HH:mm:ss';
                        } else {
                            var time_format = 'hh:mm:ss A';
                        }
                        // Country.
                        if (comma_array[0]) {
                            var country_name = comma_array[0];
                        } else {
                            var country_name = Drupal.t('Global');
                        }

                        $('#clock_' + comma_array[3]).jClocksGMT({
                            title: country_name,
                            offset: comma_array[2],
                            skin: comma_array[1],
                            date: date_status,
                            timeformat: time_format,
                            dateformat: data_format,
                        });
                        Drupal.behaviors.mtc.click_set = true;
                    }
                }
            }

        }
    };

})(jQuery);