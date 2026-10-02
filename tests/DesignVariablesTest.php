<?php

declare(strict_types=1);

namespace Nordwerk\ThemeBaseBundle\Tests;

use Nordwerk\ThemeBaseBundle\Design\DesignSettings;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

final class DesignVariablesTest extends TestCase
{
    public function testEmptyOverridesDoNotEmitVariables(): void
    {
        $html = $this->render(new DesignSettings(themeKey: 'seminar'));

        foreach (['--theme-paper', '--theme-ink', '--theme-muted', '--theme-radius', '--nw-radius-button', '--theme-tile', '--theme-callout', '--nw-radius-card'] as $variable) {
            $this->assertStringNotContainsString($variable.':', $html);
        }
        $this->assertStringContainsString('<style nonce="test-nonce">', $html);
        $this->assertStringContainsString('--nw-accent: #382f3d;', $html);
    }

    public function testExplicitOverridesAreEmittedIncludingZeroRadius(): void
    {
        $settings = new DesignSettings(paper: 'fff', text: '000', radius: '0', buttonShape: '999px', tone1: 'eeeeee', tone2: 'dddddd');
        $html = $this->render($settings);

        foreach ($settings->cssVariables() as $name => $value) {
            $this->assertStringContainsString($name.': '.$value.';', $html);
        }
        $this->assertStringContainsString('--theme-radius: 0;', $html);
        $this->assertStringContainsString('--theme-muted: color-mix(in srgb, var(--theme-ink) 75%, var(--theme-paper));', $html);
    }

    public function testPaperOverrideAloneDoesNotEmitTextOrMuted(): void
    {
        $html = $this->render(new DesignSettings(paper: 'ffffff'));
        $this->assertStringContainsString('--theme-paper: #ffffff;', $html);
        $this->assertStringNotContainsString('--theme-ink:', $html);
        $this->assertStringNotContainsString('--theme-muted:', $html);
        $this->assertStringNotContainsString('--theme-tile:', $html);
        $this->assertStringNotContainsString('--theme-callout:', $html);
    }

    private function render(DesignSettings $settings): string
    {
        $files = new FilesystemLoader();
        $files->addPath(__DIR__.'/../contao/templates', 'Contao');
        $twig = new Environment(new ChainLoader([
            new ArrayLoader(['@Contao/frontend_module/_base.html.twig' => '{% block content %}{% endblock %}']),
            $files,
        ]), ['strict_variables' => true]);
        $twig->addFunction(new TwigFunction('asset', static fn (string $path): string => $path));
        $twig->addFunction(new TwigFunction('csp_nonce', static fn (string $directive): string => 'test-nonce'));

        return $twig->render('@Contao/frontend_module/nw_theme_header.html.twig', [
            'design' => $settings, 'headingFont' => 'Lora', 'bodyFont' => 'Source Sans 3',
            'brand' => 'Test-Theme', 'logoPath' => null, 'links' => [],
        ]);
    }
}
