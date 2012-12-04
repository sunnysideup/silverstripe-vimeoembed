<?php

/**
 *@author nicolaas[at]sunnysideup.co.nz
 *
 *
 **/

class VimeoDataObject extends DataObject {

	static $db = array(
		"Title" => "Varchar(100)",
		"VimeoCode" => "Int",
		"HTMLSnippet" => "HTMLText",
		"Data" => "Text"
	);

	public static $casting = array(
		"FullName" => "Text"
	);

	public static $searchable_fields = array(
		"Title" => "PartialMatchFilter",
		"VimeoCode"
	);

	public static $singular_name = "Vimeo Video";

	public static $plural_name = "Vimeo Videos";

	public static $default_sort = "Title ASC";

	protected static $vimeo_base_url = "http://vimeo.com/api/oembed.xml?url=http%3A//vimeo.com/";//The exact width of the video. Defaults to original size.
		static function set_vimeo_base_url($v){self::$vimeo_base_url = $v;}
		static function get_vimeo_base_url(){return self::$vimeo_base_url;}

	protected static $width = null;//The exact width of the video. Defaults to original size.
		static function set_width($v){self::$width = $v;}
		static function get_width(){return self::$width;}

	protected static $maxwidth = null;////Same as width, but video will not exceed original size.
		static function set_maxwidth($v){self::$maxwidth = $v;}
		static function get_maxwidth(){return self::$maxwidth;}

	protected static $height = null;//The exact height of the video. Defaults to original size.
		static function set_height($v){self::$height = $v;}
		static function get_height(){return self::$height;}

	protected static $maxheight = null;//Same as height, but video will not exceed original size.
		static function set_maxheight($v){self::$maxheight = $v;}
		static function get_maxheight(){return self::$maxheight;}

	protected static $byline = null;//Show the byline on the video. Defaults to true.
		static function set_byline($v){self::$byline = $v;}
		static function get_byline(){return self::$byline;}

	protected static $title = null;//Show the title on the video. Defaults to true.
		static function set_title($v){self::$title = $v;}
		static function get_title(){return self::$title;}

	protected static $portrait = null;//// Show the user's portrait on the video. Defaults to true.
		static function set_portrait($v){self::$portrait = $v;}
		static function get_portrait(){return self::$portrait;}

	protected static $color = null;// Specify the color of the video controls.
		static function set_color($v){self::$color = $v;}
		static function get_color(){return self::$color;}

	protected static $callback = null;//When returning JSON, wrap in this function.
		static function set_callback($v){self::$callback = $v;}
		static function get_callback(){return self::$callback;}

	protected static $autoplay = null;//Automatically start playback of the video. Defaults to false.
		static function set_autoplay($v){self::$autoplay = $v;}
		static function get_autoplay(){return self::$autoplay;}

	protected static $xhtml = null;// Make the embed code XHTML compliant. Defaults to true.
		static function set_xhtml($v){self::$xhtml = $v;}
		static function get_xhtml(){return self::$xhtml;}

	protected static $api = null;// Enable the Javascript API for Moogaloop. Defaults to false.
		static function set_api($v){self::$api = $v;}
		static function get_api(){return self::$api;}

	protected static $wmode = null;//add the "wmode" parameter. Can be either transparent or opaque.
		static function set_wmode($v){self::$wmode = $v;}
		static function get_wmode(){return self::$wmode;}

	protected static $iframe;// Use our new embed code. Defaults to true. NEW!
		static function set_iframe($v){self::$iframe = $v;}
		static function get_iframe(){return self::$iframe;}


	protected static $add_video_array = array();
	private static $add_video_array_done = false;
		static function add_video($code, $title) {self::$add_video_array[$code] = $title;}

	protected $data = array();

	protected $variables = array(
		"type",
		"version",
		"provider_name",
		"provider_url",
		"title",
		"author_name",
		"author_url",
		"is_plus",
		"html",
		"width",
		"height",
		"duration",
		"description",
		"thumbnail_url",
		"thumbnail_width",
		"thumbnail_height",
		"video_id"
	);

	private $doNotRetrieveData = false;

	function getFullName() {
		return $this->Title." (".$this->VimeoCode.")";
	}

	function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->removeByName("HTMLSnippet");
		$fields->removeByName("Data");
		$fields->addFieldToTab("Root.Main", new LiteralField("HTMLSnippet", $this->HTML($noCaching = true)));
		if(is_array($this->data) && count($this->data)) {
			foreach($this->data as $name => $value) {
				$fields->addFieldToTab("Root.Details", new ReadOnlyField($name, $name, $value));
			}
		}
		return $fields;
	}

	function HTML($noCaching = false) {
		if($noCaching || strlen($this->HTMLSnippet) < 17 || !$this->Data || isset($_GET["flush"])) {//
			$this->updateData();
		}
		return $this->HTMLSnippet;
	}

	protected function updateData() {
		if($this->VimeoCode && !$this->doNotRetrieveData) {
			$get = array();
			if($width = self::get_width()) {$get["width"] = $width;}
			if($max_width = self::get_maxwidth()) {$get["maxwidth"] = $max_width;}
			if($height = self::get_height()) {$get["height"] = $height;}
			if($maxheight = self::get_maxheight()) {$get["maxheight"] = $maxheight;}
			if($byline = self::get_byline()) {$get["byline"] = $byline;}
			if($title = self::get_title()) {$get["title"] = $title;}
			if($portrait = self::get_portrait()) {$get["portrait"] = $portrait;}
			if($color = self::get_color()) {$get["color"] = $color;}
			if($callback = self::get_callback()) {$get["callback"] = $callback;}
			if($autoplay = self::get_autoplay ()) {$get["autoplay"] = $autoplay;}
			if($xhtml = self::get_xhtml()) {$get["xhtml"] = $xhtml;}
			if($api = self::get_api()) {$get["api"] = $api;}
			if($wmode = self::get_wmode()) {$get["wmode"] = $wmode;}
			if($iframe = self::get_iframe()) {$get["iframe"] = $iframe;}
			$url = '';
			$url .= self::get_vimeo_base_url().$this->VimeoCode;
			if(is_array($get) && count($get)) {
				foreach($get as $key => $value) {
					$get[$key] = $key."=".urlencode($value);
				}
				$url .= "?".implode("&", $get);
			}
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$data = curl_exec($ch);
			$array = $this->my_xml2array($data);
			foreach($this->variables as $variable) {
				$data_array = $this->get_value_by_path($array, 'oembed/'.$variable);
				if(isset($data_array["name"]) && isset($data_array["value"])) {
					$this->data[$data_array["name"]] = $data_array["value"];
				}
				else {
					$this->data[$variable] = null;
				}
			}
			$this->Data = serialize($this->data);
			$this->HTMLSnippet = $this->data["html"];
			$this->write();
		}
	}


	function onBeforeWrite() {
		parent::onBeforeWrite();
		$this->VimeoCode = intval($this->VimeoCode);
		$this->doNotRetrieveData = true;
		$this->updateData();
	}

	function requireDefaultRecords() {
		parent::requireDefaultRecords();
		if(!self::$add_video_array_done) {
			if(is_array(self::$add_video_array) && count(self::$add_video_array)) {
				foreach(self::$add_video_array as $code => $title) {
					if(!DataObject::get_one("VimeoDataObject", "VimeoCode = ".$code)) {
						$vimeoDataObject = new VimeoDataObject;
						$vimeoDataObject->VimeoCode = $code;
						$vimeoDataObject->Title = $title;
						$vimeoDataObject->write();
						DB::alteration_message("added VIMEO: $title", "created");
					}
				}
			}
		}
		self::$add_video_array_done = true;
	}


	//SOURCE: http://php.net/manual/en/function.xml-parse.php
	private function my_xml2array($contents){
		$parser = xml_parser_create('');
		if(!$parser) {
			return false;
		}
		xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, trim($contents), $xml_values);
		xml_parser_free($parser);
		if (!$xml_values) {
			return array();
		}
		$xml_array = array();
		$last_tag_ar =& $xml_array;
		$parents = array();
		$last_counter_in_tag = array(1=>0);
		foreach ($xml_values as $data){
			switch($data['type']){
				case 'open':
					$last_counter_in_tag[$data['level']+1] = 0;
					$new_tag = array('name' => $data['tag']);
					if(isset($data['attributes'])) {
						$new_tag['attributes'] = $data['attributes'];
					}
					if(isset($data['value']) && trim($data['value'])) {
						$new_tag['value'] = trim($data['value']);
					}
					$last_tag_ar[$last_counter_in_tag[$data['level']]] = $new_tag;
					$parents[$data['level']] =& $last_tag_ar;
					$last_tag_ar =& $last_tag_ar[$last_counter_in_tag[$data['level']]++];
					break;
				case 'complete':
					$new_tag = array('name' => $data['tag']);
					if(isset($data['attributes'])) {
						$new_tag['attributes'] = $data['attributes'];
					}
					if(isset($data['value']) && trim($data['value'])) {
						$new_tag['value'] = trim($data['value']);
					}
					$last_count = count($last_tag_ar)-1;
					$last_tag_ar[$last_counter_in_tag[$data['level']]++] = $new_tag;
					break;
				case 'close':
					$last_tag_ar =& $parents[$data['level']];
					break;
				default:
					break;
			};
		}
		return $xml_array;
	}

	//
	// use this to get node of tree by path with '/' terminator
	//
	//SOURCE: http://php.net/manual/en/function.xml-parse.php
	private function get_value_by_path($__xml_tree, $__tag_path){
		$tmp_arr =& $__xml_tree;
		$tag_path = explode('/', $__tag_path);
		foreach($tag_path as $tag_name){
			$res = false;
			foreach($tmp_arr as $key => $node){
				if(is_int($key) && $node['name'] == $tag_name){
					$tmp_arr = $node;
					$res = true;
					break;
				}
			}
			if(!$res) {
				return false;
			}
		}
		return $tmp_arr;
	}


}

