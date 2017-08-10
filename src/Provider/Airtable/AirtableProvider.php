<?php

namespace Index\Provider\Airtable;

use Index\Model\ProviderInterface;

class AirtableProvider implements ProviderInterface
{
    public function getTypes()
    {
        return [
            \Index\Provider\Airtable\Type\AirtableRecordType::class,
            \Index\Provider\Airtable\Type\AirtableTableType::class
        ];
    }
}
