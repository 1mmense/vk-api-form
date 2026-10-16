document.addEventListener('DOMContentLoaded', function () {
    const image = document.getElementById('image');
    const buttonPrev = document.getElementById('buttonPrev');
    const buttonNext = document.getElementById('buttonNext');
    const buttonPostAll = document.getElementById('buttonPostAll');
    const buttonPostVideos = document.getElementById('buttonPostVideos');
    const offset = document.getElementById('offset');

    loadDoc();

    buttonPrev.addEventListener('click', function () {
        let offsetValue = parseInt(offset.value);

        if (offsetValue >= 1) {
            offset.value = offsetValue - 1;
        }

        loadDoc();
    });

    buttonNext.addEventListener('click', function () {
        let offsetValue = parseInt(offset.value);
        offset.value = offsetValue + 1;

        loadDoc();
    });

    buttonPostAll.addEventListener('click', function () {
        savePhoto();
    });

    buttonPostVideos.addEventListener('click', function () {
        saveVideo();
    });

    function loadDoc() {
        let offsetValue = parseInt(offset.value);

        const xhr = new XMLHttpRequest();

        xhr.open('POST', 'api.php?mode=get_files_from_folder', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const response = xhr.responseText;

                if (typeof response === 'undefined' || response === '') {
                    offset.value = offsetValue - 1;
                }

                if (typeof response !== 'undefined' && response !== '') {
                    let data = JSON.parse(response);

                    if (data && typeof data.file_path !== 'undefined') {
                        image.src = data.file_path;
                    }
                }
            }
        };

        xhr.send('offset=' + offsetValue);
    }

    function savePhoto() {
        const xhr = new XMLHttpRequest();

        xhr.open('POST', 'api.php?mode=save_photos', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        const params = 'file_path=' + encodeURIComponent(image.src) + '&is_post_all_photos=true';

        xhr.send(params);
    }

    function saveVideo() {
        const xhr = new XMLHttpRequest();

        xhr.open('POST', 'api.php?mode=save_videos', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send('');
    }
});

