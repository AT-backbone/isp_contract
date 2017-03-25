<?php
/* Copyright (C) 2006 Laurent Destailleur  <eldy@users.sourceforge.net>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * or see http://www.gnu.org/
 */

/**
 * \file       htdocs/customerservices/lib/customerservices.lib.php
 */

/**
 * Initialize the array of tabs for customer invoice
 *
 * @return	array					Array of head tabs
 */
function customerservices_prepare_head()
{
	global $langs, $conf, $user;
	
	// Onglets
	$head=array();
	$h = 0;

	$head[$h][0] = DOL_URL_ROOT."/customerservices/admin/customerservices.php";
	$head[$h][1] = $langs->trans("CustomerServiceGlobalParameters");
	$head[$h][2] = 'custserv';
	$h++;

	$head[$h][0] = DOL_URL_ROOT."/customerservices/admin/customerservices.php?ISP=1";
	$head[$h][1] = $langs->trans("ISPconfigServer");
	$head[$h][2] = 'ISPcServer';
	$h++;

	return $head;
}




?>	