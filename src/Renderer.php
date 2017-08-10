<?php

namespace Index;

use Symfony\Component\HttpFoundation\Response;
use ReflectionClass;
use Parsedown;

class Renderer
{
    protected $index;
    protected $twig;
    protected $urlGenerator;

    public function __construct(Index $index, $twig, $urlGenerator)
    {
        $this->index = $index;
        $this->twig = $twig;
        $this->urlGenerator = $urlGenerator;

        // Auto-register all the provider `templates` directories
        // in a newly instantiated twig loader
        $loader = new \Twig_Loader_Filesystem();
        $twig->getLoader()->addLoader($loader);

        foreach ($index->getProviders() as $provider) {
            $fqcn = get_class($provider);
            $part = explode('\\', $fqcn);
            $providerName = array_pop($part);

            $reflector = new ReflectionClass($fqcn);

            $path = dirname($reflector->getFileName()) . '/templates/';
            if (file_exists($path)) {
                $loader->addPath(
                    $path,
                    $providerName
                );
            }
        }
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
                    $e = $this->index->getStore()->getEntryByFqen($fqen);
                    if (!$e) {
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
