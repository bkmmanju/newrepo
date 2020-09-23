<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * View all BigBlueButton instances in this course.
 *
 * @package   mod_bigbluebuttonbn
 * @copyright 2010 onwards, Blindside Networks Inc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Jesus Federico  (jesus [at] blindsidenetworks [dt] com)
 * @author    Fred Dixon  (ffdixon [at] blindsidenetworks [dt] com)
 */
require(__DIR__.'/../../config.php');
require_once(__DIR__.'/locallib.php');
require_once(__DIR__.'/lib.php');
global $CFG,$DB;
//Manju:Getting the meeting ID.
$meetingid = required_param('meetingid',PARAM_RAW);
//getting the id of particular activity.
$id = required_param('id',PARAM_RAW);

$bigbluebutton = $DB->get_record('bigbluebuttonbn',array('meetingid'=>$id));
$cm = get_coursemodule_from_instance('bigbluebuttonbn', $bigbluebutton->id, $bigbluebutton->course);
$context = context_module::instance($cm->id);
$contextid = $context->id;

//Getting the server url from the setting page.
$serverurl = $DB->get_field('config','value',array('name'=>'bigbluebuttonbn_server_url'));
$reqserverurl = str_replace('/bigbluebutton/', '', $serverurl);
//webcams url creation from the meeting ID.
$webmurl = $reqserverurl.'/download/presentation/'.$meetingid.'/video/webcams.mp4';
//MP4 url creatiion from the meeting ID.
$mp4url = $reqserverurl.'/download/presentation/'.$meetingid.'/'.$meetingid.'.mp4';
//Deskshare url creation from the meeting ID.
$deskshare = $reqserverurl.'/download/presentation/'.$meetingid.'/deskshare/deskshare.mp4';
//Lecture background.
$LectureBackgroundlink = mod_bigbluebuttonbn_image($bigbluebutton->backgroundimage,'backgroundimage',$contextid);
//Directory path where we are storing the files.
$dirname = $CFG->dataroot.'/bbb/'.$meetingid.'/';

$zip_file = $CFG->dataroot.'/bbb/'.$meetingid.'.zip';
//Check if the directory already exists.
if(!is_dir($dirname)){
//Directory does not exist, so lets create it.
    mkdir($dirname, 0777, true);
//Saving video into folder.
    $my_save_dir = $dirname;

    $filename1 = basename($webmurl);
    $complete_save_loc1 = $my_save_dir.$filename1;
    file_put_contents($complete_save_loc1,file_get_contents($webmurl));

    $filename2 = "presentation.mp4";
    $complete_save_loc2 = $my_save_dir.$filename2;
    file_put_contents($complete_save_loc2,file_get_contents($mp4url));
//Check for the file existance.
    $file_headers = @get_headers($deskshare);
    if(!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
    }
    else {
        $filename3 = basename($deskshare);
        $complete_save_loc3 = $my_save_dir.$filename3;
        file_put_contents($complete_save_loc3,file_get_contents($deskshare));
    }
    //save the lecture banckground to folder.
    $fs = get_file_storage();
    // Prepare file record object.
    $fileinfo = array('contextid' => $contextid,
        'component' =>'mod_bigbluebuttonbn',
        'filearea' => 'backgroundimage', 
        'itemid' => $bigbluebutton->backgroundimage, 
        'filepath' => '/');
    $firstpageimagefile = $fs->get_file($fileinfo['contextid'], $fileinfo['component'],
        $fileinfo['filearea'],
        $fileinfo['itemid'], $fileinfo['filepath'],$LectureBackgroundlink);
    $contents = $firstpageimagefile->get_content();
    $tempfile = $my_save_dir."LectureBackground.png";
    $fp = fopen($tempfile, 'w+');
    fputs($fp, $contents);
    fclose($fp);
    //getting preroll video.
    $syscontext = context_system::instance();
    $prerol = $fs->get_area_files($syscontext->id, 'mod_bigbluebuttonbn', 'pre_roll', 0,
        'itemid, filepath, filename', false);
    foreach ($prerol as $prekey => $prevalue) {
        $precontents = $prevalue->get_content();
        $pretempfile = $my_save_dir."Prerol.mp4";
        $prefp = fopen($pretempfile, 'w+');
        fputs($prefp, $precontents);
        fclose($prefp);
    }
    //Creating clist.txt.
    $textfilepath = $my_save_dir."clist.txt";
    $myfile = fopen($textfilepath, "w");
    $txt = "file '".$my_save_dir."out-roll.mp4'\n";
    fwrite($myfile, $txt);
    $txt = "file '".$my_save_dir."outv.mp4'\n";
    fwrite($myfile, $txt);

    $rootPath = realpath($my_save_dir);
}else{
 $rootPath = $CFG->dataroot.'/bbb/'.$meetingid;
}

// Initialize archive object
$zip = new ZipArchive();
$zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);
// Create recursive directory iterator
/** @var SplFileInfo[] $files */
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($rootPath),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($files as $name => $file)
{
    // Skip directories (they would be added automatically)
    if (!$file->isDir())
    {
        // Get real and relative path for current file
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($rootPath) + 1);

        // Add current file to archive
        $zip->addFile($filePath, $relativePath);
    }
}
// Zip archive will be created only after closing object
$zip->close();

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename='.basename($zip_file));
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($zip_file));
readfile($zip_file);
