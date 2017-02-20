<?php

namespace Examples\Gopay2x;

use Markette\Gopay\Service;
use Nette\Application\UI\Presenter;

final class GopayPresenter extends Presenter
{

    /** @var Service @inject */
    public $gopay;

    /** @var ShopModel @inject */
    public $model;

    /**
     * Creates and send payment request to GoPay
     *
     * @param int $id
     * @param string $channel
     */
    public function actionPay($id, $channel)
    {
        $gopay = $this->gopay;

        // setup success and failure callbacks
        $gopay->successUrl = $this->link('//success', ['orderId' => $id]);
        $gopay->failureUrl = $this->link('//failure', ['orderId' => $id]);

        // your custom communication with model
        $order = $this->model->findOrderById($id);

        // prepare data about customer)
        $customer = [
            'firstName' => $order->name,
            'email' => $order->email,
        ];

        // creation of payment
        $payment = $gopay->createPayment([
            'sum' => $order->getPrice(),
            'variable' => $order->varSymbol,
            'specific' => $order->specSymbol,
            'productName' => $order->product,
            'customer' => $customer,
        ]);

        // to be able to connect our internal Order with Gopay Payment,
        // we have to store its generated ID (which will be created during
        // 'pay' method call - this callback will be provided in next step
        $storePaymentId = function ($paymentId) use ($order) {
            $order->storePaymentId($paymentId);
        };

        // here we communicate with Gopay Web Service (via soap)
        $toPayResponse = $gopay->pay($payment, $channel, $storePaymentId);

        // redirect to Gopay Payment Gate
        $this->sendResponse($toPayResponse);
    }

    /**
     * Creates and send payment request to GoPay
     *
     * @param int $id
     * @param string $channel
     */
    public function actionPayInline($id, $channel)
    {
        // same as above

        // here we communicate with Gopay Web Service (via soap)
        $response = $gopay->payInline($payment, $channel, $storePaymentId);

        // redirect to Gopay Payment Gate
        $this->sendJson(['url' => $response['url'], 'signature' => $response['signature']]);
    }

    /* === Called from Gopay Payment Gate ======================================= */

    /**
     * Handles response from Gopay Payment Gate
     *
     * @param int $orderId
     * @param string $paymentSessionId
     * @param string $targetGoId
     * @param int $orderNumber
     * @param string $encryptedSignature
     */
    public function actionSuccess($orderId, $paymentSessionId, $targetGoId, $orderNumber, $encryptedSignature)
    {
        $gopay = $this->gopay;

        // your custom communication with model
        $order = $this->model->findOrderByPaymentId($paymentSessionId);

        // restores Payment object (as instance of ReturnedPayment)
        $payment = $gopay->restorePayment([
            'sum' => $order->price,
            'variable' => $order->varSymbol,
            'specific' => $order->specSymbol,
            'productName' => $order->product,
        ], [
            'paymentSessionId' => $paymentSessionId,
            'targetGoId' => $targetGoId,
            'orderNumber' => $orderNumber,
            'encryptedSignature' => $encryptedSignature,
        ]);

        // firstly we check if request is not falsified
        if ($payment->isFraud()) {
            $this->redirect('fraud');
        }

        // secondly we check if request really means the Payment is paid
        if ($payment->isPaid()) {
            // store information in our model - SUCCESSFUL END!!!
            $order->markAsPaid();
        } else {
            $this->redirect('failure');
        }

        echo 'Paid!';
    }

    /**
     * Handles fail response from Gopay Payment Gate
     *
     * @param int $orderId
     * @param string $paymentSessionId
     * @param string $targetGoId
     * @param int $orderNumber
     * @param string $encryptedSignature
     */
    public function actionFailure($orderId, $paymentSessionId, $targetGoId, $orderNumber, $encryptedSignature)
    {
        echo 'Payment failed';
    }

    /**
     * Handles automatic response from Gopay Payment Gate
     *
     * @param int $orderId
     * @param string $paymentSessionId
     * @param string $targetGoId
     * @param int $orderNumber
     * @param string $encryptedSignature
     */
    public function actionNotify($orderId, $paymentSessionId, $targetGoId, $orderNumber, $encryptedSignature)
    {
        echo 'Received notify from GoPay';
    }

    /* === Views ================================================================ */

    /**
     * View for fraud
     */
    public function actionFraud()
    {
        echo 'Not paid! Attempt of fraud!';
    }

}
