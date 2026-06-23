<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class TwoFactorSetup extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Administracija';
    protected static ?string $navigationLabel = '2FA podešavanja';
    protected static ?string $title = 'Dvofaktorska autentifikacija';
    protected static ?int $navigationSort = 20;

    protected static string $view = 'filament.pages.two-factor-setup';

    public ?string $qrCodeSvg = null;
    public ?string $recoveryCodes = null;
    public bool $twoFactorEnabled = false;

    public function mount(): void
    {
        $user = Auth::user();
        $this->twoFactorEnabled = $user->has2FAEnabled();

        if ($this->twoFactorEnabled) {
            $this->recoveryCodes = implode("\n", json_decode(decrypt($user->two_factor_recovery_codes), true));
        }
    }

    public function enableTwoFactor(): void
    {
        $user = Auth::user();

        if ($user->has2FAEnabled()) {
            Notification::make()
                ->title('2FA je već omogućen')
                ->warning()
                ->send();
            return;
        }

        // Generate new 2FA secret
        $user->forceFill([
            'two_factor_secret' => encrypt($user->generateRecoveryCodes()),
            'two_factor_recovery_codes' => encrypt(json_encode($user->generateRecoveryCodes())),
        ])->save();

        // Get QR code
        $this->qrCodeSvg = $user->twoFactorQrCodeSvg();
        $this->recoveryCodes = implode("\n", json_decode(decrypt($user->two_factor_recovery_codes), true));
        $this->twoFactorEnabled = true;

        Notification::make()
            ->title('2FA omogućen')
            ->success()
            ->send();
    }

    public function disableTwoFactor(): void
    {
        $user = Auth::user();

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $this->twoFactorEnabled = false;
        $this->qrCodeSvg = null;
        $this->recoveryCodes = null;

        Notification::make()
            ->title('2FA onemogućen')
            ->success()
            ->send();
    }

    public function regenerateRecoveryCodes(): void
    {
        $user = Auth::user();

        if (!$user->has2FAEnabled()) {
            Notification::make()
                ->title('Prvo omogućite 2FA')
                ->warning()
                ->send();
            return;
        }

        $user->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode($user->generateRecoveryCodes())),
        ])->save();

        $this->recoveryCodes = implode("\n", json_decode(decrypt($user->two_factor_recovery_codes), true));

        Notification::make()
            ->title('Recovery kodovi regenerisani')
            ->success()
            ->send();
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('code')
                ->label('Verifikacioni kod')
                ->placeholder('Unesite 6-cifreni kod iz aplikacije')
                ->numeric()
                ->length(6)
                ->visible(fn () => $this->qrCodeSvg !== null),
        ];
    }

    protected function getViewData(): array
    {
        return [
            'qrCodeSvg' => $this->qrCodeSvg,
            'recoveryCodes' => $this->recoveryCodes,
            'twoFactorEnabled' => $this->twoFactorEnabled,
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }
}
