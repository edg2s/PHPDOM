<?php
namespace Rowbot\DOM\Element\HTML;

use Rowbot\DOM\Document;

/**
 * @see https://html.spec.whatwg.org/multipage/scripting.html#the-template-element
 */
class HTMLTemplateElement extends HTMLElement
{
    protected $content;

    protected function __construct()
    {
        parent::__construct();

        $doc = $this->nodeDocument
            ->getAppropriateTemplateContentsOwnerDocument();
        $this->content = $doc->createDocumentFragment();
        $this->content->setHost($this);
    }

    public function __get(string $name)
    {
        switch ($name) {
            case 'content':
                return $this->content;

            default:
                return parent::__get($name);
        }
    }

    public function doAdoptingSteps(Document $oldDocument)
    {
        $doc = $this->nodeDocument
            ->getAppropriateTemplateContentsOwnerDocument();
        $doc->doAdoptNode($this->content);
    }

    public function onCloneNode(
        HTMLTemplateElement $copy,
        Document $document,
        bool $cloneChildren
    ) {
        if (!$cloneChildren) {
            return;
        }

        $copiedContents = $this->content->cloneNodeInternal(
            $copy->content->nodeDocument,
            true
        );
        $copy->content->appendChild($copiedContents);
    }
}
