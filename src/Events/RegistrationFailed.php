<?php

namespace DutchCodingCompany\FilamentSocialite\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use DutchCodingCompany\FilamentSocialite\Models\SocialiteUser;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;

class RegistrationFailed
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        public string $provider,
        public SocialiteUserContract $oauthUser,
        public SocialiteUser $socialiteUser,
    ) {
    }
}
