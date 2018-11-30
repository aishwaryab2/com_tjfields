<?php
/**
 * @version    SVN:<SVN_ID>
 * @package    TJFields
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */

defined('JPATH_PLATFORM') or die;

JLoader::register('JFormFieldUrl', JPATH_BASE . '/libraries/joomla/form/fields/url.php');

/**
 * Form Field video class
 * Supports a multi line area for entry of plain text with count char
 *
 * @since  DEPLOY_VERSION`
 */
class JFormFieldVideo extends JFormFieldUrl
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  DEPLOY_VERSION
	 */
	protected $type = 'video';

	/**
	 * The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 *
	 * @var    mixed
	 * @since  DEPLOY_VERSION
	 */
	protected $element;

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  DEPLOY_VERSION
	 */
	protected $layout = 'joomla.form.field.url';

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   DEPLOY_VERSION
	 */
	public function __get($name)
	{
		require_once JPATH_SITE . '/components/com_tjfields/helpers/tjfields.php';

		switch ($name)
		{
			case 'element';

				return $this->element;
				break;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to the the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   DEPLOY_VERSION
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   DEPLOY_VERSION
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		return $return;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   DEPLOY_VERSION
	 */
	protected function getInput()
	{
		require_once JPATH_SITE . '/components/com_tjfields/helpers/tjfields.php';

		$tjFieldHelper = new TjfieldsHelper;

		$layoutData = $this->getLayoutData();

		// Trim the trailing line in the layout file
		$html = rtrim($this->getRenderer($this->layout)->render($layoutData), PHP_EOL);

		if (empty($layoutData['value']))
		{
			return $html;
		}

		if (isset($layoutData['field']->element->attributes()->display_video))
		{
				$html .= '<div class="control-group">';

				$html .= '<div class="container">
							<!-- Trigger the modal with a button -->
								<button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal_' . $layoutData['field']->id . '">
								click to show video</button>

								<div class="modal fade" id="myModal_' . $layoutData['field']->id . '" role="dialog">
									<div class="modal-dialog">
										<!-- Modal content-->
											<div class="modal-content">
												<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal">&times;</button>
											<h4 class="modal-title">Video</h4>
								</div>

								<div class="modal-body">';
									$html .= $this->rendervideo($layoutData, $layoutData['value']);
									$html .= '
								</div>
									<div class="modal-footer">
									<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
								</div>
							</div>
						</div>
						</div>
					</div>';
		}
		else
		{
			$html .= $this->rendervideo($layoutData, $layoutData['value']);
		}

		if (!empty($layoutData['value']) && $layoutData['field']->element->attributes()->showvideolink)
		{
			$html .= '<br><a target="_blank" href=' . $layoutData['value'] . '> ' . $layoutData['value'] . '</a>';
		}

		$html .= $this->addMediaplayer($layoutData);
		$html .= '</div>';

		return $html;
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since DEPLOY_VERSION
	 */
	protected function getLayoutData()
	{
		$data = parent::getLayoutData();

		// Initialize some field attributes.
		$maxLength    = !empty($this->maxLength) ? ' maxlength="' . $this->maxLength . '"' : '';

		// Note that the input type "url" is suitable only for external URLs, so if internal URLs are allowed
		// we have to use the input type "text" instead.
		$inputType    = $this->element['relative'] ? 'type="text"' : 'type="url"';

		$extraData = array(
			'maxLength' => $maxLength,
			'inputType' => $inputType,
		);

		return array_merge($data, $extraData);
	}

	/**
	 * Method to render video.
	 *
	 * @param   array   $layoutData  layoutData.
	 * @param   string  $videoUrl    videoUrl
	 *
	 * @return  string
	 *
	 * @since   DEPLOY_VERSION
	 */
	protected function rendervideo($layoutData, $videoUrl)
	{
		if (isset($layoutData['field']->element->attributes()->autoplay))
		{
			$autoPlay = 'autoplay';
		}

		$html .= '
				<video ' . $autoPlay . ' id="player_' . $layoutData['field']->id . '"
					height="' . $layoutData['field']->element->attributes()->height . '"
					width="' . $layoutData['field']->element->attributes()->width . '
					preload="auto"
					poster ="' . $layoutData['field']->element->attributes()->poster . '"
					controls playsinline webkit-playsinline
					src="' . $videoUrl . '">
				</video>';

		return $html;
	}

	/**
	 * Method to add media player to render video.
	 *
	 * @param   array  $layoutData  layoutData.
	 *
	 * @return  string
	 *
	 * @since   DEPLOY_VERSION
	 */
	protected function addMediaplayer($layoutData)
	{
		$doc = JFactory::getDocument();

		$doc->addScript(JUri::root() . 'media/vendors/MediaElementPlayer/mediaelement-and-player.min.js');
		$doc->addStyleSheet(JUri::root() . 'media/vendors/MediaElementPlayer/mediaelementplayer.min.css');

		if (strpos($layoutData['value'], "vimeo") !== false)
		{
			if (isset($layoutData['field']->element->attributes()->vimeo))
			{
				$doc->addScript(JUri::root() . 'media/vendors/MediaElementPlayer/renderers/vimeo.min.js');
			}
			else
			{
				$html .= "<p>Enable vimeo Renderer</p>";
			}
		}
		elseif (strpos($layoutData['value'], "facebook") !== false)
		{
			if (isset($layoutData['field']->element->attributes()->Facebook))
			{
				$doc->addScript(JUri::root() . 'media/vendors/MediaElementPlayer/renderers/facebook.min.js');
			}
			else
			{
				$html .= "<p>Enable facebook Renderer</p>";
			}
		}
		elseif (strpos($layoutData['value'], "twitch") !== false)
		{
			if (isset($layoutData['field']->element->attributes()->twitch))
			{
				$doc->addScript(JUri::root() . 'media/vendors/MediaElementPlayer/renderers/twitch.min.js');
			}
			else
			{
				$html .= "<p>Enable twitch Renderer</p>";
			}
		}
		elseif (strpos($layoutData['value'], "dailymotion") !== false)
		{
			if (isset($layoutData['field']->element->attributes()->dailymotion))
			{
				$doc->addScript(JUri::root() . 'media/vendors/MediaElementPlayer/renderers/dailymotion.min.js');
			}
			else
			{
				$html .= "<p>Enable DailyMotion Renderer</p>";
			}
		}
		elseif (strpos($layoutData['value'], "soundcloud") !== false)
		{
			if (isset($layoutData['field']->element->attributes()->soundcloud))
			{
				$doc->addScript(JUri::root() . 'media/vendors/MediaElementPlayer/renderers/soundcloud.min.js');
			}
			else
			{
				$html .= "<p>Enable SoundCloud Renderer</p>";
			}
		}

		$doc->addScriptDeclaration('
			jQuery(document).ready(function() {
				jQuery("#player_' . $layoutData['field']->id . '").mediaelementplayer({
					pluginPath: "/path/to/shims/",
					success: function(mediaElement, originalNode, instance) {
				}
				});
			});
		');

		return $html;
	}
}
