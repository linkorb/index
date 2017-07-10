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

class GitHubRepositoryType extends BaseType implements TypeInterface
{
    protected $name = 'github-repository';
    protected $icon = 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/00/Octicons-repo.svg/2000px-Octicons-repo.svg.png';
    protected $urlPattern = '/^http(s)?:\/\/github.com\/(?P<owner>\S+)\/(?P<repository>\S+)$/';
    protected $identifiers = ['owner', 'repository'];
    protected $defaultSourceName = 'github';
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
                'repository',
                TypeProperty::TYPE_STRING,
                TypeProperty::FLAG_IDENTIFIER
            )
            ->defineProperty(
                'private',
                TypeProperty::TYPE_BOOLEAN,
                TypeProperty::FLAG_REMOTE
            )
            ->defineProperty(
                'description',
                TypeProperty::TYPE_STRING,
                TypeProperty::FLAG_REMOTE
            )
            ->defineProperty(
                'website',
                TypeProperty::TYPE_STRING,
                TypeProperty::FLAG_REMOTE
            )
            ->defineProperty(
                'created_at',
                TypeProperty::TYPE_DATE,
                TypeProperty::FLAG_REMOTE
            )
            ->defineTab(
                'readme',
                'README.md',
                TypeTab::TYPE_TEMPLATE
            )
        ;
    }

    public function getDisplayName(Entry $entry)
    {
        return $entry->getPropertyValue('owner') . '/' . $entry->getPropertyValue('repository') . ' ' . $entry->getPropertyValue('description');
    }

    public function fetchRemoteProperties(SourceInterface $source, $identifiers = [])
    {
        $properties = [];

        $client = $source->getClient();
        $api = $client->api('repository');

        $data = $api->show(
            $identifiers['owner']->getValue(),
            $identifiers['repository']->getValue()
        );
        //print_r($data); exit();


        $properties[] = new EntryProperty(
            $this->getTypeProperty('description'),
            $data['description']
        );
        if (isset($data['website'])) {
            $properties[] = new EntryProperty(
                $this->getTypeProperty('website'),
                $data['website']
            );
        }
        $properties[] = new EntryProperty(
            $this->getTypeProperty('private'),
            $data['private']
        );
        $properties[] = new EntryProperty(
            $this->getTypeProperty('created_at'),
            $data['created_at']
        );

        return $properties;
    }

    public function tabReadme(Entry $entry)
    {
        return $this->index->render('types/github-repository/readme.html.twig', ['entry' => $entry]);
    }
}
