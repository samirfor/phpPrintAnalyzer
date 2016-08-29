<?php
/**universal
 * Statistiques d'utilisation de CUPS (générales, par utilisateur, par imprimante ou par service):
 *  - Nb pages imprimées par utilisateurs
 *  - Nb pages imprimées par services
 *  - Nb pages imprimées par imprimantes
 *  - Nb pages imprimées par jours
 *  - Nb pages imprimées par mois
 *  - Nb pages imprimées par heures
 *  - Total pages imprimées + coût 
 * 
 * @author Thomas Pequet
 * @version	1.0 
 */

// Répertoire par rapport à la racine du site
$rep_par_rapport_racine = "";

// Identifiant de la page
$page = "1";

// Temps limite d'execution du script en secondes
@set_time_limit(300);

// Démarrer la session
@session_start();

if (phpversion()>="4.1.0")
	$REGISTER_GLOBALS = false;
else
	$REGISTER_GLOBALS = true;

if (!$REGISTER_GLOBALS)
{
	// Variables de session
	
	if (isset($_SESSION["utilisateurs"]))
		$utilisateurs 	= $_SESSION["utilisateurs"];
	if (isset($_SESSION["imprimantes"]))
		$imprimantes 		= $_SESSION["imprimantes"];
	if (isset($_SESSION["jours"]))
		$jours					= $_SESSION["jours"];
	if (isset($_SESSION["heures"]))
		$heures 				= $_SESSION["heures"];
	if (isset($_SESSION["langue"]))
		$langue 				= $_SESSION["langue"];
	
	// Valeurs passés en paramètres
	if (isset($_GET["action"]))
		$action 				= $_GET["action"];
	if (isset($_GET["imprimante"]))
		$imprimante			= $_GET["imprimante"];
	if (isset($_GET["utilisateur"]))
		$utilisateur		= $_GET["utilisateur"];
	if (isset($_GET["service"]))
		$service 				= $_GET["service"];
	if (isset($_GET["graphique"]))
		$graphique			= $_GET["graphique"];
	if (isset($_GET["mktimeMin"]))
		$mktimeMin			= $_GET["mktimeMin"];
	if (isset($_GET["mktimeMax"]))
		$mktimeMax			= $_GET["mktimeMax"];
}
else
{
	// Variables de session
	if (!session_is_registered("utilisateurs"))
		session_register("utilisateurs");
	if (!session_is_registered("imprimantes"))
		session_register("imprimantes");
	if (!session_is_registered("jours"))
		session_register("jours");
	if (!session_is_registered("heures"))
		session_register("heures");
}

// Fichiers à inclure
include_once($rep_par_rapport_racine."inc/fonctions.inc.php");
include_once($rep_par_rapport_racine."inc/config.inc.php");
include($rep_par_rapport_racine."lang/".$langue.".inc.php");
include($rep_par_rapport_racine."inc/img.inc.php");

// Constantes de la page

// Variables de la page
if (isset($utilisateur) && $tabUserRegroupe[$utilisateur])
	$utilisateur = $tabUserRegroupe[$utilisateur];
if (isset($imprimante) && $tabPrinterRegroupe[$imprimante])
	$imprimante = $tabPrinterRegroupe[$imprimante];
$nbPages 	= 0;
$cout 		= 0;

// Fonctions de la page
function charger_page_log()
{
	global $tab_fic_page_log, $tabUserRegroupe, $tabPrinterRegroupe, $tabPrinterIgnore, $utilisateurs, $imprimantes, $jours, $heures;
	global $REGISTER_GLOBALS, $mktimeMin, $mktimeMax;

	function analyse_ligne($ligne)
	{
		global $tabUserRegroupe, $tabPrinterRegroupe, $tabPrinterIgnore, $utilisateurs, $imprimantes, $jours, $heures;
		global $mktimeMin, $mktimeMax;
		
		if ($ligne!="")
		{				
			// Découpage de la ligne
			$tabLigneTmp = split(" ", $ligne);

			$tabLigneTmp[1] = strtolower($tabLigneTmp[1]);

			// Vérification de l'utilisateur
			if (isset($tabUserRegroupe[$tabLigneTmp[1]]))
				$tabLigneTmp[1] = strtolower($tabUserRegroupe[$tabLigneTmp[1]]);	
			// Vérification de l'imprimante
			if (isset($tabPrinterRegroupe[$tabLigneTmp[0]]))
				$tabLigneTmp[0] = $tabPrinterRegroupe[$tabLigneTmp[0]];	

			// Vérifier que ce n'est pas une imprimante ignorées
			if (!isset($tabPrinterIgnore[$tabLigneTmp[0]]))
			{
				$jourTmp = ereg_replace("^\[([0-9]{2})/([A-Za-z]{3})/([0-9]{4}):[0-9]{2}:[0-9]{2}:[0-9]{2}$", "\\3-\\2-\\1", $tabLigneTmp[3]);;
				$jourTmp = str_replace(array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"), array("01","02","03","04","05","06","07","08","09","10","11","12"), $jourTmp);
		
				if (isset($mktimeMin) && isset($mktimeMax))
				{
					list($annee, $mois, $jour) = split("-",$jourTmp);
					$mktimeTmp = mktime(12,0,0,$mois,$jour,$annee);
					//echo $mktimeMin."<=".$mktimeTmp."<=".$mktimeMax."<BR>";
					if ($mktimeTmp>=$mktimeMin && $mktimeTmp<=$mktimeMax)
						$ok = true;
					else
						$ok = false;
				}
				else
					$ok = true;
				
				if ($ok)
				{	
					// Nombre d'impression de pages par imprimantes 
					if (isset($imprimantes[$tabLigneTmp[0]]))
					{
						$imprimantes[$tabLigneTmp[0]]["total"] += $tabLigneTmp[6];
						if (isset($imprimantes[$tabLigneTmp[0]]["utilisateurs"][$tabLigneTmp[1]]))
						{
							$imprimantes[$tabLigneTmp[0]]["utilisateurs"][$tabLigneTmp[1]] += $tabLigneTmp[6];
						}
						else
						{
							$imprimantes[$tabLigneTmp[0]]["utilisateurs"][$tabLigneTmp[1]] = $tabLigneTmp[6];
						}
					}
					else
					{
						$imprimantes[$tabLigneTmp[0]]["total"] = $tabLigneTmp[6];
						$imprimantes[$tabLigneTmp[0]]["utilisateurs"] = array(
																			$tabLigneTmp[1] => $tabLigneTmp[6], 
																		);
					}	
					
					// Nombre d'impression de pages par utilisateurs 
					if (isset($utilisateurs[$tabLigneTmp[1]]))
					{
						$utilisateurs[$tabLigneTmp[1]]["total"] += $tabLigneTmp[6];
						if (isset($utilisateurs[$tabLigneTmp[1]]["imprimantes"][$tabLigneTmp[0]]))
						{
							$utilisateurs[$tabLigneTmp[1]]["imprimantes"][$tabLigneTmp[0]] += $tabLigneTmp[6];
						}
						else
						{
							$utilisateurs[$tabLigneTmp[1]]["imprimantes"][$tabLigneTmp[0]] = $tabLigneTmp[6];
						}
					}
					else
					{
						$utilisateurs[$tabLigneTmp[1]]["total"] = $tabLigneTmp[6];
						$utilisateurs[$tabLigneTmp[1]]["imprimantes"] = array(
																			$tabLigneTmp[0] => $tabLigneTmp[6], 
																		);
					}	
					
					// Nombre de pages par heures
					$heureTmp = ereg_replace("^\[[0-9]{2}/[A-Za-z]{3}/[0-9]{4}:([0-9]{2}):[0-9]{2}:[0-9]{2}$", "\\1", $tabLigneTmp[3])."h.";
					if (isset($heures[$heureTmp]))
					{
						$heures[$heureTmp]["total"] += $tabLigneTmp[6];
						if (isset($heures[$heureTmp]["imprimantes"][$tabLigneTmp[0]]))
						{
							$heures[$heureTmp]["imprimantes"][$tabLigneTmp[0]] += $tabLigneTmp[6];
						}
						else
						{
							$heures[$heureTmp]["imprimantes"][$tabLigneTmp[0]] = $tabLigneTmp[6];
						}
						if (isset($heures[$heureTmp]["utilisateurs"][$tabLigneTmp[1]]))
						{
							$heures[$heureTmp]["utilisateurs"][$tabLigneTmp[1]] += $tabLigneTmp[6];
						}
						else
						{
							$heures[$heureTmp]["utilisateurs"][$tabLigneTmp[1]] = $tabLigneTmp[6];
						}
					}
					else
					{
						$heures[$heureTmp]["total"] = $tabLigneTmp[6];
						$heures[$heureTmp]["imprimantes"] = array(
																$tabLigneTmp[0] => $tabLigneTmp[6], 
															);
						$heures[$heureTmp]["utilisateurs"] = array(
																$tabLigneTmp[1] => $tabLigneTmp[6], 
															);
					}
					
					// Nombre de pages par jours
					if (isset($jours[$jourTmp]))
					{
						$jours[$jourTmp]["total"] += $tabLigneTmp[6];
						if (isset($jours[$jourTmp]["imprimantes"][$tabLigneTmp[0]]))
						{
							$jours[$jourTmp]["imprimantes"][$tabLigneTmp[0]] += $tabLigneTmp[6];
						}
						else
						{
							$jours[$jourTmp]["imprimantes"][$tabLigneTmp[0]] = $tabLigneTmp[6];
						}
						if (isset($jours[$jourTmp]["utilisateurs"][$tabLigneTmp[1]]))
						{
							$jours[$jourTmp]["utilisateurs"][$tabLigneTmp[1]] += $tabLigneTmp[6];
						}
						else
						{
							$jours[$jourTmp]["utilisateurs"][$tabLigneTmp[1]] = $tabLigneTmp[6];
						}
					}
					else
					{
						$jours[$jourTmp]["total"] = $tabLigneTmp[6];
						$jours[$jourTmp]["imprimantes"] = array(
																$tabLigneTmp[0] => $tabLigneTmp[6], 
															);
						$jours[$jourTmp]["utilisateurs"] = array(
																$tabLigneTmp[1] => $tabLigneTmp[6], 
															);
					}				
				}
			}
		}
	}

	for ($i=0;$i<sizeof($tab_fic_page_log);$i++)
	{
		if (ereg("^pipe:",$tab_fic_page_log[$i]))
		{
			$fp = @popen(ereg_replace("^pipe:","",$tab_fic_page_log[$i]), "r");
			if ($fp)
			{
				while (!feof ($fp)) 
				{
					$ligne = trim(fgets($fp, 1024));
					
					analyse_ligne($ligne);
				}
				pclose($fp);
			}
		}
		else
		{
			// Ouverture du fichier
			$fp = @fopen($tab_fic_page_log[$i], "r");
			if ($fp)
			{
				while (!feof ($fp)) 
				{
	   			$ligne = trim(fgets($fp, 1024));
	   			
	   			analyse_ligne($ligne);
	    	}
				fclose($fp);		
			}
			
			clearstatcache();	
		}
	}
	
	// Tri des tableaux
	if (is_array($utilisateurs))
		uasort($utilisateurs, "tri_total");
	if (is_array($imprimantes))
		uasort($imprimantes, "tri_total");
	if (is_array($jours))	
		ksort($jours);
	if (is_array($heures))
		ksort($heures);
	
	if (!$REGISTER_GLOBALS)
	{
		$_SESSION["utilisateurs"] 	= $utilisateurs;
		$_SESSION["imprimantes"] 		= $imprimantes;
		$_SESSION["jours"] 					= $jours;
		$_SESSION["heures"] 				= $heures;
	}
}

function determier_periode()
{
	global $tab_fic_page_log;
	global $indiceTmp, $tabPeriode, $ligneLast;

	$tabPeriode = array();

	function analyse_ligne($ligne)
	{
		global $indiceTmp, $tabPeriode, $ligneLast;
		
		if ($indiceTmp==0 && $ligne!="")
		{				
			$jourMin = ereg_replace("^.*\[([0-9]{2})/([A-Za-z]{3})/([0-9]{4}):[0-9]{2}:[0-9]{2}:[0-9]{2}.*$", "\\3-\\2-\\1", $ligne);
			$jourMin = str_replace(array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"), array("01","02","03","04","05","06","07","08","09","10","11","12"), $jourMin);			
			
			if (isset($tabPeriode["min"]))
			{
				if ($jourMin<$tabPeriode["min"])
					$tabPeriode["min"] = $jourMin;
			}
			else
			{
				$tabPeriode["min"] = $jourMin;
			}
			
			$indiceTmp++;
		}
		
		if ($ligne!="")
			$ligneLast = $ligne;
	}

	for ($i=0;$i<sizeof($tab_fic_page_log);$i++)
	{
		$indiceTmp = 0;
		$ligneLast = "";	

		if (ereg("^pipe:",$tab_fic_page_log[$i]))
		{
			$fp = @popen(ereg_replace("^pipe:","",$tab_fic_page_log[$i]), "r");
			if ($fp)
			{
				while (!feof ($fp)) 
				{
					$ligne = trim(fgets($fp, 1024));
					
					analyse_ligne($ligne);				
				}
				pclose($fp);				
			}
		}
		else
		{		
			// Ouverture du fichier
			$fp = @fopen($tab_fic_page_log[$i], "r");
			if ($fp)
			{
				while (!feof ($fp)) 
				{
					$ligne = trim(fgets($fp, 1024));
					
					analyse_ligne($ligne);
				}			
				fclose($fp);		
			}
		}

		clearstatcache();				
		
		if (is_array($tabPeriode) && sizeof($tabPeriode)>0)
		{
			$jourMax = ereg_replace("^.*\[([0-9]{2})/([A-Za-z]{3})/([0-9]{4}):[0-9]{2}:[0-9]{2}:[0-9]{2}.*$", "\\3-\\2-\\1", $ligneLast);
			$jourMax = str_replace(array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"), array("01","02","03","04","05","06","07","08","09","10","11","12"), $jourMax);
			
			if (isset($tabPeriode["max"]))
			{
				if ($jourMax>$tabPeriode["max"])
					$tabPeriode["max"] = $jourMax;
			}
			else
			{
				$tabPeriode["max"] = $jourMax;
			}

			list($annee, $mois, $jour) = split("-",$tabPeriode["min"]);
			$tabPeriode["min"] = mktime(12,0,0,$mois,$jour,$annee);
			list($annee, $mois, $jour) = split("-",$tabPeriode["max"]);
			$tabPeriode["max"] = mktime(12,0,0,$mois,$jour,$annee);
		}
	}
	
	return $tabPeriode;
}

// Actions de la page
if ($service!="")
{
	$tabUtilisateursService = array();

	// Recherche des services via LDAP
	if ($serveurLdap!="" && $racineLdap!="")
	{
		include_once($rep_par_rapport_racine."lib/ldap.php");
	
		$objLdap = new ldap($serveurLdap);
	
		$entries = $objLdap->get_entries($racineLdap,"cn=".$service);
		$entries = $objLdap->get_entries($racineLdap,"gidnumber=".$entries[0]["gidnumber"][0]);
		//print_r($entries);
		for ($i=0;$i<sizeof($entries);$i++)
		{
			if ($entries[$i]["uid"][0]!="")
				$tabUtilisateursService[$entries[$i]["uid"][0]] = 1;
		}
		unset($entries);
		//print_r($tabUtilisateursService);
	}
	// Recherche des services via les groupes de "/etc/group"
	else
	{
		// Récupérer la liste des utilisateurs qui appartiennent au service
		$cmd = "more /etc/group|grep -i ".$service."";
		//echo $cmd."<BR>";
		$result = exec($cmd, $resultat);
		
		$tabTmp = split(":", $result);
		if (sizeof($tabTmp)==4 && ereg("^[0-9]{1,}$", $tabTmp[2]))
		{
			$gidnumber = $tabTmp[2];
		
			$cmd = "more /etc/passwd|grep ".$gidnumber."";
			//echo $cmd."<BR>";
			$result = exec($cmd, $resultat);
		
			// Parcours des lignes trouvées
			for ($i=0;$i<sizeof($resultat);$i++)
			{
				$tabTmp = split(":", $resultat[$i]);
				if ($tabTmp[3]==$gidnumber)
				{
					$tabUtilisateursService[$tabTmp[0]] = 1;
				}
			}
			//print_r($tabUtilisateursService);
		}
	}
	
	if ($action=="afficherservice" && (($REGISTER_GLOBALS && session_is_registered("utilisateurs")) || (!$REGISTER_GLOBALS && isset($_SESSION["utilisateurs"]))) && sizeof($tabUtilisateursService)>0)
	{
		$utilisateursTmp = array();
		$imprimantesTmp = array();
		$tabUtilisateursService_keys = array_keys($tabUtilisateursService);
		for ($i=0;$i<sizeof($tabUtilisateursService_keys);$i++)
		{	
			if (isset($utilisateurs[$tabUtilisateursService_keys[$i]]))
			{
				//print_r($utilisateurs[$tabUtilisateursService_keys[$i]]);
				if (isset($utilisateursTmp["total"]))
					$utilisateursTmp["total"] += $utilisateurs[$tabUtilisateursService_keys[$i]]["total"];
				else
					$utilisateursTmp["total"] = $utilisateurs[$tabUtilisateursService_keys[$i]]["total"];
			
				$tabTmp = array_keys($utilisateurs[$tabUtilisateursService_keys[$i]]["imprimantes"]);
				for ($j=0;$j<sizeof($tabTmp);$j++)
				{	
					if (isset($utilisateursTmp["imprimantes"][$tabTmp[$j]]))
						$utilisateursTmp["imprimantes"][$tabTmp[$j]] += $utilisateurs[$tabUtilisateursService_keys[$i]]["imprimantes"][$tabTmp[$j]];
					else
						$utilisateursTmp["imprimantes"][$tabTmp[$j]] = $utilisateurs[$tabUtilisateursService_keys[$i]]["imprimantes"][$tabTmp[$j]];					
				}
					
				if (isset($imprimantesTmp["utilisateurs"][$tabUtilisateursService_keys[$i]]))
					$imprimantesTmp["utilisateurs"][$tabUtilisateursService_keys[$i]] += $utilisateurs[$tabUtilisateursService_keys[$i]]["total"];
				else
					$imprimantesTmp["utilisateurs"][$tabUtilisateursService_keys[$i]] = $utilisateurs[$tabUtilisateursService_keys[$i]]["total"];									
			}
		}	
		//print_r($utilisateursTmp);
		//print_r($imprimantesTmp);
	}
	else if ($action=="afficherimage" && ($graphique=="jours" || $graphique=="mois") && (($REGISTER_GLOBALS && session_is_registered("jours")) || (!$REGISTER_GLOBALS && isset($_SESSION["jours"]))) && sizeof($tabUtilisateursService)>0)
	{
		$joursTmp = array();
		$jours_keys = array_keys($jours);
		for ($i=0;$i<sizeof($jours_keys);$i++)
		{	
			$joursUtilisateurs_keys = array_keys($jours[$jours_keys[$i]]["utilisateurs"]);
			for ($j=0;$j<sizeof($joursUtilisateurs_keys);$j++)
			{	
				if (isset($tabUtilisateursService[$joursUtilisateurs_keys[$j]]))
				{
					if (isset($joursTmp[$jours_keys[$i]]["total"]))
						$joursTmp[$jours_keys[$i]]["total"] += $jours[$jours_keys[$i]]["utilisateurs"][$joursUtilisateurs_keys[$j]];
					else
						$joursTmp[$jours_keys[$i]]["total"] = $jours[$jours_keys[$i]]["utilisateurs"][$joursUtilisateurs_keys[$j]];
				}
			}	
		}	
		//print_r($joursTmp);
	}
	else if ($action=="afficherimage" && $graphique=="heures" && (($REGISTER_GLOBALS && session_is_registered("heures")) || (!$REGISTER_GLOBALS && isset($_SESSION["heures"]))) && sizeof($tabUtilisateursService)>0)
	{
		$heuresTmp = array();
		$heures_keys = array_keys($heures);
		for ($i=0;$i<sizeof($heures_keys);$i++)
		{	
			$heuresUtilisateurs_keys = array_keys($heures[$heures_keys[$i]]["utilisateurs"]);
			for ($j=0;$j<sizeof($heuresUtilisateurs_keys);$j++)
			{	
				if (isset($tabUtilisateursService[$heuresUtilisateurs_keys[$j]]))
				{
					if (isset($heuresTmp[$heures_keys[$i]]["total"]))
						$heuresTmp[$heures_keys[$i]]["total"] += $heures[$heures_keys[$i]]["utilisateurs"][$heuresUtilisateurs_keys[$j]];
					else
						$heuresTmp[$heures_keys[$i]]["total"] = $heures[$heures_keys[$i]]["utilisateurs"][$heuresUtilisateurs_keys[$j]];
				}
			}	
		}	
		//print_r($heuresTmp);
	}
		
	unset($objLdap);
}

if ($action=="afficherimage")
{
	include_once($rep_jpgraph."/jpgraph.php");
	include_once($rep_jpgraph."/jpgraph_line.php");
	include_once($rep_jpgraph."/jpgraph_bar.php");
	include_once($rep_jpgraph."/jpgraph_canvas.php");
	include_once($rep_jpgraph."/jpgraph_pie.php");
	include_once($rep_jpgraph."/jpgraph_pie3d.php");
	
	if ($graphique=="jours" && (($REGISTER_GLOBALS && session_is_registered("jours")) || (!$REGISTER_GLOBALS && isset($_SESSION["jours"]))))
	{
		$tabTmp = array();

		if (sizeof($jours)>0)
		{
			$jours_keys = array_keys($jours);				
			
			// Mettre à vide les jours où il n'y a pas d'impression
			$dateTmp = explode("-",$jours_keys[0]);
			$timestampDebut = mktime(12,0,0,$dateTmp[1],$dateTmp[2],$dateTmp[0]);
			$dateTmp = explode("-",$jours_keys[sizeof($jours_keys)-1]);
			$timestampFin = mktime(12,0,0,$dateTmp[1],$dateTmp[2],$dateTmp[0]);
			$jours_keys = array();
			for ($i=$timestampDebut;$i<=$timestampFin;$i=$i+24*60*60)
			{
				$jours_keys[sizeof($jours_keys)] = date("Y-m-d", $i);
			}
			unset($timestampDebut);
			unset($timestampFin);
			unset($dateTmp);
			//print_r($jours_keys);
			
			for ($i=0;$i<sizeof($jours_keys);$i++)
			{
				if (isset($imprimante))
					$tabTmp[$jours_keys[$i]] = $jours[$jours_keys[$i]]["imprimantes"][$imprimante];
				else if (isset($utilisateur))
					$tabTmp[$jours_keys[$i]] = $jours[$jours_keys[$i]]["utilisateurs"][$utilisateur];
				else if (isset($service))
					$tabTmp[$jours_keys[$i]] = $joursTmp[$jours_keys[$i]]["total"];
				else
					$tabTmp[$jours_keys[$i]] = $jours[$jours_keys[$i]]["total"];
			}
		}
		else
		{
			$tabTmp[date("Y-m-d")] = 0;
		}
		
		// Création du graphique
		$graph = new Graph("950", "500", "auto");	
		$graph->SetScale("textlin");
		$graph->SetFrame(true,'#CCCCCC',1); 
		$graph->SetColor('white');
		$graph->SetMarginColor('white');
		$graph->SetBox();
		$graph->yscale->SetGrace(5);		

		$graph->img->SetMargin(50,30,40,80);
		//$graph->SetShadow();		

		// Create the bar plot
		$bplot = new BarPlot(array_values($tabTmp));
		$bplot->SetFillColor("#0099FF");	
		$bplot->value->SetFormat('%d');
		$bplot->value->HideZero();
		$bplot->value->Show();
		$bplot->SetWidth(0.75);
		$graph->Add($bplot);				

		$graph->xaxis->SetTickLabels(array_keys($tabTmp));
		$graph->xaxis->SetLabelAngle(90);
		$graph->xaxis->SetTextLabelInterval(7);
		
		// Affichage du graphique
		$graph->Stroke();	
	}
	else if ($graphique=="mois" && (($REGISTER_GLOBALS && session_is_registered("jours")) || (!$REGISTER_GLOBALS && isset($_SESSION["jours"]))))
	{
		$tabTmp = array();
		
		if (sizeof($jours)>0)
		{
			$jours_keys = array_keys($jours);
	
			for ($i=0;$i<sizeof($jours_keys);$i++)
			{
				$indiceTmp = $nomMois[ereg_replace("([0-9]{4})-([0-9]{2})-([0-9]{2})", "\\2", $jours_keys[$i])]." ".ereg_replace("([0-9]{4})-([0-9]{2})-([0-9]{2})", "\\1", $jours_keys[$i]);
				if (isset($imprimante))
					$tabTmp[$indiceTmp] += $jours[$jours_keys[$i]]["imprimantes"][$imprimante];
				else if (isset($utilisateur))
					$tabTmp[$indiceTmp] += $jours[$jours_keys[$i]]["utilisateurs"][$utilisateur];
				else if (isset($service))
					$tabTmp[$indiceTmp] += $joursTmp[$jours_keys[$i]]["total"];
				else
					$tabTmp[$indiceTmp] += $jours[$jours_keys[$i]]["total"];
			}
		}
		else
		{
			$tabTmp[$nomMois[date("m")]." ".date("Y")] = 0;
		}
		
		// Création du graphique
		$graph = new Graph("950", "500", "auto");	
		$graph->SetScale("textlin");
		$graph->SetFrame(true,'#CCCCCC',1); 
		$graph->SetColor('white');
		$graph->SetMarginColor('white');
		$graph->SetBox();
		$graph->yscale->SetGrace(5);		

		$graph->img->SetMargin(50,30,40,40);
		//$graph->SetShadow();		

		// Create the bar plot
		$bplot = new BarPlot(array_values($tabTmp));
		$bplot->SetFillColor("#0099FF");	
		$bplot->value->SetFormat('%d');
		$bplot->value->HideZero();
		$bplot->value->Show();
		$bplot->SetWidth(0.75);
		$graph->Add($bplot);				
		
		$graph->xaxis->SetTickLabels(array_keys($tabTmp));
				
		// Affichage du graphique
		$graph->Stroke();	
	}
	else if ($graphique=="heures" && (($REGISTER_GLOBALS && session_is_registered("heures")) || (!$REGISTER_GLOBALS && isset($_SESSION["heures"]))))
	{
		$tabTmp = array();
		
		$heures_keys = array_keys($heures);
			
		for ($i=0;$i<sizeof($heures_keys);$i++)
		{
			if (isset($imprimante))
				$tabTmp[$heures_keys[$i]] = $heures[$heures_keys[$i]]["imprimantes"][$imprimante];
			else if (isset($utilisateur))
				$tabTmp[$heures_keys[$i]] = $heures[$heures_keys[$i]]["utilisateurs"][$utilisateur];
			else if (isset($service))
				$tabTmp[$heures_keys[$i]] = $heuresTmp[$heures_keys[$i]]["total"];
			else
				$tabTmp[$heures_keys[$i]] = $heures[$heures_keys[$i]]["total"];
		}	
		
		// Création du graphique
		$graph = new Graph("950", "400", "auto");	
		$graph->SetScale("textlin");
		$graph->SetFrame(true,'#CCCCCC',1); 
		$graph->SetColor('white');
		$graph->SetMarginColor('white');
		$graph->SetBox();
		$graph->yscale->SetGrace(5);		

		$graph->img->SetMargin(50,30,40,40);
		//$graph->SetShadow();		
		
		// Create the bar plot
		$bplot = new BarPlot(array_values($tabTmp));
		$bplot->SetFillColor("#0099FF");	
		$bplot->value->SetFormat('%d');
		$bplot->value->HideZero();
		$bplot->value->Show();
		$bplot->SetWidth(0.75);
		$graph->Add($bplot);				
		
		$graph->xaxis->SetTickLabels(array_keys($tabTmp));
		
		// Affichage du graphique
		$graph->Stroke();	
	}
	else if ($graphique=="services" && (($REGISTER_GLOBALS && session_is_registered("utilisateurs")) || (!$REGISTER_GLOBALS && isset($_SESSION["utilisateurs"]))))
	{
		$total = 0;
		$tabServicesTmp = array();
		$tabServicesTmp1 = array();
		
		if (sizeof($tabServices)>0)
		{
			include_once($rep_par_rapport_racine."lib/ldap.php");
	
			$objLdap = new ldap($serveurLdap);
	
			$tabServicesTmp = array();
			$utilisateursTmp = $utilisateurs;
			$tabServices_keys = array_keys($tabServices);
			for ($i=0;$i<sizeof($tabServices_keys);$i++)
			{
				$tabServicesTmp[$tabServices_keys[$i]] = 0;
				$entries = $objLdap->get_entries($racineLdap,"cn=".$tabServices_keys[$i]);
				$entries = $objLdap->get_entries($racineLdap,"gidnumber=".$entries[0]["gidnumber"][0]);
				//print_r($entries);
				for ($j=0;$j<sizeof($entries);$j++)
				{				
					if ($entries[$j]["uid"][0]!="" && isset($utilisateursTmp[$entries[$j]["uid"][0]]))
					{
						//echo "->".$tabServices_keys[$i]."/".$entries[$j]["uid"][0]."<BR>";
						$tabServicesTmp[$tabServices_keys[$i]] += $utilisateursTmp[$entries[$j]["uid"][0]]["total"];
						
						$total += $utilisateursTmp[$entries[$j]["uid"][0]]["total"];
						
						// Supprimer l'utilisateur qui vient d'être ajouté au total du service
						unset($utilisateursTmp[$entries[$j]["uid"][0]]);
					}					
				}
			}
			
			// Suppression des services qui n'ont pas d'utilisateurs concernés
			for ($i=0;$i<sizeof($tabServices_keys);$i++)
			{
				if ($tabServicesTmp[$tabServices_keys[$i]]==0)
				{
					unset($tabServicesTmp[$tabServices_keys[$i]]);
				}
			}
			
			// Ajout des utilisateurs qui n'ont pas été asscociés à des services
			$utilisateursTmp_keys = array_keys($utilisateursTmp);
			for ($i=0;$i<sizeof($utilisateursTmp_keys);$i++)
			{
				$tabServicesTmp["<".$utilisateursTmp_keys[$i].">"] = $utilisateursTmp[$utilisateursTmp_keys[$i]]["total"];
				
				$total += $utilisateursTmp[$utilisateursTmp_keys[$i]]["total"];
			}			
			
			// Ajout des utilisateurs qui n'ont pas été asscociés à des services
			$tabServicesTmp_keys = array_keys($tabServicesTmp);
			for ($i=0;$i<sizeof($tabServicesTmp);$i++)
			{
				$tabServicesTmp1[$tabServicesTmp_keys[$i]." (nb: ".$tabServicesTmp[$tabServicesTmp_keys[$i]]." - ".(ceil(($tabServicesTmp[$tabServicesTmp_keys[$i]]/$total)*10000)/100)."%%)"] = $tabServicesTmp[$tabServicesTmp_keys[$i]];
			}	
			
			//print_r($tabServicesTmp);
			//print_r($utilisateursTmp);
		}
		
		ksort($tabServicesTmp);
		ksort($tabServicesTmp1);
		
		// Création du graphique
		$graph = new PieGraph("950", "430", "auto");	
		$graph->SetScale("textlin");
		$graph->SetFrame(true,'#CCCCCC',1); 
		$graph->SetColor('white');
		$graph->SetMarginColor('white');
		$graph->SetBox();
		$graph->yscale->SetGrace(5);		
		$graph->legend->Pos(0.01,0.5,"left","center");
		
		//$graph->SetMargin(20,30,40,40);
		//$graph->SetShadow();		
		
		if (sizeof($tabServicesTmp)>0)
		{
			// Create the bar plot
			$p1 = new PiePlot3D(array_values($tabServicesTmp));
			$p1->SetLegends(array_keys($tabServicesTmp1));
			$p1->SetTheme("sand");
			$p1->SetCenter(0.65);
			$p1->SetSize(0.45);
			$p1->SetLabels(array_keys($tabServicesTmp));	
			//$p1->ExplodeAll(15);	
			//$p1->SetAngle(60);
			$graph->Add($p1);				
		}
						
		// Affichage du graphique
		$graph->Stroke();	
	}
	
	die();
}
else if ($action=="afficherutilisateur")
{
	$titre = IMAGE_STATS." ".str_replace("##utilisateur##", $utilisateur, $ficIndex5);
}
else if ($action=="afficherimprimante")
{
	// Remplacer le nom de l'imprimante par l'url de l'imprimante dans la serveur CUPS
	if ($serveurWebCups!="")
		$titre = IMAGE_IMPRIMANTE." ".str_replace("##imprimante##", "<A HREF=\"".$serveurWebCups."/printers/".$imprimante."\" TARGET=\"_blank\">".$imprimante."</A>", $ficIndex6);
	else
		$titre = IMAGE_IMPRIMANTE." ".str_replace("##imprimante##", $imprimante, $ficIndex6);
}
else if ($action=="afficherservice")
{
	$titre = IMAGE_STATS." ".str_replace("##service##", $service, $ficIndex7);
}
else
{
	if ($action=="recharger")
	{
		$utilisateurs = array();

		$imprimantes = array();

		$jours = array();

		$heures = array(
					"00h." => array(
						"total" => 0,
						"imprimantes" => array(),
						"utilisateurs" => array(),
					),
					"01h." => array(
						"total" => 0,
						"imprimantes" => array(),
						"utilisateurs" => array(),
					),
					"02h." => array(
						"total" => 0,
						"imprimantes" => array(),
						"utilisateurs" => array(),
					),
					"03h." => array(
						"total" => 0,
						"imprimantes" => array(),
						"utilisateurs" => array(),
					),
					"04h." => array(
						"total" => 0,
						"imprimantes" => array(),
						"utilisateurs" => array(),
					),
					"05h." => array(
						"total" => 0,
						"imprimantes" => array(),
						"utilisateurs" => array(),
					),
					"06h." => array(
						"total" => 0,
						"imprimantes" => array(),
						"utilisateurs" => array(),
					),
					"07h." => array(
						"total" => 0,
						"imprimantes" => array(),
						"utilisateurs" => array(),
					),
					"08h." => array(
						"total" => 0,
						"imprimantes" => array(),
						"utilisateurs" => array(),
					),
					"09h." => array(
						"total" => 0,
						"imprimantes" => array(),
						"utilisateurs" => array(),
					),
					"10h." => array(
						"total" => 0,
						"imprimantes" => array(),
						"utilisateurs" => array(),
					),
					"11h." => array(
						"total" => 0,
						"imprimantes" => array(),
						"utilisateurs" => array(),
					),
					"12h." => array(
						"total" => 0,
						"imprimantes" => array(),
						"utilisateurs" => array(),
					),
					"13h." => array(
						"total" => 0,
						"imprimantes" => array(),
						"utilisateurs" => array(),
					),
					"14h." => array(
						"total" => 0,
						"imprimantes" => array(),
						"utilisateurs" => array(),
					),
					"15h." => array(
						"total" => 0,
						"imprimantes" => array(),
						"utilisateurs" => array(),
					),
					"16h." => array(
						"total" => 0,
						"imprimantes" => array(),
						"utilisateurs" => array(),
					),
					"17h." => array(
						"total" => 0,
						"imprimantes" => array(),
						"utilisateurs" => array(),
					),
					"18h." => array(
						"total" => 0,
						"imprimantes" => array(),
						"utilisateurs" => array(),
					),
					"19h." => array(
						"total" => 0,
						"imprimantes" => array(),
						"utilisateurs" => array(),
					),
					"20h." => array(
						"total" => 0,
						"imprimantes" => array(),
						"utilisateurs" => array(),
					),
					"21h." => array(
						"total" => 0,
						"imprimantes" => array(),
						"utilisateurs" => array(),
					),
					"22h." => array(
						"total" => 0,
						"imprimantes" => array(),
						"utilisateurs" => array(),
					),
					"23h." => array(
						"total" => 0,
						"imprimantes" => array(),
						"utilisateurs" => array(),
					),
		);

		charger_page_log();
	}

	if ($action!="modifierDate")
		$action = "";
	
	// Remplacer CUPS par l'url du serveur CUPS
	if ($serveurWebCups!="" && ereg("CUPS",$titre1))
		$titre1 = ereg_replace("CUPS","<A HREF=\"".$serveurWebCups."\" TARGET=\"_blank\">CUPS</A>",$titre1);
		
	$titre = IMAGE_STATS." ".$titre1;
}

// Inclure l'en-tête de page
include_once($rep_par_rapport_racine."inc/header.inc.php");
?>
<B><FONT SIZE="5"><?=$titre;?></FONT></B>
<BR>
<?
if ((($REGISTER_GLOBALS && session_is_registered("utilisateurs")) || (!$REGISTER_GLOBALS && isset($_SESSION["utilisateurs"]))) && ($mktimeMin!="" && $mktimeMax!="") && $action!="modifierDate")
{
	if (is_array($jours) && sizeof($jours)>0)
	{
		$date1 = date("d",$mktimeMin)." ".$nomMois[date("m",$mktimeMin)]." ".date("Y",$mktimeMin);
		$date2 = date("d",$mktimeMax)." ".$nomMois[date("m",$mktimeMax)]." ".date("Y",$mktimeMax);
	}
	else
	{
		$date1 = "???";
		$date2 = "???";
	}
?>
- <B><?=str_replace(array("##date1##","##date2##"), array($date1, $date2), $ficIndex12);?></B> [ <A HREF="<?=$PHP_SELF;?>?action=modifierDate&mktimeMin=<?=$mktimeMin;?>&mktimeMax=<?=$mktimeMax;?>"><?=$ficIndex25;?></A> ] -
<BR>
<BR>
[ <A HREF="<?=$PHP_SELF;?>?action=recharger&mktimeMin=<?=$mktimeMin;?>&mktimeMax=<?=$mktimeMax;?>"><?=$ficIndex22;?></A> ] 
<BR>
<BR>
<?
	if ($action=="afficherimprimante")
	{
?>
[ <A HREF="<?=$serveurWebCups;?>/printers/<?=$imprimante;?>"><?=$ficIndex8;?></A> ]
<BR>
<BR>
<?
	}
?>
<HR SIZE="2">
[
<?
	if ($action=="afficherimprimante")
	{
?>
<A HREF="#nbPagesUtilisateurs"><?=$ficIndex1;?></A>
|
<A HREF="#nbPagesJours"><?=$ficIndex3;?></A>
|
<A HREF="#nbPagesMois"><?=$ficIndex14;?></A>
|
<A HREF="#nbPagesHeures"><?=$ficIndex4;?></A>
|
<A HREF="#total"><?=$ficIndex15;?></A>
<?
	} 
	else if ($action=="afficherutilisateur")
	{
?>
<A HREF="#nbPagesImprimantes"><?=$ficIndex2;?></A>
|
<A HREF="#nbPagesJours"><?=$ficIndex3;?></A>
|
<A HREF="#nbPagesMois"><?=$ficIndex14;?></A>
|
<A HREF="#nbPagesHeures"><?=$ficIndex4;?></A>
|
<A HREF="#total"><?=$ficIndex15;?></A>
<?
	} 
	else if ($action=="afficherservice")
	{
?>
<A HREF="#nbPagesUtilisateurs"><?=$ficIndex1;?></A>
|
<A HREF="#nbPagesImprimantes"><?=$ficIndex2;?></A>
|
<A HREF="#nbPagesJours"><?=$ficIndex3;?></A>
|
<A HREF="#nbPagesMois"><?=$ficIndex14;?></A>
|
<A HREF="#nbPagesHeures"><?=$ficIndex4;?></A>
|
<A HREF="#total"><?=$ficIndex15;?></A>
<?
	} 
	else
	{
?>
<A HREF="#nbPagesUtilisateurs"><?=$ficIndex1;?></A>
|
<A HREF="#nbPagesServices"><?=$ficIndex11;?></A>
|
<A HREF="#nbPagesImprimantes"><?=$ficIndex2;?></A>
|
<A HREF="#nbPagesJours"><?=$ficIndex3;?></A>
|
<A HREF="#nbPagesMois"><?=$ficIndex14;?></A>
|
<A HREF="#nbPagesHeures"><?=$ficIndex4;?></A>
|
<A HREF="#total"><?=$ficIndex15;?></A>
<?
	} 
?>
]
<HR SIZE="2">
<?
	if ($action=="afficherimprimante" || $action=="afficherservice" || $action=="")
	{
?>
<A NAME="nbPagesUtilisateurs"></A>
<BR>
<FONT CLASS="titre"><?=$ficIndex1;?></FONT>
<BR>
<BR>
<?
		if ($action=="")
		{
?>
<TABLE WIDTH="950" BORDER="1" CELLSPACING="0" CELLPADDING="1">
  <TR>
   <TD CLASS="entete" WIDTH="50%"><?=$ficIndex16;?></TD>
   <TD CLASS="entete" WIDTH="25%"><?=$ficIndex17;?></TD>
   <TD CLASS="entete" WIDTH="25%"><?=$ficIndex18;?></TD>
  </TR>
<?
			$htmlTmp = "";
			$nbPages = 0;
		
			//print_r($utilisateurs);
			if (sizeof($utilisateurs)>0)
			{
				$utilisateurs_keys = array_keys($utilisateurs);
				for ($i=0;$i<sizeof($utilisateurs_keys);$i++)
				{	
					$htmlTmp .= "
  <TR>	
   <TD><A HREF=\"".$PHP_SELF."?action=afficherutilisateur&utilisateur=".$utilisateurs_keys[$i]."&mktimeMin=".$mktimeMin."&mktimeMax=".$mktimeMax."\" TARGET=\"_blank\">".$utilisateurs_keys[$i]."</A></TD>
   <TD>pourcentage".$utilisateurs[$utilisateurs_keys[$i]]["total"]." %</TD>
   <TD>".$utilisateurs[$utilisateurs_keys[$i]]["total"]."</TD>
  </TR>		
					";
			
					$nbPages += $utilisateurs[$utilisateurs_keys[$i]]["total"];
				}
		
				$htmlTmp = preg_replace("/(pourcentage)([0-9]+)/e", "pourcentage(\\2,$nbPages)", $htmlTmp);
		
				echo $htmlTmp;
			}
?>
</TABLE>
<BR>
<?		
			if (sizeof($tabServices)>0)
			{
?>
<HR SIZE="1">
<A NAME="nbPagesServices"></A>
<BR>
<FONT CLASS="titre"><?=$ficIndex11;?></FONT>
<BR>
<BR>
<IMG SRC="<?=$PHP_SELF."?action=afficherimage&graphique=services";?>" ALT="">
<BR>
<BR>
<TABLE WIDTH="950" BORDER="0" CELLSPACING="0" CELLPADDING="4">
  <TR>
   <TD>
   <U><?=$ficIndex13;?>:</U>
   <BR>
<?
				//print_r($tabServices);
				$tabServices_keys = array_keys($tabServices);
				for ($i=0;$i<sizeof($tabServices_keys);$i++)
				{	
?>
   [<A HREF="<?=$PHP_SELF;?>?action=afficherservice&service=<?=$tabServices_keys[$i];?>&mktimeMin=<?=$mktimeMin;?>&mktimeMax=<?=$mktimeMax;?>" TARGET="__blank"><?=$tabServices_keys[$i];?></A>] 
<?
				}		
?>
   </TD>
  </TR>
</TABLE>
<BR>
<?			
		}
?>
<HR SIZE="1">
<?
		}
		else if (($action=="afficherimprimante" || $action=="afficherservice"))
		{
?>
<TABLE WIDTH="950" BORDER="1" CELLSPACING="0" CELLPADDING="1">
  <TR>
   <TD CLASS="entete" WIDTH="40%"><?=$ficIndex16;?></TD>
   <TD CLASS="entete" WIDTH="20%"><?=$ficIndex17;?></TD>
   <TD CLASS="entete" WIDTH="20%"><?=$ficIndex18;?></TD>
<?
			if ($action=="afficherimprimante")
			{
?>
   <TD CLASS="entete" WIDTH="20%"><?=$ficIndex20;?></TD>
<?
			}
?>
  </TR>
<?
			$htmlTmp = "";
			$nbPages = 0;
		
			if ($action=="afficherimprimante")
			{
				$imprimantesTmp = $imprimantes[$imprimante];
			}
			//print_r($utilisateursTmp);
			if (sizeof($imprimantesTmp)>0)
			{
				arsort($imprimantesTmp["utilisateurs"]);	
				$utilisateurs_keys = array_keys($imprimantesTmp["utilisateurs"]);
				for ($i=0;$i<sizeof($utilisateurs_keys);$i++)
				{	
					$htmlTmp .= "
  <TR>
   <TD><A HREF=\"".$PHP_SELF."?action=afficherutilisateur&utilisateur=".$utilisateurs_keys[$i]."&mktimeMin=".$mktimeMin."&mktimeMax=".$mktimeMax."\" TARGET=\"_blank\">".$utilisateurs_keys[$i]."</A></TD>
   <TD>pourcentage".$imprimantesTmp["utilisateurs"][$utilisateurs_keys[$i]]." %</TD>
   <TD>".$imprimantesTmp["utilisateurs"][$utilisateurs_keys[$i]]."</TD>";
				   	if ($action=="afficherimprimante")
					{
						$htmlTmp .= "
   <TD>";
	   					if (isset($tabCoutPageA4[$imprimante]) && $tabCoutPageA4[$imprimante]>0)
	   					{
							$coutTmp = ($imprimantesTmp["utilisateurs"][$utilisateurs_keys[$i]]*$tabCoutPageA4[$imprimante]);
							$htmlTmp .= sprintf("%01.2f",$coutTmp)." €";
							$cout += $coutTmp;
						}
						else
							$htmlTmp .= "???";
						$htmlTmp .= "</TD>";
					}
					$htmlTmp .= "
  </TR>		  
					";
			
					$nbPages += $imprimantesTmp["utilisateurs"][$utilisateurs_keys[$i]];
				}
		
				$htmlTmp = preg_replace("/(pourcentage)([0-9]+)/e", "pourcentage(\\2,$nbPages)", $htmlTmp);
		
				echo $htmlTmp;
			}
?>
</TABLE>
<BR>
<HR SIZE="1">
<?	
		}
	}
	
	if ($action=="afficherutilisateur" || $action=="afficherservice" || $action=="")
	{
?>
<A NAME="nbPagesImprimantes"></A>
<BR>
<FONT CLASS="titre"><?=$ficIndex2;?></FONT>
<BR>
<BR>
<?
		if ($action=="")
		{
?>
<TABLE WIDTH="950" BORDER="1" CELLSPACING="0" CELLPADDING="1">
  <TR>
   <TD CLASS="entete" WIDTH="40%"><?=$ficIndex19;?></TD>
   <TD CLASS="entete" WIDTH="15%"><?=$ficIndex17;?></TD>
   <TD CLASS="entete" WIDTH="15%"><?=$ficIndex18;?></TD>
   <TD CLASS="entete" WIDTH="15%"><?=$ficIndex21;?></TD>
   <TD CLASS="entete" WIDTH="15%"><?=$ficIndex20;?></TD>
  </TR>
<?
			$htmlTmp = "";
			$nbPages = 0;
		
			//print_r($imprimantes);
			if (sizeof($imprimantes)>0)
			{
				$imprimantes_keys = array_keys($imprimantes);
				for ($i=0;$i<sizeof($imprimantes_keys);$i++)
				{	
					$htmlTmp .= "
  <TR>
   <TD><A HREF=\"".$PHP_SELF."?action=afficherimprimante&imprimante=".$imprimantes_keys[$i]."&mktimeMin=".$mktimeMin."&mktimeMax=".$mktimeMax."\" TARGET=\"_blank\">".$imprimantes_keys[$i]."</A></TD>
   <TD>pourcentage".$imprimantes[$imprimantes_keys[$i]]["total"]." %</TD>
   <TD>".$imprimantes[$imprimantes_keys[$i]]["total"]."</TD>
   <TD>".$tabCoutPageA4[$imprimantes_keys[$i]]."</TD>
   <TD>";
	   			if (isset($tabCoutPageA4[$imprimantes_keys[$i]]) && $tabCoutPageA4[$imprimantes_keys[$i]]>0)
	   			{
						$coutTmp = ($imprimantes[$imprimantes_keys[$i]]["total"]*$tabCoutPageA4[$imprimantes_keys[$i]]);
						$htmlTmp .= sprintf("%01.2f",$coutTmp)." €";
						$cout += $coutTmp;
					}
					else
						$htmlTmp .= "???";
					$htmlTmp .= "</TD>
  </TR>		   
					";
			
					$nbPages += $imprimantes[$imprimantes_keys[$i]]["total"];
				}
		
				$htmlTmp = preg_replace("/(pourcentage)([0-9]+)/e", "pourcentage(\\2,$nbPages)", $htmlTmp);
		
				echo $htmlTmp;
			}
?>
</TABLE>
<BR>
<?
		}
		else if (($action=="afficherutilisateur" || $action=="afficherservice") && sizeof($utilisateurs)>0)
		{
?>
<TABLE WIDTH="950" BORDER="1" CELLSPACING="0" CELLPADDING="1">
  <TR>
   <TD CLASS="entete" WIDTH="40%"><?=$ficIndex19;?></TD>
   <TD CLASS="entete" WIDTH="15%"><?=$ficIndex17;?></TD>
   <TD CLASS="entete" WIDTH="15%"><?=$ficIndex18;?></TD>
   <TD CLASS="entete" WIDTH="15%"><?=$ficIndex21;?></TD>
   <TD CLASS="entete" WIDTH="15%"><?=$ficIndex20;?></TD>   
  </TR>
<?
			$htmlTmp = "";
			$nbPages = 0;
				
			if ($action=="afficherutilisateur")
			{
				$utilisateursTmp = $utilisateurs[$utilisateur];
			}
			//print_r($utilisateursTmp);
			if (sizeof($utilisateursTmp)>0)
			{
				arsort($utilisateursTmp["imprimantes"]);
				$imprimantes_keys = array_keys($utilisateursTmp["imprimantes"]);
				for ($i=0;$i<sizeof($imprimantes_keys);$i++)
				{	
					$htmlTmp .= "
  <TR>
   <TD><A HREF=\"".$PHP_SELF."?action=afficherimprimante&imprimante=".$imprimantes_keys[$i]."&mktimeMin=".$mktimeMin."&mktimeMax=".$mktimeMax."\" TARGET=\"_blank\">".$imprimantes_keys[$i]."</A></TD>
   <TD>pourcentage".$utilisateursTmp["imprimantes"][$imprimantes_keys[$i]]." %</TD>
   <TD>".$utilisateursTmp["imprimantes"][$imprimantes_keys[$i]]."</TD>
   <TD>".$tabCoutPageA4[$imprimantes_keys[$i]]."</TD>
   <TD>";
	   				if (isset($tabCoutPageA4[$imprimantes_keys[$i]]) && $tabCoutPageA4[$imprimantes_keys[$i]]>0)
	   				{
						$coutTmp = ($utilisateursTmp["imprimantes"][$imprimantes_keys[$i]]*$tabCoutPageA4[$imprimantes_keys[$i]]);
						$htmlTmp .= sprintf("%01.2f",$coutTmp)." €";
						$cout += $coutTmp;
					}
					else
						$htmlTmp .= "???";
					$htmlTmp .= "</TD>
  </TR>		
					";
			
					$nbPages += $utilisateursTmp["imprimantes"][$imprimantes_keys[$i]];
				}
		
				$htmlTmp = preg_replace("/(pourcentage)([0-9]+)/e", "pourcentage(\\2,$nbPages)", $htmlTmp);
		
				echo $htmlTmp;
			}
?>
</TABLE>
<BR>
<?	
		}
?>
<HR SIZE="1">
<?
	}
?>
<A NAME="nbPagesJours"></A>
<BR>
<FONT CLASS="titre"><?=$ficIndex3;?></FONT>
<BR>
<BR>
<?
	//print_r($jours);
	if ($action=="afficherutilisateur")
		$queryTmp = $PHP_SELF."?action=afficherimage&graphique=jours&utilisateur=".$utilisateur;
	else if ($action=="afficherimprimante")
		$queryTmp = $PHP_SELF."?action=afficherimage&graphique=jours&imprimante=".$imprimante;
	else if ($action=="afficherservice")
		$queryTmp = $PHP_SELF."?action=afficherimage&graphique=jours&service=".$service;
	else
		$queryTmp = $PHP_SELF."?action=afficherimage&graphique=jours";
?>
<IMG SRC="<?=$queryTmp;?>" ALT="<?=$ficIndex3;?>">
<BR>
<BR>
<HR SIZE="1">
<A NAME="nbPagesMois"></A>
<BR>
<FONT CLASS="titre"><?=$ficIndex14;?></FONT>
<BR>
<BR>
<?
	if ($action=="afficherutilisateur")
		$queryTmp = $PHP_SELF."?action=afficherimage&graphique=mois&utilisateur=".$utilisateur;
	else if ($action=="afficherimprimante")
		$queryTmp = $PHP_SELF."?action=afficherimage&graphique=mois&imprimante=".$imprimante;
	else if ($action=="afficherservice")
		$queryTmp = $PHP_SELF."?action=afficherimage&graphique=mois&service=".$service;
	else
		$queryTmp = $PHP_SELF."?action=afficherimage&graphique=mois";
?>
<IMG SRC="<?=$queryTmp;?>" ALT="<?=$ficIndex3;?>">
<BR>
<BR>
<HR SIZE="1">
<A NAME="nbPagesHeures"></A>
<BR>
<FONT CLASS="titre"><?=$ficIndex4;?></FONT>
<BR>
<BR>
<?
	//print_r($heures);
	if ($action=="afficherutilisateur")
		$queryTmp = $PHP_SELF."?action=afficherimage&graphique=heures&utilisateur=".$utilisateur;
	else if ($action=="afficherimprimante")
		$queryTmp = $PHP_SELF."?action=afficherimage&graphique=heures&imprimante=".$imprimante;
	else if ($action=="afficherservice")
		$queryTmp = $PHP_SELF."?action=afficherimage&graphique=heures&service=".$service;
	else
		$queryTmp = $PHP_SELF."?action=afficherimage&graphique=heures";
?>
<IMG SRC="<?=$queryTmp;?>" ALT="<?=$ficIndex4;?>">
<BR>
<BR>
<HR SIZE="1">
<A NAME="total"></A>
<BR>
<?=$ficIndex9;?>: <B><?=$nbPages;?></B>
<?
	if ($action=="afficherimprimante" && isset($tabCoutPageA4[$imprimante]) && $tabCoutPageA4[$imprimante]!="")
	{
?>
 / <?=$ficIndex21;?>: <B><?=$tabCoutPageA4[$imprimante];?> €</B>
<?
	}
?>
 / <?=$ficIndex10;?> : <B><? if ($cout<="0") echo "???"; else echo sprintf("%01.2f",$cout);?> €</B>
<BR>
<BR>
<HR SIZE="2">
<?
}
else
{
	$tabTmp = determier_periode();
	$select1 = "";
	$select2 = "";
	
	if (is_array($tabTmp) && sizeof($tabTmp)>0)
	{
		for ($i=$tabTmp["min"];$i<=$tabTmp["max"];$i=$i+24*60*60)
		{
			$select1 .= "<OPTION VALUE=\"".$i."\"";
			if ($mktimeMin==$i)
				$select1 .= " SELECTED";
			$select1 .= ">".date("d",$i)." ".$nomMois[date("m",$i)]." ".date("Y",$i)."</OPTION>\n";
			
			$select2 .= "<OPTION VALUE=\"".$i."\"";
			if (($i==$tabTmp["max"] && $mktimeMax=="") || $mktimeMax==$i)
				$select2 .= " SELECTED";
			$select2 .= ">".date("d",$i)." ".$nomMois[date("m",$i)]." ".date("Y",$i)."</OPTION>\n";
		}
	}
	
	$select1 = "<SELECT NAME=\"mktimeMin\">\n".$select1."</SELECT>\n";
	$select2 = "<SELECT NAME=\"mktimeMax\">\n".$select2."</SELECT>\n";
?>
<HR SIZE="2">
<BR>
<BR>
<TABLE BORDER="0" CELLSPACING="2" CELLPADDING="2" WIDTH="400">
  <FORM NAME="form" ACTION="<?=$PHP_SELF;?>" METHOD="get">
  <INPUT TYPE="hidden" NAME="action" VALUE="recharger">
  <TR HEIGHT="30">
   <TD ALIGN="center" CLASS="titre"><?=$ficIndex23;?></TD>
  </TR>
  <TR HEIGHT="30">
   <TD ALIGN="center"><?=str_replace(array("##date1##","##date2##"),array($select1,$select2),$ficIndex12);?></TD>
  </TR>  
  <TR HEIGHT="30">
   <TD ALIGN="center"><INPUT TYPE="submit" NAME="valider" VALUE="<?=$ficIndex24;?>" STYLE="width:200px"></TD>
  </TR>  
  </FORM>
</TABLE>
<BR>
<BR>
<HR SIZE="2">
<?	
}

// Inclure le pied de page de page
include_once($rep_par_rapport_racine."inc/footer.inc.php");
?>