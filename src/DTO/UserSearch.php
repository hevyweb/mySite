<?php

namespace App\DTO;

use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @psalm-immutable
 */
final readonly class UserSearch
{
    public const DIRECTIONS = [Criteria::DESC, Criteria::ASC];

    public const SORTING = ['id'];

    public const DEFAULT_PAGE_LIMIT = 30;

    public function __construct(
        #[Assert\Choice(choices: self::SORTING, message: 'Wrong sorting parameter')]
        public ?string $sorting = 'id',

        #[Assert\Choice(choices: self::DIRECTIONS, message: 'Wrong sorting direction')]
        public ?string $dir = Criteria::DESC,

        #[Assert\Positive]
        public ?int $page = 1,

        public ?string $search = null,

        #[Assert\Positive]
        public ?int $limit = self::DEFAULT_PAGE_LIMIT,
    ) {
    }
}
