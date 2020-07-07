<?php

declare(strict_types=1);

//echo chr(1011);
include 'vendor/autoload.php';

use GraphAware\Bolt\PackStream\StreamChannel;
use GraphAware\Bolt\PackStream\Unpacker;
use GraphAware\Bolt\Protocol\Message\RawMessage;

//JAVASCRIPT INIT MESSAGE
//$bytes = "\x00\x36\xb1\x01\xa2\x8a\x75\x73\x65\x72\x5f\x61\x67\x65\x6e\x74\xd0\x1a\x6e\x65\x6f\x34\x6a\x2d\x6a\x61\x76\x61\x73\x63\x72\x69\x70\x74\x2f\x30\x2e\x30\x2e\x30\x2d\x64\x65\x76\x86\x73\x63\x68\x65\x6d\x65\x84\x6e\x6f\x6e\x65\x00\x00";
//$u = new Unpacker(new StreamChannel(new RawMessage($bytes)));
//$raw = $u->unpack();
//var_dump($raw);
//object(GraphAware\Bolt\PackStream\Structure\Structure)#5 (3) {
//["signature"\x"GraphAware\Bolt\PackStream\Structure\Structure"\xprivate]=>
//  string(4) "INIT"
//["elements"\x"GraphAware\Bolt\PackStream\Structure\Structure"\xprivate]=>
//  array(1) {
//    [0]=>
//    array(2) {
//        ["user_agent"]=>
//      string(26) "neo4j-javascript/0.0.0-dev"
//        ["scheme"]=>
//      string(4) "none"
//    }
//  }
//  ["size"\x"GraphAware\Bolt\PackStream\Structure\Structure"\xprivate]=>
//  int(1)
//}

//LONGITUDEONE INIT MESSAGE
//$bytes = "\x00\x1d\xb2\x01\xd0\x18\x47\x72\x61\x70\x68\x41\x77\x61\x72\x65\x2d\x42\x6f\x6c\x74\x50\x48\x50\x2f\x31\x2e\x35\x2e\x34\xa0\x00\x00";
//$bytes = "\x00\x21\xb2\x01\xd0\x18\x47\x72\x61\x70\x68\x41\x77\x61\x72\x65\x2d\x42\x6f\x6c\x74\x50\x48\x50\x2f\x32\x2e\x30\x2e\x30\x84\x6e\x6f\x6e\x65\x00\x00";
//$u = new Unpacker(new StreamChannel(new RawMessage($bytes)));
//$raw = $u->unpack();
//var_dump($raw);
//object(GraphAware\Bolt\PackStream\Structure\Structure)#2 (3) {
//["signature"\x"GraphAware\Bolt\PackStream\Structure\Structure"\xprivate]=>
//  string(4) "INIT"
//["elements"\x"GraphAware\Bolt\PackStream\Structure\Structure"\xprivate]=>
//  array(2) {
//    [0]=>
//    string(24) "GraphAware-BoltPHP/1.5.4"
//    [1]=>
//    array(0) {
//    }
//  }
//  ["size"\x"GraphAware\Bolt\PackStream\Structure\Structure"\xprivate]=>
//  int(2)
//}
//
//Process finished with exit code 0


//MAINTENANT JE DOIS COMPRENDRE LES ECHANGES SUIVANTS DANS JS
//W:00 02 b0 0f 00 00
//R:00 03 b1 70 a0 00 00
//W:00 02 b0 02 00 00
//$bytes = "\x00\x02\xb0\x0f\x00\x00"; //RESET
//$bytes = "\x00\x03\xb1\x11\xa0\x00\x00"; //BEGINMESSAGE
//$bytes = "\x00\x03\xb1\x70\xa0\x00\x00"; //SUCCESS
//$bytes = "\x00\x02\xb0\x02\x00\x00"; //GOODBYE
$bytes = "\x00\x1f\xb3\x10\xd0\x19\x4d\x41\x54\x43\x48\x20\x28\x6e\x29\x20\x44\x45\x54\x41\x43\x48\x20\x44\x45\x4c\x45\x54\x45\x20\x6e\xa0\xa0\x00\x00\x00\x08\xb1\x3f\xa1\x81\x6e\xc9\x03\xe8\x00\x00"; //'RUN MATCH (n) DETACH DELETE n'
$bytes = "\x00\x08\xb1\x3f\xa1\x81\x6e\xc9\x03\xe8\x00\x00"; //PULL_ALL
$u = new Unpacker(new StreamChannel(new RawMessage($bytes)));
$raw = $u->unpack(new RawMessage($bytes));
var_dump($raw);

//TODO Regrouper le RUN et le PULL ALL dans le même message
//Trouver comment mettre en place le timeout!

