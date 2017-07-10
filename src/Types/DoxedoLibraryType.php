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

class DoxedoLibraryType extends BaseType implements TypeInterface
{
    protected $name = 'doxedo-library';
    protected $icon = 'https://upr.io/OdX9Lp.jpg';
    protected $urlPattern = '/^http(s)?:\/\/www.doxedo.com\/(?P<owner>\S+)\/(?P<library>\S+)$/';
    protected $defaultSourceName = 'doxedo';
    protected $identifiers = ['owner', 'library'];
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
                'title',
                TypeProperty::TYPE_STRING,
                TypeProperty::FLAG_REMOTE
            )
            ->defineTab(
                'home',
                'Home',
                TypeTab::TYPE_TEMPLATE
            )
            ->defineTab(
                'topics',
                'Topics',
                TypeTab::TYPE_TEMPLATE
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


    public function tabHome(Entry $entry)
    {
        $client = $entry->getSource()->getClient();
        $topic = $client->getTopic(
            $entry->getPropertyValue('owner') .
            '/' .
            $entry->getPropertyValue('library'),
            'home'
        );
        $text = $topic->getVersion()->getContent();
        $html = $this->index->renderMarkdown($text, $entry);
        return $this->index->render('@Index/types/doxedo-topic/view.html.twig', ['entry' => $entry, 'html' => $html]);

        //return $this->index->render('@Index/types/doxedo-library/index.html.twig', ['entry' => $entry]);
    }

    public function tabTopics(Entry $entry)
    {
        $entries = $this->index->getStore()->getEntriesOfTypeByProperty('doxedo-topic', 'parent_library', $entry->getFqen());
        return $this->index->render('@Index/entries/index.html.twig', ['entries' => $entries]);
    }
}
