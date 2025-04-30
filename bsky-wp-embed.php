<?php /*

**************************************************************************

Plugin Name:  Bluesky Embed
Plugin URI:   https://github.com/sbm12/Bsky-WP-Embed/
Description:  Quickly and easily embed Blusky posts using the native formatting
Version:      0.0.1
Author:       Seth Miller
Author URI:   https://paxex.aero/

**************************************************************************/


	// Handle BSKY shortcodes
	function shortcode_bsky( $atts, $content='' ) {
		// Set any missing $atts items to the defaults
		$atts = shortcode_atts(array(
					'color'      => 'system',
		), $atts);
		switch (strtolower($atts['color'])){
					case 'light':
						$colorScheme='light';
						break;
					case 'dark':
						$colorScheme='dark';
						break;
					default:
						$colorScheme='system';
		}
		if(!empty($content)){
			$embedURL=$content;
			$arrURI=explode('/',$embedURL);
			//extract profile name 
			$userHandle=$arrURI[count($arrURI)-3];
			//extract postID
			$postID=$arrURI[count($arrURI)-1];
			//parse for did & display name
			try{
				$url="https://public.api.bsky.app/xrpc/app.bsky.actor.getProfile?actor=" . strtolower($userHandle);
				$file = file_get_contents($url, false);
				$json = json_decode($file, true);
				$uDID=$json['did'];
				$uDisplay=$json['displayName'];
				}

			catch (exception $e){
				$uDID='';
			}
			if($uDID){
				//Create at: URI	at://[did]/app.bsky.feed.post/[postid]
				$atURI='at://' . $uDID . '/app.bsky.feed.post/' . $postID;

				//Query post api https://public.api.bsky.app/xrpc/app.bsky.feed.getPosts?uris=[at uri]
				try{
					$url="https://public.api.bsky.app/xrpc/app.bsky.feed.getPosts?uris=" . strtolower($atURI);
					$file = file_get_contents($url, false);
					$json = json_decode($file, true)['posts'][0];
					//get post CID
					$pCID=$json['cid'];
					//get post Date/Time
					$pDate=$json['record']['createdAt'];
					//get post Text
					$pText=$json['record']['text'];
					//get post Language
					$pLang=$json['record']['langs'][0];
					//get post embed
					$pEmbed=$json['record']['embed'];
					}

				catch (exception $e){
					$output='<!-- Embed Generation Error -->';
				}
		
		    //assemble the embed code:
		
		    $output='<blockquote class="bluesky-embed" data-bluesky-uri="' . $atURI . '" data-bluesky-cid="' . $pCID . '" data-bluesky-embed-color-mode="' . $colorScheme . '"><p lang="' . $pLang . '">' . $pText;
		    if ($pEmbed){
		        $output .= '<br><br><a href="https://bsky.app/profile/' . $uDID . '/post/' . $postID . '?ref_src=embed">[image or embed]</a>';
		    }
		    $output .= '</p>&mdash; ' . $uDisplay . '(<a href="https://bsky.app/profile/' . $uDID . '?ref_src=embed">@' . $userHandle . '</a>) <a href="https://bsky.app/profile/[userDID]/post/[postID]?ref_src=embed">' . $pDate . '</a></blockquote><script async src="https://embed.bsky.app/static/embed.js" charset="utf-8"></script>';
		}
		else {
		    $output='<!-- Embed Generation Error -->';
		}
		
		return do_shortcode('<!--beginBsky-->' . $output . '<!--endBsky-->');

		} else {
			return do_shortcode("<!-- Bsky Error " . $atts['url'] . "|" . $content . " -->");
		}

	}

	// Register shortcodes
	add_shortcode( 'bsky', 'shortcode_bsky' );

?>
