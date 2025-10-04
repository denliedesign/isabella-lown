<?php

namespace App\Policies;

use App\Models\Media;
use App\Models\User;

class MediaPolicy
{
    private const ADMIN_EMAIL = [
        'customdenlie@gmail.com', // <-- put your email here exactly as used to login
    ];

    private function isAdmin(User $user): bool
    {
        return in_array($user->email, self::ADMIN_EMAIL, true);
    }

    public function viewAny(User $user): bool { return $this->isAdmin($user); }
    public function create(User $user): bool { return $this->isAdmin($user); }
    public function update(User $user, Media $media): bool { return $this->isAdmin($user); }
    public function delete(User $user, Media $media): bool { return $this->isAdmin($user); }
}
