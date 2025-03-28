<?php

namespace App\DataFixtures;

use App\Factory\CityFactory;
use App\Factory\PostingFactory;
use App\Factory\TagFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @codeCoverageIgnore
 */
class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly KernelInterface             $kernel
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        ini_set('memory_limit', '256M');

        $this->runCommand('app:import-cities');
        $this->runCommand('app:import-tags');

        $testUser = UserFactory::new()->create([
            'email' => 'test@example.com',
            'password' => 'zY5A01BOE96T0v3u1LsCud8MiQYzOkkh',
            'name' => 'Test User',
        ]);
        $hashedPassword = $this->passwordHasher->hashPassword($testUser, 'zY5A01BOE96T0v3u1LsCud8MiQYzOkkh');
        $testUser->setPassword($hashedPassword);

        UserFactory::new()->createMany(2);

        $postings = PostingFactory::new()
            ->many(100)
            ->create(fn() => [
                'cities' => CityFactory::randomRange(1, 3),
                'tags' => TagFactory::randomRange(1, 10),
                'owner' => UserFactory::random(),
            ]);
    }

    private function runCommand(string $command): void
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => $command,
        ]);

        $output = new ConsoleOutput();
        $application->run($input, $output);
    }
}
