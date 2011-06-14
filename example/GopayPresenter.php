<?php

final class PaymentPresenter extends Nette\Application\UI\Presenter
{

	/**
	 * Creates and send payment request to GoPay
	 * 
	 * @param  int $id
	 * @param  string $channel
	 */
	public function actionPay($id, $channel)
	{
		// get Gopay service
		$gopay = $this->context->gopay;

		// setup success and failure callbacks
		$gopay->success = $this->link('//success', $id);
		$gopay->failure = $this->link('//failure', $id);

		// your custom communication with model
		$shop = $this->context->shopModel;
		$order = $shop->findOrderById($id);

		// prepare data about customer)
		$customer = array(
			'firstName'   => $order->name,
			'email'       => $order->email,
			'countryCode' => 'CZE',
		);

		// creation of payment
		$payment = $gopay->createPayment(array(
			'sum'      => $order->getPrice(),
			'variable' => $order->varSymbol,
			'specific' => $order->specSymbol,
			'product'  => $order->product,
			'customer' => $customer,
		));

		// here we communicate with Gopay Web Service (via soap)
		$toPayResponse = $gopay->pay($payment, $channel);

		// to be able to connect our internal Order with Gopay Payment,
		// we have to store its generated ID (which was created during
		// calling 'pay' method
		$order->storePaymentId($payment->id);

		// redirect to Gopay Payment Gate
		$this->sendResponse($toPayResponse);
	}

/* === Called from Gopay Payment Gate ======================================= */
	
	/**
	 * Handles response from Gopay Payment Gate
	 *
	 * @param  string $paymentSessionId
	 * @param  string $eshopGoId
	 * @param  int $variableSymbol
	 * @param  string $encryptedSignature
	 */
	public function actionSuccess($paymentSessionId, $eshopGoId, $variableSymbol, $encryptedSignature)
	{
		// get Gopay service
		$gopay = $this->context->gopay;

		// your custom communication with model
		$shop = $this->context->shopModel;
		$order = $shop->findOrderByPaymentId($paymentSessionId);

		// restores Payment object
		$payment = $gopay->restorePayment(array(
			'sum'      => $order->price,
			'variable' => $order->varSymbol,
			'specific' => $order->specSymbol,
			'product'  => $order->product,
		), array(
			'paymentSessionId'   => $paymentSessionId,
			'eshopGoId'          => $eshopGoId,
			'variableSymbol'     => $variableSymbol,
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
	
	public function actionFailure()
	{
		echo 'Not paid!';
	}

	public function actionFraud()
	{
		echo 'Not paid! Attempt of fraud!';
	}


}