<?php

define('_IF_ANONYMOUS_NOT', 0);
define('_IF_ANONYMOUS_SENDER', 1);
define('_IF_ANONYMOUS_TARGET', 2);
define('_IF_ANONYMOUS_SENDER_TARGET', 3);

$Tarray_provincias= array(
	"HB" => "La Habana",
	"PR" => "Pinar del Rio",
	"AR" => "Artemisa",
	"MY" => "Mayabeque",
	"MT" => "Matanzas",
	"VC" => "Villa Clara",
	"CI" => "Cienfuegos",
	"SS" => "Sancti Spíritus",
	"CG" => "Ciego de Ávila",
	"CM" => "Camagúey",
	"LT" => "Las Tunas",
	"HO" => "Holguín",
	"GR" => "Granma",
	"SC" => "Santiago de Cuba",
	"GT" => "Guantánamo",
	"IS" => "Isla de la Juventud"
);
	
/**
 * Liatado de municipios. Se considera solo Cuba
 */
$Tarray_municipios= array(
	"PR" => array("Pinar del Rio", array(
		"CON" => "Consolación del Sur",
		"GUA" => "Guane",
		"LAP" => "La Palma",
		"LOP" => "Los Palacios",
		"MAN" => "Mantua",
		"MIN" => "Minas de Matahambre",
		"PNR" => "Pinar del Río",
		"SJM" => "San Juan y Martínez",
		"SAL" => "San Luis",
		"SAN" => "Sandino",
		"VIN" => "Viñales"
	)),

	"AR" => array("Artemisa", array(
		"ART" => "Artemisa",
		"ALQ" => "Alquízar",
		"BAU" => "Bauta",
		"CAI" => "Caimito",
		"GUA" => "Guanajay",
		"GUI" => "Guira de Melena",
		"MAR" => "Mariel",
		"SAB" => "San Antonio de los Baños",
		"BAH" => "Bahía Honda",
		"SNC" => "San Cristóbal",
		"CAN" => "Candelaria"
	)),

	"MY" => array("Mayabeque", array(
		"SJL" => "San José de las Lajas",
		"BAT" => "Batabanó",
		"BEJ" => "Bejuca",
		"GUN" => "Guines",
		"JAR" => "Jaruco",
		"MAD" => "Madruga",
		"MDS" => "Melena del Sur",
		"NUP" => "Nueva Paz",
		"QUI" => "Quivicán",
		"SNB" => "San Nicolís de Bari",
		"SCN" => "Santa Cruz del Norte"
	)),

	"HB" => array("La Habana", array(
		"PLZ" => "Plaza",
		"ARN" => "Arroyo Naranjo",
		"BOY" => "Boyeros",
		"CNH" => "Centro Habana",
		"CRR" => "Cerro",
		"CTR" => "Cotorro",
		"DOC" => "Diez de Octubre",
		"GNB" => "Guanabacoa",
		"HDE" => "Habana del Este",
		"HBV" => "Habana Vieja",
		"LLS" => "La Lisa",
		"MRN" => "Marianao",
		"PLY" => "Playa",
		"RGL" => "Regla",
		"SMP" => "San Miguel del Padrón"
	)),

	"MT" => array("Matanzas", array(
		"MAT" => "Matanzas",
		"CAL" => "Calimete",
		"CAR" => "Cárdenas",
		"CIE" => "Ciénaga de Zapata",
		"COL" => "Colón",
		"JUG" => "Jaguey Grande",
		"JOV" => "Jovellanos",
		"LIM" => "Limona",
		"LAR" => "Los Arabos",
		"MAR" => "Martí",
		"PBE" => "Pedro Betancourt",
		"PIC" => "Perico",
		"UNR" => "Unión de Reyes",
		"VAR" => "Varadero"
	)),

	"VC" => array("Villa Clara", array(
		"STC" => "Santa Clara",
		"CAB" => "Caibarién",
		"CMJ" => "Camajuany",
		"CIF" => "Cifuentes",
		"CRR" => "Corralillo",
		"ENC" => "Encrucijada",
		"MNC" => "Manicaragua",
		"PLT" => "Placetas",
		"QUE" => "Quemado de Guines",
		"RAN" => "Ranchuelo",
		"REM" => "Remedios",
		"SLG" => "Sagua la Grande",
		"STD" => "Santo Domingo"
	)),

	"CI" => array("Cienfuegos", array(
		"CIE" => "Cienfuegos",
		"ABR" => "Abreus",
		"ADP" => "Aguada de Pasajeros",
		"CRU" => "Cruces",
		"CMY" => "Cumanayagua",
		"PMR" => "Palmira",
		"ROD" => "Rodas",
		"SIL" => "Santa Isabel de las Lajas"
	)),

	"SS" => array("Sancti Spíritus", array(
		"SST" => "Sancti Spíritus",
		"CBG" => "Cabaiguan",
		"FOM" => "Fomento",
		"JAT" => "Jatibonico",
		"LSI" => "La Sierpe",
		"TAG" => "Taguasco",
		"TRI" => "Trinidad",
		"YAG" => "Yaguajay"
	)),

	"CG" => array("Ciego de Ávila", array(
		"CAV" => "Ciego de Ávila",
		"CIR" => "Ciro Redondo",
		"BAR" => "Baraguá",
		"BOV" => "Bolivia",
		"CHA" => "Chambas",
		"FLO" => "Florencia",
		"MAJ" => "Majagua",
		"MOR" => "Morón",
		"PDE" => "Primero de Enero",
		"VEN" => "Venezuela"
	)),

	"CM" => array("Camaguey", array(
		"CAM" => "Camaguey",
		"CMC" => "Carlos Manuel de Céspedes",
		"ESM" => "Esmeralda",
		"FLO" => "Florida",
		"GMO" => "Guaimaro",
		"JIM" => "Jimaguayú",
		"MIN" => "Minas",
		"NAJ" => "Najasa",
		"NUE" => "Nuevitas",
		"SCS" => "Santa Cruz del Sur",
		"SIB" => "Sibanicú",
		"SCB" => "Sierra de Cubitas"
	)),
	
	"HO" => array("Holguín", array(
		"HOL" => "Holguín",
		"ANT" => "Antilla",
		"AMC" => "Báguanos",
		"CLB" => "Banes",
		"CAC" => "Cacocum",
		"CLX" => "Calixto García",
		"CUE" => "Cueto",
		"FKP" => "Frank País",
		"MAY" => "Mayari",
		"PPD" => "Moa",
		"RFY" => "Rafael Freyre",
		"SAG" => "Sagua de Tánamo",
		"URN" => "Urbano Noris"
	)),
	
	"LT" => array("Las Tunas", array(
		"LTU" => "Las Tunas",
		"AMC" => "Amancio Rodríguez",
		"CLB" => "Colombia",
		"JMN" => "Jesús Menéndez",
		"JBB" => "Jobabo",
		"MJB" => "Majibacoa",
		"MNT" => "Manatí",
		"PPD" => "Puerto Padre"
	)),

	"GR" => array("Granma", array(
		"BYM" => "Bayamo",
		"BTM" => "Bartolomé Masó",
		"BRR" => "Buey Arriba",
		"CCH" => "Campechuela",
		"CCR" => "Cauto Cristo",
		"GSA" => "Guisa",
		"JGN" => "Jiguaní",
		"MNZ" => "Manzanillo",
		"MEL" => "Media Luna",
		"NQR" => "Niquero",
		"PLN" => "Pilón",
		"RCT" => "Río Cauto",
		"YRA" => "Yara"
	)),

	"SC" => array("Santiago de Cuba", array(
		"STC" => "Santiago de Cuba",
		"CTM" => "Contramaestre",
		"GMA" => "Guamá",
		"JAM" => "Julio Antonio Mella",
		"PSN" => "Palma Soriano",
		"SLU" => "San Luis",
		"SGF" => "Segundo Frente",
		"SLM" => "Songo la Maya",
		"TRF" => "Tercer Frente"
	)),

	"GT" => array("Guantánamo", array(
		"GTM" => "Guantánamo",
		"COA" => "Baracoa",
		"CNR" => "Caimanera",
		"SAL" => "El Salvador",
		"IMI" => "Imías",
		"MAI" => "Maisí",
		"MNT" => "Manuel Tames",
		"NCT" => "Niceto Pérez",
		"SAS" => "San Antonio del Sur",
		"YAT" => "Yateras"
	)),

	"ISL" => array("ISL", array(
		"ISL" => "Isla de la Juventud"
	))
);
