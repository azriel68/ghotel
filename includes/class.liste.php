<?php

/**
 * Classe de gestion de liste
 * Viendra l'ordonnancement, la pagination, etc... Pour le moment c'est neutre
 * @version $Id$
 * @copyright 2006
 */

class TColonne{
	/**
	 * Constructor
	 * @access protected
	 */
	function TColonne(){
		$this->nom="";
		$this->libelle="";
		$this->type="";
		$this->valeur="";
	}
}

class TListe{
	/**
	 * Constructor
	 * @access protected
	 */
	function TListe($table,$where="",$ordre_colonne="",$ordre="A"){
	/**
	 * Alexis ALGOUD
	 * 07/10/2006 15:12:01
	 * table = table sur laquelle on effectue la lecture
	 * ordre_colonne = colonne d'ordonnancement
	 * ordre = A ou D (ascendant ou descendant)
	 **/

		$this->table=$table;
		$this->ordre_colonne=$ordre_colonne;
		$this->ordre=$ordre;
		$this->where=$where;

		$this->TColonne = array();

	}

	function ajout_colonne($nom_colonne,$libelle_colonne="",$type="s"){
	/**
	 * Alexis ALGOUD
	 * 07/10/2006 14:54:17
	 * Ajoute les colonnes à lister, dans l'order fournis
	 * Libellé est le titre de la colonne si pas idem à la DB
	 * type=(s)tandart ou (d)ate ou (dh)ate heure ou (dhs)date heure seconde ou (n)ombre ou (n2)nombre 2 décimal ou (i)nvisible
	 **/

		$i = count($this->TColonne);

		$this->TColonne[$i]=new TColonne;
		$this->TColonne[$i]->nom = $nom_colonne;
		$this->TColonne[$i]->libelle = $libelle_colonne;
		$this->TColonne[$i]->type = $type;


	}

	function affiche_entete(){
	/**
	 * Alexis ALGOUD
	 * 07/10/2006 14:59:03
	 * Affiche l'entête de la liste
	 **/

		$resultat="";
		$resultat.="<table border=\"1\" width=\"500\"><tr>";

		$nb = count($this->TColonne);
		for($i = 0; $i < $nb; $i++){
			if($this->TColonne[$i]->type!='i'){
				$resultat.="<td>";

				$resultat.="<b>".(($this->TColonne[$i]->libelle!="")?$this->TColonne[$i]->libelle:$this->TColonne[$i]->nom)."</b>";

				$resultat.="</td>";
			}

		} // for

		$resultat.="</tr>";

		return $resultat;

	}
	function affiche_pied(){
	/**
	 * Alexis ALGOUD
	 * 07/10/2006 14:58:54
	 * Affiche le pied de la liste
	 **/

		$resultat="";
		$resultat.="</table>";
		return $resultat;
	}
	function affiche_requete(&$db){
	/**
	 * Alexis ALGOUD
	 * 07/10/2006 17:42:10
	 * Exécute la requête est affiche le résultat
	 **/

		$resultat="";

		$sql_colonne = "";
		$sql_where = "";

		$nb_colonne = count($this->TColonne);
		for($i = 0; $i < $nb_colonne; $i++){
			if($i>0)$sql_colonne.=",";
			$sql_colonne.=$this->TColonne[$i]->nom;
		}

		$sql="SELECT ".$sql_colonne."
		FROM ".$this->table;

		if($this->where!="")$sql.=" WHERE ".$this->where;
		if($this->ordre_colonne!="")$sql.=" ORDER BY ".$this->ordre_colonne." ".(($this->ordre=="A")?"ASC":"DESC");

		$db->Execute($sql);



		while($db->Get_line()){
			$resultat.="<tr>";
			for($i = 0; $i < $nb_colonne; $i++){
				$this->TColonne[$i]->valeur = $db->Get_field($this->TColonne[$i]->nom);
				$resultat.=$this->affiche_colonne($this->TColonne[$i]);

			}
			$resultat.="</tr>";
		}



		return $resultat;
	}
	function affiche_colonne(&$col){
		$resultat="";
		switch($col->type){
			case 's':
				$resultat.="<td>".$col->valeur."</td>";
				break;
			case 'n':
				$resultat.="<td align=\"right\">"._fnumber($col->valeur)."</td>";
				break;
			case 'n2':
				$resultat.="<td align=\"right\">"._fnumber($col->valeur)."</td>";
				break;
			case 'i':
				null; // ce cas là n'affiche rien
				break;
			case 'd':
				$resultat.="<td align=\"right\">".date("d/m/Y",strtotime($col->valeur))."</td>";
				break;
			case 'dh':
				$resultat.="<td align=\"right\">".date("d/m/Y H:i",strtotime($col->valeur))."</td>";
				break;
			case 'dhs':
				$resultat.="<td align=\"right\">".date("d/m/Y H:i:s",strtotime($col->valeur))."</td>";
				break;
		}

		return $resultat;
	}
	function affiche(&$db){
	/**
	 * Alexis ALGOUD
	 * 07/10/2006 14:54:47
	 * Affiche la liste désiré
	 **/
		$resultat="";

		$resultat.=$this->affiche_entete();
		$resultat.=$this->affiche_requete($db);
		$resultat.=$this->affiche_pied();

		return $resultat;
	}

}

?>