<?php

use Codedungeon\PHPCliColors\Color;
use GuzzleHttp\Client;

require_once "vendor/autoload.php";

const XML_URL = 'https://e-trust.gosuslugi.ru/app/scc/portal/api/v1/portal/ca/getxml';

$options = getopt("hve:", ['help', 'verbose', 'executable:']);
if (isset($options['h']) || isset($options['help'])) {
    echo Color::GREEN, 'Загрузка корневых сертификатов с Госуслуг', Color::RESET, PHP_EOL;
    echo "Утилита загружает конревые сертификаты аккредитованных УЦ, которые опубликованы по адресу https://e-trust.gosuslugi.ru/#/portal/accreditation/accreditedcalist", PHP_EOL;
    echo "Опции:", PHP_EOL;
    echo "-h, --help - Показать справку\n";
    echo "-v, --verbose - Выводить отладочную информацию\n";
    echo "-e, --executable - Путь к утилите certmgr (по умолчанию /opt/cprocsp/bin/amd64/certmgr)\n";
    exit;
}
$verbose = (isset($options['v']) || isset($options['verbose']));

//Адрес установки КриптоПро
if (isset($options['e'])) {
    $cryptcpExec = $options['e'];
} elseif (isset($options['executable'])) {
    $cryptcpExec = $options['executable'];
} else {
    $cryptcpExec = '/opt/cprocsp/bin/amd64/certmgr';
}

try {
    //Проверяем установку КриптоПро

    echo Color::CYAN, 'Проверяем установку КриптоПро по адресу ' . $cryptcpExec . '... ', Color::RESET;
    $shell = shell_exec($cryptcpExec . ' -help');
    if (strpos($shell, '[ErrorCode: 0x00000000]') === false) {
        echo Color::RED, 'ERROR', Color::RESET, PHP_EOL;
        throw new Exception('Утилита certmgr КриптоПро не найдена');
    }
    echo Color::GREEN, 'ОК', Color::RESET, PHP_EOL;

    //Скачиваем список сертов
    $client = new \GuzzleHttp\Client();

    echo Color::CYAN, 'Получаем XML по адресу '.XML_URL, Color::RESET, PHP_EOL;

    $html = $client->get(XML_URL)->getBody()->getContents();

    if (!$html) {
        throw new Exception('Получено пустое содержимое списка сертификатов');
    }

    echo Color::GREEN, 'Ответ получен, ' . strlen($html) . ' байт', Color::RESET, PHP_EOL;
    $rootCertificatesCollection = new \nikserg\CarootUpdater\RootCertificatesCollection($html);

    echo Color::GREEN, 'Всего УЦ ' . $rootCertificatesCollection->getRootCertificatesCount(), Color::RESET, PHP_EOL;
    $index = 1;

    //Загружаем кореневые каждого УЦ
    foreach ($rootCertificatesCollection as $child) {

        echo Color::LIGHT_BLUE, $index++ . '/' . $rootCertificatesCollection->getRootCertificatesCount() . ' ' . $child->getName(), Color::RESET, PHP_EOL;
        if ($child->getStatus() == \nikserg\CarootUpdater\RootUC::STATUS_ACTIVE) {
            echo Color::GREEN, '   Действует', Color::RESET, PHP_EOL;
        } else {
            echo Color::RED, '   ' . $child->getStatus(), Color::RESET, PHP_EOL;
        }

        foreach ($child->getCertificates() as $certificate) {
            echo Color::CYAN, '   Отпечаток: ', $certificate->getThumbprint(), Color::RESET, PHP_EOL;
            echo Color::CYAN, '   С: ', $certificate->getPeriodFrom()->format('d.m.Y H:i:s'), Color::RESET, PHP_EOL;
            echo Color::CYAN, '   По: ', $certificate->getPeriodTo()->format('d.m.Y H:i:s'), Color::RESET, PHP_EOL;

            //Сохраняем содержимое в файл
            $filename = tempnam(sys_get_temp_dir(), 'caroot');
            file_put_contents($filename, $certificate->getContent());

            //Устанавливаем сертификат
            $command = 'yes "o" | ' . $cryptcpExec . ' -inst -store uRoot -file ' . $filename;
            echo Color::LIGHT_YELLOW, '   Выполняем команду ', $command, '...', Color::RESET;

            $result = shell_exec($command);
            if (strpos($shell, '[ErrorCode: 0x00000000]') === false) {
                echo Color::RED, '   ERROR', Color::RESET, PHP_EOL;
                throw new Exception('Неправильный ответ на команду: ' . $result);
            }
            echo Color::GREEN, '   OK', Color::RESET, PHP_EOL;
            if ($verbose) {
                echo Color::LIGHT_GRAY, $result, Color::RESET, PHP_EOL;
            }
            unlink($filename);
        }
    }

} catch (Exception $e) {
    echo Color::RED, 'Ошибка ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
}
