<?php
// Update the GOV module database schema from version 0 to version 1
//
// Version 0: empty database
// Version 1: create the tables
//
// The script should assume that it can be interrupted at
// any point, and be able to continue by re-running the script.
// Fatal errors, however, should be allowed to throw exceptions,
// which will be caught by the framework.
// It shouldn't do anything that might take more than a few
// seconds, for systems with low timeout values.
//
// webtrees: Web based Family History software
// Copyright (C) 2011 webtrees development team.
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//
// $Id: db_schema_0_1.php 2013-11-09 18:37:40 Hermann Hartenthaler $

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

// Create all of the tables needed for this module
WT_DB::exec(
	"CREATE TABLE IF NOT EXISTS `##gov` (".
	" gov_id        INTEGER      NOT NULL,".
	" gov_parent_id INTEGER          NULL,".
	" gov_level     INTEGER          NULL,".
	" gov_place     VARCHAR(255)     NULL,".
	" gov_govid     VARCHAR(20)      NULL,".
	" PRIMARY KEY     (gov_id),".
	"         KEY ix1 (gov_govid),".
	"         KEY ix2 (gov_level),".
	"         KEY ix3 (gov_place),".
	"         KEY ix4 (gov_parent_id)".
	") COLLATE utf8_unicode_ci ENGINE=InnoDB"
);

// Update the version to indicate success
WT_Site::preference($schema_name, $next_version);
