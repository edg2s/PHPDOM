<?php
declare(strict_types=1);

namespace Rowbot\DOM\Exception;

/**
 * @see https://heycam.github.io/webidl/#namespaceerror
 */
class NamespaceError extends DOMException
{
    public function __construct(string $message = '', $previous = null)
    {
        if ($message === '') {
            $message = 'The operation is not allowed by Namespaces in XML.';
        }

        parent::__construct($message, 14, $previous);
    }
}
