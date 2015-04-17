<?php

use Markette\Gopay;
use Nette\Application\UI\Presenter;

final class GopayPresenter extends Presenter
{

	/** @var Gopay\Service @inject */
	public $gopay;


	/** @var App\ShopModel @inject */
	public $model;


	/**
	 * Creates and send payment request to GoPay
	 *
	 * @param  int
	 * @param  string
	 */
	public function actionPay($id, $channel)
	{
		$gopay = $this->gopay;

		// setup success and failure callbacks
		$gopay->successUrl = $this->link('//success', array('orderId' => $id));
		$gopay->failureUrl = $this->link('//failure', array('orderId' => $id));

		// your custom communication with model
		$order = $this->model->findOrderById($id);

		// prepare data about customer)
		$customer = array(
			'firstName'   => $order->name,
			'email'       => $order->email,
		);

		// creation of payment
		$payment = $gopay->createPayment(array(
			'sum'         => $order->getPrice(),
			'variable'    => $order->varSymbol,
			'specific'    => $order->specSymbol,
			'productName' => $order->product,
			'customer'    => $customer,
		));

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


/* === Called from Gopay Payment Gate ======================================= */


	/**
	 * Handles response from Gopay Payment Gate
	 *
	 * @param  int
	 * @param  string
	 * @param  string
	 * @param  int
	 * @param  string
	 */
	public function actionSuccess($orderId, $paymentSessionId, $targetGoId, $orderNumber, $encryptedSignature)
	{
		$gopay = $this->gopay;

		// your custom communication with model
		$order = $this->model->findOrderByPaymentId($paymentSessionId);

		// restores Payment object (as instance of ReturnedPayment)
		$payment = $gopay->restorePayment(array(
			'sum'         => $order->price,
			'variable'    => $order->varSymbol,
			'specific'    => $order->specSymbol,
			'productName' => $order->product,
		), array(
			'paymentSessionId'   => $paymentSessionId,
			'targetGoId'         => $targetGoId,
			'orderNumber'        => $orderNumber,
			'encryptedSignature' => $encryptedSignature,
		));

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
	 * @param  int
	 * @param  string
	 * @param  string
	 * @param  int
	 * @param  string
	 */
	public function actionFailure($orderId, $paymentSessionId, $targetGoId, $orderNumber, $encryptedSignature)
	{
		echo 'Payment failed';
	}



	/**
	 * Handles automatic response from Gopay Payment Gate
	 *
	 * @param  int
	 * @param  string
	 * @param  string
	 * @param  int
	 * @param  string
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
