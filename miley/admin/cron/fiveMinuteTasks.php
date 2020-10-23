<?php
/**
 * Created by PhpStorm.
 * User: evan
 * Date: 8/28/17
 * Time: 10:44 AM
 */

use SCMiley\Email\Email;

require_once('../../../../includes/adminGlobals.php');
set_time_limit(3600);
//die();
//$em = \SCMiley\Database\DoctrineEntityManager::get();
//
//\SCMiley\Email\Email::sendInterdependenceSystemMessage(
//    'evan@seop.com', 'running five minute', 'this is the email', 'this is the email'
//);
 \SCMiley\Google\GoogleSearch::searchNewsTerms();
die();