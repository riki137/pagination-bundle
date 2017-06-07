<?php

declare(strict_types=1);

namespace Everlution\PaginationBundle\Pagination;

use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Everlution\PaginationBundle\Pagination\Filter\FilterQuery;
use Everlution\PaginationBundle\Pagination\Sort\SortQuery;

/**
 * @author Ivan Barlog <ivan.barlog@everlution.sk>
 */
class QueryPagination implements QueryToPagination
{
    const DEFAULT_MAX_RESULTS = 100;

    /** @var Query */
    private $query = null;
    /** @var FilterQuery */
    private $filterQuery;
    /** @var SortQuery */
    private $sortQuery;
    /** @var int */
    private $maxResults;

    public function __construct(
        FilterQuery $filterQuery,
        SortQuery $sortQuery,
        int $maxResults = self::DEFAULT_MAX_RESULTS
    ) {
        $this->filterQuery = $filterQuery;
        $this->sortQuery = $sortQuery;
        $this->maxResults = $maxResults;
    }

    public function paginate(int $limit, int $offset): Page
    {
        if ($limit > $this->maxResults) {
            throw new MaxResultsExceeded($this->maxResults, $limit);
        }

        if (false === $this->query instanceof Query) {
            throw new QueryPaginatorNotInitialized();
        }

        $query = $this->filterQuery->addFilter($this->query);
        $query = $this->sortQuery->addSort($query);

        $paginator = new Paginator($query);
        $paginator
            ->setUseOutputWalkers(false)
            ->getQuery()
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return new ListPage($paginator);
    }

    public function setQuery(Query $query): Pagination
    {
        $this->query = $query;

        return $this;
    }

    public function getMaxResults(): int
    {
        return $this->maxResults;
    }

    public function setMaxResults(int $maxResults): Pagination
    {
        $this->maxResults = $maxResults;

        return $this;
    }
}
