<?php

/**
 * Script de définition des constantes
 *
 * @version $Id$
 * @copyright 2006
 */

	define("SESS_USER","utilisateur_session_active",true);
	define("SESS_HOTEL","hotel_session_active",true);
	define("SESS_GRP","groupe_session_active",true);
	
	define("DIR_HTTP","http://ghotel.gestion-hotel.com/",true);
	define("DIR_HTTP_SITE","http://www.gestion-hotel.com/",true);
	
	define("DIR_SCRIPTS",DIR_HTTP."scripts/",true);
	
	define("VERSION","RC 1.0",true);
	define("AUTEUR","Alexis ALGOUD - Maxime KOHLHAAS",true);
	define("ABOUT","A propos de GHOTEL...",true);
	
	define("MONEY", " &euro;", true);
	
	define("DIR_MODEL","../model/",true);
	define("DIR_MODEL_USER",DIR_MODEL."user/",true);
	
?>