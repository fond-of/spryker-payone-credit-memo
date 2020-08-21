<?php

namespace FondOfSpryker\Zed\PayoneCreditMemo\Communication;

use FondOfSpryker\Zed\PayoneCreditMemo\Dependency\Facade\PayoneCreditMemoToCreditMemoInterface;
use FondOfSpryker\Zed\PayoneCreditMemo\Dependency\Facade\PayoneCreditMemoToOmsInterface;
use FondOfSpryker\Zed\PayoneCreditMemo\Dependency\Facade\PayoneCreditMemoToPayoneInterface;
use FondOfSpryker\Zed\PayoneCreditMemo\Dependency\Facade\PayoneCreditMemoToRefundInterface;
use FondOfSpryker\Zed\PayoneCreditMemo\Dependency\Facade\PayoneCreditMemoToSalesInterface;
use FondOfSpryker\Zed\PayoneCreditMemo\PayoneCreditMemoDependencyProvider;
use Spryker\Zed\Kernel\Communication\AbstractCommunicationFactory;

/**
 * @method \FondOfSpryker\Zed\PayoneCreditMemo\PayoneCreditMemoConfig getConfig()
 * @method \FondOfSpryker\Zed\PayoneCreditMemo\Business\PayoneCreditMemoFacadeInterface getFacade()
 */
class PayoneCreditMemoCommunicationFactory extends AbstractCommunicationFactory
{
    /**
     * @return \FondOfSpryker\Zed\PayoneCreditMemo\Dependency\Facade\PayoneCreditMemoToCreditMemoInterface
     */
    public function getCreditMemoFacade(): PayoneCreditMemoToCreditMemoInterface
    {
        return $this->getProvidedDependency(PayoneCreditMemoDependencyProvider::FACADE_CREDIT_MEMO);
    }

    /**
     * @return \FondOfSpryker\Zed\PayoneCreditMemo\Dependency\Facade\PayoneCreditMemoToRefundInterface
     */
    public function getRefundFacade(): PayoneCreditMemoToRefundInterface
    {
        return $this->getProvidedDependency(PayoneCreditMemoDependencyProvider::FACADE_REFUND);
    }

    /**
     * @return \FondOfSpryker\Zed\PayoneCreditMemo\Dependency\Facade\PayoneCreditMemoToPayoneInterface
     */
    public function getPayoneFacade(): PayoneCreditMemoToPayoneInterface
    {
        return $this->getProvidedDependency(PayoneCreditMemoDependencyProvider::FACADE_PAYONE);
    }

    /**
     * @return \FondOfSpryker\Zed\PayoneCreditMemo\Dependency\Facade\PayoneCreditMemoToSalesInterface
     */
    public function getSalesFacade(): PayoneCreditMemoToSalesInterface
    {
        return $this->getProvidedDependency(PayoneCreditMemoDependencyProvider::FACADE_SALES);
    }

    /**
     * @return \FondOfSpryker\Zed\PayoneCreditMemo\Dependency\Facade\PayoneCreditMemoToOmsInterface
     */
    public function getOmsFacade(): PayoneCreditMemoToOmsInterface
    {
        return $this->getProvidedDependency(PayoneCreditMemoDependencyProvider::FACADE_OMS);
    }
}
