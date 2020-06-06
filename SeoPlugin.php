<?php

namespace nyansapow\plugins\contrib\seo;

use nyansapow\Plugin;
use nyansapow\events\PageOutputGenerated;

class SeoPlugin extends Plugin
{
    public function getEvents()
    {
        return [
            PageOutputGenerated::class => [$this, 'injectTags'],
        ];
    }

    /**
     * @param $dom
     */
    private function getHeader(\DOMDocument $dom)
    {
        $heads = $dom->getElementsByTagName("head");
        if ($heads->count() == 0) {
            $newHead = $dom->createElement("head");
            $dom->getElementsByTagName("html")->item(0)->appendChild($newHead);
            return $newHead;
        }
        return $heads->item(0);
    }

    /**
     * @param $dom
     * @param $key
     * @param $value
     * @param string $tag
     */
    private function getMetaTag(\DOMDocument $dom, string $value, string $content, string $attribute = 'name')
    {
        $tag = $dom->createElement('meta');
        $tag->setAttribute($attribute, $value);
        $tag->setAttribute('content', $content);
        return $tag;
    }

    /**
     * @param $metaData
     * @param $head
     */
    private function setDescription(array $metaData, \DOMNode $head)
    {
        if (!isset($metaData['frontmatter']['description'])) {
            return;
        }
        $description = $metaData['frontmatter']['description'];
        $head->appendChild($this->getMetaTag($head->ownerDocument, 'description', $description));
        $head->appendChild($this->getMetaTag($head->ownerDocument, 'og:desciption', $description));
    }

    private function setTitle(array $metaData, \DOMNode $head)
    {
        if (!isset($metaData['title'])) {
            return;
        }
        $head->appendChild($this->getMetaTag($head->ownerDocument, 'og:title', $metaData['title']));
    }

    private function setKeywords(array $metaData, \DOMNode $head)
    {
        if (!isset($metaData['frontmatter']['tags'])) {
            return;
        }
        $head->appendChild(
            $this->getMetaTag($head->ownerDocument, 'keywords',
            implode(", ", $metaData['frontmatter']['tags']))
        );
    }

    private function setImage(array $metaData, \DOMNode $head)
    {
        if(!isset($metaData['frontmatter']['image'])) {
            return;
        }
        $head->appendChild($this->getMetaTag($head->ownerDocument, 'og:image', $metaData['frontmatter']['image']));
    }

    public function injectTags(PageOutputGenerated $event)
    {
        try {
            $dom = $event->getDOM();
        } catch (\TypeError $error) {
            return;
        }
        $page = $event->getPage();
        $metaData = $page->getMetaData();
        $headTag = $this->getHeader($dom);
        $this->setDescription($metaData, $headTag);
        $this->setTitle($metaData, $headTag);
        $this->setKeywords($metaData, $headTag);
        $this->setImage($metaData, $headTag);
        $headTag->appendChild($this->getMetaTag($dom, 'twitter:card', 'summary'));
    }
}
