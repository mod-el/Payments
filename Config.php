<?php namespace Model\Payments;

use Model\Core\Module_Config;

class Config extends Module_Config
{
	/**
	 */
	protected function assetsList(): void
	{
		$this->addAsset('config', 'config.php', function () {
			return '<?php
$config = [
	\'order-element\' => \'Order\',
	\'response-when-paid\' => function (string $gateway, \\Model\\Payments\\PaymentsOrderInterface $order, array $gatewayMeta) {
		return [
			\'type\' => \'template\',
			\'template\' => \'confirm\',
		];
	},
	\'response-if-already-paid\' => function (string $gateway, ?\\Model\\Payments\\PaymentsOrderInterface $order, array $gatewayMeta) {
		return [
			\'type\' => \'redirect\',
			\'url\' => PATH,
		];
	},
	\'response-on-failure\' => null,
];
';
		});
	}

	/**
	 * @return array
	 */
	public function getRules(): array
	{
		return [
			'rules' => [
				'payments' => 'payments',
			],
			'controllers' => [
				'Payments',
			],
		];
	}

	public function getConfigData(): ?array
	{
		return [];
	}
}
