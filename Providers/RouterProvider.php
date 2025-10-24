<?php namespace Model\Payments\Providers;

use Model\Router\AbstractRouterProvider;

class RouterProvider extends AbstractRouterProvider
{
	public static function getRoutes(): array
	{
		return [
			[
				'pattern' => 'payments',
				'controller' => 'Payments',
			],
		];
	}
}
