<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class AutoloadStructureTest extends TestCase
{
    #[Test]
    public function application_classes_have_unique_psr4_names(): void
    {
        $applicationPath = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'app';

        $iterator = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($applicationPath)
            ),
            '/\.php$/i'
        );

        $classes = [];
        $errors = [];

        foreach ($iterator as $file) {
            $contents = file_get_contents($file->getPathname());
            $contents = implode('', array_map(
                static fn ($token): string => is_array($token)
                    ? (in_array($token[0], [T_COMMENT, T_DOC_COMMENT], true) ? '' : $token[1])
                    : $token,
                token_get_all($contents)
            ));

            if (! preg_match('/namespace\s+([^;]+);/', $contents, $namespaceMatch)) {
                continue;
            }

            if (! preg_match('/(?:final\s+)?class\s+([A-Za-z_][A-Za-z0-9_]*)/', $contents, $classMatch)) {
                continue;
            }

            $fqcn = trim($namespaceMatch[1]).'\\'.$classMatch[1];
            $relativePath = str_replace('\\', '/', $file->getPathname());
            $relativePath = substr(
                $relativePath,
                strlen(str_replace('\\', '/', $applicationPath)) + 1
            );
            $classPath = preg_replace('/^App\\\\/', '', $fqcn);
            $expectedPath = str_replace('\\', '/', $classPath).'.php';

            if ($relativePath !== $expectedPath) {
                $errors[] = "{$fqcn} is stored at {$relativePath}; expected {$expectedPath}";
            }

            if (isset($classes[$fqcn])) {
                $errors[] = "{$fqcn} is declared by both {$classes[$fqcn]} and {$relativePath}";
            }

            $classes[$fqcn] = $relativePath;
        }

        self::assertSame([], $errors, implode(PHP_EOL, $errors));
    }
}
