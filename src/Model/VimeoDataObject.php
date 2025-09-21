<?php

declare(strict_types=1);

namespace Sunnysideup\Vimeoembed\Model;

use Exception;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\FieldType\DBVarchar;

/**
 *@author nicolaas[at]sunnysideup.co.nz
 *
 *
 **/

class VimeoDataObject extends DataObject
{
    protected array $dataAsArray = [];

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

    private static $db = [
        'Title' => 'Varchar(100)',
        'VimeoCode' => 'Int',
        'HTMLSnippet' => 'HTMLText',
        'Data' => 'Text',
    ];

    private static $table_name = 'VimeoEmbed';

    private static $casting = [
        'FullName' => 'Text',
        'Icon' => 'HTMLText',
        'IconLink' => 'Varchar',
        'FullImage' => 'HTMLText',
        'FullImageLink' => 'Varchar',
    ];

    private static $searchable_fields = [
        'Title' => 'PartialMatchFilter',
        'VimeoCode',
    ];

    private static $summary_fields = [
        'Icon' => 'Icon',
        'Title' => 'Title',
    ];

    private static string $singular_name = 'Vimeo Video';

    private static string $plural_name = 'Vimeo Videos';

    private static string $default_sort = 'Title ASC';

    private static string $vimeo_base_url = 'http://vimeo.com/api/oembed.xml?url=http%3A//vimeo.com/'; //The exact width of the video. Defaults to original size.

    private static int $width = null; //The exact width of the video. Defaults to original size.

    private static int $maxwidth = null; ////Same as width, but video will not exceed original size.

    private static int $height = null; //The exact height of the video. Defaults to original size.

    private static int $maxheight = null; //Same as height, but video will not exceed original size.

    private static $byline = null; //Show the byline on the video. Defaults to true.

    private static $title = null; //Show the title on the video. Defaults to true.

    private static ?bool $portrait = null; //// Show the user's portrait on the video. Defaults to true.

    private static ?string $color = null; // Specify the color of the video controls.

    private static  $callback = null; //When returning JSON, wrap in this function.

    private static ?bool $autoplay = null; //Automatically start playback of the video. Defaults to false.

    private static ?bool $xhtml = null; // Make the embed code XHTML compliant. Defaults to true.

    private static $api = null; // Enable the Javascript API for Moogaloop. Defaults to false.

    private static $wmode = null; //add the "wmode" parameter. Can be either transparent or opaque.

    private static $iframe; // Use our new embed code. Defaults to true. NEW!

    /**
     * do not retrieve data from vimeo server ...
     * for internal use only
     * @var boolean
     */
    private bool $doNotRetrieveData = false;

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('HTMLSnippet');
        $fields->removeByName('Data');
        $fields->addFieldToTab('Root.Main', new LiteralField('HTMLSnippet', $this->HTML($noCaching = false)));
        $this->getDataAsArray();
        if (is_array($this->dataAsArray) && count($this->dataAsArray)) {
            foreach ($this->dataAsArray as $name => $value) {
                $fields->addFieldToTab('Root.Details', new ReadonlyField($name, $name, $value));
            }
        }
        return $fields;
    }

    /**
     * casted variable
     * @return string
     */
    public function getFullName(): string
    {
        return $this->Title . ' (' . $this->VimeoCode . ')';
    }

    /**
     * alias for getVariable
     * @param string $name
     * @return DBVarchar|null
     */
    public function MetaDataVariable($name): ?DBVarchar
    {
        return $this->getMetaDataVariable($name);
    }

    /**
     * @param string $name - name of variable
     *
     * @return DBVarchar Object
     */
    public function getMetaDataVariable($name): DBVarchar|null
    {
        $this->getDataAsArray();
        if (! empty($this->dataAsArray[$name])) {
            return DBVarchar::create_field('Varchar', $this->dataAsArray[$name]);
        }
        return null;
    }

    /**
     * return icon as <img tag>
     * @return string
     */
    public function getIcon(): DBHTMLText
    {
        if (! count($this->dataAsArray)) {
            //remove non-ascii characters as they were causing havoc...
            $this->Data = preg_replace('/[^(\x20-\x7F)]*/', '', $this->Data);
            $this->dataAsArray = $this->safelyUnserialize($this->Data);
        }
        if (! empty($this->dataAsArray['thumbnail_url'])) {
            $v = '<img src="' . $this->dataAsArray['thumbnail_url'] . '" width="' . $this->dataAsArray['thumbnail_width'] . '" height="' . $this->dataAsArray['thumbnail_height'] . '" alt="' . Convert::raw2att($this->Title) . '"/>';
        } else {
            $v = '[' . $this->Title . ']';
        }
        return DBHTMLText::create_field('HTMLText', $v);
    }

    /**
     * returns icon as myimage.png
     * @return string
     */
    public function getIconLink(): DBVarchar|null
    {
        $this->getDataAsArray();
        if (! empty($this->dataAsArray['thumbnail_url'])) {
            return DBVarchar::create_field('Varchar', $this->dataAsArray['thumbnail_url']);
        }
        return null;
    }

    /**
     * return icon as <img tag>
     * @return string
     */
    public function getFullImage(): DBHTMLText
    {
        if (! count($this->dataAsArray)) {
            //remove non-ascii characters as they were causing havoc...
            $this->Data = preg_replace('/[^(\x20-\x7F)]*/', '', $this->Data);
            $this->dataAsArray = $this->safelyUnserialize($this->Data);
        }
        if (! empty($this->dataAsArray['thumbnail_url'])) {
            $imageLink = str_replace('_295x166', '', $this->dataAsArray['thumbnail_url']);
            $v = '<img src="' . $imageLink . '" alt="' . Convert::raw2att($this->Title) . '"/>';
        } else {
            $v = '[' . $this->Title . ']';
        }
        return DBHTMLText::create_field('HTMLText', $v);
    }

    /**
     * returns icon as myimage.png
     * @return string
     */
    public function getFullImageLink(): DBVarchar|null
    {
        $this->getDataAsArray();
        if (! empty($this->dataAsArray['thumbnail_url'])) {
            $imageLink = str_replace('_295x166', '', $this->dataAsArray['thumbnail_url']);
            return DBVarchar::create_field('Varchar', $imageLink);
        }
        return null;
    }

    /**
     * returns the HTML Embed code
     * @return string
     */
    public function HTML($noCaching = false): ?string
    {
        if ($noCaching || strlen($this->HTMLSnippet) < 17 || ! $this->Data || isset($_GET['flush'])) {
            $this->updateData();
        }
        return $this->HTMLSnippet;
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $this->VimeoCode = intval($this->VimeoCode);
        $this->updateData(false);
    }

    /**
     * @param string $serializedData
     *
     * @return mixed
     */
    public function safelyUnserialize(string $serializedData): mixed
    {
        return unserialize(base64_decode($serializedData, true));
        // //this code needs checking.
        // try {
        //     $fixed = unserialize(base64_decode($serializedData, true));
        //     if (is_array($fixed)) {
        //         return $fixed;
        //     }
        //     return unserialize($serializedData);
        // } catch (Exception $e) {
        //     $fixed = preg_replace_callback(
        //         '!s:(\d+):"(.*?)";!',
        //         function ($match) {
        //             return $match[1] === strlen($match[2]) ? $match[0] : 's:' . strlen($match[2]) . ':"' . $match[2] . '";';
        //         },
        //         $serializedData
        //     );
        // }
        // return $fixed;
    }

    /**
     * @param array $dataAsArray
     *
     * @return string
     */
    public function safelySerialize(mixed $dataAsArray): string
    {
        return base64_encode(serialize($dataAsArray));
    }

    /**
     * turns the saved serialized data into an array to return
     * if there is no data then it will try to retrieve and save it
     * then return it.
     * @return array
     */
    protected function getDataAsArray(): array
    {
        if ($this->dataAsArray) {
            return $this->dataAsArray;
        }
        if (! $this->Data) {
            $this->updateData();
        }
        $this->dataAsArray = $this->safelyUnserialize($this->Data);
        return $this->dataAsArray;
    }

    /**
     * retrieves data from Vimeo Site
     *
     * @return array
     */
    protected function updateData(?bool $writeToDatabase = true): string
    {
        if ($this->doNotRetrieveData) {
            //do nothing
        } elseif ($this->VimeoCode) {
            $get = [];
            if ($width = $this->Config()->get('width')) {
                $get['width'] = $width;
            }
            if ($max_width = $this->Config()->get('maxwidth')) {
                $get['maxwidth'] = $max_width;
            }
            if ($height = $this->Config()->get('height')) {
                $get['height'] = $height;
            }
            if ($maxheight = $this->Config()->get('maxheight')) {
                $get['maxheight'] = $maxheight;
            }
            if ($byline = $this->Config()->get('byline')) {
                $get['byline'] = $byline;
            }
            if ($title = $this->Config()->get('title')) {
                $get['title'] = $title;
            }
            if ($portrait = $this->Config()->get('portrait')) {
                $get['portrait'] = $portrait;
            }
            if ($color = $this->Config()->get('color')) {
                $get['color'] = $color;
            }
            if ($callback = $this->Config()->get('callback')) {
                $get['callback'] = $callback;
            }
            if ($autoplay = $this->Config()->get('autoplay ')) {
                $get['autoplay'] = $autoplay;
            }
            if ($xhtml = $this->Config()->get('xhtml')) {
                $get['xhtml'] = $xhtml;
            }
            if ($api = $this->Config()->get('api')) {
                $get['api'] = $api;
            }
            if ($wmode = $this->Config()->get('wmode')) {
                $get['wmode'] = $wmode;
            }
            if ($iframe = $this->Config()->get('iframe')) {
                $get['iframe'] = $iframe;
            }
            $url = '';
            $url .= $this->Config()->get('vimeo_base_url') . $this->VimeoCode;
            if (is_array($get) && count($get)) {
                foreach ($get as $key => $value) {
                    $get[$key] = $key . '=' . urlencode($value);
                }
                $url .= '?' . implode('&', $get);
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $data = curl_exec($ch);
            $array = $this->my_xml2array($data);
            foreach ($this->variables as $variable) {
                $data_array = $this->get_value_by_path($array, 'oembed/' . $variable);
                if (isset($data_array['name']) && isset($data_array['value'])) {
                    $this->dataAsArray[$data_array['name']] = $data_array['value'];
                } else {
                    $this->dataAsArray[$variable] = null;
                }
            }
            $this->Data = $this->safelySerialize($this->dataAsArray);
            $this->HTMLSnippet = $this->dataAsArray['html'];
            if ($writeToDatabase) {
                $this->write();
            }
        }
        return $this->Data;
    }

    //SOURCE: http://php.net/manual/en/function.xml-parse.php
    private function my_xml2array($contents): array|false
    {
        $parser = xml_parser_create('');
        if (! $parser) {
            return false;
        }
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, trim($contents), $xml_values);
        xml_parser_free($parser);
        if (! $xml_values) {
            return [];
        }
        $xml_array = [];
        $last_tag_ar = &$xml_array;
        $parents = [];
        $last_counter_in_tag = [1 => 0];
        foreach ($xml_values as $data) {
            switch ($data['type']) {
                case 'open':
                    $last_counter_in_tag[$data['level'] + 1] = 0;
                    $new_tag = ['name' => $data['tag']];
                    if (isset($data['attributes'])) {
                        $new_tag['attributes'] = $data['attributes'];
                    }
                    if (isset($data['value']) && trim($data['value'])) {
                        $new_tag['value'] = trim($data['value']);
                    }
                    $last_tag_ar[$last_counter_in_tag[$data['level']]] = $new_tag;
                    $parents[$data['level']] = &$last_tag_ar;
                    $last_tag_ar = &$last_tag_ar[$last_counter_in_tag[$data['level']]++];
                    break;
                case 'complete':
                    $new_tag = ['name' => $data['tag']];
                    if (isset($data['attributes'])) {
                        $new_tag['attributes'] = $data['attributes'];
                    }
                    if (isset($data['value']) && trim($data['value'])) {
                        $new_tag['value'] = trim($data['value']);
                    }
                    $last_count = count($last_tag_ar) - 1;
                    $last_tag_ar[$last_counter_in_tag[$data['level']]++] = $new_tag;
                    break;
                case 'close':
                    $last_tag_ar = &$parents[$data['level']];
                    break;
                default:
                    break;
            }
        }
        return $xml_array;
    }

    // use this to get node of tree by path with '/' terminator
    //
    //SOURCE: http://php.net/manual/en/function.xml-parse.php
    private function get_value_by_path($__xml_tree, $__tag_path): array|false
    {
        $tmp_arr = &$__xml_tree;
        $tag_path = explode('/', $__tag_path);
        foreach ($tag_path as $tag_name) {
            $res = false;
            foreach ($tmp_arr as $key => $node) {
                if (is_int($key) && $node['name'] === $tag_name) {
                    $tmp_arr = $node;
                    $res = true;
                    break;
                }
            }
            if (! $res) {
                return false;
            }
        }
        return $tmp_arr;
    }
}
