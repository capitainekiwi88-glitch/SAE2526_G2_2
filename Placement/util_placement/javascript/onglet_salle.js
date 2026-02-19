	// CHANGEMENT D'ONGLET
	function largeurBlocOnglet(idOnglet)
	{
		// Variables
		const myOnglet=document.getElementById('blocOnglet'+idOnglet);
		const barreOnglet=document.getElementById('barreOnglet');
		const onglet1=document.getElementById('onglet1');
		const onglet2=document.getElementById('onglet2');
		
		// Taille
		const taille=document.body.scrollWidth-10;
		
		if (barreOnglet) {
			barreOnglet.style.width=taille;
		}

		if(onglet2)
		{
			onglet1.style.width=(taille/2);
			onglet2.style.width=(taille/2);
		}
		else
		{
			onglet1.style.width=taille;
		}
		// Affectation taille
		myOnglet.style.width=taille;
		
	}
	
	window.onload=largeurBlocOnglet(1);
	
	
	// Adaptation taille frame

	function tailleFrame()
	{
		const myFrame=window.top.document.getElementById('myFrame');
		const taille=document.body.scrollHeight;
		myFrame.style.height=taille+25;
	}
	
	window.onload=tailleFrame();
	
	onglet1.addEventListener('click', function(e) {
		blocOnglet1.style.display="";
		onglet1.style.background='#BEBEBE';
		blocOnglet2.style.display='none';
		onglet2.style.background='gray';
		largeurBlocOnglet(1);
		tailleFrame();
	},false);
	
	if (onglet2) {
		onglet2.addEventListener('click', function(e) {
			blocOnglet1.style.display='none';
			onglet1.style.background='gray';
			blocOnglet2.style.display="";
			onglet2.style.background='#BEBEBE';
			largeurBlocOnglet(2);
			window.onload=largeurBlocOnglet(2);
			tailleFrame();
		},false);
	}
	
	

