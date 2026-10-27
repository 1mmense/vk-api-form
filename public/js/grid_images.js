document.addEventListener('DOMContentLoaded', function () {
    const gridImages = document.getElementById('gridImages');
    const buttonUploadImages = document.getElementById('buttonUploadImages');
    const template = document.getElementById('cardTemplate');

    let isPosting = false;

    async function loadImages() {
        try {
            const response = await fetch('api.php?mode=get_all_images');
            const { data: images = [] } = await response.json();

            gridImages.innerHTML = '';

            if (!images || images.length === 0) {
                gridImages.innerHTML = '<p class="col-span-full text-center text-gray-500 py-8">No images found</p>';

                return;
            }

            images.forEach(photo => {
                const card = template.content.cloneNode(true).querySelector('.card-template');
                card.dataset.path = photo.path;

                const img = card.querySelector('.card-image');
                img.src = photo.path;
                img.alt = photo.basename;

                card.querySelector('.card-name').textContent = photo.basename;
                gridImages.appendChild(card);
            });
        } catch (error) {
            console.error('Failed to load images:', error);
            gridImages.innerHTML = '<p class="col-span-full text-center text-red-400 py-8">Error loading images</p>';
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

    async function uploadPhoto(card) {
        const form = document.getElementById('optionsForm');
        const formData = new FormData(form);

        formData.append('filePath', card.dataset.path);

        const response = await fetch('api.php?mode=upload_images', {
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

    async function postAllImages() {
        if (isPosting) {
            return
        };

        isPosting = true;

        buttonUploadImages.disabled = true;
        buttonUploadImages.classList.add('opacity-50', 'cursor-not-allowed');

        const cards = Array.from(gridImages.querySelectorAll('.card-template'));
        const interval = window.APP_CONFIG.REQUEST_SLEEP_TIME_MS;

        // Создаем пул с интервалом между стартами
        const pool = new RequestPool(interval);

        let done = 0;
        const cardsToPost = cards.length;

        const buttonText = buttonUploadImages.textContent;
        buttonUploadImages.textContent = `Posting (done ${done}/${cardsToPost}...)`;

        // Добавляем задачи в пул
        const promises = cards.map(card => {
            return pool.add(async () => {

                // Вызываем loading в момент, когда задача стартовала
                setCardStatus(card, 'loading');

                try {
                    const result = await uploadPhoto(card);

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
                buttonUploadImages.textContent = `Posting ${done}/${cardsToPost}...`;
            });
        });

        // Ждем завершения всех задач
        await Promise.all(promises);

        isPosting = false;
        buttonUploadImages.disabled = false;
        buttonUploadImages.classList.remove('opacity-50', 'cursor-not-allowed');

        if (gridImages.querySelectorAll('.card-template').length === 0) {
            gridImages.innerHTML = '<p class="col-span-full text-center text-green-400 py-8">All images posted successfully!</p>';
        }

        buttonUploadImages.textContent = buttonText;
    }

    loadImages();

    buttonUploadImages.addEventListener('click', postAllImages);
});