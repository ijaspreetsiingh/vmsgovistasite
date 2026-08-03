/**
 *
 * -----------------------------------------------------------------------------
 *
 * Template : Touriza HTML Template
 * Author : themewant
 * Author URI : https://themewant.com/ 
 *
 * -----------------------------------------------------------------------------
 *
 **/

(function ($) {
    'use strict';
    // Get the form (static contact pages only — skip enquiry / placeholder actions).
    var form = $('#contact-form');
    var action = (form.attr('action') || '').trim();
    if (!form.length || action === '' || action === '#') {
        return;
    }
    // Get the messages div.
    var formMessages = $('#form-messages');
    $(form).submit(function (e) {
        // Stop the browser from submitting the form.
        e.preventDefault();

        // Serialize the form data.
        var formData = $(form).serialize();

        // Submit the form using AJAX.
        $.ajax({
                type: 'POST',
                url: $(form).attr('action'),
                data: formData
            })
            .done(function (response) {
                // Make sure that the formMessages div has the 'success' class.
                $(formMessages).removeClass('error');
                $(formMessages).addClass('success');

                // Set the message text.
                $(formMessages).text(response);

                // Clear the form.
                $('#name, #email, #company, #website, #message').val('');
            })
            .fail(function (data) {
                // Make sure that the formMessages div has the 'error' class.
                $(formMessages).removeClass('success');
                $(formMessages).addClass('error');

                // Set the message text.
                if (data.responseText !== '') {
                    $(formMessages).text(data.responseText);
                } else {
                    $(formMessages).text('Oops! An error occured and your message could not be sent.');
                }
            });
    });

})(jQuery);
