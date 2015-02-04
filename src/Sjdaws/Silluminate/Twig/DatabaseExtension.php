<?php

namespace Sjdaws\Silluminate\Twig;

use Sjdaws\Silluminate\Twig\DoctrineExtension;
use SqlFormatter;

class DatabaseExtension extends DoctrineExtension
{
    /**
     * Define our functions
     *
     * @return array
     */
    public function getFilters()
    {
        return array_merge(parent::getFilters(), array(
            new \Twig_SimpleFilter('pretty_replace_query_parameters', array($this, 'prettyReplaceQueryParameters')),
        ));
    }

    /**
     * Replace parameters in a query, then format it
     *
     * @param string $query
     * @param array $parameters
     * @param bool $highlight
     * @return string
     */
    public function prettyReplaceQueryParameters($query, array $parameters, $highlight = true)
    {
        $query = $this->replaceQueryParameters($query, $parameters, $highlight);

        return SqlFormatter::format($query, $highlight);
    }

    /**
     * Get the name of the extension
     *
     * @return string
     */
    public function getName()
    {
        return 'database_extension';
    }
}
