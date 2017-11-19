<?php
namespace Rowbot\DOM;

use Rowbot\DOM\Element\Element;
use Rowbot\DOM\Exception\DOMException;
use Rowbot\DOM\Exception\NamespaceError;
use Rowbot\DOM\Exception\InvalidCharacterError;

class Namespaces
{
    const HTML = 'http://www.w3.org/1999/xhtml';
    const SVG = 'http://www.w3.org/2000/svg';
    const XML = 'http://www.w3.org/XML/1998/namespace';
    const XMLNS = 'http://www.w3.org/2000/xmlns/';
    const MATHML = 'http://www.w3.org/1998/Math/MathML';
    const XLINK = 'http://www.w3.org/1999/xlink';

    // Same as NAME_START_CHAR, but without the ":".
    const NCNAME_START_CHAR = '[A-Z]|_|[a-z]|[\xC0-\xD6]|[\xD8-\xF6]|' .
        '[\xF8-\x{2FF}]|[\x{370}-\x{37D}]|[\x{37F}-\x{1FFF}]|' .
        '[\x{200C}-\x{200D}]|[\x{2070}-\x{218F}]|[\x{2C00}-\x{2FEF}]|' .
        '[\x{3001}-\x{D7FF}]|[\x{F900}-\x{FDCF}]|[\x{FDF0}-\x{FFFD}]|' .
        '[\x{10000}-\x{EFFFF}]';
    const NAME_START_CHAR = ':|' . self::NCNAME_START_CHAR;
    const NAME_CHAR = self::NAME_START_CHAR . '|-|\.|[0-9]|\xB7|' .
        '[\x{0300}-\x{036F}]|[\x{203F}-\x{2040}]';
    const NAME = '(' . self::NAME_START_CHAR . ')(' . self::NAME_CHAR . ')*';
    const NAME_PRODUCTION = '/^' . self::NAME . '$/u';
    const NCNAME_CHAR = self::NCNAME_START_CHAR . '|-|\.|[0-9]|\xB7|' .
        '[\x{0300}-\x{036F}]|[\x{203F}-\x{2040}]';
    const NCNAME = '(' . self::NCNAME_START_CHAR . ')(' . self::NCNAME_CHAR .
        ')*';

    const PREFIX = self::NCNAME;
    const LOCALPART = self::NCNAME;
    const PREFIXED_NAME = '(' . self::PREFIX . '):(' . self::LOCALPART . ')';
    const UNPREFIXED_NAME = self::LOCALPART;
    const QNAME = '/^(' . self::PREFIXED_NAME . '|' . self::UNPREFIXED_NAME .
        ')$/u';

    /**
     * Finds the prefix associated with the given namespace on the given node.
     *
     * @see https://dom.spec.whatwg.org/#locate-a-namespace-prefix
     *
     * @param Element      $element   Those element whose prefix is to be found.
     *
     * @param string|null  $namespace The namespace of the prefix to be found.
     *
     * @return string|null
     */
    public static function locatePrefix(Element $element, $namespace)
    {
        if ($element->namespaceURI === $namespace &&
            ($prefix = $element->prefix) !== null
        ) {
            return $prefix;
        }

        foreach ($element->getAttributeList() as $attr) {
            if ($attr->prefix === 'xmlns' && $attr->value === $namespace) {
                return $attr->localName;
            }
        }

        if (($parentElement = $element->parentElement) !== null) {
            return self::locatePrefix($parentElement, $namespace);
        }

        return null;
    }

    /**
     * Ensures that a qualified name is a valid one.
     *
     * @see https://dom.spec.whatwg.org/#validate
     *
     * @param string $qualifiedName The qualified name to validate.
     *
     * @throws InvalidCharacterError If the qualified name does not match the
     *     XML 'Name' production.
     */
    public static function validate($qualifiedName)
    {
        // If qualifiedName does not match the 'Name' or 'QName' production,
        // then throw an InvalidCharacterError.
        if (!preg_match(self::NAME_PRODUCTION, $qualifiedName) ||
            !preg_match(self::QNAME, $qualifiedName)
        ) {
            throw new InvalidCharacterError();
        }
    }

    /**
     * Validates that the given name is valid in the given namespace and returns
     * the input broken down into its individual parts.
     *
     * @see https://dom.spec.whatwg.org/#validate-and-extract
     *
     * @param string $namespace A namespace.
     *
     * @param string $qualifiedName The qualified name to validate.
     *
     * @return string[] Returns the namespace, namespace prefix, and localName.
     *
     * @throws NamespaceError
     */
    public static function validateAndExtract($namespace, $qualifiedName)
    {
        if ($namespace === '') {
            $namespace = null;
        }

        try {
            self::validate($qualifiedName);
        } catch (DOMException $e) {
            throw $e;
        }

        $prefix = null;
        $localName = $qualifiedName;

        if (mb_strpos($qualifiedName, ':') !== false) {
            list($prefix, $localName) = explode(':', $qualifiedName, 2);
        }

        if ($prefix !== null && $namespace === null) {
            throw new NamespaceError();
        }

        if ($prefix === 'xml' && $namespace !== self::XML) {
            throw new NamespaceError();
        }

        if (($qualifiedName === 'xmlns' || $prefix === 'xmlns') &&
            $namespace !== self::XMLNS) {
            throw new NamespaceError();
        }

        if ($namespace === self::XMLNS && $qualifiedName !== 'xmlns' &&
            $prefix !== 'xmlns'
        ) {
            throw new NamespaceError();
        }

        return [
            $namespace,
            $prefix,
            $localName
        ];
    }
}
