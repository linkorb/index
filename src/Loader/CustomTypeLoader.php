<?php

namespace Index\Loader;

use Symfony\Component\Yaml\Yaml;
use Index\Model\CustomType;
use Index\Model\TypeProperty;

class CustomTypeLoader
{
    public function loadDirectory($index, $path)
    {
        $files = glob($path . '/*.yml');
        //print_r($files);
        $types = [];
        foreach ($files as $file)
        {
            $type = $this->loadFile($index, $file);
            $types[] = $type;
        }
        return $types;
    }

    public function loadFile($index, $filename)
    {
        $yaml = file_get_contents($filename);
        $data = Yaml::parse($yaml);
        foreach ($data as $typeName => $typeData) {
            $type = new CustomType($index);
            $type->setName($typeName);
            if (isset($typeData['icon'])) {
                $type->setIcon($typeData['icon']);
            }
            if (isset($typeData['type'])) {
                $type->setType($typeData['type']);
            }
            if (isset($typeData['properties'])) {
                foreach ($typeData['properties'] as $name => $details) {
                    $flags = 0;
                    if (isset($details['required'])) {
                        $flags |= TypeProperty::FLAG_REQUIRED;
                    }
                    if (isset($details['multiple'])) {
                        $flags |= TypeProperty::FLAG_MULTIPLE;
                    }

                    $p = new TypeProperty($name, $details['type'], $flags);
                    $type->addTypeProperty($p);
                }
            }
            $index->addType($type);
        }
        return $index;
    }

}
