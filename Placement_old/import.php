// TEST SI L'USER A VALIDER L'IMPORTATION (Promo)
	if(isset($_POST['envoiefic']) && $_POST['envoiefic']=="Importer promotion")
	{
			rename($_FILES['myFile']['tmp_name'],  $_FILES['myFile']['name']);
			$fp = fopen($_FILES['myFile']['name'],"r");		
			
			//Département
			$ligne = fgets($fp,'4096');
			$ligne = fgets($fp,'4096');
			$ligne = fgets($fp,'4096');
			$liste = explode(";",$ligne);
			$liste[0]=(isset($liste[0])) ? $liste[0] : null;
			$dpt=$liste[0];
			
			$countex=mysql_query('SELECT COUNT(*) a FROM departement WHERE nom_dpt=\''.$dpt.'\'');
			// Test si la departement existe deja
			if(!mysql_result($countex,0))
			{
				// Requete d'ajout
				mysql_query('INSERT INTO departement (nom_dpt) VALUES ("'.$dpt.'")');				
			}
			$query=mysql_query('SELECT id_dpt FROM departement WHERE nom_dpt=\''.$dpt.'\'');
			$id_dpt=mysql_fetch_array($query);
			
			//Promotion
			$ligne = fgets($fp,'4096');
			$liste = explode(";",$ligne);
			$liste[0]=(isset($liste[0])) ? $liste[0] : null;
			$prom=$liste[0];
			$ligne = fgets($fp,'4096');
			$liste = explode(";",$ligne);
			$liste[0]=(isset($liste[0])) ? $liste[0] : null;
			$year=$liste[0];
			$year2=substr($year,0,4);
			$annee=intval($year2);
			
			$countex=mysql_query('SELECT COUNT(*) a FROM promotion WHERE nom_promo=\''.$prom.'\'AND annee=\''.$annee.'\'AND id_dpt=\''.$id_dpt['id_dpt'].'\'');
			// Test si la promotion existe deja
			if(!mysql_result($countex,0))
			{
				// Requete d'ajout
				mysql_query('INSERT INTO promotion (nom_promo, annee, id_dpt)  VALUES ("'.$prom.'","'.$annee.'","'.$id_dpt['id_dpt'].'")');
			}
			$query=mysql_query('SELECT id_promo FROM promotion WHERE nom_promo=\''.$prom.'\'AND annee=\''.$annee.'\'AND id_dpt=\''.$id_dpt['id_dpt'].'\'');
			$id_prom=mysql_fetch_array($query);

		
			//Groupe
			$ligne = fgets($fp,'4096');
			$ligne = fgets($fp,'4096');
			$ligne = fgets($fp,'4096');
			$liste = explode(";",$ligne);
			$nbligne=0;
			$nb_etud=0;
			$nb_grp=0;
			$ok=true;
			$id_grp=array();
			while ($ok==true)
			{	
				$liste[$nbligne] = (isset($liste[$nbligne])) ? $liste[$nbligne] : null;
				$nom_grp=$liste[$nbligne];
				if (($nom_grp!="") && ($nom_grp!="\n") && ($nom_grp!=" ") && ($nom_grp!="\r\n") && ($ok==true))
				{
					$nb_grp=$nb_grp+1;
					$countgrp=mysql_query('SELECT COUNT(*) a FROM groupe WHERE nom_groupe=\''.$nom_grp.'\'AND id_promo=\''.$id_prom['id_promo'].'\'');
					// Test si la promotion existe deja
					if(!mysql_result($countgrp,0))
					{
						// Requete d'ajout
						mysql_query('INSERT INTO groupe (nom_groupe, id_promo)  VALUES ("'.$nom_grp.'","'.$id_prom['id_promo'].'")');
					}
					$query=mysql_query('SELECT id_groupe FROM groupe WHERE nom_groupe=\''.$nom_grp.'\'AND id_promo=\''.$id_prom['id_promo'].'\'');
					$id_grp[$nb_grp]=mysql_fetch_array($query);
				}
				if (($nom_grp=="\n") || ($nom_grp=="\r\n")) $ok=false;
				$nbligne=$nbligne+1;
			}
			 
			//Etudiants
			$ligne = fgets($fp,'4096');			
			while (!feof($fp))
			{
				$ligne = fgets($fp,'4096');
				$liste = explode(";",$ligne);
				$grp=2;
				$nom_etud=3;
				$prenom_etud=4;
				$ok=true;
				$nb=1;
				while ($nb<=$nb_grp)
				{
					$groupe3=0;
					$liste[$grp] = (isset($liste[$grp])) ? $liste[$grp] : null;
					$liste[$nom_etud] = (isset($liste[$nom_etud])) ? $liste[$nom_etud] : null;
					$liste[$prenom_etud] = (isset($liste[$prenom_etud])) ? $liste[$prenom_etud] : null;
					$groupe=$liste[$grp];
					$nom=$liste[$nom_etud];
					$prenom=$liste[$prenom_etud];
					$groupe2=substr($groupe,0,1);
					$groupe3=intval($groupe2);
					
					if ((($nom!="\n") && ($prenom!="\n") && ($groupe!="\n")) || (($nom!="\r\n") && ($prenom!="\r\n") && ($groupe!="\r\n")) || (($nom!="") && ($prenom!="") && ($groupe!="")))
					{
						$countet=mysql_query('SELECT COUNT(*) a FROM etudiant WHERE nom_etudiant="'.$nom.'"AND prenom_etudiant="'.$prenom.'"');
						// Test si l'étudiant existe deja
						if(!mysql_result($countet,0))
						{
							if(isset($id_grp[$groupe3]['id_groupe']))$id=$id_grp[$groupe3]['id_groupe'];
							// Requete d'ajout
							mysql_query('INSERT INTO etudiant (nom_etudiant, prenom_etudiant, id_groupe)  VALUES ("'.$nom.'","'.$prenom.'","'.$id.'")');
							$query=mysql_query('SELECT nb_etud FROM groupe WHERE id_groupe="'.$id.'"');
							$nb_etud=mysql_fetch_array($query);
							$nb_etudiant=$nb_etud['nb_etud']+1;
							mysql_query('UPDATE groupe SET nb_etud="'.$nb_etudiant.'" WHERE id_groupe="'.$id.'"');
						}
						else
						{ 
							if(isset($id_grp[$groupe3]['id_groupe']))$id=$id_grp[$groupe3]['id_groupe'];
							$query=mysql_query('SELECT id_groupe FROM etudiant WHERE nom_etudiant="'.$nom.'"AND prenom_etudiant="'.$prenom.'"');
							$id_gr=mysql_fetch_array($query);
							$query=mysql_query('SELECT nb_etud FROM groupe WHERE id_groupe="'.$id_gr['id_groupe'].'"');
							$nb_etud=mysql_fetch_array($query);
							$nb_etudiant=$nb_etud['nb_etud']-1;														
							mysql_query('UPDATE groupe SET nb_etud="'.$nb_etudiant.'" WHERE id_groupe="'.$id_gr['id_groupe'].'"');
							mysql_query('DELETE FROM etudiant WHERE nom_etudiant="'.$nom.'"AND prenom_etudiant="'.$prenom.'"');
							mysql_query('INSERT INTO etudiant (nom_etudiant, prenom_etudiant, id_groupe)  VALUES ("'.$nom.'","'.$prenom.'","'.$id.'")');
							$query=mysql_query('SELECT nb_etud FROM groupe WHERE id_groupe="'.$id.'"');
							$nb_etud=mysql_fetch_array($query);
							$nb_etudiant=$nb_etud['nb_etud']+1;
							mysql_query('UPDATE groupe SET nb_etud="'.$nb_etudiant.'" WHERE id_groupe="'.$id.'"');
						}
						$grp=$grp+5;
						$nom_etud=$nom_etud+5;
						$prenom_etud=$prenom_etud+5;
						$nb=$nb+1;
					}					
				}
			}
			fclose($fp);
			echo "Le fichier a bien été envoyé <br>";

	}		