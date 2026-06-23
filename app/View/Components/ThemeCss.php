<?php

namespace App\View\Components;

use App\Filament\Pages\ThemeSettings;
use Illuminate\View\Component;

class ThemeCss extends Component
{
    public string $css;

    public function __construct()
    {
        $tema = ThemeSettings::getAktuelnaTema();
        $this->css = $this->generateCss($tema);
    }

    public function render()
    {
        return view('components.theme-css');
    }

    protected function generateCss(array $tema): string
    {
        $primary = $tema['primary'] ?? '#10B981';
        $primaryDark = $tema['primary-dark'] ?? '#059669';
        $accent = $tema['accent'] ?? '#34D399';
        $darkMode = $tema['dark_mode'] ?? false;

        $css = "
            :root {
                --theme-primary: {$primary};
                --theme-primary-dark: {$primaryDark};
                --theme-accent: {$accent};
            }
            
            /* Sidebar active item */
            .fi-sidebar-item-active,
            [data-sidebar-item-active] {
                background-color: {$primary}20 !important;
            }
            .fi-sidebar-item-active a,
            [data-sidebar-item-active] a {
                color: {$primary} !important;
            }
            .fi-sidebar-item-active svg,
            [data-sidebar-item-active] svg {
                color: {$primary} !important;
            }
            
            /* Primary buttons */
            .fi-btn-primary,
            [class*=\"btn-primary\"] {
                background-color: {$primary} !important;
                border-color: {$primary} !important;
            }
            .fi-btn-primary:hover,
            [class*=\"btn-primary\"]:hover {
                background-color: {$primaryDark} !important;
                border-color: {$primaryDark} !important;
            }
            
            /* Links */
            a.text-primary-600, a[class*=\"text-primary\"] {
                color: {$primary} !important;
            }
            
            /* Badges */
            span.fi-badge, span[class*=\"badge\"] {
                background-color: {$primary} !important;
            }
            
            /* Toggle */
            input:checked + span {
                background-color: {$primary} !important;
            }
            
            /* Stats */
            .text-sm.font-medium.text-gray-500 {
                color: {$primary} !important;
            }
            
            /* Chart */
            canvas {
                color: {$primary};
            }
        ";

        if ($darkMode) {
            $css .= "
                body { background-color: #111827 !important; color: #F3F4F6 !important; }
                .fi-main { background-color: #1F2937 !important; }
                .fi-sidebar { background-color: #111827 !important; }
                .fi-card { background-color: #1F2937 !important; border-color: #374151 !important; }
                input, select, textarea { background-color: #374151 !important; color: #F3F4F6 !important; }
                h1, h2, h3, h4, h5, h6 { color: #F3F4F6 !important; }
            ";
        }

        return $css;
    }
}
