<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Mei;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MeiPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Mei $mei): Response
    {
        return $this->ownershipResponse($user, $mei);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Mei $mei): Response
    {
        return $this->ownershipResponse($user, $mei);
    }

    public function delete(User $user, Mei $mei): Response
    {
        return $this->ownershipResponse($user, $mei);
    }

    public function restore(User $user, Mei $mei): Response
    {
        return $this->ownershipResponse($user, $mei);
    }

    public function forceDelete(User $user, Mei $mei): bool
    {
        return false;
    }

    private function ownershipResponse(User $user, Mei $mei): Response
    {
        return (string) $user->getKey() === (string) $mei->getAttribute('user_id')
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
