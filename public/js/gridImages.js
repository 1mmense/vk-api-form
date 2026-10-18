document.addEventListener('DOMContentLoaded', function () {
    const gridImages = document.getElementById('gridImages');
    const buttonPostAll = document.getElementById('buttonPostAll');
    const template = document.getElementById('imageCardTemplate');

    let isPosting = false;

    async function loadPhotos() {
        try {
            const response = await fetch('api.php?mode=get_all_photos');
            const photos = await response.json();

            gridImages.innerHTML = '';

            if (!photos || photos.length === 0) {
                gridImages.innerHTML = '<p class="col-span-full text-center text-gray-500 py-8">No photos found</p>';

                return;
            }

            photos.forEach(photo => {
                const card = template.content.cloneNode(true).querySelector('.photo-card');
                card.dataset.path = photo.path;

                const img = card.querySelector('.card-image');
                img.src = photo.path;
                img.alt = photo.basename;

                card.querySelector('.card-name').textContent = photo.basename;
                gridImages.appendChild(card);
            });
        } catch (error) {
            console.error('Failed to load photos:', error);
            gridImages.innerHTML = '<p class="col-span-full text-center text-red-400 py-8">Error loading photos</p>';
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
        } else if (status === 'success') {
            statusEl.classList.remove('opacity-0');
            success.classList.remove('hidden');
            // setTimeout(() => card.remove(), 1000);
        } else if (status === 'error') {
            statusEl.classList.remove('opacity-0');
            error.classList.remove('hidden');
            setTimeout(() => statusEl.classList.add('opacity-0'), 3000);
        }
    }

    async function uploadPhoto(card) {
        const params = new URLSearchParams();
        params.append('file_path', card.dataset.path);
        params.append('is_post_all_photos', 'false');

        const response = await fetch('api.php?mode=upload_photos', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params.toString(),
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

    async function postAllPhotos() {
        if (isPosting) {
            return
        };

        isPosting = true;

        buttonPostAll.disabled = true;
        buttonPostAll.classList.add('opacity-50', 'cursor-not-allowed');

        const cards = Array.from(gridImages.querySelectorAll('.photo-card'));
        const interval = window.APP_CONFIG.REQUEST_SLEEP_TIME_MS;

        // Создаем пул с интервалом между стартами
        const pool = new RequestPool(interval);

        // Добавляем задачи в пул
        const promises = cards.map(card => {
            return pool.add(async () => {
                // Вызываем loading в момент, когда задача стартовала
                setCardStatus(card, 'loading');

                try {
                    const result = await uploadPhoto(card);

                    if (result.success) {
                        setCardStatus(card, 'success');
                    } else {
                        console.error('Upload error for', card.dataset.path, result.error);
                        setCardStatus(card, 'error');
                    }
                } catch (error) {
                    console.error('Upload exception for', card.dataset.path, error);
                    setCardStatus(card, 'error');
                }
            });
        });

        // Ждем завершения всех задач
        await Promise.all(promises);

        isPosting = false;
        buttonPostAll.disabled = false;
        buttonPostAll.classList.remove('opacity-50', 'cursor-not-allowed');

        if (gridImages.querySelectorAll('.photo-card').length === 0) {
            gridImages.innerHTML = '<p class="col-span-full text-center text-green-400 py-8">All photos posted successfully!</p>';
        }
    }

    loadPhotos();

    buttonPostAll.addEventListener('click', postAllPhotos);
});