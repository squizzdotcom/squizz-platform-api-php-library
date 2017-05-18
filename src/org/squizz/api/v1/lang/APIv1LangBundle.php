<?php
	/**
	* Copyright (C) 2017 Squizz PTY LTD
	* This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
	* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
	* You should have received a copy of the GNU General Public License along with this program.  If not, see http://www.gnu.org/licenses/.
	*/
	namespace org\squizz\api\v1\lang;
	use org\squizz\api\v1\APIv1Constants;

	/**
	* static class the handles language localization used to display language based on a given locale
	*/
	class APIv1LangBundle
	{
		/**
		* creates a new language bundle object based on the best matching language specified
		* @param languageLocale locale of the language to obtain the bundle for
		* @returns languageBundle class object containing the language bundle based on the language given
		*
		*/
		public static function getBundle($languageLocale)
		{
			$bundle = null;
		
			switch($languageLocale){
				case APIv1Constants::SUPPORTED_LOCALES_EN_AU:
					$bundle = new APIv1LangBundleENAU();
					break;
				default:
					$bundle = new APIv1LangBundleENAU();
					break;
			}
			
			return $bundle;
		}
	}
?>