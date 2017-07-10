<?php

namespace Index\Types;

use Index\Model\TypeInterface;
use Index\Model\Entry;
use Index\Model\EntryProperty;
use Index\Model\BaseType;
use Index\Model\TypeProperty;
use Index\Model\IdTypeProperty;
use Index\Model\SourceInterface;
use RuntimeException;

class GoogleDriveFolderType extends GoogleDriveBaseType
{
    protected $name = 'google-drive-folder';
    protected $icon = 'https://image.flaticon.com/icons/svg/61/61119.svg';
    protected $urlPattern = '/^http(s)?:\/\/drive.google.com\/drive\/folders\/(?P<file_id>\S+)$/';
}
