<?php

ini_set('display_errors', '1');

require_once(__DIR__ . '/includes/TWX.php');

final class Application extends TWXApplication {

    // The entitlement token used to identify a user which has full access
    //
    // For security reasons, change this to a custom value
    private $defaultToken = 'd6b3959ecdce4cc29830bcfc0473c938';

    // The default page which is shown when no action is specified
    public function actionIndex() {
        $this->output(
            'index',
            ['url' => '', 'title' => 'All Access Twixl Entitlements Server']
        );
    }

    // Shows the signin form used to ask for the username and password
    public function actionSigninForm() {
        $this->output(
            'signin_form',
            ['title' => 'Login']
        );
    }

    // Check the username and password from the signin form and return an
    // entitlement token uniquely identifying this user on the entitlement
    // server.
    //
    // As we are only interested in finding out if the user has access or
    // not, we return the same entitlement token for all users.
    //
    // For added security, you can return a unique token for each user
    // which can then be verified in the actionEntitlements method.
    //
    // If the login fails, we return an error message.
    //
    // The different parameters are sent as a HTTP POST request.
    public function actionSignin() {

        $username = TWXParam::any('username');
        $password = TWXParam::any('password');

        $deviceUdid = TWXParam::any('udid');
        $appId      = TWXParam::any('app_id');
        $appVersion = TWXParam::any('app_version');

        try {
            $token = $this->checkLogin($username, $password);
            $this->outputToken($token);
        } catch (Exception $e) {
            $this->outputJsonError($e->getMessage());
        }

    }

    // The signin succeeded, so we are just closing the entitlements
    // popup by calling a specific url
    public function actionSigninSucceeded() {
        TWXHttpUtils::redirect('tp-close://self');
    }

    // The signin didn't work, we retrieve the error from the URL and
    // render the error screen.
    public function actionSigninError() {

        $error = TWXParam::get('error', 'Something went wrong…');

        $this->output(
            'signin_error',
            ['title' => 'An Error Occurred', 'error' => $error]
        );

    }

    // The entitlements call checks the token, and decides based on the
    // token if the user has access to content or not.
    //
    // If the token is empty, we return an empty list of allowed product
    // identifiers and use the entitlement mode "hide_unentitled". This
    // causes the app to not show any content.
    //
    // If the token is correct, we return "*" as the entitled_products
    // which tells the app to show all content.
    //
    // If the token is invalid, we return an error message.
    //
    // The different parameters are sent as a HTTP POST request.
    public function actionEntitlements() {

        $token = TWXParam::any('token');

        $productIdentifiers = json_decode(TWXParam::any('product_identifiers'), true);
        $appId              = TWXParam::any('app_id');
        $appVersion         = TWXParam::any('app_version');

        if (empty($token)) {
            $this->outputJson([
                'entitled_products' => [],
                'mode'              => TWX_MODE_HIDE_UNENTITLED,
                'token'             => $token,
            ]);
        } else if ($token == $this->defaultToken) {
            $this->outputJson([
                'entitled_products' => [TWX_ENTITLE_ALL],
                'mode'              => TWX_MODE_HIDE_UNENTITLED,
                'token'             => $token,
            ]);
        } else {
            $this->outputJsonError('Invalid credentials');
        }

    }

    // This is a helper method to check if the login is correct or not.
    //
    // If the login is correct, we return an entitlement token.
    //
    // If the loign is incorrect, we throw an Exception.
    //
    // This is the place where you can customize the way a username and
    // password are verified. You can for example perform a database call
    // to verify the credentials.
    private function checkLogin($username, $password) {
        if ($username != 'test' && $password != 'test') {
            throw new Exception("Invalid username or password");
        }
        return $this->defaultToken;
    }

}

$app = new Application();
$app->execute();
