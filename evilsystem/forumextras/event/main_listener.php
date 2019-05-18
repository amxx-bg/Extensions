<?php
/**
 *
 * Forum Extras. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, Evil, http://github.com/stfkolev
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace evilsystem\forumextras\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Forum Extras Event listener.
 */
class main_listener implements EventSubscriberInterface
{
	public static function getSubscribedEvents()
	{
		return array(
			'core.user_setup'							=> 'load_language_on_setup',

			'core.viewtopic_cache_user_data'			=> 'viewtopic_cache_user_data',
			'core.viewtopic_cache_guest_data'			=> 'viewtopic_cache_guest_data',
			'core.viewtopic_modify_post_row'			=> 'viewtopic_modify_post_row',

			'core.memberlist_view_profile'				=> 'memberlist_view_profile',
			'core.search_get_posts_data'				=> 'search_get_posts_data',
			'core.search_modify_tpl_ary'				=> 'search_modify_tpl_ary',
		);
	}

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB root path */
	protected $phpbb_root_path;

	/** @var string phpEx */
	protected $php_ext;

	/** @var phpbb\language\language */
	protected $language;
	
	public function __construct(
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\language\language $language,
		\phpbb\user $user,
		$phpbb_root_path,
		$php_ext)
	{
		$this->request = $request;
		$this->template = $template;
		$this->language = $language;
		$this->user = $user;
		$this->root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Load common language files during user setup
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'evilsystem/forumextras',
			'lang_set' => 'common',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function viewtopic_cache_user_data($event)
	{
		$array = $event['user_cache_data'];
		$array['user_extras_rank'] = $event['row']['user_extras_rank'];
		$event['user_cache_data'] = $array;
	}

	/**
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function viewtopic_cache_guest_data($event)
	{
		$array = $event['user_cache_data'];
		$array['user_extras_rank'] = '';
		$event['user_cache_data'] = $array;
	}

	/**
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function viewtopic_modify_post_row($event)
	{
		$user_extras_rank = '';
		if ($event['user_poster_data']['user_type'] != USER_IGNORE)
		{
			$user_extras_rank = $event['user_poster_data']['user_extras_rank'];
		}

		$event['post_row'] = array_merge($event['post_row'],array(
			'USER_EXTRAS_RANK' => $user_extras_rank,
		));
	}

	/**
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function memberlist_view_profile($event)
	{
		$user_extras_rank = '';

		if ($event['member']['user_type'] != USER_IGNORE)
		{
			$user_extras_rank = $event['member']['user_extras_rank'];
		}

		$this->template->assign_vars(array(
			'USER_EXTRAS_RANK'	=> $user_extras_rank,
		));
	}

	/**
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function search_get_posts_data($event)
	{
		$array = $event['sql_array'];
		$array['SELECT'] .= ', u.user_extras_rank, u.user_type';
		$event['sql_array'] = $array;
	}

	/**
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function search_modify_tpl_ary($event)
	{
		if ($event['show_results'] == 'topics')
		{
			return;
		}

		$array = $event['tpl_ary'];
		$user_extras_rank = '';
		if ($event['row']['user_type'] != USER_IGNORE)
		{
			$user_extras_rank = $event['row']['user_extras_rank'];
		}
		$array = array_merge($array, array(
			'USER_EXTRAS_RANK'	=> $user_extras_rank,
		));

		$event['tpl_ary'] = $array;
	}
}
