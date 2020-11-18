<?php

use Codedungeon\PHPCliColors\Color;
use GuzzleHttp\Client;

require_once "vendor/autoload.php";

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

    echo Color::CYAN, 'Получаем XML по адресу https://e-trust.gosuslugi.ru/app/scc/portal/api/v1/portal/ca/getxml', Color::RESET, PHP_EOL;

    $html = $client->get('https://e-trust.gosuslugi.ru/app/scc/portal/api/v1/portal/ca/getxml')->getBody()->getContents();

    if (!$html) {
        throw new Exception('Получено пустое содержимое списка сертификатов');
    }

    echo Color::GREEN, 'Ответ получен, ' . strlen($html) . ' байт', Color::RESET, PHP_EOL;
    $xml = new SimpleXMLElement($html);
    $ucs = $xml->УдостоверяющийЦентр;
    $total = $ucs->count();
    echo Color::GREEN, 'Всего УЦ ' . $total, Color::RESET, PHP_EOL;
    $index = 1;

    //Загружаем кореневые каждого УЦ
    foreach ($ucs as $child) {

        echo Color::LIGHT_BLUE, $index++ . '/' . $total . ' ' . $child->Название, Color::RESET, PHP_EOL;
        if ($child->СтатусАккредитации->Статус == 'Действует') {
            echo Color::GREEN, '   Действует', Color::RESET, PHP_EOL;
        } else {
            echo Color::RED, '   ' . $child->СтатусАккредитации->Статус, Color::RESET, PHP_EOL;
        }

        $certificates = $child->ПрограммноАппаратныеКомплексы->ПрограммноАппаратныйКомплекс->КлючиУполномоченныхЛиц->Ключ->Сертификаты->ДанныеСертификата;
        foreach ($certificates as $certificate) {
            echo Color::CYAN, '   Отпечаток: ', $certificate->Отпечаток, Color::RESET, PHP_EOL;
            echo Color::CYAN, '   С: ', $certificate->ПериодДействияС, Color::RESET, PHP_EOL;
            echo Color::CYAN, '   По: ', $certificate->ПериодДействияДо, Color::RESET, PHP_EOL;

            //Сохраняем содержимое в файл
            $filename = tempnam(sys_get_temp_dir(), 'caroot');
            file_put_contents($filename, $certificate->Данные);

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

} catch
(Exception $e) {
    echo Color::RED, 'Ошибка ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
}
