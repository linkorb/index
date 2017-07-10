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

class GoogleDriveSpreadsheetType extends GoogleDriveBaseType
{
    protected $name = 'google-drive-spreadsheet';
    protected $icon = 'https://www.shareicon.net/data/512x512/2017/04/11/883761_file_512x512.png';
    protected $urlPattern = '/^http(s)?:\/\/docs.google.com\/spreadsheets\/d\/(?P<file_id>\S+)\//';
}
