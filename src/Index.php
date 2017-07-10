<?php

namespace Index;

use Symfony\Component\HttpFoundation\Response;
use Index\Model\TypeInterface;
use Index\Model\SourceInterface;
use RuntimeException;

class Index
{
    protected $store;

    public function __construct($store)
    {
        $this->store = $store;
        $store->setIndex($this);
    }

    public function getStore()
    {
        return $this->store;
    }

    protected $types = [];

    public function addType(TypeInterface $type)
    {
        if (isset($this->types[$type->getName()])) {
            throw new RuntimeException("Type already registered: " . $type->getName());
        }
        $this->types[$type->getName()] = $type;
    }

    public function getTypes()
    {
        return $this->types;
    }

    public function getTypeByName($name)
    {
        if (!isset($this->types[$name])) {
            throw new RuntimeException("Unknown type: " . $name);
        }
        return $this->types[$name];
    }

    public function findTypesByUrl($url)
    {
        $types = [];
        foreach ($this->getTypes() as $type) {
            if ($type->supportsUrl($url)) {
                $types[] = $type;
            }
        }
        return $types;
    }

    protected $sources = [];
    public function addSource(SourceInterface $source)
    {
        $this->sources[$source->getName()] = $source;
    }

    public function getSource($name)
    {
        if (!isset($this->sources[$name])) {
            throw new RuntimeException("Unknown source: " . $name);
        }
        return $this->sources[$name];
    }

    public function getSources()
    {
        return $this->sources;
    }

    public function render($filename, $data)
    {
        $loader = new \Twig_Loader_Filesystem(__DIR__ . '/../templates');
        $twig = new \Twig_Environment($loader, []);
        $html = $twig->render($filename, $data);
        $response = new Response(
            $html,
            Response::HTTP_OK,
            array('content-type' => 'text/html')
        );
        return $response;
    }
}
