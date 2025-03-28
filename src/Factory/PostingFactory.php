<?php

namespace App\Factory;

use App\Entity\Posting;
use App\Enum\ExperienceLevel;
use App\Enum\JobType;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Posting>
 * 
 * @codeCoverageIgnore
 */
final class PostingFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    public static function class(): string
    {
        return Posting::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        $titlesFile = __DIR__ . '/../../public/data/titles.json';
        $tiles = json_decode(file_get_contents($titlesFile), true);

        return [
            'title' => ucwords(self::faker()->randomElement($tiles)),
            'description' => self::faker()->paragraphs(self::faker()->numberBetween(3, 10), true),
            'experienceLevel' => self::faker()->randomElement(ExperienceLevel::cases()),
            'jobType' => self::faker()->randomElement(JobType::cases()),
            'fields' => json_encode(array_map(fn() => self::faker()->sentence(), range(1, self::faker()->numberBetween(2, 5))))
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this// ->afterInstantiate(function(Posting $posting): void {})
            ;
    }
}
