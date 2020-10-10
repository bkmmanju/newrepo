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
require_login(true);
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_url($CFG->wwwroot . '/mod/bigbluebuttonbn/bbb_publishreport.php');
$title = get_string('publishreport', 'mod_bigbluebuttonbn');
//$PAGE->navbar->ignore_active();
$PAGE->navbar->add($title);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->requires->jquery();
echo $OUTPUT->header();

$table  = new \html_table();
$table->id = 'usertable';
$table->head = array(get_string('serial', 'mod_bigbluebuttonbn'),
	get_string('coursename', 'mod_bigbluebuttonbn'),
	get_string('cmname', 'mod_bigbluebuttonbn'),
	get_string('publishstatus', 'mod_bigbluebuttonbn'),
	get_string('republish', 'mod_bigbluebuttonbn')
);
//Get all the data related to table.
$publishedrecs = $DB->get_records_sql("SELECT * FROM {bigbluebutton_publish}");
if(!empty($publishedrecs)){
	$counter = 1;
	foreach ($publishedrecs as $pubkey => $pubvalue) {
		//Getting the course name.
		$recobject = $DB->get_record('course_modules',array('id'=>$pubvalue->cmid));
		$coursename = $DB->get_field('course','fullname',array('id'=>$recobject->course));

		//Getting the course module name.
		$modname = $DB->get_field('bigbluebuttonbn','name',array('id'=>$recobject->instance));

		
		//Publish status.
		if($pubvalue->publishflag == 1){
			$status = get_string('republished', 'mod_bigbluebuttonbn');
			$republishlink = $CFG->wwwroot.'/mod/bigbluebuttonbn/republish.php?cmid='.$pubvalue->cmid.'&flag=1';
			$markforrepublish = '<a href="'.$republishlink.'" class="action-icon btn-action text-truncate" style="font-size: 25px;"><i class="fa fa-square-o" aria-hidden="true"></i></a>';
		}else if($pubvalue->publishflag == 0){
			$status = get_string('unrepublished', 'mod_bigbluebuttonbn');
			$republishlink = $CFG->wwwroot.'/mod/bigbluebuttonbn/republish.php?cmid='.$pubvalue->cmid.'&flag=0';
			$markforrepublish = '<a href="'.$republishlink.'" class="action-icon btn-action text-truncate" style="font-size: 25px;"><i class="fa fa-check-square-o" aria-hidden="true"></i></a>';
		}

		//Mark for republish.

		$table->data[] = array($counter, $coursename, $modname,$status,$markforrepublish);
		$counter++;
	}
	echo html_writer::table($table);
}
echo $OUTPUT->footer();
