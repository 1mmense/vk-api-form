$(document).ready(function () {
    const image = $('#image');
    const buttonPrev = $('#buttonPrev');
    const buttonNext = $('#buttonNext');
    const buttonPostAll = $('#buttonPostAll');
    const buttonPost = $('#buttonPost');
    const buttonPostVideos = $('#buttonPostVideos');
    const offset = $('#offset');

    loadDoc();

    buttonPrev.click(function () {
        let offset = parseInt($('#offset').val());

        if (offset >= 1) {
            $('#offset').val(offset - 1);
        }

        loadDoc();
    });

    buttonNext.click(function () {
        let offsetInt = parseInt(offset.val());
        offset.val(offsetInt + 1);
        loadDoc();
    });

    buttonPostAll.click(function () {
        savePhoto(true);
    });

    buttonPost.click(function () {
        savePhoto();
    });

    buttonPostVideos.click(function () {
        saveVideo();
    });

    function loadDoc() {
        let offsetInt = parseInt(offset.val());

        buttonPost.removeClass('button-post');

        $.ajax({
            url: 'controllers/upload.php?mode=get_files_from_folder',
            type: 'post',
            cache: false,
            data: {
                offset: offsetInt,
            },
            success: function (response) {
                if (typeof response === 'undefined' || response == '') {
                    offset.val(offsetInt - 1);
                }

                if (typeof response !== 'undefined' && response != '') {
                    let data = $.parseJSON(response);

                    if (typeof data.file_path !== 'undefined') {
                        image.attr('src', data.file_path);
                    }
                }
            },
        });
    }

    function savePhoto(isPostAll = false) {
        $.ajax({
            url: 'controllers/upload.php?mode=save_photos',
            type: 'post',
            cache: false,
            data: {
                file_path: image.attr('src'),
                is_post_all_photos: isPostAll,
            },
        });
    }

    function saveVideo() {
        $.ajax({
            url: 'controllers/upload.php?mode=save_videos',
            type: 'post',
            cache: false,
        });
    }
});
