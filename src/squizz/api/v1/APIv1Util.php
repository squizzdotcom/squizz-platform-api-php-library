<?php
	/**
	* Copyright (C) Squizz PTY LTD
	* This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
	* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
	* You should have received a copy of the GNU General Public License along with this program.  If not, see http://www.gnu.org/licenses/.
	*/
	namespace squizz\api\v1;

	/**
	* Class contains common utility functions
	*/
	class APIv1Util
	{
		/**
		* Marks up the plain text given to HTML, using the SQUIZZ.com platform's mark up rules to convert plain text into HTML structure
		* @param text string plain text to mark up
		* @param linkifyText boolean, if true then allow URLs in the text to be turned into anchor tags
		* @param anchorCSSClassName name of the CSS class to set for styling embedded anchor tags
		* @param boldCSSClassName name of the CSS class to set for styling text that is bolded
		* @param underlineCSSClassName name of the CSS class to set for styling text that is underlined
		* @param italicCSSClassName name of the CSS class to set for styling text that is italicised
		* @param lineThroughCSSClassName = name of the CSS class to set for styling text that contains a strike through
		* @param headingOneCSSClassName = name of the CSS class to set for styling size 1 headings
		* @param headingTwoCSSClassName = name of the CSS class to set for styling size 2 headings
		* @param headingThreeCSSClassName name of the CSS class to set for styling size 3 headings
		* @param listCSSClassName name of the CSS class to set for styling lists
		* @return string marked up text as HTML
		*/
		public static function markupTextToHTML(
			$text, 
			$linkifyText = true, 
			$anchorCSSClassName = 'markup_anchor', 
			$boldCSSClassName = 'markup_bold', 
			$underlineCSSClassName = 'markup_underline', 
			$italicCSSClassName = 'markup_italic', 
			$lineThroughCSSClassName = 'markup_line_through', 
			$headingOneCSSClassName = 'markup_h1', 
			$headingTwoCSSClassName = 'markup_h2', 
			$headingThreeCSSClassName = 'markup_h3',
			$listCSSClassName = 'markup_list'
		)
		{
			$markUpHTML = $text;

			// markup bold, underline, italic, line through text, and headings
			$markUpHTML = preg_replace('/\*\*(.*?)\*\*/', '<span class="'.$boldCSSClassName.'">$1</span>', $markUpHTML);
			$markUpHTML = preg_replace('/__(.*?)__/', '<span class="'.$underlineCSSClassName.'">$1</span>', $markUpHTML);
			$markUpHTML = preg_replace('/~~(.*?)~~/', '<span class="'.$italicCSSClassName.'">$1</span>', $markUpHTML);
			$markUpHTML = preg_replace('/--(.*?)--/', '<span class="'.$lineThroughCSSClassName.'">$1</span>', $markUpHTML);
			$markUpHTML = preg_replace('/####(.*?)####/', '<h3 class="'.$headingThreeCSSClassName.'">$1</h3>', $markUpHTML);
			$markUpHTML = preg_replace('/###(.*?)###/', '<h2 class="'.$headingTwoCSSClassName.'">$1</h2>', $markUpHTML);
			$markUpHTML = preg_replace('/##(.*?)##/', '<h1 class="'.$headingOneCSSClassName.'">$1</h1>', $markUpHTML);
			
			// split text into individual lines
			$textLines = explode("\n", $markUpHTML);
			$listCharPos = 0;
			$listsAmount = -1;
			$markupList = array();
			
			//iterate through each line and markup list elements where applicable
			foreach($textLines as $textLine){
				$textLine = trim($textLine);
				$listCharPos = strpos($textLine, '- ');
				
				//check if the line starts with a list prefix
				if($listCharPos !== false){
					//open new list if another dash character has been added
					if($listCharPos > $listsAmount){
						array_push($markupList,'<ul class="'.$listCSSClassName.'">');
					}
					if($listCharPos < $listsAmount){
						array_push($markupList,'</ul>');
						$listsAmount--;
					}
					
					array_push($markupList,'<li> '.substr($textLine, $listCharPos+1).' </li>');
					$listsAmount = $listCharPos;
				}else{
					while($listsAmount-- > -1){
						array_push($markupList,'</ul> ');
					}
					array_push($markupList, $textLine . ' <br/> ');
					$listsAmount = -1;
				}
			}

			//close off any open list tags
			while($listCharPos !== false && $listCharPos-- > -1){
				array_push($markupList, '</ul> ');
			}

			//join all the list elements together
			$markUpHTML = join('',$markupList);
			
			//place anchors for URLs
			if($linkifyText){
				$markUpHTML = self::linkifyTextToHTML($markUpHTML, $anchorCSSClassName);
			}
			
			return $markUpHTML;
		}

		/**
		 * finds urls within the given string text and replaces them with anchor tags to show as links
		 * @param text string plain text to mark up URLs within to HTML anchor tags
		 * @param anchorCSSClassName name of the CSS class to set for styling anchor tags
		 * @return string mark up text with links replaced with HTML anchor tags
		 */
		public static function linkifyTextToHTML($text, $anchorCSSClassName)
		{
			$markUpHTML = $text;

			// match URLs starting with http://, https://, ftp://
			$urlPattern = '/\b(?:https?|ftp):\/\/[a-z0-9-+&\(\)@#\/%?=~_|!:,.;]*[a-z0-9-+&@#\/%=~_|]/im';
			
			// match URLs starting with www, sans http:// or https://
			$pseudoUrlPattern = '/(^|[^\/])(www\.[\S]+(\b|$))/im';
			
			// match Email addresses
			$emailAddressPattern = '/[\w.]+@[a-zA-Z_-]+?(?:\.[a-zA-Z]{2,6})+/im';

			// embed anchor tags for URLs and email addresses found
			$markUpHTML = preg_replace($urlPattern, ' <a target="_blank" class="' . $anchorCSSClassName . '" href="$0">$0</a>', $markUpHTML);
			$markUpHTML = str_replace('href="https:// ', 'href="https://', preg_replace($pseudoUrlPattern, ' <a target="_blank" class="' . $anchorCSSClassName . '" href="https://$0">$0</a>', $markUpHTML));
			$markUpHTML = preg_replace($emailAddressPattern, ' <a class="' . $anchorCSSClassName . '" href="mailto:$0">$0</a>', $markUpHTML);
			
			return $markUpHTML;
		}
	}
?>