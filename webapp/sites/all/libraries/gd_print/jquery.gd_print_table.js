/* Mimics Highcharts print to keep print feature consistent
* TODO: Need to think about a better approach for all widgets */

(function($){
    $.fn.gd_print_table = function() {
        var table = this;

        // hide all body content
        $('body').css('display', 'none');

        // pull out the element
        $(table).css('display', 'block');
        $('html').prepend(table);

        // print
        window.print();

        // allow the browser to prepare before reverting
        setTimeout(function () {
            $(table).css('display', 'none');

            // restore all body content
            $('body').css('display', '');

        }, 1000);
    };
})(jQuery);