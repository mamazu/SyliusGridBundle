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

namespace Sylius\Component\Grid\View;

use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Parameters;
use Webmozart\Assert\Assert;

class GridView implements GridViewInterface
{
    /** @var mixed */
    private $data;

    private Grid $definition;

    private Parameters $parameters;

    /**
     * @param mixed $data
     */
    public function __construct($data, Grid $definition, Parameters $parameters)
    {
        $this->data = $data;
        $this->definition = $definition;
        $this->parameters = $parameters;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getDefinition(): Grid
    {
        return $this->definition;
    }

    public function getParameters(): Parameters
    {
        return $this->parameters;
    }

    public function getSortingOrder(string $fieldName): ?string
    {
        $this->assertFieldIsSortable($fieldName);

        $currentSorting = $this->getCurrentlySortedBy();

        if (array_key_exists($fieldName, $currentSorting)) {
            return $currentSorting[$fieldName];
        }

        $definedSorting = $this->definition->getSorting();

        return reset($definedSorting) ?: null;
    }

    public function isSortedBy(string $fieldName): bool
    {
        $this->assertFieldIsSortable($fieldName);

        if ($this->parameters->has('sorting')) {
            return array_key_exists($fieldName, $this->parameters->get('sorting'));
        }

        $sortingDefinition = $this->getDefinition()->getSorting();
        $sortedFields = array_keys($sortingDefinition);

        return $fieldName === array_shift($sortedFields);
    }

    private function getCurrentlySortedBy(): array
    {
        return $this->parameters->has('sorting')
            ? array_merge($this->definition->getSorting(), $this->parameters->get('sorting'))
            : $this->definition->getSorting()
        ;
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function assertFieldIsSortable(string $fieldName): void
    {
        Assert::true($this->definition->hasField($fieldName), sprintf('Field "%s" does not exist.', $fieldName));
        Assert::true(
            $this->definition->getField($fieldName)->isSortable(),
            sprintf('Field "%s" is not sortable.', $fieldName),
        );
    }
}
