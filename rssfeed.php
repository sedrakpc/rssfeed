<?php
/**
 * @version $Id: rssfeed.php  2008-03-04 22:47 
 * @ sedrakpc
 * @package     Joomla
 * @subpackage	Content
 * @copyright	This extension in released under the GNU/GPL License
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die();
jimport( 'joomla.event.plugin' );

$enabled = JPluginHelper :: isEnabled  ('content','rssfeed');	
/**
 * Content Plugin
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 		1.5
 */

class plgContentrssfeed extends JPlugin
{
/**
 * Constructor - note in Joomla 2.5 PHP4.x is no longer supported so we can use this.
 *
 * @access      protected
 * @param       object  $subject The object to observe
 * @param       array   $config  An array that holds the plugin configuration
 */
public function __construct(& $subject, $config)
{
  parent::__construct($subject, $config);
}

	/**
	 * prepare content method
	 *
	 * Method is called by the view
	 *
	 * @param 	object		The article object.  Note $article->text is also available
	 * @param 	object		The article params
	 * @param 	int			The 'page' number
	 */
  public function onContentPrepare($context, &$row, &$params, $page = 0)
	//function onPrepareContent( &$article, &$params, $limitstart=0 )
	{
		$plugin =& JPluginHelper::getPlugin('content','rssfeed');
		$this->params =& new JForm( $plugin->params );
	// require_once( JURI::root(true).'/includes/domit/xml_saxy_lite_parser.php' );//xml_domit_lite_parser.php
	//$live_site = JURI::base();
	// require_once( JPATH_SITE.DS.'plugins/content/simplepie/simplepie.inc.php');
  		// Start IFRAME Replacement
        // define the regular expression for the bot
	$regex = "#{rss*(.*?)}(.*?){/rss}#s";
	$plugin_enabled = $this->params->getValue('enabled', NULL, '1');
	if($plugin_enabled=="0"){//Clear TAG
	$row->text = preg_replace($regex, '', $row->text);
	}
	else {
	/************************************************/
	//		require_once( JURI::root(true).'/plugins/content/simplepie/simplepie.inc.php');
	/************************************************/
  		
	if (preg_match_all($regex, $row->text, $matches, PREG_SET_ORDER) > 0) {  
	foreach ($matches as $match) {
/*************************************************/   

		$rssrtl				= (int) $this->params->getValue( 'rssrtl', NULL,'0' );   //ltr rtl
		$rssurl				= $this->params->getValue( 'rssurl', NULL,'' );
		$rsstitle			= (int) $this->params->getValue( 'rsstitle', NULL, 0 );
		$rssitems 			= (int) $this->params->getValue( 'rssitems', NULL, 10 );
		$rssdesc 			= (int) $this->params->getValue( 'rssdesc', NULL, 0 );
		$rssimage 			= (int) $this->params->getValue( 'rssimage', NULL, 1 );
		$rssimagelink 			= $this->params->getValue( 'rssimagelink', NULL, 1 );
		$rssitemdesc			= $this->params->getValue( 'rssitemdesc', NULL, 1 );
		$words_t			= (int) $this->params->getValue( 'word_count_title', NULL, 0 );
		$words_d			= (int) $this->params->getValue( 'word_count_descr', NULL, 0 );
		$rsscache			= (int) $this->params->getValue( 'rsscache', NULL, 10 ); //min
		$height			= (int) $this->params->getValue('height', NULL, 240);
		$width			= (int) $this->params->getValue('width', NULL, 320);
		$media_align    = $this->params->getValue('media_align', NULL, 'center');
		$mp3_player    = (int) $this->params->getValue('mp3_player', NULL, '0'); //0:mp3, 1:1pixel; 2:dewplayer
		$media_enable   = (int) $this->params->getValue('media_enable', NULL, '0'); //0:Disable, 1:Enable;
/*************************************************/
	$content_buffer = '';
	$params0 = '';
	if(@$match[2])$rssurl = strip_tags(rtrim(ltrim($match[2])));

		if (get_magic_quotes_gpc())
			{
				$rssurl = stripslashes($rssurl);
			}
	
	if(@$match[1]){
	$params0 = & JUtility::parseAttributes($match[1]);
	if(isset($params0['rsstitle'])) $rsstitle = (int)$params0['rsstitle'];
	if(isset($params0['rssitems'])) $rssitems = (int)$params0['rssitems'];
	if(isset($params0['rssdesc'])) $rssdesc = (int)$params0['rssdesc'];
	if(isset($params0['rssimage'])) $rssimage = (int)$params0['rssimage'];
	if(isset($params0['rssimagelink'])) $rssimagelink = (int)$params0['rssimagelink'];
	if(isset($params0['word_count_title'])) $words_t = (int)$params0['word_count_title'];
	if(isset($params0['word_count_descr'])) $words_d = (int)$params0['word_count_descr'];
	if(isset($params0['rssitemdesc'])) $rssimage = (int)$params0['rssitemdesc'];
	if(isset($params0['rsscache'])) $rsscache = (int)$params0['rsscache'];
	if(isset($params0['width'])) $width = (int)$params0['width'];
	if(isset($params0['height'])) $height = (int)$params0['height'];
	if(isset($params0['media_align'])) $media_align = (int)$params0['media_align'];
	if(isset($params0['mp3_player'])) $mp3_player = (int)$params0['mp3_player']; //
	if(isset($params0['media_enable'])) $media_enable = (int)$params0['media_enable']; //media_enable

//legacy for Joomla 1.0.x
	if(isset($params0['cache'])) $rsscache = (int)$params0['cache'];
	if(isset($params0['items'])) $rssitems = (int)$params0['items'];
	if(isset($params0['word_count'])) $words_d = (int)$params0['word_count'];
	}
	
/**************************************************************************/
		if( trim($rssurl) != "") { 			// only if array element is not empty

		  // get RSS parsed object
		  $options = array();
		  $options['rssUrl'] = $rssurl;
		  $options['cache_time'] = $rsscache*60;
		  $rssDoc =& JFactory::getXMLparser('RSS', $options);
		  //$rssDoc->set_output_encoding();
		  $feed = new stdclass();

		  if ($rssDoc != false)
		  {
		  // channel header and link
		  $isutf8 = (strtolower($rssDoc->get_encoding()) == 'utf-8');
		  $feed->title = $rssDoc->get_title();
		  $feed->link = $rssDoc->get_link();
		  $feed->description = $rssDoc->get_description();

		  // channel image if exists
		  $feed->image->url = $rssDoc->get_image_url();
		  $feed->image->title = $rssDoc->get_image_title();

		  // items
		  $items = $rssDoc->get_items();
  
		  // feed elements
		  $feed->items = array_slice($items, 0, $rssitems);
		  } else {
		  $feed = false;
		  }
/****************************************************************************/
    $content_buffer .= '<div style="direction: ' . ($rssrtl ? 'rtl; ' :'ltr; ') . 'text-align: '. ($rssrtl ? 'right;' :'left;') .'">';
    if( $feed != false )
    {
	if($override_charset){ 
	//set / override Joomla HTML page metatag charset to match RSS character encoding
	$feed->handle_content_type(); 
	}
    //image handling
    $iUrl = isset($feed->image->url) ? $feed->image->url : null;
    $iTitle = isset($feed->image->title) ? $feed->image->title : null;

    $content_buffer .= '<table cellpadding="0" cellspacing="0" class="'. $this->params->getValue('moduleclass_sfx', NULL, '').'">';
    
    // feed title
    if (!is_null( $feed->title ) && $rsstitle) {
    $content_buffer .= '
      <tr>
        <td>
          <strong>
          <a href="'. str_replace( '&', '&amp', $feed->link ).'" target="_blank">
           '. $this->_text2utf8($feed->title,$isutf8) .'</a>
          </strong>
          </td>
    </tr>';
    }
    // feed description
    if ($rssdesc) {
    $content_buffer .= '
      <tr>
        <td>'.$this->_text2utf8($feed->description,$isutf8) .'</td>
      </tr>';
    }

    // feed image
    if ($rssimage && $iUrl) {
    if($rssimagelink) 
    $content_buffer .= '
      <tr>
        <td align="left"><a href="'. str_replace( '&', '&amp', $feed->link ).'" target="_blank"><img src="'. $iUrl .'" alt="'. @$iTitle .'"/></a></td>
      </tr>';
    else
    $content_buffer .= '
      <tr>
        <td align="left"><image src="'. $iUrl .'" alt="'. @$iTitle .'"/></td>
      </tr>';
    }    
    
    $actualItems = count( $feed->items );
    $setItems = $rssitems;

    if ($setItems > $actualItems) {
    $totalItems = $actualItems;
    } else {
      $totalItems = $setItems;
    }
    $content_buffer .= '
      <tr>
        <td>
        <ul class="newsfeed'.$this->params->getValue( 'moduleclass_sfx', NULL, '') .'">';


      for ($j = 0; $j < $totalItems; $j ++)
      {
        $currItem = & $feed->items[$j];
      // item title
          $content_buffer .= "\n".'<li style="padding-bottom:14px;">';

      if ( !is_null( $currItem->get_link() ) ) {
            // word limit check
      //$title = html_entity_decode($currItem->get_title());
      //$title = str_replace('&apos;', "'", $title); 
      $title = $this->_text2utf8($currItem->get_title(),$isutf8); 
      if($words_t){  
        $texts = explode(' ', $title);
        $count = count($texts);             
        if ($count > $words_t)
          {
         
          $text = '';
          for ($i = 0; $i < $words_t; $i ++) {
          $title .= ' '.$texts[$i];
          }
        $title .= '...';
          }
      }
      
      $content_buffer .= '<a href="' . $currItem->get_link(). '" target="_blank">'.
      $title .'</a>';      
      }
    // item description
    if ($rssitemdesc)
      {
        // item description
      //$text = html_entity_decode($currItem->get_description());
      //$text = str_replace('&apos;', "'", $text);
      $text = $this->_text2utf8($currItem->get_description(),$isutf8); 
  
      // word limit check
      if ($words_d)
      {
        $texts = explode(' ', $text);

        $count = count($texts);
        if ($count > $words)
          {
          $text = '';
          for ($i = 0; $i < $words_d; $i ++) {
          $text .= ' '.$texts[$i];
          }
        $text .= '...';
          }
      }
    $content_buffer .= '<div style="text-align: '. ($rssrtl ? 'right': 'left') . '! important">';
    $content_buffer .= $text . '</div>';
    if($media_enable){
        if(!is_null($currItem->get_enclosure())){
            $content_buffer .= '<div style="text-align:'.$media_align.';">'. $this->mediaplayer($this->_text2utf8($currItem->get_enclosure()->get_link(),$isutf8),$currItem->get_enclosure()->get_type(), $width, $height, $mp3_player).'</div>';
            }  
        }
    }
    $content_buffer .= '</li>';
    }
    $content_buffer .= '
        </ul>
      </td>
    </tr>
    </table>';
    }
    $content_buffer .= '    
    </div>
    ';
    }

/****************************************************************************/

    $row->text = preg_replace($regex, $content_buffer, $row->text, 1);
    } //end of for each
    } //end of preg_match_all
  }//end of else enable
	} // End Function
function _text2utf8($text,$isutf8){
	$text = str_replace('&nbsp;', " ",$text);
	$text = str_replace('&', "&amp;",$text);	
if($isutf8){
	$text = str_replace('&apos;', "'", html_entity_decode($text, ENT_QUOTES));//html_entity_decode
}else{
	//return utf8_html_entity_decode($text,null,'UTF-8');
	$text = preg_replace("/([\x80-\xFF])/e","chr(0xC0|ord('\\1')>>6).chr(0x80|ord('\\1')&0x3F)",$text);
	//$text=iconv("ISO-8859-1","UTF-8",$text);
	$text = str_replace('&apos;', "'", html_entity_decode($text, ENT_QUOTES));//html_entity_decode
}
	$text = str_replace('&amp;', "&",$text);
return $text;
}

function mediaplayer($link, $type=null,$width='100%', $height, $mp3_player = 0){
//http://en.wikipedia.org/wiki/Mime_type
//http://www.1pixelout.net/
//http://www.nesium.com/blog/2006/10/05/flash-mp3-player/
    if($type){
    $t_type = explode("/", $type);
    switch ($t_type[0]) {
        case 'audio':
            switch ($t_type[1]) {
                case 'mpeg': 
                    switch ($mp3_player) {
                    case 0:
                    $tag_new = '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" width="275" height="50" id="mp3" align="'.$align.'"><param name="allowScriptAccess" value="sameDomain" /><param name="movie" value="'.JURI::root(true).'/plugins/content/rssfeed/mp3.swf?mp3url='.urlencode($link).'&amp;bgcolor=#ffffff&amp;txtcolor=#336699&amp;barbgcolor=#999999&amp;loadbar=#3c3c3c&amp;posbar=000000&amp;loop=false" /><param name="quality" value="high" /><param name="bgcolor" value="#ffffff" /><param name="wmode" value="transparent" /><embed src="'.JURI::root(true).'/plugins/content/rssfeed/mp3.swf?mp3url='.urlencode($link).'&amp;bgcolor=#ffffff&amp;txtcolor=#336699&amp;barbgcolor=#999999&amp;loadbar=#3c3c3c&amp;posbar=000000&amp;loop=false" quality="high" bgcolor="#ffffff" width="275" height="50" name="mp3" wmode="transparent" align="'.$align.'" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" /></object>';
                    break;
                    case 1:
                    $tag_new = '<object type="application/x-shockwave-flash" data="' . JURI::root(true).'/plugins/content/rssfeed/1pixel.swf" id="audioplayer1" height="24" width="290">'.
                    '<param name="movie" value="'. JURI::root(true).'/plugins/content/rssfeed/1pixel.swf">'.
                    '<param name="FlashVars" value="playerID=1&amp;soundFile='.$link.'">'.
                    '<param name="quality" value="high">'.
                    '<param name="menu" value="false">'.
                    '<param name="wmode" value="transparent">'.
                    '</object>';
                    break;
                    case 2:
                    default:
                    $tag_new = '<object type="application/x-shockwave-flash" data="' . JURI::root(true).'/plugins/content/rssfeed/dewplayer.swf?mp3='.urlencode($link).'&amp;showtime=1" width="200" height="20">';
                    $tag_new .= '<param name="wmode" value="transparent" />';
                    $tag_new .= '<param name="movie" value="' . JURI::root(true).'/plugins/content/rssfeed/dewplayer.swf?mp3='.urlencode($link).'&amp;showtime=1" />';
                    $tag_new .= '<embed src="' . JURI::root(true).'/plugins/content/rssfeed/dewplayer.swf?mp3='.urlencode($link).'&amp;showtime=1" quality="high" width="200" height="20" name="dewplayer" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer"></embed></object>';
                    break;
                    }                           
                break;
                
                default: $tag_new = $link;
                break;
            }
            break;
        case 'application':
            switch ($t_type[1]) {
                case 'x-shockwave-flash': 
                $tag_new = '<embed src="'.$link.'" pluginspage="http://adobe.com/go/getflashplayer" type="application/x-shockwave-flash" quality="high" width="'.$width.'" height="'.$height.'" bgcolor="#ffffff" loop="false"></embed>';               
                break;
                
            default: $tag_new = $link;
                break;
            }
            break;        
        default: 
            $tag_new = $link;
        break;
        }
    }
    return $tag_new;
}


} // End Class
?>