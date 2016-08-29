<?php
/**universal
 * Fichier contenant le texte du site en fran�ais
 * 
 * @author Thomas Pequet
 * @version 1.0
 */

$titre1		= "Statistiques d'utilisation de CUPS"; 
 
// Nom des jours de la semaine
$nomJours = array(
			  	"0" => "Dimanche",
			  	"1" => "Lundi",
			  	"2" => "Mardi",
			  	"3" => "Mercredi",
			  	"4" => "Jeudi",
			  	"5" => "Vendredi",
			  	"6" => "Samedi"																			
			  );
// Nom des mois
$nomMois = array(
			  	"01" => "Janvier",
			  	"02" => "F�vrier",
			  	"03" => "Mars",
			  	"04" => "Avril",
			  	"05" => "Mai",
			  	"06" => "Juin",
			  	"07" => "Juillet",									
			  	"08" => "Ao�t",
			  	"09" => "Septembre",
			  	"10" => "Octobre",
			  	"11" => "Novembre",
			  	"12" => "D�cembre"
			  ); 
 
// Texte de la page "index"
if ($page=="1") {
	$ficIndex1 		= "Nb pages par utilisateurs";
	$ficIndex2 		= "Nb pages par imprimantes";
	$ficIndex3 		= "Nb pages par jours";
	$ficIndex4 		= "Nb pages par heures";
	$ficIndex5 		= "Statistiques Utilisateur: ##utilisateur##";
	$ficIndex6 		= "Statistiques Imprimante: ##imprimante##";
	$ficIndex7 		= "Statistiques Service: ##service##";
	$ficIndex8 		= "Voir les infos de l'imprimante CUPS";
	$ficIndex9 		= "Nb pages imprim�es";
	$ficIndex10		= "Co�t";	
	$ficIndex11 	= "Nb pages par services";
	$ficIndex12 	= "Du ##date1## au ##date2##";
	$ficIndex13 	= "Liste des services";
	$ficIndex14		= "Nb pages par mois";
	$ficIndex15		= "Total pages imprim�es + co�t";
	$ficIndex16 	= "Utilisateur";
	$ficIndex17 	= "% Pages";
	$ficIndex18 	= "Nb pages";
	$ficIndex19 	= "Imprimante";
	$ficIndex20		= "Co�t en �";
	$ficIndex21		= "Co�t page A4 en �";
	$ficIndex22		= "Recharger les fichiers de log";
	$ficIndex23		= "P�riode � consulter";
	$ficIndex24		= "Valider";
	$ficIndex25		= "Modifier";
} 
?>