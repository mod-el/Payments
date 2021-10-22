<?php namespace Model\Payments;

use Model\Core\Module;

class Payments extends Module
{
	/**
	 * @param PaymentsOrderInterface $order
	 * @param string $type
	 * @param array $options
	 * @return mixed
	 */
	public function beginPayment(PaymentsOrderInterface $order, string $type, array $options = [])
	{
		$gatewayName = $order->getGateway();
		if ($gatewayName === null)
			return null;

		$gateway = $this->model->getModule($gatewayName);
		if (!($gateway instanceof PaymentInterface))
			throw new \Exception('Provided gateway does not exist', 400);

		return $gateway->beginPayment($order, $type, $options);
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

		$order = $this->getElementFromId($orderId);

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
	 * @return bool
	 */
	public function checkOrderStatus(int $orderId): bool
	{
		$order = $this->getElementFromId($orderId);

		$gatewayName = $order->getGateway();
		if ($gatewayName === null)
			return false;

		$gateway = $this->model->getModule($gatewayName);
		if (!($gateway instanceof PaymentInterface))
			throw new \Exception('Provided gateway does not exist', 400);

		return $gateway->checkOrderStatus($order);
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

	/**
	 * @param int $id
	 * @return PaymentsOrderInterface
	 */
	public function getElementFromId(int $id): PaymentsOrderInterface
	{
		$config = $this->retrieveConfig();
		return $this->model->_ORM->one($config['order-element'], $id);
	}
}
