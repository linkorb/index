<?php

namespace Index\Types;

use Index\Model\TypeInterface;
use Index\Model\Entry;
use Index\Model\EntryProperty;
use Index\Model\BaseType;
use Index\Model\TypeProperty;
use Index\Model\TypeTab;
use Index\Model\SourceInterface;
use RuntimeException;

class DoxedoTopicType extends BaseType implements TypeInterface
{
    protected $name = 'doxedo-topic';
    protected $icon = 'http://wfarm3.dataknet.com/static/resources/icons/set110/3d5197b6.png';
    protected $urlPattern = '/^http(s)?:\/\/www.doxedo.com\/(?P<owner>\S+)\/(?P<library>\S+)\/topics\/(?P<topic_name>\S+)$/';
    protected $defaultSourceName = 'doxedo';
    protected $identifiers = ['owner', 'library', 'topic_name'];
    protected $index;

    public function __construct($index)
    {
        $this->index = $index;
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
            ->defineProperty(
                'parent_library',
                TypeProperty::TYPE_FQEN,
                TypeProperty::FLAG_REMOTE
            )
            ->defineTab(
                'view',
                'View',
                TypeTab::TYPE_TEMPLATE
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

        $properties[] = new EntryProperty(
            $this->getTypeProperty('parent_library'),
            'doxedo-library:' . $source->getName() . ':' . $identifiers['owner']->getValue() . ',' . $identifiers['library']->getValue()
        );

        return $properties;
    }

    public function tabView(Entry $entry)
    {
        $client = $entry->getSource()->getClient();
        $topic = $client->getTopic(
            $entry->getPropertyValue('owner') .
            '/' .
            $entry->getPropertyValue('library'),
            $entry->getPropertyValue('topic_name')
        );
        $text = $topic->getVersion()->getContent();
        $html = $this->index->renderMarkdown($text, $entry);
        return $this->index->render('@Index/types/doxedo-topic/view.html.twig', ['entry' => $entry, 'html' => $html]);
    }
}
