<?php

namespace FondOfSpryker\Zed\PayoneCreditMemo;

use FondOfSpryker\Zed\PayoneCreditMemo\Dependency\Facade\PayoneCreditMemoToCreditMemoBridge;
use FondOfSpryker\Zed\PayoneCreditMemo\Dependency\Facade\PayoneCreditMemoToRefundBridge;
use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;

class PayoneCreditMemoDependencyProvider extends AbstractBundleDependencyProvider
{
    public const FACADE_CREDIT_MEMO = 'FACADE_CREDIT_MEMO';
    public const FACADE_REFUND = 'FACADE_REFUND';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideBusinessLayerDependencies(Container $container)
    {
        $container = parent::provideBusinessLayerDependencies($container);
        $container = $this->addRefundFacade($container);
        $container = $this->addCreditMemoFacade($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideCommunicationLayerDependencies(Container $container)
    {
        $container = parent::provideCommunicationLayerDependencies($container);
        $container = $this->addCreditMemoFacade($container);
        $container = $this->addRefundFacade($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addCreditMemoFacade(Container $container)
    {
        $container->set(static::FACADE_CREDIT_MEMO, function (Container $container) {
            return new PayoneCreditMemoToCreditMemoBridge($container->getLocator()->creditMemo()->facade());
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addRefundFacade(Container $container)
    {
        $container->set(static::FACADE_REFUND, function (Container $container) {
            return new PayoneCreditMemoToRefundBridge($container->getLocator()->refund()->facade());
        });

        return $container;
    }
}
