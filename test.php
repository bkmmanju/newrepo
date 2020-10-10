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
global $CFG,$DB,$PAGE;
$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/mod/bigbluebuttonbn/js/custom.js'));
echo $OUTPUT->header();
//Getting the server url from the setting page.
$meetingid="1234567890";
$serverurl = $DB->get_field('config','value',array('name'=>'bigbluebuttonbn_server_url'));
$reqserverurl = str_replace('/bigbluebutton/', '', $serverurl);
//webcams url creation from the meeting ID.
$webmurl = $reqserverurl.'/download/presentation/'.$meetingid.'/video/webcams.mp4';
$html='';
$html.='<input type="text" value="Welcome to CodexWorld" id="textInput">';
$html.="<button name='copy' value='textInput' onclick='f1(this)'>Copy</button>";
echo $html;
echo $OUTPUT->footer();