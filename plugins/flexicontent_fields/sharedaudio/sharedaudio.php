<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

//jimport('joomla.plugin.plugin');
jimport('joomla.event.plugin');

class plgFlexicontent_fieldsSharedaudio extends JPlugin
{
	static $field_types = array('sharedaudio');
	
	// ***********
	// CONSTRUCTOR
	// ***********
	
	function plgFlexicontent_fieldsSharedaudio( &$subject, $params )
	{
		parent::__construct( $subject, $params );
		JPlugin::loadLanguage('plg_flexicontent_fields_sharedaudio', JPATH_ADMINISTRATOR);
	}
	
	
	
	// *******************************************
	// DISPLAY methods, item form & frontend views
	// *******************************************
	
	// Method to create field's HTML display for item form
	function onDisplayField(&$field, &$item)
	{
		// displays the field when editing content item
		$field->label = JText::_($field->label);
		if ( !in_array($field->field_type, self::$field_types) ) return;

		// some parameter shortcuts
		$required = $field->parameters->get('required',0);
		$required = $required ? ' required' : '';
		$embedly_key = $field->parameters->get('embedly_key','') ;
		
		$display_audiotype_form = $field->parameters->get('display_audiotype_form',1) ;
		$display_audioid_form   = $field->parameters->get('display_audioid_form',1) ;
		$display_title_form     = $field->parameters->get('display_title_form',1) ;
		$display_author_form    = $field->parameters->get('display_author_form',1) ;
		$display_duration_form    = $field->parameters->get('display_duration_form',1) ;
		$display_description_form = $field->parameters->get('display_description_form',1) ;
		
		// get stored field value
		if ( isset($field->value[0]) ) $value = unserialize($field->value[0]);
		else {
			$value['url'] = '';
			$value['audiotype'] = '';
			$value['audioid'] = '';
			$value['title'] = '';
			$value['author'] = '';
			$value['duration'] = '';
			$value['description'] = '';
		}
		if (!isset($value['url']))         $value['url'] = '';
		if (!isset($value['audiotype']))   $value['audiotype'] = '';
		if (!isset($value['audioid']))     $value['audioid'] = '';
		if (!isset($value['title']))       $value['title'] = '';
		if (!isset($value['author']))      $value['author'] = '';
		if (!isset($value['duration']))    $value['duration'] = '';
		if (!isset($value['description'])) $value['description'] = '';
		
		$field->html  = '';
		$field->html .= '
		<table class="admintable" border="0" cellspacing="0" cellpadding="5">
			<tr>
				<td class="key" align="right">'.JText::_('PLG_FLEXICONTENT_FIELDS_SHAREDAUDIO_AUDIO_URL').'</td>
				<td>
					<input type="text" class="fcfield_textval" name="custom['.$field->name.'][url]" value="'.$value['url'].'" size="60" '.$required.' />
					<input class="fcfield-button" type="button" value="'.JText::_('PLG_FLEXICONTENT_FIELDS_SHAREDAUDIO_FETCH').'" onclick="fetchAudio_'.$field->name.'();" />
					<span id="fcfield_fetching_msg_'.$field->id.'"></span>
				</td>
			</tr>'
		.($display_audiotype_form ? '
			<tr>
				<td class="key" align="right">'.JText::_('PLG_FLEXICONTENT_FIELDS_SHAREDAUDIO_AUDIO_TYPE').'</td>
				<td>
					<input type="text" class="fcfield_textval" name="custom['.$field->name.'][audiotype]" value="'.$value['audiotype'].'" size="10" readonly="readonly" style="background-color:#eee" />
				</td>
			</tr>' : '
			<input type="hidden" name="custom['.$field->name.'][audiotype]" value="'.$value['audiotype'].'" size="10" readonly="readonly" style="background-color:#eee" />')
		.($display_audioid_form ? '
			<tr>
				<td class="key" align="right">'.JText::_('PLG_FLEXICONTENT_FIELDS_SHAREDAUDIO_AUDIO_ID').'</td>
				<td>
					<input type="text" class="fcfield_textval" name="custom['.$field->name.'][audioid]" value="'.$value['audioid'].'" size="15" readonly="readonly" style="background-color:#eee" />
				</td>
			</tr>' : '
			<input type="hidden" name="custom['.$field->name.'][audioid]" value="'.$value['audioid'].'" size="15" readonly="readonly" style="background-color:#eee" />')
		.($display_title_form ? '
			<tr>
				<td class="key" align="right">'.JText::_('PLG_FLEXICONTENT_FIELDS_SHAREDAUDIO_TITLE').'</td>
				<td>
					<input type="text" class="fcfield_textval" name="custom['.$field->name.'][title]" value="'.$value['title'].'" size="60" />
				</td>
			</tr>' : '
			<input type="hidden" name="custom['.$field->name.'][title]" value="'.$value['title'].'" size="60" />')
		.($display_author_form ? '
			<tr>
				<td class="key" align="right">'.JText::_('PLG_FLEXICONTENT_FIELDS_SHAREDAUDIO_AUTHOR').'</td>
				<td>
					<input type="text" class="fcfield_textval" name="custom['.$field->name.'][author]" value="'.$value['author'].'" size="60" />
				</td>
			</tr>' : '
			<input type="hidden" name="custom['.$field->name.'][author]" value="'.$value['author'].'" size="60" />')
		.($display_duration_form ? '
			<tr>
				<td class="key" align="right">'.JText::_('PLG_FLEXICONTENT_FIELDS_SHAREDAUDIO_DURATION').'</td>
				<td>
					<input type="text" class="fcfield_textval" name="custom['.$field->name.'][duration]" value="'.$value['duration'].'" size="10" />
				</td>
			</tr>' : '
			<input type="hidden" name="custom['.$field->name.'][duration]" value="'.$value['duration'].'" size="10" />')
		.($display_description_form ? '
			<tr>
				<td class="key" align="right">'.JText::_('PLG_FLEXICONTENT_FIELDS_SHAREDAUDIO_DESCRIPTION').'</td>
				<td>
					<textarea class="fcfield_textareaval" name="custom['.$field->name.'][description]" rows="7" cols="50">'.$value['description'].'</textarea>
				</td>
			</tr>' : '
			<textarea style="display:none;" name="custom['.$field->name.'][description]" rows="7" cols="50">'.$value['description'].'</textarea>')
		;
		
		$field->html .= '<tr><td class="key" align="right">'.JText::_('PLG_FLEXICONTENT_FIELDS_SHAREDAUDIO_PREVIEW').'</td><td><div id="'.$field->name.'_thumb">';
		if($value['audiotype']!="" && $value['audioid']!="") {
			$iframecode = '<iframe class="sharedaudio" src="';
			switch($value['audiotype']){
				case "youtube":
					$iframecode .= "//www.youtube.com/embed/";
					break;
				case "vimeo":
					$iframecode .= "//player.vimeo.com/video/";
					break;
				case "dailymotion":
					$iframecode .= "//www.dailymotion.com/embed/video/";
					break;
				default:
					// For embed.ly we will have added 'URL' into our 'id' variable (below)
					break;
			}
			$val_id = $value['audioid'];  // In case of embed.ly, this is not id but it is full URL
			$iframecode .= $val_id.'" width="240" height="135" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';
			$field->html .= $iframecode;
		}
		$field->html	.= '</div></td></tr>';
		$field->html	.= '</table>';
		$field->html 	.= '
		<script type="text/javascript">
		function fetchAudio_'.$field->name.'() {
			var fieldname = \'custom['.$field->name.']\';
			var url = fieldname+"[url]";
			url = document.forms["adminForm"].elements[url].value;
			var audioID = "";
			var audioType = "";
			
			var _loading_img = "<img src=\"components/com_flexicontent/assets/images/ajax-loader.gif\" align=\"center\">";
			jQuery("#fcfield_fetching_msg_'.$field->id.'").html(_loading_img);
			
			if(window.console) window.console.log("Fetching "+url);
			// try youtube
			var myregexp = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i;
			if(url.match(myregexp) != null) {
				audioID = url.match(myregexp)[1];
				audioType = "youtube";
			}
			// try vimeo
			var myregexp = /http:\/\/(www\.)?vimeo.com\/(\d+)($|\/)/;
			if(url.match(myregexp) != null) {
				audioID = url.match(myregexp)[2];
				audioType = "vimeo";
			}
			// try dailymotion
			var myregexp = /^.+dailymotion.com\/(video|hub)\/([^_]+)[^#]*(#video=([^_&]+))?/;
			if(url.match(myregexp) != null) {
				audioID = url.match(myregexp)[4]!== undefined ? url.match(myregexp)[4] : url.match(myregexp)[2];
				audioType = "dailymotion";
			}
			var jsonurl;
			updateValueInfo_'.$field->name.'({title:"", author:"", duration:"", description:"", thumb:""});
			updateValueTypeId_'.$field->name.'(audioType,audioID);
			if(audioID && audioType){
				if(window.console) window.console.log("Audio type: "+audioType);
				if(window.console) window.console.log("Audio ID: "+audioID);
				switch(audioType) {
					case "youtube":
						jsonurl = "//gdata.youtube.com/feeds/api/videos/"+audioID+"?v=2&alt=json-in-script&callback=youtubeCallback_'.$field->name.'";
						break;
					case "vimeo":
						jsonurl = "//vimeo.com/api/v2/video/"+audioID+".json?callback=vimeoCallback_'.$field->name.'";
						break;
					case "dailymotion":
						jsonurl = "https://api.dailymotion.com/video/"+audioID+"?fields=description,duration,owner.screenname,thumbnail_60_url,title&callback=dailymotionCallback_'.$field->name.'";
						break;
				}
			}
			else { 
				// try embed.ly
				jsonurl = "http://api.embed.ly/1/oembed?url="+encodeURIComponent(url)+"&key='.$embedly_key.'&callback=embedlyCallback_'.$field->name.'";
			}
			if(url!="") {
				var jsonscript = document.createElement("script");
				jsonscript.setAttribute("type","text/javascript");
				jsonscript.setAttribute("src",jsonurl);
				document.body.appendChild(jsonscript);
			}
			else {
				updateValueInfo_'.$field->name.'({title:"", author:"", duration:"", description:"", thumb:""});
				updateValueTypeId_'.$field->name.'("","");				
			}
		}
		function youtubeCallback_'.$field->name.'(data){
			updateValueInfo_'.$field->name.'({title: data.entry.title.$t, author: data.entry.author[0].name.$t, duration: data.entry.media$group.yt$duration.seconds, description: data.entry.media$group.media$description.$t, thumb: data.entry.media$group.media$thumbnail[0].url});
			jQuery("#fcfield_fetching_msg_'.$field->id.'").html("");
		}
		function vimeoCallback_'.$field->name.'(data){
			updateValueInfo_'.$field->name.'({title: data[0].title, author: data[0].user_name, duration: data[0].duration, description: data[0].description, thumb: data[0].thumbnail_small});
			jQuery("#fcfield_fetching_msg_'.$field->id.'").html("");
		}
		function dailymotionCallback_'.$field->name.'(data){
			updateValueInfo_'.$field->name.'({title: data.title, author: data["owner.screenname"], duration: data.duration, description: data.description, thumb: data.thumbnail_60_url});
			jQuery("#fcfield_fetching_msg_'.$field->id.'").html("");
		}
		function embedlyCallback_'.$field->name.'(data){
			if(data.type!="error") {
				var myregexp = /(http:|ftp:|https:)?\/\/[\w-]+(\.[\w-]+)+([\w.,@?^=%&amp;:\/~+#-]*[\w@?^=%&amp;\/~+#-])?/;
				if(data.html.match(myregexp) != null) {
					var iframeurl = data.html.match(myregexp)[0];
					var iframecode = \'<iframe class="sharedaudio" src="\'+iframeurl+\'" width="240" height="135" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>\';
					updateValueTypeId_'.$field->name.'("embed.ly:"+data.provider_name.toLowerCase(),iframeurl);
					updateValueInfo_'.$field->name.'({title: data.title, author: data.author_name, duration: "", description: data.description, thumb: data.thumbnail_url});
					document.getElementById("'.$field->name.'_thumb").innerHTML = iframecode;
				}
			}
			else {
				alert("'.JText::_('PLG_FLEXICONTENT_FIELDS_SHAREDAUDIO_UNABLE_TO_PARSE').'"); 
				updateValueInfo_'.$field->name.'({title:"", author:"", duration:"", description:"", thumb:""});
				updateValueTypeId_'.$field->name.'("","");				
			}
			jQuery("#fcfield_fetching_msg_'.$field->id.'").html("");
		}
		function updateValueTypeId_'.$field->name.'(audioType,audioID){
			var fieldname = \'custom['.$field->name.']\';
			field = fieldname+"[audiotype]";
			document.forms["adminForm"].elements[field].value = audioType;
			field = fieldname+"[audioid]";
			document.forms["adminForm"].elements[field].value = audioID;
			if(audioType!="" && audioID!="") {
				var iframecode = \'<iframe class="sharedaudio" src="\';
				switch(audioType){
					case "youtube":
						iframecode += "//www.youtube.com/embed/";
						break;
					case "vimeo":
						iframecode += "//player.vimeo.com/video/";
						break;
					case "dailymotion":
						iframecode += "//www.dailymotion.com/embed/video/";
						break;
				}
				iframecode += audioID + \'" width="240" height="135" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>\';
				document.getElementById("'.$field->name.'_thumb").innerHTML = iframecode;
			}
			else {
				document.getElementById("'.$field->name.'_thumb").innerHTML = "";
			}

		}
		function updateValueInfo_'.$field->name.'(data){
			var fieldname = \'custom['.$field->name.']\';
			field = fieldname+"[title]";
			document.forms["adminForm"].elements[field].value = data.title;
			field = fieldname+"[author]";
			document.forms["adminForm"].elements[field].value = data.author;
			field = fieldname+"[duration]";
			document.forms["adminForm"].elements[field].value = data.duration;
			field = fieldname+"[description]";
			document.forms["adminForm"].elements[field].value = data.description;
		}
		</script>';

	}
	
	
	// Method to create field's HTML display for frontend views
	function onDisplayFieldValue(&$field, $item, $values=null, $prop='display')
	{
		// displays the field in the frontend
		
		$field->label = JText::_($field->label);
		if ( !in_array($field->field_type, self::$field_types) ) return;
		
		// Get field values
		$values = $values ? $values : $field->value;
		
		$field->{$prop} = '';
		foreach ( $values as $value )
		{
			if (empty($value)) continue;
			
			$value = unserialize($value);
			if ( empty($value['audioid']) ) continue;
			
			// some parameter shortcuts
			$display_title = $field->parameters->get('display_title',1) ;
			$display_author = $field->parameters->get('display_author',0) ;
			$display_duration = $field->parameters->get('display_duration',0) ;
			$display_description = $field->parameters->get('display_description',0) ;
			
			$pretext = $field->parameters->get('pretext','') ;
			$posttext = $field->parameters->get('posttext','') ;
			
			$headinglevel = $field->parameters->get('headinglevel',3) ;
			$width = $field->parameters->get('width',480) ;
			$height = $field->parameters->get('height',270) ;
			$autostart = $field->parameters->get('autostart',0) ;
			
			// generate html output
			$field->{$prop} .= $pretext;
			$field->{$prop} .= '<iframe class="sharedaudio" src="';
			switch($value['audiotype']){
				case 'youtube':
					$field->{$prop} .= '//www.youtube.com/embed/';
					$_show_related = '&rel=0';
					$_show_srvlogo = '&modestbranding=1';
					break;
				case 'vimeo':
					$field->{$prop} .= '//player.vimeo.com/video/';
					$_show_related = '';
					$_show_srvlogo = '';
					break;
				case 'dailymotion':
					$field->{$prop} .= '//www.dailymotion.com/embed/video/';
					$_show_related = '&related=0';
					$_show_srvlogo = '&logo=0';
					break;
				default:
					// For embed.ly we will have added 'URL' into our 'id' variable (below)
					break;
			}
			$val_id = $value['audioid'];  // In case of embed.ly, this is not id but it is full URL
			$field->{$prop} .= '
				'.$val_id.'?autoplay='.$autostart.$_show_related.$_show_srvlogo.'" width="'.$width.'" height="'.$height.'" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
				'.($display_title && !empty($value['title'])   ? '<h'.$headinglevel.'>'.$value['title'].'</h'.$headinglevel.'>' : '').'
				'.($display_author && !empty($value['author']) ? '<div class="author">'.$value['author'].'</div>' : '')
				;
			$duration = intval($value['duration']);
			if ($display_duration && $duration) {
				if ($duration >= 3600) $h = intval($duration/3600);
				if ($duration >= 60)   $m = intval($duration/60 - $h*60);
				$s = $duration - $m*60 -$h*3600;
				if ($h>0) $h .= ":";
				$m = str_pad($m,2,'0',STR_PAD_LEFT).':';
				$s = str_pad($s,2,'0',STR_PAD_LEFT);
				$field->{$prop} .= '<div class="duration">'.$h.$m.$s.'</div>';
			}
			if ($display_description && !empty($value['description'])) $field->{$prop} .= '<div class="description">'.$value['description'].'</div>';
			$field->{$prop} .= $posttext;
		}
	}
	
	
	
	// **************************************************************
	// METHODS HANDLING before & after saving / deleting field events
	// **************************************************************
	
	// Method to handle field's values before they are saved into the DB
	function onBeforeSaveField( $field, &$post, &$file )
	{
		if ( !in_array($field->field_type, self::$field_types) ) return;
		
		$use_ingroup = $field->parameters->get('use_ingroup', 0);
		if ( !is_array($post) && !strlen($post) && !$use_ingroup ) return;
		
		// Currently post is an array of properties, TODO: make field multi-value
		$post = array(0=>$post);
		
		// Reformat the posted data
		$newpost = array();
		$new = 0;
		foreach ($post as $n => $v)
		{
			// ***********************************************************
			// Validate URL, skipping URLs that are empty after validation
			// ***********************************************************
			
			$url = flexicontent_html::dataFilter($post[$n]['url'], 0, 'URL', 0);  // Clean bad text/html
			if ( empty($url) && !$use_ingroup ) continue;  // Skip empty values if not in field group
			
			$newpost[$new] = array();
			$newpost[$new]['url'] = $url;
			
			// Validate other value properties
			$newpost[$new]['audiotype'] = flexicontent_html::dataFilter(@$post[$n]['audiotype'], 0, 'STRING', 0);
			$newpost[$new]['audioid'] = flexicontent_html::dataFilter(@$post[$n]['audioid'], 0, 'STRING', 0);
			$newpost[$new]['title'] = flexicontent_html::dataFilter(@$post[$n]['title'], 0, 'STRING', 0);
			$newpost[$new]['author'] = flexicontent_html::dataFilter(@$post[$n]['author'], 0, 'STRING', 0);
			$newpost[$new]['duration'] = flexicontent_html::dataFilter(@$post[$n]['duration'], 0, 'INT', 0);
			$newpost[$new]['description'] = flexicontent_html::dataFilter(@$post[$n]['description'], 0, 'STRING', 0);

			$new++;
		}
		
		$post = $newpost;
		
		// Serialize multi-property data before storing them into the DB
		foreach($post as $i => $v) {
			$post[$i] = serialize($v);
		}
	}
	
	
	
	// *************************
	// SEARCH / INDEXING METHODS
	// *************************
	
	// Method to create (insert) advanced search index DB records for the field values
	function onIndexAdvSearch(&$field, &$post, &$item)
	{
		if ( !in_array($field->field_type, self::$field_types) ) return;
		if ( !$field->isadvsearch && !$field->isadvfilter ) return;
		
		FlexicontentFields::onIndexAdvSearch($field, $post, $item, $required_properties=array('url'), $search_properties=array('title','author','description','audiotype'), $properties_spacer=' ', $filter_func=null);
		return true;
	}
	
	
	// Method to create basic search index (added as the property field->search)
	function onIndexSearch(&$field, &$post, &$item)
	{
		if ( !in_array($field->field_type, self::$field_types) ) return;
		if ( !$field->issearch ) return;
		
		FlexicontentFields::onIndexSearch($field, $post, $item, $required_properties=array('url'), $search_properties=array('title','author','description','audiotype'), $properties_spacer=' ', $filter_func=null);
		return true;
	}

}