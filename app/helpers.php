<?php

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

if (! function_exists('purify')) {
    /**
     * Sanitizza HTML rimuovendo tag e attributi pericolosi (XSS-safe).
     * Usare al posto di {!! $var !!} quando il contenuto proviene da utenti o CMS.
     *
     * Permette i tag HTML tipici di un rich-text editor (TipTap/TinyMCE/Quill)
     * ma blocca <script>, <iframe>, event handlers (onclick ecc.) e href javascript:.
     *
     * @param  string|null  $html
     * @return string
     */
    function purify(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        static $sanitizer = null;

        if ($sanitizer === null) {
            $config = (new HtmlSanitizerConfig())
                ->allowSafeElements()           // whitelist sicura di default (Symfony)
                ->allowElement('u')             // sottolineato
                ->allowElement('s')             // barrato
                ->allowElement('del')
                ->allowElement('ins')
                ->allowElement('mark')
                ->allowElement('sub')
                ->allowElement('sup')
                ->allowElement('details')
                ->allowElement('summary')
                ->allowElement('figure')
                ->allowElement('figcaption')
                ->allowElement('table')
                ->allowElement('thead')
                ->allowElement('tbody')
                ->allowElement('tfoot')
                ->allowElement('tr')
                ->allowElement('th')
                ->allowElement('td')
                ->allowElement('caption')
                ->allowElement('colgroup')
                ->allowElement('col')
                ->allowAttribute('class', '*')  // classi CSS (nessun JS)
                ->allowAttribute('id', '*')
                ->allowAttribute('style', ['span', 'p', 'div', 'td', 'th'])
                ->allowAttribute('colspan', ['td', 'th'])
                ->allowAttribute('rowspan', ['td', 'th'])
                ->allowAttribute('scope', ['td', 'th'])
                ->allowAttribute('target', ['a'])
                ->allowAttribute('rel', ['a'])
                ->allowAttribute('src', ['img'])
                ->allowAttribute('alt', ['img'])
                ->allowAttribute('width', ['img', 'table', 'td', 'th', 'col'])
                ->allowAttribute('height', ['img'])
                ->allowAttribute('loading', ['img'])
                ->allowAttribute('start', ['ol'])
                ->allowAttribute('type', ['ol', 'ul', 'li'])
                ->allowRelativeLinks(false)     // blocca href=javascript:
                ->forceHttpsUrls(false);        // non forzare https (gestito altrove)

            $sanitizer = new HtmlSanitizer($config);
        }

        return $sanitizer->sanitize($html);
    }
}
