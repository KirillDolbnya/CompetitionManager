<?php

namespace App\Repositories;

use App\Models\VkUser;

class VkUserRepository
{
    public function firstOrCreate(int $id): VkUser
    {
        return VkUser::firstOrCreate([
            'vk_id' => $id
        ]);
    }

    public function editState(int $id, string $state): VkUser
    {
        $user = VkUser::where('vk_id', $id)->firstOrFail();

        $user->update(['state' => $state]);

        return $user;
    }

    public function getByVkId(int $id): VkUser
    {
        return VkUser::query()->where('vk_id', $id)->firstOrFail();
    }
}
