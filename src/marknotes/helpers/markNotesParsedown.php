<?php
/**
 * Overide somes functions of Parsedown
 *
 * Function inlineImage : see https://stackoverflow.com/a/41609464/1065340
 */
class markNotesParsedown extends ParsedownCheckbox
{
	/**
	 * This function will be called for each ![]() tag of the markdown source
	 *     example :  ![my-img_alt](one_image.png)
	 *
	 * In the definition of the markdown syntax, we can add a "title" like this :
	 *     ![my-img_alt](one_image.png "My awesome title")
	 *
	 * This function will examine if the title is, in fact, a width and a height like
	 *     ![my-img_alt](one_image.png "1200x522")
	 * then, and only then, i.e. a figure followed by a "x" followed by a figure, this
	 * function will capture that "fake" title and use it as a image's width/height; not as
	 * a title
	 *
	 * You can mention "*" if you don't force a width or a height; for instance :
	 *
	 *     ![my-img_alt](one_image.png "1200x*") => force width=1200px
	 *			and don't set the height
	 *			so will be automatically resized proportionnaly to the new width
	 *     ![my-img_alt](one_image.png "*x32") => force height=32px
	 *
	 */
	protected function inlineImage($Excerpt)
	{
		$Inline = parent::inlineImage($Excerpt);

		if (!isset($Inline['element']['attributes']['title'])) {
			// Nothing to do

			return parent::inlineImage($Excerpt);
		} else {
			$size = $Inline['element']['attributes']['title'];

			if (preg_match('/^(\d+|\*)x(\d+|\*)$/', $size)) {
				list($width, $height) = explode('x', $size);

				if ($width!=='*') {
					$Inline['element']['attributes']['width'] = $width;
				}
				if ($height!=='*') {
					$Inline['element']['attributes']['height'] = $height;
				}

				unset($Inline['element']['attributes']['title']);
			} else {
				$Inline = parent::inlineImage($Excerpt);
			}
		}

		return $Inline;
	}
}
