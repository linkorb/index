<?php

namespace Index\Types;

use Index\Model\TypeInterface;
use Index\Model\Entry;
use Index\Model\EntryProperty;
use Index\Model\BaseType;
use Index\Model\TypeProperty;
use Index\Model\IdTypeProperty;
use Index\Source\SourceInterface;
use RuntimeException;

class AirtableRecordType extends BaseType implements TypeInterface
{
    protected $name = 'airtable-record';
    protected $icon = 'https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/png/512/record.png';
    protected $urlPattern = '/^http(s)?:\/\/airtable.com\/(?P<table>\S+)\/(?P<view>\S+)\/(?P<record>\S+)$/';
    protected $identifiers = ['table', 'view', 'record'];
    protected $defaultSourceName = 'airtable';


    public function configure()
    {
        $this
            ->defineProperty(
                'table',
                TypeProperty::TYPE_STRING,
                TypeProperty::FLAG_IDENTIFIER
            )
            ->defineProperty(
                'view',
                TypeProperty::TYPE_STRING,
                TypeProperty::FLAG_IDENTIFIER
            )
            ->defineProperty(
                'record',
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
        return $entry->getPropertyValue('title');
    }

    public function fetchRemoteProperties(SourceInterface $source, $identifiers = [])
    {
        $properties = [];

        //$client = $source->getClient();
        /*
        $prApi = $client->api('pull_request');

        $data = $prApi->show(
            $identifiers['owner']->getValue(),
            $identifiers['repository']->getValue(),
            $identifiers['number']->getValue()
        );
        //print_r($data); exit();

        $properties[] = new EntryProperty(
            $this->getTypeProperty('title'),
            $data['title']
        );
        */
        return $properties;
    }
}
