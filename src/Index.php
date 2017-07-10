<?php

namespace Index;

use Symfony\Component\HttpFoundation\Response;
use Index\Model\TypeInterface;
use Index\Model\SourceInterface;
use RuntimeException;
use Parsedown;


class Index
{
    protected $store;

    public function __construct($store)
    {
        $this->store = $store;
        $store->setIndex($this);
    }

    protected $twig;
    public function setTwig($twig)
    {
        $this->twig = $twig;
    }
    protected $urlGenerator;
    public function setUrlGenerator($urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
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
        $html = $this->twig->render($filename, $data);
        $response = new Response(
            $html,
            Response::HTTP_OK,
            array('content-type' => 'text/html')
        );
        return $response;
    }


    public function renderMarkdown($text, $entry)
    {
        $parsedown = new Parsedown();

        // preprocess mediawiki style links (convert mediawiki style links into markdown links)
        preg_match_all('/\[\[(.+?)\]\]/u', $text, $matches);
        foreach ($matches[1] as $match) {
            $content = (string)$match;
            $part = explode('|', $content);
            $label = $part[0];
            $link = null;
            if (count($part)>1) {
                $label = $part[1];
                $link = $part[0];
            }
            if (!$link) {
                $link = $label;
            }
            $link = trim(strtolower($link));
            $link = str_replace(' ', '-', $link);
            $text = str_replace('[[' . $content . ']]', '[' . $label . '](' . $link . ')', $text);
        }

        $html = $parsedown->text($text);

        // Fix up `language-` prefix for highlight.js
        $html = str_replace('language-', '', $html);


        // Fix hyperlinks

        $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
        if (preg_match_all("/$regexp/siU", $html, $matches, PREG_SET_ORDER)) {
            foreach($matches as $match) {
                //print_r($match);
                $content = $match[0];
                $link = $match[2];
                $label = $match[3];

                // auto add http:// prefix
                if (substr($link, 0, 4)=='www.') {
                    $link = 'http://' . $link;
                }
                $type = 'internal';
                if (substr($link, 0, 4)=='http') {
                    $type = 'external';

                }
                if ($type == 'internal') {

                    //$topicRepo = $app->getTopicRepository();
                    $fqen = 'doxedo-topic:' . $entry->getSource()->getName() . ':' . $entry->getPropertyValue('owner') . ',' . $entry->getPropertyValue('library') . ',' . $link;
                    $id = $this->getStore()->getEntryIdByFqen($fqen);
                    if ($id==0) {
                        $type = 'broken';
                    }
                    $link = $this->urlGenerator->generate(
                        'index_entry_view',
                        array(
                            'fqen' => $fqen
                        )
                    );
                }
                $o = '<a href="' . $link . '" class="linktype-' . $type . '"';
                if ($type=='external') {
                    $o .= ' target="_blank"';
                } else {
                    $o .= ' target="_top"';
                }
                $o .= '>' . $label . '</a>';
                $html = str_replace($content, $o, $html);
            }
        }
        return $html;
    }
}
