<?php

namespace App\Command;

use App\Entity\Project;
use App\Gateway\ProjectGateway;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ScriptSyncCommand extends Command
{
    protected static $defaultName = 'app:script:sync';
    protected static $defaultDescription = '';

    private $em;
    private $gateway;

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
        ;
    }


    public function __construct(EntityManagerInterface $em, ProjectGateway $gateway)
    {
        parent::__construct();
        $this->gateway = $gateway;
        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $dir = './public/pieces/';
        $result = $this->em->getRepository(Project::class)->findBy([
            'script' => null
        ]);

        /** @var Project $item */
        foreach ($result as $item) {
            $script = $this->gateway->findScript($item->getProjectId());

            if (strlen($script) > 100) {
                $item->setScript($script);
                $this->em->persist($item);
                $this->em->flush();
                file_put_contents($dir."{$item->getProjectId()}.js",  $script);
                $io->success($item->getName() . ' script update succeed');
            }
        }

        $io->success('sync succeed.');

        return Command::SUCCESS;
    }
}
