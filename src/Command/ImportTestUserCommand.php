<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @codeCoverageIgnore
 */
#[AsCommand(
    name: 'app:import-test-user',
    description: 'Imports the test user',
)]
class ImportTestUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $testUser = new User();
        $testUser->setName('Test User');
        $testUser->setEmail('test@example.com');

        $hashedPassword = $this->passwordHasher->hashPassword($testUser, 'zY5A01BOE96T0v3u1LsCud8MiQYzOkkh');
        $testUser->setPassword($hashedPassword);

        $this->entityManager->persist($testUser);
        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
