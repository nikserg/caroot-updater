<?php

namespace nikserg\CarootUpdater;


/**
 * Class RootCertificatesCollection
 *
 * @package nikserg\CarootUpdater
 */
class RootCertificatesCollection implements \Iterator
{

    /**
     * @var \SimpleXMLElement
     */
    private $xml;

    /**
     * @var RootUC[]
     */
    private $rootCertificates;
    /**
     * @var int
     */
    private $pointer;

    public function __construct($xmlString)
    {
        $this->xml = new \SimpleXMLElement($xmlString);
    }

    /**
     * @return RootUC[]
     */
    protected function getRootCertificates()
    {
        if (!$this->rootCertificates) {
            $this->pointer = 0;
            $this->rootCertificates = [];
            foreach ($this->xml->УдостоверяющийЦентр as $xml) {
                $this->rootCertificates[] = new RootUC($xml);
            }
        }
        return $this->rootCertificates;
    }

    /**
     * @return int
     * @throws CarootException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getRootCertificatesCount()
    {
        return count($this->getRootCertificates());
    }

    /**
     * Return the current element
     *
     * @link https://php.net/manual/en/iterator.current.php
     * @return RootUC Can return any type.
     */
    public function current()
    {
        return $this->getRootCertificates()[$this->pointer];
    }

    /**
     * Move forward to next element
     *
     * @link https://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->pointer++;
    }

    /**
     * Return the key of the current element
     *
     * @link https://php.net/manual/en/iterator.key.php
     * @return int scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->pointer;
    }

    /**
     * Checks if current position is valid
     *
     * @link https://php.net/manual/en/iterator.valid.php
     * @return bool The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        if ($this->pointer < 0) {
            return false;
        }
        if ($this->pointer >= count($this->getRootCertificates())) {
            return false;
        }
        return true;
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @link https://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->pointer = 0;
    }
}