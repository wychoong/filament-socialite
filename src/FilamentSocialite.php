<?php

namespace DutchCodingCompany\FilamentSocialite;

use Closure;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Auth\StatefulGuard;
use DutchCodingCompany\FilamentSocialite\Models\SocialiteUser;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use DutchCodingCompany\FilamentSocialite\Exceptions\ProviderNotConfigured;

class FilamentSocialite
{
    protected ?Closure $userResolver = null;
    protected ?Closure $createSocialiteUserCallback = null;
    protected ?Closure $createUserCallback = null;

    public function __construct(
        protected Repository $config,
        protected Factory $auth,
    ) {
    }

    public function isProviderConfigured(string $provider): bool
    {
        return $this->config->has('services.'.$provider);
    }

    public function getProviderConfig(string $provider): array
    {
        if (! $this->isProviderConfigured($provider)) {
            throw ProviderNotConfigured::make($provider);
        }

        return $this->config->get('services.'.$provider);
    }

    public function getProviderScopes(string $provider): string|array
    {
        return $this->getProviderConfig($provider)['scopes'] ?? [];
    }

    public function getConfig(): array
    {
        return $this->config->get('filament-socialite', []);
    }

    public function getDomainAllowList(): array
    {
        return $this->getConfig()['domain_allowlist'] ?? [];
    }

    public function getUserModelClass(): string
    {
        return $this->getConfig()['user_model'] ?? \App\Models\User::class;
    }

    public function getUserModel(): Model
    {
        return new $this->getUserModelClass();
    }

    public function getUserResolver(): Closure
    {
        return $this->userResolver ?? fn (SocialiteUserContract $oauthUser) => $this->getUserModel()->where('email', $oauthUser->getEmail())->first();
    }

    public function getCreateSocialiteUserCallback(): Closure
    {
        return $this->createSocialiteUserCallback ?? fn (string $provider, SocialiteUserContract $oauthUser, Model $user) => SocialiteUser::create([
            'user_id' => $user->getKey(),
            'provider' => $provider,
            'provider_id' => $oauthUser->getId(),
        ]);
    }
    public function getCreateUserCallback(): Closure
    {
        return $this->createUserCallback ?? fn (SocialiteUserContract $oauthUser) => $this->getUserModelClass()::create([
            'name' => $oauthUser->getName(),
            'email' => $oauthUser->getEmail(),
        ]);
    }

    public function getGuard(): StatefulGuard
    {
        return $this->auth->guard(
            $this->config->get('filament.auth.guard')
        );
    }

    public function isRegistrationEnabled(): bool
    {
        return $this->getConfig()['registration'] == true;
    }
}
