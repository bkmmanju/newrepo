<?php
// This file is part of the Certificate module for Moodle - http://moodle.org/
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
 * Handles uploading files
 *
 * @package    mod_bigbluebuttonbn
 * @copyright  Manjunath<maanjunath@elearn10.com>
 * @copyright  Dhruv Infoline Pvt Ltd <lmsofindia.com>
 * @license    http://www.lmsofindia.com 2017 or later
 */
require_once('../../config.php');
require_once('lib.php');
$recordid = required_param('recordid', PARAM_RAW);
$cmid = required_param('cmid', PARAM_INT);
global $DB, $USER, $SESSION,$CFG;  
require_login(true);
$cm = get_coursemodule_from_id('bigbluebuttonbn', $cmid);
$contextmodule = context_module::instance($cm->id);
$PAGE->set_context($contextmodule);
$PAGE->set_pagelayout('admin');
$PAGE->set_cm($cm);
$PAGE->set_url($CFG->wwwroot . '/mod/bigbluebutton/attendance.php');
$title = get_string('attendance','mod_bigbluebuttonbn');
$PAGE->navbar->add($title);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/mod/bigbluebuttonbn/js/jquery.dataTables.min.js'),true);
$PAGE->requires->css(new moodle_url($CFG->wwwroot.'/mod/bigbluebuttonbn/css/jquery.dataTables.min.css'),true);
$PAGE->requires->css(new moodle_url($CFG->wwwroot.'/mod/bigbluebuttonbn/css/buttons.dataTables.min.css'),true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/mod/bigbluebuttonbn/js/dataTables.buttons.min.js'),true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/mod/bigbluebuttonbn/js/buttons.print.min.js'),true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/mod/bigbluebuttonbn/js/buttons.colVis.min.js'),true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/mod/bigbluebuttonbn/js/buttons.flash.min.js'),true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/mod/bigbluebuttonbn/js/buttons.html5.min.js'),true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/mod/bigbluebuttonbn/js/jszip.min.js'),true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/mod/bigbluebuttonbn/js/pdfmake.min.js'),true);
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/mod/bigbluebuttonbn/js/vfs_fonts.js'),true);

echo $OUTPUT->header();
//get all the records related to the recordid.
$records = $DB->get_records("bigbluebutton_attendance",array('recordingid'=>$recordid));
$attendancetable = new \html_table();
$attendancetable->id = "attendance" ;
$attendancetable->head = array(get_string('serialno','mod_bigbluebuttonbn'),
	get_string('view_recording_name','mod_bigbluebuttonbn'),
	get_string('starttime','mod_bigbluebuttonbn'),
	get_string('endtime','mod_bigbluebuttonbn'),
	get_string('duration','mod_bigbluebuttonbn'));

//Create table here.
if(!empty($records)){
	$counter = 1;
	foreach ($records as $record) {
		$user = $DB->get_record('user',array('id'=>$record->userid));
		$fullname = fullname($user);
		// Declare and define two dates 
		$date1 = strtotime(date("Y-m-d G:i:s",$record->jointime));  
		$date2 = strtotime(date("Y-m-d G:i:s",$record->lefttime));
		// Formulate the Difference between two dates 
		$diff = abs($date2 - $date1);
		$years = floor($diff / (365*60*60*24));  
		$months = floor(($diff - $years * 365*60*60*24) 
			/ (30*60*60*24));  
		$days = floor(($diff - $years * 365*60*60*24 -  
			$months*30*60*60*24)/ (60*60*24)); 
		$hours = floor(($diff - $years * 365*60*60*24  
			- $months*30*60*60*24 - $days*60*60*24) 
		/ (60*60));
		$minutes = floor(($diff - $years * 365*60*60*24  
			- $months*30*60*60*24 - $days*60*60*24  
			- $hours*60*60)/ 60); 

		$duration = $minutes.' Mins';
		$attendancetable->data[] = array($counter,$fullname,date("Y-m-d G:i:s",$record->jointime),date("Y-m-d G:i:s",$record->lefttime),$duration);
		$counter++;
	}
}
$html='';
$html.= html_writer::table($attendancetable);
$html.="<script>$(document).ready( function () {
	$('#attendance').DataTable({
		dom: 'lBfrtip',
		buttons: [
		'excel', 'pdf'
		]
		});
	} );</script>";
	echo $html;
	echo $OUTPUT->footer();