<?php

if(!isset($pagestarttime)) // For pages that don't need auth
{
    /* Page load time */
       $mtime = microtime();
       $mtime = explode(" ",$mtime);
       $mtime = $mtime[1] + $mtime[0];
       $pagestarttime = $mtime; 
    /**/
    
}

include('smarty/Smarty.class.php');

// create object
$smarty = new Smarty;
$smarty->compile_check = true;


// Need them defined as arrays for array_merge
$error=array();
$success = array();


function display_page($template, $pagetitle = '', $header = true, $footer = true)
{
        global $smarty, $error, $success, $TOPLEVEL, $PAGETITLE, $TITLE;
        assign_vars(); // Make assign_vars function if you need to assign vars after processing
        
        list($topmenu, $menu) = generate_menus($TOPLEVEL);
        
        $smarty->assign('topmenu', $topmenu);
        $smarty->assign('submenu', $menu);
        
        // Bring in messages from session
        if(isset($_SESSION['errormessages']) || isset($_SESSION['successmessages']))
        {
            $error = array_merge($error, $_SESSION['errormessages']);
            $success = array_merge($success, $_SESSION['successmessages']);
            unset($_SESSION['errormessages']);
            unset($_SESSION['successmessages']);
        }        
        
        $smarty->assign('errors', $error);
        $smarty->assign('success', $success);
        
        $pagetitle = $pagetitle ? $pagetitle : $PAGETITLE;
        
        if($pagetitle) $smarty->assign("pagetitle", $pagetitle);
        $smarty->assign('title', $TITLE);
        
        if($header) $smarty->display('header.tpl');
        if(!$footer) return $smarty->display($template);
        $smarty->display($template);
        return $smarty->display('footer.tpl');
}

// Assign smart vars here
function assign_vars()
{
    global $smarty;
    
    // Assign menu arrays so we can softcode links in templates
    global $toplevelmenu, $submenu;
    $smarty->assign('topmenuitems', $toplevelmenu);
    $smarty->assign('submenuitems', $submenu);
    
    // Membership amount
    $smarty->assign('CONCESSION_AMOUNT', "$" . CONCESSION_AMOUNT / 100);
    $smarty->assign('FULL_AMOUNT', "$" . FULL_AMOUNT / 100 );
}

$toplevelmenu['home'] = array('label' => "Home", 'link' => '/~tim/plugldap/memberself', 'level' => 'all');
//$toplevelmenu['admin'] = array('label' => "Admin", 'link' => '/~tim/plugldap/', 'level' => 'admin');
$toplevelmenu['ctte'] = array('label' => "Committee", 'link' => '/~tim/plugldap/ctte-members', 'level' => 'committee');
$toplevelmenu['logout'] = array('label' => "Logout", 'link' => '/~tim/plugldap/logout', 'level' => 'all');
//$toplevelmenu['web'] = array('label' => "Webmasters", 'link' => '/~tim/plugldap/', 'level' => array('admin', 'committee', 'webmaster'));

// Submenu's level is defined by parent level
$submenu['ctte']['members'] = array('label' => "Membership List", 'link' => '/~tim/plugldap/ctte-members');
$submenu['ctte']['expiredmembers'] = array('link' => $submenu['ctte']['members']['link']. '?expiredmembers=1');
$submenu['ctte']['newmember'] = array('label' => "New Member", 'link' => '/~tim/plugldap/ctte-newmember');
$submenu['ctte']['editmember'] = array('label' => '', 'link' => '/~tim/plugldap/ctte-editmember?id=');
$submenu['ctte']['resendack'] = array('label' => '', 'link' => '/~tim/plugldap/resendack?member_id=');

$submenu['admin']['usergroups'] = array('label' => "Manage User Groups", 'link' => '/~tim/plugldap/');
$submenu['admin']['emailaliases'] = array('label' => "Manage Email Aliases", 'link' => '/~tim/plugldap/');

$submenu['home']['editselfdetails'] = array('link' => 'member-editdetails');
$submenu['home']['editselfforwarding'] = array('link' => 'member-editforwarding');
$submenu['home']['editselfshell'] = array('link' => 'member-editshell');
$submenu['home']['editselfpassword'] = array('link' => 'member-editpassword');


function generate_menus($top = '')
{
    global $Auth, $toplevelmenu, $submenu;
    
    $top = $top ? $top : 'home';
    
    $smenu = array();
    $topmenu = array();
    
    // Check if we are authenticated
    if (!isset($Auth) || !$Auth->checkAuth())
    {
        return array();
    }
    foreach($toplevelmenu as $key => $menu)
    {
        if(check_level($menu['level']))
        {
            $topmenu[$key]  = $menu;
            if($key == $top)
                $smenu = $submenu[$key];
        }
    }
    return array($topmenu, $smenu);
}

function redirect_with_messages($url)
{
    global $error, $success;
    $_SESSION['errormessages'] = $error;
    $_SESSION['successmessages'] = $success;
    header('Location: ' . $url);
    exit();
}

// Shells
$shells['zsh'] = '/usr/bin/zsh';
$shells['bash'] = '/bin/bash';
$shells['csh'] = '/bin/csh';

// Clean functions from GRASE Hotspot
function cleantext($text)
{

	$text = strip_tags($text);
	$text = str_replace("<", "", $text);
	$text = str_replace(">", "", $text);

#	$text = htmlspecialchars($text, ENT_NOQUOTES);
#	$text = mysql_real_escape_string($text);

	return trim($text);
}

function cleanpassword($text)
{
    // TODO: Filter out prohibited chars?
    return $text;
}
/* Need locale code for clean_number
function clean_number($number)
{
    global $locale;
    $fmt = new NumberFormatter( $locale, NumberFormatter::DECIMAL );
    return $fmt->parse(ereg_replace("[^\.,0-9]", "", clean_text($number)));
}

*/
function clean_int($number)
{
    return intval($number);
    //return intval(clean_number($number));
    //ereg_replace("[^0-9]", "", clean_text($number));
}

