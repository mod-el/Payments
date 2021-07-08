<?php namespace Model\Payments;

class PaymentException extends \Exception
{
	public ?string $gateway = null;
	public ?int $orderId = null;
	public array $data = [];
}
