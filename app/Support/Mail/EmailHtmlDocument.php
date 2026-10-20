<?php

namespace App\Support\Mail;

/**
 * Builds a self-contained HTML document for showing an untrusted email body inside a sandboxed iframe.
 *
 * The iframe sandbox (no scripts, no forms) and the Content-Security-Policy below are the real protection;
 * the markup sanitizing here is an extra layer on top of them. Images may load from anywhere, while scripts,
 * stylesheets, fonts, frames and form submissions from outside stay blocked.
 */
class EmailHtmlDocument
{
    private const CONTENT_SECURITY_POLICY = "default-src 'none'; style-src 'unsafe-inline'; font-src data:; img-src data: 'self' https: http:; form-action 'none'; base-uri 'none'";

    private const CONTENT_ID_REFERENCE_PATTERN = '/(\b(?:src|background)\s*=\s*["\']?\s*|url\(\s*["\']?\s*)cid:([^"\'\s)>]+)/i';

    private const BASE_STYLES = 'html,body{margin:0;padding:0;background:#fff;}'
        .'body{padding:4px;color:#212529;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;font-size:14px;line-height:1.5;overflow-wrap:anywhere;}'
        .'img{max-width:100%;}';

    /**
     * @param  array<string, string>  $inlineImageUrls  Content-ID => URL that serves the inline image part.
     */
    public function render(string $html, array $inlineImageUrls): string
    {
        return '<!DOCTYPE html><html><head><meta charset="utf-8">'
            .'<meta http-equiv="Content-Security-Policy" content="'.htmlspecialchars(self::CONTENT_SECURITY_POLICY, ENT_COMPAT).'">'
            .'<meta name="referrer" content="no-referrer">'
            .'<base target="_blank">'
            .'<style>'.self::BASE_STYLES.'</style>'
            .'</head><body>'.$this->sanitize($html, $inlineImageUrls).'</body></html>';
    }

    /**
     * @return list<string>
     */
    public function referencedContentIds(string $html): array
    {
        preg_match_all(self::CONTENT_ID_REFERENCE_PATTERN, $html, $matches);

        return array_values(array_unique($matches[2]));
    }

    /**
     * @param  array<string, string>  $inlineImageUrls
     */
    private function sanitize(string $html, array $inlineImageUrls): string
    {
        $html = preg_replace('/<(script|iframe|frame|frameset|object|embed|applet)\b[^>]*>.*?<\/\1\s*>/is', '', $html) ?? '';
        $html = preg_replace('/<\/?(script|iframe|frame|frameset|object|embed|applet|meta|base|link|form)\b[^>]*>/i', '', $html) ?? '';

        $html = preg_replace_callback('/<[a-zA-Z][^>]*>/', function (array $tagMatch): string {
            $tag = preg_replace('/\s+on[a-z0-9_-]+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $tagMatch[0]) ?? '';
            $tag = preg_replace('/\b(href|src|action|formaction|background|poster|xlink:href)\s*=\s*(["\']?)\s*(?:javascript|vbscript|livescript):[^"\'\s>]*/i', '$1=$2#', $tag) ?? '';

            return preg_replace('/^<(a|area)\b/i', '<$1 target="_blank" rel="noopener noreferrer"', $tag) ?? '';
        }, $html) ?? '';

        return preg_replace_callback(self::CONTENT_ID_REFERENCE_PATTERN, function (array $referenceMatch) use ($inlineImageUrls): string {
            $url = $inlineImageUrls[$referenceMatch[2]] ?? null;

            if ($url === null) {
                return $referenceMatch[0];
            }

            $isCssReference = str_starts_with(strtolower($referenceMatch[1]), 'url(');

            return $referenceMatch[1].($isCssReference ? $url : htmlspecialchars($url, ENT_QUOTES));
        }, $html) ?? '';
    }
}
