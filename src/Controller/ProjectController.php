<?php
namespace App\Controller;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Util\Pagination;
use FOS\RestBundle\Controller\Annotations as Rest;


/**
 * Class ProjectController
 * @package App\Controller
 * @Rest\Route("/projects")
 */
class ProjectController
{
    /**
     * @Rest\Get("")
     * @Rest\QueryParam(name="page", default="1")
     * @Rest\QueryParam(name="size", default="15")
     * @Rest\QueryParam(name="artist", nullable=true)
     */
    public function cgetAction(ProjectRepository $repository, Pagination  $pagination, ?string $artist) {

        $query = $repository->createQueryBuilder('p')
            ->select('p')
            ->orderBy('p.id', 'desc')
            ;
        if ($artist) {
            $query->leftJoin('p.artist', 'a')
                ->where('a.address=:address')
                ->setParameter('address', strtolower($artist));
        }

        return $pagination->paginate($query);
    }

    /**
     * @Rest\Get("/{project}")
     */
    public function getAction(Project $project) {

        return $project;
    }

    /**
     * @Rest\Get("/{project}/assets")
     * @Rest\View(serializerGroups={"Default"})
     */
    public function assetsAction(Project $project) {

        return $project->getAssets();
    }
}
