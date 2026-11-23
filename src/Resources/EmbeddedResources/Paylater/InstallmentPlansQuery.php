<?php

namespace UnzerSDK\Resources\EmbeddedResources\Paylater;

/**
 * This class contains the query properties which are used to fetch paylater installment plans.
 *
 * @deprecated since the introduction of the generalized paylater plans endpoint. Use
 *             {@see PaylaterPlansQuery} together with Unzer::fetchPaylaterPlans() instead.
 * @link  https://docs.unzer.com/
 *
 */
class InstallmentPlansQuery extends PaylaterPlansQuery
{
}
