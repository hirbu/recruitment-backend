<?php

namespace App\Command;

use App\Entity\City;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @codeCoverageIgnore
 */
#[AsCommand(
    name: 'app:import-cities',
    description: 'Imports cities from JSON file into the database',
)]
class ImportCitiesCommand extends Command
{
    public function __construct(
        private readonly string                 $path,
        private readonly EntityManagerInterface $entityManager
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Starting city import');

        $jsonFilePath = $this->path;

        if (!file_exists($jsonFilePath)) {
            $io->error('Cities JSON file not found!');
            return Command::FAILURE;
        }

        $citiesData = json_decode(file_get_contents($jsonFilePath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $io->error('Invalid JSON format: ' . json_last_error_msg());
            return Command::FAILURE;
        }

        $io->progressStart($count = count($citiesData));

        foreach ($citiesData as $index => $cityData) {
            $city = new City();
            $city->setName($cityData);

            $this->entityManager->persist($city);

            $io->progressAdvance();

            if ($index % 100 === 0) {
                $this->entityManager->flush();
            }
        }

        $io->progressFinish();

        $this->entityManager->flush();

        $io->success("Successfully imported {$count} new cities!");
        return Command::SUCCESS;
    }
}
