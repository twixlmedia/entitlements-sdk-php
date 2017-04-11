<?php

class TWXApplication {

    protected function init() {
    }

    protected function beforeAction($action) {
    }

    protected function afterAction($action) {
    }

    protected function actionIndex() {
        echo('index action');
    }

    protected function render($name, $params=[], $layout=null) {

        $params['twxApp'] = $this;

        $template = new TWXTemplate($name, $params);
        $rendered = $template->render();

        if (!empty($layout)) {
            $params['twxContentForLayout'] = $rendered;
            $layoutTemplate = new TWXTemplate($layout, $params);
            $rendered = $layoutTemplate->render();
        }

        return $rendered;

    }

    protected function output($name, $params=[], $layout='layout') {
        $rendered = $this->render($name, $params, $layout);
        echo($rendered);
    }

    protected function outputToken($token) {
        $this->outputJson(['token' => $token]);
    }

    protected function outputJson($object) {
        header('Content-Type: application/json');
        echo(json_encode($object));
    }

    protected function outputJsonError($error) {
        $this->statusCode(500);
        $this->outputJson(['error' => $error]);
        die();
    }

    protected function statusCode($code=200, $message=null) {
        if ($code == 500 && empty($message)) {
            $message = 'Internal Server Error';
        }
        if ($code != 200) {
            header('HTTP/1.1 ' . $code . ' ' . $message);
        }
    }

    public function getActionName() {
        $action = TWXParam::get('do', 'index');
        $action = TWXStringUtils::camelize($action);
        return $action;
    }

    private function getActionFunctionName() {
        $action = $this->getActionName();
        $function = 'action' . ucfirst($action);
        return $function;
    }

    private function dispatchAction() {

        $this->init();

        $action = $this->getActionName();
        $function = $this->getActionFunctionName();

        if (!method_exists($this, $function)) {
            throw new Exception('Invalid action: ' . $action);
        }

        $this->beforeAction($action);
        call_user_func_array([$this, $function], []);
        $this->afterAction($action);

    }

    public function execute() {
        try {
            $this->dispatchAction();
        } catch (Exception $e) {
            $this->statusCode(500);
            die($e->getMessage());
        }
    }

}
