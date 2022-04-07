<?php

namespace App\Util;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use FOS\RestBundle\Request\ParamFetcherInterface;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\HttpFoundation\RequestStack;

class Pagination
{
    private $fetcher;
    private $request;

    public function __construct(ParamFetcherInterface $fetcher, RequestStack $requestStack)
    {
        $this->fetcher = $fetcher;
        $this->request = $requestStack;
    }

    #[ArrayShape(["data" => "array", "total" => "int"])]
    public function paginate(QueryBuilder $qb, ?callable $hydrate = null)
    {
        $page = $this->fetcher->get('page');
        $size = $this->fetcher->get('size');
        $start = $this->request->getCurrentRequest()->query->get('start', false);

        $p = new Paginator(
            $qb
                ->setFirstResult($start !== false ? $start : $size * ($page - 1))
                ->setMaxResults($size)
        );

        $total = $p->count();
        $data = $p->getQuery()->getResult();

        return [
            'total' => $total,
            'data' => $hydrate ? array_map($hydrate, $data) : $data,
        ];
    }
}
