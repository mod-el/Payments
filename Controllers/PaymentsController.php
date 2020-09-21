<?php namespace Model\Payments\Controllers;

use Model\Core\Controller;
use Model\Payments\PaymentInterface;

class PaymentsController extends Controller
{
	public function index()
	{
		try {
			$supposedGateway = $this->model->getRequest(1);
			if ($supposedGateway and !$this->model->moduleExists($supposedGateway))
				throw new \Exception('Gateway not found');

			$gateway = $this->model->getModule($supposedGateway);
			if (!($gateway instanceof PaymentInterface))
				throw new \Exception('Bad payment gateway');

			$confirmData = $gateway->handleRequest();

			$response = $this->model->_Payments->payOrder($supposedGateway, $confirmData['id'], $confirmData['price'], $confirmData['meta'] ?? []);

			switch ($response['type']) {
				case 'text':
					echo $response['text'];
					die();
				case 'json':
					$this->model->sendJSON($response['json']);
					break;
				case 'template':
					foreach (($response['inject'] ?? []) as $var_name => $var_content)
						$this->model->inject($var_name, $var_content);
					$this->model->viewOptions['template'] = $response['template'];
					break;
				case 'redirect':
					$this->model->redirect($response['url']);
					break;
			}
		} catch (\Exception $e) {
			http_response_code(500);
			echo getErr($e);
			die();
		}
	}
}
