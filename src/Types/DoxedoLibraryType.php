<?php

namespace Index\Types;

use Index\Model\TypeInterface;
use Index\Model\Entry;
use Index\Model\EntryProperty;
use Index\Model\BaseType;
use Index\Model\TypeProperty;
use Index\Model\SourceInterface;
use RuntimeException;

class DoxedoLibraryType extends BaseType implements TypeInterface
{
    protected $name = 'doxedo-library';
    protected $icon = 'https://upr.io/OdX9Lp.jpg';
    protected $urlPattern = '/^http(s)?:\/\/www.doxedo.com\/(?P<owner>\S+)\/(?P<library>\S+)$/';
    protected $defaultSourceName = 'doxedo';
    protected $identifiers = ['owner', 'library'];

    public function __construct()
    {
        $this
            ->defineProperty(
                'owner',
                TypeProperty::TYPE_STRING,
                TypeProperty::FLAG_IDENTIFIER
            )
            ->defineProperty(
                'library',
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
        return $entry->getPropertyValue('owner') . '/' . $entry->getPropertyValue('library');
    }

    public function fetchRemoteProperties(SourceInterface $source, $identifiers = [])
    {
        $properties = [];
        /*
        $client = $source->getClient();
        $topic = $client->getTopic(
            $identifiers['owner']->getValue() .
            '/' .
            $identifiers['library']->getValue(),
            $identifiers['topic_name']->getValue()
        );
        //print_r($topic);exit();
        $version = $topic->getVersion();

        $properties[] = new EntryProperty(
            $this->getTypeProperty('title'),
            $version->getTitle()
        );
        */

        return $properties;
    }
}
