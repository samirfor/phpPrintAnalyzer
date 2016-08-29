<?php
/**universal
 * Afficher le pourcentage d'un division
 * @param numerateur:Int Nombre qui sera le numérateur de la division
 * @param diviseur:Int Nombre qui sera le diviseur de la division
 * @return Chaine de caractères
 */  
function pourcentage($numerateur, $diviseur)
{
	return sprintf("%01.2f", ($numerateur/$diviseur)*100);
}

/**universal
 * Fonction de comparaison de "usort": trier un tableau par sa colonne "total"
 * @return si le total de $a et inférieur à celui de $b
 */  
function tri_total($a, $b)
{
	return $a["total"]<$b["total"];
}

?>