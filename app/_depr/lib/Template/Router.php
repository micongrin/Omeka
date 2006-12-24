<?php
/* SVN FILE: $Id: router.php 2642 2006-04-29 21:51:30Z phpnut $ */

/**
 * Parses the request URL into controller, action, and parameters.
 *
 * Long description for file
 *
 * PHP versions 4 and 5
 *
 * CakePHP :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright (c) 2006, Cake Software Foundation, Inc.
 *                     1785 E. Sahara Avenue, Suite 490-204
 *                     Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright    Copyright (c) 2006, Cake Software Foundation, Inc.
 * @link         http://www.cakefoundation.org/projects/info/cakephp CakePHP Project
 * @package      cake
 * @subpackage   cake.cake.libs
 * @since        CakePHP v 0.2.9
 * @version      $Revision: 2642 $
 * @modifiedby   $LastChangedBy: phpnut $
 * @lastmodified $Date: 2006-04-29 16:51:30 -0500 (Sat, 29 Apr 2006) $
 * @license      http://www.opensource.org/licenses/mit-license.php The MIT License
 */

/**
 * Parses the request URL into controller, action, and parameters.
 *
 * @package    cake
 * @subpackage cake.cake.libs
 * @since      CakePHP v 0.2.9
 *
 */
class Kea_Template_Router implements Kea_Router_Interface {

/**
 * Array of routes
 *
 * @var array
 */
    public static $routes = array();

/**
 * CAKE_ADMIN route
 *
 * @var array
 */
    var $__admin = null;

    /*
	function __construct ()
    {
        if(defined('ADMIN_THEME'))
        {
            $admin = ADMIN_THEME;
            if(!empty($admin))
            {
                $this->__admin = array (
                    '/'.$admin.'/:template/* (default)',
                    '/^(?:\/(?:('.$admin.')(?:\/([^\/]+))?(?:\/(.*))?))[\/]*$/',
                    array( 'admin', 'template' ),
                    array()
                );
            }
        }
    }
	*/

	function __construct ()
    {
		$this->__admin = array (
			'/admin/:template/* (default)',
			'/^(?:\/(?:('.ADMIN_URI.')(?:\/([^\/]+))?(?:\/(.*))?))[\/]*$/',
			array( 'admin', 'template' ),
			array()
		);
    }

/**
 * TODO: Better description. Returns this object's routes array. Returns false if there are no routes available.
 *
 * @param string $route    An empty string, or a route string "/"
 * @param array $default    NULL or an array describing the default route
 * @see routes
 * @return array            Array of routes
 */
    public static function connect ($route, $default=null)
    {
      $parsed = $names = array ();

      if (defined('ADMIN_URI') && $default == null)
      {
          if ($route == ADMIN_URI)
          {
              self::$routes[] = $this->__admin;
              $this->__admin = null;
          }
      }

      $r = null;
      if (($route == '') || ($route == '/'))
      {
         $regexp = '/^[\/]*$/';
         self::$routes[] = array($route, $regexp, array(), $default);
      }
      else
      {
         $elements = array();
         foreach (explode('/', $route) as $element)
         {
            if (trim($element)) $elements[] = $element;
         }

         if (!count($elements))
         {
            return false;
         }

         foreach ($elements as $element)
         {
            if (preg_match('/^:(.+)$/', $element, $r))
            {
                // $parsed[] = '(?:\/([^\/]+))?';
				// Added [\/]? to make the leading slash optional
				$parsed[] = '(?:[\/]?([^\/]+))?';
                $names[] = $r[1];
            }
            elseif (preg_match('/^\*$/', $element, $r))
            {
                $parsed[] = '(?:\/(.*))?';
            }
            else
            {
				// $parsed[] = '/'.$element;
				// Added .'/' so that /foo/objects didn't match /foo/objects/
                $parsed[] = '/'.$element.'/';
            }
         }
         $regexp = '#^'.join('', $parsed).'[\/]*$#';
         self::$routes[] = array($route, $regexp, $names, $default);
      }
      return self::$routes;
    }

/**
 * Parses given URL and returns an array of controllers, action and parameters
 * taken from that URL.
 *
 * @param string $url URL to be parsed
 * @return array
 */
    function parse( $url )
    {
// An URL should start with a '/', mod_rewrite doesn't respect that, but no-mod_rewrite version does.
// Here's the fix.
      if ($url && ('/' != $url[0]))
      {
         if (!defined('SERVER_IIS'))
         {
             $url = '/'.$url;
         }
      }

      $out = array();
      $r = null;

      $default_route = array
      (
         '/:template/* (default)',
         '/^(?:\/([^\/]+))?(?:\/(.*))?[\/]*$/',
         array('template'),
         array());

      if (defined('ADMIN_URI') && $this->__admin != null)
      {
          self::$routes[] = $this->__admin;
          $this->__admin = null;
      }

      self::$routes[] = $default_route;
      if (strpos($url, '?') !== false)
      {
          $url = substr($url, 0, strpos($url, '?'));
      }
      foreach (self::$routes as $route)
      {
         list($route, $regexp, $names, $defaults) = $route;

         if (preg_match($regexp, $url, $r))
         {
// $this->log($url.' matched '.$regexp, 'note');
// remove the first element, which is the url
            array_shift($r);

// hack, pre-fill the default route names
            foreach ($names as $name)
            {
                $out[$name] = null;
            }

            $ii = 0;

            if (is_array($defaults))
            {
                foreach ($defaults as $name=>$value)
                {
                  if (preg_match('#[a-zA-Z_\-]#i', $name))
                  {
                     $out[$name] = $value;
                  }
                  else
                  {
                     $out['pass'][] = $value;
                  }
                }
            }

            foreach ($r as $found)
            {
// if $found is a named url element (i.e. ':action')
                if (isset($names[$ii]))
                {
                  $out[$names[$ii]] = $found;
                }
// unnamed elements go in as 'pass'
                else
                {
                  $pass = explode('/', $found);
					$passed = array();
					foreach( $pass as $k=>$v ) {
						if( $v == '0' ) {
							$passed[$k] = $v;
						}
						elseif( $v ) {
							$passed[$k] = $v;
						}
					}
                  $out['pass'] = $passed;
                }
                $ii++;
            }
            break;
         }
      }

      return $out;
    }

	function getRoute( $url )
	{
		return $this->parse( $url );
	}

}


?>