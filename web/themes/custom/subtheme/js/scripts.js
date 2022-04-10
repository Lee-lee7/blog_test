/*(function($) {
    Drupal.behaviors.stickyNavbar = {
        attach: function(context, settings) {

            var navbar = $('.header');

            if (navbar.length) {
                var elmHeight = $('.header-top').outerHeight(true);
                $(window).scroll(function() {
                    var scrolltop = $(window).scrollTop();
                    if (scrolltop > elmHeight) {
                        if (!navbar.hasClass('sticky')) {
                            navbar.addClass('sticky');
                        }
                    } else {
                        navbar.removeClass('sticky');
                    }
                });
            }

        }

    }

})(jQuery); */
(function($) {
    var x = 0.5;
    var y = 0.5;
    var maxRotation = 10;
    var perspective = 1000;

    $(document).mousemove(function(event) {
        var pos = [event.pageX / document.images.clientWidth, event.pageY / document.images.clientHeight];
        for (var i = 0; i < pos.length; i++) {
            if (pos[i] < 0) {
                pos[i] = 0;
            }
            if (pos[i] > 1) {
                pos[i] = 1;
            }
        }
        pos[0] = Math.round(((pos[0] * 2) - 1) * maxRotation);
        pos[1] = Math.round(((pos[1] * -2) + 1) * maxRotation);
        $("img").css("transform", "perspective(" + perspective + ") rotateX(" + pos[1] + "deg) rotateY(" + pos[0] + "deg)");
        $("img").css("-webkit-transform", "perspective(" + perspective + ") rotateX(" + pos[1] + "deg) rotateY(" + pos[0] + "deg)");
    });
})(jQuery);