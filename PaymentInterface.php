<?php namespace Model\Payments;

interface PaymentInterface
{
	public function beginPayment(PaymentsOrderInterface $order, string $type, array $options = []);

	public function handleRequest(): array;

	public function handleFailure(\Throwable $e): array;
}
