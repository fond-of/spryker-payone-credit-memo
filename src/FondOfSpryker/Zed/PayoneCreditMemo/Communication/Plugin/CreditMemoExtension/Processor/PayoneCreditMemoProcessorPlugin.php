<?php

namespace FondOfSpryker\Zed\PayoneCreditMemo\Communication\Plugin\CreditMemoExtension\Processor;

use Exception;
use FondOfSpryker\Zed\CreditMemoExtension\Dependency\Plugin\CreditMemoProcessorPluginInterface;
use FondOfSpryker\Zed\PayoneCreditMemo\Exception\NoRefundableItemsFoundException;
use Generated\Shared\Transfer\CreditMemoProcessorStatusTransfer;
use Generated\Shared\Transfer\CreditMemoTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @method \FondOfSpryker\Zed\PayoneCreditMemo\Business\PayoneCreditMemoFacade getFacade()
 * @method \FondOfSpryker\Zed\PayoneCreditMemo\PayoneCreditMemoConfig getConfig()
 * @method \FondOfSpryker\Zed\PayoneCreditMemo\Communication\PayoneCreditMemoCommunicationFactory getFactory()
 */
class PayoneCreditMemoProcessorPlugin extends AbstractPlugin implements CreditMemoProcessorPluginInterface
{
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
     * @throws \FondOfSpryker\Zed\PayoneCreditMemo\Exception\NoRefundableItemsFoundException
     *
     * @return \Generated\Shared\Transfer\CreditMemoProcessorStatusTransfer|null
     */
    public function process(
        CreditMemoTransfer $creditMemoTransfer,
        CreditMemoProcessorStatusTransfer $statusResponse
    ): ?CreditMemoProcessorStatusTransfer {
        $statusResponse->setWasRefunded(false);
        if ($this->canProcess($creditMemoTransfer) === true) {
            try {
                $items = $this->getFactory()->getCreditMemoFacade()->getSalesOrderItemsByCreditMemo($creditMemoTransfer);
                if ($items === null) {
                    throw new NoRefundableItemsFoundException(sprintf(
                        'No refundable items found for CreditMemo with id %s and order reference %s',
                        $creditMemoTransfer->getIdCreditMemo(),
                        $creditMemoTransfer->getOrderReference()
                    ));
                }
                $this->getFactory()->getOmsFacade()->triggerEvent(
                    CreditMemoProcessorPluginInterface::EVENT_NAME,
                    $items,
                    []
                );
            } catch (Exception $exception) {
                $statusResponse->setMessage($exception->getMessage());
                $statusResponse->setSuccess(false);
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
}
