<?php

ini_set('display_errors', '1');

require_once(__DIR__ . '/includes/TWX.php');

final class Application extends TWXApplication {

    // The default page which is shown when no action is specified
    public function actionIndex() {
        $this->output(
            'index',
            ['url' => '', 'title' => 'Regions Twixl Entitlements Server']
        );
    }

    // Shows the signin form used to ask for the region
    public function actionSigninForm() {
        $this->output(
            'signin_form',
            ['title' => 'Select your region']
        );
    }

    // Check the region from the signin form and return it as the
    // entitlement token. This will allow us to return the correct list
    // of entitlements later on.
    //
    // If the region is invalid, we return an error message.
    //
    // The different parameters are sent as a HTTP POST request.
    public function actionSignin() {

        $region = TWXParam::any('region');

        $deviceUdid = TWXParam::any('udid');
        $appId      = TWXParam::any('app_id');
        $appVersion = TWXParam::any('app_version');

        try {
            $token = $this->checkRegion($region);
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

    // The entitlements call checks the token to find out what region
    // was selected. Based on the region, it will return a different list
    // of product identifiers which combined with the "hide_unentitled"
    // mode makes the app show or hide different issues.
    //
    // If the token is invalid, we return an error message.
    //
    // The different parameters are sent as a HTTP POST request.
    public function actionEntitlements() {

        $token = TWXParam::any('token');

        $productIdentifiers = json_decode(TWXParam::any('product_identifiers'), true);
        $appId              = TWXParam::any('app_id');
        $appVersion         = TWXParam::any('app_version');

        if ($token == 'region1') {
            $this->outputJson([
                'entitled_products' => ['com.twixlmedia.demo.region1.issue1', 'com.twixlmedia.demo.region1.issue2'],
                'mode'              => TWX_MODE_HIDE_UNENTITLED,
                'token'             => $token,
            ]);
        } else if ($token == 'region2') {
            $this->outputJson([
                'entitled_products' => ['com.twixlmedia.demo.region2.issue1', 'com.twixlmedia.demo.region2.issue2'],
                'mode'              => TWX_MODE_HIDE_UNENTITLED,
                'token'             => $token,
            ]);
        } else {
            $this->outputJson([
                'entitled_products' => [],
                'mode'              => TWX_MODE_HIDE_UNENTITLED,
                'token'             => $token,
            ]);
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
    private function checkRegion($region) {
        if (!in_array($region, ['region1', 'region2'])) {
            throw new Exception("Invalid region");
        }
        return $region;
    }

}

$app = new Application();
$app->execute();
