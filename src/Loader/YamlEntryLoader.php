<?php

namespace Index\Loader;

use Index\Index;
use Index\Source\SourceInterface;
use Symfony\Component\Yaml\Yaml;
use RuntimeException;

class YamlEntryLoader extends ArrayEntryLoader
{
    public function loadFile(Index $index, SourceInterface $source, $filename)
    {
        if (!file_exists($filename)) {
            throw new RuntimeException("File not found: " . $filename);
        }
        $yaml = file_get_contents($filename);
        $data = Yaml::parse($yaml);
        if (!$data) {
            throw new RuntimeException('Yaml parse error');
        }
        return $this->loadData($index, $source, $data);
    }
}
