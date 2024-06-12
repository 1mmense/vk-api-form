$(document).ready(function () {
    loadDoc();

    $('#buttonPrev').click(function () {
        let offset = parseInt($('#offset').val());

        if (offset >= 1) {
            $('#offset').val(offset - 1);
        }

        loadDoc();
    });

    $('#buttonNext').click(function () {
        let offset = parseInt($('#offset').val());
        $('#offset').val(offset + 1);
        loadDoc();
    });

    $('#buttonPostAll').click(function () {
        savePhoto(true);
    });

    $('#buttonPost').click(function () {
        savePhoto();
    });

    $('#buttonPostVideos').click(function () {
        saveVideo();
    });
});

function loadDoc() {
    let offset = parseInt($('#offset').val());
    let buttonPost = $('#buttonPost');

    buttonPost.removeClass('button-post');

    let errorContainer = $('#errorContainer');
    errorContainer.addClass('error-container');

    $.ajax({
        url: 'getFilesFromFolder.php',
        type: 'post',
        data: {
            offset: offset,
        },
        success: function (response) {
            if (typeof response === 'undefined' || response == '') {
                $('#offset').val(offset - 1);
            }

            if (typeof response !== 'undefined' && response != '') {
                let data = jQuery.parseJSON(response);

                if (typeof data.file_path !== 'undefined') {
                    $('#image').attr('src', data.file_path);
                    $('#photo_id').val(data.id);
                }
            }
        },
    });
}

function savePhoto(isPostAll = false) {
    let errorContainer = $('#errorContainer');
    let errorLabel = $('#errorLabel');
    let image = $('#image');
    errorContainer.addClass('error-container');

    $.ajax({
        url: 'savePhotos.php',
        type: 'post',
        data: {
            file_path: image.attr('src'),
            is_post_all_photos: isPostAll,
        },
        success: function (response) {
            if (typeof response !== 'undefined' && response != '') {
                let data = jQuery.parseJSON(response);

                if (data.error_msg) {
                    errorContainer.removeClass('error-container');
                    errorLabel.text(data.error_msg);
                }
            }
        },
    });
}

function saveVideo() {
    let errorContainer = $('#errorContainer');
    let errorLabel = $('#errorLabel');

    errorContainer.addClass('error-container');

    $.ajax({
        url: 'saveVideos.php',
        type: 'post',
        success: function (response) {
            if (typeof response !== 'undefined' && response != '') {
                let data = jQuery.parseJSON(response);

                if (data.error_msg) {
                    errorContainer.removeClass('error-container');
                    errorLabel.text(data.error_msg);
                }
            }
        },
    });
}
