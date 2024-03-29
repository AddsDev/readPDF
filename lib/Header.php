<?php
include_once('ElementArray.php');
include_once('ElementMissing.php');
include_once('ElementStruct.php');
include_once('ElementXRef.php');


class Header
{
    /**
     * @var Document
     */
    protected $document = null;

    /**
     * @var Element[]
     */
    protected $elements = null;

    /**
     * @param Element[] $elements   List of elements.
     * @param Document  $document   Document.
     */
    public function __construct($elements = array(), Document $document = null)
    {
        $this->elements = $elements;
        $this->document = $document;
    }

    /**
     * Returns all elements.
     *
     * @return mixed
     */
    public function getElements()
    {
        foreach ($this->elements as $name => $element) {
            $this->resolveXRef($name);
        }

        return $this->elements;
    }

    /**
     * Used only for debug.
     *
     * @return array
     */
    public function getElementTypes()
    {
        $types = array();

        foreach ($this->elements as $key => $element) {
            $types[$key] = get_class($element);
        }

        return $types;
    }

    /**
     * @param bool $deep
     *
     * @return array
     */
    public function getDetails($deep = true)
    {
        $values   = array();
        $elements = $this->getElements();

        foreach ($elements as $key => $element) {
            if ($element instanceof Header && $deep) {
                $values[$key] = $element->getDetails($deep);
            } elseif ($element instanceof PDFObject && $deep) {
                $values[$key] = $element->getDetails(false);
            } elseif ($element instanceof ElementArray) {
                if ($deep) {
                    $values[$key] = $element->getDetails();
                }
            } elseif ($element instanceof Element) {
                $values[$key] = (string) $element;
            }
        }

        return $values;
    }

    /**
     * Indicate if an element name is available in header.
     *
     * @param string $name The name of the element
     *
     * @return bool
     */
    public function has($name)
    {
        if (array_key_exists($name, $this->elements)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $name
     *
     * @return Element|PDFObject
     */
    public function get($name)
    {
        if (array_key_exists($name, $this->elements)) {
            return $this->resolveXRef($name);
        }

        return new ElementMissing(null, null);
    }

    /**
     * Resolve XRef to object.
     *
     * @param string $name
     *
     * @return Element|PDFObject
     * @throws \Exception
     */
    protected function resolveXRef($name)
    {
        if (($obj = $this->elements[$name]) instanceof ElementXRef && !is_null($this->document)) {
            /** @var ElementXRef $obj */
            $object = $this->document->getObjectById($obj->getId());

            if (is_null($object)) {
                return new ElementMissing(null, null);
            }

            // Update elements list for future calls.
            $this->elements[$name] = $object;
        }

        return $this->elements[$name];
    }

    /**
     * @param string   $content  The content to parse
     * @param Document $document The document
     * @param int      $position The new position of the cursor after parsing
     *
     * @return Header
     */
    public static function parse($content, Document $document, &$position = 0)
    {
        /** @var Header $header */
        if (substr(trim($content), 0, 2) == '<<') {
            $header = ElementStruct::parse($content, $document, $position);
        } else {
            $elements = ElementArray::parse($content, $document, $position);
            if ($elements) {
                $header = new self($elements->getRawContent(), null);//$document);
            } else {
                $header = new self(array(), $document);
            }
        }

        if ($header) {
            return $header;
        } else {
            // Build an empty header.
            return new self(array(), $document);
        }
    }
}
