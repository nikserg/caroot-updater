<?php

namespace nikserg\CarootUpdater;

use DateTime;
use Exception;

/**
 * Class RootUCCertificate
 *
 * @package nikserg\CarootUpdater
 */
class RootUCCertificate
{
    /**
     * @var \SimpleXMLElement
     */
    protected $xml;

    public function __construct(\SimpleXMLElement $xml)
    {
        $this->xml = $xml;
    }

    /**
     * @return DateTime
     * @throws Exception
     */
    public function getPeriodFrom()
    {
        return new DateTime($this->xml->ПериодДействияС);
    }
    /**
     * @return DateTime
     * @throws Exception
     */
    public function getPeriodTo()
    {
        return new DateTime($this->xml->ПериодДействияДо);
    }

    /**
     * @return string
     */
    public function getThumbprint()
    {
        return (string)($this->xml->Отпечаток);
    }

    /**
     * Содержимое сертификата
     *
     * @return string
     */
    public function getContent()
    {
        return (string)($this->xml->Данные);
    }


}