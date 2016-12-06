<?php

final class TWXTemplate {

    private $name;
    private $params;

    public function __construct($name, $params=[]) {
        $this->name = $name;
        $this->params = $params;
    }

    public function render() {

        $templatePath = $this->pathForTemplate($this->name);

        $errorReporting = error_reporting();

        error_reporting(error_reporting() & ~E_NOTICE);

        ob_start();
        extract($this->params);
        require($templatePath);
        $rendered = ob_get_contents();
        ob_end_clean();

        error_reporting($errorReporting);

        return $rendered;

    }

    private function templatesPath() {
        $templatesPath = dirname(__DIR__) . '/templates';
        if (!file_exists($templatesPath)) {
            throw new Exception('Templates Path not found: ' . $templatesPath);
        }
        return realpath($templatesPath);
    }

    private function pathForTemplate($name) {
        $templatePath = self::templatesPath() . '/' . $name . '.php';
        if (!file_exists($templatePath)) {
            throw new Exception('Template not found: ' . $templatePath);
        }
        return realpath($templatePath);
    }


}
