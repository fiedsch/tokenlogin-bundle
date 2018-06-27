<?php

$GLOBALS['FE_MOD']['user']['tokenlogin'] = 'Fiedsch\TokenloginBundle\ModuleTokenlogin';

$GLOBALS['TL_HOOKS']['importUser'][] = array('Fiedsch\TokenloginBundle\MyHooks', 'myImportUser');


