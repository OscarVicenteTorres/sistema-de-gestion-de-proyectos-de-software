<?php
require_once __DIR__ . '/../../core/Controller.php';
class TareaController extends Controller {
    public function index() {
        $this->render('admin/tareas');
    }
}
