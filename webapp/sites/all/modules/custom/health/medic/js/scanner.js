(function($) {

    // on ready
    $(function() {

        // attach click handlers to all treatment apply buttons
        $('.treatmentApplyAction').on('click', function () {
            var button = $(this);
            button.parent().parent().find('.treatment-message').empty();
            button.button('loading');

            var affected = button.data('affected');
            var treatment = button.data('treatment');

            $.ajax({
                type: 'POST',
                url: '/admin/structure/govdashboard/health/medic/api/treatment',
                data: {
                    affected: JSON.stringify(affected),
                    treatment: treatment
                },
                dataType: 'json'
            }).done(function (data) {
                button.parent().parent().find('.treatment-message').html('<div class="alert alert-success" role="alert">Successfully applied.</div>');
                button.parent().parent().find('.treatment-actions').empty();
            }).fail(function (jqXHR, textStatus, errorThrown) {
                button.parent().parent().find('.treatment-message').html('<div class="alert alert-danger" role="alert">'+$.parseJSON(jqXHR.responseText)+'</div>');
                button.button('reset');
            });
        });

        // attach click handlers to all treatment apply all buttons
        $('.treatmentApplyAllAction').on('click', function () {
            var button = $(this);
            button.parent().parent().find('.treatment-message').empty();
            button.button('loading');

            var treatment = button.data('treatment');
            var affected = [];
            $.each ( $('.treatmentApplyAction[data-treatment="'+treatment+'"]'), function() {
                affected.push($(this).data('affected'));
            });

            $.ajax({
                type: 'POST',
                url: '/admin/structure/govdashboard/health/medic/api/treatment',
                data: {
                    affected: JSON.stringify(affected),
                    treatment: treatment
                },
                dataType: 'json'
            }).done(function (data) {
                button.parent().parent().find('.treatment-all-message').html('<div class="alert alert-success" role="alert">Successfully applied.</div>');
                button.parent().parent().find('.treatment-all-actions').empty();
                $.each ( $('.treatmentApplyAction[data-treatment="'+treatment+'"]'), function() {
                    $(this).parent().parent().find('.treatment-actions').empty();
                });
            }).fail(function (jqXHR, textStatus, errorThrown) {
                button.parent().parent().find('.treatment-all-message').html('<div class="alert alert-danger" role="alert">'+$.parseJSON(jqXHR.responseText)+'</div>');
                button.button('reset');
            });


        });

    });


})(jQuery);