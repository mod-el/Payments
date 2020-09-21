<?php namespace Model\Payments;

interface PaymentInterface
{
	public function beginPayment(PaymentsOrderInterface $order, array $options = []);

	public function handleRequest(): array;
}
