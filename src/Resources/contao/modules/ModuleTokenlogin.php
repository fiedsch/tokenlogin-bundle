<?php 

/**
 * @copyright  Andreas Fieger 2015-2018
 * @author     Andreas Fieger (https://github.com/fiedsch)
 * @license    MIT
 *
 * Module to allow login with a token alone (as opposed to username+password).
 * To achieve this we do the following: username and token are semantically
 * swapped as
 *  - we can not have one username having multiple passwords,
 *  - but we can have different user names always having the same password!
 *
 * The password for all such users we will be the same (see class constant TOKENUSERPASSWORD).
 *
 * The token which technically is the username will serve as the "password/passcode".
 *
 * We have a special login form that takes care of these changes. It does not have
 * a password field. The corresponding (POST-)value will be set here.
 *
 * For the module to work we need to have a method registered for the importUser hook
 * that creates a new member. See README.md for an example.
 */

namespace Fiedsch\TokenloginBundle;

use Contao\ModuleLogin;
use Contao\Input;
use Contao\System;
use Patchwork\Utf8;
use Symfony\Component\HttpFoundation\Request;

class ModuleTokenlogin extends ModuleLogin {

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_tokenlogin';
    
    /**
     * the pseudo password
     */
    const TOKENUSERPASSWORD = "tokenuser";

    /**
     * generate the module
     */
    public function generate() {

        if (TL_MODE == 'BE') {

            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['tokenlogin'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();

        }

        $this->loadLanguageFile('modules');

        // If the Url contains a token parameter use it to perform an "autologin".

        $token_from_url = Input::get('token');

        if ($token_from_url && !FE_USER_LOGGED_IN) {
            Input::setPost('username', $token_from_url);
            Input::setPost('FORM_SUBMIT', 'tl_login_'.$this->id);
            Input::setPost('REQUEST_TOKEN', REQUEST_TOKEN);
        }

        // Login
        if (Input::post('FORM_SUBMIT') == 'tl_login_'.$this->id) {

            // Check whether username (token) is set
            if (empty($_POST['username'])) {
                // adjust error message (ModuleLogin asks for username *and* password)
                System::getContainer()->get('session')->getFlashBag()->set($this->strFlashType, $GLOBALS['TL_LANG']['MSC']['logtok_emptyField']);
                $this->reload();
            }

            // Login: there is no password field in our login form.
            // We will set this here so the regular login process will be happy.

            // This is needed as \Contao\ModuleLogin uses $_POST['password']
            Input::setPost('password', self::TOKENUSERPASSWORD);
            // and this is additionally needed as \Contao\User uses request->get('password')
            /** @var Request $request */
            $request = System::getContainer()->get('request_stack')->getCurrentRequest();
            $request->request->set('password', self::TOKENUSERPASSWORD);
        }

        // proceed as the normnal login module
        return parent::generate();
    }

    /**
     * Compile the module.
     */
    protected function compile() {

        parent::compile();

        if (!FE_USER_LOGGED_IN) {

            $this->Template->username = $GLOBALS['TL_LANG']['FMD']['toklog_token'];

            // adjust error message (if any)
            if ($this->Template->message == $GLOBALS['TL_LANG']['ERR']['invalidLogin']) {
                $this->Template->message = $GLOBALS['TL_LANG']['MSC']['logtok_invalidLogin'];
            }

        } else {

            // adjust the "logged in as ..." message

            $this->Template->loggedInAs
                = sprintf($GLOBALS['TL_LANG']['MSC']['logtok_loggedInAs'], $this->User->username);

        }

    }

    /**
     * Change reload() inherited from \Contao\Controller and remove the token get parameter
     * in order to avoid an infinite redirect loop.
     */
    public static function reload()
    {
        $uri = \Environment::get('uri');
        if (preg_match("/\?token=.+/", $uri)) {
            $uri = preg_replace("/\?token=.+/", '', $uri);
            static::redirect($uri);
        }
        parent::reload();
    }

}