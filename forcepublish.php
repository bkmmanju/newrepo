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
global $DB, $USER, $SESSION,$CFG,$OUTPUT,$PAGE;
$recordid = optional_param('recordid', '', PARAM_TEXT);
$cmid = optional_param('cmid', '', PARAM_TEXT);
require_login(true);
$cm = get_coursemodule_from_id('bigbluebuttonbn', $cmid);
$contextmodule = context_module::instance($cm->id);
$PAGE->set_context($contextmodule);
$PAGE->set_cm($cm);
$PAGE->set_pagelayout('admin');
$PAGE->set_url($CFG->wwwroot . '/mod/bigbluebuttonbn/forcepublish.php',array('cmid'=>$cmid,'recordid'=>$recordid));
$title = get_string('publishing','mod_bigbluebuttonbn');
$PAGE->navbar->add($title);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->requires->jquery();
echo $OUTPUT->header();
$retlink = $CFG->wwwroot.'/mod/bigbluebuttonbn/view.php?id='.$cmid;
$html='';
$html.=html_writer::start_div('text-center');
$html.=get_string('forcepublishmessage','mod_bigbluebuttonbn');
$html.=html_writer::end_div();
$html.=html_writer::start_div('text-center');
$html.=html_writer::start_tag('a',array('href'=>$retlink));
$html.=get_string('continue','mod_bigbluebuttonbn');
$html.=html_writer::end_tag('a');
$html.=html_writer::end_div();
echo $html;

echo $OUTPUT->footer();

$rec = $DB->get_record_sql("SELECT * FROM {bigbluebutton_publish} WHERE meetingid = '$recordid'");
if(!empty($rec)){
		//check for the file existance in lecturebackground folder.
	$filecheck = $CFG->dirroot.'/mod/bigbluebuttonbn/lecturebackground/'.$rec->cmid.'.png';

	//Get the complete course module from cmid.
	$recobject = $DB->get_record('course_modules',array('id'=>$rec->cmid));
	$context = context_module::instance($recobject->id);
	$contextid = $context->id;
	$bigbluebutton = $DB->get_record('bigbluebuttonbn',array('id'=>$recobject->instance));

	if (file_exists($filecheck)) {
		unlink($filecheck);
	}

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
// if file already exists then ready to go to the curl
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
		curl_setopt($ch, CURLOPT_URL, $remoteurl); //Remote Location URL
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($ch);
		print_object($output);die;

		curl_error($ch);
		curl_close($ch);

		// Getting the file size.
		$file = $publishurl->value.'/recording/'.$recordingid.'/lecture.mp4';

		//Check the file. if the file is present then proceed.
		// Open file
		$handle = @fopen($file, 'r');
		$filesize='';
		if($handle){
			//Get the file size in bytes.
			$ch1 = curl_init($file);
			curl_setopt($ch1, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch1, CURLOPT_HEADER, TRUE);
			curl_setopt($ch1, CURLOPT_NOBODY, TRUE);
			$data = curl_exec($ch1);
			$size = curl_getinfo($ch1, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
			curl_close($ch1);
			$filesize = isa_convert_bytes_to_specified($size, 'M');
		}
		$upduser = new stdClass();
		$upduser->id = $rec->id;
		$upduser->plublishdate = time();
		$upduser->publishflag = 1;
		$upduser->meetingid = $rec->meetingid;
		$upduser->filesize = $filesize.' MB';
		$result = $DB->update_record('bigbluebutton_publish', $upduser);
	}




