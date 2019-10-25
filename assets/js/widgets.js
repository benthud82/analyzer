( function ( $ ) {
    "use strict";


    // Counter Number
    $('.count').each(function () {
        debugger;
        $(this).prop('Counter',0).animate({
            Counter: $(this).text()
        }, {
            duration: 3000,
            easing: 'swing',
            step: function (now) {
                $(this).text(Math.ceil(now));
            }
        });
    });






} )( jQuery );