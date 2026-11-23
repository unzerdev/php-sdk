<?php

namespace UnzerSDK\Resources;

use UnzerSDK\Resources\EmbeddedResources\Paylater\PaylaterPlansQuery;

/**
 * Resource used to fetch instalment plans for the Paylater Installment payment method specified as parent resource.
 * Please use Unzer methods to fetch the list of instalment plans (e.g. Unzer::fetchPaylaterInstallmentPlans(...)).
 *
 * @deprecated since the introduction of the generalized paylater plans endpoint. Use
 *             {@see PaylaterPlans} together with Unzer::fetchPaylaterPlans() instead.
 * @link  https://docs.unzer.com/
 *
 */
class PaylaterInstallmentPlans extends PaylaterPlans
{
    /**
     * Keeps the original fluent return type (PaylaterInstallmentPlans) for backward compatibility.
     *
     * @param PaylaterPlansQuery $queryParameter
     *
     * @return PaylaterInstallmentPlans
     */
    public function setQueryParameter(PaylaterPlansQuery $queryParameter): PaylaterInstallmentPlans
    {
        parent::setQueryParameter($queryParameter);
        return $this;
    }
}
