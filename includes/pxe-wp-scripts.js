jQuery(document).ready(function ($) {
    $('#pxe_wp_process').click(function (e) {
        e.preventDefault();
        var posts_id = $.parseJSON($('#posts_id').val());
        var progress_bar = '<div id="progress_bar" style="background-color: #ddd;"><div id="bar" style ="width: 1%; height: 30px; background-color: #bbb;"></div></div>';
        $('#pxe_wipe_post .warning').remove();
        $('#pxe_wp_process').remove();
        $('#pxe_wipe_post').prepend(progress_bar);
        var width = 0;
        var i = 0;
        $.each(posts_id, function (item, post_id) {
            $.ajax({
                type: 'POST',
                url: pxe_wp_object.ajax_url,
                data: {
                    action: 'process_wipe_post',
                    post_id: post_id
                },
                success: function (result) {
                    var data = result.data;
                    var max_width = $.parseJSON($('#posts_id').val()).length;
                    i++;
                    width = 100 * i / max_width;
                    if (result.data.result === 'ok') {
                        var resultado = '<span style="color:green;"> - Procesado</span>';
                    } else {
                        var resultado = '<span style="color:red;"> - No Procesado</span>';
                    }
                    $('#progress_bar #bar').css('width', width + '%');
                    var text_result = '<p>' + data.title + resultado + '</p>';
                    $('#processing').append(text_result);
                    if (max_width == i) {
                        var return_link = '<a href="' + pxe_wp_object.return_link + '">Volver</a>';
                        $('#pxe_wipe_post').append(return_link);
                    }
                }
            });
        });

    });
    $('#pxe_remote_url').on('keyup', function (e) {
        e.preventDefault();
        $('.alert_message').remove();
        var remote_url = $('#pxe_remote_url').val();
        if (remote_url.length > 7) {
            if (!/^(http|https|ftp):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/i.test(remote_url)) {
                var alert_message = '<span class="alert_message"> ponga una url valida</span>';
                $('#pxe_remote_url').after(alert_message);
            }
        }
    });
    $('#pxe_year').on('keyup', function (e) {
        e.preventDefault();
        $('.alert_message').remove();
        var year = $('#pxe_year').val();
        if (!/^\d{4}$/.test(year)) {
            var alert_message = '<span class="alert_message"> ponga un año válido</span>';
            $('#pxe_year').after(alert_message);
        }
    });
});