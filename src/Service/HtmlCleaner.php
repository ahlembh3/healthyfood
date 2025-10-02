<?php
declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

final class HtmlCleaner
{
    public function __construct(
        // On injecte le sanitizer configuré "article.content"
        #[Autowire(service: 'html_sanitizer.sanitizer.article.content')]
        private HtmlSanitizerInterface $sanitizer
    ) {}

    public function clean(string $html): string
    {
        return $this->sanitizer->sanitize($html);
    }
}
