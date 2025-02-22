<?php

namespace foonoo\plugins\foonoo\seo;

use Dom\Element;
use Dom\HTMLDocument;
use Dom\HTMLElement;
use foonoo\Plugin;
use foonoo\events\ContentLayoutApplied;
use foonoo\sites\AbstractSite;

/**
 * The SEO plugin injects code that provides more information about pages to search engines.
 */
class SeoPlugin extends Plugin
{
    private AbstractSite $site;

    public function getEvents()
    {
        return [ContentLayoutApplied::class => [$this, 'injectTags']];
    }

    /**
     * @param $dom
     */
//    private function getHeader(HTMLDocument $dom)
//    {
//        $head = $dom->querySelector("head");
//        if ($head === null) {
//            $newHead = $dom->createElement("head");
//            $dom->h //getElementsByTagName("html")->item(0)->appendChild($newHead);
//            return $newHead;
//        }
//        return $heads->item(0);
//    }

    /**
     * @param $dom
     * @param $key
     * @param $value
     * @param string $tag
     */
    private function getMetaTag(HTMLDocument $dom, string $value, string $content, string $attribute = 'name') : Element
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
    private function setDescription(array $metaData, HTMLElement $head) : void
    {
        if (!isset($metaData['frontmatter']['description'])) {
            return;
        }
        $description = $metaData['frontmatter']['description'];
        $head->appendChild($this->getMetaTag($head->ownerDocument, 'description', $description));
        $head->appendChild($this->getMetaTag($head->ownerDocument, 'og:desciption', $description));
    }

    private function setTitle(array $metaData, Element $head) : void
    {
        if (!isset($metaData['title'])) {
            return;
        }
        $head->appendChild($this->getMetaTag($head->ownerDocument, 'og:title', $metaData['title']));
    }

    private function setKeywords(array $metaData, HTMLElement $head) : void
    {
        if (!isset($metaData['frontmatter']['tags'])) {
            return;
        }
        $head->appendChild(
            $this->getMetaTag($head->ownerDocument, 'keywords',
                implode(", ", $metaData['frontmatter']['tags']))
        );
    }

    private function setImage(AbstractSite $site, array $metaData, HTMLElement $head) : void
    {
        if (!isset($metaData['frontmatter']['image'])) {
            return;
        }
        $imagePath = $site->getSourcePath("_foonoo/images/{$metaData['frontmatter']['image']}");
        if (file_exists($imagePath)) {
            $head->appendChild(
                $this->getMetaTag(
                    $head->ownerDocument, 'og:image',
                    ($site->getMetaData()['url'] ?? '') . "/images/{$metaData['frontmatter']['image']}"
                )
            );
        }
    }

    private function setSiteDetails(array $siteMetadata, array $postMetadata, HTMLElement $head) : void
    {
        if(isset($siteMetadata['name'])) {
            $head->appendChild($this->getMetaTag($head->ownerDocument, 'og:site_name', $siteMetadata['name']));
        }
        if(isset($siteMetadata['url']) && isset($postMetadata['path'])) {
            $head->appendChild(
                $this->getMetaTag($head->ownerDocument, 'og:url', $siteMetadata['url'] . "/" . $postMetadata['path'])
            );
        }
    }

    public function injectTags(ContentLayoutApplied $event) : void
    {
        try {
            $dom = $event->getDOM();
        } catch (\TypeError $error) {
            return;
        }
        $page = $event->getContent();
        $site = $event->getSite();
        $metaData = $page->getMetaData();
        //$headTag = $this->getHeader($dom);
        $this->setDescription($metaData, $dom->head);
        $this->setTitle($metaData, $dom->head);
        $this->setKeywords($metaData, $dom->head);
        $this->setImage($site, $metaData, $dom->head);
        $this->setSiteDetails($site->getMetaData(), $metaData, $dom->head);
        $dom->head->appendChild($this->getMetaTag($dom, 'twitter:card', 'summary_large_image'));
    }
}
