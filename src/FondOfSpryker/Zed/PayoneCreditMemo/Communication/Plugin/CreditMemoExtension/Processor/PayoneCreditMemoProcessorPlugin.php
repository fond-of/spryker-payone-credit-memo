<?php

namespace FondOfSpryker\Zed\PayoneCreditMemo\Communication\Plugin\CreditMemoExtension\Processor;

use Exception;
use FondOfSpryker\Zed\CreditMemoExtension\Dependency\Plugin\CreditMemoProcessorPluginInterface;
use FondOfSpryker\Zed\PayoneCreditMemo\Exception\NoRefundableItemsFoundException;
use Generated\Shared\Transfer\CreditMemoProcessorStatusTransfer;
use Generated\Shared\Transfer\CreditMemoTransfer;
use Propel\Runtime\Collection\ObjectCollection;
use Spryker\Shared\Log\LoggerTrait;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @method \FondOfSpryker\Zed\PayoneCreditMemo\Business\PayoneCreditMemoFacade getFacade()
 * @method \FondOfSpryker\Zed\PayoneCreditMemo\PayoneCreditMemoConfig getConfig()
 * @method \FondOfSpryker\Zed\PayoneCreditMemo\Communication\PayoneCreditMemoCommunicationFactory getFactory()
 */
class PayoneCreditMemoProcessorPlugin extends AbstractPlugin implements CreditMemoProcessorPluginInterface
{
    use LoggerTrait;

    public const NAME = 'PayoneCreditMemoProcessorPlugin';
    public const LISTENING_PAYMENT_PROVIDER = 'Payone';
    public const LISTENING_PAYMENT_METHOD = [
        'payment.payone.creditcard',
        'payment.payone.e_wallet',
    ];

    /**
     * @param \Generated\Shared\Transfer\CreditMemoTransfer $creditMemoTransfer
     * @param \Generated\Shared\Transfer\CreditMemoProcessorStatusTransfer $statusResponse
     *
     * @return \Generated\Shared\Transfer\CreditMemoProcessorStatusTransfer|null
     */
    public function process(
        CreditMemoTransfer $creditMemoTransfer,
        CreditMemoProcessorStatusTransfer $statusResponse
    ): ?CreditMemoProcessorStatusTransfer {
        if ($this->canProcess($creditMemoTransfer) === true) {
            try {
                $items = $this->resolveItemsToRefund($creditMemoTransfer);
                $status = $this->getFactory()->getOmsFacade()->triggerEvent(
                    CreditMemoProcessorPluginInterface::EVENT_NAME,
                    $items,
                    []
                );
                $statusResponse->setMessage('internal oms failure');

                if ($status !== null) {
                    $statusResponse->setMessage('');
                    $statusResponse->setSuccess(true);
                }
            } catch (Exception $exception) {
                $statusResponse->setMessage($exception->getMessage());
                $statusResponse->setSuccess(false);
                $this->getLogger()->error($exception->getMessage(), $exception->getTrace());
            }
        }

        return $statusResponse;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return static::NAME;
    }

    /**
     * @param \Generated\Shared\Transfer\CreditMemoTransfer $creditMemoTransfer
     *
     * @return bool
     */
    public function canProcess(CreditMemoTransfer $creditMemoTransfer): bool
    {
        $salesPaymentMethodType = $creditMemoTransfer->getSalesPaymentMethodType();

        return $salesPaymentMethodType !== null
            && $salesPaymentMethodType->getPaymentProvider() === static::LISTENING_PAYMENT_PROVIDER
            && in_array($salesPaymentMethodType->getPaymentMethod(), static::LISTENING_PAYMENT_METHOD);
    }

    /**
     * @param \Generated\Shared\Transfer\CreditMemoTransfer $creditMemoTransfer
     *
     * @throws \FondOfSpryker\Zed\PayoneCreditMemo\Exception\NoRefundableItemsFoundException
     *
     * @return \Propel\Runtime\Collection\ObjectCollection
     */
    protected function resolveItemsToRefund(CreditMemoTransfer $creditMemoTransfer): ObjectCollection
    {
        $items = $this->getFactory()->getCreditMemoFacade()->getSalesOrderItemsByCreditMemo($creditMemoTransfer);

        if ($items === null) {
            throw new NoRefundableItemsFoundException(sprintf(
                'No refundable items found for CreditMemo with id %s and order reference %s',
                $creditMemoTransfer->getIdCreditMemo(),
                $creditMemoTransfer->getOrderReference()
            ));
        }

        return $items;
    }
}
