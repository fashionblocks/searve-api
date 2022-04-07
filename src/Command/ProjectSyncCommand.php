<?php

namespace App\Command;

use App\Entity\Artist;
use App\Entity\Project;
use App\Gateway\ProjectGateway;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ProjectSyncCommand extends Command
{
    protected static $defaultName = 'app:project:sync';
    protected static $defaultDescription = '';

    private $gateway;
    private $em;

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
        ;
    }

    public function __construct(ProjectGateway $gateway, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->gateway = $gateway;
        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $start = 3;
        $projectId = $this->gateway->findMaxId();
        for($i = $start ; $i < $projectId; $i++) {

            $project = $this->em->getRepository(Project::class)->findBy([
                'projectId' => $i
            ]);
            if ($project) {
                continue;
            }

            $info = $this->gateway->findProjectInfo($i);

            $address = strtolower($info['artist']);
            if (strlen($address) < 24)
                continue;
            $artist = $this->em->getRepository(Artist::class)->findOneBy([
                'address' => $address
            ]);
            if (!$artist) {
                $artist = new Artist();
                $artist->setAddress($address);
                $this->em->persist($artist);
            }
            $project = new Project();
            $project->setName($info['projectName'])
                ->setArtist($artist)
                ->setWebsite($info['website'])
                ->setProjectId($i)
                ->setDescription($info['description'])
                ->setCreatedAt(new \DateTimeImmutable())
                ;
            $this->em->persist($project);
            $this->em->flush();
            $io->success($project->getName() .' add');



        }

        return Command::SUCCESS;
    }
}
