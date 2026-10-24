FROM php:8.5-apache

# Объявляем аргументы для пользователя
ARG USERNAME=xyuser
ARG USER_UID=1000
ARG USER_GID=1000

# Создаем пользователя (пока работает root, это пройдет без ошибок)
RUN groupadd --gid $USER_GID $USERNAME \
    && useradd --uid $USER_UID --gid $USER_GID -m $USERNAME

# Установка системных утилит и Node.js под root (теперь lock-файл откроется)
RUN apt-get update && apt-get install -y \
    ca-certificates \
    gnupg \
    ffmpeg \
    nano \
    curl \
    && curl -fsSL https://deb.nodesource.com/setup_22.x -o nodesource_setup.sh \
    && bash nodesource_setup.sh \
    && apt-get install -y nodejs \
    && rm nodesource_setup.sh \
    && rm -rf /var/lib/apt/lists/*

# Копируем composer и конфигурацию Apache
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY docker/apache/site.conf /etc/apache2/sites-available/000-default.conf

# Включаем mod_rewrite
RUN a2enmod rewrite

# Настраиваем Apache так, чтобы его рабочие процессы запускались под вашим UID/GID.
# Это уберет проблемы с правами на файлы и ошибку "I have no name!".
ENV APACHE_RUN_USER=$USERNAME
ENV APACHE_RUN_GROUP=$USERNAME

# Корректируем права на системные папки Apache, чтобы он мог стартовать под новым именем
RUN chown -R $USERNAME:$USERNAME /var/lock/apache2 /var/log/apache2 /var/run/apache2

# Выставляем права на рабочую директорию проекта
WORKDIR /var/www/html
RUN chown -R $USERNAME:$USERNAME /var/www/html

# Инструкцию `USER $USERNAME` здесь НЕ пишем.
# Контейнер должен стартовать под root, чтобы Apache занял 80-й порт,
# после чего Apache сам сбросит привилегии до $USERNAME.
