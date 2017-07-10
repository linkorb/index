<?php

namespace Index\Types;

use Index\Model\TypeInterface;
use Index\Model\Entry;
use Index\Model\EntryProperty;
use Index\Model\BaseType;
use Index\Model\TypeProperty;
use Index\Model\SourceInterface;
use RuntimeException;

class DoxedoTopicType extends BaseType implements TypeInterface
{
    protected $name = 'doxedo-topic';
    protected $icon = 'http://wfarm3.dataknet.com/static/resources/icons/set110/3d5197b6.png';
    protected $urlPattern = '/^http(s)?:\/\/www.doxedo.com\/(?P<owner>\S+)\/(?P<library>\S+)\/topics\/(?P<topic_name>\S+)$/';
    protected $defaultSourceName = 'doxedo';
    protected $identifiers = ['owner', 'library', 'topic_name'];

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
                'topic_name',
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
        return $entry->getPropertyValue('owner') . '/' . $entry->getPropertyValue('library') . ' ' . $entry->getPropertyValue('title');
    }

    public function fetchRemoteProperties(SourceInterface $source, $identifiers = [])
    {
        $properties = [];

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

        return $properties;
    }
}
