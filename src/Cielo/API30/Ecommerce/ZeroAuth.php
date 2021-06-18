<?php

namespace Cielo\API30\Ecommerce;

use Cielo\API30\Ecommerce\CieloSerializable;

/**
 * Class ZeroAuth
 *
 * @package Cielo\API30\Ecommerce
 */
class ZeroAuth implements \JsonSerializable, CieloSerializable
{
    /** @var string $valid */
    private $valid;

    /** @var string $returnCode */
    private $returnCode;

    /** @var string $returnMessage */
    private $returnMessage;

    /** @var string $issuerTransactionId */
    private $issuerTransactionId;

    /** @var string $code */
    private $code;

    /** @var string $message */
    private $message;


    /**
     * ZeroAuth constructor.
     *
     * @param null
    */
    public function __construct()
    {
    }

    /**
     * @return array
    */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    /**
     * @param \stdClass $data
    */
    public function populate(\stdClass $data)
    {
        $this->valid                     = isset($data->Valid) ? $data->Valid : null;
        $this->returnCode                = isset($data->ReturnCode) ? $data->ReturnCode : null;
        $this->returnMessage             = isset($data->ReturnMessage) ? $data->ReturnMessage : null;
        $this->issuerTransactionId       = isset($data->IssuerTransactionId) ? $data->IssuerTransactionId : null;
        $this->code                      = isset($data->Code) ? $data->Code : null;
        $this->message                   = isset($data->Message) ? $data->Message : null;
    }

    /**
     * @return mixed
    */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param $message
     *
     * @return $this
    */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return mixed
    */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param $code
     *
     * @return $this
    */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return mixed
    */
    public function getValid()
    {
        return $this->valid;
    }

    /**
     * @param $valid
     *
     * @return $this
    */
    public function setValid($valid)
    {
        $this->valid = $valid;

        return $this;
    }

    /**
     * @return mixed
    */
    public function getReturnCode()
    {
        return $this->returnCode;
    }

    /**
     * @param $returnCode
     *
     * @return $this
    */
    public function setReturnCode()
    {
        $this->returnCode = $returnCode;

        return $this;
    }

    /**
     * @return mixed
    */
    public function getReturnMessage()
    {
        return $this->returnMessage;
    }

    /**
     * @param $returnMessage
     *
     * @return $this
    */
    public function setReturnMessage($returnMessage)
    {
        $this->returnMessage = $returnMessage;

        return $this;
    }

    /**
     * @return mixed
    */
    public function getIssuerTransactionId()
    {
        return $this->issuerTransactionId;
    }

    /**
     * @param $issuerTransactionId
     *
     * @return $this
    */
    public function setIssuerTransactionId($issuerTransactionId)
    {
        $this->issuerTransactionId = $issuerTransactionId;

        return $this;
    }

    public function fromJson($json)
    {
        $object = json_decode($json);

        $zeroAuth = new ZeroAuth();
        $zeroAuth->populate($object);

        return $zeroAuth;

    }
}
