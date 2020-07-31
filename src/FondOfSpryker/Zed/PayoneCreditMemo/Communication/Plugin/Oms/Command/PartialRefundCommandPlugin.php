<?php

namespace FondOfSpryker\Zed\PayoneCreditMemo\Communication\Plugin\Oms\Command;

use FondOfSpryker\Shared\CreditMemo\CreditMemoConstants;
use FondOfSpryker\Shared\CreditMemo\CreditMemoRefundHelperTrait;
use Generated\Shared\Transfer\CreditMemoTransfer;
use Generated\Shared\Transfer\PayonePartialOperationRequestTransfer;
use Generated\Shared\Transfer\RefundResponseTransfer;
use Generated\Shared\Transfer\RefundTransfer;
use Orm\Zed\Sales\Persistence\SpySalesOrder;
use Spryker\Zed\Oms\Business\Util\ReadOnlyArrayObject;
use SprykerEco\Shared\Payone\PayoneTransactionStatusConstants;
use SprykerEco\Zed\Payone\Communication\Plugin\Oms\Command\PartialRefundCommandPlugin as SprykerEcoPartialRefundCommandPlugin;

/**
 * @method \FondOfSpryker\Zed\PayoneCreditMemo\Communication\PayoneCreditMemoCommunicationFactory getFactory()
 * @method \FondOfSpryker\Zed\PayoneCreditMemo\Business\PayoneCreditMemoFacadeInterface getFacade()
 */
class PartialRefundCommandPlugin extends SprykerEcoPartialRefundCommandPlugin
{
    use CreditMemoRefundHelperTrait;

    /**
     * @api
     *
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrderItem[] $orderItems
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrder $orderEntity
     * @param \Spryker\Zed\Oms\Business\Util\ReadOnlyArrayObject $data
     *
     * @return array
     */
    public function run(array $orderItems, SpySalesOrder $orderEntity, ReadOnlyArrayObject $data)
    {
        $creditMemos = $this->getFactory()->getCreditMemoFacade()->getCreditMemosBySalesOrderItems($orderItems);
        $creditMemos = $this->resolveAndPrepareCreditMemos($creditMemos);

        $refundItems = [];
        foreach ($creditMemos as $creditMemoEntity) {
            $refundItems = array_merge(
                $refundItems,
                $this->getRefundableItemsByCreditMemo($creditMemoEntity, $orderItems)
            );
        }

        $results = [];
        foreach ($creditMemos as $creditMemoReference => $creditMemoEntity) {
            $creditMemoUpdateTransfer = new CreditMemoTransfer();
            $creditMemoUpdateTransfer->setInProgress(false);
            $results[$creditMemoReference] = $creditMemoUpdateTransfer->getInProgress();
            if (array_key_exists($creditMemoReference, $refundItems)) {
                $itemsToRefund = $this->resolveAndCheckItemsForRefund($refundItems[$creditMemoReference]);
                $refundTransfer = $this->getFactory()->getRefundFacade()->calculateRefund($itemsToRefund, $orderEntity);

                if ($this->isRefundableAmount($refundTransfer)) {
                    $orderTransfer = $this->getFactory()->getSalesFacade()->getOrderByIdSalesOrder($orderEntity->getIdSalesOrder());

                    $payonePartialOperationTransfer = (new PayonePartialOperationRequestTransfer())
                        ->setOrder($orderTransfer)
                        ->setRefund($refundTransfer);

//                    foreach ($orderItems as $orderItem) {
//                        $payonePartialOperationTransfer->addSalesOrderItemId($orderItem->getIdSalesOrderItem());
//                    }

                    $response = $this->getFactory()->getPayoneFacade()->executePartialRefund($payonePartialOperationTransfer);
                    $results[$creditMemoReference] = $response;
                    $creditMemoUpdateTransfer->setProcessed(true);
                    $creditMemoUpdateTransfer->setProcessedAt(time());
                    $creditMemoUpdateTransfer->setState($this->getState($response));
                    $creditMemoUpdateTransfer->setWasRefundSuccessful($this->wasSuccessfullyRefunded($response));
                    $creditMemoUpdateTransfer->setRefundedAmount($this->getRefundedAmount($response, $refundTransfer));
                    $creditMemoUpdateTransfer->setRefundedTaxAmount($this->getRefundedTaxAmount($response, $refundTransfer));
                    $creditMemoUpdateTransfer->setTransactionId($response->getTxid());
                    $this->handleErrorStuff($creditMemoUpdateTransfer, $response);
                }
            }

            $this->updateCreditMemo($creditMemoEntity, $creditMemoUpdateTransfer);
        }

        return [];
    }

    /**
     * @param \Generated\Shared\Transfer\RefundResponseTransfer $refundResponseTransfer
     *
     * @return string
     */
    protected function getState(RefundResponseTransfer $refundResponseTransfer): string
    {
        $status = $refundResponseTransfer->getBaseResponse()->getStatus();
        if ($status === PayoneTransactionStatusConstants::STATUS_REFUND_APPROVED) {
            return CreditMemoConstants::STATE_COMPLETE;
        }

        return CreditMemoConstants::STATE_ERROR;
    }

    /**
     * @param \Generated\Shared\Transfer\RefundResponseTransfer $refundResponseTransfer
     *
     * @return bool
     */
    protected function wasSuccessfullyRefunded(RefundResponseTransfer $refundResponseTransfer): bool
    {
        $state = $this->getState($refundResponseTransfer);
        if ($state === CreditMemoConstants::STATE_COMPLETE) {
            return true;
        }

        return false;
    }

    /**
     * @param \Generated\Shared\Transfer\RefundResponseTransfer $refundResponseTransfer
     * @param \Generated\Shared\Transfer\RefundTransfer $refundTransfer
     *
     * @return int
     */
    protected function getRefundedAmount(
        RefundResponseTransfer $refundResponseTransfer,
        RefundTransfer $refundTransfer
    ): int {
        if ($this->wasSuccessfullyRefunded($refundResponseTransfer)) {
            return $refundTransfer->getAmount();
        }

        return 0;
    }

    /**
     * @param \Generated\Shared\Transfer\RefundResponseTransfer $refundResponseTransfer
     * @param \Generated\Shared\Transfer\RefundTransfer $refundTransfer
     *
     * @return int
     */
    protected function getRefundedTaxAmount(
        RefundResponseTransfer $refundResponseTransfer,
        RefundTransfer $refundTransfer
    ): int {
        if ($this->wasSuccessfullyRefunded($refundResponseTransfer)) {
            return 0; //ToDo get or calculate tax
        }

        return 0;
    }

    /**
     * @param \Generated\Shared\Transfer\CreditMemoTransfer $creditMemoUpdateTransfer
     * @param \Generated\Shared\Transfer\RefundResponseTransfer $response
     *
     * @return void
     */
    protected function handleErrorStuff(
        CreditMemoTransfer $creditMemoUpdateTransfer,
        RefundResponseTransfer $response
    ): void {
        if ($this->wasSuccessfullyRefunded($response) === false) {
            $creditMemoUpdateTransfer->setErrorCode($response->getBaseResponse()->getErrorCode());
            $creditMemoUpdateTransfer->setErrorMessage($response->getBaseResponse()->getErrorMessage());
        }
    }
}
