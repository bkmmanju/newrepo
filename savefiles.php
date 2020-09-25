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
// $publishedrecs = $DB->get_records('bigbluebutton_publish',array('publishflag'=>0));
$publishedrecs = $DB->get_records_sql("SELECT * FROM {bigbluebutton_publish} WHERE publishflag = 0 ORDER BY id ASC LIMIT 2");

if(!empty($publishedrecs)){
	foreach ($publishedrecs as $key=> $rec) {
	//check for the file existance in lecturebackground folder.
		$filecheck = $CFG->dirroot.'/mod/bigbluebuttonbn/lecturebackground/'.$rec->cmid.'.png';

	//Get the complete course module from cmid.
		$recobject = $DB->get_record('course_modules',array('id'=>$rec->cmid));
				// sometimes teacher may delete a course module at later point ; Mihir 25 Sep 2020
		if (empty($recobject)) {
			continue;
		}
		$context = context_module::instance($recobject->id);
		$contextid = $context->id;
		$bigbluebutton = $DB->get_record('bigbluebuttonbn',array('id'=>$recobject->instance));

		if (!file_exists($filecheck)) {
	//Lecture background.
			$LectureBackgroundlink = mod_bigbluebuttonbn_image($bigbluebutton->backgroundimage,'backgroundimage',$contextid);
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
				$tempfile = $CFG->dirroot.'/mod/bigbluebuttonbn/lecturebackground/'.$rec->cmid.'.png';
				$fp = fopen($tempfile, 'w+');
				fputs($fp, $contents);
				fclose($fp);
			}else{
				$syscontext = context_system::instance();
				$defaultback = $fs->get_area_files($syscontext->id, 'mod_bigbluebuttonbn', 'defaultbackground', 0,
					'itemid, filepath, filename', false);
				foreach ($defaultback as $dfkey => $dfvalue) {
					$defcontents = $dfvalue->get_content();
					$deftempfile = $CFG->dirroot.'/mod/bigbluebuttonbn/lecturebackground/'.$rec->cmid.'.png';
					$deffp = fopen($deftempfile, 'w+');
					fputs($deffp, $defcontents);
					fclose($deffp);
				}
			}
		}

		//getting preroll video.
		$prerollvid = $CFG->dirroot.'/mod/bigbluebuttonbn/lecturebackground/Prerol.mp4';
		if(!file_exists($prerollvid)){
			$syscontext = context_system::instance();
			$prerol = $fs->get_area_files($syscontext->id, 'mod_bigbluebuttonbn', 'pre_roll', 0,
				'itemid, filepath, filename', false);
			foreach ($prerol as $prekey => $prevalue) {
				$precontents = $prevalue->get_content();
				$pretempfile = $CFG->dirroot.'/mod/bigbluebuttonbn/lecturebackground/Prerol.mp4';
				$prefp = fopen($pretempfile, 'w+');
				fputs($prefp, $precontents);
				fclose($prefp);
			}
		}
		//if file already exists then ready to go to the curl
		//Getting the publish url from the setting page.
		$publishurl = $DB->get_record_sql("SELECT * FROM {config} WHERE name = 'mod_bigbluebuttonbnpublish_url'"); 
		//Mihir changed issue with quote
		$url = $publishurl->value.'/files.php';
		$type = $bigbluebutton->recordingstyle;
		$recordingid = $rec->meetingid;
		$cmid = $rec->cmid;//// get the value
		$moodleurl = $CFG->wwwroot;//// get the value

		$data = array (
			'type' => $type,
			'recordingid' => $recordingid,
			'cmid' => $cmid,
			'moodleurl' => $moodleurl
		);
		$params = '';
		foreach($data as $key=>$value)
			$params .= $key.'='.$value.'&';
		$params = trim($params, '&');
		$remoteurl = $url.'?'.$params;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $remoteurl); 
		    //Remote Location URL	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	    //Return data instead printing directly in Browser.
		$output = curl_exec($ch);
		curl_error($ch);
		curl_close($ch);
			//Getting the file size.
		$file = $publishurl->value.'/recording/'.$recordingid.'/lecture.mp4';
		    //Manju:check if the file is exits of ready.23/09/2020.
		$handle = @fopen($file, 'r');
		if($handle){
			$ch1 = curl_init($file);
			curl_setopt($ch1, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch1, CURLOPT_HEADER, TRUE);
			curl_setopt($ch1, CURLOPT_NOBODY, TRUE);
			$data = curl_exec($ch1);
			$size = curl_getinfo($ch1, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
			curl_close($ch1);

			$filesize = isa_convert_bytes_to_specified($size, 'M');
			$upduser = new stdClass();
			$upduser->id = $rec->id;
			$upduser->plublishdate = time();
			$upduser->publishflag = 1;
			$upduser->meetingid = $rec->meetingid;
			$upduser->filesize = $filesize.' MB';
			$DB->update_record('bigbluebutton_publish', $upduser);
		}
	}
}
