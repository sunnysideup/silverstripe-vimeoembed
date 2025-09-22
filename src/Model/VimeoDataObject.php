<?php

declare(strict_types=1);

namespace Sunnysideup\Vimeoembed\Model;

use RuntimeException;
use SilverStripe\Control\Director;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\FieldType\DBVarchar;

/**

 *
 * WHAT WE SEND:
 * Always string or int (not bool):
 *
 * url → the Vimeo video URL you’re asking about.
 * width, maxwidth, height, maxheight → integers (pixel sizes).
 * color → string (hex color code without #, e.g. "00adef").
 * callback → string (JS function name, only when using JSONP).
 * wmode → string ("transparent" or "opaque").
 *
 * Boolean flags (but Vimeo expects them as "0" or "1" strings in query params):
 * byline → show author byline under title.
 * title → show video title.
 * portrait → show uploader portrait.
 * autoplay → start playback automatically.
 * xhtml → output XHTML-compliant embed code.
 * api → enable the (legacy) JS API for Moogaloop player.
 * iframe → use iframe embed (normally true by default).
 *
 *
 * WHAT WE GET BACK:
 * type – The oEmbed resource type. For Vimeo, usually "video".
 * version – oEmbed protocol version (normally "1.0").
 * provider_name – The name of the provider, here "Vimeo".
 * provider_url – The provider’s main URL (https://vimeo.com).
 * title – The video’s title, as set by its creator.
 * author_name – The name of the video’s uploader.
 * author_url – A link to the uploader’s Vimeo profile.
 * is_plus – Boolean indicating whether the video is hosted on a Vimeo Plus/Pro account (affects branding and features).
 * html – The full <iframe> embed HTML snippet you can place on a page.
 * width – Suggested width (in pixels) for the embedded player.
 * height – Suggested height (in pixels) for the embedded player.
 * duration – Length of the video in seconds.
 * description – The video’s description text.
 * thumbnail_url – URL of a preview thumbnail image.
 * thumbnail_width – Width of the thumbnail image in pixels.
 * thumbnail_height – Height of the thumbnail image in pixels.
 * video_id – Vimeo’s internal numeric ID for the video.
 *
 **/

class VimeoDataObject extends DataObject
{
    /** @var array<string,mixed> */
    protected array $dataAsArray = [];

    /** @var string[] */
    protected array $variables = [
        'type',
        'version',
        'provider_name',
        'provider_url',
        'title',
        'author_name',
        'author_url',
        'is_plus',
        'html',
        'width',
        'height',
        'duration',
        'description',
        'thumbnail_url',
        'thumbnail_width',
        'thumbnail_height',
        'video_id',
    ];

    private static array $db = [
        'Title' => 'Varchar(100)',
        'VimeoCode' => 'Int',
        'HTMLSnippet' => 'HTMLText',
        'Data' => 'Text', // base64(serialized array) for backward compat
    ];

    private static string $table_name = 'VimeoEmbed';

    private static array $casting = [
        'FullName' => 'Text',
        'Icon' => 'HTMLText',
        'IconLink' => 'Varchar',
        'FullImage' => 'HTMLText',
        'FullImageLink' => 'Varchar',
    ];

    private static array $searchable_fields = [
        'Title' => 'PartialMatchFilter',
        'VimeoCode',
    ];

    private static array $summary_fields = [
        'Icon' => 'Icon',
        'Title' => 'Title',
    ];

    private static string $singular_name = 'Vimeo Video';
    private static string $plural_name = 'Vimeo Videos';
    private static string $default_sort = 'Title ASC';

    // ---- Configurable oEmbed options ----
    private static string $vimeo_base_url = 'https://vimeo.com/api/oembed.json';

    private static ?int $width = null;
    private static ?int $maxwidth = null;
    private static ?int $height = null;
    private static ?int $maxheight = null;

    private static ?bool $byline = null;
    private static ?bool $title = null;
    private static ?bool $portrait = null;
    private static ?string $color = null;
    private static ?string $callback = null;
    private static ?bool $autoplay = null;
    private static ?bool $xhtml = null;
    private static ?bool $api = null;
    private static ?string $wmode = null;
    private static ?bool $iframe = null;

    /** For internal use only: skip remote fetch */
    private bool $doNotRetrieveData = false;

    public function getCMSFields(): FieldList
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('HTMLSnippet');
        $fields->removeByName('Data');

        $fields->addFieldToTab(
            'Root.Main',
            LiteralField::create('HTMLSnippet', (string) $this->HTML(false))
        );

        foreach ($this->getDataAsArray() as $name => $value) {
            $fields->addFieldToTab(
                'Root.Details',
                ReadonlyField::create((string) $name, (string) $name, is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_SLASHES))
            );
        }

        return $fields;
    }

    public function getFullName(): string
    {
        return $this->Title . ' (' . (string) $this->VimeoCode . ')';
    }

    public function MetaDataVariable(string $name): ?DBVarchar
    {
        return $this->getMetaDataVariable($name);
    }

    public function getMetaDataVariable(string $name): ?DBVarchar
    {
        $data = $this->getDataAsArray();
        if ($data !== [] && array_key_exists($name, $data) && $data[$name] !== null) {
            return DBVarchar::create_field('Varchar', (string) $data[$name]);
        }
        return null;
    }

    public function getIcon(): DBHTMLText
    {
        $thumb = $this->getDataValue('thumbnail_url');
        $w = (int) ($this->getDataValue('thumbnail_width') ?? 0);
        $h = (int) ($this->getDataValue('thumbnail_height') ?? 0);

        $html = $thumb
            ? '<img src=\'' . Convert::raw2att((string) $thumb) . '\' ' .
            ($w > 0 ? 'width=\'' . $w . '\' ' : '') .
            ($h > 0 ? 'height=\'' . $h . '\' ' : '') .
            'alt=\'' . Convert::raw2att($this->Title) . '\'>'
            : '[' . Convert::raw2att($this->Title) . ']';

        return DBHTMLText::create_field('HTMLText', $html);
    }

    public function getIconLink(): ?DBVarchar
    {
        $thumb = $this->getDataValue('thumbnail_url');
        return $thumb ? DBVarchar::create_field('Varchar', (string) $thumb) : null;
    }

    public function getFullImage(): DBHTMLText
    {
        $thumb = (string) ($this->getDataValue('thumbnail_url') ?? '');
        $full = $thumb !== '' ? str_replace('_295x166', '', $thumb) : '';

        $html = $full !== ''
            ? '<img src=\'' . Convert::raw2att($full) . '\' alt=\'' . Convert::raw2att($this->Title) . '\'>'
            : '[' . Convert::raw2att($this->Title) . ']';

        return DBHTMLText::create_field('HTMLText', $html);
    }

    public function getFullImageLink(): ?DBVarchar
    {
        $thumb = (string) ($this->getDataValue('thumbnail_url') ?? '');
        if ($thumb === '') {
            return null;
        }
        $full = str_replace('_295x166', '', $thumb);
        return DBVarchar::create_field('Varchar', $full);
    }

    public function HTML(bool $noCaching = false): ?string
    {
        if ($noCaching || strlen((string) $this->HTMLSnippet) < 17 || $this->Data === null) {
            $this->updateData(true);
        }
        return $this->HTMLSnippet;
    }

    public function onBeforeWrite(): void
    {
        parent::onBeforeWrite();
        $this->VimeoCode = (int) $this->VimeoCode;
        $this->updateData(false);
    }

    public function safelyUnserialize(string $serializedData): mixed
    {
        // Stored as base64(serialize(array))
        $decoded = base64_decode($serializedData, true);
        if ($decoded === false) {
            return [];
        }
        $value = @unserialize($decoded, ['allowed_classes' => false]);
        return is_array($value) ? $value : [];
    }

    public function safelySerialize(mixed $dataAsArray): string
    {
        return base64_encode(serialize($dataAsArray));
    }

    /** @return array<string,mixed> */
    protected function getDataAsArray(): array
    {
        if ($this->dataAsArray !== []) {
            return $this->dataAsArray;
        }
        if (!$this->Data) {
            $this->updateData(true);
        }
        $this->dataAsArray = $this->safelyUnserialize((string) $this->Data);
        return $this->dataAsArray;
    }

    protected function updateData(bool $writeToDatabase = true): string
    {
        if ($this->doNotRetrieveData || !$this->VimeoCode) {
            return (string) $this->Data;
        }

        $url = $this->buildOembedUrl((string) $this->VimeoCode);
        $json = $this->httpGet($url);
        $decoded = json_decode($json, true);

        if (!is_array($decoded)) {
            throw new RuntimeException('Invalid response from Vimeo oEmbed.');
        }

        // Normalise / ensure all expected vars exist
        $result = [];
        foreach ($this->variables as $key) {
            $result[$key] = $decoded[$key] ?? null;
        }

        // Persist
        $this->dataAsArray = $result;
        $this->Data = $this->safelySerialize($this->dataAsArray);
        $this->HTMLSnippet = (string) ($this->dataAsArray['html'] ?? '');

        if ($writeToDatabase) {
            // Avoid infinite loop if triggered in onBeforeWrite
            if ($this->isInDB()) {
                $this->write();
            }
        }

        return (string) $this->Data;
    }

    // ---- Helpers ---------------------------------------------------------

    protected function buildOembedUrl(string $code): string
    {
        $base = (string) $this->config()->get('vimeo_base_url') ?: self::$vimeo_base_url;
        $videoUrl = 'https://vimeo.com/' . rawurlencode($code);

        $params = array_filter([
            'url' => $videoUrl,
            'width' => $this->config()->get('width'),
            'maxwidth' => $this->config()->get('maxwidth'),
            'height' => $this->config()->get('height'),
            'maxheight' => $this->config()->get('maxheight'),
            'byline' => $this->boolParam($this->config()->get('byline')),
            'title' => $this->boolParam($this->config()->get('title')),
            'portrait' => $this->boolParam($this->config()->get('portrait')),
            'color' => $this->config()->get('color'),
            'callback' => $this->config()->get('callback'),
            'autoplay' => $this->boolParam($this->config()->get('autoplay')),
            'xhtml' => $this->boolParam($this->config()->get('xhtml')),
            'api' => $this->boolParam($this->config()->get('api')),
            'wmode' => $this->config()->get('wmode'),
            'iframe' => $this->boolParam($this->config()->get('iframe')),
        ], static fn($v) => $v !== null && $v !== '');

        return $base . '?' . http_build_query($params, arg_separator: '&', encoding_type: PHP_QUERY_RFC3986);
    }

    protected function httpGet(string $url): string
    {
        $ch = curl_init();
        if ($ch === false) {
            throw new RuntimeException('Unable to initialise cURL.');
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_USERAGENT => 'SS-VimeoEmbed/1.0 (+' . Director::baseURL() . ')',
        ]);

        $data = curl_exec($ch);
        if ($data === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('cURL error: ' . $err);
        }

        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code < 200 || $code >= 300) {
            throw new RuntimeException('HTTP error from Vimeo: ' . $code);
        }

        // Strip non-ASCII that can break unserialize in legacy storage
        $data = preg_replace('/[^\x20-\x7E\t\r\n]/', '', $data) ?? $data;

        return $data;
    }

    protected function getDataValue(string $key): mixed
    {
        $data = $this->getDataAsArray();
        return $data[$key] ?? null;
    }

    protected function boolParam(mixed $v): ?string
    {
        if ($v === null) {
            return null;
        }
        return $v ? '1' : '0';
    }
}
