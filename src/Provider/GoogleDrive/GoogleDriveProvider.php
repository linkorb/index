<?php

namespace Index\Provider\GoogleDrive;

use Index\Model\ProviderInterface;

class GoogleDriveProvider implements ProviderInterface
{
    public function getTypes()
    {
        return [
            \Index\Provider\GoogleDrive\Type\GoogleDriveDocumentType::class,
            \Index\Provider\GoogleDrive\Type\GoogleDriveFileType::class,
            \Index\Provider\GoogleDrive\Type\GoogleDriveSpreadsheetType::class,
            \Index\Provider\GoogleDrive\Type\GoogleDriveFolderType::class,
        ];
    }
}
