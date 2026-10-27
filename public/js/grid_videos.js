document.addEventListener('DOMContentLoaded', function () {
    const gridVideos = document.getElementById('gridVideos');
    const buttonUploadVideos = document.getElementById('buttonUploadVideos');
    const template = document.getElementById('cardTemplate');
    const pollingInterval = 5000;

    let isPosting = false;

    // Хранилище путей видео, для которых превью уже установлено.
    // Set обеспечивает уникальность значений и быструю проверку через has().
    const thumbnailsReady = new Set();

    // Пытается загрузить превью для карточки.
    // Если загрузка не удалась — удаляет путь из Set, чтобы следующий poll попробовал снова.
    function tryLoadThumb(img, videoPath, thumbnail) {
        img.onload = function () {
            // Только после успешной загрузки помечаем как готовое
            thumbnailsReady.add(videoPath);
        };

        img.onerror = function () {
            // При ошибке — снимаем метку готовности
            thumbnailsReady.delete(videoPath);
        };

        img.src = thumbnail;
    }

    async function loadVideos() {
        try {
            const response = await fetch('api.php?mode=get_all_videos');
            const { data: videos = [] } = await response.json();

            gridVideos.innerHTML = '';

            if (!videos || videos.length === 0) {
                gridVideos.innerHTML = '<p class="col-span-full text-center text-gray-500 py-8">No videos found</p>';

                return;
            }

            videos.forEach(video => {
                const card = template.content.cloneNode(true).querySelector('.card-template');
                card.dataset.path = video.path;

                const img = card.querySelector('.card-image');
                img.alt = video.basename;

                if (video.thumbnail) {
                    tryLoadThumb(img, video.path, video.thumbnail);
                } else {
                    // Превью нет — не ставим src, чтобы браузер не грузил видео как картинку
                    img.removeAttribute('src');
                }

                card.querySelector('.card-name').textContent = video.basename;
                gridVideos.appendChild(card);
            });
        } catch (error) {
            console.error('Failed to load videos:', error);
            gridVideos.innerHTML = '<p class="col-span-full text-center text-red-400 py-8">Error loading videos</p>';
        }
    }

    function setCardStatus(card, status) {
        const statusEl = card.querySelector('.card-status');
        const spinner = card.querySelector('.card-spinner');
        const success = card.querySelector('.card-success');
        const error = card.querySelector('.card-error');

        spinner.classList.add('hidden');
        success.classList.add('hidden');
        error.classList.add('hidden');
        statusEl.classList.add('opacity-0');

        if (status === 'loading') {
            statusEl.classList.remove('opacity-0');
            spinner.classList.remove('hidden');

            return Promise.resolve();
        } else if (status === 'success') {
            statusEl.classList.remove('opacity-0');
            success.classList.remove('hidden');

            return new Promise(resolve => {
                setTimeout(() => {
                    card.remove();
                    resolve();
                }, 2000);
            });
        } else if (status === 'error') {
            statusEl.classList.remove('opacity-0');
            error.classList.remove('hidden');
            setTimeout(() => statusEl.classList.add('opacity-0'), 3000);

            return Promise.resolve();
        }
    }

    async function uploadVideo(card) {
        const form = document.getElementById('optionsForm');
        const formData = new FormData(form);

        formData.append('filePath', card.dataset.path);

        const response = await fetch('api.php?mode=upload_videos', {
            method: 'POST',
            body: formData,
        });

        let data = null;

        try {
            data = await response.json();
        } catch (error) {
            return {
                success: false,
                error: 'invalid server response'
            };
        }

        if (!response.ok || !data.success) {
            return {
                success: false,
                error: data.error ?? 'unknown error'
            };
        }

        return { success: true };
    }

    async function postAllVideos() {
        if (isPosting) {
            return
        };

        isPosting = true;

        buttonUploadVideos.disabled = true;
        buttonUploadVideos.classList.add('opacity-50', 'cursor-not-allowed');

        const cards = Array.from(gridVideos.querySelectorAll('.card-template'));
        const interval = window.APP_CONFIG.REQUEST_SLEEP_TIME_MS;

        // Создаем пул для задач с интервалом между стартами
        const pool = new RequestPool(interval);

        let done = 0;
        const cardsToPost = cards.length;

        const buttonText = buttonUploadImages.textContent;
        buttonUploadVideos.textContent = `Posting ${done}/${cardsToPost}...`;

        // Добавляем задачи в пул
        const promises = cards.map(card => {
            return pool.add(async () => {
                // Вызываем loading в момент, когда задача стартовала
                setCardStatus(card, 'loading');

                try {
                    const result = await uploadVideo(card);

                    if (result.success) {
                        await setCardStatus(card, 'success');
                    } else {
                        console.error('Upload error for', card.dataset.path, result.error);
                        setCardStatus(card, 'error');
                    }
                } catch (error) {
                    console.error('Upload exception for', card.dataset.path, error);
                    setCardStatus(card, 'error');
                }

                done++;
                buttonUploadVideos.textContent = `Posting ${done}/${cardsToPost}...`;
            });
        });

        // Ждем завершения всех задач
        await Promise.all(promises);

        isPosting = false;

        buttonUploadVideos.disabled = false;
        buttonUploadVideos.classList.remove('opacity-50', 'cursor-not-allowed');

        if (gridVideos.querySelectorAll('.card-template').length === 0) {
            gridVideos.innerHTML = '<p class="col-span-full text-center text-green-400 py-8">All videos posted successfully!</p>';
        }

        buttonUploadVideos.textContent = buttonText;
    }

    let pollTimer = null;

    async function pollThumbs() {
        if (isPosting) {
            return
        };

        try {
            const response = await fetch('api.php?mode=get_video_thumbs');
            const { data: videos = [] } = await response.json();
            const cards = Array.from(gridVideos.querySelectorAll('.card-template'));

            videos.forEach(video => {
                const card = cards.find(card => card.dataset.path === video.path);

                if (!card) {
                    return;
                }

                const img = card.querySelector('.card-image');

                // Обновляем src только если превью есть и оно ещё не было установлено
                if (video.thumbnail && !thumbnailsReady.has(video.path)) {
                    tryLoadThumb(img, video.path, video.thumbnail);
                }
            });

            // Останавливаем polling только когда у ВСЕХ карточек в DOM есть превью
            const allCardsHaveThumb = Array.from(gridVideos.querySelectorAll('.card-template'))
                .every(card => thumbnailsReady.has(card.dataset.path));

            if (allCardsHaveThumb) {
                stopPolling();
            }
        } catch (error) {
            console.error('Polling error:', error);
        }
    }

    function startPolling() {
        if (pollTimer) {
            return;
        }

        pollTimer = setInterval(pollThumbs, pollingInterval);
    }

    function stopPolling() {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    loadVideos().then(() => {
        // Запускаем polling только если есть видео без превью
        const hasMissing = Array.from(gridVideos.querySelectorAll('.card-template'))
            .some(card => !thumbnailsReady.has(card.dataset.path));

        if (hasMissing) {
            // pollThumbs();
            startPolling();
        }
    });

    buttonUploadVideos.addEventListener('click', postAllVideos);
});