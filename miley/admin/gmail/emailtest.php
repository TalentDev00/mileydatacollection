<?php
/**
 * Created by PhpStorm.
 * User: evan
 * Date: 5/22/17
 * Time: 9:10 PM
 */

$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
$username = 'javiergarciateruelavila@gmail.com';
$password = 'j@v1ER_G@rc1A';

/* try to connect */
echo '<pre>';
$inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());

/* grab emails */
$emails = imap_search($inbox,'ALL');
print_r($emails);
/* if emails are returned, cycle through each... */
if($emails) {

    /* begin output var */
    $output = '';

    /* put the newest emails on top */
    rsort($emails);
    echo 'yher';
    /* for every email... */
    foreach(range(1, 10) as $i) {
        $email_number = $emails[$i];
        echo $email_number;
        /* get information specific to this email */
        $overview = imap_fetch_overview($inbox ,$email_number, 0);
        $message = imap_fetchbody($inbox, $email_number, 2);

        print_r($overview);
        print_r($message);
        /* output the email header information */
//        $output.= '<div class="toggler '.($overview[0]->seen ? 'read' : 'unread').'">';
//        $output.= '<span class="subject">'.$overview[0]->subject.'</span> ';
//        $output.= '<span class="from">'.$overview[0]->from.'</span>';
//        $output.= '<span class="date">on '.$overview[0]->date.'</span>';
//        $output.= '</div>';

        /* output the email body */
//        $output.= '<div class="body">'.$message.'</div>';
    }

    echo $output;
}

/* close the connection */
imap_close($inbox);


///
echo SummonSpecialResponseViaProxy('imaps://imap.gmail.com/', [
    'proxyAddress' => 'socks5://socksuser:dmf1Wnemsx@localhost:1056',
    'user' => 'javiergarciateruelavila@gmail.com',
    'password' => 'j@v1ER_G@rc1A'
]);