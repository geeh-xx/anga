<?php
class Infortis_UltraSlideshow_Block_Slideshow extends Mage_Core_Block_Template
{	
	/**
	 * Get array of static block identifiers. Blocks will be displayed as slides.
	 *
	 * @return array
	 */
	public function getStaticBlockIds()
	{
		$blockIdsString = Mage::helper('ultraslideshow')->getCfg('general/blocks');
		$blockIds = explode(",", str_replace(" ", "", $blockIdsString));
		return $blockIds;
	}
	
	/**
	 * Get content of the static block which contains additional banners for the slideshow.
	 *
	 * @return string
	 */
	public function getBanners()
	{
		$bid = Mage::helper('ultraslideshow')->getCfg('banners/banners');
		return $this->getLayout()->createBlock('cms/block')->setBlockId($bid)->toHtml();
	}
	
	//deprecated
	//TODO:remove this feature
	public function getImgUrls()
	{
		$h = Mage::helper('ultraslideshow');
		
		//Get filenames separated with commas
		$fileNames = explode(",", $h->getCfg('general/files'));
		if (!$fileNames)
			return array();
		
		//Get slides path (relative to 'media'), trim slashes. If path specified: append to 'media' and append slash.
		$slidesUrl = $mediaUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
		$dir = trim($h->getCfg('general/directory'), "/");
		if ($dir != '')
			$slidesUrl .= $dir . "/";
		
		//Create an array of image URLs
		$fileUrls = array();
		foreach ($fileNames as $filename)
		{
			if ( ($trimmedFilename = trim($filename)) != '' )
				$fileUrls[] = $slidesUrl . $trimmedFilename;
		}

		return $fileUrls;
	}
}