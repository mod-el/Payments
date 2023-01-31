<?php namespace Model\Payments\Controllers;

use Model\Core\Controller;
use Model\Db\Db;
use Model\Payments\PaymentException;
use Model\Payments\PaymentInterface;

class PaymentsController extends Controller
{
	public function index()
	{
		try {
			$request = $this->model->getRequest();

			if (count($request) < 3)
				throw new \Exception('Wrong number of parameters');

			$config = $this->model->_Payments->retrieveConfig();

			switch ($request[1]) {
				case 'pay':
					if (!isset($request[2]) or !is_numeric($request[2]))
						throw new \Exception('Wrong id parameter');

					if (!isset($request[3]) or !in_array($request[3], ['client', 'server']))
						throw new \Exception('Wrong type parameter');

					$order = $this->model->one($config['order-element'], $request[2]);
					$response = $this->model->_Payments->beginPayment($order, $request[3], $_POST);

					if ($request[3] === 'client')
						return $response;
					else
						die();

				case 'notify':
					$supposedGateway = $request[2];
					if ($supposedGateway and !$this->model->moduleExists($supposedGateway))
						throw new \Exception('Gateway not found');

					$gateway = $this->model->getModule($supposedGateway);
					if (!($gateway instanceof PaymentInterface))
						throw new \Exception('Bad payment gateway');

					$db = Db::getConnection();

					try {
						$db->beginTransaction();

						\Model\Settings\Settings::set('payments-dummy', time() . '-' . uniqid());

						$confirmData = $gateway->handleRequest();

						if ($confirmData['dummy'] ?? false) {
							$response = $config['response-if-already-paid']($supposedGateway, null, []);
						} else {
							$confirmResponse = $this->model->_Payments->payOrder($supposedGateway, $confirmData['id'], $confirmData['price'], $confirmData['meta'] ?? []);
							$response = $confirmData['response'] ?? $confirmResponse; // Se il metodo di pagamento vuole rispondere in un dato modo, ha la priorità
						}

						$db->commit();
					} catch (\Throwable $e) {
						$db->rollback();

						if (get_class($e) !== 'Model\\Payments\\PaymentException')
							$e = new PaymentException($e->getMessage(), $e->getCode(), $e);

						$e->gateway = $supposedGateway;

						if (isset($config['response-on-failure']))
							$response = $config['response-on-failure']($e);
						else
							throw $e;
					}

					switch ($response['type'] ?? null) {
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
						default:
							die();
					}
					break;
			}
		} catch (\Exception $e) {
			http_response_code(500);
			echo getErr($e);
			die();
		}
	}
}
