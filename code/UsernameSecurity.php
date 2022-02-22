<?php

/**
 * UsernameSecurity is an extension of Security, to allow requesting and sending out lost usernames or passwords.
 * 
 */
class UsernameSecurity extends Security {

    private static $allowed_actions = array(
        'forgot',
        'usernamesent',
        'passwordsent',
        'LostUsernameForm',
        'LostPasswordForm',
    );
	
	/**
	 * Show the "lost password / lost username" page
	 *
	 * @return string Returns the "lost password" page as HTML code.
	 */
	public function forgot() {
        $controller = $this->getResponseController(_t('Security.LOSTPASSWORDHEADER', 'Lost Password'));

        // if the controller calls Director::redirect(), this will break early
        if(($response = $controller->getResponse()) && $response->isFinished()) return $response;

        $customisedController = $controller->customise(array(
            'Content' =>
                '<p>' .
                _t(
                    'Security.USERNAMENOTERESETPASSWORD',
                    'Enter your username to be sent a password reset link, or enter your email in the box below to be sent your username.'
                ) .
                '</p>',
            'Form' => $this->LostPasswordForm()->forTemplate().'<br/>'.$this->LostUsernameForm()->forTemplate()
        ));

        //Controller::$currentController = $controller;
        return $customisedController->renderWith($this->getTemplatesFor('lostpassword'));
	}
	
    public static function lost_username_or_password_url() {
        return Controller::join_links(Director::baseURL(), 'UsernameSecurity', 'forgot');
    }

    /**
	 * Factory method for the lost password form
	 *
	 * @return Form Returns the lost password form
	 */
	public function LostPasswordForm() {
		
		$form = new UsernameLoginForm(
			$this,
			'LostPasswordForm',
			new FieldList(
				new TextField('Username', _t('Member.USERNAME', 'Username'))
			),
			new FieldList(
				new FormAction(
					'forgotPassword',
					_t('Security.BUTTONSEND', 'Send me the password reset link')
				)
			),
			false
		);
        $form->setFormAction('/UsernameSecurity/LostPasswordForm');
        $form->setValidator(RequiredFields::create('Username'));
        return $form;
	}
	
	public function LostUsernameForm(){
		$form = new UsernameLoginForm(
			$this,
			'LostUsernameForm',
			new FieldList(
				new TextField('Email', _t('Member.EMAIL', 'Email'))
			),
			new FieldList(
				new FormAction(
					'forgotUsername',
					_t('Security.USERNAMESEND', 'Send me my usernames')
				)
			),
			false
		);
        $form->setFormAction('/UsernameSecurity/LostUsernameForm');
        $form->setValidator(RequiredFields::create('Email'));
        return $form;
	}
	
	/**
	 * Show the "password sent" page, after a user has requested
	 * to reset their password.
	 *
	 * @param HTTPRequest $request The HTTPRequest for this action. 
	 * @return string Returns the "password sent" page as HTML code.
	 */
	public function passwordsent($request) {
        $controller = $this->getResponseController(_t('Security.PASSWORDSENT', 'Password Sent'));

        // if the controller calls Director::redirect(), this will break early
        if(($response = $controller->getResponse()) && $response->isFinished()) return $response;

        $username = (Session::get('ForgotUsername')) ? Convert::raw2xml(Session::get('ForgotUsername')) : null;
        Session::clear('ForgotUsername');

        $customisedController = $controller->customise(array(
            'Title' => _t('Security.PASSWORDSENTHEADER', "Password reset link sent for '{username}'",
                array('username' => $username)),
            'Content' =>
                "<p>"
                . _t('Security.PASSWORDSENTTEXT',
                    "Thank you! A reset link has been sent for '{username}', provided an account exists for this username"
                    . " address.",
                    array('username' => $username))
                . "</p>",
            "Username" => $username
        ));

        //Controller::$currentController = $controller;
        return $customisedController->renderWith($this->getTemplatesFor('passwordsent'));
	}
	
	public function usernamesent($request) {
        $controller = $this->getResponseController(_t('Security.USERNAMESENT', 'Username sent'));

        // if the controller calls Director::redirect(), this will break early
        if(($response = $controller->getResponse()) && $response->isFinished()) return $response;

        $email = (Session::get('ForgotEmail')) ? Convert::raw2xml(Session::get('ForgotEmail')) : null;
        Session::clear('ForgotEmail');

        $customisedController = $controller->customise(array(
            'Title' => _t('Security.USERNAMESENTHEADER', "Username sent to '{email}'",
                array('email' => $email)),
            'Content' =>
                "<p>"
                . _t('Security.USERNAMESENTTEXT',
                    "Thank you! The username has been sent to '{email}', provided an account exists for this email"
                    . " address.",
                    array('email' => $email))
                . "</p>",
            'Email' => $email
        ));

        //Controller::$currentController = $controller;
        return $customisedController->renderWith($this->getTemplatesFor('usernamesent'));
	}
}
