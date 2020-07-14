<?php

namespace FondOfSpryker\Zed\PayoneCreditMemo\Dependency\Facade;

use FondOfSpryker\Zed\CreditMemo\Business\CreditMemoFacadeInterface;
use Generated\Shared\Transfer\CreditMemoResponseTransfer;
use Generated\Shared\Transfer\CreditMemoTransfer;
use Orm\Zed\Sales\Persistence\SpySalesOrder;

class PayoneCreditMemoToCreditMemoBridge implements PayoneCreditMemoToCreditMemoInterface
{
    /**
     * @var \FondOfSpryker\Zed\CreditMemo\Business\CreditMemoFacadeInterface
     */
    protected $creditMemoFacade;

    /**
     * @param \FondOfSpryker\Zed\CreditMemo\Business\CreditMemoFacadeInterface $creditMemoFacade
     */
    public function __construct(CreditMemoFacadeInterface $creditMemoFacade)
    {
        $this->creditMemoFacade = $creditMemoFacade;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CreditMemoTransfer $creditMemoTransfer
     *
     * @return \Generated\Shared\Transfer\CreditMemoResponseTransfer
     */
    public function updateCreditMemo(CreditMemoTransfer $creditMemoTransfer): CreditMemoResponseTransfer
    {
        return $this->creditMemoFacade->updateCreditMemo($creditMemoTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\CreditMemoTransfer $creditMemoTransfer
     *
     * @return \Orm\Zed\Sales\Persistence\SpySalesOrder
     */
    public function getSalesOrderByCreditMemo(CreditMemoTransfer $creditMemoTransfer): SpySalesOrder
    {
        return $this->creditMemoFacade->getSalesOrderByCreditMemo($creditMemoTransfer);
    }

    /**
     * @param int $idSalesOrder
     *
     * @return \Orm\Zed\CreditMemo\Persistence\FosCreditMemo[]
     */
    public function getCreditMemoBySalesOrderId(int $idSalesOrder): array
    {
        return $this->creditMemoFacade->getCreditMemoBySalesOrderId($idSalesOrder);
    }

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrderItem[] $salesOrderItems
     *
     * @return \Orm\Zed\CreditMemo\Persistence\FosCreditMemo[]]
     */
    public function getCreditMemosBySalesOrderItems(array $salesOrderItems): array
    {
        return $this->creditMemoFacade->getCreditMemosBySalesOrderItems($salesOrderItems);
    }
}
