<?php

namespace App\DTO;

use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Validator\Constraints as Assert;

readonly class SearchArticle
{
    public const DIRECTIONS = [Criteria::DESC, Criteria::ASC];

    public const SORTING = ['title', 'locale', 'updatedAt', 'draft', 'hit'];

    public const DEFAULT_PAGE_LIMIT = 30;

    public function __construct(
        #[Assert\Regex(pattern: '/^[\w\-]+$/', message: 'Invalid tag search criteria')]
        public ?string $tag = null,

        #[Assert\Choice(choices: self::SORTING, message: 'Wrong sorting parameter')]
        public ?string $sorting = 'updatedAt',

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
