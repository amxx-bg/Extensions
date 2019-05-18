<?php
/**
 *
 * Forum Extras. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2019, Evil, http://github.com/stfkolev
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace evilsystem\forumextras\controller;

/**
 * Forum Extras UCP controller.
 */
class ucp_controller
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string Custom form action */
	protected $u_action;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\db\driver\driver_interface	$db			Database object
 	 * @param \phpbb\language\language			$language	Language object
 	 * @param \phpbb\request\request			$request	Request object
	 * @param \phpbb\template\template			$template	Template object
	 * @param \phpbb\user						$user		User object
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\language\language $language, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user)
	{
		$this->db		= $db;
		$this->language	= $language;
		$this->request	= $request;
		$this->template	= $template;
		$this->user		= $user;
	}

	/**
	 * Display the options a user can configure for this extension.
	 *
	 * @return void
	 */
	public function display_changerank()
	{
		// Create a form key for preventing CSRF attacks
		add_form_key('evilsystem_forumextras_ucp');

		// Create an array to collect errors that will be output to the user
		$errors = array();

		// Request the options the user can configure
		$data = array(
			'user_extras_rank' => $this->request->variable('user_extras_rank', $this->user->data['user_extras_rank']),
			'user_extras_rank_last' => strtotime(date('Y-m-d H:i:s')),
		);

		// Is the form being submitted to us?
		if ($this->request->is_set_post('submit'))
		{
			// Test if the submitted form is valid
			if (!check_form_key('evilsystem_forumextras_ucp'))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}
			
			/*! Create dates to differentiate */
			$dt1 = date_create(date('Y-m-d', $this->user->data['user_extras_rank_last']));
			$dt2 = date_create(date("Y-m-d", time()));

			/*! If date difference is under 30 days */
			if(date_diff($dt1, $dt2)->format('%a') < 30) {
				/*! Calculate date interval to show as error message */
				$daysToWait = date_create(date('Y-m-d', $this->user->data['user_extras_rank_last']));
				$daysToWait->add(new \DateInterval('P30D'))->format('Y-m-d H:i:s');
				
				/*! Add to error */
				$errors[] = $this->language->lang('FORM_WAIT_X_DAYS', date_diff($daysToWait, $dt1)->format('%a'));
			}

			// If no errors, process the form data
			if (empty($errors))
			{
				// Set the options the user configured
				$sql = 'UPDATE ' . USERS_TABLE . '
					SET ' . $this->db->sql_build_array('UPDATE', $data) . '
					WHERE user_id = ' . (int) $this->user->data['user_id'];

				$this->db->sql_query($sql);

				// Option settings have been updated
				// Confirm this to the user and provide (automated) link back to previous page
				meta_refresh(3, $this->u_action);
				$message = $this->language->lang('UCP_FORUMEXTRAS_CHANGERANK_SAVED') . '<br /><br />' . $this->language->lang('RETURN_UCP', '<a href="' . $this->u_action . '">', '</a>');
				trigger_error($message);
			}
		}

		$s_errors = !empty($errors);

		// Set output variables for display in the template
		$this->template->assign_vars(array(
			'S_ERROR'		=> $s_errors,
			'ERROR_MSG'		=> $s_errors ? implode('<br />', $errors) : '',

			'U_UCP_ACTION'	=> $this->u_action,

			'S_USER_CHANGERANK'	=> $data['user_extras_rank'],
		));
	}

	/**
	 * Set custom form action.
	 *
	 * @param string	$u_action	Custom form action
	 * @return void
	 */
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
