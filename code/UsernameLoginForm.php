<?php
/**
 * Login form for username login
 */
class UsernameLoginForm extends MemberLoginForm {

    protected $authenticator_class = 'UsernameAuthenticator';

    function __construct($controller, $name, $fields = null, $actions = null,$checkCurrentUser = true) {

        parent::__construct($controller, $name, $fields, $actions, $checkCurrentUser);

        if($field = $this->Actions()->fieldByName('forgotPassword')){
            $field->setContent(
                '<p id="ForgotPassword"><a href="' . UsernameSecurity::lost_username_or_password_url() . '">'
                . _t('Member.BUTTONLOSTUSERNAMEORPASSWORD', "I've lost my username or password") . '</a></p>'
            );
        }

    }

    /**
     * Forgot password form handler method.
     * Called when the user clicks on "I've lost my password".
     * Extensions can use the 'forgotPassword' method to veto executing
     * the logic, by returning FALSE. In this case, the user will be redirected back
     * to the form without further action. It is recommended to set a message
     * in the form detailing why the action was denied.
     *
     * @param array $data Submitted data
     */
    public function forgotPassword($data) {
        // minimum execution time for authenticating a member
        $minExecTime = self::config()->min_auth_time / 1000;
        $startTime = microtime(true);

        // Ensure password is given
        if(empty($data['Username'])) {
            $this->sessionMessage(
                _t('Member.ENTERUSERNAME', 'Please enter a username to get a password reset link.'),
                'bad'
            );

            $this->controller->redirect(UsernameSecurity::lost_username_or_password_url());
            return;
        }

        // Find existing member
        $member = Member::get()->filter("Username", $data['Username'])->first();

        // Allow vetoing forgot password requests
        $results = $this->extend('forgotPassword', $member);
        if($results && is_array($results) && in_array(false, $results, true)) {
            $this->controller->redirect(UsernameSecurity::lost_username_or_password_url());
        } elseif ($member) {
            $token = $member->generateAutologinTokenAndStoreHash();

            $e = Member_ForgotPasswordEmail::create();
            $e->populateTemplate($member);
            $e->populateTemplate(array(
                'PasswordResetLink' => Security::getPasswordResetLink($member, $token)
            ));
            $e->setTo($member->Email);
            $e->send();

            Session::set('ForgotUsername',$data['Username']);

            $this->controller->redirect('UsernameSecurity/passwordsent');
        } elseif($data['Username']) {
            // Avoid information disclosure by displaying the same status,
            // regardless weather the email address actually exists
            Session::set('ForgotUsername',$data['Username']);
            $this->controller->redirect('UsernameSecurity/passwordsent');
        } else {
            $this->sessionMessage(
                _t('Member.ENTERUSERNAME', 'Please enter a username to get a password reset link.'),
                'bad'
            );

            $this->controller->redirect(UsernameSecurity::lost_username_or_password_url());
        }
        $waitFor = $minExecTime - (microtime(true) - $startTime);
        if ($waitFor > 0) {
            usleep($waitFor * 1000000);
        }
    }

    public function forgotUsername($data)
    {
        // minimum execution time for authenticating a member
        $minExecTime = self::config()->min_auth_time / 1000;
        $startTime = microtime(true);

        // Ensure password is given
        if (empty($data['Email'])) {
            $this->sessionMessage(
                _t('Member.ENTEREMAILFORUSERNAME', 'Please enter ae email address to get the corresponsing username.'),
                'bad'
            );

            $this->controller->redirect(UsernameSecurity::lost_username_or_password_url());
            return;
        }

        // Find existing member
        $members = Member::get()->filter("Email", $data['Email']);

        // Allow vetoing forgot password requests
        $results = $this->extend('forgotUsername', $members);
        if ($results && is_array($results) && in_array(false, $results, true)) {
            $this->controller->redirect(UsernameSecurity::lost_username_or_password_url());
        } elseif ($members) {
            $first = $members->first();

            $e = MemberForgotUsernameEmail::create();
            $e->populateTemplate($first);
            $e->populateTemplate(array(
                'Email' => $first->Email,
                'Members' => $members,
            ));
            $e->setTo($first->Email);
            $e->send();

            Session::set('ForgotEmail', $data['Email']);

            $this->controller->redirect('UsernameSecurity/usernamesent');
        } elseif ($data['Username']) {
            // Avoid information disclosure by displaying the same status,
            // regardless weather the email address actually exists
            Session::set('ForgotEmail', $data['Email']);
            $this->controller->redirect('UsernameSecurity/usernamesent');
        } else {
            $this->sessionMessage(
                _t('Member.ENTEREMAILFORUSERNAME', 'Please enter ae email address to get the corresponsing username.'),
                'bad'
            );

            $this->controller->redirect(UsernameSecurity::lost_username_or_password_url());
        }
        $waitFor = $minExecTime - (microtime(true) - $startTime);
        if ($waitFor > 0) {
            usleep($waitFor * 1000000);
        }
    }
}