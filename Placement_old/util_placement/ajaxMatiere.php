<?php	
	echo "<select name='matiere'>";
	if(isset($_POST["idPromo"])){
		include('../connexion.php');
		$stmt=$pdo->prepare("SELECT * FROM matiere WHERE id_promo= :idPromo ORDER BY nom_mat");
		$stmt->execute(['idPromo' => $_POST["idPromo"]]);	
		echo "<option value='A'>Mati&egrave;re</option>";
		while($row=$stmt->fetch(PDO::FETCH_ASSOC))
		{
			echo "<option value='".$row["id_mat"]."'>".$row["nom_mat"]."</option>";
		}
	}
	echo "</select>";
?>
