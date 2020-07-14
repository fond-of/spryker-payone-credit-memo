<?php

namespace FondOfSpryker\Zed\PayoneCreditMemo\Dependency\Facade;

use Generated\Shared\Transfer\RefundTransfer;
use Orm\Zed\Sales\Persistence\SpySalesOrder;

interface PayoneCreditMemoToRefundInterface
{
    /**
     * Specification:
     * - Calculates refund amount for given OrderTransfer and OrderItems which should be refunded.
     * - Adds refundable amount to RefundTransfer object and returns it.
     * - Uses calculator plugin stack for calculation.
     *
     * @param  \Orm\Zed\Sales\Persistence\SpySalesOrderItem[]  $salesOrderItems
     * @param  \Orm\Zed\Sales\Persistence\SpySalesOrder  $salesOrderEntity
     *
     * @return \Generated\Shared\Transfer\RefundTransfer
     * @api
     *
     */
    public function calculateRefund(
        array $salesOrderItems,
        SpySalesOrder $salesOrderEntity
    ): RefundTransfer;
}
