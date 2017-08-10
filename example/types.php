<?php

require_once 'common.php';

$types = $index->getTypes();
foreach ($types as $type) {
    echo $type->getName() . "\n";
    foreach ($type->getTypeProperties() as $p) {
        echo '  - ' . $p->getName() . ' = ' . $p->presentFlags() . "\n";
    }
}
