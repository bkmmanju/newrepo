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
$cmid = required_param('cmid',PARAM_RAW);
$flag = required_param('flag',PARAM_RAW);
$record = $DB->get_record('bigbluebutton_publish',array('cmid'=>$cmid));
$upduser = new stdClass();
$upduser->id = $record->id;
$upduser->cmid = $record->cmid;
$upduser->plublishdate = time();
$upduser->publishflag = 0;
$upduser->meetingid = $record->meetingid;
$upduser->filesize = '';
$res = $DB->update_record('bigbluebutton_publish', $upduser);
if($res){
	redirect(new moodle_url('/mod/bigbluebuttonbn/bbb_publishreport.php'));
}


