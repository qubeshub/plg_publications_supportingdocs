<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2011 Purdue University. All rights reserved.
 *
 * This file is part of: The HUBzero(R) Platform for Scientific Collaboration
 *
 * The HUBzero(R) Platform for Scientific Collaboration (HUBzero) is free
 * software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * HUBzero is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @author    Alissa Nedossekina <alisa@purdue.edu>
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Publications Plugin class for supporting docs
 */
class plgPublicationsSupportingDocs extends \Hubzero\Plugin\Plugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 */
	protected $_autoloadLanguage = true;

	/**
	 * Return the alias and name for this category of content
	 *
	 * @param      object $publication 	Current publication
	 * @param      string $version 		Version name
	 * @param      boolean $extended 	Whether or not to show panel
	 * @return     array
	 */
	public function &onPublicationAreas( $publication, $version = 'default', $extended = true )
	{
		if ($publication->_category->_params->get('plg_supportingdocs'))
		{
			$areas = array(
				'supportingdocs' => Lang::txt('PLG_PUBLICATION_SUPPORTINGDOCS')
			);
		}
		else
		{
			$areas = array();
		}

		return $areas;
	}

	/**
	 * Return data on a resource view (this will be some form of HTML)
	 *
	 * @param      object  	$publication 	Current publication
	 * @param      string  	$option    		Name of the component
	 * @param      array   	$areas     		Active area(s)
	 * @param      string  	$rtrn      		Data to be returned
	 * @param      string 	$version 		Version name
	 * @param      boolean 	$extended 		Whether or not to show panel
	 * @return     array
	 */
	public function onPublication( $publication, $option, $areas, $rtrn='all',
		$version = 'default', $extended = true, $authorized = true )
	{
		$arr = array(
			'html'=>'',
			'metadata'=>''
		);

		// Check if our area is in the array of areas we want to return results for
		if (is_array( $areas ))
		{
			if (!array_intersect( $areas, $this->onPublicationAreas( $publication ) )
			&& !array_intersect( $areas, array_keys( $this->onPublicationAreas( $publication ) ) ))
			{
				// do nothing
				return $arr;
			}
		}
		if (!$publication->_category->_params->get('plg_supportingdocs'))
		{
			return $arr;
		}

		// Are we returning HTML?
		if ($rtrn == 'all' || $rtrn == 'html')
		{
			$database = JFactory::getDBO();

			$config = Component::params( $option );

			// Instantiate a view
			$view = new \Hubzero\Plugin\View(
				array(
					'folder'	=>'publications',
					'element'	=>'supportingdocs',
					'name'		=>'browse'
				)
			);

			// Get docs
			$pContent = new \Components\Publications\Tables\Attachment( $database );
			$view->docs = $pContent->getAttachments( $publication->version_id, $filters = array('role' => array(1,0,2), 'order' => 'a.role DESC, a.ordering ASC'));

			// Get projects html helper
			require_once( PATH_CORE . DS . 'components' . DS . 'com_projects' . DS . 'helpers' . DS . 'html.php' );

			// Build publication path
			$base_path 	= $config->get('webpath');
			$view->path = \Components\Publications\Helpers\Html::buildPubPath(
				$publication->id,
				$publication->version_id,
				$base_path,
				$publication->secret,
				$root = 1
			);

			// Pass the view some info
			$view->option 		= $option;
			$view->publication 	= $publication;
			$view->config 		= $config;
			$view->version 		= $version;
			$view->live_site 	= Request::base();
			$view->authorized	= $authorized;
			if ($this->getError())
			{
				$view->setError( $this->getError() );
			}

			// Return the output
			$arr['html'] = $view->loadTemplate();
		}

		return $arr;
	}
}
