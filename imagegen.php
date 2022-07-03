<?php
/* Copyright (C) 2015 Dylan J. Morrison <insidious@gmail.com>
   This work is free. You can redistribute it and/or modify
   it under the terms of the Do What The Fuck You Want To 
   Public License, Version 2, as published by Sam Hocevar.
   See http://www.wtfpl.net/ for more details.

   Stream Banner for my FF7 Stream: Image/URL generator.
  
   This code is probably useful to others only as an
   example, and a dirty one at that. Nonetheless, this
   code is freely available for your use.
*/

$font = dirname(__FILE__) . '/base/washrab.ttf';

// Function to log a line to a file.
function logIt($logstring)
{
    $logfile = fopen("imagegen.log", "a");
    fwrite($logfile, $logstring . "\n");
    fclose($logfile);
}

function pxtoPt($pixels)
{
    return ($pixels * 3) / 4;
}

function utf8_wordwrap($string, $width = 75, $break = "\n", $cut = false)
{
    if ($cut)
    {
        // Match anything 1 to $width chars long followed by whitespace or EOS,
        // otherwise match anything $width chars long
        $search = '/(.{1,' . $width . '})(?:\s|$)|(.{' . $width . '})/uS';
        $replace = '$1$2' . $break;
    }
    else
    {
        // Anchor the beginning of the pattern with a lookahead
        // to avoid crazy backtracking when words are longer than $width
        $pattern = '/(?=\s)(.{1,' . $width . '})(?:\s|$)/uS';
        $replace = '$1' . $break;
    }
    return preg_replace($search, $replace, $string);
}

// Function to print an error to an image, link the image, and then die. Contains a small subset of this program within it.
// Code fast, compile young, and leave a beautifulCorpse(). Needs to be less than 24 characters!
function beautifulCorpse($string)
{
    $errimg = imagecreatefrompng("base/background.png");
    imagesavealpha($errimg, true);
    $ercolor = imagecolorallocatealpha($errimg, 255, 255, 255, 0);
    imagettftext($errimg, pxtoPt(12) , 0, 25, 30, $ercolor, dirname(__FILE__) . '/base/washrab.ttf', $string);
    imagepng($errimg, "error.png", 0, NULL);
    $myurl = $_SERVER['REQUEST_URI'];
    $temparray = explode("/", $myurl);
    end($temparray);
    $tempkey = key($temparray);
    unset($temparray[$tempkey]);
    reset($temparray);
    unset($tempkey);
    $newurl = implode("/", $temparray) . "/error.png";
    unset($temparray);
    die($newurl);
}

//Function to word-wrap a string to the specified pixel length.
// Returns an array of two strings: A shortened first string
// and the wrapped second string. Call again on the second string
// if it's also too long!
function doWrap($string, $length, $font, $pt)
{
    $words = explode(" ", $string);
    $strbbox = imagettfbbox($pt, 0, $font, $string);
    $strlen = $strbbox[2] - $strbbox[0];
    if ($strlen <= $length)
    { // We're already done!
        return array(
            0 => $string,
            1 => ""
        );
    }
    else
    { // Wrap it!
        $secwords = array();
        while ($strlen > $length)
        {
            $wrapword = array_pop($words);
            // Check if that word is, in itself, longer than the
            // length.
            // If so, die! We can't deal with that yet.
            $wordbbox = imagettfbbox($pt, 0, $font, $wrapword);
            $wordlen = $wordbbox[2] - $wordbbox[0];
            if ($wordlen > $length)
            {
                beautifulCorpse("Word too long to wrap");
                die();
            }
            $newstr = implode(" ", $words);
            array_push($secwords, $wrapword);
            $strbbox = imagettfbbox($pt, 0, $font, $newstr);
            $strlen = $strbbox[2] - $strbbox[0];
        }
        $secwords = array_reverse($secwords);
        $secstr = implode(" ", $secwords);
        return array(
            0 => $newstr,
            1 => $secstr
        );
    }
}

// Function that returns an array of wrapped strings, using the prior function.
function fullWrap($string, $length, $font, $pt)
{
    $strbbox = imagettfbbox($pt, 0, $font, $string);
    $strlen = $strbbox[2] - $strbbox[0];
    $strings = array(
        0 => $string
    );
    $lastlen = $strlen;
    while ($lastlen > $length)
    {
        $toWrap = array_pop($strings);
        $tempArr = doWrap($toWrap, $length, $font, $pt);
        array_push($strings, $tempArr[0], $tempArr[1]);
        $lastbbox = imagettfbbox($pt, 0, $font, $tempArr[1]);
        $lastlen = $lastbbox[2] - $lastbbox[0];
    }
    return $strings;
}

// Load our XML status file.
$status = simplexml_load_file("status.xml");

// Check XML for completeness and parse to variables.
if (isset($status->disc))
{
    $disc = $status->disc;
}
else
{
}
if (isset($status->gil))
{
    $gil = $status->gil;
}
else
{
}
if (isset($status->location))
{
    $location = $status->location;
}
else
{
}
if (isset($status->streamtime))
{
    $streamtime = $status->streamtime;
}
else
{
}
if (isset($status->gametime))
{
    $gametime = $status->gametime;
}
else
{
}
if (isset($status->lastevent))
{
    $lastevent = $status->lastevent;
}
else
{
}
if (isset($status->party))
{
    if (isset($status
        ->party
        ->member))
    {
        $members = $status
            ->party->member;
        foreach ($members as $memnumber => $member)
        {
            if (isset($member->name))
            {
            }
            else
            {
            }
            if (isset($member->level))
            {
            }
            else
            {
            }
        }
    }
    else
    {
    }
}
else
{
}
/* if ($counter < 8) {
    logIt("ERR: Dying due to previous errors. DEBUG: Counter was " . $counter);
    beautifulCorpse("ERROR: Could not generate image due to incomplete XML file. See log.");
}
*/

/* The following bits create our image as a 320x720 png,
   using Covarr's background image, and set our colors.
*/

// Create our image, and be sure to save alpha
$img = imagecreatefrompng("base/background.png");
imagesavealpha($img, true);

/* Define our colors: This time it's a lot easier, as for
   starters we'll just be using white, with black for dropshadow.
*/
$black = imagecolorallocatealpha($img, 0, 0, 0, 0);
$white = imagecolorallocatealpha($img, 255, 255, 255, 0);

// Get the length of a space
$spacebbox = imagettfbbox(pxtoPt(16) , 0, $font, " ");
$spacelen = $spacebbox[2] - $spacebbox[0];
// Get the ideal max character name length
$charbbox = imagettfbbox(pxtoPt(16) , 0, $font, "Sephiroth");
$charlen = $charbbox[2] - $charbbox[0];
// Get the ideal max length of the lastevent/mod lines (number of characters calculated using trial and error :/)
$wrapbbox = imagettfbbox(pxtoPt(16) , 0, $font, "AAAAAAAAAAAAAAAAAA");
$wraplen = $wrapbbox[2] - $wrapbbox[0];

/* Add our text */
$outstring = "";
// Time
$outstring = $outstring . "Time:\n";
$ingamebbox = imagettfbbox(pxtoPt(16) , 0, $font, "In-Game");
$ingamelen = $ingamebbox[2] - $ingamebbox[0];
$streambbox = imagettfbbox(pxtoPt(16) , 0, $font, "Stream");
$streamlen = $streambbox[2] - $streambbox[0];
$localbbox = imagettfbbox(pxtoPt(16) , 0, $font, "Local");
$locallen = $localbbox[2] - $localbbox[0];
$streamdiff = $ingamelen - $streamlen;
$localdiff = $ingamelen - $locallen;
$streamspaces = $streamdiff / $spacelen;
$localspaces = $localdiff / $spacelen;
$streampad = "";
$localpad = "";
foreach (range(1, $streamspaces) as $unused)
{
    $streampad = $streampad . " ";
}
foreach (range(1, $localspaces) as $unused)
{
    $localpad = $localpad . " ";
}
$outstring = $outstring . "Local" . $localpad . " - " . date("H:i:s") . "\n";
//$outstring = $outstring . "Stream" . $streampad . " - " . $streamtime . "\n";
$outstring = $outstring . "In-Game - " . $gametime . "\n";
$outstring = $outstring . "Disc " . $disc . "/3\n" . "Location:\n " . $location . "\n";
if (isset($status->gil))
{
    $outstring = $outstring . "Gil " . $status->gil . "\n";
}
else
{
}
$nummems = 0;
foreach ($members as $member)
{
    $namebbox = imagettfbbox(pxtoPt(16) , 0, (dirname(__FILE__) . '/base/washrab.ttf') , $member->name);
    $namelen = $namebbox[2] - $namebbox[0];
    /* 	if ($namelen < $charlen) {
    $namestr = $member->name;
    $diff = $charlen - $namelen;
    $spaces = $diff/$spacelen;
    if (($diff%$spacelen) > $spacelen-3) {
    $spaces = $spaces + 1;
    }
    //foreach (range(1, $spaces) as $unused) {
    foreach (range(1, $spaces) as $unused) {
    $namestr = $namestr . " ";
    }
    } else {
    $namestr = $member->name;
    
    } */
    $namestr = $member->name;
    $outstring = $outstring . $namestr;
    if (isset($member->level))
    {
        $outstring = $outstring . " " . " L";
        $outstring = $outstring . $member->level;
    }
    if (isset($member->exptolevel))
    {
        $outstring = $outstring . " XpToLvl ";
        $outstring = $outstring . $member->exptolevel;
    }
    if (isset($member->hp))
    {
        $outstring = $outstring . "\n  HP ";
        $outstring = $outstring . $member->hp;
    }
    if (isset($member->basehp))
    {
        $outstring = $outstring . "/";
        $outstring = $outstring . $member->basehp;
    }
    if (isset($member->mp))
    {
        $outstring = $outstring . " MP ";
        $outstring = $outstring . $member->mp;
    }
    if (isset($member->basemp))
    {
        $outstring = $outstring . "/";
        $outstring = $outstring . $member->basemp;
    }
    if (isset($member->weapon))
    {
        $outstring = $outstring . "\n ";
        $outstring = $outstring . " Wp: ";
        $outstring = $outstring . $member->weapon;
    }

    if (isset($member->armor))
    {
        $outstring = $outstring . "\n  Ar: ";
        $outstring = $outstring . $member->armor;
    }
    if (isset($member->accessory))
    {
        $outstring = $outstring . "\n  Ac: ";
        $outstring = $outstring . $member->accessory . "\n";
    }
    $outstring = $outstring . "\n";
}
if (isset($status->quicknotes))
{
    $outnotes = utf8_wordwrap($status->quicknotes, 25, "\n", true);
    $outstring = $outstring . "Last Note: \n" . $outnotes;
}
$outstring = $outstring . "\n\n";
// Mods
$outstring = $outstring . "Mods:\n-Custom Random Battle/Fanfare\n-Custom Soundtrack\n-Cosmo Memory\n-60 FPS Gameplay(and NT addon)\n-Ninostyle Models(dyn+chibi)\n-Avalanche Arisen Battle\n-SYW Field Textures\n-New Threat 2.0\n-SYW Minigame Textures\n-SYW Spell Textures\n-Finishing Touch\n-Enhanced Stock UI\n-SYW Worldmap Textures\n";

// Trim any leading/trailing newlines or etc from output string
$outstring = trim($outstring);

// And output! Now with hacky drop shadow.
// First, center it.
$bounding = imagettfbbox(pxtoPt(16) , 0, $font, $outstring);
//$height = $bounding[1] - $bounding[7];
$height = 900;
$topy = 31;
imagettftext($img, pxtoPt(16) , 0, 27, $topy + 2, $black, $font, $outstring);
imagettftext($img, pxtoPt(16) , 0, 25, $topy, $white, $font, $outstring);

// Delete any PNGs still in the directory, and output our image
array_map('unlink', glob("*.png"));
$mytime = time();
$filename = "img" . $mytime . ".png";
imagepng($img, $filename, 0, NULL);

/* Get the current URL, remove the reference to the
   script, and add a refernce to the png, to get the
   correct URL for the png, then echo it for the AJAX
   calling script. This is dirty, hacky code and will
   ONLY work on Apache servers.
*/
$myurl = $_SERVER['REQUEST_URI'];
$temparray = explode("/", $myurl);
end($temparray);
$tempkey = key($temparray);
unset($temparray[$tempkey]);
reset($temparray);
unset($tempkey);
$newurl = implode("/", $temparray) . "/" . $filename;
unset($temparray);
echo ($newurl);
?>