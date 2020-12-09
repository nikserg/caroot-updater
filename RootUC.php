<?php

namespace nikserg\CarootUpdater;

/**
 * Class RootCertificate
 *
 * @package nikserg\CarootUpdater
 */
class RootUC
{
    const STATUS_ACTIVE = 'Действует';
    const STATUS_ANNULLED = 'Аннулирована';

    /**
     * @var \SimpleXMLElement
     */
    protected $xml;

    public function __construct(\SimpleXMLElement $xml)
    {
        $this->xml = $xml;
    }

    /**
     * Название УЦ
     *
     *
     * @return string
     */
    public function getName()
    {
        return (string)($this->xml->Название);
    }
    /**
     * Статус аккредитации УЦ
     *
     * @return string
     * @see RootUC::STATUS_ACTIVE
     */
    public function getStatus()
    {
        return (string)($this->xml->СтатусАккредитации->Статус);
    }

    /**
     * Сертификаты этого УЦ
     *
     *
     * @return RootUCCertificate[]
     */
    public function getCertificates()
    {
        if (!$this->xml->ПрограммноАппаратныеКомплексы->ПрограммноАппаратныйКомплекс->КлючиУполномоченныхЛиц) {
            return [];
        }
        $certificates = $this->xml->ПрограммноАппаратныеКомплексы->ПрограммноАппаратныйКомплекс->КлючиУполномоченныхЛиц->Ключ->Сертификаты->ДанныеСертификата;
        $return = [];
        foreach ($certificates as $certificate) {
            $return[] = new RootUCCertificate($certificate);
        }
        return $return;
    }

}