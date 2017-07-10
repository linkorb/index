<?php

namespace Index\Loader;

use Symfony\Component\Yaml\Yaml;
use Index\Model\CustomType;
use Index\Model\TypeProperty;

class CustomTypeLoader
{
    public function loadDirectory($path)
    {
        $files = glob($path . '/*.yml');
        //print_r($files);
        $types = [];
        foreach ($files as $file)
        {
            $type = $this->loadFile($file);
            $types[] = $type;
        }
        return $types;
    }

    public function loadFile($filename)
    {
        $yaml = file_get_contents($filename);
        $data = Yaml::parse($yaml);
        $type = new CustomType();
        $type->setName($data['name']);
        $type->setIcon($data['icon']);
        if (isset($data['properties'])) {
            foreach ($data['properties'] as $name => $details) {
                $flags = 0;
                if (isset($details['required'])) {
                    $flags &= TypeProperty::FLAG_REQUIRED;
                }
                if (isset($details['multiple'])) {
                    $flags &= TypeProperty::FLAG_MULTIPLE;
                }

                $p = new TypeProperty($name, $details['type'], $flags);
                $type->addTypeProperty($p);
            }
        }
        return $type;
    }

}
