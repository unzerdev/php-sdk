<?php

namespace UnzerSDK\Resources\EmbeddedResources\Paylater;

/**
 * Resource representing a single plan item of the legacy installment plans flow.
 *
 * @deprecated since the plan item was generalized across all paylater types. Use {@see PaylaterPlan}
 *             instead; it is the item type returned by the generalized `Unzer::fetchPaylaterPlans()`
 *             flow. This class is kept as an unchanged subclass so the deprecated
 *             `Unzer::fetchPaylaterInstallmentPlans()` flow and existing type hints keep working
 *             (an InstallmentPlan is-a PaylaterPlan). It will be removed in a future major version.
 *
 * @link  https://docs.unzer.com/
 *
 */
class InstallmentPlan extends PaylaterPlan
{
}
