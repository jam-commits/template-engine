<?php

namespace JamCommits\TemplateEngine;
class TemplateEngine {
    public function render(string $templatePath, array $data = []): string {
        extract($data);

        ob_start();
        include $templatePath;
        return ob_get_clean();
    }
}