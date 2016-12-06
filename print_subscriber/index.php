<?php

ini_set('display_errors', '1');

require_once(__DIR__ . '/includes/TWX.php');

final class Application extends TWXApplication {

    // The default page which is shown when no action is specified
    public function actionIndex() {
        $this->output(
            'index',
            ['url' => '', 'title' => 'Print Subscriber Twixl Entitlements Server']
        );
    }

    // Shows the signin form used to ask for the username and password
    public function actionSigninForm() {
        $this->output(
            'signin_form',
            ['title' => 'Login As Print Subscriber']
        );
    }

    // Check the username and password from the signin form and return an
    // entitlement token uniquely identifying this user on the entitlement
    // server.
    //
    // In this case, we use the username as the entitlement token so that
    // the actionEntitlements method can use this same token to check what
    // products the user should get free access to.
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
            $this->checkLogin($username, $password);
            $this->outputToken($username);
        } catch (Exception $e) {
            $this->outputJsonError($e->getMessage());
        }

    }

    // The signin succeeded, so we render a page that welcomes the user
    // and gives them some more information.
    public function actionSigninSucceeded() {

        $token = TWXParam::post('token');

        $this->output(
            'signin_succeeded',
            ['title' => 'Welcome', 'token' => $token]
        );

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
    // token if the user has free access to certain content or not.
    //
    // If the token is empty, we return an empty list of allowed product
    // identifiers and use the entitlement mode "purchase_unentitled". This
    // causes the app to not change any content from purchase to free.
    //
    // If the token is correct, we return a list of two product identifiers
    // as the entitled_products which tells the app to change the issues or
    // collections with these identifiers from purchase to free.
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
                'mode'              => TWX_MODE_PURCHASE_UNENTITLED,
                'token'             => $token,
            ]);
        } else if ($token == 'test') {
            $this->outputJson([
                'entitled_products' => ['com.twixlmedia.demo.product1', 'com.twixlmedia.demo.product2'],
                'mode'              => TWX_MODE_PURCHASE_UNENTITLED,
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
        return $username;
    }

}

$app = new Application();
$app->execute();
