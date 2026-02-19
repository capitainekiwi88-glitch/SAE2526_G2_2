<?php
	echo "<select name='groupe'>";
	if(isset($_POST["idPromo"])){
		include('../connexion.php');
		//$res=mysql_query("SELECT * FROM groupe WHERE id_promo='".$_POST["idPromo"]."'");
	 	$stmt = $pdo->prepare("SELECT * FROM groupe WHERE id_promo = :idPromo");

		$stmt->execute(['idPromo' => $_POST["idPromo"]]);
		echo "<option value='0'>Tous les groupes</option>";
		//while($row=mysql_fetch_assoc($res))
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) 		
		{
			echo "<option value='".$row["id_groupe"]."'>".$row["nom_groupe"]."</option>";
		}
	}
	echo "</select>";
?>
