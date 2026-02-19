<?php
	// Demarrage session php
	session_start();
	
	// Si login non detecte, renvoie vers la page de login
	if(!isset($_SESSION['login']))
	{
		header('Location: login.php');
		exit();
	}
	include('connexion.php');
?>

<?php
	$sql = 'SELECT * FROM enseignant WHERE login = :login';
    	$stmt = $pdo->prepare($sql);

    	// Exécution de la requête avec le login de session
    	$stmt->execute(['login' => $_SESSION['login']]);

    	// Récupération du résultat
    	$array = $stmt->fetch();
	/*$mdp=mysql_query('SELECT * FROM enseignant WHERE login=\''.$_SESSION['login'].'\'');
	$array=mysql_fetch_array($mdp);
*/
	if($array['pass']==md5('declic'))
	{
            ?>
                        <form action="index.php" method="post">
                            <div id="fondOpaque" style></div>
                            <div style="z-index: 2; position: absolute; position: center; color: white">    
                                
                                <div style="text-align: center; position:  relative; display: block; width: 550px; height: 225px; background-color: #888; border-radius: 8px; margin-left: 42%; margin-top: 25%">
                                <br>
                                    <?php
                                    echo'<w>Premi&egrave;re connexion, veuillez changer votre mot de passe !<w>'
                                    ?>
                                
                                <br>
                                <br>
                                <br>
                                
                                <?php
                                echo'<w>Login :<w>'
                                ?>
                                <input id="champ" type="text" name="login" style="border-radius: 4px; height: 30px; color:gray;" value="<?php if (isset($_POST['login'])) echo htmlentities(trim($_POST['login'])); else echo 'Login'; ?>" onblur="if(this.value=='') {this.style.color='gray'; this.value='Login'}" onfocus="this.style.color='black'; if(this.value=='Login') {this.value=''} "> 
                                
                                <?php
                                echo'<w>Mot de passe :<w>'
                                ?>
                                <input id="champ" type="<?php echo $passtype ?>" name="newpass" style="position: relative; border-radius: 4px; height: 30px; color:gray;" value="<?php echo 'Nouveau mot de passe'; ?>" onblur="if(this.value=='') {this.style.color='gray'; this.value='Nouveau mot de passe'; this.type='text'}" onfocus="this.style.color='black'; if(this.value=='Nouveau mot de passe') {this.value=''; this.type='password'}">   

                                <br><br><br><br>
                                
                                        <input id="bouttonmodif" type="submit" name="validemodif" value="Valider">
                                </div>

                            </div>
                            </div>
                        </form>
            
                        <?php
                        // TEST SI L'USER A VALIDER LA MODIFICATION (PASS)
                        if(isset($_POST['validemodif']) && $_POST['validemodif']=="Valider")
                            {

                            if(($_POST['newpass'] == "declic")||($_POST['newpass']== "Nouveau mot de passe") || ($_POST['login']!=$_SESSION['login']))
                                {
                                   
                                    echo "<script>alert('Mot de passe incorrect'); window.location.replace('index.php');</script>";
                                }
                                else
                                {
									$sql = 'UPDATE enseignant SET pass = :newpass WHERE login = :login';
									$stmt = $pdo->prepare($sql);

									$newpass = md5($_POST['newpass']);
									$login = $_POST['login'];

									$stmt->execute([
    									':newpass' => $newpass,
    									':login' => $login
									]);

                                    echo "<script>alert('Changement effectué avec succès'); window.location.replace('index.php');</script>";

									
                                    //header('Location: index.php');
                                    //exit();
                                }
                            }
                        ?>
                        
            <script>
                    }
            </script>

<?php
	}	
?>



<html lang="">
	<head>
		<meta charset="utf-8">
		<!-- ##################### IMPORT STYLE ##################### -->
		<link rel="stylesheet" href="css/s_index.css">
		<link rel="stylesheet" href="css/s_menu.css">
		<link rel="stylesheet" href="css/s_generique.css">
		
		<title>Accueil</title>
		
		<!-- Test si il y a un contenu de page a charger -->
		<?php
			if (isset($_GET['p']))
			{
				$p=$_GET['p'];
			}
			else
			{
				$page="back";
			}			
		?>
		
	</head>
	
	<body>
		
		<!-- ######################################### BARRE DE MENU ######################################### -->
		<div class="topbar">
			<!-- ######### Bouton Home ######### -->
			<a href="index.php?p=home"><div id="btnhome" class="btnbar_l" style="width:45px"><img src="images/home.png" style="height:65%; margin-top:5px;"></div></a>
			
			<?php
				if($_SESSION['droit'])
				{
			?>
					<!-- ####### Bouton Gestion ######## -->
					<div id="btngest" class="btnbar_l">Gestion</div>
					
						<!-- Bloc Gestion  -->
						<div id="blocgest" class="deroule_gest">
							<a id="linkdec" href="index.php?p=gest_mat">
								<div id="btndgest">Matière</div>
							</a>
							<a id="linkdec" href="index.php?p=gest_ens">
								<div id="btndgest">Enseignant</div>
							</a>
							<a id="linkdec" href="index.php?p=gest_ensmat">
								<div id="btndgest">Enseignement</div>
							</a>
							<a id="linkdec" href="index.php?p=gest_salle">
								<div id="btndgest">Salle</div>
							</a>
							<a id="linkdec" href="index.php?p=gest_dpt">
								<div id="btndgest">Département</div>
							</a>
							<a id="linkdec" href="index.php?p=gest_bat">
								<div id="btndgest">Bâtiment</div>
							</a>
							<a id="linkdec" href="index.php?p=gest_promo">
								<div style="border-bottom-left-radius: 6px; border-bottom-right-radius: 6px;" id="btndgest">Promotion</div>
							</a>
						</div>
						
					<!-- ####### Bouton Placement ######## -->
					<a id="linkdec" href="index.php?p=util_placement"><div id="btngest" class="btnbar_l">Placement</div></a>
			<?php
				}
				else
				{
			?>
					<!-- ####### Bouton Gestion ######## -->
					<div id="btngest" class="btnbar_l" style="display:none">Gestion</div>
						<!-- Bloc Gestion  -->
						<div id="blocgest" class="deroule_gest" style="display:none"></div>
					<!-- ####### Bouton Placement ######## -->
					<a id="linkdec" href="index.php?p=util_placement"><div id="btngest" class="btnbar_l">Placement</div></a>
			
			<?php
				}
			?>
					
			
			<!-- ####### Bouton Compte ######## -->
			<a id="linkdec" href="deconnexion.php"><div id="btncpt" class="btnbar_r">Déconnexion</div></a>
			
					
		</div>
		
		<!-- ############################################ CONTENU ############################################ -->
		<div id="content">
			<?php
				include("content.php");
			?>		
		</div>


		<!-- ######################################### IMPORT SCRIPT ######################################### -->
		<script src="javascript/jquery-1.7.1.js"></script>
		<script src="javascript/index.js"></script>
	
		
	</body>
</html>
