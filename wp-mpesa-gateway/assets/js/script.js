jQuery(document).ready(function ($) {
    $('#mpesa-payment-form').on('submit', function (e) {
        e.preventDefault();

        var form = $(this);
        var btn = form.find('button');
        var msg = form.find('.mpesa-message');

        btn.prop('disabled', true).text('Processing...');
        msg.removeClass('success error').text('');

        var data = {
            action: 'mpesa_initiate',
            nonce: mpesa_vars.nonce,
            phone: form.find('input[name="phone"]').val(),
            amount: form.find('input[name="amount"]').val()
        };

        $.post(mpesa_vars.ajax_url, data, function (response) {
            btn.prop('disabled', false).text('Pay Now');
            if (response.success) {
                msg.addClass('success').text(response.data);
                form[0].reset();
            } else {
                msg.addClass('error').text(response.data);
            }
        }).fail(function () {
            btn.prop('disabled', false).text('Pay Now');
            msg.addClass('error').text('Connection error. Please try again.');
        });
    });
});
