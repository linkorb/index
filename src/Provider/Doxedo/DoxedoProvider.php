<?php

namespace Index\Provider\Doxedo;

use Index\Model\ProviderInterface;

class DoxedoProvider implements ProviderInterface
{
    public function getTypes()
    {
        return [
            \Index\Provider\Doxedo\Type\DoxedoLibraryType::class,
            \Index\Provider\Doxedo\Type\DoxedoTopicType::class
        ];
    }
}
