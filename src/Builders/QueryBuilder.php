<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Builders;

use ComponoKit\Databases\Sql\QueryBuilder\Builders\Interfaces\BuildsQueries;
use ComponoKit\Databases\Sql\QueryBuilder\Helpers\QueryFilterDistributor;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\DistributesQueryFilters;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsColumn;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsCriteria;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsJoinClause;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsLimit;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Interfaces\RepresentsOrderBy;

class QueryBuilder implements BuildsQueries
{
	private readonly DistributesQueryFilters $queryFilterDistributor;

	public function __construct( ?DistributesQueryFilters $queryFilterDistributor = null )
	{
		$this->queryFilterDistributor = $queryFilterDistributor ?? QueryFilterDistributor::newEmpty();
	}

	public function addOrderBy( RepresentsOrderBy ...$orderByList ): static
	{
		return new self( $this->queryFilterDistributor->addOrderBy( ...$orderByList ) );
	}

	public function useLimit( RepresentsLimit $limit ): static
	{
		return new self( $this->queryFilterDistributor->useLimit( $limit ) );
	}

	public function addCriteria( RepresentsCriteria ...$criterias ): static
	{
		return new self( $this->queryFilterDistributor->addCriteria( ...$criterias ) );
	}

	public function addHavingCriteria( RepresentsCriteria ...$criterias ): static
	{
		return new self( $this->queryFilterDistributor->addHavingCriteria( ...$criterias ) );
	}

	public function addGroupByColumn( RepresentsColumn ...$columns ): static
	{
		return new self( $this->queryFilterDistributor->addGroupByColumn( ...$columns ) );
	}

	public function addJoinClause( RepresentsJoinClause ...$joinClauses ): static
	{
		return new self( $this->queryFilterDistributor->addJoinClause( ...$joinClauses ) );
	}

	public function getJoinClauses(): array
	{
		return $this->queryFilterDistributor->getJoinClauses();
	}

	public function buildJoin(): string
	{
		return ( new JoinBuilder( $this->queryFilterDistributor->getJoinClauses() ) )->build();
	}

	public function buildWhereStatement(): string
	{
		return ( new WhereStatementBuilder( $this->queryFilterDistributor->getCriterias() ) )->buildWhereStatement();
	}

	public function getPreparedParams(): array
	{
		return ( new WhereStatementBuilder( $this->queryFilterDistributor->getCriterias() ) )->getPreparedParams();
	}

	public function buildGroupBy(): string
	{
		return ( new GroupByBuilder( $this->queryFilterDistributor->getGroupByColumns() ) )->build();
	}

	public function buildOrderBy(): string
	{
		return ( new OrderByBuilder( $this->queryFilterDistributor->getOrderByList() ) )->build();
	}

	public function buildLimit(): string
	{
		return ( new LimitBuilder( $this->queryFilterDistributor->getLimit() ) )->build();
	}

	public function buildHaving(): string
	{
		return ( new HavingBuilder( $this->queryFilterDistributor->getHavingCriterias() ) )->build();
	}

	public function buildAll( SelectBuilder $selectBuilder ): string
	{
		return $selectBuilder->build() . $this->buildJoin() . $this->buildWhereStatement() . $this->buildGroupBy() . $this->buildHaving() . $this->buildOrderBy() . $this->buildLimit();
	}
}
