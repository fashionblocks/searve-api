<?php

namespace App\Command;

use App\Entity\Asset;
use App\Entity\Project;
use App\Gateway\ProjectGateway;
use App\Gateway\SnapGateway;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TokenSyncCommand extends Command
{
    protected static $defaultName = 'token:sync';
    protected static $defaultDescription = '';

    private $em;
    private $gateway;
    private $snap;

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
        ;
    }

    public function __construct(EntityManagerInterface $em, ProjectGateway $gateway, SnapGateway $snapGateway)
    {
        parent::__construct();
        $this->em = $em;
        $this->gateway = $gateway;
        $this->snap = $snapGateway;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $projects = $this->em->getRepository(Project::class)->findAll();

        /** @var Project $project */
        foreach ($projects as $project) {
            $tokens = $this->gateway->findAllTokens($project->getProjectId());

            foreach ($tokens as $token) {
                $tokenId = hexdec($token);
                /** @var Asset $asset */
                $asset = $this->em->getRepository(Asset::class)->findOneBy([
                    'tokenId' => $tokenId
                ]);

                if ($asset && $asset->getImage()) {
                    continue;
                }
                $hash = $this->gateway->findTokenHash($tokenId);
                if (!$asset) {
                    $asset = new Asset();
                    $asset->setProject($project)
                        ->setTokenId($tokenId)
                        ->setTokenHash($hash);
                    $this->em->persist($asset);
                    $this->em->flush();
                    $io->writeln($tokenId . ' add');
                }
                if ($asset->getImage()) {
                    continue;
                }
                //make image

                $url = implode('', [
                    $project->getProjectId(),
                    '?'.http_build_query([
                        'tokenId' => $asset->getTokenId(),
                        'hash' => $asset->getTokenHash()
                    ])
                ]);
                $name = md5($url).'.jpeg';
                $fullPath = implode('/', [
                    'public',
                    'fs', substr($name, 0, 2), substr($name, 2, 2), $name
                ]);

                if (!is_dir(dirname($fullPath))) {
                    mkdir(dirname($fullPath), 0777, 1);
                }
                $this->snap->snap($url, $name);
                $asset->setImage($name);
                $this->em->persist($asset);
                $this->em->flush();
            }
        }

        $io->success('token succeed');

        return Command::SUCCESS;
    }
}
