<?php

namespace Index\Loader;

use Symfony\Component\Yaml\Yaml;
use RuntimeException;

class ConfigLoader
{
    public function loadFile($index, $filename)
    {
        if (!file_exists($filename)) {
            throw new RuntimeException("Config file not found: $filename");
        }
        $yaml = file_get_contents($filename);
        $data = Yaml::parse($yaml);

        if (isset($data['sources'])) {
            foreach ($data['sources'] as $name => $details) {
                $className = $details['class'];
                $arguments = $details['arguments'];
                $source = new $className($name, $arguments);
                $index->addSource($source);
            }
        }

        if (isset($data['types'])) {
            foreach ($data['types'] as $className) {
                $type = new $className($index);
                $index->addType($type);
            }
        }

        // Load custom types
        if (isset($data['type_directories'])) {
            foreach ($data['type_directories'] as $path) {
                $typeLoader = new \Index\Loader\CustomTypeLoader();
                $types = $typeLoader->loadDirectory($index, $path);
            }
        }

        return $index;
    }

}
