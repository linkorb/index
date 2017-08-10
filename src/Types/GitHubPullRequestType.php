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

class GitHubPullRequestType extends BaseType implements TypeInterface
{
    protected $name = 'github-pull-request';
    protected $icon = 'https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/png/512/pull-request.png';
    protected $urlPattern = '/^http(s)?:\/\/github.com\/(?P<owner>\S+)\/(?P<repository>\S+)\/pull\/(?P<number>\d+)/';
    protected $identifiers = ['owner', 'repository', 'number'];
    protected $defaultSourceName = 'github';

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
                'number',
                TypeProperty::TYPE_STRING,
                TypeProperty::FLAG_IDENTIFIER
            )
            ->defineProperty(
                'parent_repository',
                TypeProperty::TYPE_FQEN,
                TypeProperty::FLAG_REMOTE
            )
            ->defineProperty(
                'state',
                TypeProperty::TYPE_STRING,
                TypeProperty::FLAG_REMOTE | TypeProperty::FLAG_REQUIRED
            )
            ->defineProperty(
                'title',
                TypeProperty::TYPE_STRING,
                TypeProperty::FLAG_REMOTE|TypeProperty::FLAG_SEARCH
            )
            ->defineProperty(
                'user',
                TypeProperty::TYPE_STRING,
                TypeProperty::FLAG_REMOTE
            )
            ->defineProperty(
                'created_at',
                TypeProperty::TYPE_DATE,
                TypeProperty::FLAG_REMOTE
            )
            ->defineProperty(
                'mergeable',
                TypeProperty::TYPE_BOOLEAN,
                TypeProperty::FLAG_REMOTE
            )
            ->defineProperty(
                'merged',
                TypeProperty::TYPE_BOOLEAN,
                TypeProperty::FLAG_REMOTE
            )
            ->defineProperty(
                'commits',
                TypeProperty::TYPE_INTEGER,
                TypeProperty::FLAG_REMOTE
            )
        ;
    }

    public function getDisplayName(Entry $entry)
    {
        return $entry->getPropertyValue('owner') . '/' . $entry->getPropertyValue('repository') . ' PR' . $entry->getPropertyValue('number') . ': ' . $entry->getPropertyValue('title');
    }

    public function fetchRemoteProperties(SourceInterface $source, $identifiers = [])
    {
        $properties = [];

        $client = $source->getClient();
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

        $properties[] = new EntryProperty(
            $this->getTypeProperty('parent_repository'),
            'github-repository:' . $source->getName() . ':' . $identifiers['owner']->getValue() . ',' . $identifiers['repository']->getValue()
        );

        $properties[] = new EntryProperty(
            $this->getTypeProperty('state'),
            $data['state']
        );
        $properties[] = new EntryProperty(
            $this->getTypeProperty('user'),
            $data['user']['login']
        );
        $properties[] = new EntryProperty(
            $this->getTypeProperty('created_at'),
            $data['created_at']
        );
        $properties[] = new EntryProperty(
            $this->getTypeProperty('mergeable'),
            $data['mergeable']
        );
        $properties[] = new EntryProperty(
            $this->getTypeProperty('merged'),
            $data['merged']
        );
        $properties[] = new EntryProperty(
            $this->getTypeProperty('commits'),
            $data['commits']
        );

        return $properties;
    }
}
