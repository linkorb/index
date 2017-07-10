<?php

namespace Index\Types;

use Index\Model\TypeInterface;
use Index\Model\Entry;
use Index\Model\EntryProperty;
use Index\Model\BaseType;
use Index\Model\TypeProperty;
use Index\Model\SourceInterface;
use RuntimeException;

class AirtableTableType extends BaseType implements TypeInterface
{
    protected $name = 'airtable-table';
    protected $icon = 'https://apprecs.org/ios/images/app-icons/256/dc/914172636.jpg';
    protected $urlPattern = '/^http(s)?:\/\/airtable.com\/(?P<table_id>\S+)$/';
    protected $defaultSourceName = 'airtable';
    protected $identifiers = ['table_id'];

    public function __construct()
    {
        $this
            ->defineProperty(
                'table_id',
                TypeProperty::TYPE_STRING,
                TypeProperty::FLAG_IDENTIFIER
            )
            ->defineProperty(
                'title',
                TypeProperty::TYPE_STRING,
                TypeProperty::FLAG_REMOTE
            )
        ;
    }

    public function getDisplayName(Entry $entry)
    {
        return $entry->getPropertyValue('table_id') . ': ' . $entry->getPropertyValue('title');
    }

    public function fetchRemoteProperties(SourceInterface $source, $identifiers = [])
    {
        $properties = [];

        $data = $source->get($identifiers['table_id']->getValue());
        //print_r($data);
        return $properties;
    }
}
