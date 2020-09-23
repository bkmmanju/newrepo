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
//presentation.mp4 url creatiion from the meeting ID.
$mp4url = $reqserverurl.'/download/presentation/'.$meetingid.'/'.$meetingid.'.mp4';
//Deskshare url creation from the meeting ID.
$deskshare = $reqserverurl.'/download/presentation/'.$meetingid.'/deskshare/deskshare.mp4';
//Lecture background.
$LectureBackgroundlink = mod_bigbluebuttonbn_image($bigbluebutton->backgroundimage,'backgroundimage',$contextid);
//Directory path where we are storing the files.
$dirname = $CFG->dataroot.'/bbb/'.$meetingid.'/';
//Mihir code below
$finalpath  = $CFG->dataroot.'/bbb/'.$meetingid;
//Final video link.
$filetodownload = $finalpath.'/lecture.mp4';

//Manju: check if the final video exists directly download.
if (file_exists($filetodownload)) {
  $filetodownload = $finalpath.'/lecture.mp4';
  echo $filetodownload;

  echo "Starting process to prepare video...\n\n";
  echo "Note: This page will take sometime to prepare your mp4 file so please wait and DO NOT CLOSE THE BROWSER";

  //Now download this lecture.mp4
  header('Content-Description: File Transfer');
  header('Content-Type: application/octet-stream');
  header('Content-Disposition: attachment; filename='.basename($filetodownload));
  header('Content-Transfer-Encoding: binary');
  header('Expires: 0');
  header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
  header('Pragma: public');
  header('Content-Length: ' . filesize($filetodownload));

  ob_clean();
  flush();
  readfile($filetodownload);
}else if(!is_dir($dirname)){
    //Manju:if lecture.mp4 not exists then check for the folder existance. If not present create the folder proceed upto download.
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
    //Manju: if bachground image is not present in activity download default background image.
    if(!empty($firstpageimagefile)){
        $contents = $firstpageimagefile->get_content();
        $tempfile = $my_save_dir."LectureBackground.png";
        $fp = fopen($tempfile, 'w+');
        fputs($fp, $contents);
        fclose($fp);
    }else{
        $syscontext = context_system::instance();
        $defaultback = $fs->get_area_files($syscontext->id, 'mod_bigbluebuttonbn', 'defaultbackground', 0,
            'itemid, filepath, filename', false);
        foreach ($defaultback as $dfkey => $dfvalue) {
            $defcontents = $dfvalue->get_content();
            $deftempfile = $my_save_dir."LectureBackground.png";
            $deffp = fopen($deftempfile, 'w+');
            fputs($deffp, $defcontents);
            fclose($deffp);
        }
    }
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


    echo $finalpath;

    // find the BBB activity recordig type and then pass the $type 
    $type = $bigbluebutton->recordingstyle;
    ffmpeg_work($type,$finalpath); // MIhir 31st August pass the BBB recording type

    $filetodownload = $finalpath.'/lecture.mp4';
        //Now download this lecture.mp4
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($filetodownload));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filetodownload));

    ob_clean();
    flush();
    readfile($filetodownload);
}else if(is_dir($dirname)){
    //Manju: if the directory exists. check for all the neccessory files present or not.
    $my_save_dir = $dirname;
    //1. check for presentation.mp4.
    $presentationfile = $dirname."presentation.mp4";
    if (!file_exists($presentationfile)) {
        $filename2 = "presentation.mp4";
        $complete_save_loc2 = $my_save_dir.$filename2;
        file_put_contents($complete_save_loc2,file_get_contents($mp4url));
    }

    //2. check for Prerol.mp4.
    $prerolfile = $dirname."Prerol.mp4";
    if (!file_exists($prerolfile)) {
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
    }

    //3. check for webcams.mp4.
    $webcamsfile = $dirname."webcams.mp4";
    if (!file_exists($webcamsfile)) {
        $filename1 = basename($webmurl);
        $complete_save_loc1 = $my_save_dir.$filename1;
        file_put_contents($complete_save_loc1,file_get_contents($webmurl));

    }

    //4. check for LectureBackground.png.
    $lecturebackfile = $dirname."LectureBackground.png";
    if (!file_exists($lecturebackfile)) {
        $fs = get_file_storage();
        $fileinfo = array('contextid' => $contextid,
            'component' =>'mod_bigbluebuttonbn',
            'filearea' => 'backgroundimage',
            'itemid' => $bigbluebutton->backgroundimage,
            'filepath' => '/');
        $firstpageimagefile = $fs->get_file($fileinfo['contextid'], $fileinfo['component'],
            $fileinfo['filearea'],
            $fileinfo['itemid'], $fileinfo['filepath'],$LectureBackgroundlink);

        if(!empty($firstpageimagefile)){
            $contents = $firstpageimagefile->get_content();
            $tempfile = $my_save_dir."LectureBackground.png";
            $fp = fopen($tempfile, 'w+');
            fputs($fp, $contents);
            fclose($fp);
        }else{
            $syscontext = context_system::instance();
            $defaultback = $fs->get_area_files($syscontext->id, 'mod_bigbluebuttonbn', 'defaultbackground', 0,
                'itemid, filepath, filename', false);
            foreach ($defaultback as $dfkey => $dfvalue) {
                $defcontents = $dfvalue->get_content();
                $deftempfile = $my_save_dir."LectureBackground.png";
                $deffp = fopen($deftempfile, 'w+');
                fputs($deffp, $defcontents);
                fclose($deffp);
            }
        }
    }

    //5. check for clist.txt.
    $clistfile = $dirname."clist.txt";
    if (!file_exists($clistfile)) {
        $textfilepath = $my_save_dir."clist.txt";
        $myfile = fopen($textfilepath, "w");
        $txt = "file '".$my_save_dir."out-roll.mp4'\n";
        fwrite($myfile, $txt);
        $txt = "file '".$my_save_dir."outv.mp4'\n";
        fwrite($myfile, $txt);
    }
    echo $finalpath;

    //Mihir find the activity recording type and pass the $type and $final path 31st Aug 2020
    $type = $bigbluebutton->recordingstyle;
    ffmpeg_work($type,$finalpath);

    $filetodownload = $finalpath.'/lecture.mp4';
        //Now download this lecture.mp4
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($filetodownload));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filetodownload));

    ob_clean();
    flush();
    readfile($filetodownload);
}
//Manju: check for lecture.mp4 if present delete outv.mp4 , presentation-scaled.mp4,  out-roll.mp4 as they are temporary files.
if(file_exists($filetodownload)){
    unlink($dirname."outv.mp4");
    unlink($dirname."presentation-scaled.mp4");
    unlink($dirname."out-roll.mp4");
}

// Manju note
// Write code to delete  outv.mp4 , presentation-scaled.mp4,  out-roll.mp4 as they are temporary files.
//echo $OUTPUT->footer();

//this function will receive if the BBB activity type and based on that it will execute the ffmpeg script

function ffmpeg_work($type,$finalpath) {

  // if type == 1 then COMPLETE video + presentation
  if ($type == 1) {
    echo "Starting process to prepare video...\n\n";
    echo "Note: This page will take sometime to prepare your mp4 file so please wait and DO NOT CLOSE THE BROWSER";

    exec("/usr/bin/ffmpeg -y -i ".$finalpath."/presentation.mp4 -vf scale=1440x810 -max_muxing_queue_size 2048 ".$finalpath."/presentation-scaled.mp4 ");

    //exec('/usr/bin/ffmpeg -y -i '.$finalpath.'/LectureBackground.png -i '.$finalpath.'/presentation-scaled.mp4 -i '.$finalpath.'/webcams.mp4 -filter_complex " [0:v] scale=1920x1080 [background]; [1:v] scale=1440x810 [presentation]; [2:v] scale=480x360 [video]; [background][presentation] overlay=480:180 [temp1]; [temp1][video] overlay=0:0 [outv] " -map [outv] -map 2:a -c:v libx264 -r 15 '.$finalpath.'/outv.mp4 ');
    exec('/usr/bin/ffmpeg -y -i '.$finalpath.'/LectureBackground.png -i '.$finalpath.'/presentation-scaled.mp4 -i '.$finalpath.'/webcams.mp4 -filter_complex " [0:v] scale=1920x1080 [background]; [1:v] scale=1440x810 [presentation]; [2:v] scale=480x360 [video]; [background][presentation] overlay=480:180 [temp1]; [temp1][video] overlay=0:0 [outv] " -map [outv] -map 2:a -c:v libx264 -r 25 -c:a aac -ar 44100 '.$finalpath.'/outv.mp4 ');

    //check if prerol is there
    $prerolpath = $finalpath."/Prerol.mp4";

    if (file_exists($prerolpath)) {

    //exec("/usr/bin/ffmpeg -y -i ".$finalpath."/Prerol.mp4 -c:v libx264 -r 15 -c:a aac ".$finalpath."/out-roll.mp4 ");
    exec("/usr/bin/ffmpeg -y -i ".$finalpath."/Prerol.mp4 -c:v libx264 -r 25 -c:a aac -ar 44100 -c:a aac ".$finalpath."/out-roll.mp4 ");

    exec("/usr/bin/ffmpeg -y -f concat -safe 0 -i ".$finalpath."/clist.txt -c copy ".$finalpath."/lecture.mp4 ");

    } else {
      $fn = $finalpath.'/outv.mp4';
      $newfn = $finalpath.'/lecture.mp4';
      copy($fn,$newfn);
    }

    echo "Lecture Recording completed...\n\n";
  }

  // type ==2 means only video

  if ($type == 2) {
    echo "Starting process to prepare video...\n\n";
    echo "Note: This page will take sometime to prepare your mp4 file so please wait and DO NOT CLOSE THE BROWSER";

    exec("/usr/bin/ffmpeg -y -i ".$finalpath."/presentation.mp4 -vf scale=1440x810 -max_muxing_queue_size 2048 ".$finalpath."/presentation-scaled.mp4 ");

    //exec('/usr/bin/ffmpeg -y -i '.$finalpath.'/LectureBackground.png -i '.$finalpath.'/presentation-scaled.mp4 -i '.$finalpath.'/webcams.mp4 -filter_complex " [0:v] scale=1920x1080 [background]; [1:v] scale=1440x810 [presentation]; [2:v] scale=480x360 [video]; [background][presentation] overlay=480:180 [temp1]; [temp1][video] overlay=0:0 [outv] " -map [outv] -map 2:a -c:v libx264 -r 15 '.$finalpath.'/outv.mp4 ');
    exec('/usr/bin/ffmpeg -y -i '.$finalpath.'/LectureBackground.png -i '.$finalpath.'/webcams.mp4 -filter_complex " [0:v] scale=1280x720 [background]; [1:v] scale=800x600 [video]; [background][video] overlay=400:90 [outv] " -map [outv] -map 1:a -c:v libx264 -r 25 -c:a aac -ar 44100 '.$finalpath.'/outv.mp4 ');

    //check if prerol is there
    $prerolpath = $finalpath."/Prerol.mp4";

    if (file_exists($prerolpath)) {

    //exec("/usr/bin/ffmpeg -y -i ".$finalpath."/Prerol.mp4 -c:v libx264 -r 15 -c:a aac ".$finalpath."/out-roll.mp4 ");
    exec("/usr/bin/ffmpeg -y -i ".$finalpath."/Prerol.mp4 -vf scale=1280x720 -c:v libx264 -r 25 -c:a aac -ar 44100 ".$finalpath."/out-roll.mp4 ");

    exec("/usr/bin/ffmpeg -y -f concat -safe 0 -i ".$finalpath."/clist.txt -c copy ".$finalpath."/lecture.mp4 ");

    } else {
      $fn = $finalpath.'/outv.mp4';
      $newfn = $finalpath.'/lecture.mp4';
      copy($fn,$newfn);
    }

    echo "Lecture Recording completed...\n\n";
  }

//$type ==3 means only presentation

  if ($type == 3) {
    echo "Starting process to prepare video...\n\n";
    echo "Note: This page will take sometime to prepare your mp4 file so please wait and DO NOT CLOSE THE BROWSER";

    exec("/usr/bin/ffmpeg -y -i ".$finalpath."/presentation.mp4 -vf scale=1440x810 -max_muxing_queue_size 2048 ".$finalpath."/presentation-scaled.mp4 ");

    //exec('/usr/bin/ffmpeg -y -i '.$finalpath.'/LectureBackground.png -i '.$finalpath.'/presentation-scaled.mp4 -i '.$finalpath.'/webcams.mp4 -filter_complex " [0:v] scale=1920x1080 [background]; [1:v] scale=1440x810 [presentation]; [2:v] scale=480x360 [video]; [background][presentation] overlay=480:180 [temp1]; [temp1][video] overlay=0:0 [outv] " -map [outv] -map 2:a -c:v libx264 -r 15 '.$finalpath.'/outv.mp4 ');
    exec('/usr/bin/ffmpeg -y -i '.$finalpath.'/LectureBackground.png -i '.$finalpath.'/presentation-scaled.mp4 -i '.$finalpath.'/webcams.mp4 -filter_complex " [0:v] scale=1920x1080 [background]; [1:v] scale=1600x900 [presentation]; [background][presentation] overlay=160:90 [outv] " -map [outv] -map 2:a -c:v libx264 -r 25 -c:a aac -ar 44100 '.$finalpath.'/outv.mp4 ');

    //check if prerol is there
    $prerolpath = $finalpath."/Prerol.mp4";

    if (file_exists($prerolpath)) {

    //exec("/usr/bin/ffmpeg -y -i ".$finalpath."/Prerol.mp4 -c:v libx264 -r 15 -c:a aac ".$finalpath."/out-roll.mp4 ");
    exec("/usr/bin/ffmpeg -y -i ".$finalpath."/Prerol.mp4 -c:v libx264 -r 25 -c:a aac -ar 44100 ".$finalpath."/out-roll.mp4 ");

    exec("/usr/bin/ffmpeg -y -f concat -safe 0 -i ".$finalpath."/clist.txt -c copy ".$finalpath."/lecture.mp4 ");

    } else {
      $fn = $finalpath.'/outv.mp4';
      $newfn = $finalpath.'/lecture.mp4';
      copy($fn,$newfn);
    }

    echo "Lecture Recording completed...\n\n";
  }


}
