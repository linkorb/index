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

class GoogleDriveFileType extends GoogleDriveBaseType
{
    protected $name = 'google-drive-file';
    protected $icon = 'https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/png/512/file.png';
    protected $urlPattern = '/^http(s)?:\/\/drive.google.com\/file\/d\/(?P<file_id>\S+)\//';
}
