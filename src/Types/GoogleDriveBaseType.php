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

abstract class GoogleDriveBaseType extends BaseType implements TypeInterface
{
    protected $identifiers = ['file_id'];
    protected $defaultSourceName = 'google-drive';


    public function configure()
    {
        $this
            ->defineProperty(
                'file_id',
                TypeProperty::TYPE_STRING,
                TypeProperty::FLAG_IDENTIFIER
            )
            ->defineProperty(
                'title',
                TypeProperty::TYPE_STRING,
                TypeProperty::FLAG_REMOTE
            )
            ->defineProperty(
                'parent_folder',
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

        $client = $source->getClient();

        $pageToken= null;
        $optParams = array(
            'fileId' => '',
            'pageToken' => $pageToken
        );
        $results = $source->getService()->files->get(
            $identifiers['file_id']->getValue(),
            ['fields' => 'id, name, parents, properties, mimeType, webViewLink, webContentLink, permissions, lastModifyingUser, modifiedTime']
        );


        /*
        $results = $service->files->export(
            $identifiers['file_id']->getValue(),
            'text/html',
            ['alt' => 'media']
        );
        $content = $results->getBody()->getContents();
        */


        $properties[] = new EntryProperty(
            $this->getTypeProperty('title'),
            $results['name']
        );

        $parentId = null;
        foreach ($results['parents'] as $parent) {
            $parentId = $parent;
        }

        if ($parentId) {
            $properties[] = new EntryProperty(
                $this->getTypeProperty('parent_folder'),
                'github-folder:' . $source->getName() . ':' . $parentId
            );
        }


        return $properties;


    }
}
