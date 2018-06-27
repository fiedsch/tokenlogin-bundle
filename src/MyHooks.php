<?php

/**
 * Example and Test for Hook needed by ModuleTokenlogin
 *
 * Do not use in production as we do not check for valid tokens
 * and consider any token valid.
 */

namespace Fiedsch\TokenloginBundle;

// use Fiedsch\TokenloginBundle\ModuleTokenlogin;
use Contao\MemberModel;
use Contao\Encryption;

class MyHooks {
    /**
     * We want our users that log in via token to be in a special member group.
     * This is this group's ID.
     * You would typically want to make that configurable.
     */
    const TOKENUSER_MEMBERGROUP_ID = 1;

    /**
     * @param string $strUsername The unknown username.
     * @param string $strPassword  The password submitted in the login form.
     * @param string $strTable The user model table, either tl_member (for front end) or tl_user (for back end).
     * @return bool true if the user was successfully imported, false otherwise
     */
    public function myImportUser($strUsername, $strPassword, $strTable) {
        // front end only!
        if ($strTable == 'tl_member') {
            return $this->importFromTokenlist($strUsername, $strPassword);
        }
        return false;
    }

    /**
     * @param $strUsername string The unknown username.
     * @param $strPassword string The password submitted in the login form.
     * @return bool true if the user was successfully imported, false otherwise
     */
    protected function importFromTokenlist($strUsername, $strPassword) {
        try {
            // (0) Check for our special situation (just an additional test for the paranoid)
            if ($strPassword !== ModuleTokenlogin::TOKENUSERPASSWORD) { return false; }

            // (1) Check if the token supplied in $strUsername is found in our database
            // or token list. If not found: return false
            //
            // TODO: implement/fit to your needs.
            //
            // Always assume true in this test!
            //
            // (2) If the token is valid, create new member record -- which does not
            // exist as otherwise myImportUser would not have been called
            //
            // Thoughts: (maybe TODOs)
            // ignore case in $strUsername as otherwise xyz123 and YXZ123 will be two different users
            // (only applies if the above lookup for the token was case insensitive)

            $newMember = new MemberModel();
            $newMember->allowLogin = true;
            $newMember->password = Encryption::hash($strPassword);
            $newMember->username = $strUsername;
            $newMember->login = true;
            $newMember->firstname = "Token";
            $newMember->lastname = "User";
            $newMember->email = sprintf("%s@example.com", $strUsername);
            $newMember->groups = [ self::TOKENUSER_MEMBERGROUP_ID ];
            $newMember->dateAdded = time();
            $newMember->save();

            \System::log(sprintf("created new member for token %s", $newMember->username), __METHOD__, TL_ACCESS);

            // (3) purge old entries? This might be a good place.

            // (4) return true to indicate success
            return true;
        } catch (\Exception $ignored) {
            return false;
        }
    }
}