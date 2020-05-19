<?php

$gatewayUrl = 'http://subu20.ad.novel-t.ch/argusconfig/argusGateway.php';

$userAgents = [
    'Mozilla/5.0 (Android; SDK 29; SM-G960F Build/PPR1.180610.011; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/74.0.3729.157 Mobile Safari/537.36'
    // ...
];

$diseases = ['10ROU', '11MEN', '12CHO'];
$froms = ['+228000002', '+228000003', '+228000004'];

$week = 18;
$rid = mt_rand(1, 999);
$androidid = mt_rand(1, 9999);

for ($i = 0; $i <= 2; $i++)
{
    $cas = mt_rand(0, 1500);
    $deces = mt_rand(0, $cas);

    $params = [
        'action' => 'incoming',
        'version' => 29, // test purpose
        'phone_number' => '+228000001',
        'phone_operator' => 'Orange',
        'log' => true,
        'network' => 'WIFI',

        //'from' => '+33631504945',
        //'from' => '+228000004',
        'from' => $froms[0],
        'timestamp' => time(),
        'message' => 'REPORT DISEASE=' . $diseases[$i] . ', YEAR=2020, WEEK=' . $week . ', CAS=' .  $cas . ', ANDROIDID=' . $androidid . ', DECES=' . $deces . ', RID=' . $rid
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $gatewayUrl);
    //curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $userAgents[0]);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    $res = curl_exec($ch);

    curl_close($ch);
}
