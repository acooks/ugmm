<?php

// Start of code for sorting all members into groups

require_once('/etc/private/ldapconnection.inc.php');
require_once '/usr/share/plug-ugmm/www/PLUG/PLUG.class.php';

$PLUG = new PLUG($ldap);

// Select all accounts
$filter = "(shadowExpire=*)";

$members = $PLUG->load_members_dn_from_filter($filter);

foreach($members as $dn)
{
    $member = new Person($ldap);
    $member->load_ldap($dn);

    $details = $member->userarray();
    
    echo "User ".$details['displayName']. " is being grouped correctly\n";
    // Set correct group
    $member->set_status_group();

}


?>
