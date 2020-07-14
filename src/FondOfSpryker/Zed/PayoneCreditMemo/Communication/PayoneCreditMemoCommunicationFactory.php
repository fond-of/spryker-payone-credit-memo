<?php

namespace FondOfSpryker\Zed\PayoneCreditMemo\Communication;

use FondOfSpryker\Zed\PayoneCreditMemo\Dependency\Facade\PayoneCreditMemoToCreditMemoInterface;
use FondOfSpryker\Zed\PayoneCreditMemo\PayoneCreditMemoDependencyProvider;
use SprykerEco\Zed\Payone\Communication\PayoneCommunicationFactory as SprykerEcoPayoneCommunicationFactory;

/**
 * @method \SprykerEco\Zed\Payone\PayoneConfig getConfig()
 * @method \SprykerEco\Zed\Payone\Persistence\PayoneQueryContainerInterface getQueryContainer()
 * @method \SprykerEco\Zed\Payone\Persistence\PayoneRepositoryInterface getRepository()
 * @method \SprykerEco\Zed\Payone\Persistence\PayoneEntityManagerInterface getEntityManager()
 * @method \SprykerEco\Zed\Payone\Business\PayoneFacadeInterface getFacade()()
 */
class PayoneCreditMemoCommunicationFactory extends SprykerEcoPayoneCommunicationFactory
{
    /**
     * @return \FondOfSpryker\Zed\PayoneCreditMemo\Dependency\Facade\PayoneCreditMemoToCreditMemoInterface
     */
    public function getCreditMemoFacade(): PayoneCreditMemoToCreditMemoInterface
    {
        return $this->getProvidedDependency(PayoneCreditMemoDependencyProvider::FACADE_CREDIT_MEMO);
    }
}
