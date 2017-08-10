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

class GoogleDriveDocumentType extends GoogleDriveBaseType
{
    protected $name = 'google-drive-document';
    protected $icon = 'https://png.icons8.com/google-docs/win10/1600';
    protected $urlPattern = '/^http(s)?:\/\/docs.google.com\/document\/d\/(?P<file_id>\S+)\//';
}
