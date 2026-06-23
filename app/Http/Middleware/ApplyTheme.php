<?php

namespace App\Http\Middleware;

use App\Filament\Pages\ThemeSettings;
use App\Models\Podesavanje;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyTheme
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Dobijanje teme
        $tema = ThemeSettings::getAktuelnaTema();
        $primary = $tema['primary'] ?? '#10B981';
        $primaryDark = $tema['primary-dark'] ?? '#059669';
        $accent = $tema['accent'] ?? '#34D399';
        $darkMode = $tema['dark_mode'] ?? false;

        // Dodavanje CSS varijablia u response header-e za JavaScript
        $response->headers->set('X-Theme-Primary', $primary);
        $response->headers->set('X-Theme-Primary-Dark', $primaryDark);
        $response->headers->set('X-Theme-Accent', $accent);
        $response->headers->set('X-Theme-Dark-Mode', $darkMode ? '1' : '0');

        // Samo za HTML odgovore
        if (!$response instanceof \Illuminate\Http\Response) {
            return $response;
        }

        $content = $response->getContent();

        // Provera da li je HTML stranica
        if (strpos($content, '</head>') === false && strpos($content, '</body>') === false) {
            return $response;
        }

        // Generisanje CSS-a
        $css = $this->generateThemeCss($tema);

        // Ubacivanje CSS-a pre </head> ili </body>
        $themeStyle = "<style id=\"theme-css\">{$css}</style>";
        
        if (strpos($content, '</head>') !== false) {
            $content = str_replace('</head>', $themeStyle . '</head>', $content);
        } elseif (strpos($content, '</body>') !== false) {
            $content = str_replace('</body>', $themeStyle . '</body>', $content);
        }

        $response->setContent($content);

        return $response;
    }

    /**
     * Generiši CSS za temu
     */
    protected function generateThemeCss(array $tema): string
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
            
            /* Filament primary color override - comprehensive */
            .fi-color-primary,
            [class*=\"fi-color-primary\"] {
                --fi-color: {$primary} !important;
                --fi-color-50: {$primary}15 !important;
                --fi-color-100: {$primary}25 !important;
                --fi-color-200: {$primary}35 !important;
                --fi-color-300: {$primary}45 !important;
                --fi-color-400: {$primary}55 !important;
                --fi-color-500: {$primary} !important;
                --fi-color-600: {$primaryDark} !important;
                --fi-color-700: {$primaryDark} !important;
                --fi-color-800: {$primaryDark} !important;
                --fi-color-900: {$primaryDark} !important;
                --fi-color-950: {$primaryDark} !important;
            }
            
            /* Buttons */
            .fi-btn-primary,
            button[class*=\"fi-btn-primary\"] {
                background-color: {$primary} !important;
                border-color: {$primary} !important;
            }
            .fi-btn-primary:hover,
            button[class*=\"fi-btn-primary\"]:hover {
                background-color: {$primaryDark} !important;
                border-color: {$primaryDark} !important;
            }
            
            /* Sidebar */
            .fi-sidebar-item-active,
            [class*=\"fi-sidebar-item-active\"] {
                background-color: {$primary}20 !important;
                color: {$primary} !important;
            }
            .fi-sidebar-item-active svg,
            [class*=\"fi-sidebar-item-active\"] svg {
                color: {$primary} !important;
            }
            
            /* Links */
            a:not([class]) {
                color: {$primary};
            }
            a:not([class]):hover {
                color: {$primaryDark};
            }
            
            /* Badge */
            .fi-badge,
            span[class*=\"fi-badge\"] {
                background-color: {$primary} !important;
            }
            
            /* Toggle */
            .fi-toggle-input:checked,
            input[type=\"checkbox\"]:checked {
                background-color: {$primary} !important;
                border-color: {$primary} !important;
            }
            
            /* Progress bar */
            .fi-progress-bar,
            [class*=\"fi-progress-bar\"] {
                background-color: {$primary} !important;
            }
            
            /* Stats cards accent */
            .fi-stats-overview-stat-label {
                color: {$primary} !important;
            }
            
            /* Chart colors */
            canvas {
                accent-color: {$primary};
            }
        ";

        if ($darkMode) {
            $css .= "
                /* Dark mode */
                body, .fi-body, [class*=\"fi-body\"] {
                    background-color: #111827 !important;
                    color: #F3F4F6 !important;
                }
                
                .fi-main, [class*=\"fi-main\"] {
                    background-color: #1F2937 !important;
                }
                
                .fi-sidebar, [class*=\"fi-sidebar\"] {
                    background-color: #111827 !important;
                }
                
                .fi-card, .fi-section, [class*=\"fi-card\"], [class*=\"fi-section\"] {
                    background-color: #1F2937 !important;
                    border-color: #374151 !important;
                }
                
                input, select, textarea {
                    background-color: #374151 !important;
                    color: #F3F4F6 !important;
                    border-color: #4B5563 !important;
                }
                
                .fi-table, [class*=\"fi-table\"] {
                    background-color: #1F2937 !important;
                }
                
                .fi-table-row, [class*=\"fi-table-row\"] {
                    border-color: #374151 !important;
                }
                
                .fi-table-cell, [class*=\"fi-table-cell\"] {
                    color: #F3F4F6 !important;
                }
                
                h1, h2, h3, h4, h5, h6 {
                    color: #F3F4F6 !important;
                }
                
                p, span, li {
                    color: #D1D5DB !important;
                }
            ";
        }

        return $css;
    }
}
