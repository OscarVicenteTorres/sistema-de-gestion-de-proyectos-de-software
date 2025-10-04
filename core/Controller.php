<?php
class Controller {
    // Clase base para controladores
    public function render($view, $data = []) {
        extract($data);
        require __DIR__ . '/../app/views/' . $view . '.php';
    }
}
