<?php

namespace Cielo\API30\Ecommerce;

/**
 * Indica se o cartão está sendo armazenado agora ou já estava armazenado.
 *
 * @see https://docs.cielo.com.br/ecommerce-cielo/reference/criar-pagamento-credito.md
 */
class CardOnFile implements CieloSerializable
{
    /** Primeira vez que a credencial é armazenada. */
    const USAGE_FIRST = 'First';

    /** Credencial armazenada anteriormente. */
    const USAGE_USED = 'Used';

    // Motivos, enviados só quando Usage = Used
    const REASON_RECURRING = 'Recurring';
    const REASON_UNSCHEDULED = 'Unscheduled';
    const REASON_INSTALLMENTS = 'Installments';

    private $usage;

    private $reason;

    public function __construct($usage = null, $reason = null)
    {
        $this->usage  = $usage;
        $this->reason = $reason;
    }

    public function jsonSerialize(): array
    {
        return array_filter(get_object_vars($this), fn ($value) => $value !== null);
    }

    public function populate(\stdClass $data)
    {
        $this->usage  = $data->Usage ?? null;
        $this->reason = $data->Reason ?? null;
    }

    public function getUsage()
    {
        return $this->usage;
    }

    /**
     * @return $this
     */
    public function setUsage($usage)
    {
        $this->usage = $usage;

        return $this;
    }

    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @return $this
     */
    public function setReason($reason)
    {
        $this->reason = $reason;

        return $this;
    }
}
