<?php
$passtype='text';
// Teste si le visiteur a soumis le formulaire de connexion
if (isset($_POST['connexion']) && $_POST['connexion'] == 'Connexion')
{
	if ((isset($_POST['login']) && !empty($_POST['login'])) && (isset($_POST['pass']) && !empty($_POST['pass'])))
	{
		include("connexion.php");

/*		// Teste si une entrée de la base contient le couple login / pass
		$sql = 'SELECT count(*) FROM enseignant WHERE login="'. mysql_real_escape_string($_POST['login']).'" AND pass="'. mysql_real_escape_string(md5($_POST['pass'])).'"';
		$req = mysql_query($sql) or die('Erreur SQL !<br />'.$sql.'<br />'.mysql_error());
		$data = mysql_fetch_array($req);

		// Recuperation des droits d'acces
		$sql2 = 'SELECT admin FROM enseignant WHERE login="'. mysql_real_escape_string($_POST['login']).'"';
		$req2 = mysql_query($sql2);
		$data2 = mysql_fetch_array($req2);

		mysql_free_result($req);
		mysql_close();
*/


try {
    // Préparation de la requête pour éviter les injections SQL
    $sql = 'SELECT count(*) as count FROM enseignant WHERE login = :login AND pass = :pass';
    $stmt = $pdo->prepare($sql);

    // Utilisation de paramètres préparés
    $login = $_POST['login'];
    $pass = md5($_POST['pass']);  // Hashage du mot de passe avec MD5 (mais il serait préférable d'utiliser une méthode plus sécurisée)
    
    // Exécution de la requête avec les données fournies par l'utilisateur
    $stmt->execute(['login' => $login, 'pass' => $pass]);

    // Récupération du résultat
    $data = $stmt->fetch();

        	$sql2 = 'SELECT admin FROM enseignant WHERE login = :login';
        	$stmt2 = $pdo->prepare($sql2);
        	$stmt2->execute(['login' => $login]);
        	$data2 = $stmt2->fetch();

} catch (PDOException $e) {
    die("Erreur de connexion ou de requête : " . $e->getMessage());
}


		// Si on obtient une réponse, alors l'utilisateur est un membre
		if ($data['count'] == 1)
		{
			session_start();
			$_SESSION['droit'] = $data2['admin'];
			$_SESSION['login'] = $_POST['login'];
			header('Location: index.php');
			exit();
		}
		// Si on ne trouve aucune réponse, le visiteur s'est trompé soit dans son login, soit dans son mot de passe
		elseif ($data['count'] == 0)
		{
			$erreur = 'Compte non reconnu.';
			$passtype='password';
		}
		// Sinon, alors la, il y a un gros problème :)
		else
		{
			$erreur = 'Probème dans la base de données : plusieurs membres ont les mêmes identifiants de connexion.';
		}
	}
	else
	{
		$erreur = 'Au moins un des champs est vide.';
		$passtype='password';
	}
}

?>

<html>
<head>
<title>Accueil</title>

<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" href="css/s_login.css">

<meta http-equiv="X-UA-Compatible" content="IE=8">
</head>

<body>
	<br><br><br>
	<h1>Service d'authentification</h1>
	<div id="blocinf"></div>
	<?php if (isset($erreur)) echo '<p>'.$erreur.'</p>'; ?>
	<div id="bloc1">
		<br><br>
		<form action="login.php" method="post">
		<input id="champ" type="text" name="login" style="color:gray;" value="<?php if (isset($_POST['login'])) echo htmlentities(trim($_POST['login'])); else echo 'Login'; ?>" onblur="if(this.value=='') {this.style.color='gray'; this.value='Login'}" onfocus="this.style.color='black'; if(this.value=='Login') {this.value=''} "> 
		<input id="champ" type="<?php echo $passtype ?>" name="pass" style="color:gray;" value="<?php if (isset($_POST['pass'])) echo htmlentities(trim($_POST['pass'])); else echo 'Mot de passe'; ?>" onblur="if(this.value=='') {this.style.color='gray'; this.value='Mot de passe'; this.type='text'}" onfocus="this.style.color='black'; if(this.value=='Mot de passe') {this.value=''; this.type='password'}">
		<input id="bouton" type="submit" name="connexion" value="Connexion">
		</form>
	</div>

</body>
</html>
