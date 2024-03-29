<?php

include_once('Element.php');
include_once('Document.php');

class ElementXRef extends Element
{
    /**
     * @return string
     */
    public function getId()
    {
        return $this->getContent();
    }

    /**
     * @return mixed
     */
    public function getObject()
    {
        return $this->document->getObjectById($this->getId());
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function equals($value)
    {
        $id = ($value instanceof ElementXRef) ? $value->getId() : $value;

        return $this->getId() == $id;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return '#Obj#' . $this->getId();
    }

    /**
     * @param string   $content
     * @param Document $document
     * @param int      $offset
     *
     * @return bool|ElementXRef
     */
    public static function parse($content, Document $document = null, &$offset = 0)
    {
        if (preg_match('/^\s*(?P<id>[0-9]+\s+[0-9]+\s+R)/s', $content, $match)) {
            $id = $match['id'];
            $offset += strpos($content, $id) + strlen($id);
            $id = str_replace(' ', '_', rtrim($id, ' R'));

            return new self($id, $document);
        }

        return false;
    }
}
