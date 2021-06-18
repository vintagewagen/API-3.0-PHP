<?php

namespace Cielo\API30\Ecommerce;

use Cielo\API30\Ecommerce\CieloSerializable;
use Cielo\API30\Ecommerce\Items;
/**
 * Class BinQuery
 *
 * @package Cielo\API30\Ecommerce
 */
class BinQuery implements \JsonSerializable, CieloSerializable
{
    /** @var bool $isAccepted */
    private $isAccepted;

    /** @var string $status */
    private $status;

    /** @var string $provider */
    private $provider;

    /** @var string $cardType */
    private $cardType;

    /** @var string $foreignCard */
    private $foreignCard;

    /** @var string $corporateCard */
    private $corporateCard;

    /** @var string $issuer */
    private $issuer;

    /** @var string $issuerCode */
    private $issuerCode;

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
        $this->isAccepted              = isset($data->Status) && $data->Status == '00' ? true : false;
        $this->status                  = isset($data->Status) ? $data->Status : null;
        $this->provider                = isset($data->Provider) ? $data->Provider : null;
        $this->cardType                = isset($data->CardType) ? $data->CardType : null;
        $this->foreignCard             = isset($data->ForeignCard) ? $data->ForeignCard : null;
        $this->corporateCard           = isset($data->CorporateCard) ? $data->CorporateCard : null;
        $this->issuer                  = isset($data->Issuer) ? $data->Issuer : null;
        $this->issuerCode              = isset($data->IssuerCode) ? $data->IssuerCode : null;
    }

    /**
     * @return mixed
    */
    public function getIsAccepted()
    {
        return $this->isAccepted;
    }

    /**
     * @param $isAccepted
     *
     * @return $this
    */
    public function setIsAccepted($isAccepted)
    {
        $this->isAccepted = $isAccepted;

        return $this;
    }

    /**
     * @return mixed
    */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param $status
     *
     * @return $this
    */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return mixed
    */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param $provider
     *
     * @return $this
    */
    public function setProvider($provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * @return mixed
    */
    public function getCardType()
    {
        return $this->cardType;
    }

    /**
     * @param $cardType
     *
     * @return $this
    */
    public function setCardType($cardType)
    {
        $this->cardType = $cardType;

        return $this;
    }

    /**
     * @return mixed
    */
    public function getForeignCard()
    {
        return $this->foreignCard;
    }

    /**
     * @param $foreignCard
     *
     * @return $this
    */
    public function setForeignCard($foreignCard)
    {
        $this->foreignCard = $foreignCard;

        return $this;
    }

    /**
     * @return mixed
    */
    public function getCorporateCard()
    {
        return $this->corporateCard;
    }

    /**
     * @param $corporateCard
     *
     * @return $this
    */
    public function setCorporateCard($corporateCard)
    {
        $this->corporateCard = $corporateCard;

        return $this;
    }

    /**
     * @return mixed
    */
    public function getIssuer()
    {
        return $this->issuer;
    }

    /**
     * @param $issuer
     *
     * @return $this
    */
    public function setIssuer($issuer)
    {
        $this->issuer = $issuer;

        return $this;
    }

    /**
     * @return mixed
    */
    public function getIssuerCode()
    {
        return $this->issuerCode;
    }

    /**
     * @param $issuerCode
     *
     * @return $this
    */
    public function setIssuerCode($issuerCode)
    {
        $this->issuerCode = $issuerCode;

        return $this;
    }

    /**
     * @param $json
     *
     * @return BinQuery
     */
    public static function fromJson($json)
    {
        $object = json_decode($json);

        $binQuery = new BinQuery();
        $binQuery->populate($object);

        return $binQuery;
    }

}
