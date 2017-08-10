<?php

namespace Index\Provider\GitHub\Type;

use Index\Model\TypeInterface;
use Index\Model\Entry;
use Index\Model\EntryProperty;
use Index\Model\BaseType;
use Index\Model\TypeProperty;
use Index\Model\TypeTab;
use Index\Source\SourceInterface;
use RuntimeException;

class GitHubRepositoryType extends BaseType implements TypeInterface
{
    protected $name = 'github-repository';
    protected $icon = 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/00/Octicons-repo.svg/2000px-Octicons-repo.svg.png';
    protected $urlPattern = '/^http(s)?:\/\/github.com\/(?P<owner>\S+)\/(?P<repository>\S+)$/';
    protected $identifiers = ['owner', 'repository'];
    protected $defaultSourceName = 'github';
    protected $index;
    protected $renderer;

    public function configure()
    {
        $this
            ->defineProperty(
                'owner',
                TypeProperty::TYPE_STRING,
                TypeProperty::FLAG_IDENTIFIER|TypeProperty::FLAG_SEARCH
            )
            ->defineProperty(
                'repository',
                TypeProperty::TYPE_STRING,
                TypeProperty::FLAG_IDENTIFIER|TypeProperty::FLAG_SEARCH
            )
            ->defineProperty(
                'private',
                TypeProperty::TYPE_BOOLEAN,
                TypeProperty::FLAG_REMOTE
            )
            ->defineProperty(
                'description',
                TypeProperty::TYPE_STRING,
                TypeProperty::FLAG_REMOTE|TypeProperty::FLAG_SEARCH
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
            ->defineTab(
                'commits',
                'Commits',
                TypeTab::TYPE_TEMPLATE
            )
            ->defineTab(
                'pulls',
                'Pull requests',
                TypeTab::TYPE_TEMPLATE
            )
            ->defineTab(
                'issues',
                'Issues',
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
        return $this->render('types/github-repository/readme.html.twig', ['entry' => $entry]);
    }

    public function tabPulls(Entry $entry)
    {
        $entries = $this->index->getStore()->getEntriesOfTypeByProperty('github-pull-request', 'parent_repository', $entry->getFqen());
        return $this->render('@Index/entries/index.html.twig', ['entries' => $entries]);
    }
}
