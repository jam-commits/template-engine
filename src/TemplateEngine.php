<?php

namespace JamCommits\TemplateEngine;

use Exception;

class TemplateEngine {
    /**
     * @throws Exception
     */
    public function render(string $templatePath, array $data = []): string {
        $templateContent = file_get_contents($templatePath);

        if ($templateContent === false) {
            throw new Exception("Impossible de charger le template : $templatePath");
        }

        return preg_replace_callback('/{{\s*(\w+)\s*}}/', function ($matches) use ($data) {
            $variableName = $matches[1];
            $value = $data[$variableName] ?? '';

            // Prevent XSS
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }, $templateContent);
    }
}
