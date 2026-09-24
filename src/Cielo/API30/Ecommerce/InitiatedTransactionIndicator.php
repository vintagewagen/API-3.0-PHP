<?php

namespace Cielo\API30\Ecommerce;

/**
 * Indicador de início da transação (CIT/MIT).
 *
 * Obrigatório para Mastercard em transações com credencial armazenada
 * (card-on-file, recorrência, parcelamento do lojista).
 *
 * @see https://docs.cielo.com.br/ecommerce-cielo/page/indicador-de-in%C3%ADcio-da-transa%C3%A7%C3%A3o-cit-e-mit
 */
class InitiatedTransactionIndicator implements CieloSerializable
{
    /** Iniciada pelo portador (Cardholder Initiated Transaction). */
    const CATEGORY_CARDHOLDER = 'C1';

    /** Iniciada pelo lojista, com credencial armazenada. */
    const CATEGORY_MERCHANT = 'M1';

    /** Iniciada pelo lojista por prática da indústria. */
    const CATEGORY_MERCHANT_INDUSTRY_PRACTICE = 'M2';

    // Subcategorias para C1 e M1
    const SUBCATEGORY_CREDENTIALS_ON_FILE = 'CredentialsOnFile';
    const SUBCATEGORY_STANDING_ORDER = 'StandingOrder';
    const SUBCATEGORY_SUBSCRIPTION = 'Subscription';
    const SUBCATEGORY_INSTALLMENT = 'Installment';

    // Subcategorias para M2
    const SUBCATEGORY_PARTIAL_SHIPMENT = 'PartialShipment';
    const SUBCATEGORY_RELATED_OR_DELAYED_CHARGE = 'RelatedOrDelayedCharge';
    const SUBCATEGORY_NO_SHOW = 'NoShow';
    const SUBCATEGORY_RESUBMISSION = 'Resubmission';

    private $category;

    private $subcategory;

    public function __construct($category = null, $subcategory = null)
    {
        $this->category    = $category;
        $this->subcategory = $subcategory;
    }

    public function jsonSerialize(): array
    {
        return array_filter(get_object_vars($this), fn ($value) => $value !== null);
    }

    public function populate(\stdClass $data)
    {
        $this->category    = $data->Category ?? null;
        $this->subcategory = $data->Subcategory ?? null;
    }

    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    public function getSubcategory()
    {
        return $this->subcategory;
    }

    /**
     * @return $this
     */
    public function setSubcategory($subcategory)
    {
        $this->subcategory = $subcategory;

        return $this;
    }
}
