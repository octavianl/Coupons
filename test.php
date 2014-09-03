<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
/*
header("Content-type: text/xml; charset=utf-8");
$dom = new DOMDocument('1.0', 'utf-8');
// we want a nice output
$dom->formatOutput = true;

$head = $dom->createElement('head');

$title = $dom->createElement('title');
$node = $dom->createTextNode("Hello, World");

$title->appendChild($node);
$head->appendChild($title);

$dom->appendChild($head);

echo $dom->saveXML();*/

    // Declare the interface 'iTemplate'
interface a
{
    const b = 'Interface constant';
}

// Prints: Interface constant
echo a::b;


// This will however not work because it's not allowed to 
// override constants.
class b implements a
{
    const c = 'Class constant';
}

echo b::b;
echo b::c;
?>
