<?
/**universal
 * Classe ldap: Connection avec le serveur LDAP
 * 
 * @author Thomas Pequet
 * @version	1.1
 */
class ldap {

	/**universal
	 * Adresse IP du serveur LDAP
	 */
	var $adresseServeur  = null;

	/**universal
	 * Objet LDAP definissant le serveur LDAP
	 */
	var $serveur  = null;

	/**universal
	 * Dès qu'une fonction retourne false, la variable erreur est rensignée
	 */
	var $erreur  = null;

	/**universal
	 * Constructeur
	 * @param $adresse:String Adresse IP du serveur LDAP
	 */
	function ldap($adresse = null) {
		if (isset($adresse))
			$this->adresseServeur = $adresse;	
	}	
	
	/**universal
	 * Connexion au serveur LDAP
	 * @return false si la connexion avec le serveur ne se fait pas 
	 */
	function connect() {		
		// Connexion au serveur LDAP
		if ($this->serveur = ldap_connect($this->adresseServeur)) {
			return true;
		} else {
			$this->erreur = "Impossible de se connecter au serveur LDAP";
			return false;
		}			
	}
	
	/**universal
	 * Déconnexion au serveur LDAP
	 * @return false si la connexion avec le serveur ne se fait pas 
	 */
	function close() {
		// Déconnexion au serveur LDAP
		if ($this->serveur!=null)
			ldap_close($this->serveur);		
	}	

	/**universal
	 * Authentification d'une personne contenu dans l'annuaire LDAP
	 * @param base_dn:String Base du dossier de recherche
	 * @param secret:String Mot de passe de l'ladministrateur LDAP
	 * @return false si l'authentification ne se fait pas
	 */
	function Login($base_dn, $secret) {
		if ($secret !="" && @ldap_bind($this->serveur, $base_dn, $secret)) {
			return true;
		} else {
			$this->erreur = "Echec lors de l'authentification LDAP";
			return false;
		}
	}				
		
	/**universal
	 * Authentification d'une personne contenu dans l'annuaire LDAP
	 * @param base_dn:String Base du dossier de recherche	 
	 * @param LName:String Login de l'utilisateur 
	 * @param LPassword:String Mot de passe de l'utilisateur 	
	 * @return false si l'authentification ne se fait pas
	 */
	function loginUtilisateur($base_dn, $LName = "", $LPassword = "") {
		if ($LName!="" && $LPassword!="")
			if ($this->login("uid=".$LName.",".$base_dn, $LPassword))
				return true;
			else
			{
				$this->erreur = "Echec lors de l'authentification (".$LName.")";
				return false;
			}
		else
		{
			$this->erreur = "Echec lors de l'authentification (".$LName.")";
			return false;
		}
	}		

	/**universal
	 * Retourne les entrées de la personne loguée
	 * @param base_dn:String Base du dossier de recherche
	 * @param tableau:Array Tableau contenant les à mettre à jour
	 * @return True si bien passé
	 */
	function set_entries($base_dn, $tableau) {	
		// Si pas connecté 
		if ($this->serveur==null)
			$this->connect($adresseServeur);	

		if ($this->serveur!=null) {
			// Modifier la valeur dans LDAP
			return @ldap_modify($this->serveur, $base_dn, $tableau);
		} else {
			return false;
		}
	}
	
	/**universal
	 * Retourne les entrées selon les critères
	 * @param base_dn:String Base du dossier de recherche
	 * @param filtre:String Filtre de recherche
	 * @return Tableau contenant les attibrut
	 */
	function get_entries($base_dn, $filtre) {	
		// Si pas connecté 
		if ($this->serveur==null)
			$this->connect($adresseServeur);	

		if ($this->serveur!=null) {
			// Recherche dans LDAP
   			$sr = ldap_search($this->serveur,$base_dn, $filtre); 
   			// Récupérer les entrées
   			return ldap_get_entries($this->serveur, $sr);
		} else {
			return null;
		}
	}	
   
	/**universal
	 * Retourne l'erreur au cas ou il y est une focntion qui renvoie 'false'
	 * @return Une string expliquant l'erreur
	 */	
	function retour_erreur() {
		return $this->erreur;
	}		
}
?>