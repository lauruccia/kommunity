<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Page extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'meta_description',
        'show_in_nav',
        'show_in_footer',
        'nav_order',
        'footer_order',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'show_in_nav'    => 'boolean',
            'show_in_footer' => 'boolean',
            'is_published'   => 'boolean',
        ];
    }

    /** Genera slug automatico dal titolo se non fornito */
    protected static function booted(): void
    {
        static::creating(function (self $page) {
            if (empty($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
        });
    }

    /** Pagine pubblicate visibili nel menu nav, ordinate */
    public static function forNav(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_published', true)
            ->where('show_in_nav', true)
            ->orderBy('nav_order')
            ->orderBy('title')
            ->get();
    }

    /** Pagine pubblicate visibili nel footer, ordinate */
    public static function forFooter(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_published', true)
            ->where('show_in_footer', true)
            ->orderBy('footer_order')
            ->orderBy('title')
            ->get();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Sicurezza: sanitizza l'HTML del contenuto in scrittura per evitare XSS
    // stored. Whitelist conservativa di tag e attributi.
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Mutator: ogni volta che si imposta `content` (anche dal pannello admin)
     * il valore viene ripulito da script, eventi inline, iframe non whitelistati,
     * URL javascript:, ecc.
     */
    protected function content(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => self::sanitizeHtml($value ?? ''),
        );
    }

    /**
     * Whitelist di tag HTML ammessi nel CMS.
     */
    protected const ALLOWED_TAGS = [
        'a', 'abbr', 'b', 'blockquote', 'br', 'caption', 'code', 'div', 'em',
        'figcaption', 'figure', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'i',
        'img', 'li', 'ol', 'p', 'pre', 'small', 'span', 'strong', 'sub', 'sup',
        'table', 'tbody', 'td', 'tfoot', 'th', 'thead', 'tr', 'u', 'ul',
    ];

    /**
     * Whitelist di attributi per tag. '*' = qualsiasi tag.
     */
    protected const ALLOWED_ATTRIBUTES = [
        '*'   => ['class', 'id', 'title', 'lang', 'dir'],
        'a'   => ['href', 'target', 'rel'],
        'img' => ['src', 'alt', 'width', 'height', 'loading'],
        'th'  => ['scope', 'colspan', 'rowspan'],
        'td'  => ['colspan', 'rowspan'],
    ];

    /**
     * Schemi URI permessi per attributi che contengono URL.
     */
    protected const ALLOWED_URI_SCHEMES = ['http', 'https', 'mailto', 'tel'];

    /**
     * Sanitizza una stringa HTML rimuovendo script, on*-handlers, iframe, ecc.
     * Implementazione PHP nativa (zero dipendenze) basata su DOMDocument.
     */
    public static function sanitizeHtml(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        // libxml: silenzia gli errori HTML5 e tieni il chunk corrente
        $previousInternalErrors = libxml_use_internal_errors(true);

        $doc = new \DOMDocument('1.0', 'UTF-8');
        // Wrapping con UTF-8 per evitare bug encoding di DOMDocument
        $wrapped = '<?xml encoding="UTF-8"?><div id="__km_root__">' . $html . '</div>';
        $doc->loadHTML(
            $wrapped,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET
        );

        $root = $doc->getElementById('__km_root__');
        if ($root === null) {
            // Fallback: nessun nodo valido, ritorno stringa svuotata da tag
            libxml_clear_errors();
            libxml_use_internal_errors($previousInternalErrors);
            return strip_tags($html);
        }

        self::sanitizeNode($root);

        $clean = '';
        foreach ($root->childNodes as $child) {
            $clean .= $doc->saveHTML($child);
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previousInternalErrors);

        return trim($clean);
    }

    /**
     * Visita ricorsivamente l'albero DOM rimuovendo tag/attributi non ammessi
     * e neutralizzando URL pericolosi.
     */
    protected static function sanitizeNode(\DOMNode $node): void
    {
        // Lavora su copia perché modifichiamo $node->childNodes durante l'iter
        $children = [];
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            if ($child instanceof \DOMElement) {
                $tag = strtolower($child->tagName);

                if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                    // Tag non ammesso: rimpiazza con il suo contenuto testuale
                    while ($child->firstChild) {
                        $node->insertBefore($child->firstChild, $child);
                    }
                    $node->removeChild($child);
                    continue;
                }

                self::sanitizeAttributes($child, $tag);
                self::sanitizeNode($child);
            } elseif ($child instanceof \DOMComment) {
                // Rimuove i commenti HTML (possibili vettori IE-only)
                $node->removeChild($child);
            }
            // I nodi testo restano così come sono
        }
    }

    /**
     * Pulisce gli attributi di un elemento mantenendo solo quelli in whitelist
     * e validando gli URL.
     */
    protected static function sanitizeAttributes(\DOMElement $element, string $tag): void
    {
        $allowedGlobal = self::ALLOWED_ATTRIBUTES['*'] ?? [];
        $allowedTag    = self::ALLOWED_ATTRIBUTES[$tag] ?? [];
        $allowed       = array_merge($allowedGlobal, $allowedTag);

        // Snapshot perché stiamo per modificare la collezione
        $attrs = [];
        foreach ($element->attributes as $attr) {
            $attrs[] = $attr->nodeName;
        }

        foreach ($attrs as $name) {
            $lower = strtolower($name);

            // Rimuovi sempre handler inline (onclick, onload, ecc.)
            if (str_starts_with($lower, 'on')) {
                $element->removeAttribute($name);
                continue;
            }

            if (! in_array($lower, $allowed, true)) {
                $element->removeAttribute($name);
                continue;
            }

            // Validazione URL per attributi href/src
            if (in_array($lower, ['href', 'src'], true)) {
                $value = (string) $element->getAttribute($name);
                if (! self::isSafeUrl($value)) {
                    $element->removeAttribute($name);
                    continue;
                }
            }

            // Forza rel="noopener noreferrer" per i link target="_blank"
            if ($tag === 'a' && $lower === 'target') {
                $existingRel = (string) $element->getAttribute('rel');
                if (stripos($existingRel, 'noopener') === false) {
                    $element->setAttribute(
                        'rel',
                        trim($existingRel . ' noopener noreferrer')
                    );
                }
            }
        }
    }

    /**
     * Considera "sicuro" solo un URL relativo o uno con schema in whitelist.
     */
    protected static function isSafeUrl(string $url): bool
    {
        $url = trim($url);
        if ($url === '') {
            return false;
        }

        // URL relativi e ancore sono sicuri
        if (str_starts_with($url, '/') || str_starts_with($url, '#') || str_starts_with($url, '?')) {
            return true;
        }

        // Controllo schema
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if ($scheme === null || $scheme === false) {
            // URL relativi senza schema (es. "pagina/about")
            return ! preg_match('/^(javascript|data|vbscript|file):/i', $url);
        }

        return in_array(strtolower($scheme), self::ALLOWED_URI_SCHEMES, true);
    }
}
