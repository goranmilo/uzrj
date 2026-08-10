<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Upisuje trag izmena (create/update/delete) u tabelu `audit_log`.
 *
 * Registruje se u AppServiceProvider za modele nabrojane u
 * AppServiceProvider::AUDITOVANI_MODELI.
 */
class AuditObserver
{
    /**
     * Atributi koji se nikada ne upisuju u audit log.
     *
     * @var list<string>
     */
    protected const SKRIVENI_ATRIBUTI = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'qr_token',
    ];

    public function created(Model $model): void
    {
        $this->zapisi('create', $model, null, $this->atributi($model->getAttributes()));
    }

    public function updated(Model $model): void
    {
        $posle = $this->atributi($model->getChanges());
        unset($posle['updated_at']);

        if ($posle === []) {
            return;
        }

        // U `updated` događaju getOriginal() još uvek drži vrednosti pre izmene.
        $pre = array_intersect_key($this->atributi($model->getOriginal()), $posle);

        $this->zapisi('update', $model, $pre, $posle);
    }

    public function deleted(Model $model): void
    {
        $this->zapisi('delete', $model, $this->atributi($model->getAttributes()), null);
    }

    /**
     * @param  array<string, mixed>|null  $pre
     * @param  array<string, mixed>|null  $posle
     */
    protected function zapisi(string $akcija, Model $model, ?array $pre, ?array $posle): void
    {
        try {
            AuditLog::create([
                'user_id' => auth()->id(),
                'akcija' => $akcija,
                'entitet' => Str::snake(class_basename($model)),
                'entitet_id' => $model->getKey(),
                'pre' => $pre,
                'posle' => $posle,
                'vreme' => now(),
                'ip_adresa' => app()->runningInConsole() ? null : request()->ip(),
            ]);
        } catch (\Throwable $e) {
            // Audit log ne sme da obori poslovnu operaciju.
            Log::warning('Neuspeo upis u audit log', [
                'akcija' => $akcija,
                'entitet' => class_basename($model),
                'entitet_id' => $model->getKey(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Izbaci osetljive atribute i normalizuj vrednosti za JSON kolonu.
     *
     * @param  array<string, mixed>  $atributi
     * @return array<string, mixed>
     */
    protected function atributi(array $atributi): array
    {
        $atributi = array_diff_key($atributi, array_flip(static::SKRIVENI_ATRIBUTI));

        return array_map(
            fn ($vrednost) => $vrednost instanceof \DateTimeInterface
                ? $vrednost->format('Y-m-d H:i:s')
                : $vrednost,
            $atributi,
        );
    }
}
