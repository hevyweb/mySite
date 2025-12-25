<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $slug;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class, mappedBy: 'article', cascade: ['persist', 'remove'])]
    private Collection $tags;

    /**
     * @var Collection<int, ArticleTranslation>
     */
    #[ORM\OneToMany(mappedBy: 'article', targetEntity: ArticleTranslation::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $articleTranslations;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->articleTranslations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->addArticle($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        if ($this->tags->removeElement($tag)) {
            $tag->removeArticle($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, ArticleTranslation>
     */
    public function getArticleTranslations(): Collection
    {
        return $this->articleTranslations;
    }

    public function addArticleTranslation(ArticleTranslation $articleTranslation): static
    {
        if (!$this->articleTranslations->contains($articleTranslation)) {
            $this->articleTranslations->add($articleTranslation);
            $articleTranslation->setArticle($this);
        }

        return $this;
    }

    public function getArticleTranslation(string $locale): ?ArticleTranslation
    {
        $translation = $this->articleTranslations->filter(
            fn (ArticleTranslation $articleTranslation) => $articleTranslation->getLocale() == $locale
        )->first();

        if (false === $translation) {
            return null;
        }

        return $translation;
    }

    public function getArticleTranslationWithFallBack(string $locale): ?ArticleTranslation
    {
        $translation = $this->getArticleTranslation($locale) ?? $this->articleTranslations->first();

        if (false === $translation) {
            return null;
        }

        return $translation;
    }
}
