<?php

namespace JamCommits\TemplateEngine;

use Exception;

class TemplateEngine {
    private array $blocks = [];
    private string $parentTemplate = '';

    /**
     * Renders a template with optional inheritance and variables
     *
     * @throws Exception
     */
    public function render(string $templatePath, array $data = []): string {
        $templateContent = file_get_contents($templatePath);
        if ($templateContent === false) {
            throw new Exception("Impossible de charger le template : $templatePath");
        }

        $templateContent = $this->checkForExtension($templateContent);

        $this->captureBlocks($templateContent);

        if ($this->parentTemplate) {
            $parentContent = file_get_contents($this->parentTemplate);
            if ($parentContent === false) {
                throw new Exception("Impossible de charger le template parent : $this->parentTemplate");
            }
            $output = $this->mergeParentAndChild($parentContent);
        } else {
            $output = $templateContent;
        }

        return $this->replaceVariables($output, $data);
    }

    private function checkForExtension(string $content): string {
        if (preg_match('/{{\s*extends\s+"([^"]+)"\s*}}/', $content, $matches)) {
            $this->parentTemplate = $matches[1];
            // Retirer la directive d'extension du contenu du template enfant
            return str_replace($matches[0], '', $content);
        }
        return $content;
    }

    private function captureBlocks(string $content): void {
        preg_match_all('/{{\s*block\s+(\w+)\s*}}(.*?){{\s*endblock\s*}}/s', $content, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $this->blocks[$match[1]] = trim($match[2]);
        }
    }

    private function mergeParentAndChild(string $parentContent): string {
        return preg_replace_callback('/{{\s*block\s+(\w+)\s*}}/', function ($matches) {
            $blockName = $matches[1];
            return $this->blocks[$blockName] ?? '';
        }, $parentContent);
    }


    private function replaceVariables(string $content, array $data): string {
        return preg_replace_callback('/{{\s*(\w+)\s*}}/', function ($matches) use ($data) {
            $variableName = $matches[1];
            $value = $data[$variableName] ?? '';
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }, $content);
    }
}
