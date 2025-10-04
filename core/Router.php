<?php
class Router {
	public function run() {
		// Lógica básica de enrutamiento
		$controller = $_GET['c'] ?? 'Auth';
		$action = $_GET['a'] ?? 'login';
		$controllerName = ucfirst($controller) . 'Controller';
		$controllerFile = __DIR__ . '/../app/controllers/' . $controllerName . '.php';
		if (file_exists($controllerFile)) {
			require_once $controllerFile;
			if (class_exists($controllerName)) {
				$ctrl = new $controllerName();
				if (method_exists($ctrl, $action)) {
					$ctrl->$action();
					return;
				}
			}
		}
		echo 'Página no encontrada';
	}
}
