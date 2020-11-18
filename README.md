# Обновление доверенных сертификатов КриптоПро

Скрипт для обновления доверенных корневых сертификатов с сайта 
https://e-trust.gosuslugi.ru/#/portal/accreditation/accreditedcalist

Автоматически скачивает сертификаты и устанавливает их как доверенные c помощью 
утилиты certmgr, входящей в комплект поставки криптопровайдера КриптоПро.

## Установка
`composer install`

Работает только в Unix-системах.

Для работы требуется установленный криптопровайдер КриптоПро.

## Использование
`php run.php`

### Опции
-h, --help - Показать справку

-v, --verbose - Выводить отладочную информацию

-e, --executable - Путь к утилите certmgr (по умолчанию /opt/cprocsp/bin/amd64/certmgr)
