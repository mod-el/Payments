<?php namespace Model\Payments;

interface PaymentsOrderInterface
{
	public function getGateway(): ?string;

	public function getPrice(): float;

	public function getShipping(): float;

	public function getOrderDescription(): string;

	public function isPaid(): bool;

	public function markAsPaid();
}
