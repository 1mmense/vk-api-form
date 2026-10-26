<?php

declare(strict_types=1);

namespace App\Classes;

final readonly class VkConfig
{
    public function __construct(
        public string $accessToken,
        public string $ownerId,
        public string $groupId,
        public string $albumId,
    ) {
        if (empty($accessToken)) {
            throw new \InvalidArgumentException('Access token is required');
        }

        if (empty($ownerId)) {
            throw new \InvalidArgumentException('OwnerID is required');
        }

        if (empty($groupId)) {
            throw new \InvalidArgumentException('GroupID is required');
        }

        if (empty($albumId)) {
            throw new \InvalidArgumentException('AlbumID is required');
        }
    }
}