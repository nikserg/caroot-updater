[![Build Status](https://travis-ci.org/nikserg/caroot-updater.svg?branch=main)](https://travis-ci.org/nikserg/caroot-updater)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nikserg/caroot-updater/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/nikserg/caroot-updater/?branch=main)


# Обновление доверенных сертификатов КриптоПро

Скрипт для обновления доверенных корневых сертификатов с сайта 
https://e-trust.gosuslugi.ru/#/portal/accreditation/accreditedcalist

Автоматически скачивает сертификаты и устанавливает их как доверенные c помощью 
утилиты certmgr, входящей в комплект поставки криптопровайдера КриптоПро.

## Установка
```
git clone https://github.com/nikserg/caroot-updater.git .
composer install
```

Работает только в Unix-системах.

Для работы требуется установленный криптопровайдер КриптоПро.

## Использование
`php run.php`

### Опции
-h, --help - Показать справку

-v, --verbose - Выводить отладочную информацию

-e, --executable - Путь к утилите certmgr (по умолчанию /opt/cprocsp/bin/amd64/certmgr)
