# Tokenlogin Bundle for Contao

## What it is

A module to allow Login with a token alone (as opposed to username+password).

To achieve this we do the following: username and token are semantically swapped as

* we can not have one username having multiple passwords.
* but we can have different user names always having the same password! 
  The token which technically is the username will serve as the password.

## How it works -- changes to the regular login process

1. Extend the regular login module (`ModuleTokenlogin` extends `\Contao\ModuleLogin`)
2. Provide a special login form that takes care of the changes. 
   This form will not have a password field.
3. Set the (POST-)value for `password` so the regular login module will be happy.
4. (You) register a method for the `importUser` hook that creates a new member. 
   See below for an example.


## What you have to provide

* You have to implement the Hook 
[`importUser`](https://docs.contao.org/books/api/extensions/hooks/importUser.html) 
and create a new Member there.

* You have to have a list of valid tokens somewhere to decide if a login attempt 
should be considered valid.


An Example:

```php
// your bundle's config.php

$GLOBALS['TL_HOOKS']['importUser'][] = array('MyVendor\MyBundle\MyHooks', 'myImportUser'); 
```

```php
namespace MyVendor\MyBundle;

use Fiedsch\TokenloginBundle\ModuleTokenlogin;
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
```



