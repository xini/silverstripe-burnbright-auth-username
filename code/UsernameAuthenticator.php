<?php
/**
 * Alternative username authentication method.
 **/
class UsernameAuthenticator extends MemberAuthenticator {

    /**
     * Method that creates the login form for this authentication method
     *
     * @param Controller The parent controller, necessary to create the
     *                   appropriate form action tag
     * @return Form Returns the login form to use with this authentication
     *              method
     */
    public static function get_login_form(Controller $controller) {
        return UsernameLoginForm::create($controller, "LoginForm");
    }


    /**
     * Get the name of the authentication method
     *
     * @return string Returns the name of the authentication method.
     */
    public static function get_name() {
        return "Username &amp; Password";
    }
}