<?php

namespace App\Http\Middleware;

use App\Support\Tema;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyTheme
{
    /**
     * Ubaci CSS varijable teme u HTML odgovore van Filament panela.
     *
     * Unutar panela isti CSS ubacuje <x-theme-css /> komponenta
     * (resources/views/vendor/filament/assets.blade.php), pa se ovde
     * preskače da se ne bi duplirao.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $tema = Tema::aktuelna();

        $response->headers->set('X-Theme-Primary', $tema['primary']);
        $response->headers->set('X-Theme-Primary-Dark', $tema['primary-dark']);
        $response->headers->set('X-Theme-Accent', $tema['accent']);
        $response->headers->set('X-Theme-Dark-Mode', $tema['dark_mode'] ? '1' : '0');

        if (! $response instanceof \Illuminate\Http\Response) {
            return $response;
        }

        $content = $response->getContent();

        if (! is_string($content) || str_contains($content, 'id="theme-css"')) {
            return $response;
        }

        if (! str_contains($content, '</head>') && ! str_contains($content, '</body>')) {
            return $response;
        }

        $style = '<style id="theme-css">'.Tema::css($tema).'</style>';

        $content = str_contains($content, '</head>')
            ? str_replace('</head>', $style.'</head>', $content)
            : str_replace('</body>', $style.'</body>', $content);

        $response->setContent($content);

        return $response;
    }
}
