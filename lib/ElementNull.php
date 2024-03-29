<?php

include_once('Element.php');
include_once('Document.php');

class ElementNull extends Element
{
    /**
     * @param string   $value
     * @param Document $document
     */
    public function __construct($value, Document $document = null)
    {
        parent::__construct(null, null);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return 'null';
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function equals($value)
    {
        return ($this->getContent() === $value);
    }

    /**
     * @param string   $content
     * @param Document $document
     * @param int      $offset
     *
     * @return bool|ElementNull
     */
    public static function parse($content, Document $document = null, &$offset = 0)
    {
        if (preg_match('/^\s*(null)/s', $content, $match)) {
            $offset += strpos($content, 'null') + strlen('null');

            return new self(null, $document);
        }

        return false;
    }
}
