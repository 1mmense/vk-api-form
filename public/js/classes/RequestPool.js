class RequestPool {
    constructor(intervalTimeMs) {
        this.intervalTimeMs = intervalTimeMs;
        this.queue = [];
        this.lastStartTime = 0;
        this.isScheduled = false; // Защита от дублей таймеров
    }

    add(task) {
        return new Promise((resolve, reject) => {
            this.queue.push({ task, resolve, reject });
            this._process();
        });
    }

    _process() {
        while (this.queue.length > 0) {
            // Используем performance.now() для независимости от системных часов
            const now = performance.now();
            const timeSinceLast = now - this.lastStartTime;

            if (timeSinceLast < this.intervalTimeMs) {
                if (!this.isScheduled) {
                    this.isScheduled = true;

                    setTimeout(
                        () => {
                            this.isScheduled = false;
                            this._process();
                        },
                        this.intervalTimeMs - timeSinceLast
                    );
                }

                return; // Выходим, чтобы не запускать следующий запрос раньше времени
            }

            const { task, resolve, reject } = this.queue.shift();

            this.lastStartTime = performance.now();

            task()
                .then(resolve)
                .catch(reject)
                .finally(() => {
                    this._process();
                });
        }
    }
}