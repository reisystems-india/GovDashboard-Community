/* Mimics Highcharts print to keep print feature consistent
* TODO: Need to think about a better approach for all widgets */

(function($){
    $.fn.gd_print = function() {
        var element = this,
            origParent = this.parent();

        // hide all body content
        $('body').css('display', 'none');

        // pull out the element
        $('html').prepend(element);

        // print
        window.print();

        // allow the browser to prepare before reverting
        setTimeout(function () {
            element.appendTo(origParent);

            // restore all body content
            $('body').css('display', '');

        }, 1000);
    };
})(jQuery);