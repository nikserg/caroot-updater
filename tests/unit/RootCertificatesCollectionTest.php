<?php

class RootCertificatesCollectionTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \nikserg\CarootUpdater\RootCertificatesCollection
     */
    private $rootCertificatesCollection;

    protected function setUp(): void
    {
        $this->rootCertificatesCollection = new \nikserg\CarootUpdater\RootCertificatesCollection(file_get_contents(__DIR__ . '/../_data/get.xml'));
    }

    public function testCount()
    {
        $this->assertEquals(503, $this->rootCertificatesCollection->getRootCertificatesCount());
    }

    public function testCollection()
    {
        foreach ($this->rootCertificatesCollection as $item) {
            $this->assertGreaterThan(0, strlen($item->getName()));
            $this->assertGreaterThan(0, strlen($item->getStatus()));
            foreach ($item->getCertificates() as $certificate) {
                $this->assertGreaterThan(0, strlen($certificate->getContent()));
                $this->assertGreaterThan(0, strlen($certificate->getThumbprint()));
            }
        }
    }

    public function testCheckCertain()
    {
        $this->assertEquals('Государственной корпорации по строительству олимпийских объектов и развитию города Сочи как горноклиматического курорта', $this->rootCertificatesCollection->current()->getName());
        $this->rootCertificatesCollection->next();
        $this->assertEquals('Министерство финансов Российской Федерации', $this->rootCertificatesCollection->current()->getName());
        $this->rootCertificatesCollection->next();
        $this->assertEquals('Общество с ограниченной ответственностью  «Экспертиза»', $this->rootCertificatesCollection->current()->getName());
        $this->rootCertificatesCollection->next();
        $this->assertEquals('Закрытое акционерное общество «КИБЕРПЛАТ»', $this->rootCertificatesCollection->current()->getName());
        $this->rootCertificatesCollection->next();
        $this->assertEquals('Федеральное бюджетное учреждение «Федеральный центр анализа и оценки техногенного воздействия»', $this->rootCertificatesCollection->current()->getName());
        $this->assertEquals(4, $this->rootCertificatesCollection->key());
        $root = $this->rootCertificatesCollection->current();
        $this->assertEquals('Федеральное бюджетное учреждение «Федеральный центр анализа и оценки техногенного воздействия»', $root->getName());
        $this->assertEquals(\nikserg\CarootUpdater\RootUC::STATUS_ACTIVE, $root->getStatus());
        $certificate = $root->getCertificates()[0];
        $this->assertEquals('B03612B60E79E7B416CD0B27416881718D67230D', $certificate->getThumbprint());
        $this->assertEquals('04.12.2017', $certificate->getPeriodTo()->format('d.m.Y'));
        $this->assertEquals('09.04.2015', $certificate->getPeriodFrom()->format('d.m.Y'));
        $this->assertEquals('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', $certificate->getContent());


    }
}