<?php

namespace Cielo\API30\Ecommerce;

/**
 * Resultado da autenticação 3DS 2.x feita fora da API (script 3DS da Cielo ou
 * MPI próprio). Obrigatório em transações de débito e autenticadas.
 *
 * @see https://docs.cielo.com.br/ecommerce-cielo/docs/3ds-sobre.md
 */
class ExternalAuthentication implements CieloSerializable
{
    private $cavv;

    private $xid;

    private $eci;

    private $version;

    private $referenceId;

    private $dataOnly;

    public function jsonSerialize(): array
    {
        return array_filter(get_object_vars($this), fn ($value) => $value !== null);
    }

    public function populate(\stdClass $data)
    {
        $this->cavv        = $data->Cavv ?? null;
        $this->xid         = $data->Xid ?? null;
        $this->eci         = $data->Eci ?? null;
        $this->version     = $data->Version ?? null;
        $this->referenceId = $data->ReferenceId ?? null;
        $this->dataOnly    = isset($data->DataOnly) ? (bool) $data->DataOnly : null;
    }

    public function getCavv()
    {
        return $this->cavv;
    }

    /**
     * @return $this
     */
    public function setCavv($cavv)
    {
        $this->cavv = $cavv;

        return $this;
    }

    public function getXid()
    {
        return $this->xid;
    }

    /**
     * @return $this
     */
    public function setXid($xid)
    {
        $this->xid = $xid;

        return $this;
    }

    public function getEci()
    {
        return $this->eci;
    }

    /**
     * @return $this
     */
    public function setEci($eci)
    {
        $this->eci = $eci;

        return $this;
    }

    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version "2.2.0" para Visa/Mastercard, "2.1.0" para Elo/Amex
     *
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    public function getReferenceId()
    {
        return $this->referenceId;
    }

    /**
     * @return $this
     */
    public function setReferenceId($referenceId)
    {
        $this->referenceId = $referenceId;

        return $this;
    }

    public function getDataOnly()
    {
        return $this->dataOnly;
    }

    /**
     * @return $this
     */
    public function setDataOnly($dataOnly)
    {
        $this->dataOnly = $dataOnly;

        return $this;
    }
}
