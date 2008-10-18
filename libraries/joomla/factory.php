<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * Joomla Framework Factory class
 *
 * @static
 * @package		Joomla.Framework
 * @since	1.5
 */
abstract class JFactory
{
	public static $application = null;
	public static $config = null;
	public static $session = null;
	public static $language = null;
	public static $document = null;
	public static $acl = null;
	public static $database = null;
	public static $mailer = null;

	/**
	 * Get a application object
	 *
	 * Returns a reference to the global {@link JApplication} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param	mixed	$id 		A client identifier or name.
	 * @param	array	$config 	An optional associative array of configuration settings.
	 * @return object JApplication
	 */
	public static function &getApplication($id = null, $config = array(), $prefix='J')
	{
		if (!is_object(JFactory::$application))
		{
			jimport('joomla.application.application');

			if (!$id) {
				JError::raiseError(500, 'Application Instantiation Error');
			}

			JFactory::$application = JApplication::getInstance($id, $config, $prefix);
		}

		return JFactory::$application;
	}

	/**
	 * Get a configuration object
	 *
	 * Returns a reference to the global {@link JRegistry} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param string	The path to the configuration file
	 * @param string	The type of the configuration file
	 * @return object JRegistry
	 */
	public static function &getConfig($file = null, $type = 'PHP')
	{
		if (!is_object(JFactory::$config))
		{
			if ($file === null) {
				$file = dirname(__FILE__).DS.'config.php';
			}

			JFactory::$config = JFactory::_createConfig($file, $type);
		}

		return JFactory::$config;
	}

	/**
	 * Get a session object
	 *
	 * Returns a reference to the global {@link JSession} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param array An array containing session options
	 * @return object JSession
	 */
	public static function &getSession($options = array())
	{
		if (!is_object(JFactory::$session)) {
			JFactory::$session = JFactory::_createSession($options);
		}

		return JFactory::$session;
	}

	/**
	 * Get a language object
	 *
	 * Returns a reference to the global {@link JLanguage} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @return object JLanguage
	 */
	public static function &getLanguage()
	{
		if (!is_object(JFactory::$language))
		{
			//get the debug configuration setting
			$conf = &JFactory::getConfig();
			$debug = $conf->getValue('config.debug_lang');

			JFactory::$language = JFactory::_createLanguage();
			JFactory::$language->setDebug($debug);
		}

		return JFactory::$language;
	}

	/**
	 * Get a document object
	 *
	 * Returns a reference to the global {@link JDocument} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @return object JLanguage
	 */
	public static function &getDocument()
	{
		if (!is_object(JFactory::$document)) {
			JFactory::$document = JFactory::_createDocument();
		}

		return JFactory::$document;
	}

	/**
	 * Get an user object
	 *
	 * Returns a reference to the global {@link JUser} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param 	int 	$id 	The user to load - Can be an integer or string - If string, it is converted to ID automatically.
	 *
	 * @return object JUser
	 */
	public static function &getUser($id = null)
	{
		jimport('joomla.user.user');

		if(is_null($id))
		{
			$session  = &JFactory::getSession();
			$instance = &$session->get('user');
			if (!$instance INSTANCEOF JUser) {
				$instance = &JUser::getInstance();
			}
		}
		else
		{
			$instance = &JUser::getInstance($id);
		}

		return $instance;
	}

	/**
	 * Get a cache object
	 *
	 * Returns a reference to the global {@link JCache} object
	 *
	 * @param string The cache group name
	 * @param string The handler to use
	 * @param string The storage method
	 * @return object JCache
	 */
	public static function &getCache($group = '', $handler = 'callback', $storage = null)
	{
		$handler = ($handler == 'function') ? 'callback' : $handler;

		$conf = &JFactory::getConfig();

		if(!isset($storage)) {
			$storage = $conf->getValue('config.cache_handler', 'file');
		}

		$options = array(
			'defaultgroup' => $group,
			'cachebase' => $conf->getValue('config.cache_path'),
			'lifetime' => $conf->getValue('config.cachetime') * 60,	// minutes to seconds
			'language' => $conf->getValue('config.language'),
			'storage' => $storage
		);

		jimport('joomla.cache.cache');

		$cache = &JCache::getInstance($handler, $options);
		$cache->setCaching($conf->getValue('config.caching'));
		return $cache;
	}

	/**
	 * Get an authorization object
	 *
	 * Returns a reference to the global {@link JACL} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @return object JACL
	 */
	public static function &getACL()
	{
		if (!is_object(JFactory::$acl)) {
			jimport('joomla.user.authorization');
		$db = & JFactory::getDBO();

			$options = array(
				'db'				=> &$db,
				'db_table_prefix'	=> $db->getPrefix() . 'core_acl_',
				'debug'				=> 0
			);

			JFactory::$acl = new JAuthorization($options);
		}

		return JFactory::$acl;
	}

	/**
	 * Get a template object
	 *
	 * Returns a reference to the global {@link JTemplate} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @return		object JTemplate
	 * @deprecated
	 */
	public static function &getTemplate()
	{
		if (class_exists('JLegacy')) {
			return JLegacy::JFactoryGetTemplate();
		}
		else {
			throw new JException('Exception JFactory::getTemplate deprecated. Turn on legacy mode', 500, E_ERROR, 'JFactory::getTemplate', true);
		}
	}

	/**
	 * Get a database object
	 *
	 * Returns a reference to the global {@link JDatabase} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @return object JDatabase
	 */
	public static function &getDBO()
	{
		if (!is_object(JFactory::$database))
		{
			//get the debug configuration setting
			$conf = &JFactory::getConfig();
			$debug = $conf->getValue('config.debug');

			JFactory::$database = JFactory::_createDBO();
			JFactory::$database->debug($debug);
		}

		return JFactory::$database;
	}

	/**
	 * Get a mailer object
	 *
	 * Returns a reference to the global {@link JMail} object, only creating it
	 * if it doesn't already exist
	 *
	 * @return object JMail
	 */
	public static function &getMailer()
	{
		if (! is_object(JFactory::$mailer)) {
			JFactory::$mailer = JFactory::_createMailer();
		}
		$copy	= clone(JFactory::$mailer);
		return $copy;
	}

	/**
	 * Get an XML document
	 *
	 * @param string The type of xml parser needed 'DOM', 'RSS' or 'Simple'
	 * @param array:
	 * 		boolean ['lite'] When using 'DOM' if true or not defined then domit_lite is used
	 * 		string  ['rssUrl'] the rss url to parse when using "RSS"
	 * 		string	['cache_time'] with 'RSS' - feed cache time. If not defined defaults to 3600 sec
	 * @return object Parsed XML document object
	 */
	public static function &getXMLParser($type = 'DOM', $options = array())
	 {
		$doc = null;

		switch (strtolower($type))
		{
			case 'rss' :
			case 'atom' :
			{
				if (!is_null($options['rssUrl']))
				{
					jimport ('simplepie.simplepie');
					if(!is_writable(JPATH_BASE.DS.'cache')) {
						$options['cache_time'] = 0;
					}
					$simplepie = new SimplePie(
						$options['rssUrl'],
						JPATH_BASE.DS.'cache',
						isset($options['cache_time']) ? $options['cache_time'] : 0
					);
					$simplepie->handle_content_type();
					if ($simplepie->data) {
						$doc = $simplepie;
					} else {
						JError::raiseWarning('SOME_ERROR_CODE', JText::_('ERROR LOADING FEED DATA'));
					}
				}
			}	break;

			case 'simple' :
			{
				jimport('joomla.utilities.simplexml');
				$doc = new JSimpleXML();
			}	break;

			case 'dom'  :
			default :
			{
				if (!isset($options['lite']) || $options['lite'])
				{
					jimport('domit.xml_domit_lite_include');
					$doc = new DOMIT_Lite_Document();
				}
				else
				{
					jimport('domit.xml_domit_include');
					$doc = new DOMIT_Document();
				}
			}
		}

		return $doc;
	}

	/**
	* Get an editor object
	*
	* @param string $editor The editor to load, depends on the editor plugins that are installed
	* @return object JEditor
	*/
	public static function &getEditor($editor = null)
	{
		jimport('joomla.html.editor');

		//get the editor configuration setting
		if(is_null($editor))
		{
			$conf = &JFactory::getConfig();
			$editor = $conf->getValue('config.editor');
		}

		$instance = &JEditor::getInstance($editor);

		return $instance;
	}

	/**
	 * Return a reference to the {@link JURI} object
	 *
	 * @return object JURI
	 * @since 1.5
	 */
	public static function &getURI($uri = 'SERVER')
	{
		jimport('joomla.environment.uri');

		$instance = &JURI::getInstance($uri);
		return $instance;
	}

	/**
	 * Return a reference to the {@link JDate} object
	 *
	 * @param mixed $time The initial time for the JDate object
	 * @param int $tzOffset The timezone offset.
	 * @return object JDate
	 * @since 1.5
	 */
	public static function &getDate($time = 'now', $tzOffset = 0)
	{
		jimport('joomla.utilities.date');
		static $instances;
		static $classname;
		static $mainLocale;

		if(!isset($instances)) {
			$instances = array();
		}

		$language = &JFactory::getLanguage();
		$locale = $language->getTag();

		if(!isset($classname) || $locale != $mainLocale) {
			//Store the locale for future reference
			$mainLocale = $locale;
			$localePath = JPATH_ROOT . DS . 'language' . DS . $mainLocale . DS . $mainLocale . '.date.php';
			if($mainLocale !== false && file_exists($localePath)) {
				$classname = 'JDate'.str_replace('-', '_', $mainLocale);
				JLoader::register($classname,  $localePath);
				if(!class_exists($classname)) {
					//Something went wrong.  The file exists, but the class does not, default to JDate
					$classname = 'JDate';
				}
			} else {
				//No file, so default to JDate
				$classname = 'JDate';
			}
		}
		$key = $time . '-' . $tzOffset;

		if(!isset($instances[$classname][$key])) {
			$tmp = new $classname($time, $tzOffset);
			//We need to serialize to break the reference
			$instances[$classname][$key] = serialize($tmp);
			unset($tmp);
		}

		$date = unserialize($instances[$classname][$key]);
		return $date;
	}



	/**
	 * Create a configuration object
	 *
	 * @param string	The path to the configuration file
	 * @param string	The type of the configuration file
	 * @return object JRegistry
	 * @since 1.5
	 */
	private static function &_createConfig($file, $type = 'PHP')
	{
		jimport('joomla.registry.registry');

		require_once $file;

		// Create the registry with a default namespace of config
		$registry = new JRegistry('config');

		// Create the JConfig object
		$config = new JFrameworkConfig();

		// Load the configuration values into the registry
		$registry->loadObject($config);

		return $registry;
	}

	/**
	 * Create a session object
	 *
	 * @param array $options An array containing session options
	 * @return object JSession
	 * @since 1.5
	 */
	private static function &_createSession($options = array())
	{
		jimport('joomla.session.session');

		//get the editor configuration setting
		$conf = &JFactory::getConfig();
		$handler =  $conf->getValue('config.session_handler', 'none');

		// config time is in minutes
		$options['expire'] = ($conf->getValue('config.lifetime')) ? $conf->getValue('config.lifetime') * 60 : 900;

		$session = JSession::getInstance($handler, $options);
		if ($session->getState() == 'expired') {
			$session->restart();
		}

		return $session;
	}

	/**
	 * Create an database object
	 *
	 * @return object JDatabase
	 * @since 1.5
	 */
	private static function &_createDBO()
	{
		jimport('joomla.database.database');
		jimport('joomla.database.table');

		$conf = &JFactory::getConfig();

		$host 		= $conf->getValue('config.host');
		$user 		= $conf->getValue('config.user');
		$password 	= $conf->getValue('config.password');
		$database	= $conf->getValue('config.db');
		$prefix 	= $conf->getValue('config.dbprefix');
		$driver 	= $conf->getValue('config.dbtype');
		$debug 		= $conf->getValue('config.debug');

		$options	= array ('driver' => $driver, 'host' => $host, 'user' => $user, 'password' => $password, 'database' => $database, 'prefix' => $prefix);

		$db = &JDatabase::getInstance($options);

		if (JError::isError($db)) {
			jexit('Database Error: ' . $db->toString());
		}

		if ($db->getErrorNum() > 0) {
			JError::raiseError(500 , 'JDatabase::getInstance: Could not connect to database <br/>' . 'joomla.library:'.$db->getErrorNum().' - '.$db->getErrorMsg());
		}

		$db->debug($debug);
		return $db;
	}

	/**
	 * Create a mailer object
	 *
	 * @return object JMail
	 * @since 1.5
	 */
	private static function &_createMailer()
	{
		jimport('joomla.mail.mail');

		$conf	= &JFactory::getConfig();

		$sendmail 	= $conf->getValue('config.sendmail');
		$smtpauth 	= $conf->getValue('config.smtpauth');
		$smtpuser 	= $conf->getValue('config.smtpuser');
		$smtppass  	= $conf->getValue('config.smtppass');
		$smtphost 	= $conf->getValue('config.smtphost');
		$mailfrom 	= $conf->getValue('config.mailfrom');
		$fromname 	= $conf->getValue('config.fromname');
		$mailer 	= $conf->getValue('config.mailer');

		// Create a JMail object
		$mail 		= &JMail::getInstance();

		// Set default sender
		$mail->setSender(array ($mailfrom, $fromname));

		// Default mailer is to use PHP's mail function
		switch ($mailer)
		{
			case 'smtp' :
				$mail->useSMTP($smtpauth, $smtphost, $smtpuser, $smtppass);
				break;
			case 'sendmail' :
				$mail->IsSendmail();
				break;
			default :
				$mail->IsMail();
				break;
		}

		return $mail;
	}

	/**
	 * Create a language object
	 *
	 * @return object JLanguage
	 * @since 1.5
	 */
	private static function &_createLanguage()
	{
		jimport('joomla.language.language');

		$conf	= &JFactory::getConfig();
		$locale	= $conf->getValue('config.language');
		$lang	= &JLanguage::getInstance($locale);
		$lang->setDebug($conf->getValue('config.debug_lang'));

		return $lang;
	}

	/**
	 * Create a document object
	 *
	 * @return object JDocument
	 * @since 1.5
	 */
	private static function &_createDocument()
	{
		jimport('joomla.document.document');

		$lang	= &JFactory::getLanguage();

		//Keep backwards compatibility with Joomla! 1.0
		$raw	= JRequest::getBool('no_html');
		$type	= JRequest::getWord('format', $raw ? 'raw' : 'html');

		$attributes = array (
			'charset'	=> 'utf-8',
			'lineend'	=> 'unix',
			'tab'		=> '  ',
			'language'	=> $lang->getTag(),
			'direction'	=> $lang->isRTL() ? 'rtl' : 'ltr'
		);

		$doc = &JDocument::getInstance($type, $attributes);
		return $doc;
	}
}
