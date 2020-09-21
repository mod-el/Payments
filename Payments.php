<?php namespace Model\Payments;

use Model\Core\Module;

class Payments extends Module
{
	/**
	 * @param PaymentsOrderInterface $order
	 * @param array $options
	 */
	public function beginPayment(PaymentsOrderInterface $order, array $options = [])
	{
		$gateway = $order->getGateway();
		if ($gateway === null)
			return;

		$this->model->getModule($gateway)->beginPayment($order, $options);
	}

	/**
	 * @param string $gateway
	 * @param int $orderId
	 * @param float $price
	 * @param array $gatewayMeta
	 * @return mixed
	 * @throws \Exception
	 */
	public function payOrder(string $gateway, int $orderId, float $price, array $gatewayMeta)
	{
		$config = $this->retrieveConfig();

		/** @var PaymentsOrderInterface $order */
		$order = $this->model->_ORM->one($config['order-element'], $orderId);

		if ($order->getGateway() !== $gateway)
			throw new \Exception('Payment gateway mismatch');

		$orderPrice = $order->getPrice();
		if (round($orderPrice, 2) !== round($price, 2))
			throw new \Exception('Price mismatch');

		if ($order->isPaid()) {
			return $config['response-if-already-paid']($gateway, $order, $gatewayMeta);
		} else {
			$order->markAsPaid();
			return $config['response-when-paid']($gateway, $order, $gatewayMeta);
		}
	}

	/**
	 * @param array $request
	 * @param string $rule
	 * @return array|null
	 */
	public function getController(array $request, string $rule): ?array
	{
		return $rule === 'payments' ? [
			'controller' => 'Payments',
		] : null;
	}
}
