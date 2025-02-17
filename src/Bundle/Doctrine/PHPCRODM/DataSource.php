<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Bundle\GridBundle\Doctrine\PHPCRODM;

use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;
use Pagerfanta\Doctrine\PHPCRODM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Sylius\Component\Grid\Data\DataSourceInterface;
use Sylius\Component\Grid\Data\ExpressionBuilderInterface;
use Sylius\Component\Grid\Parameters;

@trigger_error(sprintf('The "%s" class is deprecated since Sylius 1.3. Doctrine MongoDB and PHPCR support will no longer be supported in Sylius 2.0.', DataSource::class), \E_USER_DEPRECATED);

final class DataSource implements DataSourceInterface
{
    private QueryBuilder $queryBuilder;

    private ExpressionBuilderInterface $expressionBuilder;

    public function __construct(QueryBuilder $queryBuilder, ?ExpressionBuilderInterface $expressionBuilder = null)
    {
        $this->queryBuilder = $queryBuilder;
        $this->expressionBuilder = $expressionBuilder ?: new ExpressionBuilder();
    }

    public function restrict($expression, string $condition = DataSourceInterface::CONDITION_AND): void
    {
        switch ($condition) {
            case DataSourceInterface::CONDITION_AND:
                $parentNode = $this->queryBuilder->andWhere();

                break;
            case DataSourceInterface::CONDITION_OR:
                $parentNode = $this->queryBuilder->orWhere();

                break;
            default:
                throw new \RuntimeException(sprintf(
                    'Unknown restrict condition "%s"',
                    $condition,
                ));
        }

        $visitor = new ExpressionVisitor($this->queryBuilder);
        $visitor->dispatch($expression, $parentNode);
    }

    public function getExpressionBuilder(): ExpressionBuilderInterface
    {
        return $this->expressionBuilder;
    }

    public function getData(Parameters $parameters)
    {
        if (!class_exists(QueryAdapter::class)) {
            throw new \LogicException('Pagerfanta PHPCR-ODM adapter is not available. Try running "composer require pagerfanta/doctrine-phpcr-odm-adapter".');
        }

        $orderBy = $this->queryBuilder->orderBy();
        foreach ($this->expressionBuilder->getOrderBys() as $field => $direction) {
            if (is_int($field)) {
                $field = $direction;
                $direction = 'asc';
            }

            // todo: validate direction?
            $direction = strtolower($direction);
            $orderBy->{$direction}()->field(sprintf('%s.%s', Driver::QB_SOURCE_ALIAS, $field));
        }

        $paginator = new Pagerfanta(new QueryAdapter($this->queryBuilder));
        $paginator->setCurrentPage((int) $parameters->get('page', 1));

        return $paginator;
    }
}
